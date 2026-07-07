@php
    $filterCount = 0;
    foreach(['propertyType','transactionType','city','district','currency','minPrice','maxPrice','minArea','maxArea','minRooms','maxRooms','minBedrooms','maxBedrooms','minBathrooms','maxBathrooms','minFloor','maxFloor','yearBuiltFrom','yearBuiltTo','minLandArea','maxLandArea','renovationType','constructionType','furnitureType','petsPolicy','childrenPolicy','balconyType','terraceType','code','search'] as $f) {
        if(request($f) !== null && request($f) !== '') $filterCount++;
    }
    foreach(['amenities','features','appliances','utilities','heatingType','parkingType','windowView'] as $f) {
        if(request($f)) $filterCount += count(request($f));
    }
    foreach(['isNewConstruction','isNegotiable','isFrontLine','noAgentCalls','isLongTermRental','isUninhabited','sunDirection'] as $f) {
        if(request($f)) $filterCount++;
    }

    $landTypes = ['House','Villa','Townhouse','Land','Dacha','Cottage'];
    $showLand  = !request('propertyType') || in_array(request('propertyType'), $landTypes);
@endphp

<style>
/* ═══════════════════════════════════════════════════════════════════════════
   Advanced Filter v2 — Glassmorphism
═══════════════════════════════════════════════════════════════════════════ */

/* Per-theme CSS variables */
html[data-theme="dark"] {
    --afv2-panel-bg:      rgba(10, 10, 12, 0.96);
    --afv2-panel-border:  rgba(0, 212, 180, 0.18);
    --afv2-text:          rgba(255,255,255,.85);
    --afv2-text-muted:    rgba(255,255,255,.4);
    --afv2-input-bg:      rgba(255,255,255,.05);
    --afv2-input-border:  rgba(255,255,255,.1);
    --afv2-input-text:    rgba(255,255,255,.85);
    --afv2-chip-bg:       rgba(255,255,255,.04);
    --afv2-chip-border:   rgba(255,255,255,.11);
    --afv2-chip-text:     rgba(255,255,255,.6);
    --afv2-sep:           rgba(255,255,255,.07);
    --afv2-accent:        #00d4b4;
    --afv2-accent-glow:   rgba(0,212,180,.3);
    --afv2-accent-bg:     rgba(0,212,180,.1);
    --afv2-col-title:     rgba(255,255,255,.38);
    --afv2-btn-reset-bg:  rgba(255,255,255,.06);
    --afv2-btn-reset-col: rgba(255,255,255,.5);
}
html[data-theme="light"] {
    --afv2-panel-bg:      rgba(255,255,255,.96);
    --afv2-panel-border:  rgba(0,0,0,.09);
    --afv2-text:          rgba(0,0,0,.85);
    --afv2-text-muted:    rgba(0,0,0,.4);
    --afv2-input-bg:      rgba(0,0,0,.04);
    --afv2-input-border:  rgba(0,0,0,.11);
    --afv2-input-text:    rgba(0,0,0,.85);
    --afv2-chip-bg:       rgba(0,0,0,.04);
    --afv2-chip-border:   rgba(0,0,0,.11);
    --afv2-chip-text:     rgba(0,0,0,.6);
    --afv2-sep:           rgba(0,0,0,.08);
    --afv2-accent:        #10b981;
    --afv2-accent-glow:   rgba(16,185,129,.22);
    --afv2-accent-bg:     rgba(16,185,129,.08);
    --afv2-col-title:     rgba(0,0,0,.38);
    --afv2-btn-reset-bg:  rgba(0,0,0,.05);
    --afv2-btn-reset-col: rgba(0,0,0,.5);
}

/* ── Wrapper ── */
.adv-filter-v2 { position: relative; overflow-x: hidden; max-width: 100%; }

/* ── Group cards min-width ── */
.afv2-group { min-width: 0; }
.afv2-col { min-width: 0; }

/* ── Topbar ── */
.afv2-topbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px 0; }
.afv2-result  { font-size: .82rem; opacity: .55; margin: 0 0 6px; white-space: nowrap; }
.afv2-topbar-controls { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex: 1; }
.afv2-input-wrap.afv2-search { flex: 1 1 100%; }
.afv2-input-wrap {
    display: flex; align-items: center; gap: 6px;
    background: var(--afv2-input-bg);
    border: 1px solid var(--afv2-input-border);
    border-radius: 10px; padding: 0 12px; height: 40px;
    flex: 1; min-width: 140px; transition: border-color .2s;
}
.afv2-input-wrap:focus-within { border-color: var(--afv2-accent); }
.afv2-input-wrap svg { opacity: .45; flex-shrink: 0; }
.afv2-input-wrap input { background: none; border: none; outline: none; color: var(--afv2-text); font-size: .85rem; width: 100%; }
.afv2-input-wrap.afv2-code { flex: 1; min-width: 120px; }
.afv2-btn-search {
    display: flex; align-items: center; gap: 6px;
    padding: 0 18px; height: 40px; border-radius: 10px; border: none;
    background: var(--afv2-accent); color: #fff; font-size: .85rem; font-weight: 600;
    cursor: pointer; white-space: nowrap;
    transition: opacity .2s, box-shadow .2s;
}
.afv2-btn-search:hover { opacity: .88; box-shadow: 0 0 14px var(--afv2-accent-glow); }
.afv2-btn-filter {
    display: flex; align-items: center; gap: 6px;
    padding: 0 16px; height: 40px; border-radius: 10px;
    border: 1px solid var(--afv2-input-border);
    background: var(--afv2-input-bg); color: var(--afv2-text);
    font-size: .85rem; cursor: pointer; position: relative; white-space: nowrap;
    transition: border-color .2s, box-shadow .2s;
}
.afv2-btn-filter:hover,
.afv2-btn-filter.open { border-color: var(--afv2-accent); box-shadow: 0 0 10px var(--afv2-accent-glow); }
.afv2-btn-filter .afv2-filter-arrow { transition: transform .25s; }
.afv2-btn-filter.open .afv2-filter-arrow { transform: rotate(180deg); }
.afv2-filter-badge {
    position: absolute; top: -7px; right: -7px;
    background: var(--afv2-accent); color: #fff;
    border-radius: 50%; width: 18px; height: 18px;
    font-size: .68rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.afv2-view-switch { display: flex; align-items: center; gap: 4px; list-style: none; margin: 0; padding: 0; }
.afv2-view-btn {
    width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px; border: 1px solid var(--afv2-input-border);
    background: var(--afv2-input-bg); color: var(--afv2-text);
    text-decoration: none; transition: border-color .2s, background .2s, color .2s;
}
.afv2-view-btn:hover,
.afv2-view-btn.active { border-color: var(--afv2-accent); background: var(--afv2-accent-bg); color: var(--afv2-accent); }

/* ── Panel ── */
.afv2-panel {
    background: var(--afv2-panel-bg);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
    border: 1px solid var(--afv2-panel-border);
    border-radius: 16px;
    padding: 0 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,.18);
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height .38s ease, opacity .28s ease, padding .28s ease, margin-top .28s ease;
    margin-top: 0;
}
.afv2-panel.open { opacity: 1; max-height: 2000px; padding: 20px; margin-top: 8px; }

