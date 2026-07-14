<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SuggestController extends Controller
{
    public function __invoke(Request $request)
    {
        $q      = trim($request->query('q', ''));
        $locale = $request->query('lang', 'ru');

        if (\strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $yLang    = $locale === 'en' ? 'en_US' : 'ru_RU';
        $cacheKey = 'suggest:' . $locale . ':' . md5($q);

        $results = Cache::remember($cacheKey, 3600, function () use ($q, $yLang) {
            try {
                $resp = Http::withHeaders(['Referer' => config('app.url')])->withoutVerifying()->timeout(5)
                    ->get('https://suggest-maps.yandex.ru/suggest-geo', [
                        'apikey'    => config('services.yandex.maps_key'),
                        'text'      => $q,
                        'lang'      => $yLang,
                        'results'   => 7,
                        'highlight' => 0,
                        'v'         => 9,
                    ]);

                $body = trim($resp->body());
                $json = preg_replace('/^suggest\.apply\((.+)\)$/s', '$1', $body);
                $data = json_decode($json, true);

                $results = [];
                foreach (($data['results'] ?? []) as $item) {
                    $title      = $item['title']['text']   ?? '';
                    $where      = $item['log_id']['where'] ?? [];
                    $whereTitle = $where['title']           ?? '';
                    $whereName  = $where['name']            ?? '';

                    if (!$title || $title !== $whereTitle) continue;

                    $parts = array_filter(array_map('trim', explode(',', $whereName)));
                    $desc  = implode(', ', array_values(array_filter($parts, fn($p) => $p !== $title)));

                    $results[] = ['name' => $title, 'desc' => $desc];
                }

                return $results;
            } catch (\Throwable) {
                return [];
            }
        });

        return response()->json(['results' => $results]);
    }
}
