<?php $page = 'index'; ?>
@section('title')
    Home
@endsection

@extends('layout.mainlayout')
@section('content')
    <!-- Home Banner Section Start -->
    <section class="home-banner-two">

        <div>
            <div class="banner-img-right" data-aos="fade-down" data-aos-duration="1000">
                <img src="{{URL::asset('build/img/section-bg/banner-bg-02.png')}}" alt="">
            </div>
            <div>
                <img src="{{URL::asset('build/img/bg/banner-shape.svg')}}" class="banner-shape" alt="">
            </div>
        </div>

        <div class="container">

            <!-- start row -->
            <div class="row">

                <div class="col-lg-5">
                    <div class="banner-users d-flex align-items-center flex-wrap gap-2 mb-3">
                        @if(!empty($stats['propertiesListed']))
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="mb-0 me-2 text-white fw-semibold fs-14">{{ $stats['propertiesListed'] }}+ {{ __('index.counter_rentals') }}</h6>
                            </div>
                            <p class="mb-0 text-white fs-13">{{ __('index.hero_trusted') }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="banner-title aos" data-aos="fade-up">
                        <h1>{{ __('index.hero_title') }} <span>{{ __('index.hero_title_highlight') }}</span> {{ __('index.hero_title_end') }}</h1>
                        <p>{{ __('index.hero_description') }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary btn-lg d-inline-flex align-items-center me-3"><i class="material-icons-outlined me-2">shopping_basket</i>{{ __('index.hero_buy_property') }}</a>
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
            <form action="/{{ app()->getLocale() }}/property" method="GET">
                <!-- start search grid -->
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_buy_sell') }}</label>
                        <select name="transactionType" class="select">
                            <option value="">{{ __('index.search_select') }}</option>
                            <option value="Sale">{{ __('map.sale') }}</option>
                            <option value="Rent">{{ __('map.rent_monthly') }}</option>
                            <option value="RentDaily">{{ __('map.rent_daily') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_type') }}</label>
                        <select name="propertyType" class="select">
                            <option value="">{{ __('index.search_select') }}</option>
                            <option value="Apartment">{{ __('property.apartment') }}</option>
                            <option value="House">{{ __('property.house') }}</option>
                            <option value="Villa">{{ __('property.villa') }}</option>
                            <option value="Condo">{{ __('property.condo') }}</option>
                            <option value="Land">{{ __('property.land') }}</option>
                            <option value="Office">{{ __('property.office') }}</option>
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
                            <input type="number" name="minPrice" id="minPriceIndex" min="0" placeholder="{{ __('index.search_currency_symbol') }}">
                            <div class="stepper-btns">
                                <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl">
                        <label class="form-label">{{ __('index.search_max_price') }}</label>
                        <div class="filter-stepper">
                            <input type="number" name="maxPrice" id="maxPriceIndex" min="0" placeholder="{{ __('index.search_currency_symbol') }}">
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
        </div>
    </div>
    <!-- Search End -->

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

    <!-- Property Type Section Start -->
    <section class="property-type-section">
        <div class="pt-blob pt-blob-1" aria-hidden="true"></div>
        <div class="pt-blob pt-blob-2" aria-hidden="true"></div>
        <div class="pt-blob pt-blob-3" aria-hidden="true"></div>
        <div class="container">

            <!-- Section Title Start -->
            <div class="section-title-2" data-aos="fade-up" data-aos-duration="1000">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                    <h2>{{ __('index.property_type_title') }} <span> {{ __('index.property_type_highlight') }}</span> {{ __('index.property_type_end') }} </h2>
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                </div>
                <p>{{ __('index.property_type_description') }}</p>
            </div>
            <!-- Section Title End -->

            @php
                $propertyTypeConfig = [
                    'House'     => ['label' => __('index.property_type_houses'),     'fallback' => 'build/img/property-type/property-type-01.jpg'],
                    'Office'    => ['label' => __('index.property_type_offices'),    'fallback' => 'build/img/property-type/property-type-02.jpg'],
                    'Villa'     => ['label' => __('index.property_type_villas'),     'fallback' => 'build/img/property-type/property-type-03.jpg'],
                    'Apartment' => ['label' => __('index.property_type_apartments'), 'fallback' => 'build/img/property-type/property-type-04.jpg'],
                ];
                $activeTypes = array_filter($propertyTypeConfig, fn($_, $type) => ($typeCounts[$type] ?? 0) > 0, ARRAY_FILTER_USE_BOTH);
                $colClass = count($activeTypes) > 0 ? 'col-lg-' . min(12, intval(12 / count($activeTypes))) . ' col-sm-6' : 'col-lg-3 col-sm-6';
            @endphp

            <div class="d-flex flex-wrap justify-content-center gap-4">
                @foreach($activeTypes as $type => $config)
                @php $delay = 1000 + (array_search($type, array_keys($activeTypes)) * 500); @endphp
                <div data-aos="fade-up" data-aos-duration="{{ $delay }}">
                    <x-property-type-card
                        :type="$type"
                        :label="$config['label']"
                        :count="$typeCounts[$type] ?? 0"
                        :images="$typeImages[$type] ?? []"
                        :fallback="$config['fallback']"
                    />
                </div>
                @endforeach
            </div>

            <div class="text-center pt-3">
                <a href="/{{ app()->getLocale() }}/property" class="btn btn-dark d-inline-flex align-items-center">{{ __('index.property_type_view_more') }}<i class="material-icons-outlined ms-1">north_east</i></a>
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

    <!-- Popular Listing Section Start -->
    <section class="popular-listing-section">
        <div class="container">

            <!-- Section Title Start -->
            <div class="section-title-2" data-aos="fade-up" data-aos-duration="500">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                    <h2>{{ __('index.popular_title') }} <span> {{ __('index.popular_highlight') }}</span> {{ __('index.popular_end') }}</h2>
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                </div>
                <p>{{ __('index.popular_description') }}</p>
            </div>
            <!-- Section Title End -->

            <ul class="nav nav-pills listing-nav-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#listing-1" role="tab" aria-controls="listing-1" aria-selected="true">
                        {{ __('index.popular_for_rent') }}
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#listing-2" role="tab" aria-controls="listing-2" aria-selected="false" tabindex="-1">
                        {{ __('index.popular_for_sale') }}
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade active show" id="listing-1" role="tabpanel">

                    <!-- Skeleton Grid -->
                    <div class="row listing-skeleton">
                        @for($i = 0; $i < 6; $i++)
                        <div class="col-xl-4 col-md-6 d-flex">
                            <div class="property-card flex-fill skeleton-card">
                                <div class="property-listing-item p-0 mb-0 shadow-none">
                                    <div class="buy-grid-img mb-0 rounded-0" style="overflow:hidden">
                                        <span class="skeleton-block" style="width:100%;height:210px;border-radius:0"></span>
                                    </div>
                                    <div class="buy-grid-content">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="skeleton-block" style="width:80px;height:22px;border-radius:20px"></span>
                                            <span class="skeleton-block" style="width:55px;height:14px"></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="skeleton-block mb-2" style="width:75%;height:20px"></span>
                                            <span class="skeleton-block" style="width:90%;height:14px"></span>
                                        </div>
                                        <span class="skeleton-block mb-3" style="width:100%;height:54px;border-radius:8px"></span>
                                        <div class="d-flex justify-content-between">
                                            <span class="skeleton-block" style="width:42%;height:13px"></span>
                                            <span class="skeleton-block" style="width:35%;height:13px"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <!-- Real Grid -->
                    <div class="row listing-real" style="display:none;opacity:0;transition:opacity 0.35s ease">

                        @foreach($rentProperties as $property)
                        <x-property-card :prop="$property" />
                        @endforeach

                        <div class="col-md-12">
                            <div class="text-center pt-3">
                                <a href="/{{ app()->getLocale() }}/property" class="btn btn-dark d-inline-flex align-items-center">{{ __('index.explore_all_listings') }}<i class="material-icons-outlined ms-1">north_east</i></a>
                            </div>
                        </div> <!-- end col -->

                    </div>

                </div>

                <div class="tab-pane fade" id="listing-2" role="tabpanel">

                    <!-- Skeleton Grid -->
                    <div class="row listing-skeleton">
                        @for($i = 0; $i < 6; $i++)
                        <div class="col-xl-4 col-md-6 d-flex">
                            <div class="property-card flex-fill skeleton-card">
                                <div class="property-listing-item p-0 mb-0 shadow-none">
                                    <div class="buy-grid-img mb-0 rounded-0" style="overflow:hidden">
                                        <span class="skeleton-block" style="width:100%;height:210px;border-radius:0"></span>
                                    </div>
                                    <div class="buy-grid-content">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <span class="skeleton-block" style="width:80px;height:22px;border-radius:20px"></span>
                                            <span class="skeleton-block" style="width:55px;height:14px"></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="skeleton-block mb-2" style="width:75%;height:20px"></span>
                                            <span class="skeleton-block" style="width:90%;height:14px"></span>
                                        </div>
                                        <span class="skeleton-block mb-3" style="width:100%;height:54px;border-radius:8px"></span>
                                        <div class="d-flex justify-content-between">
                                            <span class="skeleton-block" style="width:42%;height:13px"></span>
                                            <span class="skeleton-block" style="width:35%;height:13px"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <!-- Real Grid -->
                    <div class="row listing-real" style="display:none;opacity:0;transition:opacity 0.35s ease">

                        @foreach($saleProperties as $property)
                        <x-property-card :prop="$property" />
                        @endforeach

                        <div class="col-md-12">
                            <div class="text-center pt-3">
                                <a href="/{{ app()->getLocale() }}/property" class="btn btn-dark d-inline-flex align-items-center">{{ __('index.explore_all_listings') }}<i class="material-icons-outlined ms-1">north_east</i></a>
                            </div>
                        </div> <!-- end col -->

                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- Popular Listing Section End -->

    <!-- Exclusive Benifits Section Start -->
    <section class="exclusive-benifit-section">
        <div class="container">

            <!-- Section Title Start -->
            <div class="section-title-2" data-aos="fade-up" data-aos-duration="500">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                    <h2>{{ __('index.benefits_title') }} <span> {{ __('index.benefits_highlight') }}</span> {{ __('index.benefits_end') }}</h2>
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                </div>
                <p>{{ __('index.benefits_description') }}</p>
            </div>
            <!-- Section Title End -->

            <!-- start row -->
            <div class="row">

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1000">
                    <div class="benifit-item">
                        <span class="benifit-icon">
                            <i class="material-icons-outlined">check_circle</i>
                        </span>
                        <div>
                            <h5 class="mb-2">{{ __('index.benefits_verified') }}</h5>
                            <p class="mb-0">{{ __('index.benefits_verified_desc') }}</p>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1500">
                    <div class="benifit-item">
                        <span class="benifit-icon">
                            <i class="material-icons-outlined">check_circle</i>
                        </span>
                        <div>
                            <h5 class="mb-2">{{ __('index.benefits_reach') }}</h5>
                            <p class="mb-0">{{ __('index.benefits_reach_desc') }}</p>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="2000">
                    <div class="benifit-item">
                        <span class="benifit-icon">
                            <i class="material-icons-outlined">check_circle</i>
                        </span>
                        <div>
                            <h5 class="mb-2">{{ __('index.benefits_communication') }}</h5>
                            <p class="mb-0">{{ __('index.benefits_communication_desc') }}</p>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="2500">
                    <div class="benifit-item">
                        <span class="benifit-icon">
                            <i class="material-icons-outlined">check_circle</i>
                        </span>
                        <div>
                            <h5 class="mb-2">{{ __('index.benefits_expert') }}</h5>
                            <p class="mb-0">{{ __('index.benefits_expert_desc') }}</p>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="3000">
                    <div class="benifit-item">
                        <span class="benifit-icon">
                            <i class="material-icons-outlined">check_circle</i>
                        </span>
                        <div>
                            <h5 class="mb-2">{{ __('index.benefits_tailored') }}</h5>
                            <p class="mb-0">{{ __('index.benefits_tailored_desc') }}</p>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="3000">
                    <div class="benifit-item">
                        <span class="benifit-icon">
                            <i class="material-icons-outlined">check_circle</i>
                        </span>
                        <div>
                            <h5 class="mb-2">{{ __('index.benefits_seamless') }}</h5>
                            <p class="mb-0">{{ __('index.benefits_seamless_desc') }}</p>
                        </div>
                    </div>
                </div> <!-- end col -->

            </div>
            <!-- end row -->

            @if(count($topViewedImages) > 0)
            <div class="sec-bottom-imgs">
                @foreach($topViewedImages as $i => $img)
                <div class="bottom-img-{{ $i + 1 }}">
                    <a href="/{{ app()->getLocale() }}/property/{{ $img['slug'] }}">
                        <img src="{{ $img['imageUrl'] }}" alt="">
                    </a>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </section>
    <!-- Exclusive Benifits Section End -->

    <!-- Feature Location Section Start -->
    <section class="feature-location-section">
        <div class="container">

            <!-- Section Title Start -->
            <div class="section-title-2" data-aos="fade-up" data-aos-duration="500">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                    <h2>{{ __('index.feature_title') }} <span> {{ __('index.feature_highlight') }}</span> {{ __('index.feature_end') }}</h2>
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                </div>
                <p>{{ __('index.feature_description') }}</p>
            </div>
            <!-- Section Title End -->

            <!-- start row -->
            <div class="row g-4">

                @php $shown = 0; @endphp
                @foreach($cityCounts as $city => $count)
                    @if($shown >= 8) @break @endif
                    @php $imgs = $cityImages[$city] ?? []; @endphp
                    @if(count($imgs) > 0)
                    <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="{{ 1000 + $shown * 300 }}">
                        <div class="location-item-two">
                            <div class="location-img" style="position:relative;overflow:hidden;cursor:pointer;border-radius:10px"
                                 onclick="window.location.href='/{{ app()->getLocale() }}/property?city={{ urlencode($city) }}'">
                                @if(count($imgs) === 1)
                                    <img src="{{ $imgs[0] }}" class="img-fluid w-100" style="object-fit:cover;height:220px" alt="{{ $city }}">
                                @else
                                    <div class="city-slideshow" data-images="{{ json_encode($imgs) }}" style="height:220px;position:relative;">
                                        @foreach($imgs as $idx => $imgUrl)
                                        <img src="{{ $imgUrl }}"
                                             class="city-slide w-100"
                                             style="object-fit:cover;height:220px;position:absolute;top:0;left:0;opacity:{{ $idx === 0 ? '1' : '0' }};transition:opacity 0.8s ease;"
                                             alt="{{ $city }}">
                                        @endforeach
                                    </div>
                                @endif
                                <div class="position-absolute top-0 end-0 p-3 z-1"><span class="badge bg-light text-dark">{{ $count }} {{ __('index.properties') }}</span></div>
                                <h5 class="position-absolute start-0 bottom-0 text-white z-1 p-3 mb-0">{{ $city }}</h5>
                            </div>
                        </div>
                    </div>
                    @php $shown++; @endphp
                    @endif
                @endforeach

                @if($shown < 8)
                <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="{{ 1000 + $shown * 300 }}">
                    <div class="location-item-two">
                        <div class="location-img" style="height:220px;border-radius:10px;overflow:hidden;position:relative;background:var(--gray-100);border:2px dashed var(--gray-300);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;">
                            <i class="material-icons-outlined" style="font-size:36px;color:var(--gray-400)">location_city</i>
                            <span style="font-weight:600;color:var(--gray-500);font-size:15px">{{ __('index.coming_soon') }}</span>
                            <span style="font-size:12px;color:var(--gray-400)">{{ __('index.coming_soon_sub') }}</span>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            <!-- end row -->

            <div class="text-center pt-3">
                <a href="/{{ app()->getLocale() }}/map" class="btn btn-dark d-inline-flex align-items-center">{{ __('index.more_locations') }}<i class="material-icons-outlined ms-1">north_east</i></a>
            </div>

        </div>
    </section>
    <!-- Feature Location Section End -->

    <!-- Work Section Start -->
    <section class="work-section">
        <div class="container">

            <!-- start row -->
            <div class="row align-items-center justify-content-lg-end"	>

                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title-2" data-aos="fade-up" data-aos-duration="500">
                        <div class="d-flex align-items-center mb-3">
                            <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                            <span class="text-white d-inline-block ms-2">{{ __('index.work_badge') }}</span>
                        </div>
                        <h2>{{ __('index.work_title') }}</h2>
                        <p>{{ __('index.work_description') }}</p>
                        <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary">{{ __('index.work_find_property') }}</a>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <div class="card work-item border-0 mb-0">
                        <div class="card-body">
                            <div class="mb-4">
                                <span class="badge bg-secondary mb-2">{{ __('index.work_how_badge') }}</span>
                                <h2>{{ __('index.work_how_title') }}</h2>
                            </div>
                            <div class="work-steps">
                                <h6 class="fw-semibold fs-16 mb-1 text-secondary">01. {{ __('index.work_step1') }}</h6>
                                <p class="mb-0 fs-14">{{ __('index.work_step1_desc') }}</p>
                            </div>
                            <div class="work-steps">
                                <h6 class="fw-semibold fs-16 mb-1 text-teal">02. {{ __('index.work_step2') }}</h6>
                                <p class="mb-0 fs-14">{{ __('index.work_step2_desc') }}</p>
                            </div>
                            <div class="work-steps mb-0">
                                <h6 class="fw-semibold fs-16 mb-1 text-purple">03. {{ __('index.work_step3') }}</h6>
                                <p class="mb-0 fs-14">{{ __('index.work_step3_desc') }}</p>
                            </div>
                        </div> <!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->

            </div>
            <!-- end row -->

        </div>
    </section>
    <!-- Work Section End -->


    <!-- FAQ Section Start -->
    <section class="faq-section-two">
        <div class="container">

            <!-- Section Title Start -->
            <div class="section-title-2">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                    <h2>{{ __('index.faq_title') }} <span> {{ __('index.faq_highlight') }}</span></h2>
                    <span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
                </div>
                <p>{{ __('index.faq_description') }}</p>
            </div>
            <!-- Section Title End -->

            <!-- start row -->
            <div class="row align-items-center gy-4">

                <div class="col-lg-6" data-aos="fade-up">
                    <div class="property-sec-img mt-0">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="mb-3"><img src="{{ $topViewedImages[0]['imageUrl'] ?? URL::asset('build/img/home-3/property/property-01.jpg') }}" class="img-fluid rounded" alt="" style="width:100%;height:200px;object-fit:cover"></div>
                                <div><img src="{{ $topViewedImages[1]['imageUrl'] ?? URL::asset('build/img/home-3/property/property-04.jpg') }}" class="img-fluid rounded" alt="" style="width:100%;height:200px;object-fit:cover"></div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3"><img src="{{ $topViewedImages[2]['imageUrl'] ?? URL::asset('build/img/home-3/property/property-02.jpg') }}" class="img-fluid rounded" alt="" style="width:100%;height:200px;object-fit:cover"></div>
                                <div><img src="{{ $topViewedImages[3]['imageUrl'] ?? URL::asset('build/img/home-3/property/property-03.jpg') }}" class="img-fluid rounded" alt="" style="width:100%;height:200px;object-fit:cover"></div>
                            </div>
                        </div>
                        <div class="rotate-div">
                        <div class="img-center-text">
                            <h3 class="mb-1 text-white">10+</h3>
                            <p class="mb-0 fs-14 text-white text-center">{!! __('index.faq_experience') !!}</p>
                        </div>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="1500">
                    <div class="accordion accordions-items-seperate faq-accordion" id="faq-accordion">
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-1" aria-expanded="true">
                                    {{ __('index.faq_q1') }}
                                </button>
                            </div>
                            <div id="accordion-1" class="accordion-collapse collapse show" data-bs-parent="#faq-accordion">
                                <div class="accordion-body">
                                    <p class="mb-0">{{ __('index.faq_a1') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-2" aria-expanded="false">
                                    {{ __('index.faq_q2') }}
                                </button>
                            </div>
                            <div id="accordion-2" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
                                <div class="accordion-body">
                                    <p class="mb-0">{{ __('index.faq_a2') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-3" aria-expanded="false">
                                    {{ __('index.faq_q3') }}
                                </button>
                            </div>
                            <div id="accordion-3" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
                                <div class="accordion-body">
                                    <p class="mb-0">{{ __('index.faq_a3') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-4" aria-expanded="false">
                                    {{ __('index.faq_q4') }}
                                </button>
                            </div>
                            <div id="accordion-4" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
                                <div class="accordion-body">
                                    <p class="mb-0">{{ __('index.faq_a4') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-5" aria-expanded="false">
                                    {{ __('index.faq_q5') }}
                                </button>
                            </div>
                            <div id="accordion-5" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
                                <div class="accordion-body">
                                    <p class="mb-0">{{ __('index.faq_a5') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->

            </div>
            <!-- end row -->
        </div>
    </section>
    <!-- FAQ Section End -->


<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Reveal listing grids, hide skeletons ──────────────────────────────
    document.querySelectorAll('.listing-skeleton').forEach(function (sk) {
        sk.style.display = 'none';
    });
    document.querySelectorAll('.listing-real').forEach(function (grid) {
        grid.style.display = '';
        requestAnimationFrame(function () { grid.style.opacity = '1'; });
    });

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
</script>

@endsection