/* ── Basic row ── */
.afv2-basic-row { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
.afv2-basic-field { min-width: 0; }

/* ── Skeleton (before JS init) ── */
@keyframes afv2-shimmer {
    0%   { background-position: -600px 0; }
    100% { background-position:  600px 0; }
}
.adv-filter-v2:not(.afv2-ready) .afv2-basic-row .filter-select {
    opacity: 0;
    pointer-events: none;
}
.adv-filter-v2:not(.afv2-ready) .afv2-basic-field:has(select) {
    position: relative;
}
.adv-filter-v2:not(.afv2-ready) .afv2-basic-field:has(select)::after {
    content: '';
    position: absolute;
    inset: auto 0 0 0;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(90deg,
        var(--afv2-chip-bg) 0%,
        var(--afv2-input-border) 45%,
        var(--afv2-chip-bg) 80%
    );
    background-size: 600px 100%;
    animation: afv2-shimmer 1.2s ease-in-out infinite;
}

/* ── Labels ── */
.afv2-label {
    display: block; font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--afv2-text-muted); margin-bottom: 5px;
}
.afv2-label.mt-2 { margin-top: 12px; }

/* ── Text input (city / district) ── */
.afv2-text-input {
    width: 100%; min-width: 0; height: 36px; padding: 0 10px;
    border-radius: 8px; border: 1px solid var(--afv2-input-border);
    background: var(--afv2-input-bg); color: var(--afv2-input-text);
    font-size: .83rem; outline: none; transition: border-color .2s;
}
.afv2-text-input:focus { border-color: var(--afv2-accent); }

/* ── Custom selects inside filter — override theme (BEM: __trigger) ── */
.adv-filter-v2 .custom-select__trigger {
    height: 36px !important;
    background-color: var(--afv2-input-bg) !important;
    border-color: var(--afv2-input-border) !important;
    color: var(--afv2-input-text) !important;
    border-radius: 8px !important;
    font-size: .83rem !important;
    padding: 0 32px 0 10px !important;
    position: relative !important;
    display: flex !important;
    align-items: center !important;
}
.adv-filter-v2 .custom-select__trigger:hover {
    border-color: var(--afv2-accent) !important;
}
.adv-filter-v2 .custom-select.open .custom-select__trigger {
    background-color: var(--afv2-accent-bg) !important;
    border-color: var(--afv2-accent) !important;
    box-shadow: 0 0 0 3px var(--afv2-accent-glow);
}
.adv-filter-v2 .custom-select.open .custom-select__trigger::before {
    content: '';
    position: absolute;
    left: -1px; right: -1px;
    bottom: 0; height: 50%;
    border-left: 1px solid var(--afv2-accent);
    border-right: 1px solid var(--afv2-accent);
    border-bottom: 1px solid var(--afv2-accent);
    border-radius: 0 0 8px 8px;
    pointer-events: none;
    z-index: 1;
}
.adv-filter-v2 .custom-select__options {
    background: var(--afv2-panel-bg) !important;
    border-color: var(--afv2-panel-border) !important;
    backdrop-filter: blur(16px) !important;
    border-radius: 10px !important;
}
.adv-filter-v2 .custom-select__option {
    color: var(--afv2-text) !important;
    font-size: .83rem !important;
}
.adv-filter-v2 .custom-select__option:hover,
.adv-filter-v2 .custom-select__option.selected {
    background: var(--afv2-accent-bg) !important;
    color: var(--afv2-accent) !important;
}

/* ── Separator ── */
.afv2-sep { height: 1px; background: var(--afv2-sep); margin: 18px 0; }

/* ── Columns ── */
.afv2-cols { display: grid; grid-template-columns: 1fr; gap: 16px; }

.afv2-col-title {
    font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--afv2-col-title); margin: 0 0 14px;
}
.afv2-sublabel {
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--afv2-text-muted); margin: 14px 0 6px;
}
.afv2-sublabel.mt-2 { margin-top: 18px; }

/* ── Group cards (col 2 & 3 sections) ── */
.afv2-group {
    border: 1px solid var(--afv2-input-border);
    border-radius: 10px;
    padding: 10px 12px 12px;
    margin-bottom: 8px;
    overflow: hidden;
    min-width: 0;
}
.afv2-group .afv2-sublabel { margin: 0 0 8px; }

/* ── Chips ── */
.afv2-chips { display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; min-width: 0; }
.afv2-chip-label { cursor: pointer; }
.afv2-chip-label input[type=checkbox] { display: none; }
.afv2-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 10px; border-radius: 20px;
    border: 1px solid var(--afv2-chip-border);
    background: var(--afv2-chip-bg); color: var(--afv2-chip-text);
    font-size: .78rem; white-space: normal; user-select: none;
    width: 100%;
    transition: border-color .18s, color .18s, background .18s, box-shadow .18s;
}
.afv2-chip svg { flex-shrink: 0; }
.afv2-chip-label:hover .afv2-chip { border-color: var(--afv2-accent); color: var(--afv2-text); }
.afv2-chip-label input:checked + .afv2-chip {
    border-color: var(--afv2-accent);
    background: var(--afv2-accent-bg);
    color: var(--afv2-accent);
    box-shadow: 0 0 10px var(--afv2-accent-glow);
}

/* ── Number chips ── */
.afv2-num-chips { display: flex; gap: 5px; flex-wrap: wrap; }
.afv2-num-chip {
    min-width: 36px; height: 36px; padding: 0 8px;
    border-radius: 8px; border: 1px solid var(--afv2-chip-border);
    background: var(--afv2-chip-bg); color: var(--afv2-chip-text);
    font-size: .83rem; font-weight: 600; cursor: pointer;
    transition: border-color .18s, color .18s, background .18s, box-shadow .18s;
}
.afv2-num-chip:hover { border-color: var(--afv2-accent); color: var(--afv2-text); }
.afv2-num-chip.active {
    border-color: var(--afv2-accent);
    background: var(--afv2-accent-bg);
    color: var(--afv2-accent);
    box-shadow: 0 0 10px var(--afv2-accent-glow);
}

/* ── Range pair ── */
.afv2-range-pair { display: flex; align-items: center; gap: 6px; width: 100%; }
.afv2-range-pair.mt-1 { margin-top: 6px; }
.afv2-num-input {
    flex: 1 1 0; height: 36px; padding: 0 10px;
    border-radius: 8px; border: 1px solid var(--afv2-input-border);
    background: var(--afv2-input-bg); color: var(--afv2-input-text);
    font-size: .83rem; outline: none; min-width: 0; width: 0;
    transition: border-color .2s;
    -moz-appearance: textfield;
}
.afv2-num-input::-webkit-outer-spin-button,
.afv2-num-input::-webkit-inner-spin-button { -webkit-appearance: none; }
.afv2-num-input:focus { border-color: var(--afv2-accent); }
.afv2-range-dash { color: var(--afv2-text-muted); font-size: .8rem; flex-shrink: 0; }

