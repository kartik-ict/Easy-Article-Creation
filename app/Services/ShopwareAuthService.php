<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Session;
use Vin\ShopwareSdk\Data\AccessToken;
use Vin\ShopwareSdk\Client\AdminAuthenticator;
use Vin\ShopwareSdk\Client\GrantType\ClientCredentialsGrantType;

class ShopwareAuthService
{
    private $client;
    private $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('shopware.api_url');
    }

    // Generate OAuth Token
    private function generateToken()
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('shopware.client_id'),
                    'client_secret' => config('shopware.client_secret'),
                ],
            ]);

            $tokenData = json_decode($response->getBody(), true);

            // Store token and expiry in session
            $expiresIn = $tokenData['expires_in'] ?? 600;
            Session::put('shopware_api_token', $tokenData['access_token']);
            Session::put('shopware_api_token_expiry', now()->addSeconds($expiresIn - 60)); // Buffer expiry by 1 minute

            return $tokenData['access_token'];
        } catch (RequestException $e) {
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('Token generation failed: ' . $errorMessage);
        }
    }

    // Get token (generate if not available or expired)
    public function getToken()
    {
        // Check if token exists and is valid
        if (Session::has('shopware_api_token') && Session::has('shopware_api_token_expiry')) {
            if (Session::get('shopware_api_token_expiry') > now()) {
                return Session::get('shopware_api_token');
            }
        }

        // Generate a new token if expired or missing
        return $this->generateToken();
    }

    // Generic API Request Method
    public function makeApiRequest($method, $endpoint, $data = [], $retries = 3)
    {
        $retryDelay = 1;

        for ($i = 0; $i <= $retries; $i++) {
            try {
                $token = $this->getToken(); // Get or generate token

                $response = $this->client->request($method, $this->apiUrl . $endpoint, [
                    'headers' => [
                        'Accept' => 'application/vnd.api+json, application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'json' => $data,
                ]);

                if ($response->getStatusCode() === 204) {
                    return ['success' => true];
                }

                return json_decode($response->getBody(), true);
            } catch (RequestException $e) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;

                if ($statusCode === 429 && $i < $retries) {
                    // If rate-limited, wait before retrying
                    sleep($retryDelay);
                    $retryDelay *= 2; // Double the delay for exponential backoff
                    continue;
                }

                // If unauthorized (token expired), clear session and retry
                if ($statusCode === 401) {
                    Session::forget(['shopware_api_token', 'shopware_api_token_expiry']);
                    return $this->makeApiRequest($method, $endpoint, $data); // Retry with new token
                }

                // Return error for non-retriable responses
                $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                return ['error' => $errorMessage];
            }
        }

        // If all retries fail, return an error
        return ['error' => 'Too many requests. Please try again later.'];
    }

    // Generate SDK Token (Optional)
    public function getSDKToken(): AccessToken
    {
        $grantType = new ClientCredentialsGrantType(config('shopware.client_id'), config('shopware.client_secret'));
        $adminClient = new AdminAuthenticator($grantType, $this->apiUrl);

        return $adminClient->fetchAccessToken();
    }
}