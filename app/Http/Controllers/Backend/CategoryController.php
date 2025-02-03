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

class CategoryController extends Controller
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

    public function swSearch(Request $request)
    {
        $request->validate([
            'lastCategory' => 'required|string',
        ]);

        $productCategory = $request->input('lastCategory');
        $payload = [
            'filter' => [
                [
                    'type' => 'contains',
                    'field' => 'name',
                    'value' => $productCategory,
                ]
            ],
            'inheritance' => true,
            'total-count-mode' => 1,
        ];

        // Make API request using the common function
        $productCategory = $this->shopwareApiService->makeApiRequest('POST', '/api/search/category', $payload);
        if ($productCategory && count($productCategory['data']) > 0) {
            return response()->json(['productCategory' => $productCategory['data']], 200);
        } else {

            return response()->json([
                'productCategory' => [],
                'message' => 'createNew'
            ], 200);
        }
    }

    public function create()
    {
        return view('backend.pages.product.create');
    }

    public function createCategory(Request $request)
    {

        $request->validate([
            'lastCategory' => 'required|string',
            'parentCategoriesValue' => 'required|string',
        ]);

        // Generate a UUID for the new product
        $uuid = str_replace('-', '', (string)\Str::uuid());
        $categoryName = $request->input('lastCategory');
        $parentCategoriesValue = $request->input('parentCategoriesValue');
        // Prepare the data for the API request
        $data = [
            'id' => $uuid,
            'name' => $categoryName,
            'parentId' => $parentCategoriesValue
        ];

        try {
            // Make the API request to create the product
            $response = $this->shopwareApiService->makeApiRequest('POST', '/api/category', $data);
            // If the API call is successful

//            if ($response['success'] === true) {


                $payload = [
                    'filter' => [
                        [
                            'type' => 'contains',
                            'field' => 'name',
                            'value' => $categoryName,
                        ]
                    ],
                    'inheritance' => true,
                    'total-count-mode' => 1,
                ];

                // Make API request using the common function
                $productCategory = $this->shopwareApiService->makeApiRequest('POST', '/api/search/category', $payload);
                if ($productCategory) {
                    return response()->json(['productCategory' => $productCategory['data']], 200);
                }
//            }
        } catch (\Exception $e) {

        }
    }
}
