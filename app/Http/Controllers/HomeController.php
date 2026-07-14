<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use TouchEstate\Sdk\TouchEstateClient;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

    public function index()
    {
        // Shell: static config-based stats only — no API call, returns instantly.
        // Dynamic sections (popular listings, property types) are loaded async via data().
        $stats = [
            'propertiesListed' => 0,
            'happyClients'     => 0,
            'citiesCovered'    => 0,
            'satisfactionRate' => 98,
            'successfulDeals'  => (int) config('site.stats.deals', 1000),
            'activeProperties' => (int) config('site.stats.active', 200),
        ];

        // Empty placeholders — filled async by JS after data() responds
        $rentProperties = [];
        $saleProperties = [];
        $typeCounts     = [];
        $typeImages     = [];
        $cityImages     = [];
        $cityCounts     = [];
        $topViewedImages = [];
        $availableTypes = [];

        return view('index', compact(
            'rentProperties', 'saleProperties', 'stats',
            'typeCounts', 'cityCounts', 'topViewedImages', 'cityImages', 'typeImages',
            'availableTypes',
        ));
    }

    public function data()
    {
        $allItems = [];
        try {
            $allItems = Cache::remember('te_home_100', 3600, function () {
                return $this->client->properties()->list([
                    'pageSize'  => 100,
                    'sortBy'    => 'viewCount',
                    'sortOrder' => 'desc',
                    'status'    => 'Active',
                ])['items'] ?? [];
            });
        } catch (\Throwable) {}

        $rentProperties = array_values(array_slice(
            array_filter($allItems, fn($p) => strtolower($p['transactionType'] ?? '') === 'rent'),
            0, 6
        ));
        $saleProperties = array_values(array_slice(
            array_filter($allItems, fn($p) => strtolower($p['transactionType'] ?? '') === 'sale'),
            0, 6
        ));

        $typeCounts = ['Apartment' => 0, 'House' => 0, 'Office' => 0, 'Villa' => 0];
        $typeImages = ['Apartment' => [], 'House' => [], 'Office' => [], 'Villa' => []];
        foreach ($allItems as $p) {
            $type = $p['propertyType'] ?? '';
            if (array_key_exists($type, $typeCounts)) {
                $typeCounts[$type]++;
                if (!empty($p['primaryImageUrl']) && count($typeImages[$type]) < 3) {
                    $typeImages[$type][] = $p['primaryImageUrl'];
                }
            }
        }

        $availableTypes = array_values(array_unique(array_filter(
            array_map(fn ($p) => $p['propertyType'] ?? '', $allItems)
        )));
        sort($availableTypes);

        $locale = app()->getLocale();

        $popularHtml = view('partials.home-popular', compact(
            'rentProperties', 'saleProperties', 'locale'
        ))->render();

        $typesHtml = view('partials.home-types', compact(
            'typeCounts', 'typeImages', 'availableTypes', 'locale'
        ))->render();

        return response()->json([
            'popularHtml' => $popularHtml,
            'typesHtml'   => $typesHtml,
        ]);
    }
}
