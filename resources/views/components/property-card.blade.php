@props(['prop'])

@php
    $locale = app()->getLocale();
    $txType = strtolower($prop['transactionType'] ?? '');
    $txLabel = match($txType) {
        'sale' => __('index.popular_for_sale'),
        'rent' => __('index.popular_rent_monthly'),
        'rentdaily' => __('index.popular_rent_daily'),
        default => ucfirst($txType),
    };
    $ptKey = 'property.' . strtolower($prop['propertyType'] ?? '');
    $ptLabel = __($ptKey);
    if ($ptLabel === $ptKey) { $ptLabel = $prop['propertyType'] ?? ''; }
    $price = isset($prop['price']) ? number_format((float) $prop['price']) : null;
    $currency = $prop['currency'] ?? '';
@endphp

<a href="{{ url('/'.$locale.'/property/'.$prop['slug']) }}"
   class="group flex h-full flex-col overflow-hidden rounded-2xl border border-sand bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
    <div class="relative h-56 overflow-hidden bg-sand">
        @if(!empty($prop['primaryImageUrl']))
            <img src="{{ $prop['primaryImageUrl'] }}" alt="{{ $prop['title'] ?? '' }}"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <div class="grid h-full place-items-center text-brand-300">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15l5-5 4 4 3-3 6 6"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
            </div>
        @endif
        @if($txLabel)
            <span class="absolute left-4 top-4 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white">{{ $txLabel }}</span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-lg font-semibold text-ink line-clamp-1">{{ $prop['title'] ?? $ptLabel }}</h3>
        <p class="mt-1 text-sm text-neutral-500 line-clamp-1">{{ $prop['fullAddress'] ?? $prop['city'] ?? '' }}</p>

        <div class="mt-4 flex flex-wrap gap-4 text-sm text-neutral-600">
            @if(!empty($prop['bedrooms']))
                <span class="inline-flex items-center gap-1.5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6M3 18h18M3 18v2M21 18v2M6 10V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/></svg>{{ $prop['bedrooms'] }} {{ __('index.bedroom') }}</span>
            @endif
            @if(!empty($prop['bathrooms']))
                <span class="inline-flex items-center gap-1.5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3zM6 12V6a2 2 0 0 1 2-2"/></svg>{{ $prop['bathrooms'] }} {{ __('index.bath') }}</span>
            @endif
            @if(!empty($prop['areaTotal']))
                <span class="inline-flex items-center gap-1.5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 3h18v18H3zM9 3v18M3 9h18"/></svg>{{ $prop['areaTotal'] }} {{ __('index.sq_ft') }}</span>
            @endif
        </div>

        <div class="mt-auto flex items-center justify-between pt-5">
            @if($price)
                <span class="font-display text-xl font-bold text-brand-700">{{ $currency }}{{ $price }}</span>
            @endif
            <span class="text-sm font-semibold text-ink transition group-hover:text-brand-600">{{ __('property.view_details') }} →</span>
        </div>
    </div>
</a>