/* ── Ranges row (price / area / land) ── */
.afv2-ranges-row { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; width: 100%; }
.afv2-range-group { flex: 1 1 160px; min-width: 0; }

/* ── Action bar ── */
.afv2-actions {
    display: flex; align-items: center; gap: 10px;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid var(--afv2-sep);
    margin-top: 16px;
}
.afv2-active-count { flex: 1; text-align: center; font-size: .82rem; color: var(--afv2-text-muted); }
.afv2-btn-reset {
    padding: 0 20px; height: 38px; border-radius: 10px;
    border: 1px solid var(--afv2-input-border);
    background: var(--afv2-btn-reset-bg); color: var(--afv2-btn-reset-col);
    font-size: .85rem; cursor: pointer; white-space: nowrap;
    transition: border-color .2s, color .2s;
}
.afv2-btn-reset:hover { border-color: var(--afv2-accent); color: var(--afv2-accent); }
.afv2-btn-apply {
    padding: 0 24px; height: 38px; border-radius: 10px; border: none;
    background: var(--afv2-accent); color: #fff;
    font-size: .85rem; font-weight: 600; cursor: pointer; white-space: nowrap;
    transition: opacity .2s, box-shadow .2s;
}
.afv2-btn-apply:hover { opacity: .88; box-shadow: 0 0 16px var(--afv2-accent-glow); }

/* ── Group header ── */
.afv2-group-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px; gap: 6px;
}
.afv2-group-header .afv2-sublabel { margin-bottom: 0; }
.afv2-group-header-right { display: flex; align-items: center; gap: 6px; }

/* ── Group body ── */
.afv2-group-body {
    overflow: visible;
    max-height: none;
    opacity: 1;
    transition: max-height .3s ease, opacity .2s ease;
}

/* ── Column collapse ── */
.afv2-col-title-wrap {
    display: flex; align-items: center; justify-content: space-between;
    cursor: pointer; margin-bottom: 10px;
    user-select: none;
    padding: 4px 0;
}
.afv2-col-toggle {
    background: none; border: none; padding: 2px; cursor: pointer;
    color: var(--afv2-text-muted); display: flex; align-items: center;
    transition: transform .25s ease, color .18s;
}
.afv2-col.collapsed .afv2-col-toggle { transform: rotate(-90deg); }
.afv2-col-toggle:hover { color: var(--afv2-accent); }
.afv2-col-body {
    overflow: visible;
    transition: max-height .3s ease, opacity .2s ease;
    opacity: 1;
}
.afv2-col.collapsed .afv2-col-body {
    max-height: 0 !important;
    overflow: hidden;
    opacity: 0;
}

/* ── Select all button ── */
.afv2-select-all-btn,
.afv2-numchip-all {
    background: none; border: 1px solid var(--afv2-input-border);
    border-radius: 6px; padding: 2px 8px;
    font-size: .68rem; font-weight: 600;
    color: var(--afv2-text-muted); cursor: pointer;
    transition: border-color .18s, color .18s;
    white-space: nowrap;
}
.afv2-select-all-btn:hover,
.afv2-select-all-btn.all-selected,
.afv2-numchip-all:hover,
.afv2-numchip-all.all-selected {
    border-color: var(--afv2-accent);
    color: var(--afv2-accent);
}

/* ══════════════════════════════════════════════════
   Responsive — mobile-first breakpoints
══════════════════════════════════════════════════ */

