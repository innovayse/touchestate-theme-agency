@extends('layout.app')
@section('title', ($property['title'] ?? __('header.property')) . ' — ' . ($workspace['name'] ?? ''))

@php
    $locale = app()->getLocale();

    // Images
    $images = [];
    foreach (($property['images'] ?? $property['media'] ?? []) as $img) {
        if (is_string($img)) { $images[] = $img; }
        elseif (is_array($img)) { $images[] = $img['url'] ?? $img['imageUrl'] ?? $img['primaryImageUrl'] ?? null; }
    }
    $images = array_values(array_filter($images));
    if (!$images && !empty($property['primaryImageUrl'])) { $images = [$property['primaryImageUrl']]; }

    $price       = isset($property['price']) ? number_format((float) $property['price']) : null;
    $currency    = $property['currency'] ?? '';
    $waNumber    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
    $viberNumber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;
    $tgLink      = $workspace['messengers']['telegram'] ?? null;

    $slug        = $property['slug'] ?? '';
    $extrasUrl   = url('/'.$locale.'/property/'.$slug.'/extras');
    $enquireUrl  = url('/api/property/'.$slug.'/enquire');
    $viewUrl     = url('/api/property/'.$slug.'/view');

    $yandexKey   = config('services.yandex.maps_key', env('YANDEX_MAPS_API_KEY', ''));

    $geoAddressParts = array_filter([
        $property['street']         ?? null,
        $property['buildingNumber'] ?? null,
        $property['district']       ?? null,
        $property['city']           ?? null,
        $property['country']        ?? null,
    ]);
    $geoAddress = implode(', ', $geoAddressParts);
    $hasMap = $yandexKey && $geoAddress;

    // Spec rows
    $specs = [];
    if (!empty($property['propertyType'])) {
        $k='property.'.strtolower($property['propertyType']); $lbl=__($k); if($lbl===$k){$lbl=$property['propertyType'];}
        $specs[__('property.type')] = $lbl;
    }
    if (!empty($property['transactionType'])) {
        $txMap = ['Sale'=>__('property.for_sale'),'Rent'=>__('property.rent_monthly'),'RentDaily'=>__('property.rent_daily')];
        $specs[__('property.deal')] = $txMap[$property['transactionType']] ?? $property['transactionType'];
    }
    if (!empty($property['rooms']))           { $specs[__('property.rooms')]         = $property['rooms']; }
    if (!empty($property['bedrooms']))        { $specs[__('index.bedroom')]           = $property['bedrooms']; }
    if (!empty($property['bathrooms']))       { $specs[__('index.bath')]              = $property['bathrooms']; }
    if (!empty($property['areaTotal']))       { $specs[__('property.area_total')]     = $property['areaTotal'] . ' ' . __('index.sq_ft'); }
    if (!empty($property['areaLiving']))      { $specs[__('property.area_living')]    = $property['areaLiving'] . ' ' . __('index.sq_ft'); }
    if (!empty($property['areaKitchen']))     { $specs[__('property.area_kitchen')]   = $property['areaKitchen'] . ' ' . __('index.sq_ft'); }
    if (!empty($property['floor']) && !empty($property['floorsTotal'])) {
        $specs[__('property.floor')] = $property['floor'] . ' / ' . $property['floorsTotal'];
    }
    if (!empty($property['yearBuilt']))       { $specs[__('property.year_built')]     = $property['yearBuilt']; }
    if (!empty($property['renovationType'])) {
        $k='compare.renovation_'.strtolower($property['renovationType']); $lbl=__($k); if($lbl===$k){$lbl=$property['renovationType'];}
        $specs[__('property.renovation')] = $lbl;
    }
    if (!empty($property['constructionType'])) {
        $k='compare.construction_'.strtolower($property['constructionType']); $lbl=__($k); if($lbl===$k){$lbl=$property['constructionType'];}
        $specs[__('property.construction')] = $lbl;
    }
    if (!empty($property['furnitureType'])) {
        $k='compare.furniture_'.strtolower($property['furnitureType']); $lbl=__($k); if($lbl===$k){$lbl=$property['furnitureType'];}
        $specs[__('property.furniture')] = $lbl;
    }
    if (!empty($property['city']))            { $specs[__('property.city')]           = $property['city']; }
    if (!empty($property['district']))        { $specs[__('property.district')]       = $property['district']; }

    // Extra specs from API
    if (!empty($property['pricePerSqm']))     { $specs[__('property-single.price_per_sqm')] = number_format((float)$property['pricePerSqm']) . ' ' . ($property['currency'] ?? ''); }
    if (!empty($property['ceilingHeight']))   { $specs[__('property-single.ceiling_height')] = $property['ceilingHeight'] . ' ' . __('property-single.meters_short'); }
    if (!empty($property['deposit']))         { $specs[__('property-single.deposit')] = number_format((float)$property['deposit']) . ' ' . ($property['currency'] ?? ''); }
    if (!empty($property['landArea']))        { $specs[__('property-single.land_area')] = $property['landArea'] . ' ' . __('index.sq_ft'); }
    if (!empty($property['parkingSpaces']))   { $specs[__('property-single.parking_spaces')] = $property['parkingSpaces']; }
    if (!empty($property['zoningType']))      { $specs[__('property-single.zoning')] = $property['zoningType']; }
    if (!empty($property['balconyType'])) {
        $k = 'property-single.balcony_' . strtolower($property['balconyType']); $lbl = __($k);
        $specs[__('property-single.balcony')] = ($lbl === $k ? $property['balconyType'] : $lbl);
    }
    if (!empty($property['terraceType'])) {
        $k = 'property-single.terrace_' . strtolower($property['terraceType']); $lbl = __($k);
        $specs[__('property-single.terrace')] = ($lbl === $k ? $property['terraceType'] : $lbl);
    }
    if (!empty($property['heatingType'])) {
        $ht = array_filter(is_array($property['heatingType']) ? $property['heatingType'] : [$property['heatingType']]);
        $specs[__('property-single.heating')] = implode(', ', array_map(function($h) {
            $k = 'property-single.heating_' . strtolower($h); $l = __($k); return $l === $k ? $h : $l;
        }, $ht));
    }
    if (!empty($property['parkingType'])) {
        $pt = array_filter(is_array($property['parkingType']) ? $property['parkingType'] : [$property['parkingType']]);
        $specs[__('property-single.parking')] = implode(', ', array_map(function($t) {
            $k = 'property-single.parking_' . strtolower($t); $l = __($k); return $l === $k ? $t : $l;
        }, $pt));
    }
    if (!empty($property['windowView'])) {
        $wv = array_filter(is_array($property['windowView']) ? $property['windowView'] : [$property['windowView']]);
        $specs[__('property-single.window_view')] = implode(', ', array_map(function($v) {
            $k = 'property-single.view_' . strtolower($v); $l = __($k); return $l === $k ? $v : $l;
        }, $wv));
    }
    if (!empty($property['utilitiesPolicy'])) {
        $k = 'property-single.utilities_' . strtolower($property['utilitiesPolicy']); $lbl = __($k);
        $specs[__('property-single.utilities_policy')] = ($lbl === $k ? $property['utilitiesPolicy'] : $lbl);
    }

    $features       = array_filter((array) ($property['features']   ?? []));
    $appliances     = array_filter((array) ($property['appliances'] ?? []));
    $utilities      = array_filter((array) ($property['utilities']  ?? []));
    $amenitiesExtra = array_filter((array) ($property['amenities']  ?? []));

    // Rental conditions
    $conditions = [];
    if (!empty($property['petsPolicy']))     $conditions['pets_policy']     = $property['petsPolicy'];
    if (!empty($property['childrenPolicy'])) $conditions['children_policy'] = $property['childrenPolicy'];

    $badges = [];
    if (!empty($property['isNegotiable']))      $badges[] = __('property-single.is_negotiable');
    if (!empty($property['isNewConstruction'])) $badges[] = __('property-single.is_new_construction');
    if (!empty($property['isFrontLine']))       $badges[] = __('property-single.is_front_line');
    if (!empty($property['isLongTermRental']))  $badges[] = __('property-single.is_long_term');
    if (!empty($property['isUninhabited']))     $badges[] = __('property-single.is_uninhabited');

    $agentName = $property['assignedAgentName'] ?? null;
    $listedAt  = !empty($property['createdAt']) ? \Carbon\Carbon::parse($property['createdAt'])->format('d.m.Y') : null;
    $updatedAt = !empty($property['updatedAt']) ? \Carbon\Carbon::parse($property['updatedAt'])->format('d.m.Y') : null;
