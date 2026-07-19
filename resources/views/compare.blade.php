<?php $page = 'compare'; ?>
@section('title')
    {{ __('compare.title') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title') {{ __('compare.title') }} @endslot
            @slot('li_1') {{ __('common.home') }} @endslot
            @slot('li_2') {{ __('compare.title') }} @endslot
        @endcomponent

        <div class="content">
            <div class="container">

                <!-- Compare Toolbar -->
                <div class="compare-toolbar mb-4">
                    <div class="compare-toolbar-left">
                        <div class="compare-toolbar-icon">
                            <i class="material-icons-outlined">balance</i>
                        </div>
                        <div>
                            <div class="compare-toolbar-title">{{ __('compare.title') }}</div>
                            <div class="compare-toolbar-sub">
                                <span id="compare-result-count">0</span> {{ __('compare.properties_selected') }}
                            </div>
                        </div>
                    </div>
                    <button class="compare-clear-all btn btn-sm" style="display:none">
                        <i class="material-icons-outlined" style="font-size:18px;vertical-align:middle">delete_sweep</i>
                        <span class="d-none d-md-inline ms-1">{{ __('compare.clear_all') }}</span>
                    </button>
                </div>

                <!-- Empty state -->
                <div id="compare-empty" class="compare-empty-state" style="display:none">
                    <div class="compare-empty-icon">
                        <i class="material-icons-outlined">balance</i>
                    </div>
                    <h5>{{ __('compare.empty_title') }}</h5>
                    <p>{{ __('compare.empty_text') }}</p>
                    <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary">
                        {{ __('compare.browse') }}
                    </a>
                </div>

                <!-- Skeleton (shown while loading) -->
                <div id="compare-skeleton" style="display:none">

                    {{-- Mobile skeleton: swipeable cards --}}
                    <div class="compare-skeleton-mobile d-md-none" style="display:flex;flex-direction:row;gap:16px;overflow:hidden;padding:0 16px 12px;margin:0 -12px">
                        @for($i = 0; $i < 2; $i++)
                        <div style="flex:0 0 calc(100% - 32px);background:var(--bs-body-bg,#fff);border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)">
                            <div class="csk-block" style="width:100%;height:200px;border-radius:0"></div>
                            <div style="padding:16px">
                                <div class="csk-block mb-2" style="width:80%;height:15px;border-radius:4px"></div>
                                <div class="csk-block mb-3" style="width:50%;height:13px;border-radius:4px"></div>
                                <div class="csk-block mb-3" style="width:40%;height:22px;border-radius:6px"></div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                                    @for($r = 0; $r < 6; $r++)
                                    <div>
                                        <div class="csk-block mb-1" style="width:60%;height:10px;border-radius:3px"></div>
                                        <div class="csk-block" style="width:80%;height:13px;border-radius:3px"></div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="csk-block mt-3" style="width:100%;height:38px;border-radius:8px"></div>
                                <div class="csk-block mt-2" style="width:100%;height:32px;border-radius:8px"></div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Desktop skeleton: table --}}
                    <div class="compare-skeleton-wrap d-none d-md-block">

                        {{-- Header cards --}}
                        <div class="compare-skeleton-header">
                            <div class="compare-skeleton-label-cell">
                                <div class="csk-block" style="width:90px;height:30px;border-radius:6px"></div>
                            </div>
                            @for($i = 0; $i < 3; $i++)
                            <div class="compare-skeleton-card">
                                <div class="csk-block" style="width:100%;height:180px;border-radius:10px"></div>
                                <div class="csk-block mt-2" style="width:85%;height:14px;border-radius:4px"></div>
                                <div class="csk-block mt-2" style="width:55%;height:14px;border-radius:4px"></div>
                                <div class="csk-block mt-3" style="width:100%;height:34px;border-radius:8px"></div>
                            </div>
                            @endfor
                        </div>

                        {{-- Body rows --}}
                        @for($r = 0; $r < 8; $r++)
                        <div class="compare-skeleton-row">
                            <div class="compare-skeleton-label-cell">
                                <div class="csk-block" style="width:{{ $r % 3 === 0 ? 70 : ($r % 3 === 1 ? 85 : 60) }}%;height:13px;border-radius:4px"></div>
                            </div>
                            @for($i = 0; $i < 3; $i++)
                            <div class="compare-skeleton-value">
                                <div class="csk-block mx-auto" style="width:{{ 40 + ($r + $i) % 3 * 15 }}%;height:13px;border-radius:4px"></div>
                            </div>
                            @endfor
                        </div>
                        @endfor

                    </div>
                </div>

                <!-- Compare table (filled by AJAX) -->
                <div id="compare-result" class="mb-4" style="display:none;opacity:0;transition:opacity 0.35s ease"></div>

            </div>
        </div>

    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var STORAGE_KEY = 'te_compare';
    var skeleton   = document.getElementById('compare-skeleton');
    var result     = document.getElementById('compare-result');
    var emptyState = document.getElementById('compare-empty');
    var counter    = document.getElementById('compare-result-count');
    var clearBtn   = document.querySelector('.compare-clear-all');

    function getSlugs() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
        catch (e) { return []; }
    }

    function showEmpty() {
        if (skeleton)   skeleton.style.display   = 'none';
        if (result)   { result.style.display      = 'none'; result.innerHTML = ''; }
        if (clearBtn)   clearBtn.style.display    = 'none';
        if (counter)    counter.textContent       = '0';
        if (emptyState) emptyState.style.display  = 'flex';
    }

    var slugs = getSlugs();
    if (slugs.length === 0) { showEmpty(); return; }

    if (skeleton) skeleton.style.display = '';

    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('/{{ app()->getLocale() }}/compare/load', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
        body: JSON.stringify({ slugs: slugs })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (skeleton) skeleton.style.display = 'none';

        if (data.slugs !== undefined) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data.slugs));
            if (typeof window.updateHeaderCompare === 'function') window.updateHeaderCompare();
            if (typeof window.applyCompareIcons  === 'function') window.applyCompareIcons();
        }

        if (data.count === 0) { showEmpty(); return; }

        if (counter)  counter.textContent = data.count;
        if (clearBtn) clearBtn.style.display = '';

        if (result) {
            result.innerHTML = data.html;
            result.style.display = '';
            requestAnimationFrame(function () {
                result.style.opacity = '1';
                // Fix Chrome bug: sticky td doesn't inherit full row height
                result.querySelectorAll('tr').forEach(function (tr) {
                    var h = tr.getBoundingClientRect().height;
                    var label = tr.querySelector('.compare-row-label, .compare-label-th');
                    if (label) label.style.height = h + 'px';
                });
            });
        }

        // Remove single property
        document.querySelectorAll('.compare-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var curr = getSlugs().filter(function (s) { return s !== btn.dataset.slug; });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(curr));
                if (typeof window.updateHeaderCompare === 'function') window.updateHeaderCompare();
                if (typeof window.applyCompareIcons  === 'function') window.applyCompareIcons();
                location.reload();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                localStorage.removeItem(STORAGE_KEY);
                if (typeof window.updateHeaderCompare === 'function') window.updateHeaderCompare();
                if (typeof window.applyCompareIcons  === 'function') window.applyCompareIcons();
                showEmpty();
            });
        }
    })
    .catch(function () {
        if (skeleton) skeleton.style.display = 'none';
        if (emptyState) emptyState.style.display = 'flex';
    });
});
</script>

@endsection
