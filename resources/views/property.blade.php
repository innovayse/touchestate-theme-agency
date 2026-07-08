<?php $page = 'property'; ?>
@section('title')
    {{ __('property.title') }}
@endsection

@extends('layout.mainlayout')
@section('content')

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

                <!-- Load More / Show Less -->
                <div class="text-center mb-4 align-items-center justify-content-center gap-3" id="grid-controls">
                    <button type="button" class="btn btn-dark d-inline-flex align-items-center gap-1" id="btnShowLess" style="display:none!important; border-radius:10px">
                        <i class="material-icons-outlined" style="font-size:18px">expand_less</i>
                        {{ __('property.show_less') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" id="btnLoadMore" style="display:none!important; border-radius:10px">
                        {{ __('property.load_more') }}
                        <i class="material-icons-outlined" style="font-size:18px">expand_more</i>
                    </button>
                </div>

            </div>
        </div>

    </div>

<script>
    // ── Load More / Show Less — real server-side pagination ─────────────────
    // Each "Load More" fetches the next page from the server (same filters, +1 page)
    // and appends the rendered cards. "Show Less" collapses back to the first page.
    var LM = { page: 1, hasNext: false, initialCount: 0, shown: 0, loading: false };

    function lmEndpoint() {
        return window.location.pathname.replace(/\/+$/, '') + '/load-more';
    }

    // Toggle a Bootstrap `d-inline-flex` button. Its display utility is `!important`,
    // so a plain inline `display` can't hide it — force `none !important`, and reveal
    // by removing the inline rule so the class takes over.
    function lmSetVisible(el, visible) {
        if (!el) return;
        if (visible) el.style.removeProperty('display');
        else el.style.setProperty('display', 'none', 'important');
    }

    function lmSyncButtons() {
        lmSetVisible(document.getElementById('btnLoadMore'), LM.hasNext);
        lmSetVisible(document.getElementById('btnShowLess'), LM.page > 1);
    }

    // (Re)initialise pagination state from the current DOM. Called on first load and
    // after a filter swap replaces the grid.
    function reinitLoadMore() {
        var grid = document.getElementById('prop-grid');
        var resultLoaded = document.getElementById('result-loaded');
        if (!grid) return;
        LM.page = 1;
        LM.hasNext = grid.dataset.hasNext === '1';
        LM.initialCount = grid.children.length;
        LM.shown = LM.initialCount;
        LM.loading = false;
        if (resultLoaded) resultLoaded.textContent = LM.shown;
        lmSyncButtons();
    }

    function lmLoadMore() {
        var grid = document.getElementById('prop-grid');
        var btnMore = document.getElementById('btnLoadMore');
        var resultLoaded = document.getElementById('result-loaded');
        if (!grid || !btnMore || LM.loading || !LM.hasNext) return;

        LM.loading = true;
        btnMore.disabled = true;
        btnMore.style.opacity = '0.6';

        var params = new URLSearchParams(window.location.search);
        params.set('page', String(LM.page + 1));

        fetch(lmEndpoint() + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var tmp = document.createElement('div');
                tmp.innerHTML = data.html;
                var added = Array.prototype.slice.call(tmp.children);
                var firstNewIndex = grid.children.length;

                added.forEach(function (el, i) {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(20px)';
                    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    grid.appendChild(el);
                    (function (node, delay) {
                        setTimeout(function () { node.style.opacity = '1'; node.style.transform = 'translateY(0)'; }, delay);
                    })(el, i * 60);
                });

                LM.page += 1;
                LM.hasNext = !!data.hasNextPage;
                LM.shown += added.length;
                if (resultLoaded) resultLoaded.textContent = LM.shown;
                lmSyncButtons();

                var anchor = grid.children[firstNewIndex];
                if (anchor) setTimeout(function () { anchor.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 120);
            })
            .catch(function () { /* leave button enabled so the user can retry */ })
            .finally(function () {
                LM.loading = false;
                btnMore.disabled = false;
                btnMore.style.opacity = '';
            });
    }

    function lmShowLess() {
        var grid = document.getElementById('prop-grid');
        var resultLoaded = document.getElementById('result-loaded');
        if (!grid) return;
        while (grid.children.length > LM.initialCount) {
            grid.removeChild(grid.lastElementChild);
        }
        LM.page = 1;
        LM.hasNext = grid.dataset.hasNext === '1';
        LM.shown = LM.initialCount;
        if (resultLoaded) resultLoaded.textContent = LM.shown;
        lmSyncButtons();
        if (grid.firstElementChild) grid.firstElementChild.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Event delegation — survives grid/button swaps without re-binding.
    document.addEventListener('click', function (e) {
        if (!e.target.closest) return;
        if (e.target.closest('#btnLoadMore')) { e.preventDefault(); lmLoadMore(); }
        else if (e.target.closest('#btnShowLess')) { e.preventDefault(); lmShowLess(); }
    });

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

        // Load More / Show Less
        reinitLoadMore();

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

                            reinitLoadMore();
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
