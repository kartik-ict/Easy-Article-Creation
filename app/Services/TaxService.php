<?php

namespace App\Services;

use App\Services\ShopwareAuthService;

class TaxService
{
    protected $taxId = null;
    protected $shopwareApiService;

    public function __construct(ShopwareAuthService $shopwareApiService)
    {
        $this->shopwareApiService = $shopwareApiService;
    }

    public function getTaxId()
    {
        // Return cached currency ID if already fetched
        if ($this->taxId) {
            return $this->taxId;
        }

        // Prepare the payload for the API call
        $payload = [
            "filter" => [
                [
                    "type" => "contains",
                    "field" => "name",
                    "value" => "Standard rate"
                ]
            ]
        ];

        // Use the common function to call the API
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/tax', $payload);

        // Check the response and fetch the currency ID
        if (!empty($response['data'][0]['id'])) {
            $this->taxId = $response['data'][0]['id'];
        }

        return $this->taxId;
    }
}
