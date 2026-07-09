<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GeocodePropertyJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(
        private string $slug,
        private array  $detail,
    ) {}

    public function handle(): void
    {
        if (Cache::has('prop_coords:' . $this->slug)) {
            return;
        }

        $coords = $this->geocode($this->detail);
        Cache::put('prop_coords:' . $this->slug, $coords, now()->addDay());
    }

    private function geocode(array $detail): array
    {
        $city     = $detail['city']           ?? null;
        $district = $detail['district']       ?? null;
        $street   = $detail['street']         ?? null;
        $building = $detail['buildingNumber'] ?? null;

        if (!$city && !$district) {
            return ['lat' => null, 'lng' => null];
        }

        $cleanDistrict = $district ? preg_replace('/\s+վարчական\s+շрджан$/u', '', $district) : null;
        $cleanStreet   = $street   ? preg_replace('/\s+փողоц$/u', '', $street) : null;

        $attempts = array_filter([
            $cleanStreet ? implode(', ', array_filter([$cleanStreet, $building, $cleanDistrict, $city])) : null,
            $cleanDistrict ? implode(', ', array_filter([$cleanDistrict, $city])) : null,
            $city,
        ]);

        foreach ($attempts as $query) {
            $result = $this->nominatim($query);
            if ($result) {
                return $result;
            }
        }

        return ['lat' => null, 'lng' => null];
    }

    private function nominatim(string $query): ?array
    {
        try {
            $ch = curl_init('https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $query, 'format' => 'json', 'limit' => 1,
            ]));
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
                    return ['lat' => (float) $data[0]['lat'], 'lng' => (float) $data[0]['lon']];
                }
            }
        } catch (\Throwable) {}

        return null;
    }
}
