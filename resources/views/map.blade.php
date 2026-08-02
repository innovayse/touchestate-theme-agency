<?php $page = 'map'; ?>
@section('title')
    Map View
@endsection

@extends('layout.mainlayout')
@section('content')

@php($locale = app()->getLocale())

    <!-- ========================
        Start Map Page (full-screen)
    ========================= -->
    <div class="page-wrapper map-fullscreen">
        <div class="map-page" id="mapPage">

            {{-- ── Cards container: side panel on desktop / bottom-sheet on mobile ── --}}
            <aside class="map-panel" id="mapPanel">
                <div class="map-panel-handle" id="sheetHandle" aria-hidden="true"><span></span></div>

                <div class="map-panel-head">
                    <button type="button" class="map-filter-btn" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <x-icon name="layers" size="18" />
                        <span>{{ __('map.filter') }}</span>
                    </button>
                    <span class="map-count"><strong id="map-count-num">0</strong> {{ __('map.result') }}</span>
                </div>

                <div class="map-panel-body" id="mapPanelBody">
                    {{-- Skeleton (shown while cards load) --}}
                    <div id="prop-grid-skeleton" class="row g-3 mb-2">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="col-12 d-flex">
                                <div class="property-card flex-fill skeleton-card">
                                    <div class="property-listing-item p-0 mb-0 shadow-none">
                                        <div class="buy-grid-img mb-0 rounded-0" style="overflow:hidden">
                                            <span class="skeleton-block" style="width:100%;height:180px;border-radius:0"></span>
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
                                            <span class="skeleton-block mb-3" style="width:100%;height:44px;border-radius:8px"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    {{-- Property cards — injected via AJAX (GET /map/cards) --}}
                    <div class="row g-3" id="prop-grid" style="display:none;opacity:0;transition:opacity .35s ease"></div>

                    {{-- Empty state (no properties in the current map view) --}}
                    <div id="map-empty" class="map-empty" style="display:none">
                        <x-icon name="search" size="40" class="text-muted" />
                        <p class="mb-2">{{ __('map.no_results_in_area') }}</p>
                        <button type="button" class="btn btn-sm btn-primary" id="map-zoom-out-btn">{{ __('map.zoom_out') }}</button>
                    </div>

                    {{-- Numbered pagination (in-bounds cards) --}}
                    <nav id="mapPagination" class="tp-pagination" aria-label="Pagination"></nav>
                </div>
            </aside>

            {{-- ── Map canvas ── --}}
            <div class="map-canvas">
                <div id="map" class="map-listing"></div>

                {{-- Hover/tap mini-card tooltip; left/top set to the marker in JS --}}
                <div id="map-card-overlay" style="display:none;position:absolute;left:0;top:0;transform:translate(-50%,calc(-100% - 64px));z-index:500;pointer-events:auto;"></div>

                {{-- Floating filter button (mobile) --}}
                <button type="button" class="map-fab map-fab-filter" data-bs-toggle="modal" data-bs-target="#filterModal" aria-label="{{ __('map.filter') }}">
                    <x-icon name="layers" size="20" />
                </button>

                {{-- Map / List toggle (mobile) --}}
                <button type="button" class="map-fab map-list-toggle" id="mapListToggle">
                    <span class="mlt-list"><x-icon name="crop_square" size="18" /> {{ __('map.list_view') }}</span>
                    <span class="mlt-map"><x-icon name="map" size="18" /> {{ __('common.map') }}</span>
                </button>
            </div>
        </div>

        {{-- ── Filter + search modal ── --}}
        <div class="modal fade map-filter-modal" id="filterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('map.filter') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('partials.advanced-filter', [
                            'filterAction' => '/'.$locale.'/map',
                            'filterPage'   => 'map',
                            'properties'   => ['items' => [], 'totalCount' => 0],
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Map Page -->

{{-- Global SVG gradient definitions for custom Yandex map markers --}}
<svg width="0" height="0" style="position:absolute;overflow:hidden">
    <defs>
        <radialGradient id="ya-pin-red" cx="35%" cy="22%" r="70%">
            <stop offset="0%" stop-color="#ff5b5b" /><stop offset="50%" stop-color="#dd1111" /><stop offset="100%" stop-color="#8b0000" />
        </radialGradient>
        <radialGradient id="ya-pin-circle" cx="40%" cy="32%" r="70%">
            <stop offset="0%" stop-color="#ffffff" /><stop offset="75%" stop-color="#e2e2e2" /><stop offset="100%" stop-color="#c0c0c0" />
        </radialGradient>
    </defs>
</svg>

