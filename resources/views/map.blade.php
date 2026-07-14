@extends('layout.app')
@section('title', __('header.map') . ' — ' . ($workspace['name'] ?? ''))

@section('content')
<x-breadcrumb :title="__('header.map')" />

<section class="py-8">
    <div class="container-x">
        <div class="flex flex-col gap-6 lg:flex-row">
            {{-- Map --}}
            <div class="flex-1">
                <div id="yandex-map" class="relative h-[60vh] min-h-[350px] overflow-hidden rounded-3xl border border-sand md:h-[70vh] md:min-h-[500px]">
                    <div id="map-skeleton" class="absolute inset-0 flex flex-col items-center justify-center bg-sand/60">
                        <div class="animate-pulse flex flex-col items-center gap-4">
                            <svg width="44" height="58" viewBox="0 0 32 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 1C8.82 1 3 6.82 3 14c0 9.25 13 27 13 27S29 23.25 29 14C29 6.82 23.18 1 16 1z" fill="#d1a392"/>
                                <path d="M9.5 15.5L16 9.5l6.5 6v6a.5.5 0 01-.5.5H10a.5.5 0 01-.5-.5v-6z" fill="#fff"/>
                                <rect x="13.5" y="18.5" width="5" height="4" rx="0.8" fill="#d1a392"/>
                            </svg>
                            <div class="h-3 w-28 rounded-full bg-brand-300/60"></div>
                            <div class="h-2 w-20 rounded-full bg-brand-200/60"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="w-full shrink-0 lg:w-80">
                <div id="map-sidebar-scroll" class="max-h-[40vh] space-y-3 overflow-y-auto pr-1 lg:max-h-[70vh]">
                    <p class="sticky top-0 z-10 px-3 py-2 text-xs text-neutral-400 mb-1 rounded-xl bg-panel">
                        {{ __('property.found_on_map') }} <span id="map-count" class="font-semibold text-ink">…</span>
                    </p>
                    {{-- Skeleton cards --}}
                    <div id="map-sidebar-skeleton" class="space-y-3">
                        @for ($i = 0; $i < 4; $i++)
                        <div class="animate-pulse flex gap-3 rounded-2xl border border-sand bg-white p-3">
                            <div class="h-16 w-16 shrink-0 rounded-xl bg-sand"></div>
                            <div class="min-w-0 flex-1 space-y-2 py-1">
                                <div class="h-3 w-3/4 rounded-full bg-sand"></div>
                                <div class="h-2 w-1/2 rounded-full bg-sand"></div>
                                <div class="h-3 w-1/3 rounded-full bg-sand"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    {{-- Real cards injected by JS --}}
                    <div id="map-sidebar-cards" class="hidden space-y-3"></div>
                </div>
            </aside>
        </div>
    </div>
</section>

@push('styles')
<style>
[class*="gototech"] { display: none !important; }
#map-sidebar-scroll { scrollbar-width: thin; scrollbar-color: #d6c4b8 transparent; }
#map-sidebar-scroll::-webkit-scrollbar { width: 4px; }
#map-sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
#map-sidebar-scroll::-webkit-scrollbar-thumb { background: #d6c4b8; border-radius: 99px; }
#map-sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #a6644f; }

