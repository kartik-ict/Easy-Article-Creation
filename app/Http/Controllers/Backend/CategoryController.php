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

}
