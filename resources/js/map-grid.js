/*
 * Properties map — Yandex Maps + clustering + bounds-based grid filtering.
 * Markers come from the /map/locations AJAX call (see map.blade.php → applyMapLocations);
 * properties without coordinates are placed near their city (deterministically per slug).
 */

// Resolve locale-aware property base URL once
function _propertyBase() {
  if (window.propertyBaseUrl) return window.propertyBaseUrl;
  var m = window.location.pathname.match(/^\/(en|ru|hy)\//);
  return m ? '/' + m[1] + '/property/' : '/property/';
}

var map, clusterer;
var PropertyIconLayout, ClusterIconLayout;

// The marker whose card overlay is currently pinned open (clicked).
var _pinnedMark = null;
var _suppressMapClick = false; // set on a marker click so the map-click handler doesn't unpin it

// Hover preview (mouse devices only): hovering a marker shows its card in the overlay;
// a click pins it open. On touch there's no hover, so click is the only interaction.
var _hoverTimer = null;
// Hover only on desktops ≥1024px that have a hover-capable pointer available.
// Use `any-hover` (not `hover`): a touchscreen laptop with a mouse reports the PRIMARY
// pointer as touch (`hover: none`), which would wrongly disable hover — `any-hover: hover`
// is true whenever a mouse is present, and still false on pure-touch tablets/phones.
// Checked live so resizing across the breakpoint is respected.
function _canHover() { return !!(window.matchMedia && window.matchMedia('(min-width: 1024px) and (any-hover: hover)').matches); }
function _overlayEl() { return document.getElementById('map-card-overlay'); }
var _shownCoords = null; // geo coords of the marker the tooltip is anchored to
function _showCard(loc, coords) {
  var o = _overlayEl(); if (!o) return;
  o.innerHTML = getMiniCardHtml(loc);
  o.style.display = 'block';
  if (coords) _shownCoords = coords;
  _positionCard();
}
// Anchor the tooltip just above the marker. globalToPage gives PAGE-relative pixels, so
// subtract the map element's page position to get coords in the overlay's parent (the map
// element fills that parent from 0,0).
function _positionCard() {
  var o = _overlayEl(); if (!o || !map || !_shownCoords) return;
  var page = map.converter.globalToPage(map.options.get('projection').toGlobalPixels(_shownCoords, map.getZoom()));
  var mapEl = document.getElementById('map');
  var r = mapEl.getBoundingClientRect();
  o.style.left = (page[0] - r.left - window.pageXOffset) + 'px';
  o.style.top  = (page[1] - r.top  - window.pageYOffset) + 'px';
}
function _hideCard() {
  var o = _overlayEl(); if (!o) return;
  o.style.display = 'none';
  o.innerHTML = '';
  _shownCoords = null;
}
// Small delay so moving the cursor from the marker onto the card doesn't close it.
function _scheduleHide() { clearTimeout(_hoverTimer); _hoverTimer = setTimeout(function () { if (!_pinnedMark) _hideCard(); }, 200); }
function _cancelHide() { clearTimeout(_hoverTimer); }

// cityCoords: city_name → [lat,lng] | null | undefined (not geocoded yet)
var cityCoords = {};

// ─── Known city coordinates (avoids geocoder API dependency) ─────────────────
var knownCities = {
  'Yerevan':[40.1872,44.5152], 'Erevan':[40.1872,44.5152],
  'Gyumri':[40.7929,43.8465], 'Vanadzor':[40.8128,44.4883],
  'Abovyan':[40.2736,44.6267], 'Kapan':[39.2075,46.4056],
  'Hrazdan':[40.4978,44.7628], 'Armavir':[40.1546,44.0383],
  'Vagharshapat':[40.1631,44.2908], 'Echmiadzin':[40.1631,44.2908],
  'Ejmiatsin':[40.1631,44.2908], 'Charentsavan':[40.4089,44.6383],
  'Sevan':[40.5344,44.9483], 'Goris':[39.5111,46.3408],
  'Dilijan':[40.7414,44.8622], 'Ijevan':[40.8786,45.1483],
  'Artashat':[39.9533,44.5506], 'Ashtarak':[40.2992,44.3619],
  'Tsaghkadzor':[40.5317,44.7250], 'Jermuk':[39.8422,45.6697],
  'Stepanavan':[41.0050,44.3856], 'Sisian':[39.5289,46.0322],
  'Masis':[39.9950,44.4508], 'Ararat':[39.8300,44.7000],
  'Meghri':[38.9025,46.2444], 'Alaverdi':[41.0972,44.6833],
  'Martuni':[40.1397,45.3047], 'Gavar':[40.3539,45.1264],
  'Yeghegnadzor':[39.7631,45.3328], 'Byureghavan':[40.3133,44.5983],
  'Nor Hachn':[40.3358,44.5883], 'Metsamor':[40.1406,44.1167]
};

// ─── Geocode via Nominatim (fallback when knownCities misses) ────────────────
function geocodeViaNominatim(city, callback) {
  fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(city + ', Armenia'))
    .then(function(r) { return r.json(); })
    .then(function(data) {
      callback(data && data.length > 0 ? [parseFloat(data[0].lat), parseFloat(data[0].lon)] : null);
    })
    .catch(function() { callback(null); });
}

