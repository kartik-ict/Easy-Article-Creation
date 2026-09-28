<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Services\CurrencyService;
use App\Http\Controllers\Controller;
use App\Services\TaxDetailService;
use App\Services\TaxService;
use App\Models\ProductLog;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Services\ShopwareProductService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $shopwareProductService;
    private $shopwareApiService;
    private $currencyId;
    private $taxId;
    private $taxDetail;

    private $client;

    public function __construct(ShopwareProductService $shopwareProductService, ShopwareAuthService $shopwareApiService, CurrencyService $currencyId, TaxService $taxId, TaxDetailService $taxDetail, Client $client)
    {
        $this->shopwareProductService = $shopwareProductService;
        $this->shopwareApiService = $shopwareApiService;
        $this->currencyId = $currencyId;
        $this->taxId = $taxId;
        $this->taxDetail = $taxDetail;
        $this->client = new Client();
    }

    public function index(Request $request)
    {
        $admin = $request->user();

        $filter = [];
        if (!empty($admin->bin_location_ids)) {
            $filter = [
                'filter' => [
                    [
                        'type' => 'equalsAny',
                        'field' => 'id',
                        'value' => $admin->bin_location_ids
                    ]
                ]
            ];
        }
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-bin-location', $filter);
        $binLocationList = $response['data'] ?? [];
        return view('backend.pages.product.index', compact('admin', 'binLocationList'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'ean' => 'required|string',
        ]);

        $ean = $request->input('ean');
        // added multiple filter to search the product by EAN or Product number from shopware site.
        $payload = [
            'filter' => [
                [
                    'type' => 'multi',
                    'operator' => 'or',
                    'queries' => [
                        [
                            'type' => 'equals',
                            'field' => 'ean',
                            'value' => $ean,
                        ],
                        [
                            'type' => 'equals',
                            'field' => 'productNumber',
                            'value' => $ean,
                        ]
                    ]
                ]
            ],
            'associations' => [
                'children' => [],
                'manufacturer' => [],
                'tax' => [],
                'categories' => [],
                'media' => [],
                'options' => [
                    'associations' => [
                        'group' => []
                    ]
                ],
                'properties' => [
                    'associations' => [
                        'group' => []
                    ]
                ],
                'configuratorSettings' => [
                    'associations' => [
                        'option' => []
                    ]
                ],
            ],
            // 'includes' => [
            //     'product' => ['id', 'productNumber', 'ean', 'stock', 'translated', 'price', 'purchasePrices', 'customFields', 'optionIds', 'parentId']
            // ],
            'inheritance' => true,
            'total-count-mode' => 1,
        ];

        // Make API request using the common function
        $product = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product?inheritance=true', $payload);
        if (!$product['data']) {
            $apiKey = '7a507de2-fc1d-4eaf-88ff-f1401d2c155b';
            $site = 'bol.com';
            $fallbackUrl = "https://api.shoppingscraper.com/info";
            $fallbackUrlPrice = "https://api.shoppingscraper.com/offers";
            $client = new Client();

            try {
                /*                Dayanamic product data*/
                $response = $client->request('GET', $fallbackUrl, [
                    'query' => [
                        'site' => $site,
                        'ean' => $ean,
                        'api_key' => $apiKey
                    ]
                ]);
                $product = json_decode($response->getBody(), true);
                if ($product['results']) {
                    $responsePrice = $client->request('GET', $fallbackUrlPrice, [
                        'query' => [
                            'site' => $site,
                            'ean' => $product['results']['0']['ean'] ?? $ean,
                            'api_key' => $apiKey
                        ]
                    ]);

                    $productPrice = json_decode($responsePrice->getBody(), true);
                    /* Dayanamic product data */
                    // $product['results']['0']['categories'] = ["Oordopjes1"]; // for testing
                    $productData = [
                        'name' => $product['results']['0']['title'],
                        'ean' => $product['results']['0']['ean'] ?? $ean,
                        'stock' => 0,
                        'id' => '',
                        'productData' => $product['results'],
                        'productPriceData' => $productPrice['results'],
                        'taxData' => $this->taxDetail->getTaxDetail(),
                        'included' => '',
                        'bol' => true,
                        'custom_fields' => $this->getCustomFieldData(),
                    ];
                    return response()->json(['product' => $productData], 200);
                } else {
                    return response()->json(['error' => 'Product not found', 'custom_fields' => $this->getCustomFieldData()], 404);
                }
            } catch (RequestException $e) {
                return response()->json(['error' => 'Product not found'], 404);
            }
        } else {

            $optionsIds = null;
            $initialProducts = $product['data'] ?? [];
            $currentProduct = $initialProducts[0] ?? [];

            // Family variant lookup: fetch parent AND all child variants in this product family
            $familyId = $currentProduct['attributes']['parentId'] ?? ($currentProduct['id'] ?? null);
            $includedData = $product['included'] ?? [];
            $products = $initialProducts;

            if ($familyId) {
                $familyPayload = [
                    'filter' => [
                        [
                            'type' => 'multi',
                            'operator' => 'or',
                            'queries' => [
                                ['type' => 'equals', 'field' => 'id', 'value' => $familyId],
                                ['type' => 'equals', 'field' => 'parentId', 'value' => $familyId],
                            ]
                        ]
                    ],
                    'associations' => [
                        'options' => [
                            'associations' => [
                                'group' => []
                            ]
                        ],
                        'properties' => [
                            'associations' => [
                                'group' => []
                            ]
                        ],
                        'media' => [],
                        'categories' => []
                    ]
                ];

                $familyResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product?inheritance=true', $familyPayload);
                if (!empty($familyResponse['data'])) {
                    $products = $familyResponse['data'];
                    $includedData = $familyResponse['included'] ?? [];
                }
            }

            $mainProduct = collect($products)->first(function ($item) {
                return data_get($item, 'attributes.parentId') === null;
            }) ?? $currentProduct;
            $parentData = $mainProduct ?: null;

            if (($currentProduct['attributes']['parentId'] ?? null) == null) {
                $productId = $currentProduct['id'] ?? null;
                if ($productId) {
                    $parentProduct = $this->shopwareApiService->makeApiRequest('GET', "/api/product/?filter[parentId]=$productId&associations[configuratorSettings][associations][option]=[]");
                    if (isset($parentProduct['data']['0']['attributes']['optionIds'])) {
                        $optionsIds = $parentProduct['data']['0']['attributes']['optionIds'];
                    }
                }
            } else {
                // If product has parentId, fetch parent data
                $parentId = $currentProduct['attributes']['parentId'];
                if (($mainProduct['id'] ?? null) === $parentId) {
                    $parentData = $mainProduct;
                } else {
                    $parentProduct = $this->shopwareApiService->makeApiRequest('GET', "/api/product/$parentId");
                    if (isset($parentProduct['data'])) {
                        $parentData = $parentProduct['data'];
                    }
                }
            }

            $productData = [
                'name' => $currentProduct['attributes']['translated']['name'] ?? '',
                'ean' => $currentProduct['attributes']['ean'] ?? $ean,
                'stock' => $currentProduct['attributes']['stock'] ?? 0,
                'id' => $currentProduct['id'] ?? '',
                'productData' => $products,
                'included' => $includedData,
                'bol' => false,
                'optionsIds' => $optionsIds,
                'custom_fields' => $this->getCustomFieldData(),
                'parentData' => $parentData,
            ];
            return response()->json(['product' => $productData], 200);
        }
    }

    public function create(Request $request)
    {
        $customFields = $this->getCustomFieldData();
        $customFields = array_combine(array_column($customFields, 'name'), $customFields);
        $admin = $request->user();

        $filter = [];
        if (!empty($admin->bin_location_ids)) {
            $filter = [
                'filter' => [
                    [
                        'type' => 'equalsAny',
                        'field' => 'id',
                        'value' => $admin->bin_location_ids
                    ]
                ]
            ];
        }
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-bin-location', $filter);
        $binLocationList = $response['data'];
        return view('backend.pages.product.create', compact('customFields', 'admin', 'binLocationList'));
    }

    public function manufacturerSearch(Request $request)
    {
        // Prepare the data payload for the API request
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,             // You can adjust this limit if needed
            'term' => $request->get('term', ''),
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        // Make the API request using the common function
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product-manufacturer', $data);

        // Return the data in the required format for select2
        return response()->json([
            'manufacturers' => $response['data'] ?? [], // Manufacturer data
            'total' => $response['total'] ?? 0,          // Total manufacturers available
        ]);
    }

    public function searchSalesChannel(Request $request)
    {
        // Check if a search term is provided and reset the page to 1
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,             // You can adjust this limit if needed
            'term' => $request->get('term', ''),
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        // Make the API request to the sales channel endpoint
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/sales-channel', $data);

        // Return the response data in the required format for Select2
        return response()->json([
            'salesChannels' => $response['data'] ?? [], // Sales channel data
            'total' => $response['total'] ?? 0,         // Total sales channels available
        ]);
    }

    public function categorySearch(Request $request)
    {
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,             // You can adjust this limit if needed
            'term' => $request->get('term', ''),
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        // Make the API request using the shopwareApiService
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/category', $data);


        return response()->json([
            'categories' => $response['data'] ?? [], // category data
            'total' => $response['meta']['total'] ?? 0,          // Total category available
        ]);
    }

    public function fetchTaxProviders(Request $request)
    {
        // Prepare the data payload for the API request
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,             // Set limit if pagination is added later
            'term' => $request->get('term', ''), // Optional search term
            'total-count-mode' => 1    // Include total count in the response
        ];

        // Make the API request using the common function
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/tax', $data);

        // Return the data in the required format for select2
        return response()->json([
            'taxProviders' => $response['data'] ?? [], // Tax provider data
            'total' => $response['total'] ?? 0,       // Total count of tax providers
        ]);
    }

    public function SaveData(Request $request)
    {
        $currencyId = $this->currencyId->getCurrencyId();

        // Validate the incoming data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'nullable|integer|min:0',
            'manufacturer' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'taxId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'productNumber' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ean' => 'nullable|string|max:255',
            'salesChannel.*' => 'required|string',
            'category.*' => 'required|string',
            'mediaUrl' => 'nullable|url',
            'priceGross' => 'required|numeric',
            'priceNet' => 'required|numeric',
            'active_for_all' => 'nullable|boolean',
            'media_id' => 'nullable|string|regex:/^[0-9a-f]{32}$/',

            'purchasePrice' => 'required|numeric',
            'purchasePriceNet' => 'nullable|numeric',
            'bolProductShortDescription' => 'nullable|string',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolBeActive' => 'nullable|in:0,1',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolNlActive' => 'nullable|in:0,1',
            'hasSerialNumber' => 'nullable|in:0,1',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolNLDeliveryTime' => 'nullable|string',
            'bin_location_id' => 'nullable|string',
        ]);

        $customFields = $this->setCustomFieldd($validatedData);

        $productId = str_replace('-', '', (string) Str::uuid());
        $width = $request->get('productWidth');
        $height = $request->get('productHeight');
        $length = $request->get('productLength');
        $weight = $request->get('productWeight');

        // Prepare visibilities
        $visibilities = [];
        foreach ($validatedData['salesChannel'] as $salesChannelId) {
            $visibilityId = str_replace('-', '', (string) Str::uuid()); // Generate a random ID
            $visibilities[] = [
                'id' => $visibilityId,
                'productId' => $productId,
                'salesChannelId' => $salesChannelId,
                'visibility' => 30 // Default visibility
            ];
        }

        // Prepare categories
        $categories = [];
        foreach ($validatedData['category'] as $categoryId) {
            $categories[] = ['id' => $categoryId];
        }
        $productMediaId = str_replace('-', '', (string) Str::uuid());

        // Prepare the data for the API request
        $data = [
            'id' => $productId,
            'name' => $validatedData['name'],
            'stock' => 0,
            'manufacturerId' => $validatedData['manufacturer'],
            'taxId' => $validatedData['taxId'],
            'productNumber' => $validatedData['productNumber'],
            'description' => $validatedData['description'],
            'ean' => $validatedData['ean'],
            'categories' => $categories,
            'visibilities' => $visibilities,
            'active' => boolval($validatedData['active_for_all']),
            'weight' => $weight,
            'width' => $width,
            'height' => $height,
            'length' => $length,
            'coverId' => $productMediaId,
            'markAsTopseller' => false, // to make on sale default false
            'isCloseout' => true, // clearance sales ON
            'price' => [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['priceGross']),
                    'net' => floatval($validatedData['priceNet']),
                    'linked' => true
                ]
            ],
            'purchasePrices' => [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['purchasePrice']),
                    'net' => floatval($validatedData['purchasePriceNet'] ?? $validatedData['purchasePrice']),
                    'linked' => true,
                ]
            ]
        ];

        try {
            // Make the API request to create the product
            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/product', $data);
            // If the API call is successful
            if (isset($response['success'])) {
                if (isset($validatedData['media_id']) && !empty($validatedData['media_id'])) {
                    $productMediaData = [
                        'id' => $productMediaId,
                        'productId' => $productId,
                        'mediaId' => $validatedData['media_id'],
                        'position' => 1,
                    ];

                    $this->shopwareApiService->makeApiRequest('POST', '/api/product-media', $productMediaData);
                }

                // set custom fields data for created product
                $this->patchProductCustomData($productId, $customFields);
                if (!empty($validatedData['bin_location_id'])) {
                    // set stock to bin location
                    $stockData = [
                        'product_id' => $productId,
                        'stock' => intval($validatedData['stock'] ?? 0)
                    ];
                    $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);

                    // Log stock if stock > 0
                    if (intval($validatedData['stock'] ?? 0) > 0) {
                        $binLocationResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $validatedData['bin_location_id']);
                        $binLocationName = $binLocationResponse['data']['attributes']['code'] ?? 'Unknown';

                        ProductLog::logProductChange($productId, 'Stock',
                            ['stock' => 0],
                            [
                                'stock' => intval($validatedData['stock'] ?? 0),
                                'bin_location_name' => $binLocationName,
                                'type' => 'product_created'
                            ],
                            "0 → {$validatedData['stock']} → {$binLocationName}"
                        );
                    }
                }
                return redirect()->route('admin.product.index')->with('success', __('product.product_created_successfully'));
            } else {
                return back()->withErrors(__('product.failed_to_create_product'));
            }
        } catch (\Exception $e) {
            // Log and return error
            Log::error('Error creating product: ' . $e->getMessage());
            return back()->withErrors(__('product.failed_to_create_product'));
        }
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'new_stock' => 'required|integer|min:0',
            'bin_location_id' => 'required|string',
        ]);

        // Get current product data to capture old stock value
        $currentProduct = $this->shopwareApiService->makeApiRequest('GET', '/api/product/' . $request->product_id);
        $oldStock = $currentProduct['data']['attributes']['stock'] ?? 0;

        // Get bin location name
        $binLocationResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $request->bin_location_id);
        $binLocationName = $binLocationResponse['data']['attributes']['code'] ?? 'Unknown';

        $stockData = [
            'product_id' => $request->product_id,
            'stock' => intval($request->new_stock)
        ];

        try {
            // for make manage stock section true
            // $this->updateStockManagement($request->product_id);
            //set the stock to select bin location
            $this->setBinLocationStock($stockData, $request->bin_location_id);

            // Log ONLY stock change
            ProductLog::logProductChange($request->product_id, 'Stock',
                ['stock' => $oldStock],
                [
                    'stock' => $request->new_stock,
                    'bin_location_name' => $binLocationName,
                    'type' => 'update'
                ],
                "{$oldStock} → {$request->new_stock} → {$binLocationName}"
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function propertyGroupSearch(Request $request)
    {
        // Prepare the data payload for the API request
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,             // You can adjust this limit if needed
            // 'term' => $request->get('term', ''),
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        // Make the API request using the common function
        $response = $this->shopwareApiService->makeApiRequest('GET', '/api/property-group', $data);

        // Return the data in the required format for select2
        return response()->json([
            'propertyGroups' => $response['data'] ?? [], // Manufacturer data
            'total' => $response['total'] ?? 0,          // Total manufacturers available
        ]);
    }

    public function propertyGroupOption(Request $request)
    {
        // Prepare the data payload for the API request
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,
            'term' => $request->get('term', ''),
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'groupId',
                    'value' => $request->get('groupId', ''),
                ]
            ],
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];
        // Make the API request using the common function
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group-option', $data);

        // Return the data in the required format for select2
        return response()->json([
            'propertyGroups' => $response['data'] ?? [], // Manufacturer data
            'total' => $response['total'] ?? 0,          // Total manufacturers available
        ]);
    }

    // Example Controller Method to save the Property Option
    public function savePropertyOption(Request $request)
    {
        $validatedData = $request->validate([
            'groupId' => 'required|regex:/^[0-9a-f]{32}$/', // Ensure it matches the required pattern
            'optionName' => 'required|string|max:255', // Validate the option name
        ]);

        // Prepare the payload for the API request
        $uuid = str_replace('-', '', (string) Str::uuid());
        $payload = [
            'id' => $uuid,
            'groupId' => $validatedData['groupId'],
            'name' => $validatedData['optionName'],
        ];

        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/property-group-option', $payload);
        if ($response['success']) {
            return response()->json([
                'message' => __('product.property_option_saved_successfully')
            ]);
        }
    }

    public function saveVariantProduct(Request $request)
    {

        $currencyId = $this->currencyId->getCurrencyId();
        // Get parent product data to inherit manufacturer if needed
        $parentProduct = $this->shopwareApiService->makeApiRequest('GET', "/api/product/{$request->parentId}");
        $parentManufacturerId = $parentProduct['data']['attributes']['manufacturerId'] ?? null;

        // Validate the incoming data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'manufacturer' => 'nullable|string|regex:/^[0-9a-f]{32}$/',
            'taxId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'productNumber' => 'required|string|max:255',
            'parentId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'propertyOptionId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'propertyOptionIdAll' => 'required|string',
            'description' => 'nullable|string',
            'priceGross' => 'required|numeric',
            'priceNet' => 'required|numeric',
            'productEanNumber' => 'string',
            'listPriceGross' => 'required|numeric',
            'listPriceNet' => 'nullable|numeric',
            'purchasePrice' => 'required|numeric',
            'purchasePriceNet' => 'nullable|numeric',
            'bolProductShortDescription' => 'nullable|string',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolBeActive' => 'nullable|in:0,1',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolNlActive' => 'nullable|in:0,1',
            'hasSerialNumber' => 'nullable|in:0,1',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolNLDeliveryTime' => 'nullable|string',
            'bin_location_id' => 'required|string',
            'serialNumber' => 'nullable|string|max:255',
        ]);

        // DGM-307 feedback: a variant always inherits "Vereist serienummer" from its
        // parent — enforced here server-side, not left to whatever the creation form
        // happened to submit.
        $parentHasSerialNumber = $parentProduct['data']['attributes']['customFields']['has_serial_number'] ?? false;
        if ($parentHasSerialNumber) {
            $validatedData['hasSerialNumber'] = '1';
        }

        if (!empty($validatedData['hasSerialNumber']) && intval($validatedData['stock']) > 0 && empty($validatedData['serialNumber'])) {
            return response()->json(['errors' => __('product.serial_number_required')], 422);
        }

        $customFields = $this->setCustomFieldd($validatedData);

        // Generate a UUID for the new product
        $productVariantId = str_replace('-', '', (string) Str::uuid());

        // Tracks exactly what this request has actually committed to Shopware so far,
        // so that if any later step fails we can undo precisely that and nothing more
        // — instead of leaving a half-built variant that blocks the SKU forever and
        // desyncs the parent's configurator options (the "ghost variant" bug).
        $parentConfiguratorOptionIdsAdded = [];
        $childCreated = false;

        try {
            // Step 1: Update Parent Product
            $optionIds = explode(',', $request->get('propertyOptionIdAll'));
            $productConfiguratorSettingsIds = explode(',', $request->get('productConfiguratorSettingsIds'));

            // Remove matching IDs from $optionIds
            $filteredOptionIds = array_diff($optionIds, $productConfiguratorSettingsIds);

            if (!empty($filteredOptionIds)) {
                $parentUpdatePayload = [
                    'configuratorSettings' => array_map(fn($id) => ['optionId' => $id], $filteredOptionIds)
                ];

                $parentEndpoint = "/api/product/{$validatedData['parentId']}";

                try {
                    $responseParent = $this->shopwareApiService->makeApiRequest('PATCH', $parentEndpoint, $parentUpdatePayload);
                    if (isset($responseParent['error']) && !empty($responseParent['error'])) {
                        $errors = json_decode($responseParent['error'], true);
                        if (
                            is_array($errors['errors']) &&
                            $errors['errors'][0]['code'] === 'PRODUCT_CONFIGURATION_OPTION_EXISTS_ALREADY'
                        ) {
                            return response()->json(['errors' => "Configuratieoptie bestaat al"], 400);
                        } else {
                            return response()->json(['errors' => "Er is iets fout gegaan!"], 400);
                        }
                    }
                    // Step 1 succeeded — remember exactly what we added to the parent,
                    // so a failure further down can remove it again cleanly.
                    $parentConfiguratorOptionIdsAdded = $filteredOptionIds;
                } catch (\Exception $e) {
                    Log::info('Product variant creation Error ' . $e->getMessage());
                    return response()->json(['errors' => __('product.failed_to_update_product')], 400);
                }
            } else {
                $responseParent['success'] = true;
            }

            if (isset($responseParent['success']) && $responseParent['success']) {
                $width = $request->get('productPackagingWidth');
                $height = $request->get('productPackagingHeight');
                $length = $request->get('productPackagingLength');
                $weight = $request->get('productPackagingWeight');

                // Step 2: Create Child (Variant) Product
                $options = [
                    ['id' => $request->get('propertyOptionId')],
                    ['id' => $request->get('propertyOptionIdSecond')],
                    ['id' => $request->get('propertyOptionIdThird')],
                    ['id' => $request->get('propertyOptionIdFour')],
                    ['id' => $request->get('propertyOptionIdFive')],
                ];

                // Remove any null values from the array
                $options = array_filter($options, fn($option) => !is_null($option['id']));
                $data = [
                    'id' => $productVariantId,
                    'name' => $validatedData['name'],
                    'stock' => 0,
                    'manufacturerId' => $validatedData['manufacturer'] ?: $parentManufacturerId,
                    'taxId' => $validatedData['taxId'],
                    'parentId' => $validatedData['parentId'],
                    'productNumber' => $validatedData['productNumber'],
                    'description' => $validatedData['description'],
                    'weight' => $weight,
                    'width' => $width,
                    'height' => $height,
                    'ean' => $validatedData['productEanNumber'],
                    'length' => $length,
                    'markAsTopseller' => false, // to make on sale default false
                    'isCloseout' => true, // clearance sales ON
                    'price' => [
                        [
                            'currencyId' => $currencyId,
                            'gross' => floatval($validatedData['priceGross']),
                            'net' => floatval($validatedData['priceNet']),
                            'linked' => true,
                            'listPrice' => [
                                'currencyId' => $currencyId,
                                'gross' => floatval($validatedData['listPriceGross']),
                                'net' =>  floatval($validatedData['listPriceNet']),
                                'linked' => true,
                            ]
                        ]
                    ],
                    'purchasePrices' => [
                        [
                            'currencyId' => $currencyId,
                            'gross' => floatval($validatedData['purchasePrice']),
                            'net' => floatval($validatedData['purchasePriceNet'] ?? $validatedData['purchasePrice']),
                            'linked' => true,
                        ]
                    ],
                    'options' => $options,
                    "variantListingConfig" => [
                        "displayParent" => true
                    ],

                ];

                $childEndpoint = "/api/product";
                $response = $this->shopwareApiService->makeApiRequest('POST', $childEndpoint, $data);

                if (isset($response['success'])) {
                    $childCreated = true;

                    // Parse properties data if provided
                    $properties = null;
                    if ($request->has('properties') && !empty($request->properties)) {
                        $properties = json_decode($request->properties, true);
                    }

                    // set custom fields data for created product
                    $customDataSaved = $this->patchProductCustomData($productVariantId, $customFields, $properties);
                    if (!$customDataSaved) {
                        // The variant exists but its condition/property link never got attached —
                        // this is exactly the "N/A" ghost pattern. Don't report success on a half-built variant.
                        Log::error('Product variant creation Error: patchProductCustomData failed for ' . $productVariantId);
                        $this->rollbackVariantCreation($productVariantId, $childCreated, $validatedData['parentId'], $parentConfiguratorOptionIdsAdded);
                        return response()->json(['errors' => __('product.failed_to_update_product')], 400);
                    }

                    // set stock to bin location
                    $stockData = [
                        'product_id' => $productVariantId,
                        'stock' => intval($validatedData['stock'])
                    ];
                    // for make manage stock section true
                    // $this->updateStockManagement($productVariantId);

                    $stockSaved = $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);
                    if (!$stockSaved) {
                        Log::error('Product variant creation Error: setBinLocationStock failed for ' . $productVariantId);
                        $this->rollbackVariantCreation($productVariantId, $childCreated, $validatedData['parentId'], $parentConfiguratorOptionIdsAdded);
                        return response()->json(['errors' => __('product.failed_to_update_product')], 400);
                    }

                    if (!empty($validatedData['serialNumber']) && intval($validatedData['stock']) > 0) {
                        $this->saveSerialNumbers(
                            $productVariantId,
                            $validatedData['bin_location_id'],
                            $validatedData['serialNumber'],
                            intval($validatedData['stock'])
                        );
                    }

                    // After product/variant creation or update
                    // $this->setClearanceSaleOn($productVariantId);

                    // Log stock if stock > 0
                    if (intval($validatedData['stock']) > 0) {
                        $binLocationResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $validatedData['bin_location_id']);
                        $binLocationName = $binLocationResponse['data']['attributes']['code'] ?? 'Unknown';

                        ProductLog::logProductChange($productVariantId, 'Stock',
                            ['stock' => 0],
                            [
                                'stock' => intval($validatedData['stock']),
                                'bin_location_name' => $binLocationName,
                                'type' => 'new_variant'
                            ],
                            "0 → {$validatedData['stock']} → {$binLocationName}"
                        );
                    }
                    return response()->json([
                        'message' => __('product.product_created_successfully')
                    ]);
                } else {
                    // Check for duplicate product number error
                    if (isset($response['error'])) {
                        $errorData = json_decode($response['error'], true);
                        if (isset($errorData['errors'][0]['code']) && $errorData['errors'][0]['code'] === 'CONTENT__DUPLICATE_PRODUCT_NUMBER') {
                            $this->rollbackVariantCreation($productVariantId, $childCreated, $validatedData['parentId'], $parentConfiguratorOptionIdsAdded);
                            return response()->json(['errors' => 'Product already exists with the same number'], 400);
                        }
                    }
                    Log::error('Product variant creation Error: ' . json_encode($response));
                    $this->rollbackVariantCreation($productVariantId, $childCreated, $validatedData['parentId'], $parentConfiguratorOptionIdsAdded);
                    return response()->json(['errors' => __('product.failed_to_update_product')], 400);
                }
            } else {
                Log::error('Product variant creation Error: ' . json_encode($responseParent));
                return response()->json(['errors' => __('product.failed_to_update_product')], 400);
            }
        } catch (\Exception $e) {
            Log::error('Product variant creation Exception: ' . $e->getMessage());
            $this->rollbackVariantCreation($productVariantId, $childCreated, $request->get('parentId'), $parentConfiguratorOptionIdsAdded);
            return response()->json(['errors' => "Er is iets fout gegaan!"], 400);
        }
    }

    /**
     * Undo whatever partial state a failed variant creation left behind: delete the
     * orphaned child if it was created, and remove any option this request just added
     * to the parent's configurator settings. Without this, a failure here leaves a row
     * that permanently blocks the SKU and desyncs the parent's variant generator —
     * the root cause of DGM-309.
     */
    private function rollbackVariantCreation($productVariantId, $childCreated, $parentId, array $optionIdsToRemove)
    {
        if ($childCreated) {
            $deleteResponse = $this->shopwareApiService->makeApiRequest('DELETE', "/api/product/{$productVariantId}");
            if (!isset($deleteResponse['success'])) {
                Log::error('Variant rollback: failed to delete orphaned child ' . $productVariantId . ': ' . json_encode($deleteResponse));
            }
        }

        if (!empty($optionIdsToRemove) && $parentId) {
            $searchPayload = [
                'filter' => [
                    [
                        'type' => 'multi',
                        'operator' => 'and',
                        'queries' => [
                            ['type' => 'equals', 'field' => 'productId', 'value' => $parentId],
                            ['type' => 'equalsAny', 'field' => 'optionId', 'value' => array_values($optionIdsToRemove)],
                        ],
                    ],
                ],
            ];
            $searchResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product-configurator-setting', $searchPayload);

            foreach (($searchResponse['data'] ?? []) as $settingRow) {
                $settingId = $settingRow['id'] ?? null;
                if ($settingId) {
                    $this->shopwareApiService->makeApiRequest('DELETE', "/api/product-configurator-setting/{$settingId}");
                }
            }
        }
    }

    public function SaveBolData(Request $request)
    {

        $currencyId = $this->currencyId->getCurrencyId();

        $validatedData = $request->validate([
            'bolProductName' => 'string',
            'bolProductEanNumber' => 'string',
            'bolProductSku' => 'string',
            'bolProductManufacturerId' => 'string',
            'bolProductCategoriesId' => 'string',
            'bolProductDescription' => 'string',
            'bolPackagingWidth' => 'string',
            'bolStock' => 'string',
            'bolPackagingHeight' => 'string',
            'bolPackagingLength' => 'string',
            'bolPackagingWeight' => 'string',
            'bolProductPrice' => 'string',
            'bolTotalPrice' => 'string',
            'bolProductListPriceGross' => 'string',
            'bolProductListPriceNet' => 'string',
            'bolProductThumbnail' => 'nullable|string',
            'salesChannelBol' => 'required|array',
            'salesChannelBol.*' => 'required|string',
            'bolTaxId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'active_for_allBol' => 'nullable|boolean',

            'purchasePrice' => 'required|numeric',
            'purchasePriceNet' => 'nullable|numeric',
            'bolProductShortDescription' => 'nullable|string',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolBeActive' => 'nullable|in:0,1',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolNlActive' => 'nullable|in:0,1',
            'hasSerialNumber' => 'nullable|in:0,1',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolNLDeliveryTime' => 'nullable|string',
            'bin_location_id' => 'required|string',
            'propertyGroups' => 'nullable|array',
            'propertyGroups.*.groupId' => 'nullable|string',
            'propertyGroups.*.optionId' => 'nullable|string',
        ]);

        $customFields = $this->setCustomFieldd($validatedData);

        $uuid = str_replace('-', '', (string) Str::uuid());
        $mediaId = str_replace('-', '', (string) Str::uuid());
        $productMediaId = str_replace('-', '', (string) Str::uuid());

        $weight = $validatedData['bolPackagingWeight'] ?? 0;
        $width = $validatedData['bolPackagingWidth'] ?? 0;
        $height = $validatedData['bolPackagingHeight'] ?? 0;
        $length = $validatedData['bolPackagingLength'] ?? 0;

        $imageUrl = $validatedData['bolProductThumbnail'];
        $fileName = 'test';

        if ($imageUrl) {
            try {
                $data = [
                    'id' => $mediaId,
                    'name' => pathinfo($imageUrl, PATHINFO_FILENAME),
                ];
                $response = $this->shopwareApiService->makeApiRequest('POST', '/api/media', $data);

                // Step 2: Download the image
                $imageContent = Http::get($imageUrl)->body();
                $tempFilePath = storage_path("app/temp_{$fileName}");
                file_put_contents($tempFilePath, $imageContent);

                // Step 3: Upload the image to Shopware
                $uploadUrl = "/api/_action/media/{$mediaId}/upload";

                // Preparing data for the file upload
                $fileData = [
                    'file' => new \CURLFile($tempFilePath, mime_content_type($tempFilePath), $fileName),
                    'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                    'fileName' => pathinfo($fileName, PATHINFO_FILENAME),
                    'url' => $imageUrl,
                ];

                // Sending the file upload request using the makeApiRequest method
                $uploadResponse = $this->shopwareApiService->makeApiRequest('POST', $uploadUrl, $fileData);
                unlink($tempFilePath); // Clean up the temp file

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }


        // Prepare visibilities
        $visibilities = [];
        foreach ($validatedData['salesChannelBol'] as $salesChannelId) {
            $visibilityId = str_replace('-', '', (string) Str::uuid()); // Generate a random ID
            $visibilities[] = [
                'id' => $visibilityId,
                'productId' => $uuid,
                'salesChannelId' => $salesChannelId,
                'visibility' => 30 // Default visibility
            ];
        }

        $data = [
            'id' => $uuid,
            'name' => $validatedData['bolProductName'],
            'manufacturerId' => $validatedData['bolProductManufacturerId'],
            'productNumber' => $validatedData['bolProductSku'],
            'description' => $validatedData['bolProductDescription'],
            'ean' => $validatedData['bolProductEanNumber'],
            'categories' => array_map(fn($categoryId) => ['id' => trim($categoryId)], explode(',', $validatedData['bolProductCategoriesId'])), // Fix here
            'stock' => 0,
            'weight' => $weight,
            'width' => $width,
            'height' => $height,
            'length' => $length,
            'visibilities' => $visibilities,
            'taxId' => $validatedData['bolTaxId'],
            'active' => boolval($validatedData['active_for_allBol']),
            'coverId' => $productMediaId,
            'markAsTopseller' => false, // to make on sale default false
            'isCloseout' => true, // clearance sales ON
            'price' => [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['bolProductPrice']),
                    'net' => floatval($validatedData['bolTotalPrice']),
                    'linked' => true,
                    'listPrice' => [
                        'currencyId' => $currencyId,
                        'gross' => floatval($validatedData['bolProductListPriceGross']),
                        'net' =>  floatval($validatedData['bolProductListPriceNet']),
                        'linked' => true,
                    ]
                ]
            ],
            'purchasePrices' => [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['purchasePrice']),
                    'net' => floatval($validatedData['purchasePriceNet'] ?? $validatedData['purchasePrice']),
                    'linked' => true,
                ]
            ]
        ];
        try {
            // Make the API request to create the product
            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/product', $data);

            if (isset($response['success'])) {
                // Prepare properties if provided
                $properties = null;
                if (isset($validatedData['propertyGroups'])) {
                    $properties = [];
                    foreach ($validatedData['propertyGroups'] as $propertyGroup) {
                        if (!empty($propertyGroup['optionId'])) {
                            $properties[] = ['id' => $propertyGroup['optionId']];
                        }
                    }
                }

                // set custom fields data for created product
                $this->patchProductCustomData($uuid, $customFields, $properties);
                if (intval($validatedData['bolStock'] ?? 0) > 0) {
                    // set stock to bin location
                    $stockData = [
                        'product_id' => $uuid,
                        'stock' => (int)$validatedData['bolStock']
                    ];
                    $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);

                    // Log stock
                    $binLocationResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $validatedData['bin_location_id']);
                    $binLocationName = $binLocationResponse['data']['attributes']['code'] ?? 'Unknown';

                    ProductLog::logProductChange($uuid, 'Stock',
                        ['stock' => 0],
                        [
                            'stock' => (int)$validatedData['bolStock'],
                            'bin_location_name' => $binLocationName,
                            'type' => 'product_created_from_bol'
                        ],
                        "0 → {$validatedData['bolStock']} → {$binLocationName}"
                    );
                }
                $productMediaData = [
                    'id' => $productMediaId,
                    'productId' => $uuid,
                    'mediaId' => $mediaId,
                    'position' => 1,
                ];

                $response = $this->shopwareApiService->makeApiRequest('POST', '/api/product-media', $productMediaData);

                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            // Log and return error
            return response()->json([
                'error' => 'Something went wrong!',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadMedia(Request $request)
    {
        // Step 1: Validate File Upload
        $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:20480', // Max 20MB
        ]);

        // Step 2: Retrieve the Uploaded File
        $uploadedFile = $request->file('media');

        // Step 3: Generate Unique Media ID
        $mediaId = str_replace('-', '', (string)  Str::uuid());

        // Step 4: Clean and format the filename
        $originalName = $uploadedFile->getClientOriginalName();
        $cleanName = preg_replace('/[^a-zA-Z0-9.]/', '_', $originalName);
        $fileName = time() . '_' . $cleanName;

        $filePath = $uploadedFile->storeAs('public/media', $fileName);

        // Get public URL of the stored file
        $path = "storage/media/{$fileName}";
        $imageUrl = asset($path);

        // Step 5: Create Media Entry in Shopware
        $data = [
            'id' => $mediaId,
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
        ];
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/media', $data);

        // Step 6: Upload File to Shopware
        $uploadUrl = "/api/_action/media/{$mediaId}/upload";
        $fileData = [
            'file' => new \CURLFile(storage_path("app/public/media/{$fileName}"), mime_content_type(storage_path("app/public/media/{$fileName}")), $fileName),
            'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
            'fileName' => pathinfo($fileName, PATHINFO_FILENAME),
            'url' => $imageUrl,
        ];
        $uploadResponse = $this->shopwareApiService->makeApiRequest('POST', $uploadUrl, $fileData);
        // Step 7: Return Success Response
        return response()->json([
            'message' => 'Media uploaded successfully',
            'mediaId' => $mediaId,
            'mediaUrl' => $imageUrl,
            'shopwareResponse' => $uploadResponse,
        ]);
    }

    // to get the custome-fields data for product
    public function getCustomFieldData()
    {
        $customFieldSetData = [
            'page' => 1,
            'limit' => 25,
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'relations.entityName',
                    'value' => 'product'
                ],
                [
                    'type' => 'equals',
                    'field' => 'name',
                    'value' => 'migration_DMG_product'
                ]
            ],
            'total-count-mode' => 1
        ];

        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/custom-field-set', $customFieldSetData);

        if (empty($response['data'][0])) {
            return [];
        }

        $customFieldSetId = $response['data'][0]['id'];

        $customFieldData = $this->shopwareApiService->makeApiRequest('POST', "/api/search/custom-field-set/{$customFieldSetId}/custom-fields", [
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'customFieldSetId',
                    'value' => $customFieldSetId
                ],
                [
                    'type' => 'multi',
                    'operator' => 'OR',
                    'queries' => array_map(fn($fieldName) => [
                        'type' => 'equals',
                        'field' => 'name',
                        'value' => $fieldName,
                    ], config('shopware.custom_fields'))
                ]
            ],
            'sort' => [
                [
                    'field' => 'config.customFieldPosition',
                    'order' => 'ASC',
                    'naturalSorting' => true
                ]
            ],
            'total-count-mode' => 1
        ]);

        return array_map(function ($item) {
            $attr = $item['attributes'];
            $config = $attr['config'] ?? [];

            $options = [];
            if (!empty($config['options']) && is_array($config['options'])) {
                $options = array_map(fn($opt) => [
                    'label' => $opt['label']['nl-NL'] ?? $opt['value'],
                    'value' => $opt['value']
                ], $config['options']);
            }

            return [
                'name' => $attr['name'],
                'label' => $config['label']['nl-NL'] ?? $attr['name'],
                'is_select_type' => ($config['type'] ?? '') === 'select',
                'options' => $options
            ];
        }, $customFieldData['data'] ?? []);
    }

    public function patchProductCustomData($productVariantId, $customFields, $properties = null)
    {
        $data = [
            'id' => $productVariantId,
            'customFields' => $customFields
        ];

        if ($properties !== null) {
            $data['properties'] = $properties;
        }

        $response = $this->shopwareApiService->makeApiRequest('PATCH', '/api/product/' . $productVariantId, $data);
        if (isset($response['success'])) {
            return true;
        } else {
            return false;
        }
    }

    public function setCustomFieldd($validatedData)
    {
        return [
            "custom_product_message_" => $validatedData['bolProductShortDescription'] ?? null,
            "migration_DMG_product_bol_price_be" => $validatedData['bolBePrice'] ?? null,
            "migration_DMG_product_bol_price_nl" => $validatedData['bolNlPrice'] ?? null,
            "migration_DMG_product_bol_be_active" => isset($validatedData['bolBeActive']) ? (bool)$validatedData['bolBeActive'] : false,
            "has_serial_number" => isset($validatedData['hasSerialNumber']) ? (bool)$validatedData['hasSerialNumber'] : false,
            "migration_DMG_product_bol_condition" => $validatedData['bolCondition'] ?? null,
            "migration_DMG_product_bol_condition_desc" => $validatedData['bolConditionDescription'] ?? null,
            // "migration_DMG_product_variant_description_long" => $validatedData['bolConditionDescription'] ?? null,
            "migration_DMG_product_bol_nl_active" => isset($validatedData['bolNlActive']) ? (bool)$validatedData['bolNlActive'] : false,
            "migration_DMG_product_proposition_1" => isset($validatedData['bolOrderBeforeTomorrow']) ? (bool)$validatedData['bolOrderBeforeTomorrow'] : false,
            "migration_DMG_product_proposition_2" => isset($validatedData['bolOrderBefore']) ? (bool)$validatedData['bolOrderBefore'] : false,
            "migration_DMG_product_proposition_3" => isset($validatedData['bolLetterboxPackage']) ? (bool)$validatedData['bolLetterboxPackage'] : false,
            "migration_DMG_product_proposition_4" => isset($validatedData['bolLetterboxPackageUp']) ? (bool)$validatedData['bolLetterboxPackageUp'] : false,
            "migration_DMG_product_proposition_5" => isset($validatedData['bolPickUpOnly']) ? (bool)$validatedData['bolPickUpOnly'] : false,
            "migration_DMG_product_bol_be_delivery_code" => $validatedData['bolBEDeliveryTime'] ?? null,
            "migration_DMG_product_bol_nl_delivery_code" => $validatedData['bolNLDeliveryTime'] ?? null,
            "purchase_price" => $validatedData['purchasePrice'] ?? 0
        ];
    }


    public function warehouseSearch(Request $request)
    {
        $data = [
            'page' => $request->get('page', 1),
            'limit' => 25,             // You can adjust this limit if needed
            'term' => $request->get('term', ''),
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        // Make the API request using the shopwareApiService
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-warehouse', $data);


        return response()->json([
            'warehouses' => $response['data'] ?? [], // category data
            'total' => $response['meta']['total'] ?? 0,          // Total category available
        ]);
    }

    public function binLocationSearch(Request $request)
    {
        $data = [
            'page' => $request->get('page', 1),
            'term' => $request->get('term', ''),
            'associations' => [
                'warehouse' => [
                    'total-count-mode' => 1
                ]
            ],
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];
        if ($request->has('filter')) {
            $data['filter'] = $request->get('filter');
        } else {
            $data['limit'] = 25;
        }

        if ($request->get('warehouseId')) {
            $filters = isset($data['filter']) ? $data['filter'] : [];
            $filters[] = [
                'type' => 'equals',
                'field' => 'warehouse.id',
                'value' => $request->get('warehouseId')
            ];
            $data['filter'] = $filters;
        }

        // Make the API request using the shopwareApiService
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-bin-location', $data);


        return response()->json([
            'binLocations' => $response['data'] ?? [], // category data
            'total' => $response['meta']['total'] ?? 0,          // Total category available
        ]);
    }

    public function setBinLocationStock($productData, $binLocationId)
    {
        // Source can be one of:
        // "unknown" - Stock from unknown source
        // "supplier" - Stock received from supplier
        // "return" - Stock returned by customer
        // "correction" - Manual stock correction
        // "production" - Stock from production
        // Create stock movement payload for Pickware ERP
        $data = [
            [
                "id" => str_replace('-', '', (string) Str::uuid()),
                "productId" => $productData['product_id'],
                "quantity" => $productData['stock'],
                "source" => "unknown",
                "comment" => "",
                "destination" => [
                    "binLocation" => [
                        "id" => $binLocationId
                    ]
                ]
            ]
        ];

        try {
            // Make API request to create stock movement in Pickware
            $response = $this->shopwareApiService->makeApiRequest(
                'POST',
                '/api/_action/pickware-erp/stock/move',
                $data
            );

            return isset($response['success']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Creates one ictech_serial_number row per unit of stock, all sharing the same
     * serial string the user entered — one input field regardless of quantity, per
     * DGM-307 follow-up feedback. Each row stays individually trackable afterwards
     * (return-flow mismatch matching etc.), only the entry step is batched.
     */
    private function saveSerialNumbers(string $productId, ?string $binLocationId, string $serial, int $quantity): void
    {
        for ($i = 0; $i < $quantity; $i++) {
            $payload = [
                'id' => str_replace('-', '', (string) Str::uuid()),
                'serial' => $serial,
                'status' => 'in_stock',
                'productId' => $productId,
                // ictech_serial_number.product_version_id is a ReferenceVersionField with no
                // DB default — Shopware's live version id must be passed explicitly here.
                'productVersionId' => '0fa91ce3e96a4bc2be4bd9ce752c3425',
            ];
            if ($binLocationId) {
                $payload['binLocationId'] = $binLocationId;
            }

            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/ictech-serial-number', $payload);
            if (isset($response['error'])) {
                Log::error('Failed to save serial number', ['product_id' => $productId, 'error' => $response['error']]);
            }
        }
    }

    /**
     * DGM-307 hotfix — a variant created before the inheritance feature existed never had
     * its own has_serial_number field written, so it reads as false even though the parent
     * requires serial tracking. The storefront/admin display already falls back to the
     * parent's value for these (shows the toggle as checked), but updateProduct's
     * requirement check read only the variant's own field, so real stock updates on these
     * older variants could bypass the serial-number requirement entirely — confirmed live
     * on AS0711719453895 (a pre-existing variant under a serial-required parent). This
     * mirrors that same display fallback on the backend side.
     *
     * @param array $productAttributes A product's attributes, as returned by the Admin API
     */
    private function productOrParentRequiresSerialNumber(array $productAttributes): bool
    {
        if (!empty($productAttributes['customFields']['has_serial_number'])) {
            return true;
        }

        $parentId = $productAttributes['parentId'] ?? null;
        if (!$parentId) {
            return false;
        }

        $parentResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/product/' . $parentId);

        return !empty($parentResponse['data']['attributes']['customFields']['has_serial_number']);
    }

    public function updateProduct(Request $request)
    {
        $currencyId = $this->currencyId->getCurrencyId();

        $validatedData = $request->validate([
            'product_id' => 'required|string',
            'name' => 'required|string',
            'new_stock' => 'nullable|integer|min:0',
            'bin_location_id' => 'nullable|string',
            'productEanNumber' => 'nullable|string',
            'productNumber' => 'nullable|string',
            'description' => 'nullable|string',
            'priceGross' => 'nullable|numeric',
            'priceNet' => 'nullable|numeric',
            'purchasePriceNet' => 'nullable|numeric',
            'bolProductShortDescription' => 'nullable|string',
            'purchasePrice' => 'nullable|numeric',
            'listPriceGross' => 'nullable|numeric',
            'listPriceNet' => 'nullable|numeric',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolNlActive' => 'nullable|in:0,1',
            'hasSerialNumber' => 'nullable|in:0,1',
            'bolBeActive' => 'nullable|in:0,1',
            'bolNLDeliveryTime' => 'nullable|string',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'serialNumber' => 'nullable|string|max:255',
        ]);

        // DGM-307 feedback: a serial number is required before stock can be added for a
        // product that has "Vereist serienummer" enabled. Checked against the product's
        // state BEFORE any writes below — setCustomFieldd() below overwrites customFields
        // wholesale from this request alone, so has_serial_number must be read here first
        // or a restock that omits the (hidden, unrelated-to-this-form) field would silently
        // reset it to false before the check ever ran.
        $currentProductForSerialCheck = $this->shopwareApiService->makeApiRequest('GET', '/api/product/' . $validatedData['product_id']);
        $requiresSerialNumber = $this->productOrParentRequiresSerialNumber($currentProductForSerialCheck['data']['attributes'] ?? []);
        if ($requiresSerialNumber && !empty($validatedData['new_stock']) && empty($validatedData['serialNumber'])) {
            return response()->json(['success' => false, 'message' => __('product.serial_number_required')], 422);
        }

        $customFields = $this->setCustomFieldd($validatedData);

        // Prepare update data similar to SaveData function
        $updateData = [];

        if (!empty($validatedData['name'])) {
            $updateData['name'] = $validatedData['name'];
        }

        if (!empty($validatedData['productEanNumber'])) {
            $updateData['ean'] = $validatedData['productEanNumber'];
        }

        if (!empty($validatedData['productNumber'])) {
            $updateData['productNumber'] = $validatedData['productNumber'];
        }

        if (!empty($validatedData['description'])) {
            $updateData['description'] = $validatedData['description'];
        }

        if (!empty($validatedData['priceGross'])) {
            $priceData = [
                'currencyId' => $currencyId,
                'gross' => floatval($validatedData['priceGross']),
                'net' => floatval($validatedData['priceNet'] ?? $validatedData['priceGross'] / 1.21),
                'linked' => true
            ];

            // Add list price if provided
            if (!empty($validatedData['listPriceGross'])) {
                $priceData['listPrice'] = [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['listPriceGross']),
                    'net' => floatval($validatedData['listPriceNet'] ?? $validatedData['listPriceGross'] / 1.21),
                    'linked' => true,
                ];
            }

            $updateData['price'] = [$priceData];
        }

        if (!empty($validatedData['purchasePriceNet'])) {
            $updateData['purchasePrices'] = [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['purchasePriceNet']) * 1.21, // Calculate gross from net
                    'net' => floatval($validatedData['purchasePriceNet']),
                    'linked' => true,
                ]
            ];
        }

        if (!empty($customFields)) {
            $updateData['customFields'] = $customFields;
        }

        try {
            // Update the product with the prepared data
            if (!empty($updateData)) {
                $response = $this->shopwareApiService->makeApiRequest('PATCH', '/api/product/' . $validatedData['product_id'], $updateData);
            }

            // Handle properties update/removal
            if ($request->has('properties')) {
                $newProperties = [];
                $propertiesJson = $request->input('properties');
                if (!empty($propertiesJson)) {
                    $newProperties = json_decode($propertiesJson, true);
                }

                // Get current product properties
                $currentProduct = $this->shopwareApiService->makeApiRequest('GET', '/api/product/' . $validatedData['product_id'] . '?associations[properties][]');
                $currentProperties = $currentProduct['data']['relationships']['properties']['data'] ?? [];

                // Remove properties that are no longer needed
                foreach ($currentProperties as $currentProp) {
                    $found = false;
                    foreach ($newProperties as $newProp) {
                        if ($currentProp['id'] === $newProp['id']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // Remove this property
                        $this->shopwareApiService->makeApiRequest('DELETE', '/api/product/' . $validatedData['product_id'] . '/properties/' . $currentProp['id']);
                    }
                }

                // Add new properties
                foreach ($newProperties as $newProp) {
                    $found = false;
                    foreach ($currentProperties as $currentProp) {
                        if ($currentProp['id'] === $newProp['id']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // Add this property
                        $this->shopwareApiService->makeApiRequest('POST', '/api/product/' . $validatedData['product_id'] . '/properties', ['id' => $newProp['id']]);
                    }
                }
            }

            // Update stock if provided
            if (!empty($validatedData['new_stock']) && !empty($validatedData['bin_location_id'])) {
                // Stock isn't touched by the customFields PATCH above, so the early fetch
                // (used for the serial-number check) is still accurate for oldStock here.
                $oldStock = $currentProductForSerialCheck['data']['attributes']['stock'] ?? 0;

                // Get bin location name
                $binLocationResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $validatedData['bin_location_id']);
                $binLocationName = $binLocationResponse['data']['attributes']['code'] ?? 'Unknown';

                $stockData = [
                    'product_id' => $validatedData['product_id'],
                    'stock' => intval($validatedData['new_stock'])
                ];
                $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);
                // for make manage stock section true
                // $this->updateStockManagement($validatedData['product_id']);

                // Log ONLY stock change
                ProductLog::logProductChange($validatedData['product_id'], 'Stock',
                    ['stock' => $oldStock],
                    [
                        'stock' => $validatedData['new_stock'],
                        'bin_location_name' => $binLocationName,
                        'type' => 'update'
                    ],
                    "{$oldStock} → {$validatedData['new_stock']} → {$binLocationName}"
                );

                if (!empty($validatedData['serialNumber'])) {
                    $this->saveSerialNumbers(
                        $validatedData['product_id'],
                        $validatedData['bin_location_id'],
                        $validatedData['serialNumber'],
                        intval($validatedData['new_stock'])
                    );
                }
            }

            // After product/variant creation or update
            // $this->setClearanceSaleOn($validatedData['product_id']);

            return response()->json(['success' => true, 'message' => __('product.product_updated_successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('product.failed_to_update_product')], 500);
        }
    }

    public function getPickwareProductByProductId(string $productId): ?array
    {
        $payload = [
            'page' => 1,
            'limit' => 1,
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'productId',
                    'value' => $productId,
                ]
            ],
            'total-count-mode' => 1,
        ];
        Log::error('Pickware product Search', [
            'product_id' => $productId,
            'payload' => $payload,
        ]);
        try {
            $response = $this->shopwareApiService->makeApiRequest(
                'POST',
                '/api/search/pickware-erp-pickware-product',
                $payload
            );

            return $response['data'][0] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to find Pickware product', [
                'product_id' => $productId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
    public function updateStockManagement(string $productId, bool $disabled = false): bool
    {
        try {
            $pickwareProduct = $this->getPickwareProductByProductId($productId);

            if (!$pickwareProduct) {
                Log::error('Pickware product not found', [
                    'product_id' => $productId,
                ]);

                return false;
            }

            $pickwareEntityId = $pickwareProduct['id'];

            $response = $this->shopwareApiService->makeApiRequest(
                'PATCH',
                "/api/pickware-erp-pickware-product/{$pickwareEntityId}",
                [
                    'id' => $pickwareEntityId,
                    'isStockManagementDisabled' => $disabled,
                ]
            );

            Log::info('Pickware stock management updated', [
                'product_id' => $productId,
                'pickware_entity_id' => $pickwareEntityId,
                'disabled' => $disabled,
                'response' => $response,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update Pickware stock management', [
                'product_id' => $productId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enable Clearance Sales (isCloseout) for a product or variant.
     *
     * @param string $productId
     * @return bool
     */
    public function setClearanceSaleOn(string $productId): bool
    {
        try {
            if (empty($productId)) {
                Log::warning('setClearanceSaleOn: Product ID is empty.');

                return false;
            }

            Log::info('setClearanceSaleOn: Request started.', [
                'product_id' => $productId,
            ]);

            $payload = [
                'id' => $productId,
                'isCloseout' => true,
            ];

            $response = $this->shopwareApiService->makeApiRequest(
                'PATCH',
                '/api/product/' . $productId,
                $payload
            );

            Log::info('setClearanceSaleOn: Shopware API response.', [
                'product_id' => $productId,
                'response' => $response,
            ]);

            if (isset($response['success']) && $response['success']) {
                Log::info('setClearanceSaleOn: Clearance sale enabled successfully.', [
                    'product_id' => $productId,
                ]);

                return true;
            }

            Log::warning('setClearanceSaleOn: Shopware API returned unsuccessful response.', [
                'product_id' => $productId,
                'response' => $response,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('setClearanceSaleOn: Exception occurred.', [
                'product_id' => $productId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Return the live "Conditie" property group's options, for the inline relink
     * dropdown on variants shown as N/A. Looked up by name rather than a hardcoded
     * id, since the group's id is generated per Shopware install and would differ
     * between staging and production.
     */
    public function getConditieOptions()
    {
        $groupSearch = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group', [
            'filter' => [['type' => 'equals', 'field' => 'name', 'value' => 'Conditie']],
            'limit' => 1,
        ]);
        $groupId = $groupSearch['data'][0]['id'] ?? null;

        if (!$groupId) {
            return response()->json(['options' => []]);
        }

        $optionsSearch = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group-option', [
            'filter' => [['type' => 'equals', 'field' => 'groupId', 'value' => $groupId]],
            'sort' => [['field' => 'name', 'order' => 'ASC']],
            'limit' => 100,
        ]);

        $options = [];
        foreach (($optionsSearch['data'] ?? []) as $row) {
            $options[] = [
                'id' => $row['id'],
                'name' => $row['attributes']['name'] ?? $row['id'],
            ];
        }

        return response()->json(['options' => $options]);
    }

    /**
     * Link the real Conditie configurator option onto a single variant. This writes
     * to the actual product_option relationship (what drives the "N/A" label and
     * Shopware's own variant generator) — distinct from updateProduct()'s "properties"
     * handling, which only ever touches the unrelated informational product_property
     * table and was never able to fix this on its own.
     *
     * Any Conditie-group option the variant already has is removed first, so a variant
     * ends up with exactly one Condition value instead of stacking a second one
     * alongside a stale or wrong one. Options from other property groups (color,
     * size, ...) are never touched.
     */
    public function relinkConditie(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'option_id' => 'required|string|regex:/^[0-9a-f]{32}$/',
        ]);
        $productId = $validatedData['product_id'];
        $newOptionId = $validatedData['option_id'];

        $groupSearch = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group', [
            'filter' => [['type' => 'equals', 'field' => 'name', 'value' => 'Conditie']],
            'limit' => 1,
        ]);
        $conditieGroupId = $groupSearch['data'][0]['id'] ?? null;
        if (!$conditieGroupId) {
            Log::error('Conditie relink failed: Conditie property group not found');
            return response()->json(['errors' => __('product.failed_to_update_product')], 400);
        }

        $product = $this->shopwareApiService->makeApiRequest('GET', "/api/product/{$productId}");
        if (!isset($product['data'])) {
            return response()->json(['errors' => __('product.failed_to_update_product')], 404);
        }
        $currentOptionIds = $product['data']['attributes']['optionIds'] ?? [];

        // Of the variant's current options, find any that belong to the Conditie
        // group specifically — those are what we replace. Everything else is left alone.
        $existingConditieOptionIds = [];
        if (!empty($currentOptionIds)) {
            $optionsLookup = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group-option', [
                'filter' => [
                    ['type' => 'equalsAny', 'field' => 'id', 'value' => $currentOptionIds],
                    ['type' => 'equals', 'field' => 'groupId', 'value' => $conditieGroupId],
                ],
            ]);
            foreach (($optionsLookup['data'] ?? []) as $row) {
                $existingConditieOptionIds[] = $row['id'];
            }
        }

        foreach ($existingConditieOptionIds as $staleOptionId) {
            if ($staleOptionId === $newOptionId) {
                continue;
            }
            $this->shopwareApiService->makeApiRequest('DELETE', "/api/product/{$productId}/options/{$staleOptionId}");
        }

        $response = $this->shopwareApiService->makeApiRequest('POST', "/api/product/{$productId}/options", ['id' => $newOptionId]);
        if (!isset($response['success'])) {
            Log::error('Conditie relink failed for ' . $productId . ': ' . json_encode($response));
            return response()->json(['errors' => __('product.failed_to_update_product')], 400);
        }

        ProductLog::logProductChange(
            $productId,
            'Conditie relink',
            ['optionIds' => $existingConditieOptionIds],
            ['optionIds' => [$newOptionId]],
            'Conditie option linked via EAC (variant was showing as N/A)'
        );

        return response()->json(['message' => __('product.product_updated_successfully')]);
    }

    /**
     * Delete a single variant, but only when it's safe: zero stock and no order has
     * ever referenced it. Every delete is written to product_logs for audit. This
     * never touches the parent product or any sibling variant.
     */
    public function deleteVariant(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|string|regex:/^[0-9a-f]{32}$/',
        ]);
        $productId = $validatedData['product_id'];

        $product = $this->shopwareApiService->makeApiRequest('GET', "/api/product/{$productId}");
        if (!isset($product['data'])) {
            return response()->json(['errors' => __('product.failed_to_update_product')], 404);
        }

        $attributes = $product['data']['attributes'];
        $productNumber = $attributes['productNumber'] ?? $productId;
        $stock = intval($attributes['stock'] ?? 0);

        if ($stock > 0) {
            return response()->json(['errors' => __('product.variant_delete_blocked_stock')], 400);
        }

        $orderCheck = $this->shopwareApiService->makeApiRequest('POST', '/api/search/order-line-item', [
            'filter' => [['type' => 'equals', 'field' => 'productId', 'value' => $productId]],
            'limit' => 1,
        ]);
        if (!empty($orderCheck['data'])) {
            return response()->json(['errors' => __('product.variant_delete_blocked_orders')], 400);
        }

        $deleteResponse = $this->shopwareApiService->makeApiRequest('DELETE', "/api/product/{$productId}");
        if (!isset($deleteResponse['success'])) {
            Log::error('Variant delete failed for ' . $productId . ': ' . json_encode($deleteResponse));
            return response()->json(['errors' => __('product.failed_to_update_product')], 400);
        }

        // The product no longer exists, so we can't reuse logProductChange()'s
        // internal lookup — record the audit entry directly with the number we
        // already had before deleting.
        ProductLog::create([
            'product_number' => $productNumber,
            'user_id' => auth('admin')->id(),
            'action' => 'Delete',
            'old_values' => ['stock' => $stock],
            'new_values' => null,
            'message' => "Variant {$productNumber} deleted via EAC (stock was 0, no orders attached)",
            'ip_address' => request()->ip(),
        ]);

        return response()->json(['message' => __('product.variant_deleted_successfully')]);
    }


    /**
     * DGM-312 — Stock Correction page.
     * Shows the dedicated "Voorraad verlagen" page.
     * Additive — does not modify any existing method.
     */
    public function stockCorrectionIndex(Request $request)
    {
        $admin = $request->user();

        // 1. Fetch Warehouses
        $whResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-warehouse', []);
        $warehouseList = $whResponse['data'] ?? [];

        // 2. Fetch Bin Locations
        $filter = [];
        if (!empty($admin->bin_location_ids)) {
            $filter = [
                'filter' => [
                    [
                        'type'  => 'equalsAny',
                        'field' => 'id',
                        'value' => $admin->bin_location_ids,
                    ]
                ]
            ];
        }
        $binResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-bin-location', $filter);
        $binLocationList = $binResponse['data'] ?? [];

        return view('backend.pages.stock-correction.index', compact('admin', 'warehouseList', 'binLocationList'));
    }

    /**
     * DGM-312 — Decrease stock by N units for a specific variant.
     * Moves stock FROM the bin location TO Pickware "stock_correction" special location.
     * Additive — does not modify any existing method.
     */
    public function decreaseStock(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => ['required', 'string', 'regex:/^[0-9a-f]{32}$/'],
            'decrease_by'     => ['required', 'integer', 'min:1'],
            'warehouse_id'    => ['required', 'string'],
            'bin_location_id' => ['required', 'string'],
            'comment'         => ['nullable', 'string', 'max:50'],
        ]);

        $productId     = $validated['product_id'];
        $decreaseBy    = (int) $validated['decrease_by'];
        $binLocationId = $validated['bin_location_id'] ?? null;
        $warehouseId   = $validated['warehouse_id'] ?? null;
        $comment       = trim($validated['comment'] ?? '');

        if (!$binLocationId) {
            $admin = $request->user();
            $filter = [];
            if (!empty($admin->bin_location_ids)) {
                $filter = [
                    'filter' => [
                        [
                            'type'  => 'equalsAny',
                            'field' => 'id',
                            'value' => $admin->bin_location_ids,
                        ]
                    ]
                ];
            }
            $binRes = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-bin-location', $filter);
            $binLocationId = $binRes['data'][0]['id'] ?? null;
        }

        if (!$binLocationId) {
            return response()->json(['error' => 'Geen stellinglocatie gevonden.'], 400);
        }

        // Re-fetch current stock server-side — never trust the client value.
        $productResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/product/' . $productId);
        $currentStock    = (int) ($productResponse['data']['attributes']['stock'] ?? 0);

        if ($decreaseBy > $currentStock) {
            return response()->json([
                'error' => __('product.not_enough_stock'),
            ], 422);
        }

        // Fetch bin name for audit log.
        $binResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $binLocationId);
        $binName     = $binResponse['data']['attributes']['code'] ?? 'Unknown';

        // Pickware stock move: FROM bin TO stock_correction special location.
        $movementId = str_replace('-', '', (string) \Illuminate\Support\Str::uuid());
        $moveComment = $comment !== '' ? $comment : 'EAC stock correction - decrease';

        $payload = [
            [
                'id'          => $movementId,
                'productId'   => $productId,
                'quantity'    => $decreaseBy,
                'source'      => [
                    'binLocation' => ['id' => $binLocationId],
                ],
                'destination' => 'stock_correction',
                'comment'     => $moveComment,
            ]
        ];

        try {
            $this->shopwareApiService->makeApiRequest('POST', '/api/_action/pickware-erp/stock/move', $payload);

            $newStock = $currentStock - $decreaseBy;
            $logMessage = "{$currentStock} -> {$newStock} -> {$binName}";
            if ($comment !== '') {
                $logMessage .= " (Opmerking: {$comment})";
            }

            \App\Models\ProductLog::logProductChange(
                $productId,
                'Stock',
                ['stock' => $currentStock],
                [
                    'stock'             => $newStock,
                    'bin_location_name' => $binName,
                    'comment'           => $comment,
                    'type'              => 'decrease',
                ],
                $logMessage
            );

            return response()->json([
                'success'   => true,
                'new_stock' => $newStock,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DGM-312 decreaseStock failed: ' . $e->getMessage());
            return response()->json(['error' => __('product.stock_decrease_failed')], 500);
        }
    }

    /**
     * DGM-312 follow-up — how much of this product physically sits at this specific bin
     * location, per Pickware's warehouse-location aggregation. A dedicated function (not a
     * branch inside decreaseStock/decreaseStockAtBinLocation) per Kartik's instruction to
     * write new functions for new behaviour rather than adding conditions to existing ones.
     */
    private function getPhysicalStockAtBinLocation(string $productId, string $binLocationId): int
    {
        $stockByBinLocationId = $this->getNetStockByBinLocationForProduct($productId);

        return $stockByBinLocationId[$binLocationId] ?? 0;
    }

    /**
     * DGM-312 follow-up fix — the pickware-erp-warehouse-location-product-stock read-model
     * this originally queried turned out to be stale/unpopulated for stock that predates
     * Pickware tracking it (confirmed live: Rory's own Shopware admin screenshot showed
     * real per-bin stock — 8 + 1 = 9 — for a product where that read-model returned only
     * zeros). Computed here instead from the actual stock movement ledger, which cannot go
     * stale: net stock at a bin = sum(quantity moved TO it) - sum(quantity moved FROM it).
     * Verified against the same real product: this returns exactly 8 and 1, matching
     * Shopware admin's own displayed totals.
     *
     * @return array<string, int> binLocationId => net stock (bins with 0 or less omitted)
     */
    private function getNetStockByBinLocationForProduct(string $productId): array
    {
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-stock-movement', [
            'filter' => [['type' => 'equals', 'field' => 'productId', 'value' => $productId]],
            'limit' => 1,
            'aggregations' => [
                [
                    'name' => 'dest',
                    'type' => 'terms',
                    'field' => 'destinationBinLocationId',
                    'aggregation' => ['name' => 'qty', 'type' => 'sum', 'field' => 'quantity'],
                ],
                [
                    'name' => 'src',
                    'type' => 'terms',
                    'field' => 'sourceBinLocationId',
                    'aggregation' => ['name' => 'qty', 'type' => 'sum', 'field' => 'quantity'],
                ],
            ],
        ]);

        $net = [];
        foreach (($response['aggregations']['dest']['buckets'] ?? []) as $bucket) {
            if (empty($bucket['key'])) {
                continue; // non-bin destinations (orders, special stock locations, etc.)
            }
            $net[$bucket['key']] = ($net[$bucket['key']] ?? 0) + (int) $bucket['qty']['sum'];
        }
        foreach (($response['aggregations']['src']['buckets'] ?? []) as $bucket) {
            if (empty($bucket['key'])) {
                continue;
            }
            $net[$bucket['key']] = ($net[$bucket['key']] ?? 0) - (int) $bucket['qty']['sum'];
        }

        return array_filter($net, fn ($qty) => $qty > 0);
    }

    /**
     * DGM-312 follow-up — Rory reported that decreasing stock at a bin location that has
     * zero stock for the product still silently succeeds. Root cause: decreaseStock only
     * checks the product's TOTAL stock across all bin locations, never the specific bin's
     * own stock. A new function (not a condition added to decreaseStock) per Kartik's
     * instruction; decreaseStock itself is left untouched.
     */
    public function decreaseStockAtBinLocation(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => ['required', 'string', 'regex:/^[0-9a-f]{32}$/'],
            'decrease_by'     => ['required', 'integer', 'min:1'],
            'warehouse_id'    => ['required', 'string'],
            'bin_location_id' => ['required', 'string'],
            'comment'         => ['nullable', 'string', 'max:50'],
        ]);

        $productId     = $validated['product_id'];
        $decreaseBy    = (int) $validated['decrease_by'];
        $binLocationId = $validated['bin_location_id'];
        $comment       = trim($validated['comment'] ?? '');

        $binStock = $this->getPhysicalStockAtBinLocation($productId, $binLocationId);
        if ($decreaseBy > $binStock) {
            return response()->json(['error' => __('product.not_enough_stock_at_bin_location')], 422);
        }

        $productResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/product/' . $productId);
        $currentStock    = (int) ($productResponse['data']['attributes']['stock'] ?? 0);

        $binResponse = $this->shopwareApiService->makeApiRequest('GET', '/api/pickware-erp-bin-location/' . $binLocationId);
        $binName     = $binResponse['data']['attributes']['code'] ?? 'Unknown';

        $movementId  = str_replace('-', '', (string) \Illuminate\Support\Str::uuid());
        $moveComment = $comment !== '' ? $comment : 'EAC stock correction - decrease';

        $payload = [
            [
                'id'          => $movementId,
                'productId'   => $productId,
                'quantity'    => $decreaseBy,
                'source'      => [
                    'binLocation' => ['id' => $binLocationId],
                ],
                'destination' => 'stock_correction',
                'comment'     => $moveComment,
            ]
        ];

        try {
            $this->shopwareApiService->makeApiRequest('POST', '/api/_action/pickware-erp/stock/move', $payload);

            $newStock = $currentStock - $decreaseBy;
            $logMessage = "{$currentStock} -> {$newStock} -> {$binName}";
            if ($comment !== '') {
                $logMessage .= " (Opmerking: {$comment})";
            }

            \App\Models\ProductLog::logProductChange(
                $productId,
                'Stock',
                ['stock' => $currentStock],
                [
                    'stock'             => $newStock,
                    'bin_location_name' => $binName,
                    'comment'           => $comment,
                    'type'              => 'decrease',
                ],
                $logMessage
            );

            return response()->json([
                'success'   => true,
                'new_stock' => $newStock,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DGM-312 decreaseStockAtBinLocation failed: ' . $e->getMessage());
            return response()->json(['error' => __('product.stock_decrease_failed')], 500);
        }
    }

    /**
     * DGM-312 follow-up — bin locations where THIS product actually has physical stock,
     * per Rory's feedback (staff need to correct stock at a location even when they aren't
     * personally assigned to it, as long as the product is really sitting there). A new,
     * dedicated endpoint rather than adding a product-filter condition inside
     * stockCorrectionIndex's existing (user-assignment based) bin location list, per
     * Kartik's instruction.
     */
    public function getBinLocationsWithStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string', 'regex:/^[0-9a-f]{32}$/'],
        ]);

        $stockByBinLocationId = $this->getNetStockByBinLocationForProduct($validated['product_id']);

        if (empty($stockByBinLocationId)) {
            return response()->json(['binLocations' => []]);
        }

        $binDetailsResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/pickware-erp-bin-location', [
            'filter' => [['type' => 'equalsAny', 'field' => 'id', 'value' => array_keys($stockByBinLocationId)]],
            'limit'  => 100,
        ]);

        $binLocations = [];
        foreach (($binDetailsResponse['data'] ?? []) as $bin) {
            $binLocations[] = [
                'id'            => $bin['id'],
                'code'          => $bin['attributes']['code'] ?? $bin['id'],
                'warehouseId'   => $bin['attributes']['warehouseId'] ?? null,
                'physicalStock' => $stockByBinLocationId[$bin['id']],
            ];
        }

        return response()->json(['binLocations' => $binLocations]);
    }

    /**
     * DGM-307 — Serial Numbers lookup page. Read-only.
     */
    public function serialNumbersIndex(Request $request)
    {
        return view('backend.pages.serial-numbers.index');
    }

    /**
     * DGM-307 — AJAX search: by serial number (partial match) or by product number/EAN (exact),
     * whichever the query looks like. Read-only, queries the Admin API only, no writes.
     */
    public function searchSerialNumbers(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
        ]);
        $query = trim($validated['query']);

        $filter = [
            [
                'type'  => 'contains',
                'field' => 'serial',
                'value' => $query,
            ],
        ];

        // If nothing matches by serial, try resolving the query as a product number/EAN instead.
        $searchResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/ictech-serial-number', [
            'filter'      => $filter,
            'associations' => ['product' => new \stdClass()],
            'limit'       => 100,
            'sort'        => [['field' => 'createdAt', 'order' => 'DESC']],
        ]);
        $rows = $searchResponse['data'] ?? [];

        if (empty($rows)) {
            $productResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product', [
                'filter' => [
                    [
                        'type'  => 'multi',
                        'operator' => 'or',
                        'queries' => [
                            ['type' => 'equals', 'field' => 'productNumber', 'value' => $query],
                            ['type' => 'equals', 'field' => 'ean', 'value' => $query],
                        ],
                    ],
                ],
                'limit' => 10,
            ]);
            $productIds = array_column($productResponse['data'] ?? [], 'id');

            if (!empty($productIds)) {
                $searchResponse = $this->shopwareApiService->makeApiRequest('POST', '/api/search/ictech-serial-number', [
                    'filter'      => [
                        ['type' => 'equalsAny', 'field' => 'productId', 'value' => $productIds],
                    ],
                    'associations' => ['product' => new \stdClass()],
                    'limit'       => 100,
                    'sort'        => [['field' => 'createdAt', 'order' => 'DESC']],
                ]);
                $rows = $searchResponse['data'] ?? [];
            }
        }

        // Build a lookup of included products (JSON:API `included` array) by id, so each row
        // can show a readable product name/number instead of just a raw product id.
        $includedProductsById = [];
        foreach (($searchResponse['included'] ?? []) as $included) {
            if (($included['type'] ?? null) === 'product') {
                $includedProductsById[$included['id']] = $included['attributes'] ?? [];
            }
        }

        $results = array_map(function ($row) use ($includedProductsById) {
            $attrs = $row['attributes'] ?? [];
            $productId = $attrs['productId'] ?? null;
            $productAttrs = $productId ? ($includedProductsById[$productId] ?? []) : [];

            return [
                'serial'          => $attrs['serial'] ?? null,
                'status'          => $attrs['status'] ?? null,
                'productId'       => $productId,
                'productNumber'   => $productAttrs['productNumber'] ?? null,
                'productName'     => $productAttrs['translated']['name'] ?? ($productAttrs['name'] ?? null),
                'orderLineItemId' => $attrs['orderLineItemId'] ?? null,
                'createdAt'       => $attrs['createdAt'] ?? null,
            ];
        }, $rows);

        return response()->json(['results' => $results]);
    }

}