/* Groups inside col: 2 cols at wide, 1 col at narrow */
.afv2-col-body { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.afv2-col-body .afv2-sublabel { grid-column: 1 / -1; }
@media (max-width: 900px) {
    .afv2-col-body { grid-template-columns: 1fr; }
}

/* ≤ 1440px: basic row → 3 columns */
@media (max-width: 1440px) {
    .afv2-basic-row { grid-template-columns: repeat(3, 1fr); gap: 8px; }
}
/* ≤ 1100px: basic row → 2 columns */
@media (max-width: 1100px) {
    .afv2-basic-row { grid-template-columns: repeat(2, 1fr); gap: 8px; }
}

/* ≤ 767px: mobile */
@media (max-width: 767px) {
    .afv2-topbar { flex-direction: column; align-items: stretch; gap: 6px; padding: 8px 0; }
    .afv2-result { font-size: .74rem; order: 2; opacity: .4; }

    /* Controls: 3-row grid
       Row 1: [search input.........] [search btn]
       Row 2: [code input...............(full width)]
       Row 3: [filter btn..........] [view switch] */
    .afv2-topbar-controls {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 6px;
        order: 1;
    }
    .afv2-input-wrap.afv2-search { grid-column: 1 / -1; grid-row: 1; min-width: 0; }
    .afv2-input-wrap.afv2-code   { grid-column: 1; grid-row: 2; display: flex; }
    .afv2-btn-search              { grid-column: 2; grid-row: 2; padding: 0 16px; }
    .afv2-btn-filter              { grid-column: 1; grid-row: 3; justify-content: center; }
    .afv2-view-switch             { grid-column: 2; grid-row: 3; flex-shrink: 0; }


    /* Panel */
    .afv2-panel { border-radius: 12px; }
    .afv2-panel.open { padding: 14px; }

    /* Ranges */
    .afv2-ranges-row { gap: 10px; }
    .afv2-range-group { flex: 1 1 160px; min-width: 130px; }

    /* Actions: 1 col stacked */
    .afv2-actions { flex-direction: column; gap: 8px; }
    .afv2-active-count { display: none; }
    .afv2-btn-reset,
    .afv2-btn-apply { width: 100%; height: 42px; justify-content: center; display: flex; align-items: center; border-radius: 12px; }

}

/* ≤ 480px: small mobile */
@media (max-width: 480px) {
    .afv2-btn-search-label { display: none; }
    .afv2-btn-search { padding: 0; justify-content: center; }

    /* Basic row → 1 column */
    .afv2-basic-row { grid-template-columns: 1fr; }

    /* Chips → 1 column */
    .afv2-chips { grid-template-columns: 1fr; }

    /* Range groups: stack */
    .afv2-range-group { flex: 1 1 100%; }

    /* Columns → 1 at 480px already covered by 600px rule,
       but ensure panel padding is tight */
    .afv2-panel.open { padding: 12px 10px; }
}
</style>

<p class="afv2-result">{{ __('map.result') }} <span class="result-value" id="result-loaded">{{ count($properties['items'] ?? []) }}</span> / <span class="result-value" id="result-total">{{ $properties['totalCount'] ?? 0 }}</span></p>

<form method="GET" action="{{ $filterAction }}" id="filterForm">
<div class="advanced-filter adv-filter-v2">

    <!-- Topbar -->
    <div class="afv2-topbar">

        <div class="afv2-topbar-controls">
            <div class="afv2-input-wrap afv2-search">
                <x-icon name="search" size="18"/>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('map.search') }}" autocomplete="off">
            </div>
            <div class="afv2-input-wrap afv2-code">
                <x-icon name="tag" size="18"/>
                <input type="text" name="code" value="{{ request('code') }}" placeholder="{{ __('map.search_code') }}">
            </div>
            <button type="submit" class="afv2-btn-search">
                <x-icon name="search" size="18"/>
                <span class="afv2-btn-search-label">{{ __('map.search') }}</span>
            </button>
            <button type="button" class="afv2-btn-filter" id="advFilterToggle">
                <x-icon name="layers" size="18"/>
                {{ __('map.filter') }}
                <x-icon name="trending_down" size="18" class="afv2-filter-arrow"/>
                @if($filterCount > 0)<span class="afv2-filter-badge">{{ $filterCount }}</span>@endif
            </button>
            <ul class="afv2-view-switch">
                <li><a href="/{{ app()->getLocale() }}/property" class="afv2-view-btn {{ ($filterPage ?? 'property') === 'property' ? 'active' : '' }}"><x-icon name="crop_square" size="20"/></a></li>
                <li><a href="/{{ app()->getLocale() }}/map" class="afv2-view-btn {{ ($filterPage ?? 'property') === 'map' ? 'active' : '' }}"><x-icon name="location_on" size="20"/></a></li>
            </ul>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="afv2-panel" id="advFilterPanel">

        <!-- Basic row -->
        <div class="afv2-basic-row">
            <div class="afv2-basic-field">
                <label class="afv2-label">{{ __('map.property_type') }}</label>
                <select name="propertyType" class="filter-select" id="propertyTypeSelect">
                    <option value="">{{ __('map.any') }}</option>
                    @foreach(['Apartment','House','Studio','Villa','Townhouse','Penthouse','Room','Complex','Land','Commercial','Office','Warehouse','Garage','Pavilion','EventVenue','Dacha','Cottage'] as $pt)
                    <option value="{{ $pt }}" @selected(request('propertyType') === $pt)>{{ __('property.'.strtolower($pt)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="afv2-basic-field">
                <label class="afv2-label">{{ __('map.transaction_type') }}</label>
                <select name="transactionType" class="filter-select">
                    <option value="">{{ __('map.any') }}</option>
                    <option value="Sale" @selected(request('transactionType') === 'Sale')>{{ __('map.sale') }}</option>
                    <option value="Rent" @selected(request('transactionType') === 'Rent')>{{ __('map.rent_monthly') }}</option>
                    <option value="RentDaily" @selected(request('transactionType') === 'RentDaily')>{{ __('map.rent_daily') }}</option>
                </select>
            </div>
            <div class="afv2-basic-field afv2-city-field">
                <label class="afv2-label">{{ __('map.city') }}</label>
                <div style="position:relative">
                    <input type="text" id="cityInput" value="{{ request('city') }}" placeholder="{{ __('map.enter_city') }}" class="afv2-text-input" autocomplete="off">
                    <input type="hidden" name="city" id="cityHidden" value="{{ request('city') }}">
                    <button type="button" id="cityClearBtn" class="city-clear-btn"><x-icon name="close" size="16"/></button>
                    <span id="citySpinner" class="city-loading-spinner"></span>
                    <ul id="citySuggestions" class="city-suggestions"></ul>
                </div>
            </div>
            <div class="afv2-basic-field">
                <label class="afv2-label">{{ __('map.district') }}</label>
                <div style="position:relative">
                    <input type="text" id="districtInput" name="district" value="{{ request('district') }}" placeholder="{{ __('map.enter_district') }}" class="afv2-text-input" autocomplete="off">
                    <button type="button" id="districtClearBtn" class="city-clear-btn"><x-icon name="close" size="16"/></button>
                    <ul id="districtSuggestions" class="city-suggestions"></ul>
                </div>
            </div>
            <div class="afv2-basic-field">
                <label class="afv2-label">{{ __('map.sort') }}</label>
                <select name="sortBy" class="filter-select">
                    <option value="createdAt" @selected(request('sortBy','createdAt') === 'createdAt')>{{ __('map.sort_date') }}</option>
                    <option value="price" @selected(request('sortBy') === 'price')>{{ __('map.sort_price') }}</option>
                    <option value="area" @selected(request('sortBy') === 'area')>{{ __('map.sort_area') }}</option>
                    <option value="rooms" @selected(request('sortBy') === 'rooms')>{{ __('map.sort_rooms') }}</option>
                    <option value="viewCount" @selected(request('sortBy') === 'viewCount')>{{ __('map.sort_views') }}</option>
                </select>
            </div>
            <div class="afv2-basic-field">
                <label class="afv2-label">{{ __('map.order') }}</label>
                <select name="sortOrder" class="filter-select">
                    <option value="desc" @selected(request('sortOrder','desc') === 'desc')>{{ __('map.sort_desc') }}</option>
                    <option value="asc" @selected(request('sortOrder') === 'asc')>{{ __('map.sort_asc') }}</option>
                </select>
            </div>
        </div>

        <div class="afv2-sep"></div>

        <!-- Price / Area / Land -->
        <div class="afv2-ranges-row">
            <div class="afv2-range-group">
                <label class="afv2-label">{{ __('map.price') }}</label>
                <div class="afv2-range-pair">
                    <input type="number" name="minPrice" value="{{ request('minPrice') }}" placeholder="{{ __('map.price_from') }}" min="0" class="afv2-num-input">
                    <span class="afv2-range-dash">—</span>
                    <input type="number" name="maxPrice" value="{{ request('maxPrice') }}" placeholder="{{ __('map.price_to') }}" min="0" class="afv2-num-input">
                </div>
            </div>
            <div class="afv2-range-group">
                <label class="afv2-label">{{ __('map.currency') }}</label>
                <select name="currency" class="filter-select">
                    <option value="">{{ __('map.any') }}</option>
                    <option value="USD" @selected(request('currency') === 'USD')>USD ($)</option>
                    <option value="AMD" @selected(request('currency') === 'AMD')>AMD (֏)</option>
                    <option value="RUB" @selected(request('currency') === 'RUB')>RUB (₽)</option>
                    <option value="EUR" @selected(request('currency') === 'EUR')>EUR (€)</option>
                </select>
            </div>
            <div class="afv2-range-group">
                <label class="afv2-label">{{ __('map.area') }} (м²)</label>
                <div class="afv2-range-pair">
                    <input type="number" name="minArea" value="{{ request('minArea') }}" placeholder="{{ __('map.area_from') }}" min="0" class="afv2-num-input">
                    <span class="afv2-range-dash">—</span>
                    <input type="number" name="maxArea" value="{{ request('maxArea') }}" placeholder="{{ __('map.area_to') }}" min="0" class="afv2-num-input">
                </div>
            </div>
            <div class="afv2-range-group" id="landAreaRow"@if(!$showLand) style="display:none"@endif>
                <label class="afv2-label">{{ __('map.land_from') }} (м²)</label>
                <div class="afv2-range-pair">
                    <input type="number" name="minLandArea" value="{{ request('minLandArea') }}" placeholder="{{ __('map.range_from') }}" min="0" class="afv2-num-input">
                    <span class="afv2-range-dash">—</span>
                    <input type="number" name="maxLandArea" value="{{ request('maxLandArea') }}" placeholder="{{ __('map.range_to') }}" min="0" class="afv2-num-input">
                </div>
            </div>
        </div>

        <div class="afv2-sep"></div>

        <!-- 3 columns -->
        <div class="afv2-cols">

            <!-- Column 1: Building characteristics -->
            <div class="afv2-col collapsed">
                <div class="afv2-col-title-wrap">
                    <h4 class="afv2-col-title">{{ __('map.construction_type') }}</h4>
                    <button type="button" class="afv2-col-toggle" aria-label="toggle"><x-icon name="expand_more" size="14"/></button>
                </div>
                <div class="afv2-col-body">

                <!-- Renovation type -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.renovation_type') }}</span>
                    </div>
                    <div class="afv2-group-body">
                        <select name="renovationType" class="filter-select">
                            <option value="">{{ __('map.any') }}</option>
                            @foreach(['Capital','Designer','Euro','Cosmetic','Partial','Old','Unrenovated'] as $v)
                            <option value="{{ $v }}" @selected(request('renovationType') === $v)>{{ __('map.renovation_'.$v) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Construction type (Material) -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.construction_type') }}</span>
                    </div>
                    <div class="afv2-group-body">
                        <select name="constructionType" class="filter-select">
                            <option value="">{{ __('map.any') }}</option>
                            @foreach(['Wood','Strip','Brick','Monolithic','Panel','Stone'] as $v)
                            <option value="{{ $v }}" @selected(request('constructionType') === $v)>{{ __('map.construction_'.$v) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Furniture type -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.furniture_type') }}</span>
                    </div>
                    <div class="afv2-group-body">
                        <select name="furnitureType" class="filter-select">
                            <option value="">{{ __('map.any') }}</option>
                            @foreach(['Furnished','Partial','Unavailable','ByAgreement'] as $v)
                            <option value="{{ $v }}" @selected(request('furnitureType') === $v)>{{ __('map.furniture_'.$v) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Year built -->
                <div class="afv2-group">
                    <p class="afv2-sublabel">{{ __('map.year_built') }}</p>
                    <div class="afv2-group-body">
                        <div class="afv2-range-pair">
                            <input type="number" name="yearBuiltFrom" id="yearBuiltFrom" value="{{ request('yearBuiltFrom') }}" placeholder="{{ __('map.range_from') }}" min="1800" max="{{ date('Y') }}" class="afv2-num-input">
                            <span class="afv2-range-dash">—</span>
                            <input type="number" name="yearBuiltTo" id="yearBuiltTo" value="{{ request('yearBuiltTo') }}" placeholder="{{ __('map.range_to') }}" min="1800" max="{{ date('Y') }}" class="afv2-num-input">
                        </div>
                    </div>
                </div>

                <!-- Rooms (chips) -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.rooms') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-numchip-all" data-target="roomChips" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <input type="hidden" name="minRooms" id="minRoomsInput" value="{{ request('minRooms') }}">
                        <input type="hidden" name="maxRooms" id="maxRoomsInput" value="{{ request('maxRooms') }}">
                        <div class="afv2-num-chips" id="roomChips">
                            @foreach([1,2,3,4,5] as $n)
                            <button type="button" class="afv2-num-chip" data-value="{{ $n }}">{{ $n }}</button>
                            @endforeach
                            <button type="button" class="afv2-num-chip" data-value="6" data-plus="1">6+</button>
                        </div>
                    </div>
                </div>

                <!-- Bathrooms (chips) -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.bathrooms') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-numchip-all" data-target="bathroomChips" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <input type="hidden" name="minBathrooms" id="minBathroomsInput" value="{{ request('minBathrooms') }}">
                        <input type="hidden" name="maxBathrooms" id="maxBathroomsInput" value="{{ request('maxBathrooms') }}">
                        <div class="afv2-num-chips" id="bathroomChips">
                            @foreach([1,2] as $n)
                            <button type="button" class="afv2-num-chip" data-value="{{ $n }}">{{ $n }}</button>
                            @endforeach
                            <button type="button" class="afv2-num-chip" data-value="3" data-plus="1">3+</button>
                        </div>
                    </div>
                </div>

                <!-- Floor range -->
                <div class="afv2-group">
                    <p class="afv2-sublabel">{{ __('map.floor_from') }} / {{ __('map.floor_to') }}</p>
                    <div class="afv2-group-body">
                        <div class="afv2-range-pair">
                            <input type="number" name="minFloor" value="{{ request('minFloor') }}" placeholder="{{ __('map.range_from') }}" min="0" class="afv2-num-input">
                            <span class="afv2-range-dash">—</span>
                            <input type="number" name="maxFloor" value="{{ request('maxFloor') }}" placeholder="{{ __('map.range_to') }}" min="0" class="afv2-num-input">
                        </div>
                    </div>
                </div>

                <!-- Balcony / Terrace type -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.balcony_type') }} / {{ __('map.terrace_type') }}</span>
                    </div>
                    <div class="afv2-group-body">
                        <div style="margin-bottom:12px">
                            <select name="balconyType" class="filter-select">
                                <option value="">{{ __('map.balcony_type') }} — {{ __('map.any') }}</option>
                                @foreach(['Unavailable','Open','Closed'] as $v)
                                <option value="{{ $v }}" @selected(request('balconyType') === $v)>{{ __('map.opentype_'.$v) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <select name="terraceType" class="filter-select">
                            <option value="">{{ __('map.terrace_type') }} — {{ __('map.any') }}</option>
                            @foreach(['Unavailable','Open','Closed'] as $v)
                            <option value="{{ $v }}" @selected(request('terraceType') === $v)>{{ __('map.opentype_'.$v) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                </div><!-- end afv2-col-body -->
            </div>

            <!-- Column 2: Amenities & Features -->
            <div class="afv2-col collapsed">
                <div class="afv2-col-title-wrap">
                    <h4 class="afv2-col-title">{{ __('map.features') }}</h4>
                    <button type="button" class="afv2-col-toggle" aria-label="toggle"><x-icon name="expand_more" size="14"/></button>
                </div>
                <div class="afv2-col-body">

                <!-- Heating type -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.heating_type') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([['Central','thermostat'],['Gas','local_fire_department'],['Electric','bolt'],['Autonomous','power'],['Solar','wb_sunny'],['UnderfloorHeating','foundation']] as [$val,$icon])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="heatingType[]" value="{{ $val }}" @checked(in_array($val, request('heatingType', [])))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.heating_'.$val) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Parking type -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.parking_type') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([['Open','local_parking'],['Covered','garage'],['Garage','domain'],['Barrier','location_on']] as [$val,$icon])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="parkingType[]" value="{{ $val }}" @checked(in_array($val, request('parkingType', [])))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.parking_'.$val) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Window view -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.window_view') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([['Garden','yard'],['City','apartment'],['Street','location_on'],['Yard','home']] as [$val,$icon])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="windowView[]" value="{{ $val }}" @checked(in_array($val, request('windowView', [])))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.view_'.$val) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Features / Amenities -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.features') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([['Elevator','elevator'],['Pool','pool'],['Gym','fitness_center'],['Security','security'],['Garden','yard'],['Basement','foundation'],['Sauna','hot_tub'],['BarbecueArea','outdoor_grill'],['PanoramicWindows','panorama']] as [$val,$icon])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="features[]" value="{{ $val }}" @checked(in_array($val, request('features', [])))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.feature_'.strtolower($val)) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Appliances -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.appliances') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([['Washer','local_laundry_service'],['Fridge','kitchen'],['Stove','outdoor_grill'],['Microwave','microwave'],['WaterHeater','water_heater'],['Dishwasher','dishwasher'],['CoffeeMaker','coffee']] as [$val,$icon])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="appliances[]" value="{{ $val }}" @checked(in_array($val, request('appliances', [])))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.appliance_'.strtolower($val)) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Utilities -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.utilities') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([['Electricity','bolt'],['Water','water_drop'],['Gas','local_fire_department'],['Sewage','plumbing']] as [$val,$icon])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="utilities[]" value="{{ $val }}" @checked(in_array($val, request('utilities', [])))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.utility_'.strtolower($val)) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                </div><!-- end afv2-col-body -->
            </div>

            <!-- Column 3: Permissions & Conditions -->
            <div class="afv2-col collapsed">
                <div class="afv2-col-title-wrap">
                    <h4 class="afv2-col-title">{{ __('map.permissions_title') }}</h4>
                    <button type="button" class="afv2-col-toggle" aria-label="toggle"><x-icon name="expand_more" size="14"/></button>
                </div>
                <div class="afv2-col-body">

                <!-- Pets / Children policy -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.pets_policy') }}</span>
                    </div>
                    <div class="afv2-group-body">
                        <select name="petsPolicy" class="filter-select">
                            <option value="">{{ __('map.any') }}</option>
                            <option value="Yes" @selected(request('petsPolicy') === 'Yes')>{{ __('map.policy_yes') }}</option>
                            <option value="No" @selected(request('petsPolicy') === 'No')>{{ __('map.policy_no') }}</option>
                            <option value="ByAgreement" @selected(request('petsPolicy') === 'ByAgreement')>{{ __('map.policy_by_agreement') }}</option>
                        </select>
                        <div style="margin-top:8px">
                            <select name="childrenPolicy" class="filter-select">
                                <option value="">{{ __('map.children_policy') }} — {{ __('map.any') }}</option>
                                <option value="Yes" @selected(request('childrenPolicy') === 'Yes')>{{ __('map.policy_yes') }}</option>
                                <option value="No" @selected(request('childrenPolicy') === 'No')>{{ __('map.policy_no') }}</option>
                                <option value="ByAgreement" @selected(request('childrenPolicy') === 'ByAgreement')>{{ __('map.policy_by_agreement') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Deal conditions (flags) -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.deal_conditions') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([
                                ['isNegotiable','handshake','negotiable'],
                                ['isLongTermRental','calendar_today','long_term_rental'],
                                ['isUninhabited','person','uninhabited'],
                            ] as [$name,$icon,$key])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="{{ $name }}" value="1" @checked(request($name))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.'.$key) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Object type (flags) -->
                <div class="afv2-group">
                    <div class="afv2-group-header">
                        <span class="afv2-sublabel">{{ __('map.object_type') }}</span>
                        <div class="afv2-group-header-right">
                            <button type="button" class="afv2-select-all-btn" data-label-select="{{ __('map.select_all_short') }}" data-label-deselect="{{ __('map.deselect_all_short') }}">{{ __('map.select_all_short') }}</button>
                        </div>
                    </div>
                    <div class="afv2-group-body">
                        <div class="afv2-chips">
                            @foreach([
                                ['isNewConstruction','apartment','new_construction'],
                                ['isFrontLine','landscape','front_line'],
                                ['sunDirection','wb_sunny','sun_direction'],
                            ] as [$name,$icon,$key])
                            <label class="afv2-chip-label">
                                <input type="checkbox" name="{{ $name }}" value="1" @checked(request($name))>
                                <span class="afv2-chip"><x-icon name="{{ $icon }}" size="14"/>{{ __('map.'.$key) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                </div><!-- end afv2-col-body -->
            </div>

        </div><!-- end afv2-cols -->

        <!-- Action bar -->
        <div class="afv2-actions">
            <button type="button" class="afv2-btn-reset" id="btnReset">{{ __('map.reset') }}</button>
            <span class="afv2-active-count">@if($filterCount > 0)({{ $filterCount }} {{ __('map.filters_selected') }})@endif</span>
            <button type="submit" class="afv2-btn-apply">{{ __('map.apply_filter') }}</button>
        </div>

    </div><!-- end afv2-panel -->

</div><!-- end adv-filter-v2 -->
</form>

<script>
// ── City resolve on load ────────────────────────────────────────────────────
(function () {
    var h    = document.getElementById('cityHidden');
    var lang = '{{ app()->getLocale() }}';
    if (!h || !h.value || lang === 'en') return;
    var localCity = h.value;
    fetch('https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(localCity) + '&lang=en_US&results=1&highlight=0&v=9')
        .then(function (r) { return r.text(); })
        .then(function (body) {
            var m = body.trim().match(/suggest\.apply\(([\s\S]+)\)/);
            if (!m) return;
            var data = JSON.parse(m[1]);
            var first = (data.results || [])[0];
            if (first) h.value = (first.title || {}).text || localCity;
        }).catch(function () {});
}());

// ── City autocomplete ───────────────────────────────────────────────────────
(function () {
    var input    = document.getElementById('cityInput');
    var hidden   = document.getElementById('cityHidden');
    var list     = document.getElementById('citySuggestions');
    var clearBtn = document.getElementById('cityClearBtn');
    var spinner  = document.getElementById('citySpinner');
    if (!input || !list) return;
    document.body.appendChild(list);
    var timer = null, lang = '{{ app()->getLocale() }}', shown = {}, districtAutoFilled = false;
    (function () {
        var d = document.querySelector('[name="district"]');
        if (d) d.addEventListener('input', function () { districtAutoFilled = false; });
    }());
    function parseYandex(body) {
        var m = (body || '').trim().match(/suggest\.apply\(([\s\S]+)\)/);
        if (!m) return [];
        try { var data = JSON.parse(m[1]); } catch (e) { return []; }
        return (data.results || []).map(function (item) {
            var title = (item.title || {}).text || '';
            var where = ((item.log_id || {}).where) || {};
            if (!title || title !== (where.title || '')) return null;
            var parts = (where.name || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
            var desc  = parts.filter(function (p) { return p !== title; }).join(', ');
            return { name: title, desc: desc };
        }).filter(Boolean);
    }
    function makeLi(name, desc, enName) {
        var li = document.createElement('li');
        li.innerHTML = '<span class="city-item-text"><span class="city-item-name">' + name + '</span>' + (desc ? '<span class="city-item-desc">' + desc + '</span>' : '') + '</span>';
        li.addEventListener('mousedown', function (e) {
            e.preventDefault();
            input.value = name;
            var cityEn = enName || name;
            if (hidden) hidden.value = cityEn;
            if (typeof window.panMapToLocation === 'function') window.panMapToLocation(name);
            if (clearBtn) clearBtn.style.display = 'none';
            if (spinner)  spinner.style.display  = 'block';
            list.style.display = 'none'; list.innerHTML = ''; shown = {};
            var districtEl = document.querySelector('[name="district"]');
            if (districtEl) districtEl.value = '';
            fetch('/api/central-district?city=' + encodeURIComponent(cityEn) + '&lang=' + encodeURIComponent(lang))
                .then(function (r) { return r.json(); })
                .then(function (data) { if (data.district && districtEl) { districtEl.value = data.district; districtAutoFilled = true; } })
                .catch(function () {})
                .finally(function () { if (spinner) spinner.style.display = 'none'; if (clearBtn) clearBtn.style.display = 'flex'; });
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
        list.innerHTML = ''; shown = {};
        if (!items.length) { list.style.display = 'none'; return; }
        positionList();
        items.forEach(function (it) {
            if (shown[it.name]) return;
            shown[it.name] = true;
            list.appendChild(makeLi(it.name, it.desc, it.enName));
        });
        list.style.display = 'block';
    }
    function updateClearBtn() { if (clearBtn) clearBtn.style.display = input.value ? 'flex' : 'none'; }
    input.addEventListener('input', function () {
        updateClearBtn(); clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) { list.style.display = 'none'; list.innerHTML = ''; shown = {}; return; }
        timer = setTimeout(function () {
            var yLang = lang === 'en' ? 'en_US' : lang === 'hy' ? 'hy_AM' : 'ru_RU';
            var base  = 'https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&results=7&highlight=0&v=9';
            var pLocal = fetch(base + '&lang=' + yLang).then(function (r) { return r.text(); }).catch(function () { return ''; });
            var pEn    = lang === 'en' ? Promise.resolve(null) : fetch(base + '&lang=en_US').then(function (r) { return r.text(); }).catch(function () { return ''; });
            Promise.all([pLocal, pEn]).then(function (texts) {
                var local = parseYandex(texts[0]);
                var en    = texts[1] !== null ? parseYandex(texts[1]) : local;
                showSuggestions(local.map(function (it, i) { return { name: it.name, desc: it.desc, enName: (en[i] || {}).name || it.name }; }));
            }).catch(function () { list.style.display = 'none'; });
        }, 150);
    });
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = ''; if (hidden) hidden.value = ''; clearBtn.style.display = 'none';
            list.style.display = 'none'; list.innerHTML = ''; shown = {};
            if (districtAutoFilled) {
                var d = document.querySelector('[name="district"]');
                if (d) d.value = '';
                districtAutoFilled = false;
            }
            input.focus();
        });
    }
    input.addEventListener('blur', function () { setTimeout(function () { list.style.display = 'none'; }, 200); });
    updateClearBtn();
}());

// ── District autocomplete ───────────────────────────────────────────────────
(function () {
    var input    = document.getElementById('districtInput');
    var list     = document.getElementById('districtSuggestions');
    var clearBtn = document.getElementById('districtClearBtn');
    if (!input || !list) return;
    document.body.appendChild(list);
    var timer = null, lang = '{{ app()->getLocale() }}', shown = {};
    function parseYandex(body) {
        var m = (body || '').trim().match(/suggest\.apply\(([\s\S]+)\)/);
        if (!m) return [];
        try { var data = JSON.parse(m[1]); } catch (e) { return []; }
        return (data.results || []).map(function (item) {
            var title = (item.title || {}).text || '';
            var where = ((item.log_id || {}).where) || {};
            if (!title) return null;
            var parts = (where.name || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
            var desc  = parts.filter(function (p) { return p !== title; }).join(', ');
            return { name: title, desc: desc };
        }).filter(Boolean);
    }
    function makeLi(name, desc) {
        var li = document.createElement('li');
        li.innerHTML = '<span class="city-item-text"><span class="city-item-name">' + name + '</span>' + (desc ? '<span class="city-item-desc">' + desc + '</span>' : '') + '</span>';
        li.addEventListener('mousedown', function (e) {
            e.preventDefault();
            input.value = name;
            if (clearBtn) clearBtn.style.display = 'flex';
            list.style.display = 'none'; list.innerHTML = ''; shown = {};
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
        list.innerHTML = ''; shown = {};
        if (!items.length) { list.style.display = 'none'; return; }
        positionList();
        items.forEach(function (it) {
            if (shown[it.name]) return;
            shown[it.name] = true;
            list.appendChild(makeLi(it.name, it.desc));
        });
        list.style.display = 'block';
    }
    function updateClearBtn() { if (clearBtn) clearBtn.style.display = input.value ? 'flex' : 'none'; }
    input.addEventListener('input', function () {
        updateClearBtn(); clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) { list.style.display = 'none'; list.innerHTML = ''; shown = {}; return; }
        timer = setTimeout(function () {
            var cityCtx = (document.getElementById('cityInput') || {}).value || '';
            var query   = cityCtx ? cityCtx + ' ' + q : q;
            var yLang   = lang === 'en' ? 'en_US' : lang === 'hy' ? 'hy_AM' : 'ru_RU';
            var url = 'https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(query) + '&types=district&results=7&highlight=0&v=9&lang=' + yLang;
            fetch(url).then(function (r) { return r.text(); }).then(function (body) {
                showSuggestions(parseYandex(body));
            }).catch(function () { list.style.display = 'none'; });
        }, 150);
    });
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = ''; clearBtn.style.display = 'none';
            list.style.display = 'none'; list.innerHTML = ''; shown = {};
            input.focus();
        });
    }
    input.addEventListener('blur', function () { setTimeout(function () { list.style.display = 'none'; }, 200); });
    updateClearBtn();
}());

// ── Year built: sync yearBuiltTo ────────────────────────────────────────────
(function () {
    var from = document.getElementById('yearBuiltFrom');
    var to   = document.getElementById('yearBuiltTo');
    if (!from || !to) return;
    from.addEventListener('input', function () {
        if (from.value && !to.value) to.value = {{ date('Y') }};
        if (!from.value) to.value = '';
    });
}());

// ── Number chips (rooms / bathrooms) ───────────────────────────────────────
(function () {
    function initNumChips(containerID, minInputID, maxInputID) {
        var container = document.getElementById(containerID);
        var minInput  = document.getElementById(minInputID);
        var maxInput  = document.getElementById(maxInputID);
        if (!container || !minInput || !maxInput) return;
        var chips  = container.querySelectorAll('.afv2-num-chip');
        var curMin = parseInt(minInput.value) || null;
        var curMax = maxInput.value !== '' ? parseInt(maxInput.value) : null;

        // Restore state on page load
        chips.forEach(function (chip) {
            var v = parseInt(chip.dataset.value);
            if (chip.dataset.plus) {
                if (curMax === null && curMin !== null && curMin <= v) chip.classList.add('active');
            } else {
                if (curMin !== null && v >= curMin && (curMax === null || v <= curMax)) chip.classList.add('active');
            }
        });

        function syncInputs() {
            var active = Array.from(chips).filter(function (c) { return c.classList.contains('active'); });
            if (!active.length) { minInput.value = ''; maxInput.value = ''; return; }
            var plusActive = active.some(function (c) { return !!c.dataset.plus; });
            var vals = active.map(function (c) { return parseInt(c.dataset.value); });
            minInput.value = Math.min.apply(null, vals);
            maxInput.value = plusActive ? '' : Math.max.apply(null, vals);
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                this.classList.toggle('active');
                syncInputs();
            });
        });
    }
    initNumChips('roomChips',     'minRoomsInput',     'maxRoomsInput');
    initNumChips('bathroomChips', 'minBathroomsInput', 'maxBathroomsInput');

    // Select-all buttons for num-chip groups
    document.querySelectorAll('.afv2-numchip-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var container = document.getElementById(btn.dataset.target);
            if (!container) return;
            var chips = container.querySelectorAll('.afv2-num-chip');
            var allActive = Array.from(chips).every(function (c) { return c.classList.contains('active'); });
            chips.forEach(function (c) {
                if (allActive) c.classList.remove('active');
                else c.classList.add('active');
            });
            // sync hidden inputs via click simulation on last chip to trigger syncInputs
            // re-use existing syncInputs by triggering input logic manually
            var plusChip = container.querySelector('[data-plus]');
            var minInput, maxInput;
            if (container.id === 'roomChips') {
                minInput = document.getElementById('minRoomsInput');
                maxInput = document.getElementById('maxRoomsInput');
            } else {
                minInput = document.getElementById('minBathroomsInput');
                maxInput = document.getElementById('maxBathroomsInput');
            }
            if (allActive) {
                if (minInput) minInput.value = '';
                if (maxInput) maxInput.value = '';
            } else {
                var active = Array.from(chips).filter(function (c) { return !c.dataset.plus && c.classList.contains('active'); });
                var vals = active.map(function (c) { return parseInt(c.dataset.value); });
                if (plusChip && plusChip.classList.contains('active')) {
                    if (minInput) minInput.value = plusChip.dataset.value;
                    if (maxInput) maxInput.value = '';
                } else if (vals.length) {
                    if (minInput) minInput.value = Math.min.apply(null, vals);
                    if (maxInput) maxInput.value = Math.max.apply(null, vals);
                }
            }
            btn.classList.toggle('all-selected', !allActive);
            btn.textContent = !allActive ? (btn.dataset.labelDeselect || btn.textContent) : (btn.dataset.labelSelect || btn.textContent);
        });
    });
}());

// ── Land area visibility ────────────────────────────────────────────────────
(function () {
    var landTypes = ['House','Villa','Townhouse','Land','Dacha','Cottage'];
    var row = document.getElementById('landAreaRow');
    var sel = document.getElementById('propertyTypeSelect');
    if (!row || !sel) return;
    function update() { row.style.display = (!sel.value || landTypes.indexOf(sel.value) !== -1) ? '' : 'none'; }
    sel.addEventListener('change', update);
    // Also catch custom-select changes (they fire 'change' on the native select)
    update();
}());

// ── Filter panel toggle ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var btn   = document.getElementById('advFilterToggle');
    var panel = document.getElementById('advFilterPanel');
    if (!btn || !panel) return;

    function openPanel() {
        panel.style.overflow  = 'hidden';
        panel.style.maxHeight = panel.scrollHeight + 'px';
        panel.style.opacity   = '1';
        panel.classList.add('open');
        btn.classList.add('open');
    }
    function closePanel() {
        panel.classList.remove('open');
        btn.classList.remove('open');
        panel.style.overflow  = 'hidden';
        panel.style.maxHeight = panel.scrollHeight + 'px';
        requestAnimationFrame(function () {
            panel.style.maxHeight = '0';
            panel.style.opacity   = '0';
        });
    }

    // After open animation completes → allow custom-select dropdowns to overflow
    panel.addEventListener('transitionend', function (e) {
        if (e.propertyName === 'max-height' && panel.classList.contains('open')) {
            panel.style.maxHeight = 'none';
            panel.style.overflow  = 'visible';
        }
    });

    if (localStorage.getItem('filterOpen') === 'true') {
        panel.style.maxHeight = 'none';
        panel.style.overflow  = 'visible';
        panel.style.opacity   = '1';
        panel.classList.add('open');
        btn.classList.add('open');
    }

    btn.addEventListener('click', function () {
        if (panel.classList.contains('open')) {
            closePanel();
            localStorage.setItem('filterOpen', 'false');
        } else {
            openPanel();
            localStorage.setItem('filterOpen', 'true');
        }
    });

    // Init custom selects inside the panel
    if (typeof window.initCustomSelects === 'function') {
        window.initCustomSelects();
    }

    // Mark filter as ready → removes skeleton
    var filterEl = document.querySelector('.adv-filter-v2');
    if (filterEl) filterEl.classList.add('afv2-ready');
});