/* Custom ymaps balloon card */
.ymap-card {
    display: flex;
    gap: 10px;
    align-items: center;
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e8ddd7;
    padding: 10px;
    width: 230px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.13);
    text-decoration: none;
    cursor: pointer;
    position: relative;
}
.ymap-card:hover .ymap-card__title { color: #a6644f; }
.ymap-card__img {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}
.ymap-card__img--loading { display: none; }
.ymap-card__img-skeleton {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    flex-shrink: 0;
    background: linear-gradient(90deg, #e8ddd7 25%, #f5ede9 50%, #e8ddd7 75%);
    background-size: 200% 100%;
    animation: ymap-shimmer 1.2s infinite linear;
}
@keyframes ymap-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.ymap-card__no-img {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    background: #e8ddd7;
    flex-shrink: 0;
}
.ymap-card__body { min-width: 0; flex: 1; }
.ymap-card__title {
    font-size: 12px;
    font-weight: 600;
    color: #1a1a1a;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.35;
    transition: color .15s;
}
.ymap-card__price {
    margin-top: 4px;
    font-size: 13px;
    font-weight: 700;
    color: #a6644f;
}

/* Hide ymaps balloon chrome — we render everything ourselves */
.ymaps-2-1-79-balloon,
.ymaps-2-1-79-balloon__layout,
.ymaps-2-1-79-balloon__content,
.ymaps-2-1-79-balloon__tail,
[class*="ymaps-"][class*="balloon__tail"],
[class*="ymaps-"][class*="balloon__layout"],
[class*="ymaps-"][class*="balloon__content"] {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
const MAP_DATA_URL = '{{ route("map.data",   ["locale" => app()->getLocale()]) }}';
const COORDS_URL   = '{{ route("map.coords", ["locale" => app()->getLocale()]) }}';
const YANDEX_KEY   = '{{ $yandexKey }}';
const IS_DESKTOP   = window.innerWidth >= 1024;

const HOUSE_ICON = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(
'<svg xmlns="http://www.w3.org/2000/svg" width="32" height="42" viewBox="0 0 32 42">' +
'<defs><filter id="d"><feDropShadow dx="0" dy="1" stdDeviation="1.5" flood-opacity="0.3"/></filter></defs>' +
'<path d="M16 1C8.82 1 3 6.82 3 14c0 9.25 13 27 13 27S29 23.25 29 14C29 6.82 23.18 1 16 1z" fill="#a6644f" filter="url(#d)"/>' +
'<path d="M16 1C8.82 1 3 6.82 3 14c0 9.25 13 27 13 27S29 23.25 29 14C29 6.82 23.18 1 16 1z" fill="none" stroke="#fff" stroke-width="1" stroke-opacity="0.3"/>' +
'<path d="M9.5 15.5L16 9.5l6.5 6v6a.5.5 0 01-.5.5H10a.5.5 0 01-.5-.5v-6z" fill="#fff"/>' +
'<rect x="13.5" y="18.5" width="5" height="4" rx="0.8" fill="#a6644f"/>' +
'</svg>'
)}`;

const HOUSE_ICON_HOVER = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(
'<svg xmlns="http://www.w3.org/2000/svg" width="38" height="50" viewBox="0 0 38 50">' +
'<defs><filter id="d"><feDropShadow dx="0" dy="2" stdDeviation="2.5" flood-opacity="0.4"/></filter></defs>' +
'<path d="M19 1C10.16 1 3 8.16 3 17c0 11 16 32 16 32S35 28 35 17C35 8.16 27.84 1 19 1z" fill="#7a3d28" filter="url(#d)"/>' +
'<path d="M19 1C10.16 1 3 8.16 3 17c0 11 16 32 16 32S35 28 35 17C35 8.16 27.84 1 19 1z" fill="none" stroke="#fff" stroke-width="1" stroke-opacity="0.3"/>' +
'<path d="M11 18.5L19 11l8 7.5V26a.5.5 0 01-.5.5h-15a.5.5 0 01-.5-.5v-7.5z" fill="#fff"/>' +
'<rect x="16" y="21" width="6" height="5.5" rx="1" fill="#7a3d28"/>' +
'</svg>'
)}`;

let balloonCloseTimer = null;
let BalloonLayout     = null;

function getBalloonLayout() {
    if (BalloonLayout) return BalloonLayout;

    BalloonLayout = ymaps.templateLayoutFactory.createClass('$[properties.bHtml]', {
        build() {
            this.constructor.superclass.build.call(this);
            const el = this.getElement();
            if (!el) return;

            // Image skeleton: show shimmer until photo loads
            const img = el.querySelector('img.ymap-card__img');
            if (img) {
                const skeleton = document.createElement('div');
                skeleton.className = 'ymap-card__img-skeleton';
                img.insertAdjacentElement('beforebegin', skeleton);
                img.classList.add('ymap-card__img--loading');
                const onLoaded = () => { skeleton.remove(); img.classList.remove('ymap-card__img--loading'); };
                if (img.complete && img.naturalWidth) {
                    onLoaded();
                } else {
                    img.addEventListener('load',  onLoaded, { once: true });
                    img.addEventListener('error', onLoaded, { once: true });
                }
            }


            if (IS_DESKTOP) {
                // Keep balloon open when mouse moves from pin to balloon
                el.addEventListener('mouseenter', () => clearTimeout(balloonCloseTimer));
                el.addEventListener('mouseleave', () => {
                    balloonCloseTimer = setTimeout(() => {
                        const geo = this.getData().geoObject;
                        if (geo) geo.balloon.close();
                    }, 150);
                });
            }

            // Let the <a> tag handle navigation naturally (Turbo Drive picks it up).
            // stopPropagation prevents ymaps from swallowing the click.
            el.addEventListener('click', (e) => e.stopPropagation());
        },
    });

    return BalloonLayout;
}

const CARD_W = 246; // balloon card width + gap
let hoverOpenTimer = null;
const _seenPins = new WeakSet(); // pins that have been opened at least once

function openBalloon(map, pm) {
    const mapEl = document.getElementById('yandex-map');
    if (mapEl) {
        try {
            const coords  = pm.geometry.getCoordinates();
            const globalPx = map.options.get('projection').toGlobalPixels(coords, map.getZoom());
            const pagePx   = map.converter.globalToPage(globalPx);
            const mapRect  = mapEl.getBoundingClientRect();
            const pinX     = pagePx[0] - mapRect.left;
            // If balloon would overflow right edge → open to the left of the pin
            const offset = (pinX + CARD_W > mapEl.offsetWidth - 8)
                ? [-(CARD_W - 3), -52]
                : [3, -52];
            pm.options.set('balloonOffset', offset);
        } catch (e) { /* fallback to default offset */ }
    }
    pm.balloon.open();
}

function buildBalloonHtml(item) {
    const imgHtml = item.img
        ? `<img src="${item.img}" alt="" class="ymap-card__img">`
        : `<div class="ymap-card__no-img"></div>`;
    const fxStore = window.Alpine && window.Alpine.store('fx');
    const displayPrice = (fxStore && item.rawPrice)
        ? fxStore.format(item.rawPrice, item.rawCurrency || 'AMD')
        : item.price;
    const priceHtml = displayPrice ? `<p class="ymap-card__price">${displayPrice}</p>` : '';
    return `<a class="ymap-card" href="${item.url}">${imgHtml}<div class="ymap-card__body"><p class="ymap-card__title">${item.title}</p>${priceHtml}</div></a>`;
}

function makePlacemark(item) {
    const pm = new ymaps.Placemark(
        [item.lat, item.lng],
        { bUrl: item.url, bHtml: buildBalloonHtml(item) },
        {
            iconLayout:            'default#image',
            iconImageHref:         HOUSE_ICON,
            iconImageSize:         [32, 42],
            iconImageOffset:       [-16, -42],
            balloonLayout:         getBalloonLayout(),
            balloonAutoPan:        false,
            balloonCloseButton:    false,
            hideIconOnBalloonOpen: false,
            balloonOffset:         [3, -52],
        }
    );
    return pm;
}

function bindPinEvents(map, pm) {
    if (IS_DESKTOP) {
        pm.events.add('mouseenter', () => {
            pm.options.set('iconImageHref',   HOUSE_ICON_HOVER);
            pm.options.set('iconImageSize',   [38, 50]);
            pm.options.set('iconImageOffset', [-19, -50]);
            clearTimeout(balloonCloseTimer);
            clearTimeout(hoverOpenTimer);
            const delay = _seenPins.has(pm) ? 0 : 300;
            hoverOpenTimer = setTimeout(() => {
                _seenPins.add(pm);
                openBalloon(map, pm);
            }, delay);
        });
        pm.events.add('mouseleave', () => {
            clearTimeout(hoverOpenTimer);
            pm.options.set('iconImageHref',   HOUSE_ICON);
            pm.options.set('iconImageSize',   [32, 42]);
            pm.options.set('iconImageOffset', [-16, -42]);
            balloonCloseTimer = setTimeout(() => pm.balloon.close(), 150);
        });
    } else {
        pm.events.add('click', (e) => {
            e.stopPropagation();
            pm.balloon.isOpen() ? pm.balloon.close() : openBalloon(map, pm);
        });
    }
}

function makeClusterer(map) {
    // Custom cluster icon — branded circle with count
    const ClusterLayout = ymaps.templateLayoutFactory.createClass(
        '<div style="' +
            'width:44px;height:44px;border-radius:50%;' +
            'background:#a6644f;color:#fff;' +
            'display:flex;align-items:center;justify-content:center;' +
            'font-size:14px;font-weight:700;font-family:inherit;' +
            'box-shadow:0 3px 12px rgba(0,0,0,0.28);' +
            'border:3px solid #fff;cursor:pointer;' +
        '">{{ properties.geoObjects.length }}</div>'
    );

    const clusterer = new ymaps.Clusterer({
        clusterIconLayout:        ClusterLayout,
        clusterIconShape:         { type: 'Circle', coordinates: [0, 0], radius: 22 },
        clusterDisableClickZoom:  false, // click → zoom in to split cluster
        groupByCoordinates:       false,
        clusterHideIconOnBalloonOpen: false,
        clusterBalloonPanelMaxMapArea: 0,
    });

    // After zooming into a cluster, bind hover/click events to newly visible pins
    clusterer.events.add('click', (e) => {
        const target = e.get('target');
        // If it's a cluster (not a single placemark), ymaps handles zoom-in automatically
        // If it's a single placemark inside a cluster, open balloon
        if (target instanceof ymaps.Placemark) {
            e.stopPropagation();
            if (!IS_DESKTOP) {
                target.balloon.isOpen() ? target.balloon.close() : openBalloon(map, target);
            }
        }
    });

    return clusterer;
}

function addPin(map, item, clusterer) {
    const pm = makePlacemark(item);
    bindPinEvents(map, pm);
    clusterer.add(pm);
}

function addSidebarCard(item) {
    const cards = document.getElementById('map-sidebar-cards');
    if (!cards) return;
    const img = item.img
        ? `<img src="${item.img}" alt="" class="h-16 w-16 shrink-0 rounded-xl object-cover">`
        : `<div class="grid h-16 w-16 shrink-0 place-items-center rounded-xl bg-sand text-brand-200"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg></div>`;
    const fxStore = window.Alpine && window.Alpine.store('fx');
    const sidebarPrice = (fxStore && item.rawPrice)
        ? fxStore.format(item.rawPrice, item.rawCurrency || 'AMD')
        : item.price;
    const price = sidebarPrice ? `<p class="mt-1 font-display text-sm font-bold text-brand-700">${sidebarPrice}</p>` : '';
    const a = document.createElement('a');
    a.href      = item.url;
    a.className = 'group flex gap-3 rounded-2xl border border-sand bg-white p-3 transition hover:border-brand-400 hover:shadow-md';
    a.innerHTML = `${img}<div class="min-w-0"><p class="text-sm font-semibold text-ink line-clamp-1 group-hover:text-brand-700">${item.title}</p><p class="mt-0.5 text-xs text-neutral-500 line-clamp-1">${item.city}</p>${price}</div>`;
    cards.appendChild(a);
}

function updateCount(n) {
    const el = document.getElementById('map-count');
    if (el) el.textContent = n;
}

const MAP_VIEWPORT_KEY = 'te_map_viewport';

function saveViewport(map) {
    try {
        const c = map.getCenter(), z = map.getZoom();
        sessionStorage.setItem(MAP_VIEWPORT_KEY, JSON.stringify({ lat: c[0], lng: c[1], zoom: z }));
    } catch (e) {}
}

function loadViewport() {
    try {
        const v = sessionStorage.getItem(MAP_VIEWPORT_KEY);
        return v ? JSON.parse(v) : null;
    } catch (e) { return null; }
}

function initMapWithData(ymap, items, pending) {
    document.getElementById('map-skeleton')?.remove();
    document.getElementById('map-sidebar-skeleton')?.remove();

    const cardsEl   = document.getElementById('map-sidebar-cards');
    const viewport  = loadViewport();

    // Restore saved viewport if available, otherwise fit to pins
    const center = viewport ? [viewport.lat, viewport.lng]
        : items.length ? [items[0].lat, items[0].lng] : [40.1872, 44.5152];
    const zoom   = viewport ? viewport.zoom : (items.length ? 12 : 11);

    const map = new ymaps.Map('yandex-map', {
        center,
        zoom,
        controls:  ['zoomControl', 'fullscreenControl'],
        behaviors: ['default', 'scrollZoom'],
    });
    map.options.set('balloonAutoPan', false);

    // Clusterer — groups nearby pins, auto-splits on zoom-in
    const clusterer = makeClusterer(map);
    map.geoObjects.add(clusterer);

    // Save viewport on every pan/zoom so tiles for visited areas stay in browser cache
    map.events.add('boundschange', () => saveViewport(map));

    // Mobile: tap on map background closes any open balloon
    if (!IS_DESKTOP) {
        map.events.add('click', () => {
            clusterer.getGeoObjects().forEach(o => o.balloon && o.balloon.close());
        });
    }

    let totalCount = items.length;
    updateCount(totalCount);
    items.forEach(item => { addPin(map, item, clusterer); addSidebarCard(item); });

    if (cardsEl) {
        if (items.length === 0 && pending.length === 0) {
            cardsEl.innerHTML = `<div class="rounded-2xl border border-dashed border-sand bg-panel py-12 text-center text-sm text-neutral-500">{{ __('index.coming_soon') }}</div>`;
        }
        cardsEl.classList.remove('hidden');
    }

    // Only auto-fit bounds on first visit (no saved viewport)
    if (!viewport && items.length > 1) {
        map.setBounds(clusterer.getBounds(), { checkZoomRange: true, zoomMargin: 50 });
    }

    // Poll for pending items that are being geocoded in background
    if (pending.length > 0) {
        const pendingBySlug = Object.fromEntries(pending.map(p => [p.slug, p]));
        let remainingSlugs = pending.map(p => p.slug).filter(Boolean);
        let attempts = 0;
        const MAX_ATTEMPTS = 20;

        const pollCoords = () => {
            if (!remainingSlugs.length || attempts >= MAX_ATTEMPTS || !window._mapPageInstance) return;
            attempts++;
            const coordsParams = new URLSearchParams();
            remainingSlugs.forEach(s => coordsParams.append('slugs[]', s));
            fetch(COORDS_URL + '?' + coordsParams.toString())
            .then(r => r.json())
            .then(data => {
                const resolved = data.coords || data || {};
                const newSlugs = [];
                remainingSlugs.forEach(slug => {
                    if (resolved[slug]) {
                        const item = { ...pendingBySlug[slug], lat: resolved[slug].lat, lng: resolved[slug].lng };
                        addPin(map, item, clusterer);
                        addSidebarCard(item);
                        totalCount++;
                        updateCount(totalCount);
                        if (totalCount === 1) {
                            map.setCenter([item.lat, item.lng], 12);
                        }
                    } else {
                        newSlugs.push(slug);
                    }
                });
                remainingSlugs = newSlugs;
                if (remainingSlugs.length && attempts < MAX_ATTEMPTS) {
                    setTimeout(pollCoords, 5000);
                }
            })
            .catch(() => { if (attempts < MAX_ATTEMPTS) setTimeout(pollCoords, 8000); });
        };

        setTimeout(pollCoords, 4000);
    }

    const resetMapLayout = () => {
        setTimeout(() => {
            const mapEl = document.getElementById('yandex-map');
            if (mapEl) {
                mapEl.style.cssText = '';
                mapEl.querySelectorAll('[style]').forEach(el => {
                    el.style.width = ''; el.style.height = '';
                    el.style.position = ''; el.style.top = '';
                    el.style.left = ''; el.style.zIndex = '';
                });
            }
            map.container.fitToViewport();
        }, 100);
    };

    map.controls.get('fullscreenControl').events.add('fullscreenexit', resetMapLayout);
    document.addEventListener('fullscreenchange',       () => { if (!document.fullscreenElement)       resetMapLayout(); });
    document.addEventListener('webkitfullscreenchange', () => { if (!document.webkitFullscreenElement) resetMapLayout(); });

    window._mapPageInstance = map;
}

function loadYmaps() {
    return new Promise((resolve) => {
        if (window.ymaps) { ymaps.ready(resolve); return; }
        if (document.querySelector('script[src*="api-maps.yandex.ru"]')) {
            window.__ymapsQueue = window.__ymapsQueue || [];
            window.__ymapsQueue.push(resolve);
            return;
        }
        const script = document.createElement('script');
        script.src = `https://api-maps.yandex.ru/2.1/?apikey=${YANDEX_KEY}&lang={{ app()->getLocale() === 'en' ? 'en_US' : 'ru_RU' }}`;
        script.onload = function () {
            ymaps.ready(() => {
                resolve();
                (window.__ymapsQueue || []).forEach(fn => fn());
                window.__ymapsQueue = [];
            });
        };
        document.head.appendChild(script);
    });
}

function fetchMapData() {
    return fetch(MAP_DATA_URL)
        .then(r => r.json())
        .catch(() => ({ items: [], pending: [] }));
}

// Preload property images — store refs to prevent GC, deduplicate by URL
const _imgCache = {};
function preloadImages(items) {
    items.forEach(item => {
        if (item.img && !_imgCache[item.img]) {
            const img = new Image();
            img.src = item.img;
            _imgCache[item.img] = img;
        }
    });
}

Promise.all([loadYmaps(), fetchMapData()]).then(([, data]) => {
    const items = data.items || [];
    preloadImages(items);
    initMapWithData(null, items, data.pending || []);
});
</script>
@endpush
@endsection