// ─── Resolve city → coords: knownCities (case-insensitive) first, then Nominatim ─
function geocodeCity(city, callback) {
  if (knownCities[city]) { callback(knownCities[city]); return; }
  var lower = city.toLowerCase();
  var keys = Object.keys(knownCities);
  for (var i = 0; i < keys.length; i++) {
    if (keys[i].toLowerCase() === lower) { callback(knownCities[keys[i]]); return; }
  }
  geocodeViaNominatim(city, callback);
}

// markerCoordsBySlug: slug → [lat,lng] — actual marker position per property
var markerCoordsBySlug = {};
// locBySlug: slug → location object (for card→marker highlight)
var locBySlug = {};
// placemarkBySlug: slug → placemark (to highlight the pin element on card hover)
var placemarkBySlug = {};

// Locations are loaded via AJAX after the map inits (see map.blade.php). If they arrive
// before the Yandex map is ready, stash them here and apply once init finishes.
var pendingLocations = null;

// 'loading' = geocoding in progress, don't filter yet; 'ready' = filter on every map move
var geocodingState = 'loading';

// ─── Price in the active display currency (falls back to the server-rendered string) ──
function _locPrice(loc) {
  if (window.CurrencyManager && loc && loc.priceCurrency != null && loc.priceAmount != null) {
    return window.CurrencyManager.format(loc.priceAmount, loc.priceCurrency);
  }
  return (loc && loc.price) || '';
}

// ─── Mini card HTML shown in the tooltip overlay on marker hover (or tap) ─────
function getMiniCardHtml(loc) {
  var imgSrc = loc.image || '/build/img/buy/buy-grid-img-01.jpg';
  var url = _propertyBase() + loc.slug;
  var type = loc.type || loc.category || '';
  var badge = function (text, bg) {
    return '<span style="background:' + bg + ';color:#fff;font-size:10px;font-weight:600;' +
      'padding:2px 7px;border-radius:20px;line-height:1.25;white-space:nowrap;">' + text + '</span>';
  };
  var badges = (type ? badge(type, '#6842EF') : '') + (loc.deal ? badge(loc.deal, loc.dealBg || '#198754') : '');
  // Colours use theme CSS variables so the card follows dark/light mode.
  return '<a href="' + url + '" style="display:block;width:212px;background:var(--white,#fff);border-radius:12px;' +
    'overflow:hidden;box-shadow:0 10px 28px rgba(0,0,0,.35);font-family:Inter,system-ui,sans-serif;' +
    'text-decoration:none;color:inherit;cursor:pointer;border:1px solid var(--gray-200,rgba(0,0,0,.06));">' +
    '<div style="position:relative;width:100%;aspect-ratio:2/1;background:var(--gray-100,#eef0f4);">' +
      '<img src="' + imgSrc + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">' +
      (badges ? '<div style="position:absolute;top:8px;left:8px;display:flex;gap:4px;flex-wrap:wrap;">' + badges + '</div>' : '') +
    '</div>' +
    '<div style="padding:9px 11px;">' +
      '<div style="font-size:15px;font-weight:700;color:var(--primary,#0e7d63);margin-bottom:3px;">' + _locPrice(loc) + '</div>' +
      '<div style="font-size:12px;font-weight:500;color:var(--dark,#222);line-height:1.35;' +
        'display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + (loc.title || '') + '</div>' +
    '</div>' +
  '</a>';
}

