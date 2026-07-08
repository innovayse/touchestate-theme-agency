/*
Author       : Dreamguys
Template Name: DreamsEstate - Bootstrap Template
Version      : 1.0
Modified     : Yandex Maps Integration + City-Based Geocoding + Animated Filtering
*/

// Resolve locale-aware property base URL once
function _propertyBase() {
  if (window.propertyBaseUrl) return window.propertyBaseUrl;
  var m = window.location.pathname.match(/^\/(en|ru|hy)\//);
  return m ? '/' + m[1] + '/property/' : '/property/';
}

var map, clusterer, current = 0;
var slider;
var PropertyIconLayout, ClusterIconLayout, PropertyBalloonLayout;

// ─── Hover balloon state ──────────────────────────────────────────────────────
var _activeHoverMark = null;
var _hoverTimer = null;
var _pinnedMark = null; // clicked/pinned balloon stays open

function cancelHoverClose() {
  if (_hoverTimer) { clearTimeout(_hoverTimer); _hoverTimer = null; }
}

function scheduleHoverClose() {
  cancelHoverClose();
  if (_pinnedMark) return; // pinned — don't auto-close
  _hoverTimer = setTimeout(function() {
    if (_activeHoverMark) {
      // Animate out before closing
      try {
        var overlay = _activeHoverMark.balloon.getOverlay();
        if (overlay) {
          overlay.then(function(o) {
            var el = o.getLayoutSync().getParentElement();
            var inner = el && el.querySelector('[style*="animation"]');
            if (inner) {
              inner.style.animation = 'hoverCardOut .2s ease-in forwards';
              setTimeout(function() {
                if (_activeHoverMark) {
                  _activeHoverMark.balloon.close();
                  _activeHoverMark = null;
                }
              }, 200);
            } else {
              _activeHoverMark.balloon.close();
              _activeHoverMark = null;
            }
          });
        } else {
          _activeHoverMark.balloon.close();
          _activeHoverMark = null;
        }
      } catch(e) {
        _activeHoverMark.balloon.close();
        _activeHoverMark = null;
      }
    }
  }, 250);
}

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
      if (data && data.length > 0) {
        var coords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
        console.warn('[map-grid] nominatim geocoded "' + city + '" \u2192 [' + coords[0] + ', ' + coords[1] + ']');
        callback(coords);
      } else {
        console.warn('[map-grid] nominatim "' + city + '" \u2192 no results');
        callback(null);
      }
    })
    .catch(function(err) {
      console.warn('[map-grid] nominatim "' + city + '" \u2192 error:', err);
      callback(null);
    });
}

// ─── Resolve city → coords: knownCities first, then Nominatim ───────────────
function geocodeCity(city, callback) {
  // Try exact match
  if (knownCities[city]) {
    console.warn('[map-grid] known city "' + city + '" \u2192 [' + knownCities[city][0] + ', ' + knownCities[city][1] + ']');
    callback(knownCities[city]);
    return;
  }
  // Try case-insensitive match
  var lower = city.toLowerCase();
  var keys = Object.keys(knownCities);
  for (var i = 0; i < keys.length; i++) {
    if (keys[i].toLowerCase() === lower) {
      console.warn('[map-grid] known city "' + city + '" (ci) \u2192 [' + knownCities[keys[i]][0] + ', ' + knownCities[keys[i]][1] + ']');
      callback(knownCities[keys[i]]);
      return;
    }
  }
  // Fallback to Nominatim
  console.warn('[map-grid] "' + city + '" not in knownCities, trying Nominatim...');
  geocodeViaNominatim(city, callback);
}

// markerCoordsBySlug: slug → [lat,lng] — actual marker position per property
var markerCoordsBySlug = {};

// 'loading' = geocoding in progress, don't filter yet
// 'ready'   = geocoding done, filter on every map interaction
var geocodingState = 'loading';

