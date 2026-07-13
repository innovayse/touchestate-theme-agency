<?php $page = 'favorites'; ?>
@section('title')
    {{ __('header.favorites') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('header.favorites') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('header.favorites') }}
            @endslot
        @endcomponent

        <div class="content">
            <div class="container">

                <!-- Result counter -->
                <div class="advanced-filter mb-4">
                    <p class="filter-result mb-0">
                        {{ __('header.favorites') }}: <span class="result-value" id="result-loaded">0</span>
                    </p>
                </div>

                <!-- Empty state -->
                <div id="favorites-empty" class="text-center py-5" style="display:none">
                    <i class="material-icons-outlined" style="font-size:72px;color:#ccc">favorite_border</i>
                    <h5 class="mt-3 text-muted">{{ __('header.favorites_empty') }}</h5>
                    <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary mt-3">
                        {{ __('property.title') }}
                    </a>
                </div>

                <!-- Load error state (favorites exist in storage but couldn't be fetched) -->
                <div id="favorites-error" class="text-center py-5" style="display:none">
                    <i class="material-icons-outlined" style="font-size:72px;color:#ccc">error_outline</i>
                    <h5 class="mt-3 text-muted">{{ __('header.favorites_error') }}</h5>
                    <button type="button" id="favorites-retry" class="btn btn-primary mt-3">
                        {{ __('header.favorites_retry') }}
                    </button>
                </div>

                <!-- Skeleton (shown while loading) -->
                <div class="row mb-4" id="prop-grid-skeleton">
                    @for($i = 0; $i < 6; $i++)
                    <div class="col-xl-4 col-lg-6 col-md-6 d-flex">
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

                <!-- Property Grid (filled by AJAX) -->
                <div class="row mb-4" id="prop-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                </div>

            </div>
        </div>

    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var STORAGE_KEY = 'te_favorites';
        var skeletonGrid = document.getElementById('prop-grid-skeleton');
        var grid         = document.getElementById('prop-grid');
        var emptyState   = document.getElementById('favorites-empty');
        var errorState   = document.getElementById('favorites-error');
        var counter      = document.getElementById('result-loaded');

        var loaded = 0; // cards currently shown

        function getFavs() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
            catch (e) { return []; }
        }

        // Retry simply reloads — re-runs the whole load flow (cache is warmer by then).
        var retryBtn = document.getElementById('favorites-retry');
        if (retryBtn) retryBtn.addEventListener('click', function () { window.location.reload(); });

        // Terminal state when nothing rendered: if favorites still exist in storage we just
        // couldn't load them (error + retry) — NOT the "no favorites" empty state, which
        // would contradict the header badge count.
        function showEmptyOrError() {
            if (grid) grid.style.display = 'none';
            var el = getFavs().length > 0 ? errorState : emptyState;
            if (el) el.style.display = 'block';
        }

        // Sync localStorage without clobbering favorites added while the request was in
        // flight: keep every slug the server kept, plus any slug we did NOT send (added
        // meanwhile). Drop only slugs we sent that the server didn't return (genuine 404/
        // ghost). data.slugs includes transient `pending`, so those survive.
        function syncKeptFavs(sent, kept) {
            var keptSet = {};
            (kept || []).forEach(function (s) { keptSet[s] = true; });
            var next = getFavs().filter(function (s) {
                return keptSet[s] || sent.indexOf(s) === -1;
            });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
            if (typeof window.updateHeaderFav === 'function') window.updateHeaderFav();
        }

        var favs = getFavs();

        if (favs.length === 0) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        // Un-favoriting on this page: fade the card out and hide it. Attached once (outside
        // the load callbacks) so the transient-retry re-load can't stack duplicate handlers.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.favourite');
            if (!btn) return;
            setTimeout(function () {
                var card = btn.closest('[data-slug]');
                if (!card || !grid) return;
                if (getFavs().indexOf(card.dataset.slug) === -1) {
                    card.style.transition = 'opacity 0.3s';
                    card.style.opacity = '0';
                    setTimeout(function () {
                        card.classList.add('d-none');
                        card.style.opacity = '';
                        card.style.transition = '';
                        var visible = Array.from(grid.querySelectorAll(':scope > [data-slug]'))
                            .filter(function (c) { return !c.classList.contains('d-none'); }).length;
                        loaded = visible;
                        if (counter) counter.textContent = visible;
                        if (visible === 0) {
                            grid.style.display = 'none';
                            if (emptyState) emptyState.style.display = 'block';
                        }
                    }, 300);
                }
            }, 50);
        });

        function requestFavs(slugList) {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            return fetch('/{{ app()->getLocale() }}/favorites/load', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                },
                body: JSON.stringify({ slugs: slugList })
            }).then(function (res) { return res.json(); });
        }

        function appendCards(html) {
            if (!html || !grid) return;
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            while (tmp.firstElementChild) grid.appendChild(tmp.firstElementChild);
        }

        function showCards(count) {
            loaded += count;
            if (counter) counter.textContent = loaded;
            if (grid && loaded > 0) {
                grid.style.display = '';
                if (emptyState) emptyState.style.display = 'none';
                requestAnimationFrame(function () { grid.style.opacity = '1'; });
            }
            if (errorState) errorState.style.display = 'none';
            if (typeof window.applyFavIcons === 'function') window.applyFavIcons();
            // Cards also carry a compare button — reflect its pressed state for slugs already
            // in te_compare (these cards are inserted after DOMContentLoaded, so the initial
            // applyCompareIcons() pass never saw them).
            if (typeof window.applyCompareIcons === 'function') window.applyCompareIcons();
        }

        // Initial load
        requestFavs(favs).then(function (data) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';

            // Sync localStorage: drop only slugs the server didn't keep; preserve anything
            // added during the request and the transient `pending` ones (still in data.slugs).
            if (data.slugs !== undefined) {
                syncKeptFavs(favs, data.slugs);
            }

            var pending = data.pending || [];

            if (data.count === 0 && pending.length === 0) {
                showEmptyOrError();
                return;
            }

            appendCards(data.html);
            showCards(data.count || 0);

            // Some slugs failed transiently (rate limit / 5xx / network) and were kept in
            // favorites but NOT rendered. Re-request just those once, after a short pause, so
            // the cards appear instead of silently going missing while still counted.
            if (pending.length) {
                setTimeout(function () {
                    requestFavs(pending).then(function (retry) {
                        if (retry && retry.count) {
                            appendCards(retry.html);
                            showCards(retry.count);
                        }
                    }).catch(function () {}).then(function () {
                        if (loaded === 0) showEmptyOrError();
                    });
                }, 800);
            }
        }).catch(function () {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            showEmptyOrError();
        });
    });
</script>

@endsection