// ─── Deterministic per-slug offset — properties without coordinates scatter around
// their city, but the SAME property always lands on the SAME spot (no jumping on
// re-render, and stable bounds filtering). ───────────────────────────────────
function _slugOffset(seed, spread) {
  var h = 5381;
  seed = String(seed || '');
  for (var i = 0; i < seed.length; i++) h = ((h << 5) + h + seed.charCodeAt(i)) | 0;
  var ax = ((h & 0xffff) / 0xffff) - 0.5;
  var ay = (((h >>> 16) & 0xffff) / 0xffff) - 0.5;
  return [ax * spread, ay * spread];
}

// ─── Create custom icon layouts (called once after ymaps.ready) ──────────────
function createLayouts() {
  if (PropertyIconLayout) return;

  // Single property marker — red teardrop with a white house icon.
  // Hover/click are wired per-placemark via Yandex events (see placeMarkerForLoc):
  // Yandex renders an "events pane" ON TOP of the marker DOM and hit-tests against the
  // placemark's iconShape, so native DOM listeners on the marker element never receive
  // the pointer — the geoObject's own events are the only reliable channel.
  PropertyIconLayout = ymaps.templateLayoutFactory.createClass(
    '<div class="tp-pin-wrap" style="position:relative;width:46px;height:60px;cursor:pointer;">' +
    '<svg width="46" height="60" viewBox="0 0 46 60" fill="none" xmlns="http://www.w3.org/2000/svg" ' +
      'style="filter:drop-shadow(1px 4px 7px rgba(0,0,0,.38))">' +
      '<path d="M23,57 C17,47 2,36 2,22 A21,21 0 1,1 44,22 C44,36 29,47 23,57Z" fill="#E8443C"/>' +
      '<circle cx="23" cy="22" r="13" fill="#C0312A"/>' +
      '<circle cx="23" cy="22" r="9" fill="#FFFFFF"/>' +
      '<path d="M23,16 L18,21 L18,27 L21.5,27 L21.5,24 L24.5,24 L24.5,27 L28,27 L28,21 Z" fill="#C0312A"/>' +
    '</svg>' +
    // Price label shown under the pin when zoomed in (toggled by .map-show-prices on #map).
    '<div class="tp-pin-price">{{ properties.price }}</div>' +
    '</div>'
  );

  // Cluster marker — larger pin with the count in the white circle
  ClusterIconLayout = ymaps.templateLayoutFactory.createClass(
    '<div style="width:52px;height:68px;cursor:pointer;' +
    'margin:-65px 0 0 -26px;' +
    'filter:drop-shadow(1px 4px 7px rgba(0,0,0,.38))">' +
    '<svg width="52" height="68" viewBox="0 0 52 68" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      '<path d="M26,65 C20,53 2,42 2,26 A24,24 0 1,1 50,26 C50,42 32,53 26,65Z" fill="#E8443C"/>' +
      '<circle cx="26" cy="26" r="16" fill="#C0312A"/>' +
      '<circle cx="26" cy="26" r="10" fill="#FFFFFF"/>' +
      '<text x="26" y="30" text-anchor="middle" fill="#C0312A"' +
      '  font-size="12" font-weight="800" font-family="Arial,sans-serif">' +
      '{{ properties.geoObjects.length }}</text>' +
    '</svg>' +
    '</div>'
  );
}