// ─── Static demo locations (fallback) ───────────────────────────────────────
var locations = [
  {
    "id": 1,
    "lat": 53.470692,
    "lng": -2.220328,
    "rent_prize": "$1,100 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1900",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "/build/img/buy/buy-grid-img-01.jpg",
    "profile_image": "build/img/users/user-01.jpg"
  },
  {
    "id": 2,
    "lat": 53.469189,
    "lng": -2.199262,
    "rent_prize": "$1,700 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1100",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "image": "build/img/buy/buy-grid-img-02.jpg",
    "profile_image": "build/img/users/user-02.jpg"
  },
  {
    "id": 3,
    "lat": 53.468665,
    "lng": -2.189269,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1500",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Beautiful apartment",
    "total_review": "15",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/buy/buy-grid-img-03.jpg",
    "profile_image": "build/img/users/user-03.jpg"
  },
  {
    "id": 4,
    "lat": 53.463894,
    "lng": -2.177880,
    "rent_prize": "$1,600 ",
    "rent_bed": "3",
    "rent_baths": "2",
    "rent_sqft": "1200",
    "rent_listedon": "18 Jan 2023",
    "rent_Category": "Apartments",
    "rent_name": "Modern living space",
    "total_review": "12",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "image": "build/img/buy/buy-grid-img-04.jpg",
    "profile_image": "build/img/users/user-04.jpg"
  },
  {
    "id": 5,
    "lat": 53.466359,
    "lng": -2.213314,
    "rent_prize": "$1,800 ",
    "rent_bed": "5",
    "rent_baths": "3",
    "rent_sqft": "2000",
    "rent_listedon": "19 Jan 2023",
    "rent_Category": "Houses",
    "rent_name": "Spacious family home",
    "total_review": "20",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/buy/buy-grid-img-05.jpg",
    "profile_image": "build/img/users/user-05.jpg"
  },
  {
    "id": 6,
    "lat": 53.469189,
    "lng": -2.210661,
    "rent_prize": "$1,300 ",
    "rent_bed": "2",
    "rent_baths": "1",
    "rent_sqft": "800",
    "rent_listedon": "20 Jan 2023",
    "rent_Category": "Studio",
    "rent_name": "Cozy studio apartment",
    "total_review": "8",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "image": "build/img/buy/buy-grid-img-02.jpg",
    "profile_image": "build/img/users/user-02.jpg"
  },
  {
    "id": 7,
    "lat": 53.468665,
    "lng": -2.188532,
    "rent_prize": "$2,000 ",
    "rent_bed": "4",
    "rent_baths": "3",
    "rent_sqft": "1800",
    "rent_listedon": "21 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Luxury condo",
    "total_review": "25",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/buy/buy-grid-img-03.jpg",
    "profile_image": "build/img/users/user-03.jpg"
  },
  {
    "id": 8,
    "lat": 53.463894,
    "lng": -2.1950372,
    "rent_prize": "$1,500 ",
    "rent_bed": "3",
    "rent_baths": "2",
    "rent_sqft": "1400",
    "rent_listedon": "22 Jan 2023",
    "rent_Category": "Apartments",
    "rent_name": "Downtown apartment",
    "total_review": "14",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "image": "build/img/buy/buy-grid-img-04.jpg",
    "profile_image": "build/img/users/user-04.jpg"
  },
  {
    "id": 9,
    "lat": 53.466359,
    "lng": -2.203314,
    "rent_prize": "$1,900 ",
    "rent_bed": "4",
    "rent_baths": "2",
    "rent_sqft": "1600",
    "rent_listedon": "23 Jan 2023",
    "rent_Category": "Houses",
    "rent_name": "Suburban house",
    "total_review": "18",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/buy/buy-grid-img-05.jpg",
    "profile_image": "build/img/users/user-05.jpg"
  }
];

