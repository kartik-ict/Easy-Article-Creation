<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Services\CurrencyService;
use App\Http\Controllers\Controller;
use App\Services\TaxDetailService;
use App\Services\TaxService;
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
        $binLocationList = $response['data'];
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
                    /*                Dayanamic product data*/

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
            if ($product['data'][0]['attributes']['parentId'] == null) {
                $productId = $product['data'][0]['id'];
                $parentProduct = $this->shopwareApiService->makeApiRequest('GET', "/api/product/?filter[parentId]=$productId&associations[configuratorSettings][associations][option]=[]");
                if (isset($parentProduct['data']['0']['attributes']['optionIds'])) {
                    $optionsIds = $parentProduct['data']['0']['attributes']['optionIds'];
                }
            }

            $productData = [
                'name' => $product['data']['0']['attributes']['translated']['name'],
                'ean' => $product['data']['0']['attributes']['ean'] ?? $ean,
                'stock' => $product['data']['0']['attributes']['stock'],
                'id' => $product['data']['0']['id'],
                'productData' => $product['data'],
                'included' => $product['included'],
                'bol' => false,
                'optionsIds' => $optionsIds,
                'custom_fields' => $this->getCustomFieldData(),
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
            'stock' => 'required|integer',
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

            'bolProductShortDescription' => 'nullable|string',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolBeActive' => 'nullable|in:0,1',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolNlActive' => 'nullable|in:0,1',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolNLDeliveryTime' => 'nullable|string',
            'bin_location_id' => 'required|string',
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
            'markAsTopseller' => true, // to make on sale default true
            'price' => [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['priceGross']),
                    'net' => floatval($validatedData['priceNet']),
                    'linked' => true
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

                // set stock to bin location
                $stockData = [
                    'product_id' => $productId,
                    'stock' => intval($validatedData['stock'])
                ];
                $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);
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

        $stockData = [
            'product_id' => $request->product_id,
            'stock' => intval($request->new_stock)
        ];
        // Using common API call function
        try {
            //set the stock to select bin location
            $this->setBinLocationStock($stockData, $request->bin_location_id);

            // $response = $this->shopwareApiService->makeApiRequest(
            //     'PATCH',
            //     '/api/product/' . $request->product_id,
            //     $data
            // );

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

        // Validate the incoming data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'manufacturer' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'taxId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'productNumber' => 'required|string|max:255',
            'parentId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'propertyOptionId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'propertyOptionIdAll' => 'required|string',
            'description' => 'nullable|string',
            'priceGross' => 'required|numeric',
            'priceNet' => 'required|numeric',
            'productEanNumber' => 'string',

            'bolProductShortDescription' => 'nullable|string',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolBeActive' => 'nullable|in:0,1',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolNlActive' => 'nullable|in:0,1',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolNLDeliveryTime' => 'nullable|string',
            'bin_location_id' => 'required|string',

        ]);

        $customFields = $this->setCustomFieldd($validatedData);

        // Generate a UUID for the new product
        $productVariantId = str_replace('-', '', (string) Str::uuid());
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
                    'manufacturerId' => $validatedData['manufacturer'],
                    'taxId' => $validatedData['taxId'],
                    'parentId' => $validatedData['parentId'],
                    'productNumber' => $validatedData['productNumber'],
                    'description' => $validatedData['description'],
                    'weight' => $weight,
                    'width' => $width,
                    'height' => $height,
                    'ean' => $validatedData['productEanNumber'],
                    'length' => $length,
                    'markAsTopseller' => true, // to make on sale default true
                    'price' => [
                        [
                            'currencyId' => $currencyId,
                            'gross' => floatval($validatedData['priceGross']),
                            'net' => floatval($validatedData['priceNet']),
                            'linked' => true
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
                    // set custom fields data for created product
                    $this->patchProductCustomData($productVariantId, $customFields);

                    // set stock to bin location
                    $stockData = [
                        'product_id' => $productVariantId,
                        'stock' => intval($validatedData['stock'])
                    ];
                    $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);
                    return response()->json([
                        'message' => __('product.product_created_successfully')
                    ]);
                } else {
                    Log::error('Product variant creation Error: ' . json_encode($response));
                    return response()->json(['errors' => __('product.failed_to_update_product')], 400);
                }
            } else {
                Log::error('Product variant creation Error: ' . json_encode($responseParent));
                return response()->json(['errors' => __('product.failed_to_update_product')], 400);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['errors' => "Er is iets fout gegaan!"], 400);
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
            'salesChannelBol.*' => 'required|string',
            'bolTaxId' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'active_for_allBol' => 'nullable|boolean',

            'bolProductShortDescription' => 'nullable|string',
            'bolNlPrice' => 'nullable|numeric',
            'bolBePrice' => 'nullable|numeric',
            'bolBeActive' => 'nullable|in:0,1',
            'bolCondition' => 'nullable|string',
            'bolConditionDescription' => 'nullable|string',
            'bolNlActive' => 'nullable|in:0,1',
            'bolOrderBeforeTomorrow' => 'nullable|in:0,1',
            'bolOrderBefore' => 'nullable|in:0,1',
            'bolLetterboxPackage' => 'nullable|in:0,1',
            'bolLetterboxPackageUp' => 'nullable|in:0,1',
            'bolPickUpOnly' => 'nullable|in:0,1',
            'bolBEDeliveryTime' => 'nullable|string',
            'bolNLDeliveryTime' => 'nullable|string',
            'bin_location_id' => 'required|string',
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
            'markAsTopseller' => true, // to make on sale default true
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
            ]
        ];
        try {
            // Make the API request to create the product
            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/product', $data);

            if (isset($response['success'])) {

                // set custom fields data for created product
                $this->patchProductCustomData($uuid, $customFields);

                // set stock to bin location
                $stockData = [
                    'product_id' => $uuid,
                    'stock' => (int)$validatedData['bolStock']
                ];
                $this->setBinLocationStock($stockData, $validatedData['bin_location_id']);

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

        // Step 4: Store File in Public Storage
        $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
        $filePath = $uploadedFile->storeAs('public/media', $fileName);

        // Get public URL of the stored file
        $imageUrl = asset("storage/media/{$fileName}");

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

    public function patchProductCustomData($productVariantId, $customFields)
    {
        $data = [
            'id' => $productVariantId,
            'customFields' => $customFields
        ];
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
            "migration_DMG_product_bol_be_active" => $validatedData['bolBeActive'] ? true : false,
            "migration_DMG_product_bol_condition" => $validatedData['bolCondition'] ?? null,
            "migration_DMG_product_bol_condition_desc" => $validatedData['bolConditionDescription'] ?? null,
            "migration_DMG_product_bol_nl_active" => $validatedData['bolNlActive'] ? true : false,
            "migration_DMG_product_proposition_1" => $validatedData['bolOrderBeforeTomorrow'] ? true : false,
            "migration_DMG_product_proposition_2" => $validatedData['bolOrderBefore'] ? true : false,
            "migration_DMG_product_proposition_3" => $validatedData['bolLetterboxPackage'] ? true : false,
            "migration_DMG_product_proposition_4" => $validatedData['bolLetterboxPackageUp'] ? true : false,
            "migration_DMG_product_proposition_5" => $validatedData['bolPickUpOnly'] ? true : false,
            "migration_DMG_product_bol_be_delivery_code" => $validatedData['bolBEDeliveryTime'] ?? null,
            "migration_DMG_product_bol_nl_delivery_code" => $validatedData['bolNLDeliveryTime'] ?? null
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
            'limit' => 25,             // You can adjust this limit if needed
            'term' => $request->get('term', ''),
            'associations' => [
                'warehouse' => [
                    'total-count-mode' => 1
                ]
            ],
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        if ($request->get('warehouseId')) {
            $data['filter'] = [
                [
                    'type' => 'equals',
                    'field' => 'warehouse.id',
                    'value' => $request->get('warehouseId')
                ]
            ];
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
}
