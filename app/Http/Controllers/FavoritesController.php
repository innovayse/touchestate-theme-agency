<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use TouchEstate\Sdk\Exception\InvalidRequestException;
use TouchEstate\Sdk\Exception\RateLimitException;
use TouchEstate\Sdk\TouchEstateClient;

class FavoritesController extends Controller
{
    public function __construct(private TouchEstateClient $client)
    {
    }

    public function index(): View
    {
        return view('favorites');
    }

    public function load(Request $request): \Illuminate\Http\JsonResponse
    {
        $slugs = $request->input('slugs', []);

        if (!is_array($slugs) || empty($slugs)) {
            return response()->json(['html' => '', 'count' => 0, 'slugs' => [], 'pending' => []]);
        }

        // De-dupe and drop empties before limiting (parity with compare) — otherwise a
        // duplicated slug renders the same card twice.
        $slugs = array_values(array_unique(array_filter($slugs, static fn ($s) => is_string($s) && $s !== '')));

        // Limit to 50 to prevent abuse. Favourites fan out one retrieve() per slug, and the
        // per-slug retries below can add up, so give the request some headroom.
        $slugs = array_slice($slugs, 0, 50);
        set_time_limit(60);

        $properties   = [];
        $foundSlugs   = [];
        $pendingSlugs = [];

        foreach ($slugs as $slug) {
            if (!is_string($slug) || $slug === '') {
                continue;
            }

            try {
                $prop = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                    $p = $this->retrieveWithRetry($slug);

                    // Empty/ghost stub: an unknown or incomplete listing the API answers with a
                    // blank object instead of a 404. A showable listing has both id and title.
                    // A distinct exception type marks this as "drop" (vs a transient API error);
                    // throwing also keeps the empty result OUT of the cache.
                    if (!is_array($p) || empty($p['id']) || empty($p['title'])) {
                        throw new \UnexpectedValueException('Empty property for slug ' . $slug);
                    }

                    $p['slug'] = $slug;

                    // retrieve() returns a `media` array but no ready primaryImageUrl (only
                    // list() provides that). Compute it here — same as compare — otherwise the
                    // property-card renders its no-image placeholder.
                    $p['primaryImageUrl'] = null;
                    foreach ($p['media'] ?? [] as $m) {
                        if ($m['isPrimary'] ?? false) {
                            $p['primaryImageUrl'] = $m['url'] ?? null;
                            break;
                        }
                    }
                    if (!$p['primaryImageUrl'] && !empty($p['media'])) {
                        $p['primaryImageUrl'] = $p['media'][0]['url'] ?? null;
                    }

                    $addrParts = array_filter([
                        $p['street']         ?? null,
                        $p['buildingNumber'] ?? null,
                        $p['district']       ?? null,
                        $p['city']           ?? null,
                        $p['country']        ?? null,
                    ]);
                    if ($addrParts) {
                        $p['fullAddress'] = implode(', ', $addrParts);
                    }

                    return $p;
                });
            } catch (InvalidRequestException | \UnexpectedValueException) {
                // Genuine 404 / gone, or empty ghost stub → drop the slug from favorites.
                continue;
            } catch (\Throwable) {
                // Transient failure (429 rate limit, 5xx, 401, network). DO NOT drop the
                // favorite — keep the slug so it survives the localStorage sync, and mark it
                // pending so the client re-requests just these once the burst subsides. Without
                // that the card silently vanishes while still counted as favorited (desync).
                $foundSlugs[]   = $slug;
                $pendingSlugs[] = $slug;
                continue;
            }

            // Guard against a previously-cached empty result (from before this check existed).
            if (!is_array($prop) || empty($prop['id']) || empty($prop['title'])) {
                Cache::forget('te_prop:' . $slug);
                continue;
            }

            $properties[] = $prop;
            $foundSlugs[] = $slug;
        }

        $html = '';
        foreach ($properties as $prop) {
            $html .= view('components.property-card', ['prop' => $prop])->render();
        }

        return response()->json([
            'html'    => $html,
            'count'   => count($properties),
            'slugs'   => $foundSlugs,
            'pending' => $pendingSlugs,
        ]);
    }

    /**
     * Retrieve a property, retrying on rate-limit (429) with a short backoff. Favourites
     * load many slugs in one request; without this the burst trips the API rate limit and
     * cards silently vanish. Results are cached (1h), so this only bites the first load.
     *
     * @return array<string, mixed>
     */
    private function retrieveWithRetry(string $slug, int $attempts = 3): array
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $this->client->properties()->retrieve($slug);
            } catch (RateLimitException $e) {
                if ($attempt >= $attempts) {
                    throw $e;
                }
                usleep(400_000); // 0.4s before retrying
            }
        }
    }
}