// ─── Balloon content for static demo locations ───────────────────────────────
function getBalloonContent(marker) {
  return `
    <div class="property-card property-map-card mb-lg-0" style="min-width: 280px; max-width: 320px;">
      <div class="property-listing-item p-0 mb-0 shadow-none">
        <div class="buy-grid-img mb-0 rounded-0 position-relative">
          <a href="javascript:void(0);">
            <img class="img-fluid" src="${marker.image}" alt="${marker.rent_name}" style="width: 100%; height: 180px; object-fit: cover;">
          </a>
          <div class="d-flex align-items-center justify-content-between position-absolute bottom-0 end-0 start-0 p-3 z-1">
            <h6 class="text-white mb-0">${marker.rent_prize}</h6>
          </div>
        </div>
        <div class="buy-grid-content p-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h6 class="title mb-1">
                <a href="javascript:void(0);">${marker.rent_name}</a>
              </h6>
              <p class="d-flex align-items-center fs-14 mb-0">
                <i class="material-icons-outlined me-1 ms-0">location_on</i>${marker.rent_address}
              </p>
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
            <p class="fs-14 fw-medium text-dark mb-0">
              Listed on : <span class="fw-medium text-body">${marker.rent_listedon}</span>
            </p>
            <p class="badge bg-secondary text-white mb-0">${marker.rent_Category}</p>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ─── Price in the active display currency (falls back to the server-rendered string) ──
function _locPrice(loc) {
  if (window.CurrencyManager && loc && loc.priceCurrency != null && loc.priceAmount != null) {
    return window.CurrencyManager.format(loc.priceAmount, loc.priceCurrency);
  }
  return (loc && loc.price) || '';
}

// ─── Balloon content for API locations ───────────────────────────────────────
function getBalloonContentFromApi(loc) {
  var imgSrc = loc.image || '/build/img/buy/buy-grid-img-01.jpg';
  var url = _propertyBase() + loc.slug;
  return `
    <div class="property-card property-map-card mb-lg-0" style="min-width: 280px; max-width: 320px;">
      <div class="property-listing-item p-0 mb-0 shadow-none">
        <div class="buy-grid-img mb-0 rounded-0 position-relative">
          <a href="${url}">
            <img class="img-fluid" src="${imgSrc}" alt="${loc.title}"
              style="width: 100%; height: 180px; object-fit: cover;"
              onerror="this.onerror=null;this.src='/build/img/buy/buy-grid-img-01.jpg';">
          </a>
          <div class="d-flex align-items-center justify-content-between position-absolute bottom-0 end-0 start-0 p-3 z-1">
            <h6 class="text-white mb-0">${_locPrice(loc)}</h6>
          </div>
        </div>
        <div class="buy-grid-content p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
              <h6 class="title mb-1">
                <a href="${url}">${loc.title}</a>
              </h6>
              <p class="d-flex align-items-center fs-14 mb-0">
                <i class="material-icons-outlined me-1 ms-0" style="font-size:14px;">location_on</i>${loc.address}
              </p>
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-end">
            <span class="badge bg-secondary">${loc.category}</span>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ─── Mini card HTML for marker hover hint ─────────────────────────────────────
function getMiniCardHtml(loc) {
  var imgSrc = loc.image || '/build/img/buy/buy-grid-img-01.jpg';
  var url = _propertyBase() + loc.slug;
  return '<a href="' + url + '" style="display:block;width:260px;background:#1e2330;border-radius:12px;' +
    'overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.45);font-family:Inter,system-ui,sans-serif;' +
    'text-decoration:none;color:inherit;cursor:pointer;border:1px solid rgba(255,255,255,.08);">' +
    '<div style="position:relative;">' +
      '<img src="' + imgSrc + '" alt="" style="width:100%;height:140px;object-fit:cover;display:block;">' +
      '<div style="position:absolute;bottom:0;left:0;right:0;padding:6px 10px;' +
        'background:linear-gradient(transparent,rgba(0,0,0,.65));">' +
        '<span style="font-size:15px;font-weight:700;color:#fff;">' + _locPrice(loc) + '</span>' +
      '</div>' +
    '</div>' +
    '<div style="padding:10px 12px;">' +
      '<div style="font-size:13px;font-weight:600;color:#f0f0f0;margin-bottom:4px;' +
        'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (loc.title || '') + '</div>' +
      '<div style="font-size:11px;color:#9aa0b0;' +
        'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (loc.address || '') + '</div>' +
    '</div>' +
  '</a>';
}

// ─── Create custom icon layouts (called once after ymaps.ready) ──────────────
function createLayouts() {
  if (PropertyIconLayout) return;

  // ── Single property marker ────────────────────────────────────────────────
  // Red teardrop with house icon in white center
  PropertyIconLayout = ymaps.templateLayoutFactory.createClass(
    '<div style="width:46px;height:60px;cursor:pointer;' +
    'filter:drop-shadow(1px 4px 7px rgba(0,0,0,.38))">' +
    '<svg width="46" height="60" viewBox="0 0 46 60" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      '<path d="M23,57 C17,47 2,36 2,22 A21,21 0 1,1 44,22 C44,36 29,47 23,57Z" fill="#E8443C"/>' +
      '<circle cx="23" cy="22" r="13" fill="#C0312A"/>' +
      '<circle cx="23" cy="22" r="9" fill="#FFFFFF"/>' +
      '<path d="M23,16 L18,21 L18,27 L21.5,27 L21.5,24 L24.5,24 L24.5,27 L28,27 L28,21 Z" fill="#C0312A"/>' +
    '</svg>' +
    '</div>'
  );

  // ── Custom balloon layout (mini card fixed above marker on hover) ───────
  PropertyBalloonLayout = ymaps.templateLayoutFactory.createClass(
    '<div style="position:absolute;bottom:0;left:50%;transform:translateX(-50%);margin-bottom:8px;' +
      'animation:hoverCardIn .25s ease-out;">' +
      '{{ properties.balloonContent|raw }}' +
    '</div>', {
      build: function() {
        this.constructor.superclass.build.call(this);
        var el = this.getParentElement();
        var node = el;
        while (node && node !== document.body) {
          if (node.className && typeof node.className === 'string' && node.className.indexOf('balloon') !== -1) {
            node.style.cssText += ';background:transparent!important;box-shadow:none!important;border:none!important;padding:0!important;';
          }
          node = node.parentNode;
        }
        el.addEventListener('mouseenter', cancelHoverClose);
        el.addEventListener('mouseleave', scheduleHoverClose);
      },
      clear: function() {
        var el = this.getParentElement();
        el.removeEventListener('mouseenter', cancelHoverClose);
        el.removeEventListener('mouseleave', scheduleHoverClose);
        this.constructor.superclass.clear.call(this);
      }
    }
  );

  // ── Cluster marker ─────────────────────────────────────────────────────────
  // Same Google Maps pin style, larger, with count number in the white circle
  ClusterIconLayout = ymaps.templateLayoutFactory.createClass(
    '<div style="width:52px;height:68px;cursor:pointer;' +
    'margin:-65px 0 0 -26px;' +
    'filter:drop-shadow(1px 4px 7px rgba(0,0,0,.38))">' +
    '<svg width="52" height="68" viewBox="0 0 52 68" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      // Teardrop body
      '<path d="M26,65 C20,53 2,42 2,26 A24,24 0 1,1 50,26 C50,42 32,53 26,65Z"' +
      '  fill="#E8443C"/>' +
      // Dark inner ring
      '<circle cx="26" cy="26" r="16" fill="#C0312A"/>' +
      // White center circle
      '<circle cx="26" cy="26" r="10" fill="#FFFFFF"/>' +
      // Count number
      '<text x="26" y="30" text-anchor="middle" fill="#C0312A"' +
      '  font-size="12" font-weight="800" font-family="Arial,sans-serif">' +
      '{{ properties.geoObjects.length }}</text>' +
    '</svg>' +
    '</div>'
  );
}

// ─── Initialize with static demo locations ───────────────────────────────────
function initialize() {
  ymaps.ready(function () {
    createLayouts();

    map = new ymaps.Map('map', {
      center: [locations[0].lat, locations[0].lng],
      zoom: 14,
      controls: ['zoomControl', 'typeSelector', 'fullscreenControl']
    }, {
      minZoom: 3
    });

    clusterer = new ymaps.Clusterer({
      clusterIconLayout: ClusterIconLayout,
      clusterIconShape: { type: 'Circle', coordinates: [0, -39], radius: 24 },
      groupByCoordinates: false,
      gridSize: 60,
      clusterDisableClickZoom: false
    });

    var placemarks = [];

    locations.forEach(function(location) {
      var placemark = new ymaps.Placemark(
        [location.lat, location.lng],
        {
          balloonContent: getBalloonContent(location),
          hintContent: location.rent_name
        },
        {
          iconLayout: PropertyIconLayout,
          iconOffset: [-23, -57],
          iconShape: { type: 'Circle', coordinates: [0, -35], radius: 21 }
        }
      );
      placemarks.push(placemark);
    });

    clusterer.add(placemarks);
    map.geoObjects.add(clusterer);

    if (placemarks.length > 0) {
      var bounds = clusterer.getBounds();
      if (bounds) map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 50 });
    }
  });
}

