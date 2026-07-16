<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ParallelPropertyFetcher;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function __construct(private ParallelPropertyFetcher $fetcher) {}

    public function index()
    {
        return view('compare');
    }

    public function load(Request $request)
    {
        // Release session lock immediately so other browser requests
        // (CSS, JS, favicon) are not blocked during API calls.
        session()->save();

        $slugs = array_values(array_unique(array_filter((array) $request->input('slugs', []))));
        $slugs = array_slice($slugs, 0, 10);
        $slugs = array_values(array_filter($slugs, fn($s) => is_string($s) && strlen($s) <= 200));

        if (empty($slugs)) {
            return response()->json(['html' => '', 'count' => 0, 'slugs' => []]);
        }

        $fetched    = $this->fetcher->fetchMany($slugs);
        $properties = [];
        $validSlugs = [];

        foreach ($slugs as $slug) {
            if (!isset($fetched[$slug])) {
                continue;
            }
            $prop                    = $fetched[$slug];
            $prop['primaryImageUrl'] = $this->extractPrimaryImageUrl($prop);
            $prop['fullAddress']     = $this->buildPropertyAddress($prop) ?: null;
            $properties[]            = $prop;
            $validSlugs[]            = $slug;
        }

        $highlights = $this->computeHighlights($properties);

        $html = view('partials.compare-table', compact('properties', 'highlights'))->render();

        return response()->json([
            'html'  => $html,
            'count' => count($properties),
            'slugs' => $validSlugs,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Highlights
    // ─────────────────────────────────────────────────────────────

    private function computeHighlights(array $properties): array
    {
        if (count($properties) < 2) {
            return [];
        }

        $renovationScore = [
            'Designer' => 10, 'Capital' => 9, 'Euro' => 8,
            'Cosmetic' => 6,  'Partial' => 4, 'Old'  => 2, 'Unrenovated' => 1,
        ];
        $constructionScore = [
            'Monolithic' => 10, 'Stone' => 9, 'Brick' => 8,
            'Strip' => 5, 'Panel' => 4, 'Wood' => 3,
        ];

        // Normalize daily rent to monthly so prices are comparable
        $comparablePrice = fn($p) => ($p['price'] ?? null)
            ? (strtolower($p['transactionType'] ?? '') === 'rentdaily'
                ? (float) $p['price'] * 30
                : (float) $p['price'])
            : null;

        // Only compare prices when all properties are in the same broad category
        $txTypes = array_unique(array_map(
            fn($p) => strtolower($p['transactionType'] ?? ''), $properties
        ));
        $allSale  = array_filter($txTypes, fn($t) => $t === 'sale');
        $allRent  = array_filter($txTypes, fn($t) => in_array($t, ['rent', 'rentdaily']));
        $sameTxCategory = count($txTypes) === 1
            || (count($allSale) === 0 && count($allRent) === count($txTypes))
            || (count($allRent) === 0 && count($allSale) === count($txTypes));

        return [
            'price'           => $sameTxCategory
                                    ? $this->bestSlugs($properties, $comparablePrice, 'min')
                                    : [],
            'areaTotal'       => $this->bestSlugs($properties, fn($p) => ($p['areaTotal']   ?? null) ?: null, 'max'),
            'rooms'           => $this->bestSlugs($properties, fn($p) => ($p['rooms']        ?? null) ?: null, 'max'),
            'bedrooms'        => $this->bestSlugs($properties, fn($p) => ($p['bedrooms']     ?? null) ?: null, 'max'),
            'bathrooms'       => $this->bestSlugs($properties, fn($p) => ($p['bathrooms']    ?? null) ?: null, 'max'),
            'yearBuilt'       => $this->bestSlugs($properties, fn($p) => ($p['yearBuilt']    ?? null) ?: null, 'max'),
            'floor'           => $this->bestSlugs($properties, function ($p) {
                $f  = $p['floor']       ?? null;
                $ft = $p['floorsTotal'] ?? null;
                if (!$f || !$ft) return null;
                return min($f - 1, $ft - $f); // golden middle
            }, 'max'),
            'renovationType'  => $this->bestSlugs($properties,
                fn($p) => $renovationScore[$p['renovationType'] ?? ''] ?? null, 'max'),
            'constructionType'=> $this->bestSlugs($properties,
                fn($p) => $constructionScore[$p['constructionType'] ?? ''] ?? null, 'max'),
        ];
    }

    private function bestSlugs(array $properties, callable $getValue, string $mode = 'max'): array
    {
        $withVal = array_filter($properties, fn($p) => $getValue($p) !== null);
        if (count($withVal) < 2) return [];

        $nums = array_map($getValue, array_values($withVal));
        if (count(array_unique($nums)) === 1) return [];

        $best = $mode === 'max' ? max($nums) : min($nums);

        return array_values(array_map(
            fn($p) => $p['slug'],
            array_filter($withVal, fn($p) => $getValue($p) === $best)
        ));
    }
}
