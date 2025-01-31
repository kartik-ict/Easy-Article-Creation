<?php

namespace App\Http\Controllers\Backend;

use App\Services\ShopwareAuthService;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class ManufacturerController extends Controller
{
    private $shopwareApiService;

    public function __construct(ShopwareAuthService $shopwareApiService)
    {
        $this->shopwareApiService = $shopwareApiService;
    }

    // Get all manufacturers
    public function index(Request $request)
    {
        return view('backend.pages.manufacturers.index');
    }

    public function getAjaxData(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            $queryParams = http_build_query([
                    'limit' => $limit,
                'page' => $page,
            ]);

            // Fetch paginated manufacturers
            $response = $this->shopwareApiService->makeApiRequest('GET', "/api/product-manufacturer?$queryParams");

            if (isset($response['data'])) {
                $manufacturers = $response['data'];
                $totalCount = $response['meta']['total'] ?? count($manufacturers);
                // Format the manufacturers to include actions (Edit and Delete buttons)
                $formattedManufacturers = collect($manufacturers)->map(function ($manufacturer) {
                    return [
                        'id' => $manufacturer['id'],
                        'name' => $manufacturer['attributes']['translated']['name'], // Access the name under 'attributes.translated.name'
                    ];
                });

                return response()->json([
                    'draw' => $request->input('draw', 1), // for DataTables (helps with pagination state)
                    'recordsTotal' => 17752,
                    'recordsFiltered' => 17752,
                    'data' => $formattedManufacturers
                ]);
            }
            // If no data is returned
            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        } catch (\Exception $e) {
            // Handle errors gracefully
            return response()->json([
                'error' => 'Something went wrong!',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Create a manufacturer
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255', // Name is required
            'link' => 'nullable|url|max:255', // Link is optional
            'description' => 'nullable|string', // Description is optional
        ]);

        // Extract the validated fields
        $data = $validatedData;
        $response = $this->shopwareApiService->makeApiRequest('POST', '/api/product-manufacturer', $data);

        if ($response['success']) {
            return redirect()->route('admin.manufacturers.index')->with('success', 'Fabrikant met succes aangemaakt.');
        } else {
            return redirect()->back()->with('error', 'Fabrikant niet bijgewerkt.');
        }
    }

    public function create()
    {
        return view('backend.pages.manufacturers.create');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'nullable|url|max:255',
        ]);

        $data = $validatedData;
        $data = $request->only(['name', 'link', 'description']);

        $response = $this->shopwareApiService->makeApiRequest('PATCH', "/api/product-manufacturer/{$id}", $data);

        if ($response['success']) {
            return redirect()->route('admin.manufacturers.index')->with('success', 'Fabrikant succesvol bijgewerkt.');
        } else {
            return redirect()->back()->with('error', 'Fabrikant niet bijgewerkt.');
        }
    }


    public function edit($id)
    {
        $endpoint = "/api/product-manufacturer/{$id}";

        // Call the API using shopwareApiService
        $response = $this->shopwareApiService->makeApiRequest('GET', $endpoint);

        if ($response && isset($response['data'])) {
            $manufacturer = $response['data'];

            // Pass the manufacturer data to the Blade view
            return view('backend.pages.manufacturers.edit', compact('manufacturer'));
        } else {
            // Handle error scenarios gracefully
            return redirect()->route('admin.manufacturers.index')
                ->with('error', 'Fout bij het ophalen van fabrikantgegevens van de API.');
        }
    }

    public function destroy($id)
    {
        $response = $this->shopwareApiService->makeApiRequest('DELETE', "/api/product-manufacturer/{$id}");

        // Handle the API response
        if ($response['success']) {
            return redirect()->route('admin.manufacturers.index')->with('success', 'Fabrikant succesvol verwijderd!');
        } else {
            return redirect()->back()->with('error', 'Fabrikant niet bijgewerkt.');
        }
    }

    public function swSearch(Request $request)
    {
        $request->validate([
            'productManufacturer' => 'required|string',
        ]);

        $productManufacturer = $request->input('productManufacturer');
        $payload = [
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'name',
                    'value' => $productManufacturer,
                ]
            ],
            'inheritance' => true,
            'total-count-mode' => 1,
        ];

        // Make API request using the common function
        $productManufacturer = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product-manufacturer', $payload);
        if ($productManufacturer) {
//            dd($productManufacturer['data']);
            return response()->json(['productManufacturer' => $productManufacturer['data']], 200);
        }
    }
}
