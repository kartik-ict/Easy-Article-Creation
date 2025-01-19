<?php

namespace App\Services;

use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Entity\Product\ProductDefinition;
use Vin\ShopwareSdk\Data\Entity\ProductManufacturerTranslation\ProductManufacturerTranslationDefinition;
use Vin\ShopwareSdk\Factory\RepositoryFactory;
use Vin\ShopwareSdk\Data\Criteria;
use Vin\ShopwareSdk\Data\Filter\EqualsFilter;
use Vin\ShopwareSdk\Data\Filter\ContainsFilter;

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

    public function searchManufacturer($searchTerm)
    {
        $token = $this->authService->getSDKToken(); // Ensure you have a valid token

        $context = new Context(config('shopware.api_url'), $token);

        $manufacturerRepository = RepositoryFactory::create(ProductManufacturerTranslationDefinition::ENTITY_NAME);

        $criteria = new Criteria();
        if ($searchTerm) {
            $criteria->addFilter(new EqualsFilter('name', $searchTerm));
        }
        sleep(2);
        $manufacturers = $manufacturerRepository->search($criteria, $context);

        return $manufacturers->getEntities();
    }
}