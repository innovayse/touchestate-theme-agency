<?php $page = 'map'; ?>
@section('title')
    Map View
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.properties_map') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.map') }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content">
            <div class="container-fluid">

                <!-- start row -->
                    <div class="row map-page-row">
                    <div class="col-lg-5 map-col-left">
                        <div class="map-section-filter">
                        <!-- Advanced Filter -->
                        <form method="GET" action="/{{ app()->getLocale() }}/map" id="filterForm">
                        <div class="advanced-filter">

                            <!-- Result count -->
                            <p class="filter-result mb-3">{{ __('map.result') }} <span class="result-value" id="result-loaded">{{ count($properties['items'] ?? []) }}</span> / <span class="result-value" id="result-total">{{ $properties['totalCount'] ?? 0 }}</span></p>

                            <!-- Search row -->
                            <div class="filter-search-row">
                                <div class="filter-search-input fsr-search">
                                    <i class="material-icons-outlined">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('map.search') }}">
                                </div>
                                <button type="submit" class="btn-filter-search fsr-submit">
                                    <i class="material-icons-outlined">search</i>
                                    {{ __('map.search') }}
                                </button>
                                <div class="fsr-break"></div>
                                <button type="button" class="btn-filter-toggle fsr-filter" id="advFilterToggle">
                                    <i class="material-icons-outlined">filter_alt</i>
                                    {{ __('map.filter') }}
                                    <i class="material-icons-outlined arrow">expand_more</i>
                                </button>
                                <ul class="grid-list-view d-flex align-items-center justify-content-center mb-0 fsr-views">
                                    <li><a href="/{{ app()->getLocale() }}/property" class="list-icon"><i class="material-icons">grid_view</i></a></li>
                                    <li><a href="/{{ app()->getLocale() }}/map" class="list-icon active"><i class="material-icons-outlined">location_on</i></a></li>
                                </ul>
                            </div>

                            <!-- Filter Panel -->
                            <div class="filter-panel" id="advFilterPanel">

                                <!-- Row 1: Property Type | Transaction Type | Sort | Order -->
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.property_type') }}</label>
                                            <select name="propertyType" class="filter-select">
                                                <option value="">{{ __('map.any') }}</option>
                                                @foreach(['Apartment','Villa','House','Condo','Townhouse','Room'] as $pt)
                                                <option value="{{ $pt }}" @selected(request('propertyType') === $pt)>{{ __('property.'.strtolower($pt)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.transaction_type') }}</label>
                                            <select name="transactionType" class="filter-select">
                                                <option value="">{{ __('map.any') }}</option>
                                                <option value="Sale" @selected(request('transactionType') === 'Sale')>{{ __('index.popular_for_sale') }}</option>
                                                <option value="RentMonthly" @selected(request('transactionType') === 'RentMonthly')>{{ __('index.popular_rent_monthly') }}</option>
                                                <option value="RentDaily" @selected(request('transactionType') === 'RentDaily')>{{ __('index.popular_rent_daily') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.sort') }}</label>
                                            <select name="sortBy" class="filter-select">
                                                <option value="date" @selected(request('sortBy','date') === 'date')>{{ __('map.date') }}</option>
                                                <option value="price" @selected(request('sortBy') === 'price')>{{ __('map.price') }}</option>
                                                <option value="area" @selected(request('sortBy') === 'area')>{{ __('map.min_area') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.order') }}</label>
                                            <select name="sortOrder" class="filter-select">
                                                <option value="desc" @selected(request('sortOrder','desc') === 'desc')>{{ __('map.descending') }}</option>
                                                <option value="asc" @selected(request('sortOrder') === 'asc')>{{ __('map.ascending') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: City | District -->
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.city') }}</label>
                                            <div class="filter-input" style="position:relative">
                                                <i class="material-icons-outlined">location_on</i>
                                                <input type="text" name="city" id="cityInputMap" value="{{ request('city') }}" placeholder="{{ __('map.enter_city') }}" autocomplete="off">
                                                <button type="button" id="cityClearBtnMap" class="city-clear-btn"><i class="material-icons-outlined">close</i></button>
                                                <span id="citySpinnerMap" class="city-loading-spinner"></span>
                                                <ul id="citySuggestionsMap" class="city-suggestions"></ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.district') }}</label>
                                            <div class="filter-input">
                                                <input type="text" name="district" value="{{ request('district') }}" placeholder="{{ __('map.enter_district') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Min Price | Max Price | Min Area | Max Area -->
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.min_price') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="minPrice" value="{{ request('minPrice') }}" placeholder="0" min="0" step="1000">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.max_price') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="maxPrice" value="{{ request('maxPrice') }}" placeholder="∞" min="0" step="1000">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.min_area') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="minArea" value="{{ request('minArea') }}" placeholder="0" min="0" step="10">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.max_area') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="maxArea" value="{{ request('maxArea') }}" placeholder="∞" min="0" step="10">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Rooms min | Rooms max | Bedrooms min | Bedrooms max -->
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.rooms') }}</label>
                                            <select name="minRooms" class="filter-select">
                                                <option value="">{{ __('map.any') }}</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" @selected(request('minRooms') == $i)>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.max_rooms') }}</label>
                                            <select name="maxRooms" class="filter-select">
                                                <option value="">{{ __('map.any') }}</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" @selected(request('maxRooms') == $i)>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.bedrooms_min') }}</label>
                                            <select name="minBedrooms" class="filter-select">
                                                <option value="">{{ __('map.any') }}</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" @selected(request('minBedrooms') == $i)>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.bedrooms_max') }}</label>
                                            <select name="maxBedrooms" class="filter-select">
                                                <option value="">{{ __('map.any') }}</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" @selected(request('maxBedrooms') == $i)>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Floor min | Floor max | Year Built from | Year Built to -->
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.floor_min') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="minFloor" value="{{ request('minFloor') }}" placeholder="0" min="0">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.floor_max') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="maxFloor" value="{{ request('maxFloor') }}" placeholder="∞" min="0">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.year_built_from') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="yearBuiltFrom" value="{{ request('yearBuiltFrom') }}" placeholder="1950" min="1950" max="{{ date('Y') }}">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.year_built_to') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="yearBuiltTo" value="{{ request('yearBuiltTo') }}" placeholder="{{ date('Y') }}" min="1950" max="{{ date('Y') }}">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 6: Land Area min | Land Area max -->
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.land_area_min') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="minLandArea" value="{{ request('minLandArea') }}" placeholder="0" min="0">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-group">
                                            <label class="filter-label">{{ __('map.land_area_max') }}</label>
                                            <div class="filter-stepper">
                                                <input type="number" name="maxLandArea" value="{{ request('maxLandArea') }}" placeholder="∞" min="0">
                                                <div class="stepper-btns">
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button>
                                                    <button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 7: Amenities -->
                                <div class="filter-group mb-0">
                                    <label class="filter-label">{{ __('map.amenities') }}</label>
                                    <div class="amenities-grid">
                                        @foreach([
                                            ['Elevator','elevator','elevator'],
                                            ['Parking','local_parking','parking'],
                                            ['Balcony','balcony','balcony'],
                                            ['Garage','garage','garage'],
                                            ['Pool','pool','pool'],
                                            ['Garden','yard','garden'],
                                            ['Basement','foundation','basement'],
                                            ['Gym','fitness_center','gym'],
                                            ['Security','security','security_24_7'],
                                        ] as [$val, $icon, $key])
                                        <label class="amenity-item">
                                            <input type="checkbox" name="amenities[]" value="{{ $val }}" @checked(in_array($val, request('amenities', [])))>
                                            <i class="material-icons-outlined">{{ $icon }}</i>
                                            <span>{{ __('map.'.$key) }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="filter-actions">
                                    <button type="button" class="btn-reset" onclick="window.location.href='/{{ app()->getLocale() }}/map'">{{ __('map.reset') }}</button>
                                    <button type="submit" class="btn-apply">{{ __('map.apply_filter') }}</button>
                                </div>

                            </div><!-- end filter-panel -->
                        </div><!-- end advanced-filter -->
                        </form>
                        </div><!-- end map-section-filter -->

                        <div class="map-section-grid">
                        <!-- Skeleton Grid (shown while page loads) -->
                        <div id="prop-grid-skeleton" class="row mb-4">
                            @for($i = 0; $i < 4; $i++)
                            <div class="col-lg-6 col-md-6 d-flex">
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

                        <!-- start row -->
                        <div class="row mb-4" id="prop-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                            @foreach($properties['items'] ?? [] as $prop)
                                @include('partials.map-card', ['prop' => $prop])
                            @endforeach
                        </div>
                        <!-- end row -->

                        <!-- Load More / Show Less -->
                        <div class="text-center mb-4 d-flex align-items-center justify-content-center gap-3" id="grid-controls">
                            <button type="button" class="btn btn-dark d-inline-flex align-items-center gap-1" id="btnShowLess" style="display:none">
                                <i class="material-icons-outlined" style="font-size:18px">expand_less</i>
                                {{ __('map.show_less') }}
                            </button>
                            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" id="btnLoadMore" style="display:none">
                                {{ __('map.load_more') }}
                                <i class="material-icons-outlined" style="font-size:18px">expand_more</i>
                            </button>
                        </div>
                        </div><!-- end map-section-grid -->
                    </div> <!-- end col left -->

                    <div class="col-lg-7 map-col-right">
                        <div class="buy-grid-map-item-04">
                            <div id="map" class="map-listing"></div>
                        </div>
                    </div> <!-- end col right -->
                </div>
                    <!-- end row -->

            </div>
        </div>
        <!-- End Content -->

    </div>

    <!-- ========================
        End Page Content
    ========================= -->

@php
$mapLocations = array_map(function($p) {
    return [
        'title'    => $p['title'] ?? '',
        'address'  => $p['fullAddress'] ?? ($p['city'] ?? ''),
        'city'     => $p['city'] ?? '',
        'district' => $p['district'] ?? '',
        'price'    => number_format($p['price'] ?? 0, 0) . ' ' . ($p['currency'] ?? ''),
        'image'    => $p['primaryImageUrl'] ?? '',
        'slug'     => $p['slug'] ?? '',
        'category' => $p['propertyType'] ?? '',
        'lat'      => $p['latitude'] ?? null,
        'lng'      => $p['longitude'] ?? null,
    ];
}, $properties['items'] ?? []);
@endphp

{{-- Global SVG gradient definitions for custom Yandex map markers --}}
<svg width="0" height="0" style="position:absolute;overflow:hidden">
    <defs>
        <radialGradient id="ya-pin-red" cx="35%" cy="22%" r="70%">
            <stop offset="0%"   stop-color="#ff5b5b"/>
            <stop offset="50%"  stop-color="#dd1111"/>
            <stop offset="100%" stop-color="#8b0000"/>
        </radialGradient>
        <radialGradient id="ya-pin-circle" cx="40%" cy="32%" r="70%">
            <stop offset="0%"   stop-color="#ffffff"/>
            <stop offset="75%"  stop-color="#e2e2e2"/>
            <stop offset="100%" stop-color="#c0c0c0"/>
        </radialGradient>
    </defs>
</svg>

<script>
    window.apiPropertyLocations = @json($mapLocations);
    window.propertyBaseUrl = '/{{ app()->getLocale() }}/property/';

    document.addEventListener('DOMContentLoaded', function () {

        // ── Custom select dropdowns ─────────────────────────────────────
        if (typeof initCustomSelects === 'function') initCustomSelects();

        // ── Reveal real grid, hide skeleton ────────────────────────────────
        var skeletonGrid = document.getElementById('prop-grid-skeleton');
        var realGrid     = document.getElementById('prop-grid');
        if (realGrid) {
            // Hide all cards initially — filterCardsByBounds will show the right ones
            Array.prototype.forEach.call(realGrid.children, function(c) {
                c.classList.add('map-card-hidden');
            });
        }
        if (skeletonGrid) skeletonGrid.style.display = 'none';
        if (realGrid)    { realGrid.style.display = ''; requestAnimationFrame(function() { realGrid.style.opacity = '1'; }); }

        // Filter panel toggle (persist state in localStorage)
        var btn   = document.getElementById('advFilterToggle');
        var panel = document.getElementById('advFilterPanel');
        if (btn && panel) {
            var filterOpen = localStorage.getItem('filterOpen') !== 'false';
            if (filterOpen) {
                btn.classList.add('open');
                panel.classList.add('open');
            }
            btn.addEventListener('click', function () {
                var isOpen = btn.classList.toggle('open');
                panel.classList.toggle('open');
                localStorage.setItem('filterOpen', isOpen ? 'true' : 'false');
            });
        }

        // Load More / Show Less — integrated with map bounds filter
        (function () {
            var btnMore = document.getElementById('btnLoadMore');
            var btnLess = document.getElementById('btnShowLess');
            if (!btnMore || !btnLess) return;

            btnMore.addEventListener('click', function () {
                if (typeof gridLimit !== 'undefined' && typeof GRID_STEP !== 'undefined') {
                    gridLimit += GRID_STEP;
                    if (map) filterCardsByBounds(map.getBounds());
                }
            });

            btnLess.addEventListener('click', function () {
                if (typeof gridLimit !== 'undefined' && typeof GRID_PAGE_SIZE !== 'undefined') {
                    gridLimit = GRID_PAGE_SIZE;
                    if (map) filterCardsByBounds(map.getBounds());
                    var grid = document.getElementById('prop-grid');
                    if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        })();
    });

    // ── City autocomplete: Nominatim + Overpass nearby cities ────────────────
    (function () {
        var input    = document.getElementById('cityInputMap');
        var list     = document.getElementById('citySuggestionsMap');
        var clearBtn = document.getElementById('cityClearBtnMap');
        var spinner  = document.getElementById('citySpinnerMap');
        if (!input || !list) return;

        // Portal: move list to <body> so no ancestor transform/overflow can misplace it
        document.body.appendChild(list);

        var timer = null;
        var lang  = '{{ app()->getLocale() }}';
        var shown = {};
        var districtAutoFilled = false;

        (function () {
            var d = document.querySelector('[name="district"]');
            if (d) d.addEventListener('input', function () { districtAutoFilled = false; });
        }());

        function makeLi(name, desc) {
            var li = document.createElement('li');
            li.innerHTML = '<i class="material-icons-outlined city-item-icon">location_on</i>'
                + '<span class="city-item-text">'
                +   '<span class="city-item-name">' + name + '</span>'
                +   (desc ? '<span class="city-item-desc">' + desc + '</span>' : '')
                + '</span>';
            li.addEventListener('mousedown', function (e) {
                if (e.cancelable) e.preventDefault();
                input.value = name;
                if (clearBtn) clearBtn.style.display = 'flex';
                list.style.display = 'none';
                list.innerHTML = '';
                shown = {};
                // Pan map to selected city
                if (typeof window.panMapToLocation === 'function') {
                    window.panMapToLocation(name);
                }
                // Show spinner while district is loading
                if (clearBtn) clearBtn.style.display = 'none';
                if (spinner)  spinner.style.display  = 'block';
                var districtEl = document.querySelector('[name="district"]');
                if (districtEl) districtEl.value = '';
                fetch('/api/central-district?city=' + encodeURIComponent(name) + '&lang=' + encodeURIComponent(lang))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.district && districtEl) {
                            districtEl.value = data.district;
                            districtAutoFilled = true;
                        }
                    })
                    .catch(function () {})
                    .finally(function () {
                        if (spinner)  spinner.style.display  = 'none';
                        if (clearBtn) clearBtn.style.display = 'flex';
                    });
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
                list.appendChild(makeLi(it.name, it.desc));
            });
            list.style.display = 'block';
        }

        function appendSuggestions(items) {
            items.forEach(function (it) {
                if (shown[it.name]) return;
                shown[it.name] = true;
                list.appendChild(makeLi(it.name, it.desc));
            });
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
                fetch('https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&lang=' + yLang + '&results=7&highlight=0&v=9')
                    .then(function (r) { return r.text(); })
                    .then(function (body) {
                        var m = body.match(/^suggest\.apply\((.+)\)$/);
                        var data = m ? JSON.parse(m[1]) : {};
                        var results = (data.results || []).map(function (item) {
                            var title = (item.title || {}).text || '';
                            var where = ((item.log_id || {}).where) || {};
                            if (!title || title !== (where.title || '')) return null;
                            var parts = (where.name || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
                            var desc = parts.filter(function (p) { return p !== title; }).join(', ');
                            return { name: title, desc: desc };
                        }).filter(Boolean);
                        showSuggestions(results);
                    })
                    .catch(function () { list.style.display = 'none'; });
            }, 150);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                clearBtn.style.display = 'none';
                list.style.display = 'none';
                list.innerHTML = '';
                shown = {};
                if (districtAutoFilled) {
                    var districtEl = document.querySelector('[name="district"]');
                    if (districtEl) districtEl.value = '';
                    districtAutoFilled = false;
                }
                input.focus();
            });
        }

        input.addEventListener('blur', function () {
            setTimeout(function () { list.style.display = 'none'; }, 200);
        });

        // Pan map when district field changes (on blur/Enter)
        var districtPanEl = document.querySelector('[name="district"]');
        if (districtPanEl) {
            districtPanEl.addEventListener('change', function () {
                var city     = input.value.trim();
                var district = this.value.trim();
                if (!district && !city) return;
                var query = district ? (city ? district + ', ' + city : district) : city;
                if (typeof window.panMapToLocation === 'function') {
                    window.panMapToLocation(query);
                }
            });
        }

        updateClearBtn();
    }());
</script>

@endsection
