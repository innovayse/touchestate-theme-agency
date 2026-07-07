<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TouchEstate\Sdk\TouchEstateClient;

class CompareController extends Controller
{
    public function __construct(private TouchEstateClient $client)
    {
    }

    public function index(): \Illuminate\View\View
    {
        return view('compare');
    }

    public function load(Request $request): \Illuminate\Http\JsonResponse
    {
        $slugs = array_values(array_unique(array_filter((array) $request->input('slugs', []))));
        $slugs = array_slice($slugs, 0, 4);

        if (empty($slugs)) {
            return response()->json(['html' => '', 'count' => 0, 'slugs' => []]);
        }

        $properties = [];
        $validSlugs = [];

        foreach ($slugs as $slug) {
            if (!is_string($slug) || strlen($slug) > 200) {
                continue;
            }
            try {
                $prop         = $this->client->properties()->retrieve($slug);
                $prop['slug'] = $slug;

                $prop['primaryImageUrl'] = null;
                foreach ($prop['media'] ?? [] as $m) {
                    if ($m['isPrimary'] ?? false) {
                        $prop['primaryImageUrl'] = $m['url'] ?? null;
                        break;
                    }
                }
                if (!$prop['primaryImageUrl'] && !empty($prop['media'])) {
                    $prop['primaryImageUrl'] = $prop['media'][0]['url'] ?? null;
                }

                $addrParts = array_filter([
                    $prop['street']         ?? null,
                    $prop['buildingNumber'] ?? null,
                    $prop['district']       ?? null,
                    $prop['city']           ?? null,
                    $prop['country']        ?? null,
                ]);
                $prop['fullAddress'] = $addrParts ? implode(', ', $addrParts) : null;

                $properties[]  = $prop;
                $validSlugs[]  = $slug;
            } catch (\Exception) {
                // property deleted or not found — skip silently
            }
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

    /**
     * @param  array<int, array<string, mixed>>  $properties
     * @return array<string, array<int, string>>
     */
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
            'Strip'      => 5, 'Panel' => 4, 'Wood' => 3,
        ];

        // Normalize daily rent to monthly so prices are comparable
        $comparablePrice = fn ($p) => ($p['price'] ?? null)
            ? (strtolower($p['transactionType'] ?? '') === 'rentdaily'
                ? (float) $p['price'] * 30
                : (float) $p['price'])
            : null;

        // Only compare prices when all properties are in the same broad category
        $txTypes = array_unique(array_map(
            fn ($p) => strtolower($p['transactionType'] ?? ''),
            $properties
        ));
        $allSale        = array_filter($txTypes, fn ($t) => $t === 'sale');
        $allRent        = array_filter($txTypes, fn ($t) => in_array($t, ['rent', 'rentdaily']));
        $sameTxCategory = count($txTypes) === 1
            || (count($allSale) === 0 && count($allRent) === count($txTypes))
            || (count($allRent) === 0 && count($allSale) === count($txTypes));

        return [
            'price'           => $sameTxCategory
                                    ? $this->bestSlugs($properties, $comparablePrice, 'min')
                                    : [],
            'areaTotal'       => $this->bestSlugs($properties, fn ($p) => ($p['areaTotal']   ?? null) ?: null, 'max'),
            'rooms'           => $this->bestSlugs($properties, fn ($p) => ($p['rooms']        ?? null) ?: null, 'max'),
            'bedrooms'        => $this->bestSlugs($properties, fn ($p) => ($p['bedrooms']     ?? null) ?: null, 'max'),
            'bathrooms'       => $this->bestSlugs($properties, fn ($p) => ($p['bathrooms']    ?? null) ?: null, 'max'),
            'yearBuilt'       => $this->bestSlugs($properties, fn ($p) => ($p['yearBuilt']    ?? null) ?: null, 'max'),
            'floor'           => $this->bestSlugs($properties, function ($p) {
                $f  = $p['floor']       ?? null;
                $ft = $p['floorsTotal'] ?? null;
                if (!$f || !$ft) {
                    return null;
                }

                return min($f - 1, $ft - $f); // golden middle
            }, 'max'),
            'renovationType'  => $this->bestSlugs(
                $properties,
                fn ($p) => $renovationScore[$p['renovationType'] ?? ''] ?? null,
                'max'
            ),
            'constructionType'=> $this->bestSlugs(
                $properties,
                fn ($p) => $constructionScore[$p['constructionType'] ?? ''] ?? null,
                'max'
            ),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>> $properties
     * @return array<int, string>
     */
    private function bestSlugs(array $properties, callable $getValue, string $mode = 'max'): array
    {
        $withVal = array_filter($properties, fn ($p) => $getValue($p) !== null);
        if (count($withVal) < 2) {
            return [];
        }

        $nums = array_map($getValue, array_values($withVal));
        if (count(array_unique($nums)) === 1) {
            return [];
        }

        $best = $mode === 'max' ? max($nums) : min($nums);

        return array_values(array_map(
            fn ($p) => $p['slug'],
            array_filter($withVal, fn ($p) => $getValue($p) === $best)
        ));
    }
}
