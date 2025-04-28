<?php
return [
    'api_url' => env('SHOPWARE_API_URL', 'default-url'),
    'client_id' => env('SHOPWARE_CLIENT_ID', 'default-client-id'),
    'client_secret' => env('SHOPWARE_CLIENT_SECRET', 'default-client-secret'),
    'custom_fields' => [
        "migration_DMG_product_bol_price_be", // price of bol BE
        "migration_DMG_product_bol_price_nl", // price for bol NL
        "migration_DMG_product_bol_be_active", // Active for bol BE
        "migration_DMG_product_bol_condition", // bol condition
        "migration_DMG_product_bol_condition_desc", // bol condition description
        "migration_DMG_product_bol_nl_active", // Active for nl
        "migration_DMG_product_proposition_1", // Ordered before 11pm, delivered tomorrow
        "migration_DMG_product_proposition_2", // Ordered before 4pm, delivered tomorrow - up to 13.99
        "migration_DMG_product_proposition_3", // Letterbox package 14€+
        "migration_DMG_product_proposition_4", // Letterbox package up to 13.99
        "migration_DMG_product_proposition_5", // Pick up only
        "migration_DMG_product_bol_be_delivery_code", // BOL BE Delivery time
        "migration_DMG_product_bol_nl_delivery_code", // BOL NL Delivery time
    ],
];
