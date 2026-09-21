<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BolImageImportBatch;
use App\Models\BolOfferMapping;
use App\Services\BolOfferImportService;
use Illuminate\Http\Request;

/**
 * Bol circular-pilot image import. Read/write against Bol's Retailer API only — never touches
 * Shopware's product data directly, and Shopware/Channable stay untouched by this feature.
 */
class BolImageImportController extends Controller
{
    public function __construct(private BolOfferImportService $bolOfferImportService)
    {
    }

    public function index(Request $request)
    {
        return view('backend.pages.bol-image-import.index');
    }

    /**
     * AJAX: pull Bol's offer export and refresh bol_offer_mappings from it.
     */
    public function syncOfferMappings(Request $request)
    {
        $result = $this->bolOfferImportService->syncOfferMappings();

        if ($result['error']) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['synced' => $result['synced']]);
    }

    /**
     * AJAX: look up a product's known offer mapping + import history, by product number.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'product_number' => ['required', 'string', 'max:255'],
        ]);

        $mapping = BolOfferMapping::where('product_number', $validated['product_number'])->first();
        $batches = BolImageImportBatch::where('product_number', $validated['product_number'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'mapping' => $mapping,
            'batches' => $batches,
        ]);
    }

    /**
     * AJAX: upload a photo file for a product's used-unit condition photos, stored locally and
     * served back as a public URL — the same URL shape `pushImages()` expects. Kept alongside
     * plain URL entry (not a replacement) since a photo may already be hosted elsewhere.
     */
    public function uploadPhoto(Request $request)
    {
        $validated = $request->validate([
            'product_number' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ]);

        $productNumber = preg_replace('/[^a-zA-Z0-9_-]/', '_', $validated['product_number']);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $request->file('photo')->getClientOriginalName());
        $request->file('photo')->storeAs("public/bol-images/{$productNumber}", $fileName);

        return response()->json([
            'url' => asset("storage/bol-images/{$productNumber}/{$fileName}"),
        ]);
    }

    /**
     * AJAX: submit (replace) the full photo set for a product's Bol offer. Expects an ordered
     * list of image URLs — first is treated as the primary photo.
     */
    public function pushImages(Request $request)
    {
        $validated = $request->validate([
            'product_number' => ['required', 'string', 'max:255'],
            'image_urls' => ['required', 'array', 'min:1', 'max:10'],
            'image_urls.*' => ['required', 'url'],
        ]);

        $result = $this->bolOfferImportService->submitImageBatch($validated['product_number'], $validated['image_urls']);

        if ($result['error']) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json(['batch' => $result['batch']]);
    }

    /**
     * AJAX: manual "check status now" for a specific batch (the scheduled console command also
     * does this automatically for anything still PENDING).
     */
    public function checkBatchStatus(Request $request, int $batchId)
    {
        $batch = BolImageImportBatch::findOrFail($batchId);
        $batch = $this->bolOfferImportService->pollBatchStatus($batch);

        return response()->json(['batch' => $batch]);
    }
}
