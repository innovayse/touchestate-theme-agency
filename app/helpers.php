<?php

use App\Services\ExchangeRateService;

if (!function_exists('display_currency')) {
    /**
     * The visitor's chosen display currency (session), validated against config.
     */
    function display_currency(): string
    {
        $default   = config('currency.default');
        $supported = config('currency.supported', []);
        $currency  = session('currency', $default);

        return in_array($currency, $supported, true) ? $currency : $default;
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Display symbol for an ISO code (falls back to the code itself).
     */
    function currency_symbol(string $currency): string
    {
        return config('currency.symbols.' . $currency, $currency);
    }
}

if (!function_exists('convert_price')) {
    /**
     * Convert an amount from its native currency into the visitor's display currency.
     * Returns ['amount' => float, 'currency' => string, 'converted' => bool].
     * If conversion is unavailable, keeps the original amount/currency.
     *
     * @return array{amount: float, currency: string, converted: bool}
     */
    function convert_price(float|int $amount, ?string $from): array
    {
        $to     = display_currency();
        $from   = $from ?: $to;
        $amount = (float) $amount;

        if ($from === $to) {
            return ['amount' => $amount, 'currency' => $to, 'converted' => false];
        }

        $result = app(ExchangeRateService::class)->convert($amount, $from, $to);
        if ($result === null) {
            return ['amount' => $amount, 'currency' => $from, 'converted' => false];
        }

        return ['amount' => $result, 'currency' => $to, 'converted' => true];
    }
}

if (!function_exists('format_price')) {
    /**
     * Format an amount in the visitor's display currency, e.g. "1,234,000 USD".
     * Keeps the project's existing "<number> <ISO code>" rendering.
     */
    function format_price(float|int $amount, ?string $from): string
    {
        $price = convert_price($amount, $from);

        return number_format($price['amount'], 0) . ' ' . $price['currency'];
    }
}

if (!function_exists('all_currency_prices')) {
    /**
     * Every SUPPORTED currency except the one already shown as the main price
     * (the display currency). Used on the property-single page to list all
     * currency translations under the headline price.
     *
     * @return array<int, array{currency: string, amount: float, formatted: string}>
     */
    function all_currency_prices(float|int $amount, ?string $from): array
    {
        $display   = display_currency();
        $from      = $from ?: $display;
        $amount    = (float) $amount;
        $service   = app(ExchangeRateService::class);
        $supported = config('currency.supported', []);

        $out = [];
        foreach ($supported as $currency) {
            if ($currency === $display) {
                continue; // already shown as the headline price
            }
            $value = $service->convert($amount, $from, $currency);
            if ($value !== null) {
                $out[] = [
                    'currency'  => $currency,
                    'amount'    => $value,
                    'formatted' => number_format($value, 0) . ' ' . $currency,
                ];
            }
        }

        return $out;
    }
}

if (!function_exists('property_is_showable')) {
    /**
     * True only when the API returned a real listing. An unknown/incomplete slug is
     * sometimes answered with a blank "ghost" stub (no id/title) instead of a 404 —
     * such stubs must be kept OUT of the shared `te_prop:{slug}` cache.
     *
     * @param mixed $p
     */
    function property_is_showable($p): bool
    {
        return is_array($p) && !empty($p['id']) && !empty($p['title']);
    }
}

if (!function_exists('normalize_property')) {
    /**
     * Enrich a raw single-property retrieve() payload with the derived fields every
     * property-card / property page needs but the endpoint does NOT return: `slug`,
     * `primaryImageUrl` (first isPrimary media, else first media) and `fullAddress`.
     *
     * Shared by PropertyController::show()/extras(), FavoritesController and
     * CompareController — the first three write the SAME `te_prop:{slug}` cache key, so
     * the cached shape must be identical no matter which request fills it first (else a
     * card read from a page-populated cache would miss primaryImageUrl → no image).
     *
     * @param  array<string, mixed> $p
     * @return array<string, mixed>
     */
    function normalize_property(array $p, string $slug): array
    {
        $p['slug'] = $slug;

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
        $p['fullAddress'] = $addrParts ? implode(', ', $addrParts) : null;

        return $p;
    }
}
