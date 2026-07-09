<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Cache;

class MapEnrich extends Command
{
    protected $signature   = 'map:enrich';
    protected $description = 'Fetch precise coordinates for all map properties via detail API';

    public function handle(PropertyController $controller): int
    {
        $result = Cache::get('te_map_list');
        if (!$result) {
            $this->error('te_map_list cache is empty — visit /map/data first');
            return 1;
        }

        $allItems = $result['items'] ?? [];
        $toEnrich = [];

        foreach ($allItems as $item) {
            $slug = $item['slug'] ?? null;
            if (!$slug) continue;
            $cached = Cache::get('prop_coords:' . $slug);
            // Skip if already has coords (any precision)
            if ($cached !== null && $cached['lat'] !== null) continue;
            $toEnrich[] = $item;
        }

        if (empty($toEnrich)) {
            $this->info('All coordinates already cached.');
            return 0;
        }

        $this->info('Enriching ' . count($toEnrich) . ' properties...');
        $controller->enrichWithCoordinates($toEnrich, respectRateLimit: true);

        Cache::forget('map_enrich_running');

        $withCoords = 0;
        foreach ($toEnrich as $item) {
            $c = Cache::get('prop_coords:' . ($item['slug'] ?? ''));
            if ($c && $c['lat'] !== null) $withCoords++;
        }

        $this->info("Done. Coords found: {$withCoords}/" . count($toEnrich));
        return 0;
    }
}
