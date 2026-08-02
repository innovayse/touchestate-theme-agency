<?php $page = 'property'; ?>
@section('title')
    {{ __('property.title') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    {{-- On mobile the listing defaults to the map view (filters carried over).
         location.replace → no history entry, so no back-button loop. --}}
    <script>
        (function () {
            if (window.matchMedia && window.matchMedia('(max-width: 991px)').matches) {
                window.location.replace('/{{ app()->getLocale() }}/map' + window.location.search);
            }
        })();
    </script>

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('property.title') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('property.title') }}
            @endslot
        @endcomponent

        <div class="content">
            <div class="container">

                <!-- Advanced Filter -->
                @include('partials.advanced-filter', [
                    'filterAction' => '/'.app()->getLocale().'/property',
                    'filterPage'   => 'property',
                ])

                <!-- Skeleton Grid (shown while page loads) -->
                <div id="prop-grid-skeleton" class="row mb-4">
                    @for($i = 0; $i < 6; $i++)
                    <div class="col-lg-4 col-md-6 d-flex">
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

                <!-- Property Grid (hidden until DOM ready) -->
                <div class="row mb-4" id="prop-grid" data-has-next="{{ ($properties['hasNextPage'] ?? false) ? '1' : '0' }}" style="display:none;opacity:0;transition:opacity 0.35s ease">
                    @include('partials.property-cards', ['properties' => $properties])
                </div>

                <!-- Numbered pagination -->
                <nav id="listPagination" class="tp-pagination" aria-label="Pagination"></nav>

            </div>
        </div>

    </div>

