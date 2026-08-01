<?php

use App\Http\Controllers\CompareController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Geocoding proxy routes (server-side to avoid CORS)
// ─────────────────────────────────────────────

Route::get('/api/suggest', function (\Illuminate\Http\Request $request) {
    $q      = trim($request->query('q', ''));
    $locale = $request->query('lang', 'ru');
    if (strlen($q) < 2) {
        return response()->json(['results' => []]);
    }

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
        $data = json_decode($json ?? '', true);

        $results = [];
        foreach (($data['results'] ?? []) as $item) {
            $title      = $item['title']['text']          ?? '';
            $where      = $item['log_id']['where']        ?? [];
            $whereTitle = $where['title']                 ?? '';
            $whereName  = $where['name']                  ?? '';

            // Only actual localities: result title must match the "where" city
            if (!$title || $title !== $whereTitle) {
                continue;
            }

            // Description: everything in where.name except the city name itself
            $parts = array_filter(array_map('trim', explode(',', $whereName)));
            $desc  = implode(', ', array_values(array_filter($parts, fn ($p) => $p !== $title)));

            $results[] = ['name' => $title, 'desc' => $desc];
        }

        return response()->json(['results' => $results]);
    } catch (\Throwable $e) {
        return response()->json(['results' => []]);
    }
});


Route::get('/api/city-en', function (\Illuminate\Http\Request $request) {
    $name = trim($request->query('name', ''));
    if (!$name) return response()->json(['en' => '']);
    try {
        $results = Http::withHeaders(['User-Agent' => 'RealEstateSite/1.0', 'Accept-Language' => 'en'])
            ->withoutVerifying()->timeout(5)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q'              => $name,
                'format'         => 'json',
                'limit'          => 5,
                'namedetails'    => 1,
                'addressdetails' => 1,
            ])
            ->json();
        if (empty($results)) return response()->json(['en' => $name]);

        // Prefer an actual populated place (city/town/village) over other matches
        // like border crossings that Nominatim may rank first for some queries.
        $places = ['city', 'town', 'village', 'municipality', 'hamlet'];
        $r = collect($results)->first(function ($item) use ($places) {
            return ($item['class'] ?? '') === 'place' && in_array($item['type'] ?? '', $places, true);
        }) ?? $results[0];

        // Prefer the explicit English name, then the address' city/town/village,
        // and only fall back to the first segment of display_name.
        $address = $r['address'] ?? [];
        $enName = $r['namedetails']['name:en']
            ?? $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? (isset($r['display_name']) ? trim(explode(',', $r['display_name'])[0]) : $name);

        return response()->json(['en' => trim($enName) ?: $name]);
    } catch (\Throwable $e) {
        return response()->json(['en' => $name]);
    }
});

