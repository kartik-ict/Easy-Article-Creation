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
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $shopwareProductService;
    private $shopwareApiService;
    private $currencyId;
    private $taxId;

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

    public function index()
    {
        return view('backend.pages.product.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'ean' => 'required|string',
        ]);

        $ean = $request->input('ean');
        $payload = [
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'ean',
                    'value' => $ean,
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

                $responsePrice = $client->request('GET', $fallbackUrlPrice, [
                    'query' => [
                        'site' => $site,
                        'ean' => $product['results']['0']['ean'] ?? $ean,
                        'api_key' => $apiKey
                    ]
                ]);

                $productPrice = json_decode($responsePrice->getBody(), true);
                /*                Dayanamic product data*/
//                $product = [
//                    "results" => [
//                        [
//                            "ean" => '9789493170179',
//                            "sku" => "9200000127656703",
//                            "url" => "https://www.bol.com/nl/nl/p/rRIB/9200000127656703/",
//                            "title" => "Groeten uit Den Haag",
//                            "brand" => 'testdemobrand123',
//                            "thumbnail" => "https://media.s-bol.com/qrBOVwrOm9Vy/ADX7xyj/1200x1200.jpg",
//                            "categories" => [
//                                "Boeken",
//                                "Kunst & Fotografie",
//                                "test123"
//                            ],
//                            "description" => "Groeten uit Den Haag: Honderd jaar veranderingen in de stad Je staat op dezelfde plek, maar in een andere tijd. Iedereen kent de vervreemdende sensatie van het kijken naar oude foto’s. Het verleden is verdwenen, maar nog merkbaar in de details. Robert Mulder zocht honderd oude foto’s van de stad en maakte honderd recente foto’s op exact dezelfde plek. In één oogopslag zie je de veranderingen in beeld. De beeldbepalende ministeries, het verschil tussen zand en veen en de nabijheid van de zee hebben het aangezicht van de stad bepaald. Groeten uit Den Haag laat zien wat er verdwenen en verschenen is in de stad. Door de oude en nieuwe foto’s in groot formaat naast elkaar te zetten, blijf je gefascineerd kijken naar de veranderingen. De ene keer valt de vergelijking uit in het voordeel van het verleden, de andere keer in het voordeel van het heden. De vooruitgang gaat soms gepaard met een dosis weemoed.",
//                            "specs" => [
//                                "Taal" => "Nederlands",
//                                "Bindwijze" => "Hardcover",
//                                "Oorspronkelijke releasedatum" => "15 mei 2020",
//                                "Aantal pagina's" => "216",
//                                "Illustraties" => "Met illustraties",
//                                "Hoofdauteur" => "Robert Mulder",
//                                "Hoofduitgeverij" => "Uitgeverij Kleine Uil",
//                                "Editie" => "1",
//                                "Product breedte" => "295 mm",
//                                "Product hoogte" => "21 mm",
//                                "Product lengte" => "297 mm",
//                                "Studieboek" => "Nee",
//                                "Verpakking breedte" => "296 mm",
//                                "Verpakking hoogte" => "21 mm",
//                                "Verpakking lengte" => "298 mm",
//                                "Verpakkingsgewicht" => "1722 g",
//                                "EAN" => "9789493170179",
//                                "Verantwoordelijk marktdeelnemer in de EU" => "Bekijk gegevens",
//                                "Categorieën" => "Kunst & FotografieFotografieBoeken",
//                                "Boek, ebook of luisterboek?" => "Boek",
//                                "Select-bezorgopties" => "Vandaag Bezorgd, Avondbezorging, Zondagbezorging, Gratis verzending",
//                                "Studieboek of algemeen" => "Algemene boeken"
//                            ]
//                        ]
//                    ]
//                ];
//
//                $productPrice = [
//                    "results" => [
//                        [
//                            "ean" => "9789493170179",
//                            "sku" => "9200000127656703",
//                            "url" => "https://www.bol.com/nl/nl/p/groeten-uit-den-haag/9200000127656703/",
//                            "title" => "Groeten uit Den Haag",
//                            "thumbnail" => "https://media.s-bol.com/qrBOVwrOm9Vy/ADX7xyj/124x124.jpg",
//                            "availability" => "InStock",
//                            "currency" => "EUR",
//                            "offers" => [
//                                [
//                                    "sellerName" => "Bol",
//                                    "sellerReference" => "/nl/order/basket/addItems.html?productId=9200000127656703&offerUid=9185b50f-6970-47e0-88da-959cf4ba6418&quantity=1",
//                                    "price" => "29.95",
//                                    "shippingPrice" => "0.00",
//                                    "totalPrice" => "29.95",
//                                    "condition" => "Nieuw",
//                                    "shippingMethod" => "standard"
//                                ],
//                                [
//                                    "sellerName" => "Paagman.nl",
//                                    "sellerReference" => "/nl/nl/v/paagman-nl/1092159/?sellingOfferId=151422169b716fc6de9d017ff30a54c4",
//                                    "price" => "34.90",
//                                    "shippingPrice" => "0.00",
//                                    "totalPrice" => "34.90",
//                                    "condition" => "Nieuw",
//                                    "shippingMethod" => "standard"
//                                ]
//                            ]
//                        ]
//                    ]
//                ];


                $productData = [
                    'name' => $product['results']['0']['title'],
                    'ean' => $product['results']['0']['ean'] ?? $ean,
                    'stock' => 0,
                    'id' => '',
                    'productData' => $product['results'],
                    'productPriceData' => $productPrice['results'],
                    'taxData' => $this->taxDetail->getTaxDetail(),
                    'included' => '',
                    'bol' => true
                ];

                return response()->json(['product' => $productData], 200);

            } catch (RequestException $e) {
//                Log::error('Failed to fetch product data from fallback API', [
//                    'url' => $fallbackUrl,
//                    'error' => $e->getMessage()
//                ]);

                return response()->json(['error' => 'Product not found'], 404);
            }

