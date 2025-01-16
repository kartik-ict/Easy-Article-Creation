<?php

namespace App\Services;

use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Entity\Product\ProductDefinition;
use Vin\ShopwareSdk\Factory\RepositoryFactory;
use Vin\ShopwareSdk\Data\Criteria;
use Vin\ShopwareSdk\Data\Filter\EqualsFilter;

class ShopwareProductService
{
    protected $authService;

    public function __construct(ShopwareAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function searchByEAN(string $ean)
    {
        $token = $this->authService->getSDKToken();
        $context = new Context(config('shopware.api_url'), $token);

        $productRepository = RepositoryFactory::create(ProductDefinition::ENTITY_NAME);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $ean)); // EAN as product number

        $products = $productRepository->search($criteria, $context);

        return $products->getEntities()->first(); // Return the first matching product
    }
}