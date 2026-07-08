@props(['amount' => 0, 'currency' => null])
@php
    // Native currency of this amount (falls back to display currency when unknown).
    $native = $currency ?: display_currency();
    // Server-render in the current display currency; JS reconverts client-side on switch
    // using the native amount/currency held in the data-* attributes.
    $p = convert_price($amount, $currency);
@endphp
<span class="js-price" data-amount="{{ (float) $amount }}" data-currency="{{ $native }}">{{ number_format($p['amount'], 0) }} {{ $p['currency'] }}</span>
