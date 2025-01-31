<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Services\CurrencyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShopwareProductService;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $shopwareProductService;
    private $shopwareApiService;
    private $currencyId;

    public function __construct(ShopwareProductService $shopwareProductService, ShopwareAuthService $shopwareApiService, CurrencyService $currencyId)
    {
        $this->shopwareProductService = $shopwareProductService;
        $this->shopwareApiService = $shopwareApiService;
        $this->currencyId = $currencyId;
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
            return response()->json([
                'error' => __('messages.product_not_found'),
            ], 404);
        }
        $productData = [
            'name' => $product['data']['0']['attributes']['translated']['name'],
            'ean' => $product['data']['0']['attributes']['ean'],
            'stock' => $product['data']['0']['attributes']['stock'],
            'id' => $product['data']['0']['id'],
            'productData' => $product['data'],
            'included' => $product['included'],
        ];

        return response()->json(['product' => $productData], 200);
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
        $response =  $this->shopwareApiService->makeApiRequest('POST', '/api/search/product-manufacturer', $data);

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
        $uuid = str_replace('-', '', (string) \Str::uuid());

        // Prepare visibilities
        $visibilities = [];
        foreach ($validatedData['salesChannel'] as $salesChannelId) {
            $visibilityId = str_replace('-', '', (string) \Str::uuid()); // Generate a random ID
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
        $response =  $this->shopwareApiService->makeApiRequest('GET', '/api/property-group', $data);

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
        $response =  $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group-option', $data);

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
        $uuid = str_replace('-', '', (string) \Str::uuid());
        $payload = [
            'id' => $uuid,
            'groupId' => $validatedData['groupId'],
            'name' => $validatedData['optionName'],
        ];

        $response =  $this->shopwareApiService->makeApiRequest('POST', '/api/property-group-option', $payload);
        if ($response['success']){
            return response()->json([
                'message' => __('product.property_option_saved_successfully')
            ]);
        }
    }



}