@endphp

@section('content')
{{-- Custom breadcrumb with fav/compare actions --}}
<section class="relative bg-ink pt-28 pb-12" x-data="{
    slug: '{{ $slug }}',
    isFav: false,
    isCmp: false,
    popFav: false,
    popCmp: false,
    init() {
        this.refresh();
        window.addEventListener('te-storage', () => this.refresh());
    },
    refresh() {
        try { this.isFav = JSON.parse(localStorage.getItem('te_favorites') || '[]').includes(this.slug); } catch(e) { this.isFav = false; }
        try { this.isCmp = JSON.parse(localStorage.getItem('te_compare') || '[]').includes(this.slug); } catch(e) { this.isCmp = false; }
    },
    toggleFav() {
        let list = []; try { list = JSON.parse(localStorage.getItem('te_favorites') || '[]'); } catch(e) {}
        const idx = list.indexOf(this.slug);
        if (idx >= 0) list.splice(idx, 1); else list.push(this.slug);
        localStorage.setItem('te_favorites', JSON.stringify(list));
        this.isFav = !this.isFav;
        this.popFav = true; setTimeout(() => this.popFav = false, 300);
        window.dispatchEvent(new Event('te-storage'));
    },
    toggleCmp() {
        let list = []; try { list = JSON.parse(localStorage.getItem('te_compare') || '[]'); } catch(e) {}
        const idx = list.indexOf(this.slug);
        if (idx >= 0) list.splice(idx, 1); else list.push(this.slug);
        localStorage.setItem('te_compare', JSON.stringify(list));
        this.isCmp = !this.isCmp;
        this.popCmp = true; setTimeout(() => this.popCmp = false, 300);
        window.dispatchEvent(new Event('te-storage'));
    }
}">
    <div class="container-x text-center">
        <h1 class="font-display text-xl font-bold text-white sm:text-3xl lg:text-4xl">{{ $property['title'] ?? __('header.property') }}</h1>
        <div class="mt-3 flex items-center justify-center gap-2 text-sm text-white/60">
            <nav class="flex flex-col items-center gap-1">
                <div class="flex items-center gap-2">
                    <a href="{{ url('/'.$locale) }}" class="text-brand-400 hover:text-brand-300">{{ __('header.home') }}</a>
                    <span>›</span>
                </div>
                <span class="truncate max-w-xs">{{ $property['title'] ?? __('header.property') }}</span>
            </nav>
        </div>
        {{-- Fav / Compare --}}
        <div class="mt-4 flex items-center justify-center gap-2">
            <button type="button" @click="toggleFav()"
                    class="flex h-10 w-10 items-center justify-center rounded-full border transition-all duration-200"
                    :class="[isFav ? 'border-red-500 bg-white/10 text-red-500' : 'border-white/20 bg-white/10 text-white/60 hover:border-red-400 hover:text-red-400', popFav ? 'scale-125' : 'scale-100']">
                <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                     style="transition: fill 0.2s ease"
                     :fill="isFav ? 'currentColor' : 'none'">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
            <button type="button" @click="toggleCmp()"
                    class="flex h-10 w-10 items-center justify-center rounded-full border transition-all duration-200"
                    :class="[isCmp ? 'border-brand-400 bg-brand-500/20 text-brand-300' : 'border-white/20 bg-white/10 text-white/60 hover:border-brand-400 hover:text-brand-300', popCmp ? 'scale-125' : 'scale-100']">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 20V10M12 20V4M6 20v-6"/>
                </svg>
            </button>
        </div>
    </div>
