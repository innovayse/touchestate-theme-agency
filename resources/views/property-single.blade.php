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
  "price": {{ (float)($property['price'] ?? 0) }},
  "priceCurrency": @json($property['currency'] ?? 'USD'),
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
    {"@@type":"ListItem","position":1,"name":"Home","item":@json(url($seoLocale))},
    {"@@type":"ListItem","position":2,"name":"Properties","item":@json(url($seoLocale . '/property'))},
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
    height: 92px;
    cursor: pointer;
    box-sizing: border-box;
}
.slider-card .slider-nav-thumbnails .slick-slide .slide-img {
    display: block;
    width: 100%;
    height: 100%;
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
    .slider-card .slider-nav-thumbnails .slick-slide { height: 70px; }
    .service-slider-zone { width: 22%; }
    .service-slider-zone::after { width: 38px; height: 38px; background-size: 18px; }
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

    // Enum переводы через helper
    $enumKey = fn(string $prefix, string $value): string =>
        'property-single.' . $prefix . '_' . strtolower($value);

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
                                <div class="fs-14 mb-0 text-white d-flex align-items-center flex-wrap gap-1 custom-address-item"><x-icon name="location_on" class="text-white me-1"/>{{ $property['fullAddress'] ?? $property['city'] ?? '' }}</div>
                                @if(!empty($property['createdAt']))
                                <i class="fa-solid fa-circle text-body"></i>
                                @php $__d = \Carbon\Carbon::parse($property['createdAt']); @endphp
                                <p class="fs-14 mb-0 text-white">{{ __('property-single.last_updated_on') }} {{ $__d->format('d') }} {{ __('property.' . strtolower($__d->format('M'))) }} {{ $__d->format('Y') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-xl-4 d-flex d-xl-block flex-wrap gap-3">
                            <div class="breadcrumb-icons d-flex align-items-center justify-content-xl-end justify-content-start gap-2 mb-xl-4 mb-2 mt-xl-0 mt-4">
                                <a href="javascript:void(0);" class="favourite" data-slug="{{ $property['slug'] ?? '' }}" aria-label="{{ __('property-single.favorite') }}"><x-icon name="favorite_border" class="rounded"/></a>
                                <a href="javascript:void(0);" class="share-btn" id="sharePropertyBtn"><x-icon name="share" class="rounded"/></a>
                            </div>
                            <div class="d-flex align-items-center gap-3 justify-content-xl-end justify-content-start">
                                <h4 class="mb-0 text-primary text-xl-end text-start">
                                    {{ number_format($property['price'] ?? 0, 0) }} {{ $property['currency'] ?? '' }}
                                    @if($txType === 'rentmonthly')
                                        <span class="fs-14 fw-normal text-white">{{ __('property-single.per_month') }}</span>
                                    @elseif($txType === 'rentdaily')
                                        <span class="fs-14 fw-normal text-white">{{ __('property-single.per_day') }}</span>
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Breadcrumb -->

        </div>

        <!-- Start Content -->
        <div class="content">
            <div class="container">

                <!-- start row -->
                <div class="row">
                    <div class="col-xl-8">

                        <!-- start slider -->
                        @if($mediaImages->isNotEmpty())
                        <div class="slider-card service-slider-card mb-4">
                            <div class="slide-part mb-4 service-slider-wrap">
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
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="bed" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.bedrooms') }}</small><strong>{{ $property['bedrooms'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['bathrooms'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="bathtub" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.bathrooms') }}</small><strong>{{ $property['bathrooms'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['rooms'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="meeting_room" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.rooms') }}</small><strong>{{ $property['rooms'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['areaTotal'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="straighten" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.area') }}</small><strong>{{ $property['areaTotal'] }} {{ __('property-single.meters') }}²</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['areaLiving'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="crop_square" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.area_living') }}</small><strong>{{ $property['areaLiving'] }} {{ __('property-single.meters') }}²</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['floor'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="layers" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.floor') }}</small><strong>{{ $property['floor'] }}@if($property['floorsTotal'] ?? null) / {{ $property['floorsTotal'] }}@endif</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['yearBuilt'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="calendar_today" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.year_built') }}</small><strong>{{ $property['yearBuilt'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['ceilingHeight'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="height" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.ceiling_height') }}</small><strong>{{ $property['ceilingHeight'] }} {{ __('property-single.meters') }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['landArea'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="landscape" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.land_area') }}</small><strong>{{ $property['landArea'] }} {{ __('property-single.meters') }}²</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['parkingSpaces'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="local_parking" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.parking_spaces') }}</small><strong>{{ $property['parkingSpaces'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['pricePerSqm'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="payments" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.price_per_sqm') }}</small><strong>{{ number_format($property['pricePerSqm'], 0) }} {{ $property['currency'] ?? '' }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['totalUnits'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="apartment" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.total_units') }}</small><strong>{{ $property['totalUnits'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['availableUnits'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="door_front" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.available_units') }}</small><strong>{{ $property['availableUnits'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['totalBuildings'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="domain" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.total_buildings') }}</small><strong>{{ $property['totalBuildings'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['code'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="tag" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.code') }}</small><strong>#{{ $property['code'] }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['renovationType'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="construction" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.renovation_type') }}</small><strong>{{ __($enumKey('renovation', $property['renovationType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['constructionType'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="architecture" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.construction_type') }}</small><strong>{{ __($enumKey('construction', $property['constructionType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['furnitureType'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="chair" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.furniture_type') }}</small><strong>{{ __($enumKey('furniture', $property['furnitureType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['balconyType'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="balcony" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.balcony_type') }}</small><strong>{{ __($enumKey('balcony', $property['balconyType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['terraceType'] ?? null)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="deck" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.terrace_type') }}</small><strong>{{ __($enumKey('terrace', $property['terraceType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            {{-- Tiles below show only single-value variants; multi-value sets render in the "additional_details" accordion below. --}}
                                            @if(!empty($property['heatingType']) && count((array)$property['heatingType']) === 1)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="thermostat" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.heating_type') }}</small><strong>{{ __($enumKey('heating', ((array)$property['heatingType'])[0])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['parkingType']) && count((array)$property['parkingType']) === 1)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="local_parking" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.parking_type') }}</small><strong>{{ __($enumKey('parking', ((array)$property['parkingType'])[0])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['windowView']) && count((array)$property['windowView']) === 1)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="panorama" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.window_view') }}</small><strong>{{ __($enumKey('view', ((array)$property['windowView'])[0])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['zoningType']))
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="map" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.zoning_type') }}</small><strong>{{ __($enumKey('zoning', $property['zoningType'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(isset($property['sunDirection']) && $property['sunDirection'] !== null && $property['sunDirection'] !== false)
                                            <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
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
                                            <div class="col-lg-4 col-md-6">
                                                <p class="mb-2 d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-circle-check text-success fs-18"></i>
                                                    {{ $feat['label'] }}
                                                </p>
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
                                        <div class="row row-gap-2">
                                            @foreach($property['amenities'] as $amenity)
                                            <div class="col-lg-3 col-md-6">
                                                <p class="mb-2 d-flex align-items-center gap-2">
                                                    @php $__amKey = 'property-single.amenity_' . strtolower($amenity); @endphp
                                                    <x-icon name="check" class="text-secondary"/> {{ __($__amKey) !== $__amKey ? __($__amKey) : $amenity }}
                                                </p>
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-feat" aria-expanded="false">
                                        {{ __('property-single.features_section') }}
                                    </button>
                                </div>
                                <div id="accordion-feat" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($property['features'] as $feat)
                                            @php $fKey = strtolower($feat); @endphp
                                            <div class="col-lg-4 col-md-6 col-6">
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-appl" aria-expanded="false">
                                        {{ __('property-single.appliances_section') }}
                                    </button>
                                </div>
                                <div id="accordion-appl" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($property['appliances'] as $appl)
                                            @php $aKey = strtolower($appl); @endphp
                                            <div class="col-lg-4 col-md-6 col-6">
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-util" aria-expanded="false">
                                        {{ __('property-single.utilities_section') }}
                                    </button>
                                </div>
                                <div id="accordion-util" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row row-gap-2">
                                            @foreach($property['utilities'] as $util)
                                            @php $uKey = strtolower($util); @endphp
                                            <div class="col-lg-4 col-md-6 col-6">
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-cond" aria-expanded="false">
                                        {{ __('property-single.rental_conditions') }}
                                    </button>
                                </div>
                                <div id="accordion-cond" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row row-gap-3">
                                            @if($property['deposit'] ?? null)
                                            <div class="col-lg-3 col-md-4 col-6">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="account_balance_wallet" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.deposit') }}</small><strong>{{ number_format($property['deposit'], 0) }} {{ $property['currency'] ?? '' }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['utilitiesPolicy'] ?? null)
                                            <div class="col-lg-3 col-md-4 col-6">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="receipt_long" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.utilities_policy') }}</small><strong>{{ __($enumKey('utilities', $property['utilitiesPolicy'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['petsPolicy'] ?? null)
                                            <div class="col-lg-3 col-md-4 col-6">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="pets" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.pets_policy') }}</small><strong>{{ __($enumKey('policy', $property['petsPolicy'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($property['childrenPolicy'] ?? null)
                                            <div class="col-lg-3 col-md-4 col-6">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="child_care" class="text-primary"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.children_policy') }}</small><strong>{{ __($enumKey('policy', $property['childrenPolicy'])) }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['isNegotiable']))
                                            <div class="col-lg-3 col-md-4 col-6">
                                                <div class="feature-item d-flex align-items-center gap-2">
                                                    <x-icon name="handshake" class="text-success"/>
                                                    <div><small class="text-muted d-block">{{ __('property-single.negotiable') }}</small><strong>{{ __('property-single.yes') }}</strong></div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(!empty($property['isLongTermRental']))
                                            <div class="col-lg-3 col-md-4 col-6">
                                                <div class="feature-item d-flex align-items-center gap-2">
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-ext" aria-expanded="false">
                                        {{ __('property-single.extended_attributes') }}
                                    </button>
                                </div>
                                <div id="accordion-ext" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row row-gap-3">
                                            @foreach($property['extendedAttributes'] as $attrKey => $attrVal)
                                            <div class="col-lg-3 col-md-4 col-6">
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
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-hist" aria-expanded="false">
                                        {{ __('property-single.price_history') }}
                                    </button>
                                </div>
                                <div id="accordion-hist" class="accordion-collapse collapse">
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
                                                    <div>{{ $label }}: <strong>{{ number_format($newAmt, 0) }} {{ $cur }}</strong>
                                                    @if($oldAmt !== null) <span class="text-muted ms-1 text-decoration-line-through">{{ number_format($oldAmt, 0) }} {{ $cur }}</span>@endif
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Gallery -->
                            @if($mediaImages->isNotEmpty())
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-6" aria-expanded="true">
                                        {{ __('property-single.gallery') }}
                                    </button>
                                </div>
                                <div id="accordion-6" class="accordion-collapse collapse show">
                                    <div class="accordion-body gallery-body">
                                        @if($mediaImages->count() === 1)
                                            @php $img = $mediaImages->first(); @endphp
                                            <div class="gallery-single-wrap" style="max-width:480px;">
                                                <a href="{{ $img['url'] }}" data-fancybox="gallery" class="gallery-item rounded d-block">
                                                    <img src="{{ $img['thumbnailUrl'] ?? $img['url'] }}" alt="{{ $property['title'] ?? '' }}" class="rounded img-fluid" loading="lazy">
                                                </a>
                                            </div>
                                        @else
                                            <div class="gallery-slider" data-slides-count="{{ $mediaImages->count() }}">
                                                @foreach($mediaImages as $img)
                                                <div class="gallery-card">
                                                    <a href="{{ $img['url'] }}" data-fancybox="gallery" data-caption="{{ $property['title'] ?? '' }}" class="gallery-item rounded">
                                                        <img src="{{ $img['thumbnailUrl'] ?? $img['url'] }}" alt="{{ $property['title'] ?? '' }}" class="rounded img-fluid" onerror="this.onerror=null;this.src='{{ $img['url'] }}'">
                                                    </a>
                                                </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Comments -->
                            <div class="accordion-item mb-xl-0">
                                <div class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-9" aria-expanded="true">
                                        {{ __('property-single.comments') }}
                                    </button>
                                </div>
                                <div id="accordion-9" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="sub-head mb-4">
                                            <h6 class="fs-16 fw-semibold"> {{ __('property-single.comments') }} ({{ $comments['totalCount'] ?? 0 }}) </h6>
                                        </div>

                                        @forelse($comments['items'] ?? [] as $comment)
                                        @php
                                            $__rName = $comment['authorName'] ?? __('property-single.anonymous');
                                            $__rInitials = collect(explode(' ', $__rName))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                                            $__rDate = \Carbon\Carbon::parse($comment['createdAt'] ?? now());
                                            $__rDateStr = $__rDate->format('d') . ' ' . __('property.' . strtolower($__rDate->format('M'))) . ', ' . $__rDate->format('Y');
                                        @endphp
                                        <div class="card shadow-none review-items">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start gap-3 mb-3">
                                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:48px;height:48px;font-size:16px;">
                                                        {{ $__rInitials }}
                                                    </div>
                                                    <div class="flex-fill">
                                                        <h6 class="fs-16 fw-semibold mb-0">{{ $__rName }}</h6>
                                                        <p class="fs-13 text-muted mb-0">{{ $__rDateStr }}</p>
                                                    </div>
                                                    @if(!empty($comment['authorType']))
                                                    <span class="badge bg-primary fs-11">{{ $comment['authorType'] }}</span>
                                                    @endif
                                                </div>

                                                @if(!empty($comment['commentText']))
                                                <p class="mb-2 text-body">{{ $comment['commentText'] }}</p>
                                                @endif

                                                @if(!empty($comment['replies']))
                                                <div class="mt-3 ms-4 border-start ps-3">
                                                    @foreach($comment['replies'] as $__reply)
                                                    @php
                                                        $__aName = $__reply['authorName'] ?? '';
                                                        $__aInitials = collect(explode(' ', $__aName))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                                                        $__aDate = \Carbon\Carbon::parse($__reply['createdAt'] ?? now());
                                                        $__aDateStr = $__aDate->format('d') . ' ' . __('property.' . strtolower($__aDate->format('M'))) . ', ' . $__aDate->format('Y');
                                                    @endphp
                                                    <div class="d-flex align-items-start gap-3 {{ !$loop->last ? 'mb-3' : '' }}">
                                                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;font-size:14px;">
                                                            {{ $__aInitials }}
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <h6 class="fs-14 fw-semibold mb-0">{{ $__aName }}</h6>
                                                                @if(!empty($__reply['isOwnerReply']))
                                                                <span class="badge bg-primary fs-11">{{ __('property-single.owner_reply') }}</span>
                                                                @endif
                                                            </div>
                                                            <p class="fs-12 text-muted mb-1">{{ $__aDateStr }}</p>
                                                            <p class="mb-0 fs-14 text-body">{{ $__reply['replyText'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted text-center py-3">{{ __('property-single.no_comments_yet') }}</p>
                                        @endforelse

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- col end -->

                    <!-- ============================================================
                         Sidebar (right column): Agent card + Enquire form + Map
                         ============================================================ -->
                    <div class="col-xl-4 buy-details-item">
                        <div class="property-single-sidebar">
                        <!-- Agent -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('property-single.provider_details') }}</h5>
                            </div>
                            <div class="card-body">
                                {{-- Workspace (company) --}}
                                @if(!empty($workspace['name']))
                                <div class="card bg-light border-0 rounded shadow-none custom-btn mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-lg flex-shrink-0">
                                                @if(!empty($workspace['logoUrl']))
                                                <img src="{{ $workspace['logoUrl'] }}" alt="{{ $workspace['name'] }}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                                                @else
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width:48px;height:48px;font-size:18px;">
                                                    {{ mb_strtoupper(mb_substr($workspace['name'], 0, 1)) }}
                                                </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fs-16 fw-semibold">{{ $workspace['name'] }}</h6>
                                                @if(!empty($workspace['description']))
                                                <p class="mb-0 fs-13 text-muted">{{ Str::limit($workspace['description'], 60) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Agent name --}}
                                @php $agentName = $property['assignedAgentName'] ?? ''; @endphp
                                @if($agentName)
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;font-size:16px;">
                                        {{ mb_strtoupper(mb_substr($agentName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-semibold fs-15">{{ $agentName }}</p>
                                        <p class="mb-0 fs-13 text-muted">{{ __('property-single.company_agent') }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Agent contact: phone + Viber + WhatsApp (rendered only when phone is known) -->
                                @if(!empty($property['agentPhoneE164']))
                                @php
                                    $phoneE164   = $property['agentPhoneE164'];
                                    $phoneShown  = $property['assignedAgentPhone'] ?: $phoneE164;
                                @endphp
                                <a href="tel:{{ $phoneE164 }}" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2 mb-2 py-2">
                                    <x-icon name="phone" size="18"/>
                                    <span class="fw-semibold">{{ $phoneShown }}</span>
                                </a>
                                <div class="d-flex gap-2 mb-3">
                                    <a href="viber://chat?number=+{{ $phoneE164 }}" class="btn btn-light flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2" style="background:#7360f2;color:#fff;border:0;">
                                        <svg width="18" height="18" fill="currentColor" aria-hidden="true" viewBox="0 0 24 24"><path d="M11.4 0C9.473.024 5.333.34 3.02 2.464 1.302 4.187.696 6.7.633 9.819.572 12.93.494 18.762 6.18 20.378v2.463s-.04 1.005.61 1.205c.795.246 1.252-.5 2.005-1.293.42-.435.992-1.082 1.43-1.59 3.85.327 6.812-.41 7.144-.515.78-.254 5.187-.811 5.91-6.66.74-6.034-.376-9.86-2.394-11.582l-.013-.005C20.27.65 16.36.027 13.59 0c0 0-.193-.011-.633-.014-.222 0-.598-.005-.998 0-.4-.005-.776 0-.998 0-.44.003-.633.014-.633.014z"/></svg>
                                        Viber
                                    </a>
                                    <a href="https://wa.me/{{ $phoneE164 }}" target="_blank" rel="noopener" class="btn btn-light flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2" style="background:#25d366;color:#fff;border:0;">
                                        <svg width="18" height="18" fill="currentColor" aria-hidden="true" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l.236.375-.999 3.648 3.742-.982zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.296-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.695.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        WhatsApp
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

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

                        <!-- Map -->
                        @if(($property['latitude'] ?? null) && ($property['longitude'] ?? null))
                        @php
                            $mapTitle = urlencode($property['title'] ?? '');
                            $mapLang = ['ru' => 'ru_RU', 'hy' => 'hy_AM', 'en' => 'en_US'][app()->getLocale()] ?? 'en_US';
                            $mapSrc = "https://yandex.ru/map-widget/v1/?ll={$property['longitude']}%2C{$property['latitude']}&z=16&pt={$property['longitude']},{$property['latitude']},pm2rdm~{$mapTitle}&l=map&lang={$mapLang}";
                        @endphp
                        <div class="card mb-0">
                            <div class="custom-map position-relative">
                                <button class="map-fullscreen-btn" id="mapFullscreenBtn" title="Fullscreen">
                                    <x-icon name="fullscreen"/>
                                </button>
                                {{-- Кнопки управления картой (правый верхний угол) --}}
                                <div id="mapControlBtns" class="map-control-btns" style="display:none;">
                                    <button id="mapGoHome" class="map-control-btn map-control-btn--home" title="Показать объект">
                                        <x-icon name="home"/>
                                    </button>
                                    <button id="mapGoMetro" class="map-control-btn map-control-btn--metro" title="Показать метро">
                                        <x-icon name="subway"/>
                                    </button>
                                </div>
                                <div id="propertyJsMap" style="width:100%;height:195px;"></div>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-2"> {{ __('property-single.location') }} </h6>
                                <p class="mb-0 text-body d-flex align-items-center">
                                    <x-icon name="location_on" class="me-1"/>
                                    {{ $property['fullAddress'] ?? $property['city'] ?? '' }}
                                </p>

                                {{-- Nearby metro stations --}}
                                {{-- Metro loading --}}
                                <div id="nearbyMetroLoading" style="display:none; margin-top:16px; border-top:1px solid var(--gray-100); padding-top:12px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <x-icon name="subway" style="color:#03bd9d"/>
                                        <span class="fs-14 text-body">{{ __('property-single.nearby_metro') }}</span>
                                        <div class="spinner-border spinner-border-sm ms-1" style="color:#03bd9d; width:14px; height:14px; border-width:2px;" role="status"></div>
                                    </div>
                                </div>

                                {{-- Metro results --}}
                                <div id="nearbyMetroBlock" style="display:none; margin-top:16px; border-top:1px solid var(--gray-100); padding-top:12px;">
                                    <h6 id="nearbyMetroToggle" class="mb-2 d-flex align-items-center" style="cursor:pointer; user-select:none;">
                                        <x-icon name="subway" class="me-1" style="color:#03bd9d"/>
                                        {{ __('property-single.nearby_metro') }}
                                    </h6>
                                    <div id="nearbyMetroList"></div>
                                    <p id="nearbyMetroEmpty" class="mb-0 text-body fs-14" style="display:none;">{{ __('property-single.no_metro_nearby') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Map fullscreen overlay (appended to body via JS) -->
                        <div id="mapFullscreenOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; background:#000;">
                            <div id="mapFullscreenJsMap" style="width:100%;height:100%;"></div>
                        </div>
                        <!-- Кнопка закрытия fullscreen — вне карты, всегда поверх -->
                        <button id="mapFullscreenClose" style="display:none; position:fixed; top:12px; left:12px; z-index:2147483647; width:40px; height:40px; border:none; border-radius:6px; background:rgba(0,0,0,0.7); color:#fff; cursor:pointer; align-items:center; justify-content:center;">
                            <x-icon name="fullscreen_exit" size="24"/>
                        </button>
                        @endif

                        </div>{{-- property-single-sidebar --}}
                    </div> <!-- col end -->
                </div>
                <!-- end row -->


                <!-- Similar properties -->
                @if(!empty($similar))
                <h4 class="mt-5 mb-4 ps-2">{{ __('property-single.similar_properties') }}</h4>
                <div class="row row-gap-4 custom-properties-items">
                    @foreach($similar as $sim)
                        <x-property-card :prop="$sim" />
                    @endforeach
                </div>
                @endif

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

<!-- Map: Yandex Map init + fullscreen overlay + Nearby Metro fetch & toggle -->
<script>
(function () {
    var btn       = document.getElementById('mapFullscreenBtn');
    var overlay   = document.getElementById('mapFullscreenOverlay');
    var closeBtn  = document.getElementById('mapFullscreenClose');
    var fsMapEl   = document.getElementById('mapFullscreenJsMap');
    var mapEl     = document.getElementById('propertyJsMap');
    var block     = document.getElementById('nearbyMetroBlock');
    var list      = document.getElementById('nearbyMetroList');
    var empty     = document.getElementById('nearbyMetroEmpty');
    var toggle    = document.getElementById('nearbyMetroToggle');

    if (!mapEl) return;
    if (btn && overlay) document.body.appendChild(overlay);

    var lat       = {{ $property['latitude'] ?? 'null' }};
    var lng       = {{ $property['longitude'] ?? 'null' }};
    var propTitle = @json($property['title'] ?? '');

    if (!lat || !lng) return;

    var metroStations  = [];
    var metroMapShown  = false;
    var mainMap        = null;
    var metroCollection = null; // коллекция меток метро на основной карте
    var fsMap          = null;

    // ── SVG иконки ──
    var homeIconSvg = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="52" viewBox="0 0 40 52">' +
        '<path d="M20 0C8.95 0 0 8.95 0 20c0 14 20 32 20 32s20-18 20-32C40 8.95 31.05 0 20 0z" fill="#E74C3C"/>' +
        '<path d="M20 2C9.51 2 1 10.51 1 20c0 13.37 19 30.5 19 30.5S39 33.37 39 20C39 10.51 30.49 2 20 2z" fill="#FF6B6B"/>' +
        '<path d="M20 5C11.18 5 4 12.18 4 21c0 11.8 16 27 16 27s16-15.2 16-27C36 12.18 28.82 5 20 5z" fill="#E74C3C"/>' +
        '<g transform="translate(20,20) scale(0.72) translate(-12,-12)">' +
        '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="#fff"/>' +
        '</g></svg>'
    );

    var metroIconSvg = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="48" viewBox="0 0 36 48">' +
        '<path d="M18 0C8.06 0 0 8.06 0 18c0 12.6 18 30 18 30s18-17.4 18-30C36 8.06 27.94 0 18 0z" fill="#2D3436"/>' +
        '<circle cx="18" cy="17" r="10" fill="#03bd9d"/>' +
        '<g transform="translate(18,17) scale(0.58) translate(-12,-12)">' +
        '<path d="M12 2c-4 0-8 .5-8 4v9.5C4 17.43 5.57 19 7.5 19L6 20.5v.5h2l2-2h4l2 2h2v-.5L16.5 19c1.93 0 3.5-1.57 3.5-3.5V6c0-3.5-3.58-4-8-4zM7.5 17c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm3.5-6H6V6h5v5zm5 6c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6h-5V6h5v5z" fill="#fff"/>' +
        '</g></svg>'
    );

    // ── Хелперы ──
    function homePlacemark() {
        return new ymaps.Placemark([lat, lng], { balloonContent: propTitle }, {
            iconLayout: 'default#image',
            iconImageHref: homeIconSvg,
            iconImageSize: [28, 36],
            iconImageOffset: [-14, -36]
        });
    }

    function metroPlacemark(st) {
        var dist = st.km < 1 ? Math.round(st.km * 1000) + ' m' : st.km.toFixed(1) + ' km';
        return new ymaps.Placemark([st.lat, st.lon], {
            balloonContent: '<strong>' + st.name + '</strong><br>' + dist
        }, {
            iconLayout: 'default#image',
            iconImageHref: metroIconSvg,
            iconImageSize: [26, 34],
            iconImageOffset: [-13, -34]
        });
    }

    function calcBounds(includeMetro) {
        var minLat = lat, maxLat = lat, minLon = lng, maxLon = lng;
        if (includeMetro) {
            metroStations.forEach(function (st) {
                if (st.lat < minLat) minLat = st.lat;
                if (st.lat > maxLat) maxLat = st.lat;
                if (st.lon < minLon) minLon = st.lon;
                if (st.lon > maxLon) maxLon = st.lon;
            });
        }
        var diff = Math.max(maxLat - minLat, maxLon - minLon);
        return {
            center: [(minLat + maxLat) / 2, (minLon + maxLon) / 2],
            zoom: diff > 0.04 ? 13 : diff > 0.02 ? 14 : 15
        };
    }

    var goHomeBtn  = document.getElementById('mapGoHome');
    var goMetroBtn = document.getElementById('mapGoMetro');
    var controlBtns = document.getElementById('mapControlBtns');

    // ── Инициализация основной карты (ждём загрузки ymaps) ──
    function initMap() {
        ymaps.ready(function () {
            mainMap = new ymaps.Map(mapEl, { center: [lat, lng], zoom: 16, controls: ['zoomControl'] });
            mainMap.geoObjects.add(homePlacemark());
            metroCollection = new ymaps.GeoObjectCollection();
            mainMap.geoObjects.add(metroCollection);
            // Показываем кнопки после инициализации карты
            if (controlBtns) controlBtns.style.display = 'flex';
        });
    }
    if (typeof ymaps !== 'undefined') {
        initMap();
    } else {
        window.addEventListener('load', initMap);
    }

    // ── Кнопки управления картой ──
    if (goHomeBtn) {
        goHomeBtn.addEventListener('click', function () {
            if (!mainMap) return;
            mainMap.setCenter([lat, lng], 16, { duration: 400 });
            // Подсветить активную кнопку
            goHomeBtn.style.background = 'rgba(231,76,60,0.15)';
            if (goMetroBtn) goMetroBtn.style.background = 'rgba(255,255,255,0.92)';
        });
    }

    if (goMetroBtn) {
        goMetroBtn.addEventListener('click', function () {
            if (!mainMap || !metroCollection) return;
            if (!metroStations.length) return;
            // Если метро не показано — показать
            if (!metroMapShown) {
                metroMapShown = true;
                metroStations.forEach(function (st) { metroCollection.add(metroPlacemark(st)); });
            }
            // Считаем bounds включая объект и все станции метро
            var minLat = lat, maxLat = lat, minLon = lng, maxLon = lng;
            metroStations.forEach(function (st) {
                if (st.lat < minLat) minLat = st.lat;
                if (st.lat > maxLat) maxLat = st.lat;
                if (st.lon < minLon) minLon = st.lon;
                if (st.lon > maxLon) maxLon = st.lon;
            });
            // setBounds автоматически подбирает zoom чтобы всё вошло
            mainMap.setBounds([[minLat, minLon], [maxLat, maxLon]], {
                checkZoomRange: true,
                duration: 400,
                padding: [40, 40, 40, 40]
            });
            goMetroBtn.style.background = 'rgba(3,189,157,0.15)';
            if (goHomeBtn) goHomeBtn.style.background = 'rgba(255,255,255,0.92)';
        });
    }

    // ── Fullscreen ──
    function openFs() {
        if (typeof ymaps === 'undefined' || !mainMap) return;
        overlay.style.display = 'block';
        if (closeBtn) { closeBtn.style.display = 'flex'; }
        document.body.style.overflow = 'hidden';
        fsMapEl.innerHTML = '';
        setTimeout(function () {
            var b = calcBounds(metroMapShown);
            fsMap = new ymaps.Map(fsMapEl, { center: b.center, zoom: metroMapShown ? b.zoom : 16, controls: ['zoomControl'] });
            fsMap.geoObjects.add(homePlacemark());
            if (metroMapShown) {
                metroStations.forEach(function (st) { fsMap.geoObjects.add(metroPlacemark(st)); });
            }
        }, 50);
    }

    function closeFs() {
        overlay.style.display = 'none';
        if (closeBtn) { closeBtn.style.display = 'none'; }
        fsMapEl.innerHTML = '';
        if (fsMap) { fsMap.destroy(); fsMap = null; }
        document.body.style.overflow = '';
    }

    if (btn) btn.addEventListener('click', openFs);
    if (closeBtn) closeBtn.addEventListener('click', closeFs);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeFs(); });

    // ── Overpass + Yandex Geocoding ──
    var loadingEl = document.getElementById('nearbyMetroLoading');
    if (!block || !list) return;

    // Показываем спиннер сразу
    if (loadingEl) loadingEl.style.display = 'block';

    var radius  = 2000;

    var overpassQuery = '[out:json][timeout:10];(' +
        'node["railway"="station"]["station"="subway"](around:' + radius + ',' + lat + ',' + lng + ');' +
        'node["station"="subway"](around:' + radius + ',' + lat + ',' + lng + ');' +
    ');out body;';

    function runOverpass(server) {
        return fetch(server, { method: 'POST', body: 'data=' + encodeURIComponent(overpassQuery), headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(function (r) { return r.json(); });
    }

    runOverpass('https://overpass-api.de/api/interpreter')
    .catch(function () { return runOverpass('https://overpass.kumi.systems/api/interpreter'); })
    .then(function (data) {
        var seen = {};
        (data.elements || []).forEach(function (el) {
            var tags = el.tags || {};
            var name = tags['name:{{ app()->getLocale() }}'] || tags['name:en'] || tags.name || '';
            if (!name || !el.lat || !el.lon) return;
            if (seen[name] && tags.railway !== 'station') return;
            seen[name] = true;
            var c = { lat: el.lat, lon: el.lon };
            var R = 6371, dLat = (c.lat - lat) * Math.PI / 180, dLon = (c.lon - lng) * Math.PI / 180;
            var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat*Math.PI/180)*Math.cos(c.lat*Math.PI/180)*Math.sin(dLon/2)*Math.sin(dLon/2);
            var km = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            if (km <= radius / 1000) metroStations.push({ name: name, km: km, lat: c.lat, lon: c.lon });
        });
    })
    .then(function () {
        metroStations.sort(function (a, b) { return a.km - b.km; });
        // Прячем спиннер, показываем результат
        if (loadingEl) loadingEl.style.display = 'none';
        block.style.display = 'block';

        if (metroStations.length === 0) { empty.style.display = 'block'; return; }

        metroStations.forEach(function (st) {
            var dist = st.km < 1 ? Math.round(st.km * 1000) + ' m' : st.km.toFixed(1) + ' km';
            var item = document.createElement('div');
            item.className = 'd-flex align-items-center mb-2';
            item.innerHTML = '<svg class="me-2" width="18" height="18" fill="currentColor" style="color:#03bd9d" aria-hidden="true"><use href="{{ asset('img/icons.svg') }}#icon-train"/></svg>' +
                '<span class="fs-14 text-body">' + st.name + '</span>' +
                '<span class="fs-14 text-body ms-auto">' + dist + '</span>';
            list.appendChild(item);
        });

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (!metroStations.length || !mainMap || !metroCollection) return;
                metroMapShown = !metroMapShown;
                if (metroMapShown) {
                    metroStations.forEach(function (st) { metroCollection.add(metroPlacemark(st)); });
                    var minLat = lat, maxLat = lat, minLon = lng, maxLon = lng;
                    metroStations.forEach(function (st) {
                        if (st.lat < minLat) minLat = st.lat;
                        if (st.lat > maxLat) maxLat = st.lat;
                        if (st.lon < minLon) minLon = st.lon;
                        if (st.lon > maxLon) maxLon = st.lon;
                    });
                    mainMap.setBounds([[minLat, minLon], [maxLat, maxLon]], { checkZoomRange: true, duration: 400, padding: [40, 40, 40, 40] });
                    if (goMetroBtn) goMetroBtn.style.background = 'rgba(3,189,157,0.15)';
                    if (goHomeBtn) goHomeBtn.style.background = 'rgba(255,255,255,0.92)';
                } else {
                    metroCollection.removeAll();
                    mainMap.setCenter([lat, lng], 16, { duration: 400 });
                    if (goMetroBtn) goMetroBtn.style.background = 'rgba(255,255,255,0.92)';
                    if (goHomeBtn) goHomeBtn.style.background = 'rgba(255,255,255,0.92)';
                }
            });
        }
    })
    .catch(function () {
        if (loadingEl) loadingEl.style.display = 'none';
    });
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

@endsection