// ── Collapsible columns ─────────────────────────────────────────────────────
(function () {
    document.querySelectorAll('.afv2-col-title-wrap').forEach(function (wrap) {
        wrap.addEventListener('click', function () {
            var col = wrap.closest('.afv2-col');
            var body = col.querySelector('.afv2-col-body');
            if (col.classList.contains('collapsed')) {
                // Expanding
                body.style.overflow = 'hidden';
                body.style.opacity = '1';
                body.style.maxHeight = body.scrollHeight + 'px';
                col.classList.remove('collapsed');
                body.addEventListener('transitionend', function onEnd(e) {
                    if (e.propertyName !== 'max-height') return;
                    body.style.maxHeight = 'none';
                    body.style.overflow = 'visible';
                    body.removeEventListener('transitionend', onEnd);
                });
            } else {
                body.style.overflow = 'hidden';
                body.style.maxHeight = body.scrollHeight + 'px';
                requestAnimationFrame(function () {
                    body.style.maxHeight = '0';
                    body.style.opacity = '0';
                    col.classList.add('collapsed');
                });
            }
        });
    });
}());

// ── Select all toggle ───────────────────────────────────────────────────────
(function () {
    function syncBtn(btn) {
        var group = btn.closest('.afv2-group');
        var checkboxes = group.querySelectorAll('input[type="checkbox"]');
        var allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(function (cb) { return cb.checked; });
        btn.classList.toggle('all-selected', allChecked);
        btn.textContent = allChecked ? (btn.dataset.labelDeselect || btn.textContent) : (btn.dataset.labelSelect || btn.textContent);
    }
    document.querySelectorAll('.afv2-select-all-btn').forEach(function (btn) {
        syncBtn(btn);
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var group = btn.closest('.afv2-group');
            var checkboxes = group.querySelectorAll('input[type="checkbox"]');
            var allChecked = Array.from(checkboxes).every(function (cb) { return cb.checked; });
            checkboxes.forEach(function (cb) {
                cb.checked = !allChecked;
                cb.dispatchEvent(new Event('change'));
            });
            syncBtn(btn);
        });
    });
}());
</script>

