@props(['prop'])

@php
    $locale = app()->getLocale();
    $txType = strtolower($prop['transactionType'] ?? '');
    $txLabel = match ($txType) {
        'sale' => __('index.popular_for_sale'),
        'rent' => __('index.popular_rent_monthly'),
        'rentdaily' => __('index.popular_rent_daily'),
        default => ucfirst($txType),
    };
    $ptKey = 'property.' . strtolower($prop['propertyType'] ?? '');
    $ptLabel = __($ptKey);
    if ($ptLabel === $ptKey) {
        $ptLabel = $prop['propertyType'] ?? '';
    }
    $price       = isset($prop['price']) ? number_format((float) $prop['price']) : null;
    $currency    = $prop['currency'] ?? '';
    $rawPrice    = isset($prop['price']) ? (float) $prop['price'] : null;
    $rawCurrency = $prop['currency'] ?? 'AMD';
    $slug = $prop['slug'] ?? '';
@endphp

<div x-data="propertyToggle('{{ $slug }}')"
    data-fav-card
    data-title="{{ $prop['title'] ?? '' }}"
    data-address="{{ $prop['fullAddress'] ?? $prop['city'] ?? '' }}"
    data-code="{{ $prop['code'] ?? $prop['ref'] ?? $prop['internalCode'] ?? $slug }}"
    class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-sand bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">

    {{-- Action buttons — positioned on outer div so overflow-hidden on image doesn't clip them --}}
    <div class="absolute right-3 top-3 z-20 flex flex-row gap-1.5">
        <button type="button" @click="toggleFav($event)"
            class="flex h-11 w-11 items-center justify-center rounded-full shadow-md transition-all duration-200"
            :class="[isFav ? 'bg-red-50 text-red-500' : 'bg-white text-neutral-400 hover:text-red-400', popFav ? 'scale-125' : 'scale-100']">
            <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                class="transition-[fill] duration-200" :fill="isFav ? 'currentColor' : 'none'">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
        </button>
        <button type="button" @click="toggleCmp($event)"
            class="flex h-11 w-11 items-center justify-center rounded-full shadow-md transition-all duration-200"
            :class="[isCmp ? 'bg-brand-50 text-brand-600' : 'bg-white text-neutral-400 hover:text-brand-500', popCmp ? 'scale-125' : 'scale-100']">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                :stroke-width="isCmp ? '2.5' : '1.8'">
                <path d="M18 20V10M12 20V4M6 20v-6" />
            </svg>
        </button>
    </div>

    {{-- Image --}}
    <a href="{{ url('/' . $locale . '/property/' . $slug) }}" class="relative block h-56 overflow-hidden bg-sand">
        @if(!empty($prop['primaryImageUrl']))
            <img src="{{ $prop['primaryImageUrl'] }}" alt="{{ $prop['title'] ?? '' }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy"
                onerror="this.style.display='none';this.parentElement.querySelector('.img-fallback').style.display='grid'">
            <div class="img-fallback grid h-full place-items-center text-brand-300" style="display:none">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
        @else
            <div class="grid h-full place-items-center text-brand-300">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <path d="M3 15l5-5 4 4 3-3 6 6" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                </svg>
            </div>
        @endif
        @if($txLabel)
            <span
                class="absolute left-4 top-4 z-10 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white">{{ $txLabel }}</span>
        @endif
    </a>

    {{-- Body --}}
    <a href="{{ url('/' . $locale . '/property/' . $slug) }}" class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-lg font-semibold text-ink line-clamp-1">{{ $prop['title'] ?? $ptLabel }}</h3>
        <p class="mt-1 text-sm text-neutral-500 line-clamp-1">{{ $prop['fullAddress'] ?? $prop['city'] ?? '' }}</p>

        <div class="mt-4 flex flex-wrap gap-4 text-sm text-neutral-600">
            @if(!empty($prop['bedrooms']))
                <span class="inline-flex items-center gap-1.5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.6">
                        <path
                            d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6M3 18h18M3 18v2M21 18v2M6 10V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2" />
                    </svg>{{ $prop['bedrooms'] }} {{ __('index.bedroom') }}</span>
            @endif
            @if(!empty($prop['bathrooms']))
                <span class="inline-flex items-center gap-1.5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.6">
                        <path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3zM6 12V6a2 2 0 0 1 2-2" />
                    </svg>{{ $prop['bathrooms'] }} {{ __('index.bath') }}</span>
            @endif
            @if(!empty($prop['areaTotal']))
                <span class="inline-flex items-center gap-1.5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.6">
                        <path d="M3 3h18v18H3zM9 3v18M3 9h18" />
                    </svg>{{ $prop['areaTotal'] }} {{ __('index.sq_ft') }}</span>
            @endif
        </div>

        <div class="mt-auto pt-5">
            @if($rawPrice)
                <div class="font-display text-xl font-bold text-brand-700"
                     x-data
                     x-text="$store.fx.format({{ $rawPrice }}, '{{ $rawCurrency }}')">{{ $price }} {{ $currency }}</div>
            @endif
            <div class="mt-2 text-sm font-semibold text-ink transition group-hover:text-brand-600">
                {{ __('property.view_details') }} →
            </div>
        </div>
    </a>
</div>