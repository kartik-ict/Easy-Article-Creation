<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShopwareProductService;

class ProductController extends Controller
{
    protected $shopwareProductService;

    public function __construct(ShopwareProductService $shopwareProductService)
    {
        $this->shopwareProductService = $shopwareProductService;
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
        $product = $this->shopwareProductService->searchByEAN($ean);

        if (!$product) {
            return response()->json([
                'error' => __('messages.product_not_found'),
            ], 404);
        }

        return response()->json(['product' => $product], 200);
    }

    public function create()
    {
        return view('backend.pages.product.create');
    }

    public function manufacturerSearch(Request $request)
    {
        $searchTerm = $request->input('search', null); // Optional search parameter

        $manufacturers = $this->shopwareProductService->searchManufacturer($searchTerm);

        if ($manufacturers) {
            return response()->json([
                'error' => __('messages.no_manufacturers_found'),
            ], 404);
        }

        return response()->json(['manufacturers' => $manufacturers->map(function ($manufacturer) {
            return [
                'id' => $manufacturer->getId(),
                'name' => $manufacturer->getName(),
            ];
        })], 200);
    }
}
