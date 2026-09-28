<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Session;

/**
 * Mirrors ShopwareAuthService's shape exactly (same OAuth client_credentials + cached-token +
 * retrying-request pattern), pointed at Bol's Retailer API instead of Shopware's Admin API.
 */
class BolAuthService
{
    private $client;
    private $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('bol.api_url');
    }

    // Generate OAuth Token
    private function generateToken()
    {
        try {
            // UNVERIFIED (can't be tested without real credentials): Bol's docs confirm
            // grant_type=client_credentials against login.bol.com/token but don't say whether
            // client_id/secret go as HTTP Basic Auth or as form fields. Basic Auth is what
            // OAuth2's spec recommends and is used here as the best guess — if token requests
            // fail once real credentials exist, try moving client_id/secret into 'form_params'
            // below instead (same shape as ShopwareAuthService's own token call).
            $response = $this->client->post(config('bol.token_url'), [
                'auth' => [config('bol.client_id'), config('bol.client_secret')],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'scope' => 'retailer',
                ],
            ]);

            $tokenData = json_decode($response->getBody(), true);

            $expiresIn = $tokenData['expires_in'] ?? 300;
            Session::put('bol_api_token', $tokenData['access_token']);
            Session::put('bol_api_token_expiry', now()->addSeconds($expiresIn - 30)); // Bol tokens are short-lived (5-15 min), buffer by 30s

            return $tokenData['access_token'];
        } catch (RequestException $e) {
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('Bol token generation failed: ' . $errorMessage);
        }
    }

    // Get token (generate if not available or expired)
    public function getToken()
    {
        if (Session::has('bol_api_token') && Session::has('bol_api_token_expiry')) {
            if (Session::get('bol_api_token_expiry') > now()) {
                return Session::get('bol_api_token');
            }
        }

        return $this->generateToken();
    }

    /**
     * Generic API Request Method. $accept lets callers ask for the CSV media type the offer-export
     * report endpoint returns instead of JSON.
     */
    public function makeApiRequest($method, $endpoint, $data = [], $retries = 3, $accept = 'application/vnd.retailer.v10+json')
    {
        $retryDelay = 1;

        for ($i = 0; $i <= $retries; $i++) {
            try {
                $token = $this->getToken();

                $options = [
                    'headers' => [
                        'Accept' => $accept,
                        'Authorization' => 'Bearer ' . $token,
                    ],
                ];
                if (!empty($data)) {
                    $options['json'] = $data;
                    $options['headers']['Content-Type'] = $accept;
                }

                $response = $this->client->request($method, $this->apiUrl . $endpoint, $options);

                if (in_array($response->getStatusCode(), [201, 204])) {
                    return ['success' => true];
                }

                $body = (string) $response->getBody();
                if (str_contains($accept, 'csv')) {
                    return ['success' => true, 'raw' => $body];
                }

                return json_decode($body, true);
            } catch (RequestException $e) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;

                if ($statusCode === 429 && $i < $retries) {
                    sleep($retryDelay);
                    $retryDelay *= 2;
                    continue;
                }

                if ($statusCode === 401) {
                    Session::forget(['bol_api_token', 'bol_api_token_expiry']);
                    return $this->makeApiRequest($method, $endpoint, $data, 0, $accept); // one retry with a fresh token, no further recursion
                }

                $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                return ['error' => $errorMessage];
            } catch (\Exception $e) {
                // getToken()/generateToken() throw a plain \Exception (not RequestException) on
                // failure — catch it here too so a bad/missing client_id+secret surfaces as a
                // clean error response instead of an uncaught 500.
                return ['error' => $e->getMessage()];
            }
        }

        return ['error' => 'Too many requests. Please try again later.'];
    }
}
