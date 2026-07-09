@extends('layout.app')
@section('title', __('header.property'))

@php
    $locale = app()->getLocale();
    $items  = $properties['items'] ?? [];
    $total  = $properties['totalCount'] ?? count($items);
    $hasNext = $properties['hasNextPage'] ?? false;
    $curPage = (int) request('page', 1);
    $totalPages = $properties['totalPages'] ?? 1;

    $sortOptions = [
        'viewCount_desc' => __('property.featured'),
        'price_asc'      => __('property.low_to_high'),
        'price_desc'     => __('property.high_to_low'),
        'createdAt_desc' => __('property.newest'),
        'areaTotal_desc' => __('property.largest'),
    ];
    if (request()->filled('sortByFull')) {
        $curSort = request('sortByFull');
    } elseif (request()->filled('sortBy')) {
        $curSort = request('sortBy') . '_' . request('sortOrder', 'desc');
    } else {
        $curSort = 'viewCount_desc';
    }
@endphp

@section('content')
<x-breadcrumb :title="__('header.property')" />

<section class="py-12">
    <div class="container-x">
        <div class="flex flex-col gap-8 lg:flex-row">

            {{-- ── Sidebar filters ────────────────────────────────── --}}
            <aside class="w-full shrink-0 lg:w-72 lg:self-start lg:sticky lg:top-6" x-data="{ open: false }" x-init="open = window.innerWidth >= 1024">
                <button @click="open = !open"
                        class="flex w-full items-center justify-between rounded-2xl border border-sand bg-panel px-5 py-4 text-sm font-semibold text-ink lg:hidden">
                    {{ __('property.filters') }}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="open ? 'rotate-180' : ''" class="transition"><path d="M5 9l7 7 7-7"/></svg>
                </button>

                <form id="filter-form" action="{{ url('/'.$locale.'/property') }}" method="GET"
                      novalidate
                      class="mt-3 flex flex-col overflow-hidden transition-[max-height,opacity] duration-200 ease-in-out lg:mt-0"
                      :style="open ? 'max-height:calc(100vh - 5rem);opacity:1;pointer-events:auto' : 'max-height:0;opacity:0;pointer-events:none'"
                      style="max-height:0;opacity:0;pointer-events:none">
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto pb-2 pr-1" style="overflow-anchor:none">

                    {{-- Transaction type --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('index.search_buy_sell') }}</h3>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach([''=>__('property.all_types'),'Sale'=>__('property.for_sale'),'Rent'=>__('property.rent_monthly'),'RentDaily'=>__('property.rent_daily')] as $val=>$lbl)
                                @php $active = request('transactionType', '') === $val; @endphp
                                <label class="cursor-pointer block">
                                    <input type="radio" name="transactionType" value="{{ $val }}" {{ $active ? 'checked' : '' }} class="hidden" @change="$el.closest('form').submit()">
                                    <span class="flex w-full items-center justify-center rounded-full border px-3 py-1.5 text-xs font-medium transition select-none
                                        {{ $active ? 'border-brand-600 bg-brand-600 text-white' : 'border-sand bg-white text-ink hover:border-brand-400 hover:text-brand-700' }}">
                                        {{ $lbl }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Property type --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('index.search_type') }}</h3>
                        @php
                            $ptypes = ['Apartment','House','Studio','Villa','Townhouse','Penthouse','Room','Office','Commercial','Land','Garage'];
                            $activePtypes = array_filter((array) request('propertyType', []));
                        @endphp
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-2">
                            @foreach($ptypes as $pt)
                                @php $k='property.'.strtolower($pt); $lbl=__($k); if($lbl===$k){$lbl=$pt;} $checked=in_array($pt,$activePtypes); @endphp
                                <label class="cursor-pointer block">
                                    <input type="checkbox" name="propertyType" value="{{ $pt }}" {{ $checked ? 'checked' : '' }} class="hidden">
                                    <span class="flex w-full items-center justify-center rounded-full border px-3 py-1.5 text-xs font-medium transition select-none
                                        {{ $checked ? 'border-brand-600 bg-brand-600 text-white' : 'border-sand bg-white text-ink hover:border-brand-400 hover:text-brand-700' }}">
                                        {{ $lbl }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Price range --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('property.price_range') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('index.search_min_price') }}</label>
                                <input type="number" name="minPrice" value="{{ request('minPrice') }}" placeholder="0"
                                       class="field">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('index.search_max_price') }}</label>
                                <input type="number" name="maxPrice" value="{{ request('maxPrice') }}" placeholder="∞"
                                       class="field">
                            </div>
                        </div>
                        <x-custom-select name="currency" class="mt-3"
                            :selected="request('currency', '')"
                            :placeholder="__('property.any_currency')"
                            :options="['USD'=>'USD','EUR'=>'EUR','AMD'=>'AMD','RUB'=>'RUB']" />
                    </div>

                    {{-- Rooms --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('property.rooms') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('property.min_rooms') }}</label>
                                <input type="number" name="minRooms" value="{{ request('minRooms') }}" placeholder="1" min="0" class="field">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('property.max_rooms') }}</label>
                                <input type="number" name="maxRooms" value="{{ request('maxRooms') }}" placeholder="∞" min="0" class="field">
                            </div>
                        </div>

                        {{-- Bedrooms --}}
                        <h3 class="mb-2 mt-4 font-display text-sm font-semibold text-ink">{{ __('property.bedrooms') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('property.min') }}</label>
                                <input type="number" name="minBedrooms" value="{{ request('minBedrooms') }}" placeholder="0" min="0" class="field">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('property.max') }}</label>
                                <input type="number" name="maxBedrooms" value="{{ request('maxBedrooms') }}" placeholder="∞" min="0" class="field">
                            </div>
                        </div>

                        {{-- Floor --}}
                        <h3 class="mb-2 mt-4 font-display text-sm font-semibold text-ink">{{ __('property.floor') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('property.min') }}</label>
                                <input type="number" name="minFloor" value="{{ request('minFloor') }}" placeholder="1" min="0" class="field">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-neutral-500">{{ __('property.max') }}</label>
                                <input type="number" name="maxFloor" value="{{ request('maxFloor') }}" placeholder="∞" min="0" class="field">
                            </div>
                        </div>
                    </div>

                    {{-- Area --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('property.area') }} ({{ __('index.sq_ft') }})</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="number" name="minArea" value="{{ request('minArea') }}" placeholder="{{ __('property.min') }}" class="field">
                            <input type="number" name="maxArea" value="{{ request('maxArea') }}" placeholder="{{ __('property.max') }}" class="field">
                        </div>
                    </div>

                    {{-- Renovation & Furniture --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5 space-y-4">
                        <div>
                            <h3 class="mb-2 font-display text-base font-semibold text-ink">{{ __('property.renovation') }}</h3>
                            <x-custom-select name="renovationType"
                                :selected="request('renovationType', '')"
                                :placeholder="__('property.any')"
                                :options="[
                                    'Capital'     => __('compare.renovation_capital'),
                                    'Designer'    => __('compare.renovation_designer'),
                                    'Euro'        => __('compare.renovation_euro'),
                                    'Cosmetic'    => __('compare.renovation_cosmetic'),
                                    'Partial'     => __('compare.renovation_partial'),
                                    'Old'         => __('compare.renovation_old'),
                                    'Unrenovated' => __('compare.renovation_unrenovated'),
                                ]" />
                        </div>
                        <div>
                            <h3 class="mb-2 font-display text-base font-semibold text-ink">{{ __('property.construction') }}</h3>
                            <x-custom-select name="constructionType"
                                :selected="request('constructionType', '')"
                                :placeholder="__('property.any')"
                                :options="[
                                    'Wood'       => __('compare.construction_wood'),
                                    'Strip'      => __('compare.construction_strip'),
                                    'Brick'      => __('compare.construction_brick'),
                                    'Monolithic' => __('compare.construction_monolithic'),
                                    'Panel'      => __('compare.construction_panel'),
                                    'Stone'      => __('compare.construction_stone'),
                                ]" />
                        </div>
                        <div>
                            <h3 class="mb-2 font-display text-base font-semibold text-ink">{{ __('property.furniture') }}</h3>
                            <x-custom-select name="furnitureType"
                                :selected="request('furnitureType', '')"
                                :placeholder="__('property.any')"
                                :options="[
                                    'Furnished'    => __('compare.furniture_furnished'),
                                    'Partial'      => __('compare.furniture_partial'),
                                    'Unavailable'  => __('compare.furniture_unavailable'),
                                    'ByAgreement'  => __('compare.furniture_byagreement'),
                                ]" />
                        </div>
                    </div>

                    {{-- Features --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('property.features') }}</h3>
                        @php
                            $popularFeatures = ['Elevator','Parking','Balcony','Pool','Gym','Security','Garden','Garage'];
                            $activeFeatures  = (array) request('features', []);
                        @endphp
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($popularFeatures as $feat)
                                @php $fk='property.feature_'.strtolower($feat); $fl=__($fk); $checked=in_array($feat,$activeFeatures); @endphp
                                <label class="cursor-pointer block">
                                    <input type="checkbox" name="features[]" value="{{ $feat }}"
                                           {{ $checked ? 'checked' : '' }} class="hidden">
                                    <span class="flex w-full items-center justify-center rounded-full border px-3 py-1.5 text-xs font-medium transition select-none
                                        {{ $checked ? 'border-brand-600 bg-brand-600 text-white' : 'border-sand bg-white text-ink hover:border-brand-400 hover:text-brand-700' }}">
                                        {{ $fl === $fk ? $feat : $fl }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- City / Search --}}
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="mb-3 font-display text-base font-semibold text-ink">{{ __('index.search_location') }}</h3>
                        <x-location-input name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('index.search_location') }}" />
                    </div>

                </div>
                {{-- Buttons sticky --}}
                <div class="mt-3 flex shrink-0 gap-3 border-t border-sand bg-cream pt-3 pb-1 lg:border-none lg:pt-3">
                        <button type="submit" class="btn-brand flex-1">{{ __('header.search') }}</button>
                        <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline flex-1 text-center">{{ __('property.reset') }}</a>
                    </div>
                </form>
            </aside>

            {{-- ── Results ────────────────────────────────────────── --}}
            <div class="min-w-0 flex-1" style="overflow-anchor:none"
                 x-data="{ _r: false }" x-init="_r = true">

                {{-- Skeleton: visible before Alpine init --}}
                <div x-show="!_r" class="animate-pulse">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div class="h-4 w-52 rounded-full bg-sand"></div>
                        <div class="h-10 w-44 rounded-2xl bg-sand"></div>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @for($i = 0; $i < 6; $i++)
                        <div class="rounded-2xl border border-sand bg-white">
                            <div class="h-56 rounded-t-2xl bg-sand"></div>
                            <div class="p-5 space-y-3">
                                <div class="h-5 w-3/4 rounded-full bg-sand"></div>
                                <div class="h-4 w-1/2 rounded-full bg-sand/70"></div>
                                <div class="flex gap-3 pt-1">
                                    <div class="h-4 w-20 rounded-full bg-sand/50"></div>
                                    <div class="h-4 w-16 rounded-full bg-sand/50"></div>
                                </div>
                                <div class="space-y-2 pt-2">
                                    <div class="h-6 w-2/5 rounded-full bg-sand"></div>
                                    <div class="h-4 w-1/4 rounded-full bg-sand/60"></div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Real content: hidden until Alpine init --}}
                <div x-show="_r" x-cloak>
                    {{-- Top bar --}}
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <p class="text-sm text-neutral-500">
                            {{ __('property.showing_result') }}
                            <span class="font-semibold text-ink">{{ count($items) }}</span>
                            {{ __('property.of') }}
                            <span class="font-semibold text-ink">{{ $total }}</span>
                            {{ __('property.properties') }}
                        </p>
                        <div class="flex items-center gap-3">
                            <form id="sort-form" action="{{ url('/'.$locale.'/property') }}" method="GET">
                                @foreach(request()->except(['sortBy','sortOrder','page']) as $k=>$v)
                                    @if(is_array($v))
                                        @foreach($v as $vi)<input type="hidden" name="{{ $k }}[]" value="{{ $vi }}">@endforeach
                                    @else
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endif
                                @endforeach
                                <x-custom-select name="sortByFull"
                                    :selected="$curSort"
                                    :options="$sortOptions"
                                    :autosubmit="true"
                                    :placeholder="__('property.default')"
                                    class="w-full sm:w-auto sm:min-w-[220px]" />
                            </form>
                        </div>
                    </div>

                    @if(count($items))
                        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($items as $prop)
                                <x-property-card :prop="$prop" />
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($totalPages > 1)
                            <nav class="mt-10 flex flex-wrap items-center justify-center gap-2">
                                @if($curPage > 1)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $curPage - 1]) }}"
                                       class="flex h-10 w-10 items-center justify-center rounded-full border border-sand bg-white text-sm text-ink transition hover:border-brand-500 hover:text-brand-600">
                                        ‹
                                    </a>
                                @endif
                                @for($p = max(1, $curPage-3); $p <= min($totalPages, $curPage+3); $p++)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $p]) }}"
                                       class="flex h-10 w-10 items-center justify-center rounded-full border text-sm transition
                                              {{ $p === $curPage ? 'border-brand-600 bg-brand-600 text-white' : 'border-sand bg-white text-ink hover:border-brand-500 hover:text-brand-600' }}">
                                        {{ $p }}
                                    </a>
                                @endfor
                                @if($curPage < $totalPages)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $curPage + 1]) }}"
                                       class="flex h-10 w-10 items-center justify-center rounded-full border border-sand bg-white text-sm text-ink transition hover:border-brand-500 hover:text-brand-600">
                                        ›
                                    </a>
                                @endif
                            </nav>
                        @endif
                    @else
                        <div class="rounded-3xl border border-dashed border-sand bg-panel py-24 text-center">
                            <svg class="mx-auto mb-4 text-brand-200" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg>
                            <p class="font-display text-xl text-neutral-500">{{ __('index.coming_soon') }}</p>
                            <p class="mt-1 text-sm text-neutral-400">{{ __('index.coming_soon_sub') }}</p>
                            <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline mt-6">{{ __('property.reset') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
