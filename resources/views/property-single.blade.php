<?php $page = 'property-single'; ?>
@section('title')
    {{ $property['title'] ?? __('property-single.title') }}
@endsection

@extends('layout.mainlayout')

@push('head')
@php
    $seoTitle = ($property['title'] ?? '') . ' — ' . config('app.name');
    $seoDesc  = Str::limit(strip_tags($property['description'] ?? ''), 160);
    $seoImage = $property['primaryImageUrl'] ?? '';
    $seoUrl   = request()->url();
    $seoLocale = app()->getLocale();
    $ldPrice  = convert_price($property['price'] ?? 0, $property['currency'] ?? null);
@endphp
{{-- Canonical + hreflang --}}
<link rel="canonical" href="{{ $seoUrl }}">
@foreach(['ru','en','hy'] as $lang)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ url($lang . '/property/' . ($property['slug'] ?? '')) }}">
@endforeach
{{-- Open Graph --}}
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:type" content="website">
<meta property="og:locale" content="{{ $seoLocale }}">
{{-- Twitter card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:image" content="{{ $seoImage }}">
{{-- JSON-LD: RealEstateListing --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "RealEstateListing",
  "name": @json($property['title'] ?? ''),
  "description": @json(Str::limit(strip_tags($property['description'] ?? ''), 300)),
  "url": @json($seoUrl),
  "price": {{ (float) $ldPrice['amount'] }},
  "priceCurrency": @json($ldPrice['currency']),
  "numberOfRooms": {{ (int)($property['rooms'] ?? 0) }},
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": @json(trim(($property['street'] ?? '') . ' ' . ($property['buildingNumber'] ?? ''))),
    "addressLocality": @json($property['city'] ?? ''),
    "addressCountry": @json($property['country'] ?? '')
  },
  "floorSize": {
    "@@type": "QuantitativeValue",
    "value": {{ (float)($property['areaTotal'] ?? 0) }},
    "unitCode": "MTK"
  },
  "image": @json($seoImage)
}
</script>
{{-- JSON-LD: BreadcrumbList --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    {"@@type":"ListItem","position":1,"name":@json(__('common.home')),"item":@json(url($seoLocale))},
    {"@@type":"ListItem","position":2,"name":@json(__('property.title')),"item":@json(url($seoLocale . '/property'))},
    {"@@type":"ListItem","position":3,"name":@json($property['title'] ?? ''),"item":@json($seoUrl)}
  ]
}
</script>
{{-- Page-scoped styles: SVG icon defaults, main image slider zones, thumbnail strip --}}
<style>
/* SVG icon defaults — inherits font size & color from parent */
.i-icon {
    width: 1em; height: 1em;
    display: inline-block;
    vertical-align: -0.15em;
    fill: currentColor;
    flex-shrink: 0;
}

/* Main image: stable aspect ratio so slide transitions stay smooth across mixed sizes */
.service-slider-wrap { position: relative; }
.service-slider .service-img-wrap img {
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    border-radius: 10px;
    background: #1a1a1a;
    display: block;
    cursor: pointer;
}

/* Location card: "Nearby" category tabs */
/* Nearby tabs: single row, horizontal scroll when they don't fit (scrollbar hidden) */
.poi-tabs-row {
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: none;
}
.poi-tabs-row::-webkit-scrollbar { display: none; }
.poi-tab {
    border: 1px solid var(--gray-200, #e2e8f0);
    background: transparent;
    color: var(--body-color, #64748b);
    border-radius: 20px;
    padding: 5px 16px;
    font-size: 13px;
    line-height: 1.2;
    transition: background .2s ease, color .2s ease, border-color .2s ease;
}
.poi-tab:hover { border-color: var(--primary, #03bd9d); color: var(--primary, #03bd9d); }
.poi-tab.active {
    background: var(--primary, #03bd9d);
    color: #fff;
    border-color: var(--primary, #03bd9d);
}
/* Fullscreen-map tab bar: readable on the dark map overlay */
.poi-tab-fs {
    color: #fff;
    border-color: rgba(255,255,255,.5);
    background: rgba(255,255,255,.08);
}
.poi-tab-fs:hover { color: #fff; border-color: #fff; }
.poi-tab-fs.active {
    background: var(--primary, #03bd9d);
    border-color: var(--primary, #03bd9d);
    color: #fff;
}
.service-slider-zone {
    position: absolute;
    top: 0; bottom: 0;
    width: 18%;
    background: transparent;
    border: 0;
    cursor: pointer;
    z-index: 5;
    padding: 0;
    transition: background .25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.service-slider-zone--prev { left: 0; border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
.service-slider-zone--next { right: 0; border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
.service-slider-zone--prev:hover { background: linear-gradient(to right, rgba(0,0,0,.35), transparent); }
.service-slider-zone--next:hover { background: linear-gradient(to left,  rgba(0,0,0,.35), transparent); }
.service-slider-zone::after {
    content: '';
    width: 48px; height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,.92);
    opacity: 0;
    transition: opacity .2s ease, transform .2s ease;
    background-repeat: no-repeat;
    background-position: center;
    background-size: 22px;
}
.service-slider-zone--prev::after { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23222'><path d='M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z'/></svg>"); }
.service-slider-zone--next::after { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23222'><path d='M8.59 16.59 10 18l6-6-6-6-1.41 1.41L13.17 12z'/></svg>"); }
.service-slider-zone:hover::after { opacity: 1; transform: scale(1.04); }

/* Thumbnails: uniform tile size + visible border + active state.
   Selector prefixed with .slider-card so it outranks the legacy
   `.slider-card .slider-nav-thumbnails .slick-slide` rule in style.css. */
.slider-card .slider-nav-thumbnails {
    margin: 16px 0 24px;         /* reserve space between main image and section below */
}
.slider-card .slider-nav-thumbnails .slick-list {
    overflow: hidden;            /* keep slick clones outside the visible area */
}
.slider-card .slider-nav-thumbnails .slick-slide {
    margin: 0 6px;
    height: auto;            /* let the thumbnail scale by aspect-ratio, not a fixed px height */
    cursor: pointer;
    box-sizing: border-box;
}
.slider-card .slider-nav-thumbnails .slick-slide .slide-img {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;   /* height tracks the slick-set width across all breakpoints */
    height: auto;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,.12);
    box-sizing: border-box;
    transition: border-color .2s ease, transform .2s ease, opacity .2s ease;
    opacity: .65;
}
.slider-card .slider-nav-thumbnails .slick-slide .slide-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 0;
    margin: 0;
    max-width: none;            /* override Bootstrap .img-fluid */
}
.slider-card .slider-nav-thumbnails .slick-slide:hover .slide-img {
    opacity: .95;
    box-shadow: 0 4px 12px rgba(0,0,0,.35);
}
.slider-card .slider-nav-thumbnails .slick-current .slide-img {
    border-color: #1abc9c;
    opacity: 1;
}
@media (max-width: 576px) {
    .service-slider-zone { width: 22%; }
    .service-slider-zone::after { width: 38px; height: 38px; background-size: 18px; }
}

/* Gallery skeleton — shown only until slick initializes, then `.gallery-loading` is removed (JS).
   Prevents the "stacked images → carousel" jump (FOUC). */
.gallery-skeleton-main,
.gallery-skeleton-thumbs { display: none; }
.slider-card.gallery-loading .slide-part { aspect-ratio: 16 / 10; }            /* definite box while loading */
.slider-card.gallery-loading .service-slider { visibility: hidden; }           /* hidden but measurable for slick */
.slider-card.gallery-loading .service-slider-zone { display: none; }
.slider-card.gallery-loading .gallery-skeleton-main {
    display: block;
    position: absolute;
    inset: 0;
    border-radius: 10px;
    z-index: 4;
}
.slider-card.gallery-loading .slider-nav-thumbnails { display: none; }
.slider-card.gallery-loading .gallery-skeleton-thumbs {
    display: flex;
    gap: 12px;
    margin: 16px 0 24px;
    justify-content: center;
}
.gallery-skeleton-thumbs .csk-block {
    flex: 1 1 0;
    max-width: 140px;
    aspect-ratio: 16 / 10;
    border-radius: 8px;
}

/* Accordion headers: keep long titles on one line, truncate with … instead of overflowing.
   Block layout + absolutely-positioned chevron so text-overflow works (flex breaks ellipsis). */
.accordions-items-seperate .accordion-button {
    position: relative;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 3rem;
}
.accordions-items-seperate .accordion-button::after {
    position: absolute;
    right: 1.25rem;
    top: 50%;
    margin: 0;
    transform: translateY(-50%);
}
.accordions-items-seperate .accordion-button:not(.collapsed)::after {
    transform: translateY(-50%) rotate(-180deg);
}

</style>
@endpush
@section('content')

{{-- Page data prep: media filtered to photos/images, boolean-feature → icon/label map, enum→i18n-key helper --}}
@php
    $mediaImages = collect($property['media'] ?? [])
        ->whereIn('type', ['Photo', 'Image'])
        ->filter(fn($m) => !empty(trim($m['url'] ?? '')))
        ->values();
    $primaryImage = $mediaImages->firstWhere('isPrimary', true)
        ?? $mediaImages->first();

    // Boolean фичи (fallback если features[] пуст)
    $boolMap = [
        'hasElevator' => ['icon' => 'elevator',       'label' => __('property-single.feature_elevator')],
        'hasParking'  => ['icon' => 'local_parking',  'label' => __('property-single.feature_parking')],
        'hasBalcony'  => ['icon' => 'balcony',         'label' => __('property-single.feature_balcony')],
        'hasGarage'   => ['icon' => 'garage',          'label' => __('property-single.feature_garage')],
        'hasPool'     => ['icon' => 'pool',            'label' => __('property-single.feature_pool')],
        'hasGarden'   => ['icon' => 'yard',            'label' => __('property-single.feature_garden')],
        'hasBasement' => ['icon' => 'foundation',      'label' => __('property-single.feature_basement')],
        'hasGym'      => ['icon' => 'fitness_center',  'label' => __('property-single.feature_gym')],
        'hasSecurity' => ['icon' => 'security',        'label' => __('property-single.feature_security')],
    ];
    $boolFeatures = [];
    foreach ($boolMap as $key => $meta) {
        if (!empty($property[$key])) $boolFeatures[] = $meta;
    }

    // Features[] иконки
    $featureIconMap = [
        'elevator' => 'elevator', 'parking' => 'local_parking', 'balcony' => 'balcony',
        'garage' => 'garage', 'pool' => 'pool', 'garden' => 'yard', 'basement' => 'foundation',
        'gym' => 'fitness_center', 'security' => 'security', 'loadingdock' => 'local_shipping',
        'panoramicwindows' => 'panorama', 'sauna' => 'hot_tub', 'fireplace' => 'local_fire_department',
        'gazebo' => 'deck', 'barbecuearea' => 'outdoor_grill', 'sportscourt' => 'sports',
    ];
    $applianceIconMap = [
        'washer' => 'local_laundry_service', 'dryer' => 'dry', 'fridge' => 'kitchen',
        'stove' => 'outdoor_grill', 'microwave' => 'microwave', 'coffeemaker' => 'coffee',
        'waterheater' => 'water_heater', 'hairdryer' => 'air', 'iron' => 'iron',
        'dishwasher' => 'dishwasher', 'vacuumcleaner' => 'cleaning_services',
    ];
    $utilityIconMap = [
        'electricity' => 'bolt', 'water' => 'water_drop', 'gas' => 'local_fire_department', 'sewage' => 'plumbing',
    ];

    // Enum переводы через helper — Str::snake даёт корректные ключи:
    // NotIncluded→not_included, ByAgreement→by_agreement (strtolower их слепляет)
    $enumKey = fn(string $prefix, string $value): string =>
        'property-single.' . $prefix . '_' . \Illuminate\Support\Str::snake($value);

    // История цен
    $priceHistory = collect($property['history'] ?? [])
        ->map(function ($entry) {
            return [
                'old' => $entry['oldValue'] ? json_decode($entry['oldValue'], true) : null,
                'new' => json_decode($entry['newValue'], true),
                'at'  => $entry['changedAt'] ?? null,
            ];
        })->filter(fn($e) => !empty($e['new']['amount']));

    // Условия аренды (показывать только для аренды)
    $isRental = str_starts_with(strtolower($property['transactionType'] ?? ''), 'rent');
@endphp

    <div class="page-wrapper" data-slug="{{ $property['slug'] ?? '' }}">

        <div class="buy-details-header-item"
            @if($primaryImage)
            style="background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{{ $primaryImage['url'] }}'), url('{{ asset('build/img/bg/breadcrumb-bg.jpg') }}'); background-size: cover; background-position: center;"
            @endif
        >

            <!-- Start Breadcrumb -->
            <div class="breadcrumb-bar custom-breadcrumb-bar">
                <div class="container">
                    <div class="row align-items-center text-center position-relative z-1">
                        <div class="col-xl-8">
                            <div class="d-flex align-center gap-2 mb-2 flex-wrap">
                                @php $__ptKey = 'property.' . strtolower($property['propertyType'] ?? ''); @endphp
                                <span class="badge bg-primary">{{ __($__ptKey) !== $__ptKey ? __($__ptKey) : ($property['propertyType'] ?? '') }}</span>
                                @php $txType = strtolower($property['transactionType'] ?? ''); @endphp
                                <span class="badge bg-secondary">
                                    @if($txType === 'rentdaily') {{ __('property-single.rent_daily') }}
                                    @elseif(str_starts_with($txType, 'rent')) {{ __('property-single.rent_monthly') }}
                                    @else {{ __('property-single.for_sale') }}
                                    @endif
                                </span>
                                @php $status = ucfirst(strtolower($property['status'] ?? '')); @endphp
                                @if($status === 'Draft')
                                <span class="badge bg-secondary">{{ __('property.status_draft') }}</span>
                                @elseif($status === 'Active')
                                <span class="badge bg-success">{{ __('property.status_active') }}</span>
                                @elseif($status === 'Sold')
                                <span class="badge bg-dark">{{ __('property.status_sold') }}</span>
                                @elseif($status === 'Rented')
                                <span class="badge bg-dark">{{ __('property.status_rented') }}</span>
                                @elseif($status === 'Reserved')
                                <span class="badge" style="background:#f59e0b">{{ __('property.status_reserved') }}</span>
                                @elseif($status === 'Inactive')
                                <span class="badge bg-secondary">{{ __('property.status_inactive') }}</span>
                                @endif
                                <span class="text-white-50 d-flex align-items-center gap-1 fs-14" id="view-count-wrap">
                                    <x-icon name="visibility" size="16"/>
                                    <span id="property-view-count" data-count="{{ $property['viewCount'] ?? 0 }}">{{ $property['viewCount'] ?? 0 }}</span> {{ __('property.views') }}
                                </span>
                            </div>
                            <h1 class="breadcrumb-title text-start">{{ $property['title'] ?? '' }}</h1>
                            <div class="d-flex align-items-center gap-2 flex-wrap gap-1 mb-xl-0 mb-4">
                                @if(!empty($property['createdAt']))
                                @php $__d = \Carbon\Carbon::parse($property['createdAt']); @endphp
                                <p class="fs-14 mb-0 text-white">{{ __('property-single.last_updated_on') }} {{ $__d->format('d') }} {{ __('property.' . strtolower($__d->format('M'))) }} {{ $__d->format('Y') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-xl-4 d-flex d-xl-block flex-wrap gap-3">
                            <div class="breadcrumb-icons d-flex align-items-center justify-content-xl-end justify-content-start gap-2 mb-xl-4 mb-2 mt-xl-0 mt-4">
                                <a href="javascript:void(0);" class="compare-btn" data-slug="{{ $property['slug'] ?? '' }}" aria-label="{{ __('header.compare') }}" title="{{ __('header.compare') }}"><x-icon name="balance" size="20"/></a>
                                <a href="javascript:void(0);" class="favourite" data-slug="{{ $property['slug'] ?? '' }}" aria-label="{{ __('property-single.favorite') }}"><x-icon name="favorite_border" class="rounded"/></a>
                                <a href="javascript:void(0);" class="share-btn" id="sharePropertyBtn"><x-icon name="share" class="rounded"/></a>
                            </div>
                            <div class="d-flex align-items-center gap-3 justify-content-xl-end justify-content-start">
                                <h4 class="mb-0 text-primary text-xl-end text-start">
                                    <x-price :amount="$property['price'] ?? 0" :currency="$property['currency'] ?? null" />
                                    @if($txType === 'rentmonthly')
                                        <span class="fs-14 fw-normal text-white">{{ __('property-single.per_month') }}</span>
                                    @elseif($txType === 'rentdaily')
                                        <span class="fs-14 fw-normal text-white">{{ __('property-single.per_day') }}</span>
                                    @endif
                                </h4>
                            </div>
                            <!-- Price in all other supported currencies (rebuilt client-side on currency switch) -->
                            @php $altPrices = all_currency_prices($property['price'] ?? 0, $property['currency'] ?? null); @endphp
                            <div class="price-alt-currencies text-xl-end text-start mt-1 js-price-alt"
                                 data-amount="{{ (float) ($property['price'] ?? 0) }}"
                                 data-currency="{{ $property['currency'] ?? display_currency() }}"
                                 @unless(count($altPrices)) style="display:none" @endunless>
                                @foreach($altPrices as $ap)
                                    <span class="price-alt-currency">{{ $ap['formatted'] }}</span>@unless($loop->last)<span class="price-alt-sep">·</span>@endunless
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Breadcrumb -->

        </div>

        <!-- Start Content -->
        <div class="content ps-page">
            <div class="container">

                <!-- start row -->
                <div class="row">
                    <div class="col-xl-8">

                        <!-- start slider -->
                        @if($mediaImages->isNotEmpty())
                        <div class="slider-card service-slider-card mb-4 gallery-loading">
                            <div class="slide-part mb-4 service-slider-wrap">
                                {{-- Skeleton shown until slick initializes (prevents the stacked-images jump) --}}
                                <div class="gallery-skeleton-main csk-block"></div>
                                <div class="slider service-slider">
                                    @foreach($mediaImages as $img)
                                    <div class="service-img-wrap">
                                        <img src="{{ $img['url'] }}" class="img-fluid"
                                            @if($loop->first) fetchpriority="high" loading="eager" @else loading="lazy" @endif
                                            alt="{{ $property['title'] ?? '' }}"
                                            onerror="this.onerror=null;this.parentElement.innerHTML='<div style=\'height:100%;min-height:400px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border-radius:8px;color:#aaa\'><svg width=64 height=64 fill=currentColor aria-hidden=true><use href=\'{{ asset('img/icons.svg') }}#icon-image\'/></svg></div>';">
                                    </div>
                                    @endforeach
                                </div>
                                <!-- Slider click zones: invisible left/right overlays that trigger slick prev/next (handler in resources/js/script.js) -->
                                @if($mediaImages->count() > 1)
                                <button type="button" class="service-slider-zone service-slider-zone--prev" aria-label="Previous image"></button>
                                <button type="button" class="service-slider-zone service-slider-zone--next" aria-label="Next image"></button>
                                @endif
                            </div>
                            @if($mediaImages->count() > 1)
                            {{-- Thumbnails skeleton shown until slick initializes --}}
                            <div class="gallery-skeleton-thumbs">
                                @for($i = 0; $i < 6; $i++)<span class="csk-block"></span>@endfor
                            </div>
                            <div class="slider slider-nav-thumbnails text-center">
                                @foreach($mediaImages as $img)
                                <div class="slide-img"><img src="{{ $img['thumbnailUrl'] ?? $img['url'] }}" class="img-fluid" loading="lazy" alt="{{ $property['title'] ?? '' }}" onerror="this.onerror=null;this.style.background='#f0f0f0';this.style.minHeight='80px';"></div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @elseif($property['primaryImageUrl'] ?? null)
                        <div class="mb-4">
                            <img src="{{ $property['primaryImageUrl'] }}" class="img-fluid rounded w-100" alt="{{ $property['title'] ?? '' }}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'img-placeholder\' style=\'height:400px;border-radius:8px;\'>832 x 472</div>';">
                        </div>
                        @else
                        <div class="mb-4">
                            <div class="img-placeholder" style="height:400px;border-radius:8px;">832 x 472</div>
                        </div>
                        @endif
                        <!-- End slider -->

                        <!-- Accordion sections -->
                        <div class="accordion accordions-items-seperate">

                            <!-- Media: Video / 360 / FloorPlan / Documents (auto-shown when present in API media[]) -->
                            @include('partials.property-single-media')

                            <!-- Description -->
                            @if($property['description'] ?? null)
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-1" aria-expanded="true">
                                        {{ __('property-single.description') }}
                                    </button>
                                </div>
                                <div id="accordion-1" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        {!! Purifier::clean($property['description'] ?? '') !!}
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Property Features -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-2" aria-expanded="true">
                                        {{ __('property-single.property_features') }}
                                    </button>
                                </div>
                                <div id="accordion-2" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-3">
                                            @if($property['bedrooms'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="bed" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.bedrooms') }}</small><strong>{{ $property['bedrooms'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['bathrooms'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="bathtub" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.bathrooms') }}</small><strong>{{ $property['bathrooms'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['rooms'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="meeting_room" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.rooms') }}</small><strong>{{ $property['rooms'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['areaTotal'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="straighten" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.area') }}</small><strong>{{ $property['areaTotal'] }} {{ __('property-single.meters') }}²</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['areaLiving'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="crop_square" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.area_living') }}</small><strong>{{ $property['areaLiving'] }} {{ __('property-single.meters') }}²</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['floor'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="layers" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.floor') }}</small><strong>{{ $property['floor'] }}@if($property['floorsTotal'] ?? null) / {{ $property['floorsTotal'] }}@endif</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['yearBuilt'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="calendar_today" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.year_built') }}</small><strong>{{ $property['yearBuilt'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['ceilingHeight'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="height" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.ceiling_height') }}</small><strong>{{ $property['ceilingHeight'] }} {{ __('property-single.meters') }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['landArea'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="landscape" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.land_area') }}</small><strong>{{ $property['landArea'] }} {{ __('property-single.meters') }}²</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['parkingSpaces'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="local_parking" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.parking_spaces') }}</small><strong>{{ $property['parkingSpaces'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['pricePerSqm'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="payments" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.price_per_sqm') }}</small><strong><x-price :amount="$property['pricePerSqm']" :currency="$property['currency'] ?? null" /></strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['totalUnits'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="apartment" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.total_units') }}</small><strong>{{ $property['totalUnits'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['availableUnits'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="door_front" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.available_units') }}</small><strong>{{ $property['availableUnits'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['totalBuildings'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="domain" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.total_buildings') }}</small><strong>{{ $property['totalBuildings'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['code'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="tag" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.code') }}</small><strong>#{{ $property['code'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['renovationType'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="construction" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.renovation_type') }}</small><strong>{{ __($enumKey('renovation', $property['renovationType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['constructionType'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="architecture" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.construction_type') }}</small><strong>{{ __($enumKey('construction', $property['constructionType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['furnitureType'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="chair" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.furniture_type') }}</small><strong>{{ __($enumKey('furniture', $property['furnitureType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['balconyType'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="balcony" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.balcony_type') }}</small><strong>{{ __($enumKey('balcony', $property['balconyType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['terraceType'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="deck" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.terrace_type') }}</small><strong>{{ __($enumKey('terrace', $property['terraceType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            {{-- Tiles below show only single-value variants; multi-value sets render in the "additional_details" accordion below. --}}
                                            @if(!empty($property['heatingType']) && count((array)$property['heatingType']) === 1)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="thermostat" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.heating_type') }}</small><strong>{{ __($enumKey('heating', ((array)$property['heatingType'])[0])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['parkingType']) && count((array)$property['parkingType']) === 1)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="local_parking" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.parking_type') }}</small><strong>{{ __($enumKey('parking', ((array)$property['parkingType'])[0])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['windowView']) && count((array)$property['windowView']) === 1)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="panorama" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.window_view') }}</small><strong>{{ __($enumKey('view', ((array)$property['windowView'])[0])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['zoningType']))
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="map" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.zoning_type') }}</small><strong>{{ __($enumKey('zoning', $property['zoningType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(isset($property['sunDirection']) && $property['sunDirection'] !== null && $property['sunDirection'] !== false)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="wb_sunny" class="text-warning"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.sun_direction') }}</small><strong>{{ $property['sunDirection'] ? __('property-single.yes') : __('property-single.no') }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional details: multi-value fields (parking, views, heating) rendered as full-width checklist blocks -->
                            @php
                                $multiBlocks = [];
                                if (!empty($property['parkingType']) && count((array)$property['parkingType']) > 1) {
                                    $multiBlocks[] = ['icon' => 'local_parking', 'label' => __('property-single.parking_type'), 'values' => (array)$property['parkingType'], 'prefix' => 'parking'];
                                }
                                if (!empty($property['windowView']) && count((array)$property['windowView']) > 1) {
                                    $multiBlocks[] = ['icon' => 'panorama', 'label' => __('property-single.window_view'), 'values' => (array)$property['windowView'], 'prefix' => 'view'];
                                }
                                if (!empty($property['heatingType']) && count((array)$property['heatingType']) > 1) {
                                    $multiBlocks[] = ['icon' => 'thermostat', 'label' => __('property-single.heating_type'), 'values' => (array)$property['heatingType'], 'prefix' => 'heating'];
                                }
                            @endphp
                            @if(!empty($multiBlocks))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-extra" aria-expanded="true">
                                        {{ __('property-single.additional_details') }}
                                    </button>
                                </div>
                                <div id="accordion-extra" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="d-flex flex-column gap-4">
                                            @foreach($multiBlocks as $block)
                                            <div class="multi-detail-block">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <x-icon :name="$block['icon']" class="text-primary"/>
                                                    <strong class="fs-15">{{ $block['label'] }}</strong>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-1">
                                                    @foreach($block['values'] as $v)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <x-icon name="check" size="16" class="text-success"/>
                                                        <span>{{ __($enumKey($block['prefix'], $v)) }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Boolean features -->
                            @if(!empty($boolFeatures))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-3" aria-expanded="true">
                                        {{ __('property-single.about_property') }}
                                    </button>
                                </div>
                                <div id="accordion-3" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($boolFeatures as $feat)
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="bool-pill d-inline-flex align-items-center gap-2">
                                                    <x-icon :name="$feat['icon']" class="text-primary" size="18"/>
                                                    <span>{{ $feat['label'] }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Amenities -->
                            @if(!empty($property['amenities']))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-4" aria-expanded="true">
                                        {{ __('property-single.amenities') }}
                                    </button>
                                </div>
                                <div id="accordion-4" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-3">
                                            @foreach($property['amenities'] as $amenity)
                                            @php $__amKey = 'property-single.amenity_' . strtolower($amenity); @endphp
                                            <div class="col-12 col-md-6 col-xl-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="check" class="text-secondary"/>
                                                    <span>{{ __($__amKey) !== $__amKey ? __($__amKey) : $amenity }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Features[] array -->
                            @if(!empty($property['features']))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-feat" aria-expanded="true">
                                        {{ __('property-single.features_section') }}
                                    </button>
                                </div>
                                <div id="accordion-feat" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($property['features'] as $feat)
                                            @php $fKey = strtolower($feat); @endphp
                                            <div class="col-12 col-md-6 col-xl-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon :name="$featureIconMap[$fKey] ?? 'check_circle'" class="text-success"/>
                                                    <span>{{ __($enumKey('feature', $feat)) }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Appliances -->
                            @if(!empty($property['appliances']))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-appl" aria-expanded="true">
                                        {{ __('property-single.appliances_section') }}
                                    </button>
                                </div>
                                <div id="accordion-appl" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($property['appliances'] as $appl)
                                            @php $aKey = strtolower($appl); @endphp
                                            <div class="col-12 col-md-6 col-xl-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon :name="$applianceIconMap[$aKey] ?? 'devices'" class="text-primary"/>
                                                    <span>{{ __($enumKey('appliance', $appl)) }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Utilities -->
                            @if(!empty($property['utilities']))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-util" aria-expanded="true">
                                        {{ __('property-single.utilities_section') }}
                                    </button>
                                </div>
                                <div id="accordion-util" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($property['utilities'] as $util)
                                            @php $uKey = strtolower($util); @endphp
                                            <div class="col-12 col-md-6 col-xl-4">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon :name="$utilityIconMap[$uKey] ?? 'power'" class="text-warning"/>
                                                    <span>{{ __($enumKey('utility', $util)) }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Rental conditions -->
                            @if($isRental && ($property['deposit'] ?? $property['petsPolicy'] ?? $property['utilitiesPolicy'] ?? null))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-cond" aria-expanded="true">
                                        {{ __('property-single.rental_conditions') }}
                                    </button>
                                </div>
                                <div id="accordion-cond" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-3">
                                            @if($property['deposit'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-1">
                                                    <x-icon name="account_balance_wallet" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.deposit') }}</small><strong><x-price :amount="$property['deposit']" :currency="$property['currency'] ?? null" /></strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['utilitiesPolicy'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-1">
                                                    <x-icon name="receipt_long" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.utilities_policy') }}</small><strong>{{ __($enumKey('utilities', $property['utilitiesPolicy'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['petsPolicy'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-1">
                                                    <x-icon name="pets" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.pets_policy') }}</small><strong>{{ __($enumKey('policy', $property['petsPolicy'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['childrenPolicy'] ?? null)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-1">
                                                    <x-icon name="child_care" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.children_policy') }}</small><strong>{{ __($enumKey('policy', $property['childrenPolicy'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['isNegotiable']))
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-1">
                                                    <x-icon name="handshake" class="text-success"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.negotiable') }}</small><strong>{{ __('property-single.yes') }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['isLongTermRental']))
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item d-flex align-items-center gap-1">
                                                    <x-icon name="date_range" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.long_term_rental') }}</small><strong>{{ __('property-single.yes') }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Extended attributes -->
                            @if(!empty($property['extendedAttributes']) && is_array($property['extendedAttributes']))
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-ext" aria-expanded="true">
                                        {{ __('property-single.extended_attributes') }}
                                    </button>
                                </div>
                                <div id="accordion-ext" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="row row-gap-3">
                                            @foreach($property['extendedAttributes'] as $attrKey => $attrVal)
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="feature-item">
                                                    <small class="text-muted d-block">{{ $attrKey }}</small>
                                                    <strong>{{ $attrVal }}</strong>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Price history -->
                            @if($priceHistory->isNotEmpty())
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-hist" aria-expanded="true">
                                        {{ __('property-single.price_history') }}
                                    </button>
                                </div>
                                <div id="accordion-hist" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <ul class="price-history-list list-unstyled mb-0">
                                            @foreach($priceHistory as $entry)
                                            @php
                                                $newAmt  = $entry['new']['amount'] ?? 0;
                                                $oldAmt  = $entry['old']['amount'] ?? null;
                                                $cur     = $entry['new']['currency'] ?? ($entry['old']['currency'] ?? ($property['currency'] ?? ''));
                                                $date    = $entry['at'] ? \Carbon\Carbon::parse($entry['at'])->format('d.m.Y') : '';
                                                if ($oldAmt === null) {
                                                    $label = __('property-single.price_listed_at');
                                                    $icon  = 'sell'; $color = 'text-primary';
                                                } elseif ($newAmt > $oldAmt) {
                                                    $label = __('property-single.price_increased');
                                                    $icon  = 'trending_up'; $color = 'text-danger';
                                                } else {
                                                    $label = __('property-single.price_decreased');
                                                    $icon  = 'trending_down'; $color = 'text-success';
                                                }
                                            @endphp
                                            <li class="price-history-item d-flex align-items-start gap-3 pb-3">
                                                <x-icon :name="$icon" :class="($color) . ' mt-1'"/>
                                                <div>
                                                    <small class="text-muted">{{ $date }}</small>
                                                    <div>{{ $label }}: <strong><x-price :amount="$newAmt" :currency="$cur" /></strong>
                                                    @if($oldAmt !== null) <span class="text-muted ms-1 text-decoration-line-through"><x-price :amount="$oldAmt" :currency="$cur" /></span>@endif
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif


                            <!-- Comments — skeleton-first: injected async into #ps-comments via /property/{slug}/extras -->
                            <div id="ps-comments">
                                <div class="accordion-item mb-xl-0">
                                    <div class="accordion-body">
                                        <div class="csk-block mb-3" style="width:170px;height:18px;border-radius:4px"></div>
                                        <div class="csk-block mb-2" style="width:100%;height:64px;border-radius:8px"></div>
                                        <div class="csk-block" style="width:100%;height:64px;border-radius:8px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- col end -->

                    <!-- ============================================================
                         Sidebar (right column): Provider details + Property details
                         + Enquire (currently hidden) + Location details (map)
                         ============================================================ -->
                    <div class="col-xl-4 buy-details-item">
                        <div class="property-single-sidebar">
                        <!-- Agent -->
                        <div class="card agent-card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('property-single.provider_details') }}</h5>
                            </div>
                            <div class="card-body agent-card-body text-center">

                                {{-- Workspace: logo or initial --}}
                                @if(!empty($workspace['name']))
                                <div class="agent-ws-row mb-3">
                                    @if(!empty($workspace['logoUrl']))
                                    <img src="{{ $workspace['logoUrl'] }}" alt="{{ $workspace['name'] }}" class="agent-ws-logo">
                                    @else
                                    <div class="agent-ws-initial">{{ mb_strtoupper(mb_substr($workspace['name'], 0, 1)) }}</div>
                                    @endif
                                    <p class="mb-0 fw-semibold fs-15 mt-2">{{ $workspace['name'] }}</p>
                                </div>
                                @endif

                                {{-- Agent --}}
                                @php $agentName = $property['assignedAgentName'] ?? ''; @endphp
                                @if($agentName)
                                @if(!empty($workspace['name']))<hr class="my-3">@endif
                                <div class="agent-avatar-circle mb-2">{{ mb_strtoupper(mb_substr($agentName, 0, 1)) }}</div>
                                <p class="mb-0 fw-bold fs-16">{{ $agentName }}</p>
                                <p class="mb-3 fs-13 text-muted">{{ __('property-single.company_agent') }}</p>
                                @endif

                                <!-- Contact: single button → opens modal with phone / Viber / WhatsApp / email -->
                                @php
                                    $wsMsg     = $workspace['messengers'] ?? [];
                                    $agentE164 = preg_replace('/[^0-9]/', '', $property['agentPhoneE164'] ?? '');
                                    // Prefer the listing's own agent contact; fall back to the broker workspace.
                                    $cPhoneShown = ($property['assignedAgentPhone'] ?? '') ?: ($workspace['phone'] ?? '');
                                    $cPhoneDial  = $agentE164 ?: preg_replace('/[^0-9]/', '', $workspace['phone'] ?? '');
                                    $cViber      = $agentE164 ?: preg_replace('/[^0-9]/', '', $wsMsg['viber']    ?? $workspace['phone'] ?? '');
                                    $cWhats      = $agentE164 ?: preg_replace('/[^0-9]/', '', $wsMsg['whatsApp'] ?? $workspace['phone'] ?? '');
                                    $cEmail      = $workspace['email'] ?? '';
                                    $hasContact  = $cPhoneDial || $cEmail;
                                @endphp
                                @if($hasContact)
                                <button type="button" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    <x-icon name="phone" size="18"/>
                                    <span class="fw-semibold">{{ __('property-single.contact') }}</span>
                                </button>
                                @endif

                            </div>
                        </div>

                        @if($hasContact)
                        <!-- Contact modal (phone / Viber / WhatsApp / email) -->
                        <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold mb-0">{{ __('property-single.contact_title') }}</h5>
                                        <button type="button" class="btn-close btn-close-modal custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><x-icon name="close"/></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex flex-column gap-2">
                                            @if($cPhoneDial)
                                            <a href="tel:+{{ $cPhoneDial }}" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-dark" style="border:1px solid var(--gray-100)">
                                                <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;border-radius:50%;background:#eef3fb;color:#1565c0"><x-icon name="phone" size="18"/></span>
                                                <span class="fw-semibold">{{ $cPhoneShown }}</span>
                                            </a>
                                            @endif
                                            @if($cViber)
                                            <a href="viber://chat?number=+{{ $cViber }}" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-dark" style="border:1px solid var(--gray-100)">
                                                <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;border-radius:50%;background:#7360f2;color:#fff"><svg width="18" height="18" fill="currentColor" aria-hidden="true" viewBox="0 0 24 24"><path d="M11.4 0C9.473.024 5.333.34 3.02 2.464 1.302 4.187.696 6.7.633 9.819.572 12.93.494 18.762 6.18 20.378v2.463s-.04 1.005.61 1.205c.795.246 1.252-.5 2.005-1.293.42-.435.992-1.082 1.43-1.59 3.85.327 6.812-.41 7.144-.515.78-.254 5.187-.811 5.91-6.66.74-6.034-.376-9.86-2.394-11.582l-.013-.005C20.27.65 16.36.027 13.59 0c0 0-.193-.011-.633-.014-.222 0-.598-.005-.998 0-.4-.005-.776 0-.998 0-.44.003-.633.014-.633.014z"/></svg></span>
                                                <span class="fw-semibold">Viber</span>
                                            </a>
                                            @endif
                                            @if($cWhats)
                                            <a href="https://wa.me/{{ $cWhats }}" target="_blank" rel="noopener" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-dark" style="border:1px solid var(--gray-100)">
                                                <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;border-radius:50%;background:#25d366;color:#fff"><svg width="18" height="18" fill="currentColor" aria-hidden="true" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l.236.375-.999 3.648 3.742-.982zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.296-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.695.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg></span>
                                                <span class="fw-semibold">WhatsApp</span>
                                            </a>
                                            @endif
                                            @if($cEmail)
                                            <a href="mailto:{{ $cEmail }}" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-dark" style="border:1px solid var(--gray-100)">
                                                <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;border-radius:50%;background:#f0f0f0;color:#555"><x-icon name="mail" size="18"/></span>
                                                <span class="fw-semibold text-break">{{ $cEmail }}</span>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Property Details -->
                        <div class="card prop-details-card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('property-single.property_details') }}</h5>
                            </div>
                            <div class="card-body">
                                @if($property['propertyType'] ?? null)
                                @php $__pdType = 'property.' . strtolower($property['propertyType']); @endphp
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted">{{ __('property-single.property_type') }}</span>
                                    <span class="fw-semibold">{{ __($__pdType) !== $__pdType ? __($__pdType) : $property['propertyType'] }}</span>
                                </div>
                                @endif
                                @if($property['transactionType'] ?? null)
                                @php $__pdTx = strtolower($property['transactionType']); @endphp
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted">{{ __('property-single.transaction_type') }}</span>
                                    <span class="fw-semibold">
                                        @if($__pdTx === 'rentdaily') {{ __('property-single.rent_daily') }}
                                        @elseif(str_starts_with($__pdTx, 'rent')) {{ __('property-single.rent_monthly') }}
                                        @else {{ __('property-single.for_sale') }}
                                        @endif
                                    </span>
                                </div>
                                @endif
                                @if($property['status'] ?? null)
                                @php $__pdStatus = ucfirst(strtolower($property['status'])); @endphp
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted">{{ __('property-single.status') }}</span>
                                    <span>
                                        @if($__pdStatus === 'Draft')<span class="badge bg-secondary">{{ __('property.status_draft') }}</span>
                                        @elseif($__pdStatus === 'Active')<span class="badge bg-success">{{ __('property.status_active') }}</span>
                                        @elseif($__pdStatus === 'Sold')<span class="badge bg-dark">{{ __('property.status_sold') }}</span>
                                        @elseif($__pdStatus === 'Rented')<span class="badge bg-dark">{{ __('property.status_rented') }}</span>
                                        @elseif($__pdStatus === 'Reserved')<span class="badge" style="background:#f59e0b">{{ __('property.status_reserved') }}</span>
                                        @elseif($__pdStatus === 'Inactive')<span class="badge bg-secondary">{{ __('property.status_inactive') }}</span>
                                        @else<span class="fw-semibold">{{ $property['status'] }}</span>
                                        @endif
                                    </span>
                                </div>
                                @endif
                                @if($property['currency'] ?? null)
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted">{{ __('property-single.currency') }}</span>
                                    <span class="fw-semibold js-currency-code">{{ display_currency() }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Enquire — TEMPORARILY HIDDEN. To restore: change @if(false) below to @if(true) (or remove the @if/@endif). --}}
                        @if(false)
                        <!-- Enquire -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('property-single.enquire_us') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id="enquireSuccess" class="alert alert-success d-none mb-3">
                                    {{ __('property-single.enquire_success') }}
                                </div>
                                <div id="enquireError" class="alert alert-danger d-none mb-3"></div>
                                <form id="enquireForm" data-slug="{{ $property['slug'] ?? '' }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ __('property-single.name') }}</label>
                                        <input type="text" name="name" class="form-control" placeholder="{{ __('property-single.your_name') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ __('property-single.email') }}</label>
                                        <input type="email" name="email" class="form-control" placeholder="{{ __('property-single.your_email') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ __('property-single.phone') }}</label>
                                        <input type="tel" name="phone" class="form-control" placeholder="{{ __('property-single.your_phone') }}">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">{{ __('property-single.description_label') }}</label>
                                        <textarea name="message" class="form-control" rows="3" style="resize:none;overflow:hidden" placeholder="{{ __('property-single.enquire_message_placeholder', ['title' => $property['title'] ?? '']) }}"></textarea>
                                    </div>
                                    <button type="submit" id="enquireSubmit" class="btn btn-dark w-100 py-2 fs-14">
                                        {{ __('property-single.submit') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                        {{-- /Enquire (temporarily hidden) --}}

                        <!-- Location details -->
                        @php
                            $__hasCoords  = ($property['latitude'] ?? null) && ($property['longitude'] ?? null);
                            $__geoAddress = $property['fullAddress'] ?? trim(implode(', ', array_filter([$property['street'] ?? null, $property['buildingNumber'] ?? null, $property['city'] ?? null, $property['country'] ?? null])));
                            $__canMap     = $__hasCoords || $__geoAddress !== '';   // map+POI work via coords OR by geocoding the address client-side
                        @endphp
                        @if(($property['country'] ?? null) || ($property['city'] ?? null) || ($property['street'] ?? null) || $__hasCoords)
                        <div class="card mb-0">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('property-single.location_details') }}</h5>
                            </div>
                            <div class="card-body">
                                {{-- Country / City / Street (shown even without coordinates) --}}
                                @if($property['country'] ?? null)
                                <div class="d-flex justify-content-between align-items-center gap-4 py-2 border-bottom">
                                    <span class="text-muted">{{ __('property-single.country') }}</span>
                                    <span class="fw-semibold fs-14">{{ $property['country'] }}</span>
                                </div>
                                @endif
                                @if($property['city'] ?? null)
                                <div class="d-flex justify-content-between align-items-center gap-4 py-2 border-bottom">
                                    <span class="text-muted">{{ __('property-single.city') }}</span>
                                    <span class="fw-semibold fs-14">{{ $property['city'] }}</span>
                                </div>
                                @endif
                                @if($property['street'] ?? null)
                                <div class="d-flex justify-content-between align-items-center gap-4 py-2 border-bottom">
                                    <span class="text-muted">{{ __('property-single.street') }}</span>
                                    <span class="fw-semibold text-end fs-14">{{ $property['street'] }}{{ !empty($property['buildingNumber']) ? ', ' . $property['buildingNumber'] : '' }}</span>
                                </div>
                                @endif

                                @if($__canMap)
                                {{-- Additional information: nearest POI per category (filled by JS) --}}
                                <div id="poiLoading" style="display:none; margin-top:16px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-14 text-muted">{{ __('property-single.additional_info') }}</span>
                                        <div class="spinner-border spinner-border-sm ms-1" style="color:#03bd9d; width:14px; height:14px; border-width:2px;" role="status"></div>
                                    </div>
                                </div>
                                <div id="poiAdditional" style="display:none; margin-top:16px;">
                                    <h6 class="mb-2 fs-14 text-muted">{{ __('property-single.additional_info') }}</h6>
                                    <div id="poiNearestList"></div>
                                </div>

                                {{-- Nearby: category tabs that toggle POI markers on the map below --}}
                                <div id="poiTabs" style="display:none; margin-top:16px; border-top:1px solid var(--gray-100); padding-top:12px;">
                                    <h6 class="mb-2 fs-14 text-muted">{{ __('property-single.nearby') }}</h6>
                                    <div class="d-flex gap-2 poi-tabs-row">
                                        <button type="button" class="btn btn-sm poi-tab flex-shrink-0" data-cat="transport">{{ __('property-single.cat_transport') }}</button>
                                        <button type="button" class="btn btn-sm poi-tab flex-shrink-0" data-cat="education">{{ __('property-single.cat_education') }}</button>
                                        <button type="button" class="btn btn-sm poi-tab flex-shrink-0" data-cat="food">{{ __('property-single.cat_food') }}</button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($__canMap)
                            {{-- Map --}}
                            <div class="custom-map position-relative">
                                <button class="map-fullscreen-btn" id="mapFullscreenBtn" title="Fullscreen">
                                    <x-icon name="fullscreen"/>
                                </button>
                                {{-- Кнопка центрирования на объекте --}}
                                <div id="mapControlBtns" class="map-control-btns" style="display:none;">
                                    <button id="mapGoHome" class="map-control-btn map-control-btn--home" title="{{ __('property-single.location') }}">
                                        <x-icon name="home"/>
                                    </button>
                                </div>
                                <div id="propertyJsMap" style="width:100%;height:195px;"></div>
                            </div>
                            @endif
                        </div>

                        @if($__canMap)
                        <!-- Map fullscreen overlay (appended to body via JS) -->
                        <div id="mapFullscreenOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; background:#000;">
                            <div id="mapFullscreenJsMap" style="width:100%;height:100%;"></div>
                        </div>
                        <!-- Кнопка закрытия fullscreen — вне карты, всегда поверх -->
                        <button id="mapFullscreenClose" style="display:none; position:fixed; top:12px; left:12px; z-index:2147483647; width:40px; height:40px; border:none; border-radius:6px; background:rgba(0,0,0,0.7); color:#fff; cursor:pointer; align-items:center; justify-content:center;">
                            <x-icon name="fullscreen_exit" size="24"/>
                        </button>
                        <!-- Category tabs over the fullscreen map -->
                        <div id="mapFsTabs" style="display:none; position:fixed; bottom:12px; left:50%; transform:translateX(-50%); z-index:2147483647; gap:8px; background:rgba(0,0,0,0.55); padding:6px; border-radius:24px;">
                            <button type="button" class="btn btn-sm poi-tab poi-tab-fs" data-cat="transport">{{ __('property-single.cat_transport') }}</button>
                            <button type="button" class="btn btn-sm poi-tab poi-tab-fs" data-cat="education">{{ __('property-single.cat_education') }}</button>
                            <button type="button" class="btn btn-sm poi-tab poi-tab-fs" data-cat="food">{{ __('property-single.cat_food') }}</button>
                        </div>
                        @endif
                        @endif

                        </div>{{-- property-single-sidebar --}}
                    </div> <!-- col end -->
                </div>
                <!-- end row -->


                <!-- Similar properties — skeleton-first: injected async into #ps-similar via /property/{slug}/extras -->
                <div id="ps-similar">
                    <h4 class="mt-5 mb-4 ps-2 csk-block" style="width:240px;height:24px;border-radius:6px"></h4>
                    <div class="row row-gap-4">
                        @for($i = 0; $i < 3; $i++)
                        <div class="col-xl-4 col-md-6">
                            <div class="csk-block" style="width:100%;height:220px;border-radius:12px"></div>
                            <div class="csk-block mt-2" style="width:80%;height:16px;border-radius:4px"></div>
                            <div class="csk-block mt-2" style="width:50%;height:14px;border-radius:4px"></div>
                        </div>
                        @endfor
                    </div>
                </div>

            </div>
        </div>
        <!-- End Content -->

    </div>

{{-- Mobile floating CTA --}}
@php $agentPhone = $property['assignedAgentPhone'] ?? ($property['agentPhone'] ?? null); @endphp
@if(!empty($agentPhone))
<div class="mobile-cta d-xl-none">
    <a href="tel:{{ $agentPhone }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
        <x-icon name="phone"/>
        {{ __('property-single.call_us') }}
    </a>
</div>
@endif

<!-- Map: Yandex Map init + fullscreen overlay + nearby POI (transport via OSM, education via Yandex) + category tabs -->
<script>
(function () {
    var btn       = document.getElementById('mapFullscreenBtn');
    var overlay   = document.getElementById('mapFullscreenOverlay');
    var closeBtn  = document.getElementById('mapFullscreenClose');
    var fsMapEl   = document.getElementById('mapFullscreenJsMap');
    var fsTabs    = document.getElementById('mapFsTabs');
    var mapEl     = document.getElementById('propertyJsMap');

    if (!mapEl) return;
    if (btn && overlay) document.body.appendChild(overlay);

    var lat       = {{ $property['latitude'] ?? 'null' }};
    var lng       = {{ $property['longitude'] ?? 'null' }};
    var propTitle = @json($property['title'] ?? '');
    var locale    = @json(app()->getLocale());
    var address     = @json($__geoAddress);
    var addrStreet  = @json($property['street'] ?? '');
    var addrCity    = @json($property['city'] ?? '');
    var addrCountry = @json($property['country'] ?? '');

    // Need coordinates OR an address (to geocode) — otherwise nothing to show
    if ((!lat || !lng) && !address) return;

    var mainMap   = null;
    var poiLayer  = null;   // active-category markers on the main map
    var fsMap     = null;
    var fsPoiLayer = null;  // active-category markers on the fullscreen map
    var activeCat = null;

    // Category meta: marker color + sprite icon id
    var CATS = {
        transport: { color: '#03bd9d', icon: 'train' },
        education: { color: '#1565c0', icon: 'domain' },
        food:      { color: '#e67e22', icon: 'shopping_cart' }
    };
    var CAT_ORDER = ['transport', 'education', 'food'];
    var poiData = { transport: [], education: [], food: [] };

    // ── icons ──
    var homeIconSvg = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="52" viewBox="0 0 40 52">' +
        '<path d="M20 0C8.95 0 0 8.95 0 20c0 14 20 32 20 32s20-18 20-32C40 8.95 31.05 0 20 0z" fill="#E74C3C"/>' +
        '<path d="M20 2C9.51 2 1 10.51 1 20c0 13.37 19 30.5 19 30.5S39 33.37 39 20C39 10.51 30.49 2 20 2z" fill="#FF6B6B"/>' +
        '<path d="M20 5C11.18 5 4 12.18 4 21c0 11.8 16 27 16 27s16-15.2 16-27C36 12.18 28.82 5 20 5z" fill="#E74C3C"/>' +
        '<g transform="translate(20,20) scale(0.72) translate(-12,-12)">' +
        '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="#fff"/>' +
        '</g></svg>'
    );
    function pinSvg(color) {
        return 'data:image/svg+xml,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="42" viewBox="0 0 32 42">' +
            '<path d="M16 0C7.16 0 0 7.16 0 16c0 11.2 16 26 16 26s16-14.8 16-26C32 7.16 24.84 0 16 0z" fill="' + color + '"/>' +
            '<circle cx="16" cy="15" r="6" fill="#fff"/></svg>'
        );
    }

    function fmtDist(km) { return km < 1 ? Math.round(km * 1000) + ' м' : km.toFixed(1) + ' км'; }
    function haversine(la, lo) {
        var R = 6371, dLat = (la - lat) * Math.PI / 180, dLon = (lo - lng) * Math.PI / 180;
        var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat*Math.PI/180)*Math.cos(la*Math.PI/180)*Math.sin(dLon/2)*Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function homePlacemark() {
        return new ymaps.Placemark([lat, lng], { balloonContent: propTitle }, {
            iconLayout: 'default#image', iconImageHref: homeIconSvg,
            iconImageSize: [28, 36], iconImageOffset: [-14, -36]
        });
    }
    function poiPlacemark(item, color) {
        return new ymaps.Placemark([item.lat, item.lon], {
            balloonContent: '<strong>' + item.name + '</strong><br>' + fmtDist(item.km)
        }, {
            iconLayout: 'default#image', iconImageHref: pinSvg(color),
            iconImageSize: [26, 34], iconImageOffset: [-13, -34]
        });
    }
    function calcBounds(items) {
        var minLat = lat, maxLat = lat, minLon = lng, maxLon = lng;
        (items || []).forEach(function (p) {
            if (p.lat < minLat) minLat = p.lat;
            if (p.lat > maxLat) maxLat = p.lat;
            if (p.lon < minLon) minLon = p.lon;
            if (p.lon > maxLon) maxLon = p.lon;
        });
        return [[minLat, minLon], [maxLat, maxLon]];
    }

    var goHomeBtn   = document.getElementById('mapGoHome');
    var controlBtns = document.getElementById('mapControlBtns');

    // ── main map ──
    function initMap() {
        ymaps.ready(function () {
            mainMap = new ymaps.Map(mapEl, { center: [lat, lng], zoom: 16, controls: ['zoomControl'] });
            mainMap.geoObjects.add(homePlacemark());
            poiLayer = new ymaps.GeoObjectCollection();
            mainMap.geoObjects.add(poiLayer);
            if (controlBtns) controlBtns.style.display = 'flex';
            // Force correct sizing in case the container was measured before layout settled
            mainMap.container.fitToViewport();
        });
    }
    // initMap() is invoked from boot() once coordinates are known (see bottom)

    if (goHomeBtn) {
        goHomeBtn.addEventListener('click', function () {
            if (!mainMap) return;
            mainMap.setCenter([lat, lng], 16, { duration: 400 });
        });
    }

    // ── fullscreen ──
    function openFs() {
        if (typeof ymaps === 'undefined' || !mainMap) return;
        overlay.style.display = 'block';
        if (closeBtn) closeBtn.style.display = 'flex';
        if (fsTabs) fsTabs.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        fsMapEl.innerHTML = '';
        setTimeout(function () {
            fsMap = new ymaps.Map(fsMapEl, { center: [lat, lng], zoom: 16, controls: ['zoomControl'] });
            fsMap.geoObjects.add(homePlacemark());
            fsPoiLayer = new ymaps.GeoObjectCollection();
            fsMap.geoObjects.add(fsPoiLayer);
            if (activeCat) renderCategoryOnMap(fsMap, fsPoiLayer, activeCat);
        }, 50);
    }
    function closeFs() {
        overlay.style.display = 'none';
        if (closeBtn) closeBtn.style.display = 'none';
        if (fsTabs) fsTabs.style.display = 'none';
        fsMapEl.innerHTML = '';
        if (fsMap) { fsMap.destroy(); fsMap = null; }
        fsPoiLayer = null;
        document.body.style.overflow = '';
    }
    if (btn) btn.addEventListener('click', openFs);
    if (closeBtn) closeBtn.addEventListener('click', closeFs);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeFs(); });

    // ── category tabs: toggle POI markers on the given map ──
    function renderCategoryOnMap(map, layer, cat) {
        if (!map || !layer) return;
        layer.removeAll();
        var items = poiData[cat] || [];
        items.forEach(function (p) { layer.add(poiPlacemark(p, CATS[cat].color)); });
        if (items.length) {
            map.setBounds(calcBounds(items), { checkZoomRange: true, duration: 400, padding: [40, 40, 40, 40] });
        } else {
            map.setCenter([lat, lng], 16, { duration: 400 });
        }
    }
    function selectCategory(cat) {
        activeCat = cat;
        renderCategoryOnMap(mainMap, poiLayer, cat);
        if (fsMap && fsPoiLayer) renderCategoryOnMap(fsMap, fsPoiLayer, cat);
        // Highlight active tab in both the inline and fullscreen tab bars
        document.querySelectorAll('.poi-tab, .poi-tab-fs').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-cat') === cat);
        });
    }
    document.querySelectorAll('.poi-tab, .poi-tab-fs').forEach(function (b) {
        b.addEventListener('click', function () { selectCategory(b.getAttribute('data-cat')); });
    });

    // ── fetch POI data (uses resolved lat/lng) ──
    function fetchPOI() {
        var loadingEl = document.getElementById('poiLoading');
        var addBlock  = document.getElementById('poiAdditional');
        var nearList  = document.getElementById('poiNearestList');
        var tabsBlock = document.getElementById('poiTabs');
        if (loadingEl) loadingEl.style.display = 'block';

        // One Overpass query for all categories (transport / education / supermarkets)
        var q = '[out:json][timeout:30];(' +
            'node["station"="subway"](around:2000,' + lat + ',' + lng + ');' +
            'node["railway"="station"](around:2000,' + lat + ',' + lng + ');' +
            'nwr["amenity"="school"](around:1500,' + lat + ',' + lng + ');' +
            'nwr["amenity"="university"](around:1500,' + lat + ',' + lng + ');' +
            'nwr["shop"="supermarket"](around:1500,' + lat + ',' + lng + ');' +
        ');out center tags 60;';
        function runOverpass(server) {
            // Abort after 30s so a stuck server fails over to the mirror instead of hanging
            var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
            var to = ctrl ? setTimeout(function () { ctrl.abort(); }, 30000) : null;
            return fetch(server, {
                method: 'POST',
                body: 'data=' + encodeURIComponent(q),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                signal: ctrl ? ctrl.signal : undefined
            }).then(function (r) { if (to) clearTimeout(to); return r.json(); });
        }
        function catOf(t) {
            if (t.station === 'subway' || t.railway === 'station') return 'transport';
            if (t.amenity === 'school' || t.amenity === 'university') return 'education';
            if (t.shop === 'supermarket') return 'food';
            return null;
        }

        runOverpass('https://overpass-api.de/api/interpreter')
            .catch(function () { return runOverpass('https://overpass.kumi.systems/api/interpreter'); })
            .then(function (data) {
                var seen = { transport: {}, education: {}, food: {} };
                (data.elements || []).forEach(function (el) {
                    var t = el.tags || {};
                    var cat = catOf(t);
                    if (!cat) return;
                    var elat = el.lat != null ? el.lat : (el.center && el.center.lat);
                    var elon = el.lon != null ? el.lon : (el.center && el.center.lon);
                    var name = t['name:' + locale] || t['name:en'] || t['name'] || '';
                    if (elat == null || elon == null || !name || seen[cat][name]) return;
                    seen[cat][name] = true;
                    poiData[cat].push({ name: name, lat: elat, lon: elon, km: haversine(elat, elon) });
                });
                CAT_ORDER.forEach(function (c) { poiData[c].sort(function (a, b) { return a.km - b.km; }); });
            })
            .catch(function () {})
            .then(function () {
                if (loadingEl) loadingEl.style.display = 'none';

                // Additional info: up to 5 nearest places across all categories (all if fewer)
                var all = [];
                CAT_ORDER.forEach(function (c) {
                    poiData[c].forEach(function (p) { all.push({ cat: c, p: p }); });
                });
                all.sort(function (a, b) { return a.p.km - b.p.km; });
                var rows = '';
                all.slice(0, 5).forEach(function (it) {
                    var meta = CATS[it.cat];
                    rows += '<div class="d-flex align-items-center mb-2">' +
                        '<svg class="me-2" width="18" height="18" fill="currentColor" style="color:' + meta.color + '" aria-hidden="true"><use href="{{ asset('img/icons.svg') }}#icon-' + meta.icon + '"/></svg>' +
                        '<span class="fs-14 text-body">' + it.p.name + '</span>' +
                        '<span class="fs-14 text-body ms-auto ps-2 text-nowrap flex-shrink-0">' + fmtDist(it.p.km) + '</span>' +
                    '</div>';
                });
                if (rows && nearList) { nearList.innerHTML = rows; if (addBlock) addBlock.style.display = 'block'; }

                // Show only the category tabs that actually have data
                var anyData = false;
                document.querySelectorAll('.poi-tab, .poi-tab-fs').forEach(function (b) {
                    var c = b.getAttribute('data-cat');
                    if (poiData[c] && poiData[c].length) { anyData = true; b.style.display = ''; }
                    else { b.style.display = 'none'; }
                });
                if (anyData && tabsBlock) tabsBlock.style.display = 'block';
            });
    }

    // ── resolve coordinates, then boot the map + POI ──
    function boot() { initMap(); fetchPOI(); }

    // Nominatim fallback when Yandex geocoder key has no geocoding permission.
    // Tries progressively simpler queries: full address → street+city → city only.
    function geocodeViaNominatim(cb) {
        var queries = [];
        if (addrStreet && addrCity) queries.push([addrStreet, addrCity, addrCountry].filter(Boolean).join(', '));
        if (addrCity)               queries.push([addrCity, addrCountry].filter(Boolean).join(', '));
        if (!queries.length)        queries.push(address);

        function tryNext(i) {
            if (i >= queries.length) return;
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(queries[i]) + '&format=json&limit=1')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data[0]) {
                        lat = parseFloat(data[0].lat); lng = parseFloat(data[0].lon); cb();
                    } else {
                        tryNext(i + 1);
                    }
                }).catch(function () { tryNext(i + 1); });
        }
        tryNext(0);
    }

    if (lat && lng) {
        boot();
    } else {
        // No coords from API → try Yandex geocoder, fall back to Nominatim on failure/empty result
        var geocodeAndBoot = function () {
            if (typeof ymaps !== 'undefined' && ymaps.geocode) {
                ymaps.geocode(address, { results: 1 }).then(function (res) {
                    var obj = res.geoObjects.get(0);
                    if (obj) {
                        var c = obj.geometry.getCoordinates();
                        lat = c[0]; lng = c[1];
                        boot();
                    } else {
                        geocodeViaNominatim(boot);
                    }
                }, function () { geocodeViaNominatim(boot); });
            } else {
                geocodeViaNominatim(boot);
            }
        };
        if (typeof ymaps !== 'undefined') { ymaps.ready(geocodeAndBoot); }
        else { window.addEventListener('load', function () { ymaps && ymaps.ready ? ymaps.ready(geocodeAndBoot) : geocodeViaNominatim(boot); }); }
    }
}());
</script>

<!-- View counter: flash animation styles for incrementing the views badge -->
<style>
@keyframes vcFlash {
    0%   { color: rgba(255,255,255,0.5); text-shadow: none; }
    35%  { color: #4ade80; text-shadow: 0 0 10px rgba(74,222,128,0.7); }
    100% { color: rgba(255,255,255,0.5); text-shadow: none; }
}
#property-view-count {
    display: inline-block;
    transition: opacity .2s ease, transform .2s ease;
}
#property-view-count.vc-out {
    opacity: 0;
    transform: translateY(-5px);
}
#property-view-count.vc-in {
    opacity: 0;
    transform: translateY(5px);
}
#view-count-wrap.vc-flash {
    animation: vcFlash .9s ease-out forwards;
}
</style>

<!-- View counter: POST /api/property/{slug}/view (throttled to once per 12 min via localStorage) and animate count update -->
<script>
(function () {
    var wrapper = document.querySelector('[data-slug]');
    if (!wrapper) return;

    var slug = wrapper.dataset.slug;
    if (!slug) return;

    var STORAGE_KEY = 'te_pv_' + slug;
    var TEN_MIN     = 0.2 * 60 * 60 * 1000; // 0.2 hours = 12 min
    var now         = Date.now();
    var lastSeen    = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
    var remaining   = TEN_MIN - (now - lastSeen);

    // Still within 10-minute window — skip recording
    if (now - lastSeen < TEN_MIN) return;

    var csrf  = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var numEl = document.getElementById('property-view-count');
    var wrap  = document.getElementById('view-count-wrap');

    fetch('/api/property/' + slug + '/view', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    })
    .then(function (r) {
        return r.ok ? r.json() : r.json().then(function(e){ return null; });
    })
    .then(function (data) {
        if (!data || !data.ok) return;

        // Save timestamp so next visit within 10 min won't record again
        localStorage.setItem(STORAGE_KEY, String(now));

        if (!numEl) return;

        // Determine new count: from API response, or optimistic +1
        var current  = parseInt(numEl.dataset.count || numEl.textContent || '0', 10);
        var newCount = (data.viewCount !== null && data.viewCount !== undefined)
            ? data.viewCount
            : current + 1;

        if (newCount === current) return; // nothing changed

        // Animate: slide out current number
        numEl.classList.add('vc-out');

        setTimeout(function () {
            numEl.textContent    = newCount;
            numEl.dataset.count  = newCount;
            numEl.classList.remove('vc-out');
            numEl.classList.add('vc-in');

            // Force reflow so transition fires
            void numEl.offsetWidth;

            numEl.classList.remove('vc-in');

            // Flash the whole row green
            if (wrap) {
                wrap.classList.add('vc-flash');
                setTimeout(function () { wrap.classList.remove('vc-flash'); }, 900);
            }
        }, 200);
    })
    .catch(function () {});
}());
</script>

<!-- Enquire form: AJAX submit to /api/property/{slug}/enquire + Share button + Favorites toggle (localStorage) -->
<script>
// ── Enquire form ──────────────────────────────────────────
(function () {
    var form    = document.getElementById('enquireForm');
    var btn     = document.getElementById('enquireSubmit');
    var success = document.getElementById('enquireSuccess');
    var error   = document.getElementById('enquireError');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var slug = form.dataset.slug;
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        btn.disabled = true;
        error.classList.add('d-none');
        success.classList.add('d-none');

        var body = new FormData(form);

        fetch('/api/property/' + slug + '/enquire', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: body,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                success.classList.remove('d-none');
                form.reset();
                setTimeout(function () { success.classList.add('d-none'); }, 5000);
            } else {
                error.textContent = data.error || '{{ __("property-single.enquire_error") }}';
                error.classList.remove('d-none');
            }
        })
        .catch(function () {
            error.textContent = '{{ __("property-single.enquire_error") }}';
            error.classList.remove('d-none');
        })
        .finally(function () { btn.disabled = false; });
    });
}());

// ── Share button ──────────────────────────────────────────
document.getElementById('sharePropertyBtn')?.addEventListener('click', async function() {
    var url   = window.location.href;
    var title = document.title;
    if (navigator.share) {
        try { await navigator.share({ title: title, url: url }); } catch (e) { /* cancelled */ }
        return;
    }
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(url);
            showToast('{{ __("property-single.link_copied") }}', 'success');
            return;
        } catch (e) { /* fall through */ }
    }
    // Fallback: execCommand for HTTP / old Safari
    try {
        var ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.focus(); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('{{ __("property-single.link_copied") }}', 'success');
    } catch (e) {
        showToast('{{ __("property-single.copy_error") }}', 'danger');
    }
});

// Favorites button — handled by the global delegated handler in footer-scripts.blade.php
</script>

<script>
// ── Skeleton-first: load similar + comments after the shell paints ──────────
// The page shell (SEO head + core) renders instantly; these two sections show
// skeletons, then get injected from /property/{slug}/extras (all cached 1h).
(function () {
    var extrasUrl = window.location.pathname.replace(/\/+$/, '') + '/extras';
    fetch(extrasUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;
            var sim = document.getElementById('ps-similar');
            var com = document.getElementById('ps-comments');
            if (sim) sim.innerHTML = data.similar || '';
            if (com) com.innerHTML = data.comments || '';
            // Re-apply favourite/compare state to the freshly injected similar cards
            if (typeof window.applyFavIcons === 'function') window.applyFavIcons();
            if (typeof window.applyCompareIcons === 'function') window.applyCompareIcons();
        })
        .catch(function () { /* keep skeletons on failure */ });
}());
</script>

@endsection
