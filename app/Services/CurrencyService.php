<?php

namespace App\Services;

use App\Services\ShopwareAuthService;

class CurrencyService
{
    protected $currencyId = null;
    protected $shopwareApiService;

    public function __construct(ShopwareAuthService $shopwareApiService)
    {
        $this->shopwareApiService = $shopwareApiService;
    }

    public function getCurrencyId()
    {
        // Return cached currency ID if already fetched
        if ($this->currencyId) {
            return $this->currencyId;
        }

        // Prepare the payload for the API call
        $payload = [
            "page" => 1,
            "limit" => 10,
            "filter" => [
                [
                    "type" => "equals",
                    "field" => "name",
                    "value" => "Euro"
                ]
            ]
        ];

        // Use the common function to call the API
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/currency', $payload);

        // Check the response and fetch the currency ID
        if (!empty($response['data'][0]['id'])) {
            $this->currencyId = $response['data'][0]['id'];
        }

        return $this->currencyId;
    }
}
