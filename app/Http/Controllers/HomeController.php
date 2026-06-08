<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use TouchEstate\Sdk\TouchEstateClient;

class HomeController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

    public function index()
    {
        $allItems   = [];
        $stats      = ['propertiesListed' => 0, 'happyClients' => 0, 'citiesCovered' => 0, 'satisfactionRate' => 98];

        try {
            $batch    = $this->client->properties()->list([
                'pageSize'  => 100,
                'sortBy'    => 'viewCount',
                'sortOrder' => true,
                'status'    => 'Active',
            ]);
            $allItems = $batch['items'] ?? [];
        } catch (\Throwable) {}

        try {
            $stats = $this->client->properties()->stats();
        } catch (\Throwable) {}

        // Rent / Sale tabs — top 6 each
        $rentProperties = array_values(array_slice(
            array_filter($allItems, fn($p) => strtolower($p['transactionType'] ?? '') === 'rent'),
            0, 6
        ));
        $saleProperties = array_values(array_slice(
            array_filter($allItems, fn($p) => strtolower($p['transactionType'] ?? '') === 'sale'),
            0, 6
        ));

        // Type counts + images (up to 3 per type)
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

        // City counts + images (up to 4 per city)
        $cityCounts = [];
        $cityImages = [];
        foreach ($allItems as $p) {
            $city = $p['city'] ?? '';
            if (!$city) continue;
            $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
            if (!empty($p['primaryImageUrl']) && count($cityImages[$city] ?? []) < 4) {
                $cityImages[$city][] = $p['primaryImageUrl'];
            }
        }
        arsort($cityCounts);

        // Top 6 viewed images
        $topViewedImages = [];
        foreach ($allItems as $p) {
            if (!empty($p['primaryImageUrl'])) {
                $topViewedImages[] = ['slug' => $p['slug'], 'imageUrl' => $p['primaryImageUrl']];
                if (count($topViewedImages) >= 6) break;
            }
        }

        return view('index', compact(
            'rentProperties', 'saleProperties', 'stats',
            'typeCounts', 'cityCounts', 'topViewedImages', 'cityImages', 'typeImages',
        ));
    }
}
