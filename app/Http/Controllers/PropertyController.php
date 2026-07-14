<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CbaRatesService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use TouchEstate\Sdk\Signer\SignatureV4Signer;
use TouchEstate\Sdk\TouchEstateClient;

class PropertyController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

    private function validateFilters(): void
    {
        // Parse combined sortByFull (e.g. "viewCount_desc") into sortBy + sortOrder
        if (request()->filled('sortByFull')) {
            $full = request('sortByFull');
            $pos  = strrpos($full, '_');
            if ($pos !== false) {
                request()->merge([
                    'sortBy'    => substr($full, 0, $pos),
                    'sortOrder' => substr($full, $pos + 1),
                ]);
            }
        }

        request()->validate([
            // status is always Active on public endpoint — reject if someone tries to override
            'status'           => 'prohibited',

            // Enum singles / arrays
            'propertyType'     => ['nullable', 'array'],
            'propertyType.*'   => ['string', Rule::in(['Apartment','House','Studio','Villa','Townhouse','Penthouse','Room','Complex','Land','Commercial','Office','Warehouse','Garage','Pavilion','EventVenue','Dacha','Cottage'])],
            'transactionType'  => ['nullable', Rule::in(['Sale','Rent','RentDaily'])],
            'renovationType'   => ['nullable', Rule::in(['Capital','Designer','Euro','Cosmetic','Partial','Old','Unrenovated'])],
            'constructionType' => ['nullable', Rule::in(['Wood','Strip','Brick','Monolithic','Panel','Stone'])],
            'furnitureType'    => ['nullable', Rule::in(['Furnished','Partial','Unavailable','ByAgreement'])],
            'petsPolicy'       => ['nullable', Rule::in(['Yes','No','ByAgreement'])],
            'childrenPolicy'   => ['nullable', Rule::in(['Yes','No','ByAgreement'])],
            'currency'         => ['nullable', Rule::in(['USD','EUR','AMD','RUB'])],

            // Sorting
            'sortBy'           => ['nullable', Rule::in(['price','area','rooms','title','createdAt','viewCount'])],
            'sortOrder'        => ['nullable', Rule::in(['asc','desc'])],

            // Free-text strings — length cap, no control chars allowed by default
            'search'           => ['nullable', 'string', 'max:200'],
            'code'             => ['nullable', 'string', 'max:50'],
            'city'             => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],

            // Numeric ranges
            'minPrice'         => ['nullable', 'integer', 'min:0'],
            'maxPrice'         => ['nullable', 'integer', 'min:0'],
            'minArea'          => ['nullable', 'integer', 'min:0'],
            'maxArea'          => ['nullable', 'integer', 'min:0'],
            'minRooms'         => ['nullable', 'integer', 'min:0'],
            'maxRooms'         => ['nullable', 'integer', 'min:0'],
            'minBedrooms'      => ['nullable', 'integer', 'min:0'],
            'maxBedrooms'      => ['nullable', 'integer', 'min:0'],
            'minBathrooms'     => ['nullable', 'integer', 'min:0'],
            'maxBathrooms'     => ['nullable', 'integer', 'min:0'],
            'minFloor'         => ['nullable', 'integer', 'min:0'],
            'maxFloor'         => ['nullable', 'integer', 'min:0'],
            'minLandArea'      => ['nullable', 'integer', 'min:0'],
            'maxLandArea'      => ['nullable', 'integer', 'min:0'],
            'yearBuiltFrom'    => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'yearBuiltTo'      => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'pageSize'         => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'             => ['nullable', 'integer', 'min:1'],

            // Enum arrays — validate each element
            'features'         => ['nullable', 'array'],
            'features.*'       => ['string', Rule::in(['Elevator','Parking','Balcony','Garage','Pool','Garden','Basement','Gym','Security','PanoramicWindows','Sauna','Fireplace','Gazebo','BarbecueArea','SportsCourt','LoadingDock'])],
            'appliances'       => ['nullable', 'array'],
            'appliances.*'     => ['string', Rule::in(['Washer','Dryer','Fridge','Stove','Microwave','CoffeeMaker','WaterHeater','HairDryer','Iron','Dishwasher','VacuumCleaner'])],
            'utilities'        => ['nullable', 'array'],
            'utilities.*'      => ['string', Rule::in(['Electricity','Water','Gas','Sewage'])],
            'heatingType'      => ['nullable', 'array'],
            'heatingType.*'    => ['string', Rule::in(['Central','Gas','Electric','Autonomous','Solar','UnderfloorHeating'])],
            'parkingType'      => ['nullable', 'array'],
            'parkingType.*'    => ['string', Rule::in(['Open','Covered','Garage','Barrier'])],
            'windowView'       => ['nullable', 'array'],
            'windowView.*'     => ['string', Rule::in(['Garden','City','Street','Yard'])],

            // Single enums — new
            'balconyType'      => ['nullable', Rule::in(['Unavailable','Open','Closed'])],
            'terraceType'      => ['nullable', Rule::in(['Unavailable','Open','Closed'])],

            // Booleans
            'isLongTermRental' => ['nullable', 'boolean'],
            'isUninhabited'    => ['nullable', 'boolean'],
            'sunDirection'     => ['nullable', 'boolean'],
        ]);
    }

    private function buildFilters(): array
    {
        $pageSize = min((int) request('pageSize', 21), 100);

        $params = [
            'pageNumber' => (int) request('page', 1),
            'pageSize'   => $pageSize,
            'status'     => 'Active',
        ];

        // Search
        if (request()->filled('search')) {
            $params['search'] = request('search');
        }

        // Code search
        if (request()->filled('code')) {
            $params['code'] = request('code');
        }

        // Basic filters
        foreach (['transactionType', 'city', 'district', 'currency',
                  'renovationType', 'constructionType', 'furnitureType', 'petsPolicy', 'childrenPolicy',
                  'balconyType', 'terraceType'] as $key) {
            if (request()->filled($key)) {
                $params[$key] = request($key);
            }
        }

        // propertyType can be a single value or array (multi-select pill buttons)
        if (request()->filled('propertyType')) {
            $pt = (array) request('propertyType');
            $params['propertyType'] = count($pt) === 1 ? $pt[0] : $pt;
        }

        // Numeric range filters
        foreach (['minPrice', 'maxPrice', 'minRooms', 'maxRooms', 'minBedrooms', 'maxBedrooms', 'minBathrooms', 'maxBathrooms', 'minFloor', 'maxFloor', 'minLandArea', 'maxLandArea', 'minYearBuilt', 'maxYearBuilt'] as $key) {
            if (request()->filled($key)) {
                $params[$key] = (int) request($key);
            }
        }

        // Area filters (form uses minArea/maxArea, SDK expects same)
        if (request()->filled('minArea')) {
            $params['minArea'] = (int) request('minArea');
        }
        if (request()->filled('maxArea')) {
            $params['maxArea'] = (int) request('maxArea');
        }

        // Year built (form sends yearBuiltFrom/yearBuiltTo, SDK expects minYearBuilt/maxYearBuilt)
        if (request()->filled('yearBuiltFrom')) {
            $params['minYearBuilt'] = (int) request('yearBuiltFrom');
        }
        if (request()->filled('yearBuiltTo')) {
            $params['maxYearBuilt'] = (int) request('yearBuiltTo');
        }

        // Sorting
        if (request()->filled('sortBy')) {
            $params['sortBy'] = request('sortBy');
        }
        if (request('sortOrder') === 'desc') {
            $params['sortDescending'] = true;
        } elseif (request('sortOrder') === 'asc') {
            $params['sortDescending'] = false;
        }

        // Multi-select array filters
        foreach (['features', 'appliances', 'utilities', 'heatingType', 'parkingType', 'windowView'] as $key) {
            $values = array_filter((array) request($key, []));
            if (!empty($values)) {
                $params[$key] = array_values($values);
            }
        }

        // Boolean flag filters
        foreach (['isNewConstruction', 'isNegotiable', 'isFrontLine', 'noAgentCalls',
                  'isLongTermRental', 'isUninhabited', 'sunDirection'] as $key) {
            if (request()->boolean($key, false)) {
                $params[$key] = true;
            }
        }

        // Boolean amenity filters — map amenities[] checkboxes to SDK boolean params
        $amenityMap = [
            'Elevator' => 'hasElevator',
            'Parking'  => 'hasParking',
            'Balcony'  => 'hasBalcony',
            'Garage'   => 'hasGarage',
            'Pool'     => 'hasPool',
            'Garden'   => 'hasGarden',
            'Basement' => 'hasBasement',
            'Gym'      => 'hasGym',
            'Security' => 'hasSecurity',
        ];
        foreach (request('amenities', []) as $amenity) {
            if (isset($amenityMap[$amenity])) {
                $params[$amenityMap[$amenity]] = true;
            }
        }
        // Also support direct boolean params (e.g. from map page)
        foreach ($amenityMap as $sdkKey) {
            if (request()->filled($sdkKey)) {
                $params[$sdkKey] = true;
            }
        }

        return $params;
    }

    private function crossCurrencySearch(array $baseFilters, array $queries): array
    {
        $cacheKey = 'te_xfx:' . md5(serialize($queries) . serialize($baseFilters));
        return Cache::remember($cacheKey, 900, function () use ($baseFilters, $queries) {
            $seen    = [];
            $results = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];

            foreach (array_keys($queries) as $cur) {
                $filters             = $baseFilters;
                $filters['currency'] = $cur;
                $filters['minPrice'] = (int) round($queries[$cur]['min']);
                if ($queries[$cur]['max'] < PHP_INT_MAX) {
                    $filters['maxPrice'] = (int) round($queries[$cur]['max']);
                }

                try {
                    $res = $this->client->properties()->list($filters);
                    foreach ($res['items'] ?? [] as $item) {
                        $slug = $item['slug'] ?? null;
                        if ($slug && !isset($seen[$slug])) {
                            $seen[$slug]        = true;
                            $results['items'][] = $item;
                        }
                    }
                    if (!empty($res['totalCount'])) {
                        $results['totalCount'] = max($results['totalCount'], (int) $res['totalCount']);
                    }
                    if (!empty($res['hasNextPage'])) {
                        $results['hasNextPage'] = true;
                    }
                } catch (\Throwable) {
                    // skip failed currency request
                }
            }

            return $results;
        });
    }

    public function index()
    {
        // Shell: returns instantly with no API call.
        // Results are loaded async via results() endpoint by JS on the page.
        $this->validateFilters();
        $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false, 'skeleton' => true];
        return view('property', compact('properties'));
    }

    public function results()
    {
        $this->validateFilters();

        $filters = $this->buildFilters();

        if (request()->filled('minPrice') || request()->filled('maxPrice')) {
            $selectedCurrency = in_array(request('selectedCurrency'), ['USD', 'EUR', 'AMD', 'RUB'])
                ? request('selectedCurrency') : 'USD';
            $minPrice = request()->filled('minPrice') ? (float) request('minPrice') : 0;
            $maxPrice = request()->filled('maxPrice') ? (float) request('maxPrice') : PHP_INT_MAX;

            $rates      = app(CbaRatesService::class)->getRates();
            $currencies = ['USD', 'EUR', 'AMD', 'RUB'];
            $queries    = [];
            foreach ($currencies as $cur) {
                $queries[$cur] = [
                    'min' => $minPrice * ($rates[$selectedCurrency] ?? 1) / ($rates[$cur] ?? 1),
                    'max' => $maxPrice < PHP_INT_MAX
                        ? $maxPrice * ($rates[$selectedCurrency] ?? 1) / ($rates[$cur] ?? 1)
                        : PHP_INT_MAX,
                ];
            }

            $baseFilters = $filters;
            unset($baseFilters['minPrice'], $baseFilters['maxPrice'], $baseFilters['currency']);

            try {
                $properties = $this->crossCurrencySearch($baseFilters, $queries);
            } catch (\Exception $e) {
                $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
            }
        } else {
            try {
                $properties = Cache::remember('te_list:' . md5(serialize($filters)), 3600, function () use ($filters) {
                    return $this->client->properties()->list($filters);
                });
            } catch (\Exception $e) {
                $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
            }
        }

        $html = view('partials.property-results', compact('properties'))->render();
        return response()->json(['html' => $html]);
    }

    public function map()
    {
        $yandexKey = config('services.yandex.maps_key', env('YANDEX_MAPS_API_KEY', ''));
        return view('map', compact('yandexKey'));
    }

    public function mapData(): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        try {
            $result   = Cache::remember('te_map_list', 1800, fn () => $this->client->properties()->list([
                'pageNumber' => 1,
                'pageSize'   => 100,
                'status'     => 'Active',
            ]));
            $allItems = $result['items'] ?? [];

            $withCoords = [];
            $pending    = [];
            $needEnrich = false;

            foreach ($allItems as $item) {
                $slug       = $item['slug'] ?? null;
                $coordCache = $slug ? Cache::get('prop_coords:' . $slug) : null;
                $apiLat     = is_numeric($item['latitude']  ?? null) ? (float) $item['latitude']  : null;
                $apiLng     = is_numeric($item['longitude'] ?? null) ? (float) $item['longitude'] : null;

                if ($coordCache !== null && $coordCache['lat'] !== null) {
                    $lat = $coordCache['lat'];
                    $lng = $coordCache['lng'];
                } elseif ($apiLat !== null && $apiLng !== null) {
                    $lat = $apiLat;
                    $lng = $apiLng;
                    if ($slug && $coordCache === null) {
                        Cache::put('prop_coords:' . $slug, ['lat' => $lat, 'lng' => $lng, 'precise' => true, 'api_checked' => true], now()->addDay());
                    }
                } else {
                    $lat = null;
                    $lng = null;
                    if ($coordCache === null) {
                        $needEnrich = true;
                    }
                }

                $meta = [
                    'slug'        => $slug,
                    'title'       => $item['title'] ?? '',
                    'price'       => isset($item['price']) ? number_format((float) $item['price']) . ' ' . ($item['currency'] ?? '') : '',
                    'rawPrice'    => isset($item['price']) ? (float) $item['price'] : null,
                    'rawCurrency' => $item['currency'] ?? 'AMD',
                    'url'         => url('/' . $locale . '/property/' . ($slug ?? '')),
                    'img'         => $item['primaryImageUrl'] ?? '',
                    'city'        => ($item['city'] ?? '') . (!empty($item['district']) ? ', ' . $item['district'] : ''),
                ];

                if ($lat !== null && $lng !== null) {
                    $withCoords[] = array_merge($meta, ['lat' => $lat, 'lng' => $lng]);
                } else {
                    $pending[] = $meta;
                }
            }

            // If unchecked items exist and enrichment isn't already running, spawn it as a
            // separate process so the web server stays responsive (php artisan serve is
            // single-threaded — terminating callbacks block; exec with & does not).
            $artisan = base_path('artisan');

            if ($needEnrich && !Cache::has('map_enrich_running')) {
                Cache::put('map_enrich_running', true, now()->addMinutes(10));
                exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' map:enrich > /dev/null 2>&1 &');
            }

            // Pre-warm property page caches so clicks open instantly
            if (!Cache::has('map_prewarm_running')) {
                $uncachedProps = collect($allItems)->filter(
                    fn($i) => !empty($i['slug']) && !Cache::has('te_prop:' . $i['slug'])
                )->count();
                if ($uncachedProps > 0) {
                    Cache::put('map_prewarm_running', true, now()->addMinutes(15));
                    exec(PHP_BINARY . ' ' . escapeshellarg($artisan) . ' map:prewarm > /dev/null 2>&1 &');
                }
            }

            return response()->json(['items' => $withCoords, 'pending' => $pending]);
        } catch (\Exception $e) {
            return response()->json(['items' => [], 'pending' => []]);
        }
    }

    // Endpoint for JS polling — returns coords for slugs that are now cached
    public function mapCoords(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $slugs  = (array) $request->input('slugs', []);
        $result = [];
        foreach ($slugs as $slug) {
            $cached = \Cache::get('prop_coords:' . $slug);
            if ($cached && $cached['lat'] !== null && ($cached['precise'] ?? false)) {
                $result[$slug] = $cached;
            }
        }
        return response()->json($result);
    }

    /**
     * Fetch latitude/longitude for each property via parallel curl_multi requests, with caching.
     */
    public function enrichWithCoordinates(array $items, bool $respectRateLimit = false): array
    {
        // Step 1: Separate already-cached from items needing detail API lookup
        $uncached = []; // slug → index in $items
        foreach ($items as $i => &$item) {
            $slug = $item['slug'] ?? null;
            if (!$slug) continue;

            $cached = Cache::get('prop_coords:' . $slug);
            if ($cached !== null && $cached['lat'] !== null) {
                $item['latitude']  = $cached['lat'];
                $item['longitude'] = $cached['lng'];
            } else {
                $uncached[$slug] = $i;
            }
        }
        unset($item);

        if (empty($uncached)) {
            return $items;
        }

        // Step 2: Fetch full property details in parallel via curl_multi (max 25 concurrent)
        $signer    = new SignatureV4Signer();
        $baseUrl   = rtrim(config('touchestate.base_url', env('TOUCHESTATE_BASE_URL', '')), '/');
        $publicKey = config('touchestate.public_key', env('TOUCHESTATE_PUBLIC_KEY', ''));
        $secretKey = config('touchestate.secret_key', env('TOUCHESTATE_SECRET_KEY', ''));
        $urlParts  = parse_url($baseUrl);
        $host      = $urlParts['host'] ?? 'localhost';
        $port      = $urlParts['port'] ?? null;
        $scheme    = $urlParts['scheme'] ?? 'https';
        $hostHeader = ($port !== null && $port !== (($scheme === 'https') ? 443 : 80))
            ? sprintf('%s:%d', $host, $port) : $host;

        $handles = [];
        foreach (array_keys($uncached) as $slug) {
            $path      = '/api/external/properties/' . $slug;
            $timestamp = gmdate('Ymd\THis\Z');
            $bodyHash  = $signer->sha256Hex('');
            $headers   = ['host' => $hostHeader, 'x-te-date' => $timestamp];
            $keys = array_keys($headers);
            sort($keys);
            $signedHeaders = implode(';', $keys);
            $signature = $signer->calculateSignature('GET', $path, '', $headers, $signedHeaders, $bodyHash, $timestamp, $secretKey);
            $dateStamp = substr($timestamp, 0, 8);
            $authorization = sprintf(
                'TE-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
                $publicKey, $signer->getCredentialScope($dateStamp), $signedHeaders, $signature
            );
            $ch = curl_init($baseUrl . $path);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPHEADER     => [
                    'Host: ' . $hostHeader,
                    'X-Te-Date: ' . $timestamp,
                    'Authorization: ' . $authorization,
                ],
            ]);
            $handles[$slug] = $ch;
        }

        $slugs = array_keys($handles);
        for ($offset = 0; $offset < count($slugs); $offset += 25) {
            $batch = array_slice($slugs, $offset, 25);
            $mh    = curl_multi_init();
            foreach ($batch as $s) curl_multi_add_handle($mh, $handles[$s]);
            $running = null;
            do { curl_multi_exec($mh, $running); if ($running > 0) curl_multi_select($mh, 1.0); } while ($running > 0);
            foreach ($batch as $s) {
                $ch   = $handles[$s];
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $body = ($code >= 200 && $code < 300) ? curl_multi_getcontent($ch) : null;
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                $detail = null;
                $coords = ['lat' => null, 'lng' => null, 'precise' => false];
                if ($body !== null) {
                    try {
                        $detail = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                        $apiLat = $detail['latitude']  ?? null;
                        $apiLng = $detail['longitude'] ?? null;
                        if ($apiLat !== null && $apiLng !== null) {
                            $coords = ['lat' => (float)$apiLat, 'lng' => (float)$apiLng, 'precise' => true];
                        }
                    } catch (\JsonException) {}
                }

                // Geocode from full address (street, district, city) if no API coords
                if ($coords['lat'] === null && $detail !== null) {
                    if ($respectRateLimit) usleep(1_100_000);
                    $coords = $this->geocodeAddress($detail);
                }

                Cache::put('prop_coords:' . $s, $coords, now()->addDay());
                $idx = $uncached[$s];
                $items[$idx]['latitude']  = $coords['lat'];
                $items[$idx]['longitude'] = $coords['lng'];
            }
            curl_multi_close($mh);
        }

        return $items;
    }

    public function geocodeAddress(array $detail): array
    {
        $city     = $detail['city']           ?? null;
        $district = $detail['district']       ?? null;
        $street   = $detail['street']         ?? null;
        $building = $detail['buildingNumber'] ?? null;
        $country  = $detail['country']        ?? null;

        if (!$city && !$district && !$country && !$street) {
            return ['lat' => null, 'lng' => null, 'precise' => false];
        }

        $haystack    = ($street ?? '') . ($city ?? '') . ($district ?? '') . ($country ?? '');
        $isArmenian  = (bool) preg_match('/[\x{0530}-\x{058F}]/u', $haystack);
        $extraParams = $isArmenian ? ['countrycodes' => 'am'] : [];

        // Street-level: $building + $street + $city, or $street + $city
        $streetFull = trim(($street ?? '') . ($building ? ' ' . $building : '')) ?: null;

        // [query, precise] — ordered from most to least specific
        $attempts = [];
        if ($streetFull && $city)   $attempts[] = [implode(', ', [$streetFull, $city]),   true];
        if ($street     && $city)   $attempts[] = [implode(', ', [$street,     $city]),   true];
        if ($district   && $city)   $attempts[] = [implode(', ', [$district,   $city]),   false];
        if ($city       && $country) $attempts[] = [implode(', ', [$city,      $country]), false];
        if ($city)                  $attempts[] = [$city,                                  false];
        if ($district)              $attempts[] = [$district,                              false];

        foreach ($attempts as [$query, $isPrecise]) {
            try {
                $ch = curl_init('https://nominatim.openstreetmap.org/search?' . http_build_query(
                    array_merge(['q' => $query, 'format' => 'json', 'limit' => 1], $extraParams)
                ));
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_USERAGENT      => 'TouchEstateDemo/1.0',
                ]);
                $body = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code === 200 && $body) {
                    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                    if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
                        return ['lat' => (float) $data[0]['lat'], 'lng' => (float) $data[0]['lon'], 'precise' => $isPrecise];
                    }
                }
            } catch (\Throwable) {
                // try next attempt
            }
        }

        return ['lat' => null, 'lng' => null, 'precise' => false];
    }

    public function show(string $slug)
    {
        // Shell: returns instantly with no API call.
        // Full property content is loaded async via content() endpoint by JS on the page.
        return view('property-single-shell', compact('slug'));
    }

    /**
     * Async extras for the skeleton-first property page: similar listings + comments.
     * Returned as rendered HTML fragments; the page JS injects them into their skeletons.
     * All API calls cached 1h (no admin panel — data changes rarely).
     */
    public function extras(string $slug)
    {
        try {
            $property = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                $property = $this->client->properties()->retrieve($slug);
                $property['slug'] = $slug;
                $property['fullAddress'] = $this->buildPropertyAddress($property) ?: null;
                return $property;
            });
        } catch (\Exception $e) {
            $property = ['slug' => $slug];
        }

        // Similar = our own Active listings (cached globally 1h — identical for every page),
        // scored/filtered per-property in PHP.
        try {
            $items = Cache::remember('te_all_50', 3600, function () {
                return $this->client->properties()->list(['pageSize' => 50])['items'] ?? [];
            });
            $sorted = collect($items)
                ->filter(fn($p) => ($p['slug'] ?? null) !== $slug)
                ->sortByDesc(function ($p) use ($property) {
                    $score = 0;
                    if (($p['transactionType'] ?? null) === ($property['transactionType'] ?? null)) $score += 2;
                    if (($p['propertyType'] ?? null)    === ($property['propertyType'] ?? null))    $score += 2;
                    if (($p['city'] ?? null)            === ($property['city'] ?? null))            $score += 1;
                    return $score;
                })
                ->values()
                ->all();
            $similar = array_slice($sorted, 0, 6);
        } catch (\Exception $e) {
            $similar = [];
        }

        try {
            $comments = Cache::remember('te_comments:' . $slug, 3600, function () use ($slug) {
                return $this->client->properties()->comments($slug, [
                    'pageNumber' => 1,
                    'pageSize'   => 10,
                    'sortBy'     => 'recent',
                ]);
            });
        } catch (\Exception $e) {
            $comments = ['items' => []];
        }

        return response()->json([
            'similar'  => view('partials.property-single-similar', compact('similar'))->render(),
            'comments' => view('partials.property-single-comments', compact('comments'))->render(),
        ]);
    }

    /**
     * Async main content for skeleton-first property page.
     * Returns the full property content HTML; the shell JS injects it after page opens.
     */
    public function content(string $slug)
    {
        try {
            $property = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                $property = $this->client->properties()->retrieve($slug);
                $property['slug'] = $slug;
                $property['fullAddress'] = $this->buildPropertyAddress($property) ?: null;
                return $property;
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $cached = Cache::get('prop_coords:' . $slug);
        if ($cached === null) {
            $apiLat = is_numeric($property['latitude']  ?? null) ? (float) $property['latitude']  : null;
            $apiLng = is_numeric($property['longitude'] ?? null) ? (float) $property['longitude'] : null;
            if ($apiLat !== null && $apiLng !== null) {
                $cached = ['lat' => $apiLat, 'lng' => $apiLng, 'precise' => true, 'api_checked' => true];
                Cache::put('prop_coords:' . $slug, $cached, now()->addDay());
            } else {
                $cached = ['lat' => null, 'lng' => null];
            }
        }
        $lat = $cached['lat'] ?? null;
        $lng = $cached['lng'] ?? null;

        $html = view('partials.property-single-content', compact('property', 'lat', 'lng'))->render();
        return response()->json(['html' => $html, 'title' => ($property['title'] ?? '')]);
    }

    public function enquire(string $slug)
    {
        $data = request()->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => ['nullable', 'string', 'max:30'],
            'message' => 'required|string|max:1000',
        ]);

        try {
            $property = $this->client->properties()->retrieve($slug);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => 'Property not found'], 404);
        }

        try {
            $this->client->contacts()->inquiry([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'phone'      => preg_replace('/[^\d+]/', '', $data['phone'] ?? ''),
                'message'    => $data['message'] ?? '',
                'propertyId' => $property['id'],
            ]);
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function recordView(string $slug)
    {
        try {
            $result = $this->client->properties()->recordView($slug, request()->ip());
            return response()->json([
                'ok'        => true,
                'viewCount' => $result['viewCount'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'viewCount' => null, 'error' => $e->getMessage()]);
        }
    }
}