// ─── Pagination state for Load More / Show Less ──────────────────────────────
var GRID_PAGE_SIZE = 20;
var GRID_STEP = 10;
var gridLimit = GRID_PAGE_SIZE; // how many in-bounds cards to show

// ─── Run filter by map bounds + paginate with Load More ──────────────────────
// 1. Mark every card as in-bounds or out-of-bounds (via marker coords)
// 2. Show first `gridLimit` in-bounds cards, hide the rest
// 3. Update counter and Load More / Show Less buttons
function filterCardsByBounds(bounds) {
  if (!bounds || geocodingState !== 'ready') return;

  var cards = document.querySelectorAll('#prop-grid > div[data-slug]');
  var inBoundsCount = 0;
  var shownCount = 0;

  cards.forEach(function(card) {
    var slug = card.dataset.slug || '';
    var coords = slug ? markerCoordsBySlug[slug] : null;
    var inBounds;

    if (!coords) {
      // No marker placed (geocoding failed) — hide
      inBounds = false;
    } else {
      inBounds =
        coords[0] >= bounds[0][0] && coords[0] <= bounds[1][0] &&
        coords[1] >= bounds[0][1] && coords[1] <= bounds[1][1];
    }

    if (inBounds) {
      inBoundsCount++;
      if (inBoundsCount <= gridLimit) {
        // Show this card
        card.classList.remove('map-card-hidden', 'd-none');
        shownCount++;
      } else {
        // In bounds but over page limit — hide with d-none
        card.classList.remove('map-card-hidden');
        card.classList.add('d-none');
      }
    } else {
      // Out of bounds — hide
      card.classList.add('map-card-hidden');
      card.classList.remove('d-none');
    }
  });

  // Update counter
  var el = document.getElementById('result-loaded');
  if (el) el.textContent = inBoundsCount;

  // Update Load More / Show Less buttons
  var btnMore = document.getElementById('btnLoadMore');
  var btnLess = document.getElementById('btnShowLess');
  if (btnMore) btnMore.style.display = inBoundsCount > gridLimit ? '' : 'none';
  if (btnLess) btnLess.style.display = gridLimit > GRID_PAGE_SIZE ? '' : 'none';
}