<script>
    // ── Numbered pagination (AJAX loads the chosen page and replaces the grid) ──
    var PAGE_SIZE = 21;
    var curPage = (function () {
        var p = parseInt(new URLSearchParams(window.location.search).get('page'), 10);
        return p > 0 ? p : 1;
    })();

    function pgTotal() {
        var t = document.getElementById('result-total');
        return t ? (parseInt(t.textContent, 10) || 0) : 0;
    }
    function pgEndpoint() {
        return window.location.pathname.replace(/\/+$/, '') + '/load-more';
    }
    function drawPager() {
        var box = document.getElementById('listPagination');
        if (box && typeof window.renderPagination === 'function') {
            window.renderPagination(box, curPage, Math.ceil(pgTotal() / PAGE_SIZE), goToPage);
        }
    }
    function goToPage(n) {
        var grid = document.getElementById('prop-grid');
        if (!grid) return;
        var params = new URLSearchParams(window.location.search);
        params.set('page', String(n));
        grid.style.opacity = '0.5';
        fetch(pgEndpoint() + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                grid.innerHTML = data.html || '';
                grid.style.opacity = '1';
                curPage = n;
                var rl = document.getElementById('result-loaded'); if (rl) rl.textContent = data.count;
                var rt = document.getElementById('result-total');  if (rt) rt.textContent = data.total;
                history.pushState(null, '', window.location.pathname + '?' + params.toString());
                drawPager();
                var top = document.getElementById('prop-grid');
                if (top) window.scrollTo({ top: top.getBoundingClientRect().top + window.pageYOffset - 90, behavior: 'smooth' });
            })
            .catch(function () { grid.style.opacity = '1'; });
    }

    document.addEventListener('DOMContentLoaded', function () {

        // ── Persist filters in sessionStorage ──────────────────────────────
        (function () {
            var form = document.getElementById('filterForm');
            if (!form) return;
            var key = 'propertyFilters';

            if (!window.location.search) {
                var saved = sessionStorage.getItem(key);
                if (saved) {
                    var params = new URLSearchParams(saved);
                    params.forEach(function (val, name) {
                        var el = form.querySelector('[name="' + name + '"]');
                        if (el) el.value = val;
                    });
                    // Update filter badge count
                    var count = 0;
                    params.forEach(function (val, name) {
                        if (val && name !== 'search' && name !== 'page') count++;
                    });
                    if (count > 0) {
                        var toggle = document.getElementById('advFilterToggle');
                        if (toggle && !toggle.querySelector('.filter-badge')) {
                            var badge = document.createElement('span');
                            badge.className = 'filter-badge';
                            badge.textContent = count;
                            toggle.insertBefore(badge, toggle.querySelector('.arrow'));
                        }
                    }
                }
            }

            function saveFilters() {
                var data = new URLSearchParams(new FormData(form)).toString();
                sessionStorage.setItem(key, '?' + data);
            }
            form.addEventListener('input', saveFilters);
            form.addEventListener('change', saveFilters);

            if (window.location.search) {
                sessionStorage.setItem(key, window.location.search);
            }
        }());

        // ── Custom select dropdowns (after session restore) ─────────────
        if (typeof initCustomSelects === 'function') initCustomSelects();

        // ── Reveal real grid, hide skeleton ────────────────────────────────
        var skeletonGrid = document.getElementById('prop-grid-skeleton');
        var realGrid     = document.getElementById('prop-grid');
        if (skeletonGrid) skeletonGrid.style.display = 'none';
        if (realGrid)    { realGrid.style.display = ''; requestAnimationFrame(function() { realGrid.style.opacity = '1'; }); }

        // Pagination
        drawPager();

        // ── AJAX filter submit ──────────────────────────────────────────────
        var filterFormEl = document.getElementById('filterForm');
        if (filterFormEl) {
            filterFormEl.addEventListener('submit', function (e) {
                e.preventDefault();

                var cityDisplay = document.getElementById('cityInput');
                var cityHidden  = document.getElementById('cityHidden');
                var filterLang  = '{{ app()->getLocale() }}';

                function doSubmit() {
                    var params = new URLSearchParams(new FormData(filterFormEl)).toString();
                    var url = filterFormEl.action + '?' + params;

                    var skeleton = document.getElementById('prop-grid-skeleton');
                    var grid = document.getElementById('prop-grid');
                    if (skeleton) skeleton.style.display = '';
                    if (grid) { grid.style.display = 'none'; grid.style.opacity = '0'; }

                    history.pushState(null, '', url);
                    sessionStorage.setItem('propertyFilters', '?' + params);

                    fetch(url)
                        .then(function (r) { return r.text(); })
                        .then(function (html) {
                            var doc = new DOMParser().parseFromString(html, 'text/html');

                            var newGrid = doc.getElementById('prop-grid');
                            if (grid && newGrid) {
                                grid.innerHTML = newGrid.innerHTML;
                                grid.dataset.hasNext = newGrid.dataset.hasNext || '0';
                            }

                            var newLoaded = doc.getElementById('result-loaded');
                            var newTotal  = doc.getElementById('result-total');
                            if (newLoaded) document.getElementById('result-loaded').textContent = newLoaded.textContent;
                            if (newTotal)  document.getElementById('result-total').textContent  = newTotal.textContent;

                            if (skeleton) skeleton.style.display = 'none';
                            if (grid) {
                                grid.style.display = '';
                                requestAnimationFrame(function () { grid.style.opacity = '1'; });
                            }

                            curPage = 1; drawPager();
                        })
                        .catch(function () {
                            filterFormEl.submit();
                        });
                }

                // Гарантируем English название города перед отправкой
                var q = cityDisplay ? cityDisplay.value.trim() : '';
                if (q && filterLang !== 'en' && cityHidden && (!cityHidden.value || cityHidden.value === q)) {
                    fetch('https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&lang=en_US&results=1&highlight=0&v=9')
                        .then(function (r) { return r.text(); })
                        .then(function (body) {
                            var m = body.trim().match(/suggest\.apply\(([\s\S]+)\)/);
                            var data = m ? JSON.parse(m[1]) : {};
                            var first = (data.results || [])[0];
                            cityHidden.value = first ? ((first.title || {}).text || q) : q;
                        })
                        .catch(function () { if (cityHidden) cityHidden.value = q; })
                        .finally(doSubmit);
                } else {
                    doSubmit();
                }
            });
        }

        // ── Reset button (no reload) ──────────────────────────────────────
        var btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                var form = document.getElementById('filterForm');
                if (!form) return;
                form.reset();
                form.querySelectorAll('input[type="text"], input[type="number"]').forEach(function (el) { el.value = ''; });
                form.querySelectorAll('input[type="checkbox"]').forEach(function (el) { el.checked = false; });
                form.querySelectorAll('select').forEach(function (el) { el.selectedIndex = 0; el.dispatchEvent(new Event('change', { bubbles: true })); });
                sessionStorage.removeItem('propertyFilters');
                var badge = document.querySelector('.filter-badge');
                if (badge) badge.remove();
                history.replaceState(null, '', window.location.pathname);
            });
        }
    });

</script>

@endsection
