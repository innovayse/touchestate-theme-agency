# Yandex Map — Custom House Pin Marker Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the default red dot Yandex Maps marker on the property-single page with a branded circular pin containing a house SVG icon and a balloon popup that displays property address fields sourced from the API.

**Architecture:** Two-file change. `property-single.blade.php` — the map div height is increased to avoid balloon clipping, and the `@push('scripts')` section gets a PHP balloon-builder block plus a JS `templateLayoutFactory` custom icon. All API string values are sanitised with `strip_tags()` before entering the balloon HTML to prevent both XSS and double-encoding from pre-escaped API responses.

**Tech Stack:** Laravel Blade, Yandex Maps JS API 2.1, Alpine.js (not touched), vanilla JS, Tailwind CSS.

## Global Constraints

- Only touch `property-single.blade.php`.
- Brand accent color: `#a6644f` (terracotta). Use for pin fill and balloon price text.
- Balloon must render cleanly when any/all address fields are absent.
- Icon tail must point precisely at `[$lat, $lng]` — set `iconOffset` accordingly.
- Do not upgrade or swap the Yandex Maps API version (keep `2.1`).
- `templateLayoutFactory` template string must be built with JS string concatenation (`'...' + '...'`) — never template literals or multi-line strings; Yandex Maps silently drops markers containing unexpected whitespace nodes.

---

### Task 1: Fix map height and build compact balloon

**Files:**
- Modify: `resources/views/property-single.blade.php`
  - Line ~626: map div height
  - Lines ~685–706: entire `@if($lat && $lng && $yandexKey)` map block inside `@push('scripts')`

**Interfaces:**
- Consumes: `$property` (array — keys: `title`, `street`, `buildingNumber`, `district`, `city`, `country`), `$price` (string|null, already `number_format()` output — safe, no escaping needed), `$currency` (raw API string), `$lat`, `$lng`, `$yandexKey` — all defined in the view's `@php` preamble.
- Produces: inline JS that renders a custom Yandex Maps placemark. No external API contracts change.

---

#### Step 1.1 — Increase map div height

- [ ] Locate line ~626:

  ```blade
  <div id="prop-map" class="h-52"></div>
  ```

  Change `h-52` → `h-64`:

  ```blade
  <div id="prop-map" class="h-64"></div>
  ```

  **Why:** `h-52` = 208 px. A balloon with title + address + price needs ~110 px; on a 208 px map it covers more than half the viewport. `h-64` = 256 px gives the balloon room to sit above the pin without eating the entire map.

---

#### Step 1.2 — Replace the map script block

- [ ] Locate the block starting with `@if($lat && $lng && $yandexKey)` inside `@push('scripts')` (~line 685). Replace the entire block with:

  ```blade
  @if($lat && $lng && $yandexKey)
  @php
      // strip_tags() used instead of e(): removes any injected markup and
      // avoids double-encoding when the API returns pre-escaped HTML entities
      // (e.g. &amp;, &quot;). $price is already number_format() output — no escaping needed.
      $bT = function(string $v): string { return strip_tags($v); };

      $bLines = [];

      if (!empty($property['title'])) {
          $bLines[] = '<b style="font-size:13px;color:#1a1209">' . $bT($property['title']) . '</b>';
      }

      // One compact address line: street + building, district, city, country
      $addrParts = array_filter([
          isset($property['street'], $property['buildingNumber'])
              ? $bT($property['street']) . ' ' . $bT($property['buildingNumber'])
              : ($property['street'] ?? null ? $bT($property['street']) : null),
          !empty($property['district']) ? $bT($property['district']) : null,
          !empty($property['city'])     ? $bT($property['city'])     : null,
          !empty($property['country'])  ? $bT($property['country'])  : null,
      ]);
      if ($addrParts) {
          $bLines[] = '<span style="color:#666;font-size:12px">' . implode(', ', $addrParts) . '</span>';
      }

      if ($price) {
          $bLines[] = '<b style="color:#a6644f;font-size:13px">'
              . $price . ' ' . $bT($currency)
              . '</b>';
      }

      $balloonHtml = '<div style="line-height:1.7;padding:2px 4px;max-width:210px">'
          . implode('<br>', $bLines)
          . '</div>';
  @endphp
  // Yandex map
  (function loadMap() {
      const script = document.createElement('script');
      script.src = 'https://api-maps.yandex.ru/2.1/?apikey={{ $yandexKey }}&lang={{ app()->getLocale() === "en" ? "en_US" : "ru_RU" }}';
      script.onload = function() {
          ymaps.ready(function() {
              const map = new ymaps.Map('prop-map', {
                  center: [{{ $lat }}, {{ $lng }}],
                  zoom: 15,
                  controls: ['zoomControl'],
              });

              // Each line is a separate '+' concatenation — no newlines, no template literals.
              // Yandex Maps drops custom icons silently when the template string
              // contains unexpected whitespace text nodes.
              const HousePin = ymaps.templateLayoutFactory.createClass(
                  '<div style="display:flex;flex-direction:column;align-items:center;cursor:pointer;filter:drop-shadow(0 4px 8px rgba(0,0,0,0.28))">' +
                  '<div style="width:44px;height:44px;border-radius:50%;background:#a6644f;display:flex;align-items:center;justify-content:center">' +
                  '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">' +
                  '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>' +
                  '<path d="M9 21V12h6v9"/>' +
                  '</svg>' +
                  '</div>' +
                  '<div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:11px solid #a6644f;margin-top:-1px"></div>' +
                  '</div>'
              );

              map.geoObjects.add(new ymaps.Placemark([{{ $lat }}, {{ $lng }}], {
                  balloonContent: @json($balloonHtml),
              }, {
                  iconLayout: HousePin,
                  // Pin geometry: 44px circle + 11px tail = 55px total height, 44px wide.
                  // iconOffset moves top-left corner of the icon relative to the coordinate.
                  // [-22, -55] → circle is centred horizontally, tail tip sits on the coordinate.
                  iconOffset: [-22, -55],
                  // iconShape defines the clickable hit area. Extended 4px on each side
                  // and +5px below for comfortable touch targets on mobile.
                  iconShape: { type: 'Rectangle', coordinates: [[-26, -60], [26, 5]] },
                  // Push balloon up so it clears the pin and doesn't overlap the map centre.
                  balloonOffset: [0, -60],
              }));
          });
      };
      document.head.appendChild(script);
  })();
  @endif
  ```

