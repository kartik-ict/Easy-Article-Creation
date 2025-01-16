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
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json(['product' => $product], 200);
    }

    public function create()
    {
        return view('backend.pages.product.create');
    }
}