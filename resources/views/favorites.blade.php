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
        var counter      = document.getElementById('result-loaded');

        function getFavs() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
            catch (e) { return []; }
        }

        var favs = getFavs();

        if (favs.length === 0) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        fetch('/{{ app()->getLocale() }}/favorites/load', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
            },
            body: JSON.stringify({ slugs: favs })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';

            // Always sync localStorage with what API actually found (removes deleted/404 slugs)
            if (data.slugs !== undefined) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data.slugs));
                if (typeof window.updateHeaderFav === 'function') window.updateHeaderFav();
            }

            if (data.count === 0) {
                if (emptyState) emptyState.style.display = 'block';
                return;
            }

            if (counter) counter.textContent = data.count;
            if (grid) {
                grid.innerHTML = data.html;
                grid.style.display = '';
                requestAnimationFrame(function () { grid.style.opacity = '1'; });
            }

            if (typeof window.applyFavIcons === 'function') window.applyFavIcons();

            // When un-favoriting on this page — fade out and hide the card
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
                            if (counter) counter.textContent = visible;
                            if (visible === 0) {
                                grid.style.display = 'none';
                                if (emptyState) emptyState.style.display = 'block';
                            }
                        }, 300);
                    }
                }, 50);
            });
        })
        .catch(function () {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
        });
    });
</script>

@endsection
