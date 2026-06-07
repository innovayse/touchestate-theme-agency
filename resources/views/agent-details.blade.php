<?php $page = 'agent-details'; ?>
@section('title')
    {{ $agent['fullName'] }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ $agent['fullName'] }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('agent.agents') }}
            @endslot
            @slot('li_2_url')
                /{{ app()->getLocale() }}/agent
            @endslot
            @slot('li_3')
                {{ $agent['fullName'] }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content">
            <div class="container">

                <!-- Agent Profile -->
                <div class="agent-profile-item">
                    <div class="agent-img">
                        @if($agent['avatarUrl'])
                        <img src="{{ $agent['avatarUrl'] }}" alt="{{ $agent['fullName'] }}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'agent-avatar-placeholder agent-avatar-placeholder-lg\'><i class=\'material-icons-outlined\'>person</i></div>';">
                        @else
                        <div class="agent-avatar-placeholder agent-avatar-placeholder-lg">
                            <i class="material-icons-outlined">person</i>
                        </div>
                        @endif
                    </div>
                    <div class="agent-content flex-fill">
                        <div class="d-flex align-items-center justify-content-between border-bottom flex-wrap row-gap-3 pb-3 mb-3">
                            <div>
                                <h5 class="mb-1">{{ $agent['fullName'] }}</h5>
                                @php $roleKey = 'agent.role_' . strtolower($agent['role'] ?? ''); @endphp
                                <p class="mb-0 fs-14 text-muted">{{ __($roleKey) !== $roleKey ? __($roleKey) : ($agent['role'] ?? '') }}</p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($agent['phone'])
                                <a href="tel:{{ $agent['phone'] }}" class="btn btn-dark d-inline-flex align-items-center">
                                    <i class="material-icons-outlined me-1">call</i>{{ __('agent-details.call_me') }}
                                </a>
                                @endif
                                @if($agent['email'])
                                <a href="mailto:{{ $agent['email'] }}" class="btn btn-primary d-inline-flex align-items-center">
                                    <i class="material-icons-outlined me-1">email</i>{{ __('agent-details.send_email') }}
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="agent-info">
                            @if($agent['phone'])
                            <p><i class="material-icons-outlined me-2" style="font-size:18px;vertical-align:middle">phone</i>{{ __('agent-details.mobile') }} : <span>{{ $agent['phone'] }}</span></p>
                            @endif
                            @if($agent['email'])
                            <p><i class="material-icons-outlined me-2" style="font-size:18px;vertical-align:middle">email</i>{{ __('agent-details.email') }} : <span>{{ $agent['email'] }}</span></p>
                            @endif
                            <p><i class="material-icons-outlined me-2" style="font-size:18px;vertical-align:middle">maps_home_work</i>{{ __('agent-details.my_listing') }} : <span>{{ $properties['totalCount'] ?? 0 }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Agent Properties -->
                @if(count($properties['items'] ?? []) > 0)
                <div class="border-top position-relative mt-3 pt-4">
                    <h5 class="mb-4">{{ __('agent-details.my_listing') }} ({{ $properties['totalCount'] ?? 0 }})</h5>

                    <!-- Skeleton Grid -->
                    <div id="agent-prop-skeleton" class="row mb-4">
                        @for($i = 0; $i < 3; $i++)
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

                    <!-- Real Grid -->
                    <div class="row mb-4" id="agent-prop-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                        @foreach($properties['items'] as $prop)
                            <x-property-card :prop="$prop" />
                        @endforeach
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
                @else
                <div class="border-top mt-3 pt-4 text-center py-5">
                    <i class="material-icons-outlined text-muted" style="font-size:48px">home_work</i>
                    <p class="text-muted mt-2 fs-16">{{ __('agent-details.no_properties') }}</p>
                </div>
                @endif

            </div>
        </div>
        <!-- End Content -->

    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Reveal real grid, hide skeleton
        var skeleton = document.getElementById('agent-prop-skeleton');
        var realGrid = document.getElementById('agent-prop-grid');
        if (skeleton) skeleton.style.display = 'none';
        if (realGrid) { realGrid.style.display = ''; requestAnimationFrame(function() { realGrid.style.opacity = '1'; }); }

        // Load More / Show Less
        (function () {
            var grid = document.getElementById('agent-prop-grid');
            var btnMore = document.getElementById('btnLoadMore');
            var btnLess = document.getElementById('btnShowLess');
            if (!grid || !btnMore || !btnLess) return;

            var cards = grid.children;
            var INITIAL = 12;
            var STEP = 6;
            var visible = Math.min(INITIAL, cards.length);

            if (cards.length <= INITIAL) return;

            function update() {
                for (var i = 0; i < cards.length; i++) {
                    if (i < visible) cards[i].classList.remove('d-none');
                    else cards[i].classList.add('d-none');
                }
                btnMore.style.display = visible < cards.length ? '' : 'none';
                btnLess.style.display = visible > INITIAL ? '' : 'none';
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
                        setTimeout(function() { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; }, delay);
                    })(cards[i], (i - prevVisible) * 80);
                }
                btnMore.style.display = visible < cards.length ? '' : 'none';
                btnLess.style.display = visible > INITIAL ? '' : 'none';
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