//            return response()->json([
//                'error' => __('messages.product_not_found'),
//            ], 404);
        } else {

            $optionsIds = null;
            if ($product['data'][0]['attributes']['parentId'] == null) {
                $productId = $product['data'][0]['id'];
                $parentProduct = $this->shopwareApiService->makeApiRequest('GET', "/api/product/?filter[parentId]=$productId&associations[configuratorSettings][associations][option]=[]");
                $optionsIds = $parentProduct['data']['0']['attributes']['optionIds'];
            }

            $productData = [
                'name' => $product['data']['0']['attributes']['translated']['name'],
                'ean' => $product['data']['0']['attributes']['ean'] ?? $ean,
                'stock' => $product['data']['0']['attributes']['stock'],
                'id' => $product['data']['0']['id'],
                'productData' => $product['data'],
                'included' => $product['included'],
                'bol' => false,
                'optionsIds' => $optionsIds
            ];

            return response()->json(['product' => $productData], 200);
        }
    }

    public function create()
    {
        return view('backend.pages.product.create');
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
            'active_for_all' => 'nullable|boolean'
        ]);

        // Generate a UUID for the new product
        $uuid = str_replace('-', '', (string)\Str::uuid());

        // Prepare visibilities
        $visibilities = [];
        foreach ($validatedData['salesChannel'] as $salesChannelId) {
            $visibilityId = str_replace('-', '', (string)\Str::uuid()); // Generate a random ID
            $visibilities[] = [
                'id' => $visibilityId,
                'productId' => $uuid,
                'salesChannelId' => $salesChannelId,
                'visibility' => 30 // Default visibility
            ];
        }

        // Prepare categories
        $categories = [];
        foreach ($validatedData['category'] as $categoryId) {
            $categories[] = ['id' => $categoryId];
        }

        // Prepare the data for the API request
        $data = [
            'id' => $uuid,
            'name' => $validatedData['name'],
            'stock' => intval($validatedData['stock']),
            'manufacturerId' => $validatedData['manufacturer'],
            'taxId' => $validatedData['taxId'],
            'productNumber' => $validatedData['productNumber'],
            'description' => $validatedData['description'],
            'ean' => $validatedData['ean'],
            'categories' => $categories,
            'visibilities' => $visibilities,
            'mediaUrl' => $validatedData['mediaUrl'],
            'active' => boolval($validatedData['active_for_all']),
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
                return redirect()->route('product.index')->with('success', __('product.product_created_successfully'));
            } else {
                return back()->withErrors(__('product.failed_to_create_product'));
            }
        } catch (\Exception $e) {
            // Log and return error
            \Log::error('Error creating product: ' . $e->getMessage());
            return back()->withErrors(__('product.failed_to_create_product'));
        }
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string|regex:/^[0-9a-f]{32}$/',
            'new_stock' => 'required|integer|min:0'
        ]);

        $data = [
            'id' => $request->product_id,
            'stock' => intval($request->new_stock)
        ];
        // Using common API call function
        try {
            $response = $this->shopwareApiService->makeApiRequest(
                'PATCH',
                '/api/product/' . $request->product_id,
                $data
            );

            return response()->json(['success' => true, 'response' => $response]);
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
//            'term' => $request->get('term', ''),
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
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'groupId',
                    'value' => $request->get('groupId', ''),
                ]
            ],
            'term' => $request->get('term', ''),
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
        $uuid = str_replace('-', '', (string)\Str::uuid());
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
        ]);

        // Generate a UUID for the new product
        $uuid = str_replace('-', '', (string)\Str::uuid());

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
                } catch (\Exception $e) {
                    return back()->withErrors(__('product.failed_to_update_product'));
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
                    'id' => $uuid,
                    'name' => $validatedData['name'],
                    'stock' => intval($validatedData['stock']),
                    'manufacturerId' => $validatedData['manufacturer'],
                    'taxId' => $validatedData['taxId'],
                    'parentId' => $validatedData['parentId'],
                    'productNumber' => $validatedData['productNumber'],
                    'description' => $validatedData['description'],
                    'weight' => $weight,
                    'width' => $width,
                    'height' => $height,
                    'length' => $length,
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
                    ]
                ];

                $childEndpoint = "/api/product";
                $response = $this->shopwareApiService->makeApiRequest('POST', $childEndpoint, $data);

                if (isset($response['success'])) {
                    return response()->json([
                        'message' => __('product.product_created_successfully')
                    ]);
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
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
            'bolPackagingHeight' => 'string',
            'bolPackagingLength' => 'string',
            'bolPackagingWeight' => 'string',
            'bolProductPrice' => 'string',
            'bolTotalPrice' => 'string',
            'bolProductThumbnail' => 'string',
        ]);
        $uuid = str_replace('-', '', (string)\Str::uuid());
        $mediaId = str_replace('-', '', (string)\Str::uuid());

        $weight = strstr($validatedData['bolPackagingWeight'], ' ', true); // Output: "1722"
        $width = strstr($validatedData['bolPackagingWidth'], ' ', true);  // Output: "296"
        $height = strstr($validatedData['bolPackagingHeight'], ' ', true);  // Output: "21"
        $length = strstr($validatedData['bolPackagingLength'], ' ', true); // Output: "298"

        $imageUrl = $validatedData['bolProductThumbnail'];
        $fileName = 'test';

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


        $data = [
            'id' => $uuid,
            'name' => $validatedData['bolProductName'],
            'manufacturerId' => $validatedData['bolProductManufacturerId'],
            'productNumber' => $validatedData['bolProductSku'],
            'description' => $validatedData['bolProductDescription'],
            'ean' => $validatedData['bolProductEanNumber'],
            'coverId' => $mediaId,
            'categories' => array_map(fn($categoryId) => ['id' => trim($categoryId)], explode(',', $validatedData['bolProductCategoriesId'])), // Fix here
            'stock' => 0,
            'taxId' => $this->taxId->getTaxId(),
            'weight' => $weight,
            'width' => $width,
            'height' => $height,
            'length' => $length,
            'price' => [
                [
                    'currencyId' => $currencyId,
                    'gross' => floatval($validatedData['bolProductPrice']),
                    'net' => floatval($validatedData['bolTotalPrice']),
                    'linked' => true
                ]
            ]
        ];

        try {
            // Make the API request to create the product
            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/product', $data);

            // If the API call is successful
            if ($response['success'] === true) {
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
}

