<?php $page = 'agent-grid'; ?>
@section('title')
    {{ __('agent.title') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('agent.title') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('agent.title') }}
            @endslot
        @endcomponent

        <div class="content overflow-hidden">
            <div class="container">

                <!-- Search + Result -->
                <div class="advanced-filter">
                    <p class="filter-result mb-3">{{ __('map.result') }} <span class="result-value" id="result-loaded">{{ count($agents) }}</span> / <span class="result-value" id="result-total">{{ count($agents) }}</span></p>

                    <form method="GET" action="/{{ app()->getLocale() }}/agent" class="filter-search-row">
                        <div class="filter-search-input fsr-search">
                            <i class="material-icons-outlined">search</i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('agent.search_placeholder') }}">
                        </div>
                        <button type="submit" class="btn-filter-search fsr-submit">
                            <i class="material-icons-outlined">search</i>
                            {{ __('map.search') }}
                        </button>
                        @if(request('search'))
                        <button type="button" class="btn-reset" onclick="window.location.href='/{{ app()->getLocale() }}/agent'">{{ __('map.reset') }}</button>
                        @endif
                    </form>
                </div>

                <!-- Skeleton Grid -->
                <div id="agent-grid-skeleton" class="row mb-4">
                    @for($i = 0; $i < 8; $i++)
                    <div class="col-xl-3 col-lg-4 col-md-6 d-flex">
                        <div class="agent-item flex-fill skeleton-card">
                            <div class="agent-img" style="overflow:hidden">
                                <span class="skeleton-block" style="width:100%;height:220px;border-radius:0"></span>
                            </div>
                            <div class="agent-content">
                                <span class="skeleton-block mx-auto mb-2" style="width:60%;height:20px"></span>
                                <span class="skeleton-block mx-auto mb-1" style="width:40%;height:14px"></span>
                                <span class="skeleton-block mx-auto" style="width:50%;height:14px"></span>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- Agent Grid -->
                <div class="row mb-4" id="agent-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                    @forelse($agents as $agent)
                        @include('partials.agent-card', ['agent' => $agent])
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="material-icons-outlined text-muted" style="font-size:48px">person_off</i>
                            <p class="text-muted mt-2 fs-16">{{ __('agent.no_agents') }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Load More / Show Less -->
                <div class="text-center mb-4 d-flex align-items-center justify-content-center gap-3" id="grid-controls">
                    <button type="button" class="btn btn-dark d-inline-flex align-items-center gap-1" id="btnShowLess" style="display:none">
                        <i class="material-icons-outlined" style="font-size:18px">expand_less</i>
                        {{ __('property.show_less') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" id="btnLoadMore" style="display:none">
                        {{ __('agent.load_more') }}
                        <i class="material-icons-outlined" style="font-size:18px">expand_more</i>
                    </button>
                </div>

            </div>
        </div>

    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Reveal real grid, hide skeleton
        var skeletonGrid = document.getElementById('agent-grid-skeleton');
        var realGrid     = document.getElementById('agent-grid');
        if (skeletonGrid) skeletonGrid.style.display = 'none';
        if (realGrid)    { realGrid.style.display = ''; requestAnimationFrame(function() { realGrid.style.opacity = '1'; }); }

        // Load More / Show Less
        (function () {
            var grid = document.getElementById('agent-grid');
            var btnMore = document.getElementById('btnLoadMore');
            var btnLess = document.getElementById('btnShowLess');
            var resultLoaded = document.getElementById('result-loaded');
            if (!grid || !btnMore || !btnLess) return;

            var cards = grid.querySelectorAll('.col-xl-3');
            if (!cards.length) return;

            var INITIAL = 20;
            var STEP = 10;
            var visible = Math.min(INITIAL, cards.length);

            if (cards.length <= INITIAL) {
                if (resultLoaded) resultLoaded.textContent = cards.length;
                return;
            }

            function update() {
                for (var i = 0; i < cards.length; i++) {
                    if (i < visible) {
                        cards[i].classList.remove('d-none');
                    } else {
                        cards[i].classList.add('d-none');
                    }
                }
                btnMore.style.display = visible < cards.length ? '' : 'none';
                btnLess.style.display = visible > INITIAL ? '' : 'none';
                if (resultLoaded) resultLoaded.textContent = visible;
            }

            btnMore.addEventListener('click', function () {
                var prevVisible = visible;
                visible = Math.min(visible + STEP, cards.length);
                for (var i = prevVisible; i < visible; i++) {
                    cards[i].classList.remove('d-none');
                    cards[i].style.opacity = '0';
                    cards[i].style.transform = 'translateY(20px)';
                    cards[i].style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    (function(el, delay) {
                        setTimeout(function() {
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        }, delay);
                    })(cards[i], (i - prevVisible) * 80);
                }
                btnMore.style.display = visible < cards.length ? '' : 'none';
                btnLess.style.display = visible > INITIAL ? '' : 'none';
                if (resultLoaded) resultLoaded.textContent = visible;
                if (cards[prevVisible]) setTimeout(function() { cards[prevVisible].scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
            });

            btnLess.addEventListener('click', function () {
                visible = Math.max(visible - STEP, INITIAL);
                update();
                if (cards[visible - 1]) cards[visible - 1].scrollIntoView({ behavior: 'smooth', block: 'end' });
            });

            update();
        })();
    });
</script>

@endsection
