<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use TouchEstate\Sdk\TouchEstateClient;

class FavoritesController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

    public function index()
    {
        return view('favorites');
    }

    public function load(Request $request)
    {
        $slugs = $request->input('slugs', []);

        if (!is_array($slugs) || empty($slugs)) {
            return response()->json(['html' => '', 'count' => 0, 'slugs' => []]);
        }

        // Limit to 50 to prevent abuse
        $slugs = array_slice(array_values($slugs), 0, 50);

        $properties = [];
        $foundSlugs = [];

        foreach ($slugs as $slug) {
            if (!is_string($slug) || empty($slug)) {
                continue;
            }
            try {
                $prop = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                    $p = $this->client->properties()->retrieve($slug);
                    $p['slug'] = $slug;
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

                $properties[] = $prop;
                $foundSlugs[] = $slug;
            } catch (\Exception) {
                // Property no longer exists — drop it from localStorage
            }
        }

        $html = '';
        foreach ($properties as $prop) {
            $html .= view('components.property-card', ['prop' => $prop])->render();
        }

        return response()->json([
            'html'  => $html,
            'count' => count($properties),
            'slugs' => $foundSlugs,
        ]);
    }
}
