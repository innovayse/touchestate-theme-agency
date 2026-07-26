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

                <!-- Row 1: Filter -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="map-section-filter">
                        @include('partials.advanced-filter', [
                            'filterAction' => '/'.app()->getLocale().'/map',
                            'filterPage'   => 'map',
                        ])
                        </div>
                    </div>
                </div>

                <!-- Row 2: Grid + Map -->
                <div class="row map-page-row">

                    <!-- Left: scrollable property grid -->
                    <div class="col-lg-5 map-col-left">
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

                        <!-- Property cards -->
                        <div class="row mb-4" id="prop-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                            @foreach($properties['items'] ?? [] as $prop)
                                <x-property-card :prop="$prop" col="col-xl-6" />
                            @endforeach
                        </div>

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
                    </div><!-- end col left -->

                    <!-- Right: natural-sticky map (JS-driven) -->
                    <div class="col-lg-7 map-col-right" style="align-self:flex-start;">
                        <div class="buy-grid-map-item-04" style="position:relative;">
                            <div id="map" class="map-listing" style="height:100%;"></div>
                            <!-- Property card overlay on marker click -->
                            <div id="map-card-overlay" style="display:none;position:absolute;bottom:16px;left:50%;transform:translateX(-50%);z-index:500;pointer-events:auto;"></div>
                        </div>
                    </div><!-- end col right -->

                </div><!-- end row map-page-row -->

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
        'address'  => localized_address($p),   // shown in the balloon (localized)
        'city'     => $p['city'] ?? '',         // English — used for marker geocoding, do NOT localize
        'district' => $p['district'] ?? '',
        'price'         => format_price($p['price'] ?? 0, $p['currency'] ?? null),
        'priceAmount'   => (float) ($p['price'] ?? 0),
        'priceCurrency' => ($p['currency'] ?? null) ?: display_currency(),
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

        // Reset button
        var btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                window.location.href = '/{{ app()->getLocale() }}/map';
            });
        }
    });


    // ── Pan map when district field changes ────────────────────────────────
    (function () {
        var districtPanEl = document.querySelector('[name="district"]');
        if (districtPanEl) {
            districtPanEl.addEventListener('change', function () {
                var cityEl   = document.getElementById('cityInput');
                var city     = cityEl ? cityEl.value.trim() : '';
                var district = this.value.trim();
                if (!district && !city) return;
                var query = district ? (city ? district + ', ' + city : district) : city;
                if (typeof window.panMapToLocation === 'function') {
                    window.panMapToLocation(query);
                }
            });
        }
    }());

</script>

@endsection