</section>

<section class="py-12" x-data="{
    lightbox: false,
    current: 0,
    fading: false,
    images: {{ json_encode($images) }},
    enquirySent: false,
    enquiryLoading: false,
    enquiryError: '',
    goto(idx) {
        if (idx === this.current) return;
        this.fading = true;
        setTimeout(() => {
            this.current = idx;
            this.fading = false;
        }, 180);
        setTimeout(() => {
            const el = this.$refs['thumb' + idx];
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }, 220);
    },
    async sendEnquiry(form) {
        this.enquiryLoading = true;
        this.enquiryError = '';
        try {
            const data = Object.fromEntries(new FormData(form));
            const r = await fetch('{{ $enquireUrl }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify(data),
            });
            const j = await r.json();
            if (j.ok) { this.enquirySent = true; form.reset(); }
            else { this.enquiryError = '{{ __('property.enquiry_error') }}'; }
        } catch(e) { this.enquiryError = '{{ __('property.enquiry_error') }}'; }
        this.enquiryLoading = false;
    }
}" @keydown.escape.window="lightbox = false">

    <div class="container-x">
        <div class="flex flex-col gap-10 lg:flex-row lg:items-start">

            {{-- ── Main column ───────────────────────────────────── --}}
            <div class="min-w-0 flex-1">

                {{-- Gallery --}}
                @if($images)
                    <div class="space-y-3">
                        <div class="relative overflow-hidden rounded-3xl">
                            <img :src="images[current]" src="{{ $images[0] }}"
                                 alt="{{ $property['title'] ?? '' }}"
                                 :class="fading ? 'opacity-0' : 'opacity-100'"
                                 class="h-56 w-full cursor-zoom-in object-cover transition-opacity duration-200 sm:h-[420px]"
                                 @click="lightbox = true">
                            @if(count($images) > 1)
                                <div class="absolute bottom-3 right-3 rounded-full bg-black/40 px-2.5 py-1 text-xs text-white/80 backdrop-blur-sm" x-text="`${current+1} / {{ count($images) }}`"></div>
                            @endif
                        </div>
                        @if(count($images) > 1)
                            <div x-ref="thumbs" class="flex gap-3 overflow-x-auto p-2" style="scrollbar-width:thin;scrollbar-color:#ead2c7 transparent">
                                @foreach($images as $idx => $img)
                                    <div class="shrink-0 cursor-pointer rounded-xl transition-all duration-200 overflow-hidden"
                                         :style="{{ $idx }} === current ? 'outline: 3px solid var(--color-brand-600); outline-offset: 2px; opacity:1; position:relative; z-index:10' : 'opacity:0.5; position:relative; z-index:1'"
                                         x-ref="thumb{{ $idx }}"
                                         @click="goto({{ $idx }})">
                                        <img src="{{ $img }}" alt="" class="h-20 w-28 object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="grid h-56 place-items-center rounded-3xl bg-sand text-brand-200 sm:h-[420px]">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg>
                    </div>
                @endif

                {{-- Lightbox --}}
                <div x-show="lightbox" x-cloak
                     x-effect="lightbox ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')"
                     @keydown.arrow-right.window="if(lightbox) goto((current+1) % images.length)"
                     @keydown.arrow-left.window="if(lightbox) goto((current-1+images.length) % images.length)"
                     class="fixed inset-0 z-[100] flex flex-col" style="background:rgba(15,12,10,0.97);backdrop-filter:blur(12px)">

                    {{-- Top bar --}}
                    <div class="flex shrink-0 items-center justify-between gap-4 border-b border-white/10 px-5 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white/90">{{ $property['title'] ?? '' }}</p>
                            <p class="text-xs text-white/40">{{ $property['fullAddress'] ?? $property['city'] ?? '' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            {{-- Counter --}}
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/70" x-text="`${current+1} / ${images.length}`"></span>
                            {{-- Download --}}
                            <a :href="images[current]" download target="_blank"
                               class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-brand-600 hover:text-white">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </a>
                            {{-- Close --}}
                            <button @click="lightbox = false"
                                    class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-red-500 hover:text-white">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Main image --}}
                    <div class="relative flex min-h-0 flex-1 items-center justify-center p-4"
                         @click.self="lightbox = false">
                        {{-- Prev --}}
                        <button @click="goto((current-1+images.length) % images.length)"
                                class="absolute left-3 z-10 grid h-11 w-11 place-items-center rounded-full border border-white/10 bg-black/40 text-white backdrop-blur-sm transition hover:border-brand-500 hover:bg-brand-600">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>

                        <img :src="images[current]"
                             :class="fading ? 'opacity-0 scale-[0.98]' : 'opacity-100 scale-100'"
                             class="max-h-full max-w-full rounded-2xl object-contain shadow-[0_32px_80px_rgba(0,0,0,0.8)] select-none transition-all duration-200">

                        {{-- Next --}}
                        <button @click="goto((current+1) % images.length)"
                                class="absolute right-3 z-10 grid h-11 w-11 place-items-center rounded-full border border-white/10 bg-black/40 text-white backdrop-blur-sm transition hover:border-brand-500 hover:bg-brand-600">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18l6-6-6-6"/></svg>
                        </button>

                    </div>

                    {{-- Thumbnails strip --}}
                    <div class="shrink-0 border-t border-white/10 bg-black/30 px-5 py-3"
                         style="scrollbar-width:thin;scrollbar-color:#a6644f transparent;overflow-x:auto">
                        <div class="flex gap-2">
                            <template x-for="(img, i) in images" :key="i">
                                <button type="button" @click="goto(i)"
                                        :class="i === current
                                            ? 'ring-2 ring-brand-500 ring-offset-2 ring-offset-black/80 opacity-100'
                                            : 'opacity-35 hover:opacity-65'"
                                        class="shrink-0 overflow-hidden rounded-lg transition-all duration-150">
                                    <img :src="img" class="h-14 w-20 object-cover">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Title & address --}}
                <div class="mt-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between sm:gap-4">
                        <div class="min-w-0 sm:flex-1">
                            <h1 class="font-display text-2xl font-bold leading-snug text-ink sm:text-3xl">{{ $property['title'] ?? '' }}</h1>
                            @if(!empty($property['fullAddress']) || !empty($property['city']))
                                <p class="mt-2 flex items-center gap-1.5 text-neutral-500">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                    {{ $property['fullAddress'] ?? $property['city'] ?? '' }}
                                </p>
                            @endif
                        </div>
                        @if($price)
                            <div class="text-right">
                                <div class="flex items-center gap-1 sm:justify-end">
                                    <span class="font-display text-3xl font-bold text-brand-700">{{ $price }} {{ $currency }}</span>
                                    @if(!empty($property['transactionType']) && strtolower($property['transactionType']) !== 'sale')
                                        <span class="text-sm text-neutral-500">{{ strtolower($property['transactionType']) === 'rentdaily' ? __('property.per_day') : __('property.per_month') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Quick stats --}}
                    <div class="mt-6 flex flex-wrap gap-5 border-y border-sand py-5">
                        @if(!empty($property['rooms']))
                            <div class="flex items-center gap-2 text-sm text-neutral-700">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="10" width="18" height="11" rx="2"/><path d="M7 10V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v3"/></svg>
                                {{ $property['rooms'] }} {{ __('property.rooms') }}
                            </div>
                        @endif
                        @if(!empty($property['bedrooms']))
                            <div class="flex items-center gap-2 text-sm text-neutral-700">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6M3 18h18M6 10V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/></svg>
                                {{ $property['bedrooms'] }} {{ __('index.bedroom') }}
                            </div>
                        @endif
                        @if(!empty($property['bathrooms']))
                            <div class="flex items-center gap-2 text-sm text-neutral-700">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3zM6 12V6a2 2 0 0 1 2-2"/></svg>
                                {{ $property['bathrooms'] }} {{ __('index.bath') }}
                            </div>
                        @endif
                        @if(!empty($property['areaTotal']))
                            <div class="flex items-center gap-2 text-sm text-neutral-700">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg>
                                {{ $property['areaTotal'] }} {{ __('index.sq_ft') }}
                            </div>
                        @endif
                        @if(!empty($property['floor']) && !empty($property['floorsTotal']))
                            <div class="flex items-center gap-2 text-sm text-neutral-700">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21h18M4 18V9l8-6 8 6v9"/><path d="M9 21v-6h6v6"/></svg>
                                {{ $property['floor'] }}/{{ $property['floorsTotal'] }} {{ __('property.floor') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                @if(!empty($property['description']))
                    <div class="mt-8">
                        <h2 class="font-display text-xl font-semibold text-ink mt-6 mb-4">{{ __('property.description') }}</h2>
                        <div class="prose prose-neutral mt-4 max-w-none text-neutral-600 leading-relaxed">{!! $property['description'] !!}</div>
                    </div>
                @endif

                {{-- Badges --}}
                @if($badges)
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach($badges as $badge)
                            <span class="rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ $badge }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Specs table --}}
                @if($specs)
                    <div class="mt-8">
                        <h2 class="font-display text-xl font-semibold text-ink mt-6 mb-4">{{ __('property.details') }}</h2>
                        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($specs as $label => $value)
                                <div class="rounded-xl border border-sand bg-panel px-3 py-2.5 sm:px-4 sm:py-3">
                                    <div class="text-[11px] leading-tight text-neutral-500 sm:text-xs">{{ $label }}</div>
                                    <div class="mt-0.5 truncate text-sm font-semibold text-ink" title="{{ $value }}">{{ $value }}</div>
                                </div>
                            @endforeach
                            @if(!empty($property['code']))
                                <div class="rounded-xl border border-sand bg-panel px-3 py-2.5 sm:px-4 sm:py-3">
                                    <div class="text-[11px] leading-tight text-neutral-500 sm:text-xs">{{ __('property.code') }}</div>
                                    <div class="mt-0.5 truncate text-sm font-semibold text-ink">{{ $property['code'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Features / Amenities --}}
                @if($features || $appliances || $utilities || $amenitiesExtra)
                    <div class="mt-8">
                        <h2 class="font-display text-xl font-semibold text-ink mt-6 mb-4">{{ __('property.amenities') }}</h2>
                        <div class="mt-4 space-y-4">
                            @if($amenitiesExtra)
                                <div>
                                    <h3 class="mb-2 text-sm font-semibold text-brand-700">{{ __('property-single.amenities_extra') }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($amenitiesExtra as $a)
                                            @php $ak='property-single.amenity_'.strtolower($a); $al=__($ak); @endphp
                                            <span class="rounded-full border border-sand bg-white px-3 py-1 text-sm text-ink">{{ $al===$ak ? $a : $al }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($features)
                                <div>
                                    <h3 class="mb-2 text-sm font-semibold text-brand-700">{{ __('property.features') }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($features as $f)
                                            @php $fk='property.feature_'.strtolower($f); $fl=__($fk); @endphp
                                            <span class="rounded-full border border-sand bg-white px-3 py-1 text-sm text-ink">{{ $fl===$fk ? $f : $fl }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($appliances)
                                <div>
                                    <h3 class="mb-2 text-sm font-semibold text-brand-700">{{ __('property.appliances') }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($appliances as $a)
                                            @php $ak='property.appliance_'.strtolower($a); $al=__($ak); @endphp
                                            <span class="rounded-full border border-sand bg-white px-3 py-1 text-sm text-ink">{{ $al===$ak ? $a : $al }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($utilities)
                                <div>
                                    <h3 class="mb-2 text-sm font-semibold text-brand-700">{{ __('property.utilities') }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($utilities as $u)
                                            @php $uk='property.utility_'.strtolower($u); $ul=__($uk); @endphp
                                            <span class="rounded-full border border-sand bg-white px-3 py-1 text-sm text-ink">{{ $ul===$uk ? $u : $ul }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Rental Conditions --}}
                @if($conditions)
                    <div class="mt-8">
                        <h2 class="font-display text-xl font-semibold text-ink mt-6 mb-4">{{ __('property-single.conditions') }}</h2>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($conditions as $type => $value)
                                @php
                                    $vk = 'property-single.policy_' . strtolower($value);
                                    $vl = __($vk);
                                    $vl = ($vl === $vk) ? $value : $vl;
                                    $isYes = strtolower($value) === 'yes';
                                    $isNo  = strtolower($value) === 'no';
                                @endphp
                                <div class="flex items-center justify-between rounded-xl border border-sand bg-panel px-4 py-3">
                                    <span class="text-sm text-neutral-500">{{ __('property-single.'.$type) }}</span>
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $isYes ? 'bg-green-100 text-green-700' : ($isNo ? 'bg-red-100 text-red-700' : 'bg-sand text-neutral-600') }}">
                                        {{ $vl }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Async: Similar + Comments (skeleton until loaded) --}}
                <div id="extras-wrap">
                    {{-- Similar --}}
                    <div id="similar-wrap" class="mt-12">
                        <div class="h-6 w-48 animate-pulse rounded bg-sand mb-4"></div>
                        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @for($i=0;$i<3;$i++)
                                <div class="animate-pulse rounded-2xl border border-sand bg-panel">
                                    <div class="h-48 rounded-t-2xl bg-sand"></div>
                                    <div class="space-y-2 p-4">
                                        <div class="h-4 w-3/4 rounded bg-sand"></div>
                                        <div class="h-3 w-1/2 rounded bg-sand"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    {{-- Comments --}}
                    <div class="mt-12" id="comments-section">
                        <h2 class="font-display text-xl font-semibold text-ink mt-6 mb-4">{{ __('property.comments') }}</h2>
                        <div id="comments-container" class="mt-4 space-y-4">
                            @for($i=0;$i<2;$i++)
                                <div class="animate-pulse rounded-2xl border border-sand bg-panel p-5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-sand"></div>
                                        <div class="h-4 w-32 rounded bg-sand"></div>
                                    </div>
                                    <div class="mt-3 h-3 w-full rounded bg-sand"></div>
                                    <div class="mt-2 h-3 w-4/5 rounded bg-sand"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Sidebar ─────────────────────────────────────────── --}}
            <aside class="w-full shrink-0 lg:w-80">
                <div class="sticky top-28 space-y-5">

                    {{-- Price & Contact --}}
                    <div class="rounded-3xl border border-sand bg-panel p-6">
                        @if($price)
                            <div class="flex items-center gap-1">
                                <span class="font-display text-3xl font-bold text-brand-700">{{ $price }} {{ $currency }}</span>
                                @if(!empty($property['transactionType']) && strtolower($property['transactionType']) !== 'sale')
                                    <span class="text-sm text-neutral-500">{{ strtolower($property['transactionType']) === 'rentdaily' ? __('property.per_day') : __('property.per_month') }}</span>
                                @endif
                            </div>
                        @endif
                        <div class="mt-5 space-y-2.5">
                            @if(!empty($workspace['phone']))
                                <a href="tel:{{ $workspace['phone'] }}" class="btn-brand w-full">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.62 19a19.45 19.45 0 0 1-6-6 19.79 19.79 0 0 1-2.91-7.18A2 2 0 0 1 4.71 3.73h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11l-1.27 1.27a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7a2 2 0 0 1 1.72 2.02z"/></svg>
                                    {{ $workspace['phone'] }}
                                </a>
                            @endif
                            @if($waNumber)
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                                   class="btn-outline w-full">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 2C6.51 2 2.04 6.47 2.04 11.975c0 1.757.46 3.47 1.33 4.978L2 22l5.2-1.36A10.01 10.01 0 0 0 12.017 22c5.506 0 9.977-4.47 9.977-9.975 0-2.664-1.04-5.17-2.92-7.05C17.19 3.09 14.68 2 12.017 2zm.009 18.13c-1.498 0-2.97-.402-4.248-1.163l-.303-.18-3.14.822.838-3.065-.197-.314A8.1 8.1 0 0 1 3.9 11.975c0-4.47 3.638-8.1 8.118-8.1a8.07 8.07 0 0 1 5.74 2.376 8.05 8.05 0 0 1 2.377 5.733c.008 4.48-3.63 8.146-8.109 8.146z"/></svg>
                                    {{ __('contact-us.whatsapp') }}
                                </a>
                            @endif
                            @if($viberNumber)
                                <a href="viber://chat?number=%2B{{ $viberNumber }}"
                                   class="btn-outline w-full">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.992 2C6.91 2 2.76 5.47 2.76 11.784c0 2.94 1.042 5.49 2.895 7.312L4.96 22l3.015-.86A9.56 9.56 0 0 0 11.992 22c5.082 0 9.232-3.47 9.232-9.784 0-2.744-.98-5.18-2.76-6.95A9.21 9.21 0 0 0 11.992 2z"/></svg>
                                    {{ __('contact-us.viber') }}
                                </a>
                            @endif
                            @if($tgLink)
                                <a href="{{ $tgLink }}" target="_blank" rel="noopener" class="btn-outline w-full">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.94 8.2l-2.02 9.53c-.15.68-.54.84-1.09.52l-3-2.21-1.45 1.4c-.16.16-.3.3-.61.3l.22-3.07 5.59-5.05c.24-.22-.05-.34-.38-.12L7.08 14.07 4.13 13.1c-.63-.2-.64-.63.13-.93l11.6-4.47c.53-.2.99.12.08 1.5z"/></svg>
                                    {{ __('contact-us.telegram') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Location Details --}}
                    @php
                        $hasLocFields = !empty($property['country']) || !empty($property['city']) || !empty($property['district']) || !empty($property['street']) || !empty($property['buildingNumber']);
                    @endphp
                    @if($hasLocFields || ($lat && $lng))
                    <div class="overflow-hidden rounded-3xl border border-sand bg-panel"
                         x-data="{
                             lat: {{ $lat ?? 0 }},
                             lng: {{ $lng ?? 0 }},
                             locale: '{{ $locale }}',
                             activeCategory: 'transport',
                             cache: {},
                             loading: false,
                             activeItems: [],
                             async init() { if (this.lat && this.lng) await this.loadCategory('transport'); },
                             haversine(lat2, lon2) {
                                 const R = 6371000, toRad = x => x * Math.PI / 180;
                                 const dLat = toRad(lat2 - this.lat), dLon = toRad(lon2 - this.lng);
                                 const a = Math.sin(dLat/2)**2 + Math.cos(toRad(this.lat)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
                                 return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
                             },
                             formatDist(m) { return m >= 1000 ? (m/1000).toFixed(1).replace(/\.0$/,'') + ' км' : m + ' м'; },
                             async loadCategory(cat) {
                                 if (this.cache[cat] !== undefined) { this.activeItems = this.cache[cat]; return; }
                                 this.loading = true; this.activeItems = [];
                                 try {
                                     const r = await fetch('/api/nearby?lat='+this.lat+'&lon='+this.lng+'&category='+cat+'&lang='+this.locale);
                                     const d = await r.json();
                                     const items = (d.results||[]).map(p=>({...p,dist:this.haversine(p.lat,p.lon)})).sort((a,b)=>a.dist-b.dist).slice(0,5);
                                     this.cache[cat] = items; this.activeItems = items;
                                 } catch(e) { this.cache[cat] = []; this.activeItems = []; }
                                 finally { this.loading = false; }
                             },
                             async setCategory(cat) { this.activeCategory = cat; await this.loadCategory(cat); }
                         }"
                         x-init="init()">

                        <div class="p-6">
                            <h3 class="font-display text-lg font-semibold text-ink">{{ __('property-single.location_details') }}</h3>

                            {{-- Location table --}}
                            <div class="mt-4 divide-y divide-sand">
                                @if(!empty($property['country']))
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm text-neutral-500">{{ __('property-single.country') }}</span>
                                    <span class="text-sm font-medium text-ink text-right">{{ $property['country'] }}</span>
                                </div>
                                @endif
                                @if(!empty($property['city']))
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm text-neutral-500">{{ __('property-single.city') }}</span>
                                    <span class="text-sm font-medium text-ink text-right">{{ $property['city'] }}</span>
                                </div>
                                @endif
                                @if(!empty($property['district']))
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm text-neutral-500">{{ __('property-single.district') }}</span>
                                    <span class="text-sm font-medium text-ink text-right">{{ $property['district'] }}</span>
                                </div>
                                @endif
                                @if(!empty($property['street']))
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm text-neutral-500">{{ __('property-single.street') }}</span>
                                    <span class="text-sm font-medium text-ink text-right">{{ $property['street'] }}</span>
                                </div>
                                @endif
                                @if(!empty($property['buildingNumber']))
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm text-neutral-500">{{ __('property-single.building_number') }}</span>
                                    <span class="text-sm font-medium text-ink text-right">{{ $property['buildingNumber'] }}</span>
                                </div>
                                @endif
                            </div>

                            {{-- Nearby places --}}
                            @if($lat && $lng)
                            <div class="mt-5">
                                <p class="text-xs font-semibold uppercase tracking-widest text-neutral-400">{{ __('property-single.additional_info') }}</p>

                                <div class="mt-2 min-h-[90px] divide-y divide-sand">
                                    <template x-if="loading && !activeItems.length">
                                        <div class="flex items-center justify-center py-6 text-neutral-400">
                                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                        </div>
                                    </template>
                                    <template x-for="(item, i) in activeItems" :key="i">
                                        <div class="flex items-center gap-2.5 py-2">
                                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-700">
                                                <template x-if="activeCategory === 'transport'">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="22" height="13" rx="2"/><path d="M1 11h22M7 22l5-3 5 3"/></svg>
                                                </template>
                                                <template x-if="activeCategory === 'education'">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h20M12 4L2 9l10 5 10-5-10-5z"/><path d="M6 12v5a6 6 0 0012 0v-5"/></svg>
                                                </template>
                                                <template x-if="activeCategory === 'food'">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 01-8 0"/></svg>
                                                </template>
                                                <template x-if="activeCategory === 'fitness'">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 010 8h-1M5 8H4a4 4 0 000 8h1M8 8v8M16 8v8M8 12h8"/></svg>
                                                </template>
                                            </span>
                                            <span class="flex-1 truncate text-sm text-ink" x-text="item.name"></span>
                                            <span class="shrink-0 text-sm font-semibold text-brand-700" x-text="formatDist(item.dist)"></span>
                                        </div>
                                    </template>
                                    <template x-if="!loading && !activeItems.length">
                                        <div class="py-5 text-center text-sm text-neutral-400">—</div>
                                    </template>
                                </div>
                            </div>

                            {{-- Category filter --}}
                            <div class="mt-4">
                                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-neutral-400">{{ __('property-single.nearby') }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach([
                                        'transport' => __('property-single.cat_transport'),
                                        'education' => __('property-single.cat_education'),
                                        'food'      => __('property-single.cat_food'),
                                        'fitness'   => __('property-single.cat_fitness'),
                                    ] as $cat => $label)
                                    <button @click="setCategory('{{ $cat }}')"
                                            :class="activeCategory === '{{ $cat }}' ? 'bg-brand-600 border-brand-600 text-white' : 'border-sand bg-white text-neutral-600 hover:border-brand-300 hover:text-brand-700'"
                                            class="rounded-full border px-3 py-1 text-xs font-medium transition">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Map --}}
                        @if($hasMap)
                        <div id="prop-map" class="h-64"></div>
                        @endif
                    </div>
                    @endif

                    {{-- Agent & dates --}}
                    @if($agentName || $listedAt)
                    <div class="rounded-2xl border border-sand bg-panel px-5 py-4 text-sm">
                        @if($agentName)
                        <div class="flex items-center gap-2.5">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-700">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs text-neutral-400">{{ __('property-single.agent') }}</div>
                                <div class="truncate font-semibold text-ink">{{ $agentName }}</div>
                            </div>
                        </div>
                        @endif
                        @if($listedAt || $updatedAt)
                        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-neutral-400 {{ $agentName ? 'border-t border-sand pt-3' : '' }}">
                            @if($listedAt)<span>{{ __('property-single.listed_on') }}: <span class="text-neutral-600">{{ $listedAt }}</span></span>@endif
                            @if($updatedAt)<span>{{ __('property-single.updated_on') }}: <span class="text-neutral-600">{{ $updatedAt }}</span></span>@endif
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Enquiry form (temporarily disabled) --}}
                    @if(false)
                    <div class="rounded-3xl border border-sand bg-panel p-6">
                        <h3 class="font-display text-lg font-semibold text-ink">{{ __('property.send_enquiry') }}</h3>

                        <div x-show="enquirySent" class="mt-4 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                            {{ __('property.enquiry_sent') }}
                        </div>
                        <div x-show="enquiryError" x-text="enquiryError" class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700"></div>

                        <form x-show="!enquirySent" class="mt-4 space-y-3" @submit.prevent="sendEnquiry($el)">
                            @csrf
                            <input type="text" name="name" required placeholder="{{ __('property.your_name') }}"
                                   class="field">
                            <input type="email" name="email" required placeholder="{{ __('property.your_email') }}"
                                   class="field">
                            <input type="tel" name="phone" placeholder="{{ __('property.your_phone') }}"
                                   class="field">
                            <textarea name="message" rows="3" placeholder="{{ __('property.your_message') }}"
                                      class="field resize-none" required></textarea>
                            <button type="submit" class="btn-brand w-full" :disabled="enquiryLoading">
                                <span x-show="!enquiryLoading">{{ __('property.send') }}</span>
                                <span x-show="enquiryLoading">…</span>
                            </button>
                        </form>
                    </div>
                    @endif

                </div>
            </aside>
        </div>
    </div>
