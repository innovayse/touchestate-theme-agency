<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;

// ─────────────────────────────────────────────
// Geocoding proxy routes (server-side to avoid CORS)
// ─────────────────────────────────────────────

Route::get('/api/suggest', function (\Illuminate\Http\Request $request) {
    $q      = trim($request->query('q', ''));
    $locale = $request->query('lang', 'ru');
    if (strlen($q) < 2) return response()->json(['results' => []]);

    $yLang = $locale === 'en' ? 'en_US' : 'ru_RU';

    try {
        $resp = Http::withHeaders(['Referer' => config('app.url')])->withoutVerifying()->timeout(5)
            ->get('https://suggest-maps.yandex.ru/suggest-geo', [
                'apikey'    => config('services.yandex.maps_key'),
                'text'      => $q,
                'lang'      => $yLang,
                'results'   => 7,
                'highlight' => 0,
                'v'         => 9,
            ]);

        // Yandex returns JSONP: suggest.apply({...})  — strip wrapper
        $body = trim($resp->body());
        $json = preg_replace('/^suggest\.apply\((.+)\)$/s', '$1', $body);
        $data = json_decode($json, true);

        $results = [];
        foreach (($data['results'] ?? []) as $item) {
            $title      = $item['title']['text']          ?? '';
            $where      = $item['log_id']['where']        ?? [];
            $whereTitle = $where['title']                 ?? '';
            $whereName  = $where['name']                  ?? '';

            // Only actual localities: result title must match the "where" city
            if (!$title || $title !== $whereTitle) continue;

            // Description: everything in where.name except the city name itself
            $parts = array_filter(array_map('trim', explode(',', $whereName)));
            $desc  = implode(', ', array_values(array_filter($parts, fn($p) => $p !== $title)));

            $results[] = ['name' => $title, 'desc' => $desc];
        }

        return response()->json(['results' => $results]);
    } catch (\Throwable $e) {
        return response()->json(['results' => []]);
    }
});


Route::get('/api/central-district', function (\Illuminate\Http\Request $request) {
    $city = trim($request->query('city', ''));
    $lang = $request->query('lang', 'ru');
    if (!$city) return response()->json(['district' => '']);

    $acceptLang = match($lang) {
        'en' => 'en',
        'hy' => 'hy,ru',
        default => 'ru,en',
    };

    $client = Http::withHeaders(['User-Agent' => 'RealEstateSite/1.0', 'Accept-Language' => $acceptLang])
        ->withoutVerifying()->timeout(6);

    try {
        // Step 1: get city coordinates from Nominatim structured search
        $places = $client->get('https://nominatim.openstreetmap.org/search', [
            'city'   => $city,
            'format' => 'json',
            'limit'  => 5,
        ])->json();

        if (empty($places)) return response()->json(['district' => '']);

        // Expand types to catch administrative cities (e.g. federal cities, municipalities)
        $place = collect($places)->first(fn($p) => in_array($p['type'] ?? '', [
            'city', 'town', 'village', 'municipality', 'administrative',
        ])) ?? $places[0];

        $lat = (float) $place['lat'];
        $lon = (float) $place['lon'];

        // Helper: extract best district name from a Nominatim address array
        $extractDistrict = function (array $addr) use ($city): string {
            $cityName = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['municipality'] ?? '';
            $skip = [strtolower($cityName), strtolower($city), '', null];
            foreach (['suburb','borough','city_district','county','quarter','neighbourhood'] as $key) {
                $val = $addr[$key] ?? '';
                if ($val && !in_array(strtolower($val), $skip, true)) return $val;
            }
            return '';
        };

        // Step 2: try exact centroid at zoom 12, 14, 10
        $district = '';
        foreach ([12, 14, 10] as $zoom) {
            $addr = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'lat'            => $lat,
                'lon'            => $lon,
                'format'         => 'json',
                'addressdetails' => 1,
                'zoom'           => $zoom,
            ])->json('address', []);

            $d = $extractDistrict($addr);
            if ($d) { $district = $d; break; }
        }

        // Step 3: centroid may fall on a park/river — shift ~400 m north and retry
        if (!$district) {
            $addr = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'lat'            => $lat + 0.004,
                'lon'            => $lon,
                'format'         => 'json',
                'addressdetails' => 1,
                'zoom'           => 12,
            ])->json('address', []);

            $district = $extractDistrict($addr);
        }

        // If no district found, fall back to the city name itself
        if (!$district) {
            $district = $place['name'] ?? $place['display_name'] ?? $city;
            // display_name can be "Капан, Сюникская область, Армения" — take only first part
            if (str_contains($district, ',')) {
                $district = trim(explode(',', $district)[0]);
            }
        }

        return response()->json(['district' => $district]);
    } catch (\Throwable $e) {
        return response()->json(['district' => '']);
    }
});


