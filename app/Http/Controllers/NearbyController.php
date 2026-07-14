<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NearbyController extends Controller
{
    private const TAG_SETS = [
        'transport' => [
            '["railway"="station"]["station"="subway"]',
            '["railway"="subway_entrance"]',
            '["railway"="station"]["station"="light_rail"]',
            '["amenity"="bus_station"]',
        ],
        'education' => [
            '["amenity"="university"]',
            '["amenity"="college"]',
            '["amenity"="school"]',
            '["amenity"="kindergarten"]',
        ],
        'food' => [
            '["shop"="supermarket"]',
            '["shop"="mall"]',
            '["amenity"="marketplace"]',
            '["shop"="grocery"]',
        ],
        'fitness' => [
            '["leisure"="fitness_centre"]',
            '["leisure"="sports_centre"]',
            '["leisure"="stadium"]',
            '["leisure"="swimming_pool"]',
        ],
    ];

    private const MIRRORS = [
        'https://overpass.kumi.systems/api/interpreter',
        'https://overpass-api.de/api/interpreter',
        'https://overpass.openstreetmap.ru/api/interpreter',
    ];

    public function __invoke(Request $request)
    {
        $lat      = (float) $request->query('lat', 0);
        $lon      = (float) $request->query('lon', 0);
        $category = $request->query('category', '');
        $locale   = $request->query('lang', 'ru');

        if (!$lat || !$lon) return response()->json(['results' => []]);

        $tags = self::TAG_SETS[$category] ?? [];
        if (empty($tags)) return response()->json(['results' => []]);

        $cacheKey = 'nearby:' . round($lat, 3) . ':' . round($lon, 3) . ':' . $category;
        $raw      = Cache::get($cacheKey);

        if ($raw === null) {
            $nodeLines = implode("\n  ", array_map(
                fn($t) => "node{$t}(around:500,{$lat},{$lon});",
                $tags
            ));
            $query = "[out:json][timeout:10];\n(\n  {$nodeLines}\n);\nout body;";

            try {
                $body = null;
                $code = 0;
                foreach (self::MIRRORS as $mirror) {
                    $ch = curl_init($mirror);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => 'data=' . urlencode($query),
                        CURLOPT_TIMEOUT        => 8,
                        CURLOPT_CONNECTTIMEOUT => 3,
                        CURLOPT_USERAGENT      => 'TouchEstateDemo/1.0',
                    ]);
                    $body = curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($code === 200 && $body) break;
                }

                if ($code !== 200 || !$body) return response()->json(['results' => []]);

                $elements = json_decode($body, true, 512, JSON_THROW_ON_ERROR)['elements'] ?? [];

                $raw = [];
                foreach ($elements as $el) {
                    if (!isset($el['lat'])) continue;
                    $raw[] = ['tags' => $el['tags'] ?? [], 'lat' => (float) $el['lat'], 'lon' => (float) $el['lon']];
                }

                Cache::put($cacheKey, $raw, empty($raw) ? 3600 : 86400);
            } catch (\Throwable) {
                return response()->json(['results' => []]);
            }
        }

        if (empty($raw)) return response()->json(['results' => []]);

        $namePriority = match($locale) {
            'hy'    => ['name:hy', 'name', 'name:ru', 'name:en'],
            'en'    => ['name:en', 'name:ru', 'name'],
            default => ['name:ru', 'name:en', 'name'],
        };

        $seen   = [];
        $unique = [];
        foreach ($raw as $r) {
            $name = null;
            foreach ($namePriority as $key) {
                if (!empty($r['tags'][$key])) { $name = $r['tags'][$key]; break; }
            }
            if (!$name) continue;
            $nameKey = mb_strtolower($name);
            if (!isset($seen[$nameKey])) {
                $seen[$nameKey] = true;
                $unique[] = ['name' => $name, 'lat' => $r['lat'], 'lon' => $r['lon']];
            }
        }

        return response()->json(['results' => array_slice($unique, 0, 7)]);
    }
}
