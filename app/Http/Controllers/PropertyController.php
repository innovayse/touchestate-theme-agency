<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use App\Support\TeCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use TouchEstate\Sdk\Signer\SignatureV4Signer;
use TouchEstate\Sdk\TouchEstateClient;

class PropertyController extends Controller
{
    public function __construct(private TouchEstateClient $client)
    {
    }

    private function validateFilters(): void
    {
        request()->validate([
            // status is always Active on public endpoint — reject if someone tries to override
            'status'           => 'prohibited',

            // Enum singles
            'propertyType'     => ['nullable', Rule::in(['Apartment','House','Studio','Villa','Townhouse','Penthouse','Room','Complex','Land','Commercial','Office','Warehouse','Garage','Pavilion','EventVenue','Dacha','Cottage'])],
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

    /** @return array<string, mixed> */
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
        // NOTE: 'currency' + minPrice/maxPrice are intentionally NOT added here — the
        // price range is currency-aware and handled in index() (multi-currency search).
        foreach (['propertyType', 'transactionType', 'city', 'district',
                  'renovationType', 'constructionType', 'furnitureType', 'petsPolicy', 'childrenPolicy',
                  'balconyType', 'terraceType'] as $key) {
            if (request()->filled($key)) {
                $params[$key] = request($key);
            }
        }

        // Numeric range filters (price handled separately — see index())
        foreach (['minRooms', 'maxRooms', 'minBedrooms', 'maxBedrooms', 'minBathrooms', 'maxBathrooms', 'minFloor', 'maxFloor', 'minLandArea', 'maxLandArea'] as $key) {
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

    /** Listing page size — one server page = one "Load More" step. */
    private const PER_PAGE = 21;

    public function index(): \Illuminate\View\View
    {
        $this->validateFilters();

        $properties = $this->fetchPage();

        return view('property', compact('properties'));
    }

    /**
     * AJAX endpoint for "Load More": returns the rendered card partial for the
     * requested page plus pagination meta, so the client can append and keep going.
     */
    public function loadMore(): \Illuminate\Http\JsonResponse
    {
        $this->validateFilters();

        $properties = $this->fetchPage();
        $html       = view('partials.property-cards', [
            'properties' => ['items' => $properties['items']],
        ])->render();

        return response()->json([
            'html'        => $html,
            'hasNextPage' => $properties['hasNextPage'],
            'count'       => count($properties['items']),
            'total'       => $properties['totalCount'],
        ]);
    }

    /**
     * Fetch one display page (see PER_PAGE) for the current request, honouring the
     * `page` query param. Returns ['items', 'totalCount', 'hasNextPage'].
     *
     * Both modes build the FULL matched, deduplicated set once (cached) and slice the
     * requested page in-memory. We deliberately do NOT rely on the API's own paging:
     * the default ordering is not stable across independent page requests (equal
     * createdAt values shift page boundaries), so item-by-item paging would drop and
     * duplicate results. Slicing a stable in-memory list guarantees complete,
     * non-overlapping pages.
     *
     *  - No price range → single query (all API pages), API sort order preserved.
     *  - Price range    → multi-currency search, merged + globally re-sorted.
     */
    /** @return array{items: array<int, array<string, mixed>>, totalCount: int, hasNextPage: bool} */
    private function fetchPage(): array
    {
        $base   = $this->buildFilters();
        $page   = max(1, (int) request('page', 1));
        $hasMin = request()->filled('minPrice');
        $hasMax = request()->filled('maxPrice');

        if (!$hasMin && !$hasMax) {
            // No price range → one query across ALL currencies (no currency restriction).
            $all = $this->fetchAll($base);
        } else {
            // Price range is expressed in the visitor's SEARCH currency (filter dropdown,
            // falling back to the header display currency). We convert that range into
            // every supported currency and query each one, then merge — so an object
            // priced in any currency is matched by its equivalent value.
            $merged = $this->fetchMergedAll($base, $hasMin, $hasMax);

            if ($merged !== null) {
                $all = $this->sortMerged($merged);
            } else {
                // Rates unavailable (e.g. CBA down) → degrade to a single query in the
                // search currency with the raw values, so search still works.
                $supported      = config('currency.supported', []);
                $searchCurrency = request('currency') ?: display_currency();
                if (!in_array($searchCurrency, $supported, true)) {
                    $searchCurrency = config('currency.default');
                }
                $params             = $base;
                $params['currency'] = $searchCurrency;
                if ($hasMin) {
                    $params['minPrice'] = (int) request('minPrice');
                }
                if ($hasMax) {
                    $params['maxPrice'] = (int) request('maxPrice');
                }
                $all = $this->fetchAll($params);
            }
        }

        $total  = count($all);
        $offset = ($page - 1) * self::PER_PAGE;

        return [
            'items'       => array_slice($all, $offset, self::PER_PAGE),
            'totalCount'  => $total,
            'hasNextPage' => ($offset + self::PER_PAGE) < $total,
        ];
    }

    /**
     * Fetch the FULL result set for a single query (all API pages, largest page size),
     * deduplicated by id, preserving the API's own sort order. Each page is cached.
     */
    /**
     * @param  array<string, mixed>             $base
     * @return array<int, array<string, mixed>>
     */
    private function fetchAll(array $base): array
    {
        $params             = $base;
        $params['pageSize'] = 100; // largest allowed page → fewest boundary seams

        $all  = [];
        $seen = [];
        for ($p = 1; $p <= 20; $p++) { // cap as a runaway safety net
            $params['pageNumber'] = $p;
            $result               = $this->cachedList($params);
            foreach ($result['items'] ?? [] as $item) {
                $id = $item['id'] ?? null;
                if ($id !== null) {
                    if (isset($seen[$id])) {
                        continue;
                    }
                    $seen[$id] = true;
                }
                $all[] = $item;
            }
            if (!($result['hasNextPage'] ?? false)) {
                break;
            }
        }

        return $all;
    }

    /**
     * Multi-currency price search: fetch the FULL matched set across every supported
     * currency (all API pages), merged into one flat array. Returns null if exchange
     * rates are unavailable (caller falls back to a single-currency query).
     */
    /**
     * @param  array<string, mixed>                  $base
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchMergedAll(array $base, bool $hasMin, bool $hasMax): ?array
    {
        $supported      = config('currency.supported', []);
        $searchCurrency = request('currency') ?: display_currency();
        if (!in_array($searchCurrency, $supported, true)) {
            $searchCurrency = config('currency.default');
        }

        $minInput = $hasMin ? (int) request('minPrice') : null;
        $maxInput = $hasMax ? (int) request('maxPrice') : null;
        $rates    = app(ExchangeRateService::class);

        // Dedup by id: the API's `currency` param scopes only the price comparison,
        // not the result set, so the same object can surface under several currencies.
        $merged = [];
        $seen   = [];
        foreach ($supported as $currency) {
            $params             = $base;
            $params['currency'] = $currency;
            $params['pageSize'] = 100; // fetch in the largest allowed pages

            if ($minInput !== null) {
                $converted = $rates->convert($minInput, $searchCurrency, $currency);
                if ($converted === null) {
                    return null;
                }
                $params['minPrice'] = (int) floor($converted);
            }
            if ($maxInput !== null) {
                $converted = $rates->convert($maxInput, $searchCurrency, $currency);
                if ($converted === null) {
                    return null;
                }
                $params['maxPrice'] = (int) ceil($converted);
            }

            // Walk every page for this currency (cap as a runaway safety net).
            for ($p = 1; $p <= 20; $p++) {
                $params['pageNumber'] = $p;
                $result               = $this->cachedList($params);
                foreach ($result['items'] ?? [] as $item) {
                    $id = $item['id'] ?? null;
                    if ($id !== null) {
                        if (isset($seen[$id])) {
                            continue;
                        }
                        $seen[$id] = true;
                    }
                    $merged[] = $item;
                }
                if (!($result['hasNextPage'] ?? false)) {
                    break;
                }
            }
        }

        return $merged;
    }

    /**
     * Fetch a property list for the given filters, cached 1h (no admin panel).
     * On API failure returns an empty result instead of throwing.
     */
    /**
     * @param  array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function cachedList(array $filters): array
    {
        try {
            return Cache::remember(TeCache::key('te_list:' . md5(serialize($filters))), 1800, function () use ($filters) {
                return $this->client->properties()->list($filters);
            });
        } catch (\Exception $e) {
            return ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
        }
    }

    /**
     * Re-sort a merged (multi-currency) result set. The API sorts within each currency
     * query, so the concatenation must be globally re-sorted. Price sort compares values
     * converted into the display currency; otherwise newest-first by createdAt.
     */
    /**
     * @param  array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function sortMerged(array $items): array
    {
        $descending = request('sortOrder') !== 'asc'; // default: descending

        if (request('sortBy') === 'price') {
            $svc     = app(ExchangeRateService::class);
            $display = display_currency();
            usort($items, function ($a, $b) use ($svc, $display, $descending) {
                $pa = $svc->convert((float) ($a['price'] ?? 0), $a['currency'] ?? $display, $display) ?? ($a['price'] ?? 0);
                $pb = $svc->convert((float) ($b['price'] ?? 0), $b['currency'] ?? $display, $display) ?? ($b['price'] ?? 0);

                return $descending ? $pb <=> $pa : $pa <=> $pb;
            });
        } else {
            usort($items, fn ($a, $b) => strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? ''));
        }

        return $items;
    }

    public function map(): \Illuminate\View\View
    {
        try {
            $result = $this->client->properties()->list([
                'pageNumber' => 1,
                'pageSize'   => 100,
                'status'     => 'Active',
            ]);
            $items      = $this->enrichWithCoordinates($result['items'] ?? []);
            $properties = array_merge($result, ['items' => $items]);
        } catch (\Exception $e) {
            $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
        }

        return view('map', compact('properties'));
    }

    /**
     * Fetch latitude/longitude for each property via parallel curl_multi requests, with caching.
     *
     * @param  array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function enrichWithCoordinates(array $items): array
    {
        // Step 1: Separate cached from uncached
        $uncached = []; // slug → index in $items
        foreach ($items as $i => &$item) {
            $slug = $item['slug'] ?? null;
            if (!$slug) {
                continue;
            }

            $cacheKey = 'prop_coords:' . $slug;
            $coords   = Cache::get($cacheKey);

            if ($coords !== null) {
                $item['latitude']  = $coords['lat'];
                $item['longitude'] = $coords['lng'];
            } else {
                $uncached[$slug] = $i;
            }
        }
        unset($item);

        if (empty($uncached)) {
            return $items;
        }

        // Step 2: Build signed curl handles for all uncached slugs
        $signer    = new SignatureV4Signer();
        $baseUrl   = rtrim((string) config('touchestate.base_url', ''), '/');
        $publicKey = (string) config('touchestate.public_key', '');
        $secretKey = (string) config('touchestate.secret_key', '');

        $urlParts    = parse_url($baseUrl);
        $host        = $urlParts['host']   ?? 'localhost';
        $port        = $urlParts['port']   ?? null;
        $scheme      = $urlParts['scheme'] ?? 'https';
        $defaultPort = ($scheme === 'https') ? 443 : 80;
        $hostHeader  = ($port !== null && $port !== $defaultPort)
            ? sprintf('%s:%d', $host, $port)
            : $host;

        $handles = []; // slug → curl handle

        foreach (array_keys($uncached) as $slug) {
            $path      = '/api/external/properties/' . $slug;
            $uri       = $baseUrl . $path;
            $timestamp = gmdate('Ymd\THis\Z');
            $bodyHash  = $signer->sha256Hex('');

            $headers = [
                'host'      => $hostHeader,
                'x-te-date' => $timestamp,
            ];

            $signedHeaderNames = array_keys($headers);
            sort($signedHeaderNames);
            $signedHeaders = implode(';', $signedHeaderNames);

            $signature = $signer->calculateSignature(
                'GET',
                $path,
                '',
                $headers,
                $signedHeaders,
                $bodyHash,
                $timestamp,
                $secretKey
            );

            $dateStamp       = substr($timestamp, 0, 8);
            $credentialScope = $signer->getCredentialScope($dateStamp);
            $authorization   = sprintf(
                'TE-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
                $publicKey,
                $credentialScope,
                $signedHeaders,
                $signature
            );

            $ch = curl_init($uri);
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

        // Step 3: Execute in parallel with curl_multi (max 25 concurrent)
        $maxConcurrent = 25;
        $slugs         = array_keys($handles);
        $results       = []; // slug → json string or null

        for ($offset = 0; $offset < count($slugs); $offset += $maxConcurrent) {
            $batch = array_slice($slugs, $offset, $maxConcurrent);
            $mh    = curl_multi_init();

            foreach ($batch as $slug) {
                curl_multi_add_handle($mh, $handles[$slug]);
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                if ($running > 0) {
                    curl_multi_select($mh, 1.0);
                }
            } while ($running > 0);

            foreach ($batch as $slug) {
                $ch             = $handles[$slug];
                $code           = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $body           = ($code >= 200 && $code < 300) ? curl_multi_getcontent($ch) : null;
                $results[$slug] = $body;
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }

            curl_multi_close($mh);
        }

        // Step 4: Parse responses, cache, and merge into items
        foreach ($results as $slug => $body) {
            $coords = ['lat' => null, 'lng' => null];

            if ($body !== null) {
                try {
                    $detail = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                    $coords = [
                        'lat' => $detail['latitude']  ?? null,
                        'lng' => $detail['longitude'] ?? null,
                    ];
                } catch (\JsonException $e) {
                    // keep null coords
                }
            }

            Cache::put('prop_coords:' . $slug, $coords, now()->addMinutes(30));

            $idx                      = $uncached[$slug];
            $items[$idx]['latitude']  = $coords['lat'];
            $items[$idx]['longitude'] = $coords['lng'];
        }

        return $items;
    }

    /**
     * Retrieve + normalize a property, cached 30 min under the shared te_prop:{slug} key.
     * Ghost stubs (no id/title) throw UnexpectedValueException so they never enter the cache
     * and callers can 404 or fall back. Used by both show() and extras().
     */
    private function cachedProperty(string $slug): array
    {
        return Cache::remember(TeCache::prop($slug), 1800, function () use ($slug) {
            $p = $this->client->properties()->retrieve($slug);

            if (!property_is_showable($p)) {
                throw new \UnexpectedValueException('Empty property for slug ' . $slug);
            }

            return normalize_property($p, $slug);
        });
    }

    public function show(string $slug): \Illuminate\View\View
    {
        set_time_limit(60);

        // On API failure / ghost stub the exception propagates out → not cached → 404.
        try {
            $property = $this->cachedProperty($slug);
        } catch (\Exception $e) {
            abort(404);
        }

        // Skeleton-first: similar + comments are loaded async via extras() after the shell
        // renders, so the page (with SEO head + core) appears instantly.
        return view('property-single', compact('property'));
    }

    /**
     * Async extras for the skeleton-first property page: similar listings + comments.
     * Returned as rendered HTML fragments; the page JS injects them into their skeletons.
     * All API calls cached 30 min (no admin panel — data changes rarely).
     */
    public function extras(string $slug): \Illuminate\Http\JsonResponse
    {
        try {
            $property = $this->cachedProperty($slug);
        } catch (\Exception $e) {
            $property = ['slug' => $slug];
        }

        // Similar = our own Active listings (cached globally 30 min — identical for every page),
        // scored/filtered per-property in PHP.
        try {
            $items = Cache::remember(TeCache::key('te_active_50'), 1800, function () {
                return $this->client->properties()->list(['pageSize' => 50, 'status' => 'Active'])['items'] ?? [];
            });
            /** @var array<int, array<string, mixed>> $items */
            $similar = collect($items)
                ->reject(fn ($p) => ($p['slug'] ?? null) === $slug)
                ->sortByDesc(function ($p) use ($property) {
                    $score = 0;
                    if (($p['transactionType'] ?? null) === ($property['transactionType'] ?? null)) {
                        $score += 2;
                    }
                    if (($p['propertyType'] ?? null)    === ($property['propertyType'] ?? null)) {
                        $score += 2;
                    }
                    if (($p['city'] ?? null)            === ($property['city'] ?? null)) {
                        $score += 1;
                    }

                    return $score;
                })
                ->take(6)
                ->values()
                ->all();
        } catch (\Exception $e) {
            $similar = [];
        }

        try {
            $comments = Cache::remember('te_comments:' . $slug, 1800, function () use ($slug) {
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

    public function enquire(string $slug): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => ['nullable', 'string', 'max:30', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'message' => 'nullable|string|max:1000',
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

    public function recordView(string $slug): \Illuminate\Http\JsonResponse
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
