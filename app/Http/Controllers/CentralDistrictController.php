<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CentralDistrictController extends Controller
{
    public function __invoke(Request $request)
    {
        $city = trim($request->query('city', ''));
        $lang = $request->query('lang', 'ru');

        if (!$city) {
            return response()->json(['district' => '']);
        }

        $cacheKey = 'central_district:' . $lang . ':' . mb_strtolower($city);
        $district = Cache::remember($cacheKey, 86400, function () use ($city, $lang) {
            $acceptLang = match($lang) {
                'en'    => 'en',
                'hy'    => 'hy,ru',
                default => 'ru,en',
            };

            $client = Http::withHeaders(['User-Agent' => 'RealEstateSite/1.0', 'Accept-Language' => $acceptLang])
                ->withoutVerifying()->timeout(6);

            try {
                $places = $client->get('https://nominatim.openstreetmap.org/search', [
                    'city'   => $city,
                    'format' => 'json',
                    'limit'  => 5,
                ])->json();

                if (empty($places)) return '';

                $place = collect($places)->first(fn($p) => in_array($p['type'] ?? '', [
                    'city', 'town', 'village', 'municipality', 'administrative',
                ])) ?? $places[0];

                $lat = (float) $place['lat'];
                $lon = (float) $place['lon'];

                $extractDistrict = function (array $addr) use ($city): string {
                    $cityName = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['municipality'] ?? '';
                    $skip = [strtolower($cityName), strtolower($city), '', null];
                    foreach (['suburb', 'borough', 'city_district', 'county', 'quarter', 'neighbourhood'] as $key) {
                        $val = $addr[$key] ?? '';
                        if ($val && !in_array(strtolower($val), $skip, true)) return $val;
                    }
                    return '';
                };

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

                if (!$district) {
                    $district = $place['name'] ?? $place['display_name'] ?? $city;
                    if (str_contains($district, ',')) {
                        $district = trim(explode(',', $district)[0]);
                    }
                }

                return $district;
            } catch (\Throwable) {
                return '';
            }
        });

        return response()->json(['district' => $district]);
    }
}
