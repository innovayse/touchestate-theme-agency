<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ParallelPropertyFetcher;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function __construct(private ParallelPropertyFetcher $fetcher) {}

    public function index(): \Illuminate\View\View
    {
        return view('favorites');
    }

    public function load(Request $request): \Illuminate\Http\JsonResponse
    {
        // Release session lock so other browser requests don't queue behind API calls.
        session()->save();

        $slugs = array_values(array_unique(array_filter((array) $request->input('slugs', []))));
        $slugs = array_slice($slugs, 0, 20);
        $slugs = array_values(array_filter($slugs, fn($s) => is_string($s) && strlen($s) <= 200));

        if (empty($slugs)) {
            return response()->json(['items' => [], 'validSlugs' => []]);
        }

        $fetched    = $this->fetcher->fetchMany($slugs);
        $items      = [];
        $validSlugs = [];

        foreach ($slugs as $slug) {
            if (!isset($fetched[$slug])) {
                continue;
            }
            $prop                    = $fetched[$slug];
            $prop['primaryImageUrl'] = $this->extractPrimaryImageUrl($prop);
            $prop['fullAddress']     = $this->buildPropertyAddress($prop) ?: null;

            $items[]      = ['slug' => $slug, 'html' => view('components.property-card', ['prop' => $prop])->render()];
            $validSlugs[] = $slug;
        }

        return response()->json(['items' => $items, 'validSlugs' => $validSlugs]);
    }
}