---

#### Step 1.3 — Visual verification: full address

- [ ] Open a property page with coordinates and all address fields populated (street, buildingNumber, district, city, country, price).

  Expected:
  - Map renders at ~256 px tall.
  - Marker: terracotta circle, white house SVG icon, triangular tail pointing at the pin coordinate. Drop shadow visible.
  - Click marker → balloon appears **above** the pin, not obscured by the map edge.
  - Balloon content: title (bold dark), one address line (`Street 5, Kentron, Yerevan, Armenia`), price in terracotta bold. Max width 210 px — long titles wrap, don't overflow.
  - No JS errors in the browser console.

---

#### Step 1.4 — Visual verification: partial address

- [ ] Open a property page with only `city` and `country` set (no street, no district, no buildingNumber, no price).

  Expected:
  - Balloon shows: title + `"Yerevan, Armenia"` only. No empty `<br>` lines, no commas dangling at the start.

---

#### Step 1.5 — Visual verification: no coordinates

- [ ] Open a property page where `$lat` / `$lng` are null.

  Expected: no map div rendered, no JS errors in console.

---

#### Step 1.6 — Visual verification: special characters in currency / title

- [ ] Test with a property whose title contains `&` (e.g. `"Bed & Breakfast"`) and whose currency is `֏` (Armenian dram) or `€`.

  Expected:
  - Title displays as `Bed & Breakfast` in the balloon — not as `Bed &amp; Breakfast`.
  - Currency symbol renders as the actual glyph, not as an HTML entity.

---

#### Step 1.7 — Visual verification: touch target on mobile

- [ ] Open DevTools → toggle device toolbar (375 px wide). Tap the pin.

  Expected: balloon opens on the first tap without accidentally panning the map. The clickable zone is ~52 × 65 px (larger than the 44 px visual circle).

---

#### Step 1.8 — Commit

- [ ] Commit:

  ```bash
  git add resources/views/property-single.blade.php
  git commit -m "feat(map): custom terracotta house-pin marker with compact address balloon"
  ```

---

## Self-Review

**Spec coverage:**
- ✅ Custom house icon pin — `HousePin` templateLayoutFactory layout with house SVG.
- ✅ Balloon shows API address fields — compact single address line + title + price.
- ✅ Brand color `#a6644f` — pin fill and price text.
- ✅ Pin tip at coordinate — `iconOffset: [-22, -55]`.
- ✅ Missing fields are silently omitted — `array_filter` + `if ($price)` guards.
- ✅ Balloon overflow fix — map height raised to `h-64`; `balloonOffset: [0, -60]` pushes popup above pin.
- ✅ Double-encoding fix — `strip_tags()` normalises API values; `$price` is already a formatted float string.
- ✅ Touch target fix — `iconShape` rectangle extended ±4 px horizontally and +5 px below.
- ✅ Yandex Maps whitespace fix — template string is pure JS `'...' + '...'` concatenation with no newlines or template literals.

**Placeholder scan:** None.

**Type consistency:** `$balloonHtml` (string) → `@json($balloonHtml)` → JS string literal. `$bT` closure returns `string`. All address parts are `string|null`; `array_filter` removes nulls before `implode`.
