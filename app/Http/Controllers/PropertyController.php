<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
        foreach (['propertyType', 'transactionType', 'city', 'district', 'currency',
                  'renovationType', 'constructionType', 'furnitureType', 'petsPolicy', 'childrenPolicy',
                  'balconyType', 'terraceType'] as $key) {
            if (request()->filled($key)) {
                $params[$key] = request($key);
            }
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

    public function index()
    {
        $this->validateFilters();

        // Cache each unique filter/page combination 1h (no admin panel). Repeat listings
        // (and the AJAX filter/load-more fetches) become instant.
        $filters = $this->buildFilters();
        try {
            $properties = Cache::remember('te_list:' . md5(serialize($filters)), 3600, function () use ($filters) {
                return $this->client->properties()->list($filters);
            });
        } catch (\Exception $e) {
            $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
        }

        return view('property', compact('properties'));
    }

    public function map()
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
        $baseUrl   = rtrim(config('touchestate.base_url', env('TOUCHESTATE_BASE_URL', '')), '/');
        $publicKey = config('touchestate.public_key', env('TOUCHESTATE_PUBLIC_KEY', ''));
        $secretKey = config('touchestate.secret_key', env('TOUCHESTATE_SECRET_KEY', ''));

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

            Cache::put('prop_coords:' . $slug, $coords, now()->addHour());

            $idx                      = $uncached[$slug];
            $items[$idx]['latitude']  = $coords['lat'];
            $items[$idx]['longitude'] = $coords['lng'];
        }

        return $items;
    }

    public function show(string $slug)
    {
        set_time_limit(60);

        // Cached 1h (no admin panel — property data changes rarely). Cache the fully-built
        // array; on API failure the exception propagates out of remember() → not cached → 404.
        try {
            $property = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                $property         = $this->client->properties()->retrieve($slug);
                $property['slug'] = $slug;

                // Build fullAddress from components (retrieve() doesn't return it)
                $addrParts = array_filter([
                    $property['street']         ?? null,
                    $property['buildingNumber'] ?? null,
                    $property['district']       ?? null,
                    $property['city']           ?? null,
                    $property['country']        ?? null,
                ]);
                if ($addrParts) {
                    $property['fullAddress'] = implode(', ', $addrParts);
                }

                return $property;
            });
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
     * All API calls cached 1h (no admin panel — data changes rarely).
     */
    public function extras(string $slug)
    {
        try {
            $property = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                $property         = $this->client->properties()->retrieve($slug);
                $property['slug'] = $slug;
                $addrParts        = array_filter([
                    $property['street']         ?? null,
                    $property['buildingNumber'] ?? null,
                    $property['district']       ?? null,
                    $property['city']           ?? null,
                    $property['country']        ?? null,
                ]);
                if ($addrParts) {
                    $property['fullAddress'] = implode(', ', $addrParts);
                }

                return $property;
            });
        } catch (\Exception $e) {
            $property = ['slug' => $slug];
        }

        // Similar = our own Active listings (cached globally 1h — identical for every page),
        // scored/filtered per-property in PHP.
        try {
            $items = Cache::remember('te_active_50', 3600, function () {
                return $this->client->properties()->list(['pageSize' => 50, 'status' => 'Active'])['items'] ?? [];
            });
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

    public function enquire(string $slug)
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
