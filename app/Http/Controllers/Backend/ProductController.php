<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShopwareProductService;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $shopwareProductService;
    private $shopwareApiService;

    public function __construct(ShopwareProductService $shopwareProductService, ShopwareAuthService $shopwareApiService)
    {
        $this->shopwareProductService = $shopwareProductService;
        $this->shopwareApiService = $shopwareApiService;
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
                    'field' => 'productNumber',
                    'value' => $ean,
                ]
            ],
            'limit' => 1,
        ];

        // Make API request using the common function
        $product = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product', $payload);

        if (!$product['data']) {
            return response()->json([
                'error' => __('messages.product_not_found'),
            ], 404);
        }

        $productData = [
           'name' => $product['data']['0']['attributes']['translated']['name'],
           'productNumber' => $product['data']['0']['attributes']['productNumber'],
        ] ;

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
        // Validate the incoming data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'manufacturer' => 'required|integer',
            'taxId' => 'required|integer',
            'productNumber' => 'required|string|max:255',
            'description' => 'nullable|string',
            'salesChannel' => 'required|integer',
            'category' => 'required|integer',
            'mediaUrl' => 'nullable|url',
        ]);

        $uuid = (string) \Str::uuid();
        // Prepare the data for the API request
        $data = [
            'id' => $uuid,  // Set the generated UUID here
            'name' => $validatedData['name'],
            'stock' => $validatedData['stock'],
            'manufacturer' => $validatedData['manufacturer'],
            'taxId' => $validatedData['taxId'],
            'productNumber' => $validatedData['productNumber'],
            'description' => $validatedData['description'],
            'salesChannel' => $validatedData['salesChannel'],
            'category' => $validatedData['category'],
            'mediaUrl' => $validatedData['mediaUrl'],
        ];

        try {
            // Make the API request to create the product
            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/products', $data);

            // If the API call is successful
            if (isset($response['data'])) {
                // Return a success message or redirect the user
                return redirect()->route('product.index')->with('success', __('product.product_created_successfully'));
            } else {
                return back()->withErrors(__('product.failed_to_create_product'));
            }
        } catch (\Exception $e) {
            // Handle any errors from the API
            \Log::error('Error creating product: ' . $e->getMessage());
            return back()->withErrors(__('product.failed_to_create_product'));
        }
    }



}