Route::get('/api/nearby', function (\Illuminate\Http\Request $request) {
    $lat      = (float) $request->query('lat', 0);
    $lon      = (float) $request->query('lon', 0);
    $category = $request->query('category', '');
    $locale   = $request->query('lang', 'ru');
    if (!$lat || !$lon) return response()->json(['results' => []]);

    // Overpass QL tag filters per category (OpenStreetMap, free, no key)
    $tagSets = [
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

    $tags = $tagSets[$category] ?? [];
    if (empty($tags)) return response()->json(['results' => []]);

    // Round to 3 decimal places (~110m grid) for cache key — avoids misses from GPS noise
    $latKey   = round($lat, 3);
    $lonKey   = round($lon, 3);
    $cacheKey = "nearby:{$latKey}:{$lonKey}:{$category}";

    $raw = \Illuminate\Support\Facades\Cache::get($cacheKey);

    if ($raw === null) {
        $radius    = 500;
        $nodeLines = implode("\n  ", array_map(
            fn($t) => "node{$t}(around:{$radius},{$lat},{$lon});",
            $tags
        ));
        $query = "[out:json][timeout:10];\n(\n  {$nodeLines}\n);\nout body;";

        try {
            $ch = curl_init('https://overpass-api.de/api/interpreter');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => 'data=' . urlencode($query),
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT      => 'TouchEstateDemo/1.0',
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code !== 200 || !$body) return response()->json(['results' => []]);

            $data     = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            $elements = $data['elements'] ?? [];

            $raw = [];
            foreach ($elements as $el) {
                if (!isset($el['lat'])) continue;
                $raw[] = ['tags' => $el['tags'] ?? [], 'lat' => (float) $el['lat'], 'lon' => (float) $el['lon']];
            }

            // Cache results: 24h for non-empty, 1h for empty (real absence of POI, not a transient error)
            // Transient errors (non-200, exception) skip caching entirely via early return above
            \Illuminate\Support\Facades\Cache::put($cacheKey, $raw, empty($raw) ? 3600 : 86400);
        } catch (\Throwable) {
            return response()->json(['results' => []]);
        }
    }

    if (empty($raw)) return response()->json(['results' => []]);

    // Apply locale-aware name selection at response time (raw tags are locale-neutral in cache)
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
});


// ─────────────────────────────────────────────
// Contacts API
// ─────────────────────────────────────────────
Route::get('/api/contacts', [ContactController::class, 'index']);
Route::get('/api/contacts/{id}', [ContactController::class, 'show']);
Route::post('/api/contact', [ContactController::class, 'inquiry']);


// ─────────────────────────────────────────────
// Default routes (no locale prefix) → Armenian
// ─────────────────────────────────────────────

// Home
Route::get('/', [HomeController::class, 'index']);

// Property listing + single (API-driven)
Route::get('/property', [PropertyController::class, 'index']);
Route::get('/property/{slug}', [PropertyController::class, 'show']);
Route::get('/property/{slug}/extras', [PropertyController::class, 'extras']); // skeleton-first: similar + comments
Route::post('/api/property/{slug}/view',    [PropertyController::class, 'recordView']);
Route::post('/api/property/{slug}/enquire', [PropertyController::class, 'enquire']);

// Map
Route::get('/map',      [PropertyController::class, 'map']);
Route::get('/map/data', [PropertyController::class, 'mapData']);

// All simple static pages (default to Armenian locale)
$defaultRoutes = [
    'contact-us', // 'about-us' temporarily disabled (page kept); 'our-team' removed
    'faq', 'privacy-policy', 'terms-condition', /* 'testimonial', */
    'cart', 'checkout',
    'maintenance', 'error-404', 'error-500',
];

foreach ($defaultRoutes as $route) {
    Route::get('/' . $route, fn () => view($route));
}


// ─────────────────────────────────────────────
// Localized routes: /en/* /ru/* /hy/*
// ─────────────────────────────────────────────
Route::group(
    ['prefix' => '{locale}', 'where' => ['locale' => 'en|ru|hy'], 'middleware' => 'setlocale'],
    function () {

        // Home (API-driven)
        Route::get('/', [HomeController::class, 'index'])->name('index');

        // Property listing + single (API-driven)
        Route::get('/property',        [PropertyController::class, 'index'])->name('property');
        Route::get('/property/{slug}', [PropertyController::class, 'show'])->name('property.single');
        Route::get('/property/{slug}/extras', [PropertyController::class, 'extras'])->name('property.extras'); // skeleton-first: similar + comments

        // Map
        Route::get('/map',        [PropertyController::class, 'map'])->name('map');
        Route::get('/map/data',   [PropertyController::class, 'mapData'])->name('map.data');
        Route::get('/map/coords', [PropertyController::class, 'mapCoords'])->name('map.coords');

        // Favorites
        Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites');
        Route::post('/favorites/load', [FavoritesController::class, 'load'])->name('favorites.load');

        // Compare
        Route::get('/compare', [CompareController::class, 'index'])->name('compare');
        Route::post('/compare/load', [CompareController::class, 'load'])->name('compare.load');

        // Static pages
        // Route::get('/about-us',        fn () => view('about-us'))->name('about-us'); // temporarily disabled (page kept)
        Route::get('/contact-us',      fn () => view('contact-us'))->name('contact-us');
        Route::get('/faq',             fn () => view('faq'))->name('faq');
        Route::get('/privacy-policy',  fn () => view('privacy-policy'))->name('privacy-policy');
        Route::get('/terms-condition', fn () => view('terms-condition'))->name('terms-condition');
        // Route::get('/testimonial',     fn () => view('testimonial'))->name('testimonial');
        Route::get('/cart',            fn () => view('cart'))->name('cart');
        Route::get('/checkout',        fn () => view('checkout'))->name('checkout');

        // Error / utility pages
        Route::get('/maintenance', fn () => view('maintenance'))->name('maintenance');
        Route::get('/error-404',   fn () => view('error-404'))->name('error-404');
        Route::get('/error-500',   fn () => view('error-500'))->name('error-500');
    }
);
