<?php $page = 'index'; ?>
@section('title')
    Home
@endsection

@extends('layout.mainlayout')
@section('content')
    <!-- Home Banner Section Start -->
    <section class="home-banner-two">

        <div class="banner-img-right" data-aos="fade-down" data-aos-duration="1000">
            <img src="{{URL::asset('build/img/section-bg/banner-bg-02.png')}}" alt="">
        </div>

        <div class="container">

            <!-- start row -->
            <div class="row">

                <div class="col-lg-6">
                    <div class="banner-title aos" data-aos="fade-up">
                        <h1>{{ __('index.hero_title') }} <span>{{ __('index.hero_title_highlight') }}</span> {{ __('index.hero_title_end') }}</h1>
                        <p>{{ __('index.hero_description') }}</p>
                    </div>
                    <div class="banner-stats mb-4" data-aos="fade-up" data-aos-duration="700">
                        <div class="banner-stat-item">
                            <h6>{{ $stats['successfulDeals'] }}+</h6>
                            <p>{{ __('index.counter_deals') }}</p>
                        </div>
                        <div class="banner-stat-divider"></div>
                        <div class="banner-stat-item">
                            <h6>{{ $stats['activeProperties'] }}+</h6>
                            <p>{{ __('index.counter_active') }}</p>
                        </div>
                    </div>
                    <div class="banner-cta d-flex align-items-center gap-3" data-aos="fade-up" data-aos-duration="900">
                        <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2">
                            <x-icon name="search" size="20"/> {{ __('index.hero_buy_property') }}
                        </a>
                        <a href="/{{ app()->getLocale() }}/map" class="btn btn-outline-light btn-lg d-inline-flex align-items-center gap-2">
                            <x-icon name="map" size="20"/>{{ __('index.hero_map') }}
                        </a>
                    </div>
                </div> <!-- end col -->

            </div>
            <!-- end row -->

        </div>
    </section>
    <!-- Home Banner Section End -->
     
    <!-- Search Start -->
    <div class="home-search-2">
        <div class="container">
        <div class="hero-search-card">
            <form action="/{{ app()->getLocale() }}/property" method="GET">
                <!-- start search grid -->
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_buy_sell') }}</label>
                        <select name="transactionType" class="filter-select">
                            <option value="">{{ __('index.search_select') }}</option>
                            <option value="Sale">{{ __('map.sale') }}</option>
                            <option value="Rent">{{ __('map.rent_monthly') }}</option>
                            <option value="RentDaily">{{ __('map.rent_daily') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_type') }}</label>
                        <select name="propertyType" class="filter-select">
                            <option value="">{{ __('index.search_select') }}</option>
                            @foreach($availableTypes as $pt)
                            @php $__ptKey = 'property.' . strtolower($pt); @endphp
                            <option value="{{ $pt }}">{{ __($__ptKey) !== $__ptKey ? __($__ptKey) : $pt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_location') }}</label>
                        <div class="filter-input-icon" style="position:relative">
                            <input type="text" id="cityInputIndex" placeholder="{{ __('map.enter_city') }}" class="form-control" autocomplete="off">
                            <input type="hidden" name="city" id="cityHiddenIndex">
                            <button type="button" id="cityClearBtnIndex" class="city-clear-btn"><x-icon name="close" size="18"/></button>
                            <span id="citySpinnerIndex" class="city-loading-spinner"></span>
                            <ul id="citySuggestionsIndex" class="city-suggestions"></ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_min_price') }}</label>
                        <div class="filter-stepper">
                            <input type="number" name="minPrice" id="minPriceIndex" min="0" placeholder="{{ currency_symbol(display_currency()) }}" data-currency-symbol>
                            <div class="stepper-btns">
                                <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_max_price') }}</label>
                        <div class="filter-stepper">
                            <input type="number" name="maxPrice" id="maxPriceIndex" min="0" placeholder="{{ currency_symbol(display_currency()) }}" data-currency-symbol>
                            <div class="stepper-btns">
                                <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-auto">
                        <button type="submit" class="btn btn-primary w-100">
                            <x-icon name="search" size="20"/>
                        </button>
                    </div>
                </div>
                <!-- end search grid -->
            </form>
        </div><!-- end hero-search-card -->
        </div>
    </div>
    <!-- Search End -->

    {{-- About Us section temporarily hidden — about-us page disabled (links would 404)
    <!-- About Us Section Start -->
    <section class="about-us-section-2">
        <div class="container">

            <!-- start row -->
            <div class="row align-items-center gy-4">

                <div class="col-lg-6">

                    <!-- Section Title Start -->
                    <div class="title-head" data-aos="fade-up" data-aos-duration="500">
                        <a href="{{ url('about-us') }}" class="badge bg-secondary mb-2" style="text-decoration:none;cursor:pointer">{{ __('index.about_badge') }}</a>
                        <h2 class="mb-2">{{ __('index.about_title') }}</h2>
                        <p>{{ __('index.about_description') }}</p>
                        <div class="d-flex align-items-center">
                            <a href="/{{ app()->getLocale() }}/property" class="btn btn-dark btn-lg me-3">{{ __('index.about_find_property') }}</a>
                            <a href="{{url('contact-us')}}" class="btn btn-primary btn-lg">{{ __('index.about_contact') }}</a>
                        </div>
                    </div>
                    <!-- Section Title End -->

                </div> <!-- end col -->

                <div class="col-lg-6">
                    <div class="position-relative" data-aos="fade-up" data-aos-duration="1000">
                        <div><img src="{{URL::asset('build/img/section-bg/section-bg-01.png')}}" class="img-fluid" alt=""></div>
                        <div class="position-absolute end-0 top-0">
                            <img src="{{URL::asset('build/img/bg/line-01.svg')}}" alt="">
                        </div>
                        <div class="position-absolute start-0 bottom-0">
                            <img src="{{URL::asset('build/img/bg/line-02.svg')}}" alt="">
                        </div>
                    </div>
                </div> <!-- end col -->

            </div>
            <!-- end row -->

        </div>
    </section>
    <!-- About Us Section End -->
    --}}

    <!-- Stats Bar Start -->
    <section class="stats-bar-section">
        <div class="container">
            <div class="stats-bar-grid">
                <div class="stats-bar-item">
                    <div class="stats-bar-icon"><x-icon name="domain" size="28"/></div>
                    <div>
                        <div class="stats-bar-number">{{ $stats['activeProperties'] }}+</div>
                        <div class="stats-bar-label">{{ __('index.counter_active') }}</div>
                    </div>
                </div>
                <div class="stats-bar-item">
                    <div class="stats-bar-icon"><x-icon name="check" size="28"/></div>
                    <div>
                        <div class="stats-bar-number">{{ $stats['successfulDeals'] }}+</div>
                        <div class="stats-bar-label">{{ __('index.counter_deals') }}</div>
                    </div>
                </div>
                <div class="stats-bar-item">
                    <div class="stats-bar-icon"><x-icon name="star" size="28"/></div>
                    <div>
                        <div class="stats-bar-number">10+</div>
                        <div class="stats-bar-label">{{ __('index.stats_experience') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Stats Bar End -->

    <!-- Property Type Section Start -->
    <section class="property-type-section">
        <div class="container">

            <!-- Section Title -->
            <div class="section-head-row" data-aos="fade-up" data-aos-duration="600">
                <div>
                    <span class="section-eyebrow">{{ __('index.property_type_eyebrow') }}</span>
                    <h2 class="section-h2">{{ __('index.property_type_title') }} <span>{{ __('index.property_type_highlight') }}</span></h2>
                </div>
                <a href="/{{ app()->getLocale() }}/property" class="btn btn-dark d-inline-flex align-items-center gap-2">
                    {{ __('index.property_type_view_more') }}<x-icon name="north_east" size="18"/>
                </a>
            </div>

            @php
                $propertyTypeConfig = [
                    'House'     => ['label' => __('index.property_type_houses'),     'fallback' => 'build/img/property-type/property-type-01.jpg', 'icon' => 'home'],
                    'Office'    => ['label' => __('index.property_type_offices'),    'fallback' => 'build/img/property-type/property-type-02.jpg', 'icon' => 'office'],
                    'Villa'     => ['label' => __('index.property_type_villas'),     'fallback' => 'build/img/property-type/property-type-03.jpg', 'icon' => 'villa'],
                    'Apartment' => ['label' => __('index.property_type_apartments'), 'fallback' => 'build/img/property-type/property-type-04.jpg', 'icon' => 'apartment'],
                ];
                $activeTypes = array_filter($propertyTypeConfig, fn($_, $type) => ($typeCounts[$type] ?? 0) > 0, ARRAY_FILTER_USE_BOTH);
            @endphp

            <div class="pt-grid">
                @foreach($activeTypes as $type => $config)
                <div data-aos="fade-up" data-aos-duration="{{ 500 + array_search($type, array_keys($activeTypes)) * 150 }}">
                    <x-property-type-card
                        :type="$type"
                        :label="$config['label']"
                        :count="$typeCounts[$type] ?? 0"
                        :images="$typeImages[$type] ?? []"
                        :fallback="$config['fallback']"
                        :icon="$config['icon']"
                    />
                </div>
                @endforeach
            </div>

        </div>
    </section>
    <!-- Property Type Section End -->

    <!-- Support Section Start -->
    <section class="support-section">
        <div class="horizontal-slide d-flex" data-direction="right" data-speed="slow">
            <div class="slide-list d-flex">
                <div class="support-item">
                    <h5>{{ __('index.support_personalized') }}</h5>
                </div>
                <div class="support-item">
                    <h5>{{ __('index.support_planning') }}</h5>
                </div>
                <div class="support-item">
                    <h5>{{ __('index.support_guidance') }}</h5>
                </div>
                <div class="support-item">
                    <h5>{{ __('index.support_local') }}</h5>
                </div>
                <div class="support-item">
                    <h5>{{ __('index.support_customer') }}</h5>
                </div>
                <div class="support-item">
                    <h5>{{ __('index.support_sustainability') }}</h5>
                </div>
                <div class="support-item">
                    <h5>{{ __('index.support_regions') }}</h5>
                </div>
            </div>
        </div>
    </section>
    <!-- Support Section End -->

    <!-- Featured Listings Section Start -->
    <section class="featured-listings-section">
        <div class="container">

            @php
                $featuredAll  = array_values(array_slice(array_merge($rentProperties, $saleProperties), 0, 5));
                $featuredMain = $featuredAll[0] ?? null;
                $featuredRest = array_slice($featuredAll, 1, 4);
                $locale       = app()->getLocale();
            @endphp

            <div class="row gy-4 align-items-start">

                <!-- Left: section heading + large featured card -->
                <div class="col-lg-5" data-aos="fade-right" data-aos-duration="700">
                    <div class="featured-section-head mb-4">
                        <span class="featured-eyebrow">{{ __('index.featured_eyebrow') }}</span>
                        <h2 class="featured-heading">{{ __('index.featured_title') }} <span>{{ __('index.featured_highlight') }}</span></h2>
                        <p class="featured-desc">{{ __('index.featured_description') }}</p>
                        <a href="/{{ $locale }}/property" class="btn btn-dark d-inline-flex align-items-center gap-2">
                            {{ __('index.explore_all_listings') }}<x-icon name="north_east" size="18"/>
                        </a>
                    </div>

                    @if($featuredMain)
                    @php
                        $txMain  = strtolower($featuredMain['transactionType'] ?? '');
                        $ptMain  = 'property.' . strtolower($featuredMain['propertyType'] ?? '');
                    @endphp
                    <div class="featured-main-card" data-slug="{{ $featuredMain['slug'] }}"
                         style="cursor:pointer;"
                         onclick="if(!event.target.closest('.compare-btn')&&!event.target.closest('.favourite')){window.location.href='/{{ $locale }}/property/{{ $featuredMain['slug'] }}'}">
                        <div class="featured-main-img">
                            @if(!empty($featuredMain['primaryImageUrl']))
                                <img src="{{ $featuredMain['primaryImageUrl'] }}" alt="{{ $featuredMain['title'] }}">
                            @else
                                <div class="featured-main-img-placeholder"></div>
                            @endif
                            <div class="featured-main-overlay">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge {{ $txMain === 'sale' ? 'bg-success' : 'bg-info' }}">
                                        {{ $txMain === 'sale' ? __('index.popular_for_sale') : __('index.popular_for_rent') }}
                                    </span>
                                    <div class="d-flex gap-1">
                                        <a href="javascript:void(0)" class="compare-btn"
                                           style="background:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.15);">
                                            <x-icon name="balance" size="17" style="color:#555"/>
                                        </a>
                                        <a href="javascript:void(0)" class="favourite"
                                           style="background:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.15);">
                                            <x-icon name="favorite_border" size="17"/>
                                        </a>
                                    </div>
                                </div>
                                <h5 class="featured-main-title">{{ $featuredMain['title'] }}</h5>
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="featured-main-address mb-0">
                                        <x-icon name="location_on" size="14"/>
                                        {{ $featuredMain['city'] ?? '' }}
                                    </p>
                                    <span class="featured-main-price">
                                        <x-price :amount="$featuredMain['price'] ?? 0" :currency="$featuredMain['currency'] ?? null" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right: 2x2 grid of smaller cards -->
                <div class="col-lg-7" data-aos="fade-left" data-aos-duration="700">
                    <div class="row g-3">
                        @foreach($featuredRest as $i => $property)
                        <x-property-card :prop="$property" col="col-sm-6" />
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- Featured Listings Section End -->

    <!-- Services / Benefits Section Start -->
    <section class="services-section">
        <div class="container">

            <!-- Section Title -->
            <div class="text-center mb-5" data-aos="fade-up" data-aos-duration="600">
                <span class="section-eyebrow">{{ __('index.benefits_eyebrow') }}</span>
                <h2 class="section-h2">{{ __('index.benefits_title') }} <span>{{ __('index.benefits_highlight') }}</span></h2>
                <p class="services-subtitle">{{ __('index.benefits_description') }}</p>
            </div>

            @php
            $services = [
                ['icon' => 'check',              'title' => __('index.benefits_verified'),      'desc' => __('index.benefits_verified_desc')],
                ['icon' => 'visibility',         'title' => __('index.benefits_reach'),         'desc' => __('index.benefits_reach_desc')],
                ['icon' => 'domain',             'title' => __('index.benefits_communication'), 'desc' => __('index.benefits_communication_desc')],
                ['icon' => 'phone',              'title' => __('index.benefits_expert'),        'desc' => __('index.benefits_expert_desc')],
                ['icon' => 'crop_square',        'title' => __('index.benefits_tailored'),      'desc' => __('index.benefits_tailored_desc')],
                ['icon' => 'language',           'title' => __('index.benefits_seamless'),      'desc' => __('index.benefits_seamless_desc')],
            ];
            @endphp

            <div class="services-grid">
                @foreach($services as $i => $svc)
                <div class="svc-card" data-aos="fade-up" data-aos-duration="{{ 400 + $i * 100 }}">
                    <div class="svc-header">
                        <div class="svc-icon">
                            <x-icon name="{{ $svc['icon'] }}" size="26"/>
                        </div>
                        <h5 class="svc-title">{{ $svc['title'] }}</h5>
                    </div>
                    <p class="svc-desc">{{ $svc['desc'] }}</p>
                </div>
                @endforeach
            </div>

        </div>
    </section>
    <!-- Services / Benefits Section End -->

    <!-- Feature Location Section Start -->
    <section class="feature-location-section">
        <div class="container">

            <!-- Section Title -->
            <div class="section-head-row" data-aos="fade-up" data-aos-duration="600">
                <div>
                    <span class="section-eyebrow">{{ __('index.feature_eyebrow') }}</span>
                    <h2 class="section-h2">{{ __('index.feature_title') }} <span>{{ __('index.feature_highlight') }}</span></h2>
                </div>
                <a href="/{{ app()->getLocale() }}/map" class="btn btn-dark d-inline-flex align-items-center gap-2">
                    {{ __('index.more_locations') }}<x-icon name="north_east" size="18"/>
                </a>
            </div>

            <!-- Cities grid -->
            <div class="row g-3">

                @php $shown = 0; @endphp
                @foreach($cityCounts as $city => $count)
                    @if($shown >= 8) @break @endif
                    @php $imgs = $cityImages[$city] ?? []; @endphp
                    @if(count($imgs) > 0)
                    <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="{{ 400 + $shown * 80 }}">
                        <a href="/{{ app()->getLocale() }}/property?city={{ urlencode($city) }}" class="city-card">
                            <div class="city-card-img">
                                @if(count($imgs) === 1)
                                    <img src="{{ $imgs[0] }}" alt="{{ $city }}">
                                @else
                                    <div class="city-slideshow" style="position:absolute;inset:0;">
                                        @foreach($imgs as $idx => $imgUrl)
                                        <img src="{{ $imgUrl }}" class="city-slide"
                                             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:{{ $idx === 0 ? '1' : '0' }};transition:opacity 0.8s ease;"
                                             alt="{{ $city }}">
                                        @endforeach
                                    </div>
                                @endif
                                <div class="city-card-overlay"></div>
                                <div class="city-card-body">
                                    <h5 class="city-card-name">{{ $city }}</h5>
                                    <span class="city-card-count">{{ $count }} {{ __('index.properties') }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @php $shown++; @endphp
                    @endif
                @endforeach

                @if($shown < 8)
                <div class="col-lg-3 col-sm-6" data-aos="fade-up">
                    <div class="city-card city-card--soon">
                        <div class="city-card-img">
                            <div class="city-card-overlay"></div>
                            <div class="city-card-body city-card-body--soon">
                                <x-icon name="location_on" size="32"/>
                                <span>{{ __('index.coming_soon') }}</span>
                                <small>{{ __('index.coming_soon_sub') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>

        </div>
    </section>
    <!-- Feature Location Section End -->

    <!-- How It Works Section Start -->
    <section class="how-it-works-section">
        <div class="container">

            <!-- Section Title -->
            <div class="text-center mb-5" data-aos="fade-up" data-aos-duration="600">
                <span class="section-eyebrow">{{ __('index.work_badge') }}</span>
                <h2 class="section-h2 hiw-title">{{ __('index.work_how_title') }}</h2>
                <p class="hiw-subtitle">{{ __('index.work_description') }}</p>
            </div>

            @php
            $steps = [
                ['num' => '01', 'icon' => 'search',      'title' => __('index.work_step1'), 'desc' => __('index.work_step1_desc')],
                ['num' => '02', 'icon' => 'domain',      'title' => __('index.work_step2'), 'desc' => __('index.work_step2_desc')],
                ['num' => '03', 'icon' => 'phone',       'title' => __('index.work_step3'), 'desc' => __('index.work_step3_desc')],
            ];
            @endphp

            <!-- Steps row -->
            <div class="hiw-steps">
                @foreach($steps as $i => $step)

                <div class="hiw-step" data-aos="fade-up" data-aos-duration="{{ 400 + $i * 150 }}">
                    <div class="hiw-step-num">{{ $step['num'] }}</div>
                    <div class="hiw-step-header">
                        <div class="hiw-step-icon">
                            <x-icon name="{{ $step['icon'] }}" size="28"/>
                        </div>
                        <h5 class="hiw-step-title">{{ $step['title'] }}</h5>
                    </div>
                    <p class="hiw-step-desc">{{ $step['desc'] }}</p>
                </div>


                @endforeach
            </div>

            <!-- CTA -->
            <div class="text-center mt-5" data-aos="fade-up" data-aos-duration="700">
                <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2">
                    {{ __('index.work_find_property') }}<x-icon name="north_east" size="18"/>
                </a>
            </div>

        </div>
    </section>
    <!-- How It Works Section End -->


    <!-- FAQ Section Start -->
    <section class="faq-section-two">
        <div class="container">

            <div class="row align-items-center gy-5">

                <!-- Left: title + stat badge -->
                <div class="col-lg-5" data-aos="fade-right" data-aos-duration="700">
                    <span class="section-eyebrow">{{ __('index.faq_eyebrow') }}</span>
                    <h2 class="section-h2 mt-2 mb-3">{{ __('index.faq_title') }} <span>{{ __('index.faq_highlight') }}</span></h2>
                    <p class="faq-intro">{{ __('index.faq_description') }}</p>

                    <div class="faq-stat-badge">
                        <div class="faq-stat-num">10+</div>
                        <div class="faq-stat-text">{!! __('index.faq_experience') !!}</div>
                    </div>
                </div>

                <!-- Right: accordion -->
                <div class="col-lg-7" data-aos="fade-left" data-aos-duration="700">
                    <div class="accordion faq-accordion" id="faq-accordion">

                        @foreach([
                            ['q' => __('index.faq_q1'), 'a' => __('index.faq_a1'), 'open' => true],
                            ['q' => __('index.faq_q2'), 'a' => __('index.faq_a2'), 'open' => false],
                            ['q' => __('index.faq_q3'), 'a' => __('index.faq_a3'), 'open' => false],
                            ['q' => __('index.faq_q4'), 'a' => __('index.faq_a4'), 'open' => false],
                            ['q' => __('index.faq_q5'), 'a' => __('index.faq_a5'), 'open' => false],
                        ] as $i => $faq)
                        <div class="accordion-item faq-item">
                            <div class="accordion-header">
                                <button class="accordion-button {{ $faq['open'] ? '' : 'collapsed' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#faq-{{ $i }}"
                                        aria-expanded="{{ $faq['open'] ? 'true' : 'false' }}">
                                    <span class="faq-q-num">0{{ $i + 1 }}</span>
                                    {{ $faq['q'] }}
                                </button>
                            </div>
                            <div id="faq-{{ $i }}" class="accordion-collapse collapse {{ $faq['open'] ? 'show' : '' }}" data-bs-parent="#faq-accordion">
                                <div class="accordion-body faq-body">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- FAQ Section End -->


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.city-slideshow').forEach(function (slideshow) {
        var slides = slideshow.querySelectorAll('.city-slide');
        if (slides.length < 2) return;
        var current = 0;
        setInterval(function () {
            slides[current].style.opacity = '0';
            current = (current + 1) % slides.length;
            slides[current].style.opacity = '1';
        }, 3000);
    });

    document.querySelectorAll('.type-slideshow').forEach(function (slideshow) {
        var slides = slideshow.querySelectorAll('img');
        if (slides.length < 2) return;
        var current = 0;
        setInterval(function () {
            slides[current].style.opacity = '0';
            current = (current + 1) % slides.length;
            slides[current].style.opacity = '1';
        }, 3000);
    });
});

// ── City autocomplete (index) ────────────────
(function () {
    var input    = document.getElementById('cityInputIndex');
    var hidden   = document.getElementById('cityHiddenIndex');
    var list     = document.getElementById('citySuggestionsIndex');
    var clearBtn = document.getElementById('cityClearBtnIndex');
    var spinner  = document.getElementById('citySpinnerIndex');
    if (!input || !list) return;

    document.body.appendChild(list);

    var timer = null;
    var lang  = '{{ app()->getLocale() }}';
    var shown = {};

    function parseYandex(body) {
        var m = (body || '').trim().match(/suggest\.apply\(([\s\S]+)\)/);
        if (!m) return [];
        try { var data = JSON.parse(m[1]); } catch (e) { return []; }
        return (data.results || []).map(function (item) {
            var title = (item.title || {}).text || '';
            var where = ((item.log_id || {}).where) || {};
            if (!title || title !== (where.title || '')) return null;
            var parts = (where.name || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
            var desc = parts.filter(function (p) { return p !== title; }).join(', ');
            return { name: title, desc: desc };
        }).filter(Boolean);
    }

    function makeLi(name, desc, enName) {
        var li = document.createElement('li');
        li.innerHTML = '<i class="material-icons-outlined city-item-icon">location_on</i>'
            + '<span class="city-item-text">'
            +   '<span class="city-item-name">' + name + '</span>'
            +   (desc ? '<span class="city-item-desc">' + desc + '</span>' : '')
            + '</span>';
        li.addEventListener('mousedown', function (e) {
            e.preventDefault();
            input.value = name;
            if (hidden) hidden.value = enName || name;
            if (clearBtn) clearBtn.style.display = 'flex';
            list.style.display = 'none';
            list.innerHTML = '';
            shown = {};
        });
        return li;
    }

    function positionList() {
        var rect = input.getBoundingClientRect();
        list.style.top   = (rect.bottom + window.scrollY) + 'px';
        list.style.left  = (rect.left   + window.scrollX) + 'px';
        list.style.width = rect.width + 'px';
    }

    function showSuggestions(items) {
        list.innerHTML = '';
        shown = {};
        if (!items.length) { list.style.display = 'none'; return; }
        positionList();
        items.forEach(function (it) {
            if (shown[it.name]) return;
            shown[it.name] = true;
            list.appendChild(makeLi(it.name, it.desc, it.enName));
        });
        list.style.display = 'block';
    }

    function updateClearBtn() {
        if (clearBtn) clearBtn.style.display = input.value ? 'flex' : 'none';
    }

    input.addEventListener('input', function () {
        updateClearBtn();
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) { list.style.display = 'none'; list.innerHTML = ''; shown = {}; return; }

        timer = setTimeout(function () {
            var yLang = lang === 'en' ? 'en_US' : lang === 'hy' ? 'hy_AM' : 'ru_RU';
            var base  = 'https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&results=7&highlight=0&v=9';
            var pLocal = fetch(base + '&lang=' + yLang).then(function (r) { return r.text(); }).catch(function () { return ''; });
            var pEn    = lang === 'en' ? Promise.resolve(null) : fetch(base + '&lang=en_US').then(function (r) { return r.text(); }).catch(function () { return ''; });
            Promise.all([pLocal, pEn]).then(function (texts) {
                var local = parseYandex(texts[0]);
                var en    = texts[1] !== null ? parseYandex(texts[1]) : local;
                var combined = local.map(function (it, i) {
                    return { name: it.name, desc: it.desc, enName: (en[i] || {}).name || it.name };
                });
                showSuggestions(combined);
            }).catch(function () { list.style.display = 'none'; });
        }, 150);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            if (hidden) hidden.value = '';
            clearBtn.style.display = 'none';
            list.style.display = 'none';
            list.innerHTML = '';
            shown = {};
            input.focus();
        });
    }

    input.addEventListener('blur', function () {
        setTimeout(function () { list.style.display = 'none'; }, 200);
    });

    updateClearBtn();
}());

// ── City: гарантированный резолв English имени перед submit ──────────
(function () {
    var form    = document.querySelector('.home-search-2 form');
    var display = document.getElementById('cityInputIndex');
    var hidden  = document.getElementById('cityHiddenIndex');
    var lang    = '{{ app()->getLocale() }}';
    if (!form || !display || !hidden) return;

    form.addEventListener('submit', function (e) {
        var q = display.value.trim();
        if (!q) { hidden.value = ''; return; }
        if (lang === 'en') { hidden.value = q; return; }
        // Если hidden уже содержит другое (резолвленное) значение — доверяем ему
        if (hidden.value && hidden.value !== q) return;
        // Иначе резолвим перед отправкой
        e.preventDefault();
        fetch('https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&lang=en_US&results=1&highlight=0&v=9')
            .then(function (r) { return r.text(); })
            .then(function (body) {
                var m = body.trim().match(/suggest\.apply\(([\s\S]+)\)/);
                var data = m ? JSON.parse(m[1]) : {};
                var first = (data.results || [])[0];
                hidden.value = first ? ((first.title || {}).text || q) : q;
            })
            .catch(function () { hidden.value = q; })
            .finally(function () { form.submit(); });
    });
}());

// ── Price inputs: block negative, ±1000 buttons ──────────────────────
(function () {
    ['minPriceIndex', 'maxPriceIndex'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('keydown', function (e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E') e.preventDefault();
        });
    });
    document.querySelectorAll('[data-price-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector('[name="' + btn.dataset.target + '"]');
            if (!target) return;
            var step = parseInt(btn.dataset.priceStep, 10);
            target.value = Math.max(0, (parseInt(target.value, 10) || 0) + step);
        });
    });
    // Strip negative values before submit
    document.querySelector('.home-search-2 form') && document.querySelector('.home-search-2 form').addEventListener('submit', function () {
        ['minPriceIndex', 'maxPriceIndex'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && parseInt(el.value, 10) < 0) el.value = '';
        });
    });
}());

// ── Custom select dropdowns for Buy/Rent + Type (modern dropdown like the filter) ──
// DOMContentLoaded so script.js (loaded in the footer) has defined initCustomSelects.
document.addEventListener('DOMContentLoaded', function () {
    if (typeof initCustomSelects === 'function') initCustomSelects();
});
</script>

@endsection