</section>

@push('scripts')
<script>
// Record view
fetch('{{ $viewUrl }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });

// Load extras (similar + comments) async
fetch('{{ $extrasUrl }}')
    .then(r => r.json())
    .then(data => {
        const similarWrap = document.getElementById('similar-wrap');
        if (similarWrap) similarWrap.outerHTML = data.similar || '';
        if (data.comments) {
            document.getElementById('comments-container').outerHTML = data.comments;
            if (!document.getElementById('comments-container').children.length) {
                document.getElementById('comments-section').style.display = 'none';
            }
        }
    })
    .catch(() => {});
</script>
@if($hasMap)
<script>
@php
    $bT = function(string $v): string { return strip_tags($v); };
    $bLines = [];
    if (!empty($property['title'])) {
        $bLines[] = '<b style="font-size:13px;color:#1a1209">' . $bT($property['title']) . '</b>';
    }
    $addrParts = array_filter([
        isset($property['street'], $property['buildingNumber'])
            ? $bT($property['street']) . ' ' . $bT($property['buildingNumber'])
            : (!empty($property['street']) ? $bT($property['street']) : null),
        !empty($property['district']) ? $bT($property['district']) : null,
        !empty($property['city'])     ? $bT($property['city'])     : null,
        !empty($property['country'])  ? $bT($property['country'])  : null,
    ]);
    if ($addrParts) {
        $bLines[] = '<span style="color:#666;font-size:12px">' . implode(', ', $addrParts) . '</span>';
    }
    if ($price) {
        $bLines[] = '<b style="color:#a6644f;font-size:13px">' . $price . ' ' . $bT($currency) . '</b>';
    }
    $balloonHtml = '<div style="line-height:1.7;padding:2px 4px;max-width:210px">' . implode('<br>', $bLines) . '</div>';
@endphp
(function loadMap() {
    const GEO_ADDR = @json($geoAddress);
    const BALLOON  = @json($balloonHtml);

    const script = document.createElement('script');
    script.src = 'https://api-maps.yandex.ru/2.1/?apikey={{ $yandexKey }}&lang={{ app()->getLocale() === "en" ? "en_US" : "ru_RU" }}';
    script.onload = function() {
        ymaps.ready(function() {
            const HousePin = ymaps.templateLayoutFactory.createClass(
                '<div style="display:flex;flex-direction:column;align-items:center;cursor:pointer;filter:drop-shadow(0 4px 8px rgba(0,0,0,0.28))">' +
                '<div style="width:44px;height:44px;border-radius:50%;background:#a6644f;display:flex;align-items:center;justify-content:center">' +
                '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>' +
                '<path d="M9 21V12h6v9"/>' +
                '</svg>' +
                '</div>' +
                '<div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:11px solid #a6644f;margin-top:-1px"></div>' +
                '</div>'
            );
            const pinOpts = {
                iconLayout: HousePin,
                iconOffset: [-22, -55],
                iconShape: { type: 'Rectangle', coordinates: [[-26, -60], [26, 5]] },
                balloonOffset: [0, -60],
            };

            ymaps.geocode(GEO_ADDR, { results: 1 }).then(function(res) {
                const obj = res.geoObjects.get(0);
                if (!obj) return;
                const coords = obj.geometry.getCoordinates();
                const map = new ymaps.Map('prop-map', { center: coords, zoom: 15, controls: ['zoomControl'] });
                map.geoObjects.add(new ymaps.Placemark(coords, { balloonContent: BALLOON }, pinOpts));
            });
        });
    };
    document.head.appendChild(script);
})();
</script>
@endif
@endpush
@endsection
