<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TouchEstate\Sdk\TouchEstateClient;

class FavoritesController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

    public function index(): \Illuminate\View\View
    {
        return view('favorites');
    }

    public function load(Request $request)
    {
        $slugs = array_values(array_unique(array_filter((array) $request->input('slugs', []))));
        $slugs = array_slice($slugs, 0, 20);

        if (empty($slugs)) {
            return response()->json(['html' => '', 'count' => 0, 'slugs' => []]);
        }

        $properties = [];
        $validSlugs = [];

        foreach ($slugs as $slug) {
            if (!is_string($slug) || strlen($slug) > 200) continue;
            try {
                $prop = $this->client->properties()->retrieve($slug);
                $prop['slug'] = $slug;

                // Build primaryImageUrl from media
                $prop['primaryImageUrl'] = null;
                foreach ($prop['media'] ?? [] as $m) {
                    if ($m['isPrimary'] ?? false) { $prop['primaryImageUrl'] = $m['url'] ?? null; break; }
                }
                if (!$prop['primaryImageUrl'] && !empty($prop['media'])) {
                    $prop['primaryImageUrl'] = $prop['media'][0]['url'] ?? null;
                }

                $addrParts = array_filter([
                    $prop['street'] ?? null, $prop['buildingNumber'] ?? null,
                    $prop['district'] ?? null, $prop['city'] ?? null,
                ]);
                $prop['fullAddress'] = $addrParts ? implode(', ', $addrParts) : null;

                $properties[] = $prop;
                $validSlugs[]  = $slug;
            } catch (\Exception) {}
        }

        $html = view('partials.favorites-grid', compact('properties'))->render();

        return response()->json(['html' => $html, 'count' => count($properties), 'slugs' => $validSlugs]);
    }
}