// ─── Numbered pagination over the in-bounds cards ────────────────────────────
var MAP_PAGE_SIZE = 12;
var mapPage = 1;

// Show the price label under each (non-clustered) marker from this zoom level up
// (higher zoom = closer; 15 ≈ street level).
var PRICE_ZOOM = 15;
function _syncPriceLabels() {
  if (!map) return;
  var el = document.getElementById('map');
  if (el) el.classList.toggle('map-show-prices', map.getZoom() >= PRICE_ZOOM);
}

// ─── Filter cards by the current map bounds, then paginate them by page number ─
function filterCardsByBounds(bounds) {
  if (!bounds || geocodingState !== 'ready') return;

  var cards = document.querySelectorAll('#prop-grid > div[data-slug]');
  var inBounds = [];

  cards.forEach(function(card) {
    var slug = card.dataset.slug || '';
    var coords = slug ? markerCoordsBySlug[slug] : null;
    var ok = coords &&
      coords[0] >= bounds[0][0] && coords[0] <= bounds[1][0] &&
      coords[1] >= bounds[0][1] && coords[1] <= bounds[1][1];
    if (ok) { inBounds.push(card); }
    else { card.classList.add('map-card-hidden'); card.classList.remove('d-none'); }
  });

  var totalPages = Math.max(1, Math.ceil(inBounds.length / MAP_PAGE_SIZE));
  if (mapPage > totalPages) mapPage = totalPages;
  var startIdx = (mapPage - 1) * MAP_PAGE_SIZE;

  inBounds.forEach(function(card, i) {
    card.classList.remove('map-card-hidden');
    card.classList.toggle('d-none', i < startIdx || i >= startIdx + MAP_PAGE_SIZE);
  });

  var el = document.getElementById('result-loaded');
  if (el) el.textContent = inBounds.length;
  var el2 = document.getElementById('map-count-num');       // always-visible panel/sheet count
  if (el2) el2.textContent = inBounds.length;

  var empty = document.getElementById('map-empty');
  if (empty) empty.style.display = (inBounds.length === 0 && cards.length > 0) ? 'block' : 'none';

  var pg = document.getElementById('mapPagination');
  if (pg && typeof window.renderPagination === 'function') {
    window.renderPagination(pg, mapPage, totalPages, function(n) {
      mapPage = n;
      if (map) filterCardsByBounds(map.getBounds());
      var body = document.getElementById('mapPanelBody');
      if (body) body.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
}

// ─── Card → marker highlight (hovering a card focuses its pin) ────────────────
var _hlSlug = null;
window.mapHighlightSlug = function (slug) {
  if (!map || !slug || slug === _hlSlug) return;
  window.mapClearHighlight();
  _hlSlug = slug;
  var pm = placemarkBySlug[slug];
  var coords = markerCoordsBySlug[slug];
  var loc = locBySlug[slug];
  if (pm) pm.options.set('zIndex', 10000);
  // Bring the marker into view and show its card if the map has real hover capability off.
  if (coords && loc) {
    var b = map.getBounds();
    var inView = b && coords[0] >= b[0][0] && coords[0] <= b[1][0] && coords[1] >= b[0][1] && coords[1] <= b[1][1];
    if (!inView) map.panTo(coords, { flying: true, duration: 350 });
    _cancelHide(); _showCard(loc, coords);
  }
};
window.mapClearHighlight = function () {
  if (_hlSlug && placemarkBySlug[_hlSlug]) placemarkBySlug[_hlSlug].options.set('zIndex', undefined);
  _hlSlug = null;
  if (!_pinnedMark) _scheduleHide();
};

// ─── Zoom the map out one step (empty-state helper) ──────────────────────────
window.mapZoomOut = function () { if (map) map.setZoom(Math.max(3, map.getZoom() - 2), { duration: 300 }); };

// ─── Place a single marker ───────────────────────────────────────────────────
function placeMarkerForLoc(loc, cityCenter) {
  var coords;
  if (loc.lat && loc.lng) {
    coords = [loc.lat, loc.lng];
  } else {
    // No coords → deterministic scatter around the city center (stable per slug)
    var off = _slugOffset(loc.slug, 0.008);
    coords = [cityCenter[0] + off[0], cityCenter[1] + off[1]];
  }
  if (loc.slug) { markerCoordsBySlug[loc.slug] = coords; locBySlug[loc.slug] = loc; }

  var placemark = new ymaps.Placemark(
    coords,
    { slug: loc.slug, price: _locPrice(loc) },
    {
      iconLayout: PropertyIconLayout,
      iconOffset: [-23, -57],
      // iconShape is in the layout's own coordinate system (same as the 46×60 SVG viewBox,
      // 0,0 = top-left) — NOT anchor-relative. This rectangle covers the whole visible pin
      // so hovering anywhere on it works; being off earlier is why hover only fired above it.
      iconShape: { type: 'Rectangle', coordinates: [[2, 1], [44, 57]] }
    }
  );
  // Desktop: hover shows the tooltip card anchored at the marker, click opens the
  // property page. Touch: tap toggles the card (the card itself is a link → tap it to
  // open the page). Handled via the placemark's own Yandex events (the only channel that
  // receives pointer input — see PropertyIconLayout).
  placemark.events.add('mouseenter', function () {
    if (_canHover() && !_pinnedMark) { _cancelHide(); _showCard(loc, coords); }
  });
  placemark.events.add('mouseleave', function () {
    if (_canHover() && !_pinnedMark) _scheduleHide();
  });
  placemark.events.add('click', function () {
    if (_canHover()) {
      window.location.href = _propertyBase() + loc.slug; // desktop: go straight to the object
      return;
    }
    _suppressMapClick = true; // touch: show/hide the card (tap the card to navigate)
    if (_pinnedMark === placemark) { _pinnedMark = null; _hideCard(); }
    else { _pinnedMark = placemark; _cancelHide(); _showCard(loc, coords); }
  });
  if (loc.slug) placemarkBySlug[loc.slug] = placemark;
  clusterer.add(placemark);
}

// ─── Geocode unique cities, place markers, then unlock the bounds filter ─────
function addMarkersFromLocations(apiLocations) {
  function done() {
    geocodingState = 'ready';
    if (map) filterCardsByBounds(map.getBounds());
  }
  if (!apiLocations || apiLocations.length === 0) { done(); return; }

  // Properties with their own coordinates are placed immediately.
  var needsGeocoding = [];
  apiLocations.forEach(function(loc) {
    if (loc.lat && loc.lng) placeMarkerForLoc(loc, [loc.lat, loc.lng]);
    else needsGeocoding.push(loc);
  });

  if (needsGeocoding.length === 0) { done(); return; }

  // Group the rest by city.
  var byCityMap = {};
  needsGeocoding.forEach(function(loc) {
    var city = loc.city || 'Yerevan';
    (byCityMap[city] = byCityMap[city] || []).push(loc);
  });

  // Place already-geocoded cities immediately.
  Object.keys(byCityMap).forEach(function(city) {
    if ((city in cityCoords) && cityCoords[city]) {
      byCityMap[city].forEach(function(loc) { placeMarkerForLoc(loc, cityCoords[city]); });
    }
  });

  var citiesToGeocode = Object.keys(byCityMap).filter(function(city) { return !(city in cityCoords); });
  if (citiesToGeocode.length === 0) { done(); return; }

  var pending = citiesToGeocode.length;
  citiesToGeocode.forEach(function(city) {
    geocodeCity(city, function(coords) {
      cityCoords[city] = coords || null;
      if (coords && byCityMap[city]) {
        byCityMap[city].forEach(function(loc) { placeMarkerForLoc(loc, coords); });
      }
      if (--pending === 0) done();
    });
  });
}

// ─── Initialize with API locations ───────────────────────────────────────────
function initializeWithApiLocations(apiLocations) {
  ymaps.ready(function () {
    createLayouts();

    // No default Yandex controls (no fullscreen, no Layers/typeSelector, no zoom +/- —
    // pinch/scroll to zoom).
    map = new ymaps.Map('map', {
      center: [40.1872, 44.5152],
      zoom: 10,
      controls: []
    }, {
      minZoom: 3
    });
    if (window.enableMapPinchZoom) window.enableMapPinchZoom(map, document.getElementById('map'));

    clusterer = new ymaps.Clusterer({
      clusterIconLayout: ClusterIconLayout,
      clusterIconShape: { type: 'Circle', coordinates: [0, -39], radius: 24 },
      groupByCoordinates: false,
      gridSize: 60,
      clusterDisableClickZoom: false
    });
    map.geoObjects.add(clusterer);

    // Re-filter the grid every time the user finishes panning/zooming (back to page 1).
    map.events.add('actionend', function() {
      mapPage = 1;
      filterCardsByBounds(map.getBounds());
      _syncPriceLabels();
      if (_shownCoords) _positionCard(); // keep a pinned tooltip glued to its marker
    });
    _syncPriceLabels();

    // Click on the map (not a marker) closes the pinned overlay.
    map.events.add('click', function() {
      if (_suppressMapClick) { _suppressMapClick = false; return; }
      if (_pinnedMark) { _pinnedMark = null; _hideCard(); }
    });

    // Tap ANYWHERE outside the map / marker / card also closes the pinned mini-card.
    // Clicks inside #map are handled by the map/placemark handlers above; clicks on the
    // card itself are ignored (let its link work).
    document.addEventListener('click', function (e) {
      if (!_pinnedMark) return;
      var o = _overlayEl();
      if (o && o.contains(e.target)) return;
      if (e.target.closest && e.target.closest('#map')) return;
      _pinnedMark = null; _hideCard();
    });

    // Keep the hover card open while the cursor is over it (mouse devices).
    var overlayEl = document.getElementById('map-card-overlay');
    if (overlayEl) {
      overlayEl.addEventListener('mouseenter', _cancelHide);
      overlayEl.addEventListener('mouseleave', _scheduleHide);
    }

    addMarkersFromLocations(apiLocations);

    // If AJAX locations landed before the map was ready, apply them now.
    if (pendingLocations && pendingLocations.length) {
      var pl = pendingLocations;
      pendingLocations = null;
      resetMapWithLocations(pl);
    }
  });
}

// ─── Apply AJAX-loaded locations (called from map.blade.php once coords arrive) ──
// Applies immediately if the map is ready, otherwise defers until init completes.
window.applyMapLocations = function(locations) {
  if (map) resetMapWithLocations(locations || []);
  else pendingLocations = locations || [];
};

// ─── Reset map markers after filter form AJAX ─────────────────────────────────
window.resetMapWithLocations = function(newLocations) {
  if (!map) return;
  geocodingState = 'loading';
  clusterer.removeAll();
  cityCoords = {};
  markerCoordsBySlug = {};
  locBySlug = {};
  placemarkBySlug = {};
  mapPage = 1;

  // Hide every card — filterCardsByBounds re-shows the in-bounds ones after geocoding.
  document.querySelectorAll('#prop-grid > div[data-slug]').forEach(function(card) {
    card.classList.add('map-card-hidden');
    card.classList.remove('d-none');
  });

  addMarkersFromLocations(newLocations);
};

// ─── Pan map to a named location ─────────────────────────────────────────────
window.panMapToLocation = function(query) {
  if (!map) return;
  geocodeCity(query, function(coords) {
    if (coords) map.setCenter(coords, 13, { duration: 300 });
  });
};

// ─── Entry point — always API-driven; wait for the Yandex API if it's not ready yet ──
(function () {
  function start() { initializeWithApiLocations(window.apiPropertyLocations || []); }
  if (typeof ymaps !== 'undefined') {
    start();
  } else {
    window.addEventListener('load', function () {
      if (typeof ymaps !== 'undefined') start();
      else console.error('Yandex Maps API not loaded');
    });
  }
}());
