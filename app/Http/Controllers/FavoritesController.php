<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                $prop = Cache::remember('te_prop:' . $slug, 3600, function () use ($slug) {
                    $p = $this->client->properties()->retrieve($slug);
                    $p['slug'] = $slug;
                    return $p;
                });

                $prop['primaryImageUrl'] = $this->extractPrimaryImageUrl($prop);
                $prop['fullAddress'] = $this->buildPropertyAddress($prop) ?: null;

                $properties[] = $prop;
                $validSlugs[]  = $slug;
            } catch (\Exception) {}
        }

        $html = view('partials.favorites-grid', compact('properties'))->render();

        return response()->json(['html' => $html, 'count' => count($properties), 'slugs' => $validSlugs]);
    }
}
