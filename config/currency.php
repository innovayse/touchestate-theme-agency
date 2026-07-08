<?php

return [

    // Default site display currency (site default locale is Armenian → AMD).
    'default' => env('CURRENCY_DEFAULT', 'AMD'),

    // Currencies offered in the header switcher (and used for conversion).
    'supported' => ['AMD', 'USD', 'RUB', 'EUR'],

    // Display symbols per ISO code.
    'symbols' => [
        'AMD' => '֏',
        'USD' => '$',
        'RUB' => '₽',
        'EUR' => '€',
    ],

    // Central Bank of Armenia (CBA) SOAP rates source.
    // Rates are quoted against AMD: 1 unit of foreign currency = Rate/Amount AMD.
    'cba' => [
        'endpoint'    => env('CBA_ENDPOINT', 'http://api.cba.am/exchangerates.asmx'),
        'soap_action' => 'http://www.cba.am/ExchangeRatesLatest',
        // Rates are kept for 12h, then refreshed (scheduled command: currency:refresh).
        'cache_ttl'   => (int) env('CBA_CACHE_TTL', 43200),
        // How long to wait before retrying CBA after a failed fetch (seconds) — so a
        // temporarily-unreachable CBA does not slow down every page request.
        'retry_after' => (int) env('CBA_RETRY_AFTER', 300),
    ],

    // Approximate AMD-per-unit rates used ONLY when CBA has never been reachable and no
    // stored rates exist yet — so search/conversion keeps working. Edit as needed; real
    // CBA rates override these as soon as a fetch succeeds. (AMD = 1 is implied.)
    'fallback' => [
        'USD' => (float) env('CBA_FALLBACK_USD', 387),
        'EUR' => (float) env('CBA_FALLBACK_EUR', 425),
        'RUB' => (float) env('CBA_FALLBACK_RUB', 4.3),
    ],

];