Route::get('/api/central-district', function (\Illuminate\Http\Request $request) {
    $city = trim($request->query('city', ''));
    $lang = $request->query('lang', 'ru');
    if (!$city) {
        return response()->json(['district' => '']);
    }

    $acceptLang = match($lang) {
        'en'    => 'en',
        'hy'    => 'hy,ru',
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

        if (empty($places)) {
            return response()->json(['district' => '']);
        }

        // Expand types to catch administrative cities (e.g. federal cities, municipalities)
        $place = collect($places)->first(fn ($p) => in_array($p['type'] ?? '', [
            'city', 'town', 'village', 'municipality', 'administrative',
        ])) ?? $places[0];

        $lat = (float) $place['lat'];
        $lon = (float) $place['lon'];

        // Helper: extract best district name from a Nominatim address array
        $extractDistrict = function (array $addr) use ($city): string {
            $cityName = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['municipality'] ?? '';
            $skip     = [strtolower($cityName), strtolower($city), '', null];
            foreach (['suburb','borough','city_district','county','quarter','neighbourhood'] as $key) {
                $val = $addr[$key] ?? '';
                if ($val && !in_array(strtolower($val), $skip, true)) {
                    return $val;
                }
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
            if ($d) {
                $district = $d;
                break;
            }
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
    // Nearby POI lookup via Yandex "Search for Organizations" API (server-side: hides key, avoids CORS).
    // Used by the property page "Location details" card for non-transport categories (e.g. education).
    $lat      = (float) $request->query('lat', 0);
    $lon      = (float) $request->query('lon', 0);
    $category = $request->query('category', '');
    $locale   = $request->query('lang', 'ru');
    if (!$lat || !$lon) {
        return response()->json(['results' => []]);
    }

    // category → localized search text
    $rubrics = [
        'education' => ['ru' => 'школа',  'en' => 'school',     'hy' => 'դպրոց'],
        'transport' => ['ru' => 'метро',  'en' => 'metro',      'hy' => 'մետրո'],
        'food'      => ['ru' => 'магазин','en' => 'supermarket','hy' => 'խանութ'],
        'hotel'     => ['ru' => 'отель',  'en' => 'hotel',      'hy' => 'հյուրանոց'],
    ];
    $text = $rubrics[$category][$locale] ?? ($rubrics[$category]['en'] ?? $category);
    if (!$text) {
        return response()->json(['results' => []]);
    }

    $yLang = match ($locale) {
        'en'    => 'en_US',
        'hy'    => 'hy_AM',
        default => 'ru_RU',
    };

    try {
        $resp = Http::withoutVerifying()->timeout(6)
            ->get('https://search-maps.yandex.ru/v1/', [
                'apikey'  => config('services.yandex.places_key'),
                'text'    => $text,
                'lang'    => $yLang,
                'll'      => "{$lon},{$lat}",
                'spn'     => '0.06,0.04',
                'rspn'    => 1,
                'type'    => 'biz',
                'results' => 15,
            ]);

        $features = $resp->json('features', []);
        $results  = [];
        foreach ($features as $f) {
            $coords = $f['geometry']['coordinates'] ?? null; // [lon, lat]
            $name   = $f['properties']['name']      ?? '';
            if (!$coords || !$name) {
                continue;
            }
            $results[] = ['name' => $name, 'lon' => (float) $coords[0], 'lat' => (float) $coords[1]];
        }

        return response()->json(['results' => $results]);
    } catch (\Throwable $e) {
        return response()->json(['results' => []]);
    }
});



// ─────────────────────────────────────────────
// Default routes (no locale prefix) → Armenian
// ─────────────────────────────────────────────

// Currency switcher — stores the chosen display currency in the session, then
// returns to the referring page (locale prefix is preserved by the referer).
Route::get('/currency/{currency}', function (\Illuminate\Http\Request $request, string $currency) {
    if (in_array($currency, config('currency.supported'), true)) {
        session(['currency' => $currency]);
    }
    // JS switches currency client-side and hits this only to persist the session.
    if ($request->ajax() || $request->wantsJson()) {
        return response()->noContent();
    }

    return redirect()->back();
})->where('currency', 'USD|AMD|RUB|EUR')->name('currency.switch');

// Home
Route::get('/', [HomeController::class, 'index']);

// Property listing + single (API-driven)
Route::get('/property', [PropertyController::class, 'index']);
Route::get('/property/load-more', [PropertyController::class, 'loadMore']); // must precede /property/{slug}
Route::get('/property/{slug}', [PropertyController::class, 'show']);
Route::get('/property/{slug}/extras', [PropertyController::class, 'extras']); // skeleton-first: similar + comments
Route::post('/api/property/{slug}/view', [PropertyController::class, 'recordView']);
Route::post('/api/property/{slug}/enquire', [PropertyController::class, 'enquire']);

// Map
Route::get('/map', [PropertyController::class, 'map']);

// All simple static pages (default to Armenian locale)
$defaultRoutes = [
    'contact-us', // 'about-us' temporarily disabled (page kept); 'our-team' removed
    'faq', 'privacy-policy', 'terms-condition', // 'testimonial' disabled — demo page, access closed

    // 'cart', 'checkout' disabled — broken demo pages, access closed
    'maintenance', 'error-404', 'error-500',
];

foreach ($defaultRoutes as $route) {
    Route::get('/' . $route, fn () => view($route)); // @phpstan-ignore argument.type
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
        Route::get('/property', [PropertyController::class, 'index'])->name('property');
        Route::get('/property/load-more', [PropertyController::class, 'loadMore'])->name('property.loadmore'); // must precede /property/{slug}
        Route::get('/property/{slug}', [PropertyController::class, 'show'])->name('property.single');
        Route::get('/property/{slug}/extras', [PropertyController::class, 'extras'])->name('property.extras'); // skeleton-first: similar + comments

        // Map
        Route::get('/map', [PropertyController::class, 'map'])->name('map');
        Route::get('/map/cards', [PropertyController::class, 'mapCards'])->name('map.cards');         // AJAX: left-column cards
        Route::get('/map/locations', [PropertyController::class, 'mapLocations'])->name('map.locations'); // AJAX: marker coords

        // Favorites
        Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites');
        Route::post('/favorites/load', [FavoritesController::class, 'load'])->name('favorites.load');

        // Compare
        Route::get('/compare', [CompareController::class, 'index'])->name('compare');
        Route::post('/compare/load', [CompareController::class, 'load'])->name('compare.load');

        // Static pages
        // Route::get('/about-us',        fn () => view('about-us'))->name('about-us'); // temporarily disabled (page kept)
        Route::get('/contact-us', fn () => view('contact-us'))->name('contact-us');
        Route::get('/faq', fn () => view('faq'))->name('faq');
        Route::get('/privacy-policy', fn () => view('privacy-policy'))->name('privacy-policy');
        Route::get('/terms-condition', fn () => view('terms-condition'))->name('terms-condition');
        // Route::get('/testimonial', fn () => view('testimonial'))->name('testimonial'); // disabled — demo page, access closed
        // Route::get('/cart', fn () => view('cart'))->name('cart'); // disabled — broken demo page, access closed
        // Route::get('/checkout', fn () => view('checkout'))->name('checkout'); // disabled — broken demo page, access closed

        // Error / utility pages
        Route::get('/maintenance', fn () => view('maintenance'))->name('maintenance');
        Route::get('/error-404', fn () => view('error-404'))->name('error-404');
        Route::get('/error-500', fn () => view('error-500'))->name('error-500');
    }
);
