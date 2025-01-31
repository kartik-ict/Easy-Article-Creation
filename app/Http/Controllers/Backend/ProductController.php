<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Services\CurrencyService;
use App\Http\Controllers\Controller;
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

    private $client;

    public function __construct(ShopwareProductService $shopwareProductService, ShopwareAuthService $shopwareApiService, CurrencyService $currencyId)
    {
        $this->shopwareProductService = $shopwareProductService;
        $this->shopwareApiService = $shopwareApiService;
        $this->currencyId = $currencyId;
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
            ],
            'inheritance' => true,
            'total-count-mode' => 1,
        ];

        // Make API request using the common function
        $product = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product?inheritance=true', $payload);

        if (!$product['data']) {

//            $apiKey = '7a507de2-fc1d-4eaf-88ff-f1401d2c155b';
//            $site = 'bol.com';

//            $fallbackUrl = "https://api.shoppingscraper.com/info";

//            $client = new Client();

            try {
//                $response = $client->request('GET', $fallbackUrl, [
//                    'query' => [
//                        'site' => $site,
//                        'ean' => $ean,
//                        'api_key' => $apiKey
//                    ]
//                ]);
//
//                $product = json_decode($response->getBody(), true);

                $product = [
                    "results" => [
                        [
                            "ean" => null,
                            "sku" => "9200000127656703",
                            "url" => "https://www.bol.com/nl/nl/p/rRIB/9200000127656703/",
                            "title" => "Groeten uit Den Haag",
                            "brand" => null,
                            "thumbnail" => "https://media.s-bol.com/qrBOVwrOm9Vy/ADX7xyj/1200x1200.jpg",
                            "categories" => [
                                "Boeken",
                                "Kunst & Fotografie",
                                "Fotografie"
                            ],
                            "description" => "Groeten uit Den Haag: Honderd jaar veranderingen in de stad Je staat op dezelfde plek, maar in een andere tijd. Iedereen kent de vervreemdende sensatie van het kijken naar oude foto’s. Het verleden is verdwenen, maar nog merkbaar in de details. Robert Mulder zocht honderd oude foto’s van de stad en maakte honderd recente foto’s op exact dezelfde plek. In één oogopslag zie je de veranderingen in beeld. De beeldbepalende ministeries, het verschil tussen zand en veen en de nabijheid van de zee hebben het aangezicht van de stad bepaald. Groeten uit Den Haag laat zien wat er verdwenen en verschenen is in de stad. Door de oude en nieuwe foto’s in groot formaat naast elkaar te zetten, blijf je gefascineerd kijken naar de veranderingen. De ene keer valt de vergelijking uit in het voordeel van het verleden, de andere keer in het voordeel van het heden. De vooruitgang gaat soms gepaard met een dosis weemoed.",
                            "specs" => [
                                "Taal" => "Nederlands",
                                "Bindwijze" => "Hardcover",
                                "Oorspronkelijke releasedatum" => "15 mei 2020",
                                "Aantal pagina's" => "216",
                                "Illustraties" => "Met illustraties",
                                "Hoofdauteur" => "Robert Mulder",
                                "Hoofduitgeverij" => "Uitgeverij Kleine Uil",
                                "Editie" => "1",
                                "Product breedte" => "295 mm",
                                "Product hoogte" => "21 mm",
                                "Product lengte" => "297 mm",
                                "Studieboek" => "Nee",
                                "Verpakking breedte" => "296 mm",
                                "Verpakking hoogte" => "21 mm",
                                "Verpakking lengte" => "298 mm",
                                "Verpakkingsgewicht" => "1722 g",
                                "EAN" => "9789493170179",
                                "Verantwoordelijk marktdeelnemer in de EU" => "Bekijk gegevens",
                                "Categorieën" => "Kunst & FotografieFotografieBoeken",
                                "Boek, ebook of luisterboek?" => "Boek",
                                "Select-bezorgopties" => "Vandaag Bezorgd, Avondbezorging, Zondagbezorging, Gratis verzending",
                                "Studieboek of algemeen" => "Algemene boeken"
                            ]
                        ]
                    ]
                ];


                $productData = [
                    'name' => $product['results']['0']['title'],
                    'ean' => $product['results']['0']['ean'] ?? $ean,
                    'stock' => 0,
                    'id' => '',
                    'productData' => $product['results'],
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
            $productData = [
                'name' => $product['data']['0']['attributes']['translated']['name'],
                'ean' => $product['data']['0']['attributes']['ean'] ?? $ean,
                'stock' => $product['data']['0']['attributes']['stock'],
                'id' => $product['data']['0']['id'],
                'productData' => $product['data'],
                'included' => $product['included'],
                'bol' => false
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
            if (isset($response['data'])) {
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
            'limit' => 25,             // You can adjust this limit if needed
//            'term' => $request->get('term', ''),
            'total-count-mode' => 1    // Flag to include the total count in the response
        ];

        // Make the API request using the common function
        $response =  $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group-option', $data);

        // Return the data in the required format for select2
        return response()->json([
            'propertyGroups' => $response['data'] ?? [], // Manufacturer data
            'total' => $response['total'] ?? 0,          // Total manufacturers available
        ]);
    }
}
