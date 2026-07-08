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
    function convert_price($amount, ?string $from): array
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
    function format_price($amount, ?string $from): string
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
    function all_currency_prices($amount, ?string $from): array
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
