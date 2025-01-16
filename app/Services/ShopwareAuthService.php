<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Vin\ShopwareSdk\Data\AccessToken;
use \Vin\ShopwareSdk\Client\AdminAuthenticator;
use \Vin\ShopwareSdk\Client\GrantType\ClientCredentialsGrantType;

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

            // Cache the token with its expiration time
            $expiresIn = $tokenData['expires_in'] ?? 3600; // Default to 1 hour
            Cache::put('shopware_api_token', $tokenData['access_token'], $expiresIn - 60); // Store token for slightly less time to avoid expiry issues

            return $tokenData['access_token'];
        } catch (RequestException $e) {
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('Token generation failed: ' . $errorMessage);
        }
    }

    // Get token (generate if not available or expired)
    public function getToken()
    {
        if (Cache::has('shopware_api_token')) {
            return Cache::get('shopware_api_token');
        }

        return $this->generateToken();
    }

    // Generic API Request Method
    public function makeApiRequest($method, $endpoint, $data = [])
    {
        try {
            $token = $this->getToken();

            $response = $this->client->request($method, $this->apiUrl . $endpoint, [
                'headers' => [
                    'Accept' => 'application/vnd.api+json, application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $data,
            ]);

            if ($response->getStatusCode() == 204) {
                return ['success' => true];
            } else {
                return json_decode($response->getBody(), true);
            }
        } catch (RequestException $e) {
            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();

            // If unauthorized (token expired), refresh token and retry once
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 401) {
                $newToken = $this->generateToken();

                return $this->makeApiRequest($method, $endpoint, $data); // Retry with new token
            }

            return ['error' => $errorMessage];
        }
    }

    public function getSDKToken(): AccessToken
    {
        $grantType = new ClientCredentialsGrantType(config('shopware.client_id'), config('shopware.client_secret'));
        $adminClient = new AdminAuthenticator($grantType, $this->apiUrl);

        return $adminClient->fetchAccessToken();
    }
}