// ─── Place a single marker ───────────────────────────────────────────────────
function placeMarkerForLoc(loc, cityCenter) {
  var coords;
  if (loc.lat && loc.lng) {
    // Use actual coordinates from API
    coords = [loc.lat, loc.lng];
  } else {
    // Fallback: scatter around city center
    var spread = 0.004;
    coords = [
      cityCenter[0] + (Math.random() - 0.5) * spread,
      cityCenter[1] + (Math.random() - 0.5) * spread
    ];
  }
  // Store actual marker position so filterCardsByBounds can use it
  if (loc.slug) markerCoordsBySlug[loc.slug] = coords;

  var placemark = new ymaps.Placemark(
    coords,
    {
      balloonContent: getMiniCardHtml(loc),
      slug: loc.slug
    },
    {
      iconLayout: PropertyIconLayout,
      iconOffset: [-20, -50],
      iconShape: { type: 'Circle', coordinates: [23, 22], radius: 18 },
      balloonLayout: PropertyBalloonLayout,
      balloonOffset: [0, -30],
      balloonAutoPan: false,
      hideIconOnBalloonOpen: false,
      openBalloonOnClick: false
    }
  );
  placemark.events.add('mouseenter', function() {
    map.container.getElement().style.cursor = 'pointer';
  });
  placemark.events.add('mouseleave', function() {
    map.container.getElement().style.cursor = '';
  });
  placemark.events.add('click', function(e) {
    cancelHoverClose();
    // Close hover balloon
    if (_activeHoverMark) { _activeHoverMark.balloon.close(); _activeHoverMark = null; }
    var overlay = document.getElementById('map-card-overlay');
    if (!overlay) return;
    if (_pinnedMark === placemark) {
      // Unpin — hide overlay
      _pinnedMark = null;
      overlay.style.display = 'none';
      overlay.innerHTML = '';
    } else {
      _pinnedMark = placemark;
      overlay.innerHTML = getMiniCardHtml(loc);
      overlay.style.display = 'block';
    }
  });
  clusterer.add(placemark);
}