<script>
    window.propertyBaseUrl = '/{{ $locale }}/property/';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initCustomSelects === 'function') initCustomSelects();

        var skeletonGrid = document.getElementById('prop-grid-skeleton');
        var realGrid     = document.getElementById('prop-grid');
        var emptyState   = document.getElementById('map-empty');
        var localePrefix = '/{{ $locale }}';
        var qs           = window.location.search; // forward active filters to the AJAX endpoints
        var cardsReady = false, locationsReady = false, locationsData = [];

        function applyWhenReady() {
            if (cardsReady && locationsReady && typeof window.applyMapLocations === 'function') {
                window.applyMapLocations(locationsData);
            }
        }

        // 1) Cards (fast — list cached) → replace skeleton with the real grid.
        fetch(localePrefix + '/map/cards' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (realGrid) {
                    realGrid.innerHTML = data.html || '';
                    Array.prototype.forEach.call(realGrid.children, function (c) { c.classList.add('map-card-hidden'); });
                }
                if (skeletonGrid) skeletonGrid.style.display = 'none';
                if (realGrid) { realGrid.style.display = ''; requestAnimationFrame(function () { realGrid.style.opacity = '1'; }); }
                var total = data.count || 0;
                var rt = document.getElementById('result-total'); if (rt) rt.textContent = total;
                if (total === 0 && emptyState) emptyState.style.display = 'block';
                cardsReady = true;
                applyWhenReady();
            })
            .catch(function () { if (skeletonGrid) skeletonGrid.style.display = 'none'; });

        // 2) Marker coordinates (slow — enrichWithCoordinates) → placed when ready.
        fetch(localePrefix + '/map/locations' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { locationsData = data.locations || []; window.apiPropertyLocations = locationsData; locationsReady = true; applyWhenReady(); })
            .catch(function () { locationsReady = true; applyWhenReady(); });

        // Filter modal: header is z-index:10001, so lift the backdrop above it (proper dim).
        var filterModalEl = document.getElementById('filterModal');
        if (filterModalEl) filterModalEl.addEventListener('shown.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(function (bd) { bd.style.zIndex = '10040'; });
        });

        // Reset (inside the filter modal)
        var btnReset = document.getElementById('btnReset');
        if (btnReset) btnReset.addEventListener('click', function () { window.location.href = localePrefix + '/map'; });

        // Empty-state "zoom out"
        var zoomOutBtn = document.getElementById('map-zoom-out-btn');
        if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () { if (typeof window.mapZoomOut === 'function') window.mapZoomOut(); });

        // Card → marker highlight (hover a card, focus its pin) — desktop only
        if (realGrid && window.matchMedia('(min-width: 992px)').matches) {
            realGrid.addEventListener('mouseover', function (e) {
                var card = e.target.closest('[data-slug]');
                if (card && typeof window.mapHighlightSlug === 'function') window.mapHighlightSlug(card.dataset.slug);
            });
            realGrid.addEventListener('mouseout', function (e) {
                var card = e.target.closest('[data-slug]');
                if (card && !card.contains(e.relatedTarget) && typeof window.mapClearHighlight === 'function') window.mapClearHighlight();
            });
        }

        // ── Bottom-sheet (mobile) + Map/List toggle ──────────────────────────
        (function () {
            var panel  = document.getElementById('mapPanel');
            var handle = document.getElementById('sheetHandle');
            var toggle = document.getElementById('mapListToggle');
            if (!panel) return;

            // translateY as a % of the PANEL's own height (panel lives inside .map-page,
            // which already starts below the fixed header → never slides under the navbar).
            // peek: only handle + count visible (no cards) · half: horizontal ribbon · full: vertical list
            var SNAP = { peek: 92.5, half: 35, full: 0 };
            var current = 'peek';
            function isMobile() { return window.matchMedia('(max-width: 991px)').matches; }
            function applySnap(name, animate) {
                current = name;
                panel.style.transition = animate ? 'transform .32s cubic-bezier(.4,0,.2,1)' : 'none';
                panel.style.transform = 'translateY(' + SNAP[name] + '%)';
                panel.classList.toggle('is-peek', name === 'peek');
                panel.classList.toggle('is-half', name === 'half');
                panel.classList.toggle('is-full', name === 'full');
                if (toggle) toggle.classList.toggle('showing-map', name === 'peek');
            }
            if (isMobile()) applySnap('peek', false);
            window.addEventListener('resize', function () { if (!isMobile()) { panel.style.transform = ''; panel.classList.remove('is-peek', 'is-full'); } else if (!panel.style.transform) applySnap('peek', false); });

            // Drag
            var startY = 0, startPct = 0, dragging = false;
            function pct() { var m = /translateY\(([-0-9.]+)%\)/.exec(panel.style.transform); return m ? parseFloat(m[1]) : SNAP.peek; }
            function onDown(e) { if (!isMobile()) return; dragging = true; startY = (e.touches ? e.touches[0].clientY : e.clientY); startPct = pct(); panel.style.transition = 'none'; }
            function onMove(e) {
                if (!dragging) return;
                var y = (e.touches ? e.touches[0].clientY : e.clientY);
                var dPct = ((y - startY) / panel.offsetHeight) * 100;
                var f = Math.min(SNAP.peek, Math.max(SNAP.full, startPct + dPct));
                panel.style.transform = 'translateY(' + f + '%)';
            }
            function onUp() {
                if (!dragging) return; dragging = false;
                var f = pct();
                var best = 'peek', bd = 999;
                Object.keys(SNAP).forEach(function (k) { var d = Math.abs(SNAP[k] - f); if (d < bd) { bd = d; best = k; } });
                applySnap(best, true);
            }
            if (handle) { handle.addEventListener('mousedown', onDown); handle.addEventListener('touchstart', onDown, { passive: true }); }
            document.addEventListener('mousemove', onMove); document.addEventListener('touchmove', onMove, { passive: true });
            document.addEventListener('mouseup', onUp); document.addEventListener('touchend', onUp);

            if (toggle) toggle.addEventListener('click', function () { applySnap(current === 'peek' ? 'full' : 'peek', true); });
        }());
    });

    // Pan the map when the district field changes (inside the modal)
    (function () {
        var el = document.querySelector('[name="district"]');
        if (!el) return;
        el.addEventListener('change', function () {
            var city = (document.getElementById('cityInput') || {}).value || '';
            var district = this.value.trim();
            if (!district && !city.trim()) return;
            var query = district ? (city ? district + ', ' + city : district) : city;
            if (typeof window.panMapToLocation === 'function') window.panMapToLocation(query);
        });
    }());
</script>

@endsection
