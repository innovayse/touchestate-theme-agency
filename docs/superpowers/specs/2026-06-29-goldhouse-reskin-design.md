# GOLDHOUSE Reskin of TouchEstate Agency Theme — Design

**Date:** 2026-06-29
**Status:** Approved (design), pending spec review

## Goal

Build a complete multilingual real-estate website in `touchestate-demo` with the
**full functionality** of `touchestate-theme-agency` (the site behind
tun.touchestate.am), but **restyled visually** to match the "GOLDHOUSE / Modern
Real Estate" design from Behance
(https://www.behance.net/gallery/249732115/Modern-real-estate-website-design).

We keep all backend behavior; we change only the visual layer and home-page
content/sections.

## Approach (approved)

Fork the existing `touchestate-theme-agency` (Laravel 12) into `touchestate-demo`
unchanged, then reskin. We do **not** touch PHP controllers, routes, the
TouchEstate API/SDK integration, geocoding, or the multilingual machinery. We
change only:

- SCSS design tokens (colors, typography, radius)
- Blade markup + page SCSS for visual structure (home, header, footer first)

### Why this approach

The theme already implements everything tun.touchestate.am does: API-driven
listings, property detail (gallery + map + similar + comments), map view,
favorites, compare, contact, FAQ, and `/hy` `/ru` `/en` localization. Rebuilding
that is wasted work. The design is a skin over a working app.

## Source architecture (what we inherit, unchanged)

- **Laravel 12 / PHP 8.2**, Blade + SCSS compiled by Vite.
- **No own properties DB** — data comes from the **TouchEstate API** via
  `touchestate-sdk/php-sdk`; responses cached ~1h.
- **Controllers:** Home, Property (list/single/map/enquire/recordView),
  Favorites (localStorage), Compare, Contact.
- **Routing:** default (no prefix) → Armenian; `{locale}` group for `en|ru|hy`
  via `setlocale` middleware. Geocoding proxy routes (`/api/suggest`,
  `/api/central-district`, `/api/nearby`) for Yandex + Nominatim.
- **Pages:** home, property listing, property single, map, favorites, compare,
  contact-us, faq, privacy-policy, terms-condition, testimonial, cart, checkout,
  404/500, maintenance.
- **Maps:** Yandex Maps + Nominatim.

## Design system changes

### 1. Colors — `resources/scss/utils/_colors.scss`

The file generates CSS custom properties for everything from `$theme-colors`.
Change the primary brand color so the whole site re-themes from one place.

- `$theme-colors.primary`: `#03BD9D` (teal) → **`#A6644F` (terracotta)**.
- `$theme-colors.dark`: keep a near-black green/charcoal for headings
  (`#2B2A26`-ish) consistent with the Behance dark serif text.
- Add cream/beige surface tokens used by the new sections:
  - page background `#F3EBE0`
  - card/panel surface `#FBF6EF`
  - soft beige `#EFE4D6`
- Optionally remap the `primary` shade ramp (100–950) to terracotta tints so
  buttons/badges/hovers stay on-palette.

### 2. Typography — `resources/scss/utils/_typography.scss`

- Body font stays **IBM Plex Sans**.
- Add a **serif display font for headings**: **Playfair Display** (default
  choice) loaded via Google Fonts. Introduce `$font-family-display` and apply it
  to hero/section headings (`h1`, `.section-title`, etc.).

### 3. Shape — `resources/scss/utils/_root.scss` / `_base.scss`

- Body background → cream surface token.
- Increase card/panel radius to ~16–20px to match Behance's rounded cards
  (current `$border-radius` is 2px; introduce a large token, e.g.
  `$border-radius-2xl: 20px`, used by cards/hero/search panels).

### 4. Home page — `resources/views/index.blade.php` + `resources/scss/pages/_index.scss`

Restyle into the Behance sections, fed by existing `HomeController` data:

- **Hero:** serif headline "Build Your Dream Home" (split black + terracotta),
  left stats column (150+ / 10+ / 500+ → mapped to existing `$stats`), a
  "with Modern Design" search bar + "Search Now" button, hero house image, and a
  bottom **Find Properties** filter panel (City / Property type / Bedroom +
  search) wired to the existing property search.
- **Trusted Advisors:** 4 stat cards (one terracotta-filled) + image cluster
  with a circular logo badge.
- **Modern Homes Built with Smart Technology:** large serif heading + copy.
- **Our Services:** wide banner image + 4 terracotta service cards with white
  icon badges (Affordable Property, Guaranteed Quality, Fast and Easy Process,
  Property Insurance).
- **Catalog of Our Properties:** horizontal property carousel using existing
  `property-card` component, restyled, with prev/next controls.

### 5. Header & Footer — `layout/partials/header.blade.php`, `footer.blade.php`

- Header: GOLDHOUSE-style wordmark/logo, terracotta accents, language switcher
  kept (EN/HY/RU), search + menu icons.
- Footer: GOLDHOUSE logo + tagline, four link columns (Quick Links / Company /
  Support / Movement), bottom Terms & Privacy row.

## Out of scope (inherit styling, polish later)

Property listing/single, map, favorites, compare, FAQ keep their current
structure; they inherit the new color/font tokens automatically and get
targeted polish in a later pass, not in this first iteration.

## Decisions / defaults

- **Serif font:** Playfair Display (can swap to DM Serif Display later).
- **API keys:** Build proceeds **without** TouchEstate / Yandex keys so the
  design is reviewable; listing areas render empty until keys are added to
  `.env`. Keys go into `.env` when available (`TOUCHESTATE_*`, `YANDEX_MAPS_API_KEY`).
- **Default locale:** Armenian (unchanged from source).

## Verification

- `composer install` succeeds (PHP 8.2+).
- `npm install && npm run build` compiles SCSS via Vite with no errors.
- `php artisan serve` boots; home, header, footer render in GOLDHOUSE style at
  `/`, `/en`, `/ru`, `/hy`.
- With no API key: pages load without fatal errors, listing sections show empty
  states. With keys: listings populate.
