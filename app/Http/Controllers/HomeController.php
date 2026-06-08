<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use TouchEstate\Sdk\TouchEstateClient;

class HomeController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

    public function index()
    {
        // Rent properties (for slider)
        try {
            $rentResult    = $this->client->properties()->list([
                'transactionType' => 'Rent',
                'pageSize'        => 8,
                'status'          => 'Active',
                'sortBy'          => 'viewCount',
                'sortDescending'  => true,
            ]);
            $rentProperties = $rentResult['items'] ?? [];
        } catch (\Exception) {
            $rentProperties = [];
        }

        // Sale properties (for slider)
        try {
            $saleResult    = $this->client->properties()->list([
                'transactionType' => 'Sale',
                'pageSize'        => 8,
                'status'          => 'Active',
                'sortBy'          => 'viewCount',
                'sortDescending'  => true,
            ]);
            $saleProperties = $saleResult['items'] ?? [];
        } catch (\Exception) {
            $saleProperties = [];
        }

        // Broad list for counts / images (cached 5 min)
        $client = $this->client;
        $allItems = Cache::remember('home_all_props', 300, function () use ($client) {
            try {
                return $client->properties()->list([
                    'pageSize' => 100,
                    'status'   => 'Active',
                ])['items'] ?? [];
            } catch (\Exception) {
                return [];
            }
        });

        // Aggregate: type counts/images, city counts/images, top-viewed images
        $typeCounts      = ['Apartment' => 0, 'House' => 0, 'Office' => 0, 'Villa' => 0];
        $typeImages      = ['Apartment' => [], 'House' => [], 'Office' => [], 'Villa' => []];
        $cityCounts      = [];
        $cityImages      = [];
        $topViewedImages = [];

        foreach ($allItems as $item) {
            $type = $item['propertyType'] ?? '';
            $city = $item['city']         ?? '';
            $img  = $item['primaryImageUrl'] ?? null;

            if (isset($typeCounts[$type])) {
                $typeCounts[$type]++;
                if ($img && count($typeImages[$type]) < 4) {
                    $typeImages[$type][] = ['imageUrl' => $img];
                }
            }

            if ($city) {
                $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
                if ($img && count($cityImages[$city] ?? []) < 3) {
                    $cityImages[$city][] = ['imageUrl' => $img];
                }
            }

            if ($img) {
                $topViewedImages[] = [
                    'imageUrl'  => $img,
                    'viewCount' => $item['viewCount'] ?? 0,
                ];
            }
        }

        arsort($cityCounts);

        usort($topViewedImages, fn($a, $b) => $b['viewCount'] - $a['viewCount']);
        $topViewedImages = array_slice($topViewedImages, 0, 4);

        $totalCount = count($allItems);

        $stats = [
            'totalProperties'  => $totalCount,
            'propertiesListed' => $totalCount,
            'totalAgents'      => 0,
            'totalCities'      => count($cityCounts),
        ];

        $faqSliderProperties = array_slice(
            array_merge($rentProperties, $saleProperties), 0, 6
        );

        return view('index', compact(
            'rentProperties', 'saleProperties', 'stats',
            'typeCounts', 'cityCounts', 'topViewedImages', 'cityImages', 'typeImages',
            'faqSliderProperties'
        ));
    }
}