// ─── Geocode unique cities, place markers, then unlock filter ─────────────────
function addMarkersFromLocations(apiLocations) {
  if (apiLocations.length === 0) {
    geocodingState = 'ready';
    filterCardsByBounds(map ? map.getBounds() : null);
    return;
  }

  // Separate: properties with own coords vs those needing city geocoding
  var withCoords = [];
  var needsGeocoding = [];
  apiLocations.forEach(function(loc) {
    if (loc.lat && loc.lng) {
      withCoords.push(loc);
    } else {
      needsGeocoding.push(loc);
    }
  });

  // Place markers for properties that have their own coordinates immediately
  withCoords.forEach(function(loc) {
    placeMarkerForLoc(loc, [loc.lat, loc.lng]); // cityCenter unused when loc has coords
  });

  if (needsGeocoding.length === 0) {
    geocodingState = 'ready';
    filterCardsByBounds(map ? map.getBounds() : null);
    return;
  }

  // Group remaining by city
  var byCityMap = {};
  needsGeocoding.forEach(function(loc) {
    var city = loc.city || 'Yerevan';
    if (!byCityMap[city]) byCityMap[city] = [];
    byCityMap[city].push(loc);
  });

  // Place markers for already-geocoded cities immediately
  Object.keys(byCityMap).forEach(function(city) {
    if ((city in cityCoords) && cityCoords[city]) {
      byCityMap[city].forEach(function(loc) {
        placeMarkerForLoc(loc, cityCoords[city]);
      });
    }
  });

  // Determine which cities still need geocoding
  var citiesToGeocode = Object.keys(byCityMap).filter(function(city) {
    return !(city in cityCoords);
  });

  if (citiesToGeocode.length === 0) {
    geocodingState = 'ready';
    filterCardsByBounds(map ? map.getBounds() : null);
    return;
  }

  var pending = citiesToGeocode.length;

  function oneCityDone() {
    pending--;
    if (pending === 0) {
      geocodingState = 'ready';
      filterCardsByBounds(map.getBounds());
    }
  }

  citiesToGeocode.forEach(function(city) {
    geocodeCity(city, function(coords) {
      if (coords) {
        cityCoords[city] = coords;
        if (byCityMap[city]) {
          byCityMap[city].forEach(function(loc) {
            placeMarkerForLoc(loc, coords);
          });
        }
      } else {
        cityCoords[city] = null;
      }
      oneCityDone();
    });
  });
}

