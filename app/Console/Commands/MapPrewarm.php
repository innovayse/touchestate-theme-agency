<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MapPrewarm extends Command
{
    protected $signature   = 'map:prewarm';
    protected $description = 'Pre-warm te_prop:{slug} cache for all map properties';

    public function handle(): int
    {
        $result = Cache::get('te_map_list');
        if (!$result) {
            $this->error('te_map_list cache is empty — visit /map/data first');
            return 1;
        }

        $items = $result['items'] ?? [];
        $warmed = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $slug = $item['slug'] ?? null;
            if (!$slug) continue;

            if (Cache::has('te_prop:' . $slug)) {
                $skipped++;
                continue;
            }

            try {
                Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                    $client   = app(\TouchEstate\Sdk\TouchEstateClient::class);
                    $property = $client->properties()->retrieve($slug);
                    $property['slug'] = $slug;
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
                $warmed++;
            } catch (\Exception $e) {
                // skip failed slugs silently
            }
        }

        Cache::forget('map_prewarm_running');
        $this->info("Done. Warmed: {$warmed}, skipped (already cached): {$skipped}");
        return 0;
    }
}