// ─── Initialize with API locations ───────────────────────────────────────────
function initializeWithApiLocations(apiLocations) {
  // Diagnostic: check if API provides coordinates
  var withCoords = apiLocations.filter(function(l) { return l.lat && l.lng; });
  console.warn('[map-grid] ' + apiLocations.length + ' properties, ' + withCoords.length + ' with lat/lng from API');
  if (apiLocations.length > 0 && withCoords.length === 0) {
    console.warn('[map-grid] API does not provide coordinates — falling back to city geocoding + random scatter');
  }

  ymaps.ready(function () {
    createLayouts();

    map = new ymaps.Map('map', {
      center: [40.1872, 44.5152],
      zoom: 10,
      controls: ['zoomControl', 'typeSelector', 'fullscreenControl']
    }, {
      minZoom: 3
    });

    // Clusterer: groups nearby markers, shows count badge
    clusterer = new ymaps.Clusterer({
      clusterIconLayout: ClusterIconLayout,
      clusterIconShape: { type: 'Circle', coordinates: [0, -39], radius: 24 },
      groupByCoordinates: false,
      gridSize: 60,
      clusterDisableClickZoom: false
    });
    map.geoObjects.add(clusterer);

    // Every time the user finishes panning or zooming — apply filter with animation
    map.events.add('actionend', function() {
      filterCardsByBounds(map.getBounds());
    });

    // Click on map (not on marker) — close pinned overlay
    map.events.add('click', function() {
      if (_pinnedMark) {
        _pinnedMark = null;
        _activeHoverMark = null;
        var overlay = document.getElementById('map-card-overlay');
        if (overlay) { overlay.style.display = 'none'; overlay.innerHTML = ''; }
      }
    });

    addMarkersFromLocations(apiLocations);
  });
}

// ─── Reset map markers after filter form AJAX ─────────────────────────────────
window.resetMapWithLocations = function(newLocations) {
  if (!map) return;

  var savedScrollY = (window.scrollY !== undefined) ? window.scrollY : window.pageYOffset;

  // Pause filter, clear all markers from clusterer
  geocodingState = 'loading';
  clusterer.removeAll();
  cityCoords = {};
  markerCoordsBySlug = {};
  gridLimit = GRID_PAGE_SIZE;

  // Hide all cards — filterCardsByBounds will show the right ones after geocoding
  document.querySelectorAll('#prop-grid > div[data-slug]').forEach(function(card) {
    card.classList.add('map-card-hidden');
    card.classList.remove('d-none');
  });

  addMarkersFromLocations(newLocations);
};

// ─── Add markers for newly loaded items (Load More) ──────────────────────────
window.addNewMapLocations = function(newLocations) {
  if (!map || !newLocations || !newLocations.length) return;
  geocodingState = 'loading'; // pause while geocoding any new cities
  addMarkersFromLocations(newLocations);
};

// ─── Remove map markers that have no corresponding DOM card ──────────────────
window.syncMarkersWithDom = function() {
  if (!clusterer || !map) return;
  // Collect slugs of cards currently in the DOM
  var domSlugs = {};
  document.querySelectorAll('#prop-grid > div[data-slug]').forEach(function(c) {
    if (c.dataset.slug) domSlugs[c.dataset.slug] = true;
  });
  // Remove placemarks whose slug is not in the DOM
  var toRemove = [];
  clusterer.getGeoObjects().forEach(function(pm) {
    var slug = pm.properties.get('slug');
    if (slug && !domSlugs[slug]) toRemove.push(pm);
  });
  toRemove.forEach(function(pm) { clusterer.remove(pm); });
  // Re-run bounds filter to refresh counter
  if (geocodingState === 'ready') {
    filterCardsByBounds(map.getBounds());
  }
};

// ─── Pan map to a named location ─────────────────────────────────────────────
window.panMapToLocation = function(query) {
  if (!map) return;
  geocodeCity(query, function(coords) {
    if (coords) {
      map.setCenter(coords, 13, { duration: 300 });
    }
  });
};

// ─── Entry point ─────────────────────────────────────────────────────────────
if (typeof ymaps !== 'undefined') {
  if (typeof window.apiPropertyLocations !== 'undefined' && window.apiPropertyLocations.length > 0) {
    initializeWithApiLocations(window.apiPropertyLocations);
  } else {
    initialize();
  }
} else {
  console.error('Yandex Maps API not loaded');
}
