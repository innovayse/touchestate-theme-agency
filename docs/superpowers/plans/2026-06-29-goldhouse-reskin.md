# GOLDHOUSE Reskin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reskin the `touchestate-theme-agency` Laravel theme into `touchestate-demo` with the GOLDHOUSE (terracotta + cream + serif) visual design, keeping all backend/API/multilingual behavior.

**Architecture:** Copy the existing Laravel 12 theme into the working directory unchanged, then change only design tokens (SCSS colors/typography/radius) and Blade markup + page SCSS for home, header, and footer. PHP controllers, routes, the TouchEstate SDK integration, geocoding, and the `hy/ru/en` localization are untouched.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, SCSS compiled by Vite, TouchEstate PHP SDK, Yandex Maps + Nominatim.

## Global Constraints

- PHP 8.2+, Laravel 12 (inherited from source theme — do not downgrade).
- Do NOT modify: `app/`, `routes/`, `config/`, `lang/` logic, the SDK integration. Visual layer only.
- Reskin re-themes from tokens: change brand color in ONE place (`_colors.scss`) so it propagates via generated CSS custom properties.
- Brand primary color: terracotta `#A6644F`. Page bg cream `#F3EBE0`. Panel surface `#FBF6EF`. Soft beige `#EFE4D6`. Heading dark `#2B2A26`.
- Heading display font: Playfair Display (Google Fonts). Body font: IBM Plex Sans (unchanged).
- Verification is build + render based (visual reskin): `npm run build` must compile with no SCSS errors; `php artisan serve` must boot and render home/header/footer without fatal errors. No API key required for build (listing sections render empty).
- Frequent commits, one per task.

---

### Task 1: Fork the theme into the working directory

**Files:**
- Copy entire `touchestate-theme-agency` tree into repo root (preserving `docs/` already present).

- [ ] **Step 1:** Clone the source theme to a temp dir (if not already cloned):
  `git clone --depth 1 https://github.com/innovayse/touchestate-theme-agency.git /tmp/te-theme`
- [ ] **Step 2:** Copy all theme files (excluding its `.git`) into the repo root, keeping existing `docs/` and `.git`:
  `rsync -a --exclude='.git' /tmp/te-theme/ /home/innovayse/www/touchestate-demo/`
- [ ] **Step 3:** Remove the leftover empty `test.am` placeholder: `rm -f test.am`
- [ ] **Step 4:** Verify structure present: `ls artisan composer.json resources/scss/main.scss` (all exist).
- [ ] **Step 5:** Commit: `git add -A && git commit -m "chore: fork touchestate-theme-agency as base"`

---

### Task 2: Install dependencies and confirm a clean baseline build

**Files:** none (tooling).

- [ ] **Step 1:** `composer install` (expect success on PHP 8.2+).
- [ ] **Step 2:** `npm install` (expect node_modules created).
- [ ] **Step 3:** `cp .env.example .env && php artisan key:generate`.
- [ ] **Step 4:** Baseline build BEFORE any reskin: `npm run build`.
  Expected: Vite builds, SCSS compiles to `resources/css/style.css` / public assets with no errors.
- [ ] **Step 5:** Boot check: `php artisan serve` in background, `curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/` → expect `200`. Stop the server.
- [ ] **Step 6:** Commit lockfiles/env scaffolding if changed: `git add -A && git commit -m "chore: install deps and baseline build"`

---

### Task 3: Color tokens → terracotta + cream

**Files:**
- Modify: `resources/scss/utils/_colors.scss`

- [ ] **Step 1:** In `$theme-colors`, change `"primary": #03BD9D` → `"primary": #A6644F` and `"dark": #0D1520` → `"dark": #2B2A26`.
- [ ] **Step 2:** Remap the `"primary"` shade ramp (100–950) in `$colors` to terracotta tints (light `#F4E7E1` → dark `#7A4334`), so badges/hovers stay on-palette. Keep the same 10 keys (100,200,...,950).
- [ ] **Step 3:** Add flat surface tokens to `$colors` map: `"cream": #F3EBE0`, `"surface": #FBF6EF`, `"beige": #EFE4D6`. (These become `--cream`, `--surface`, `--beige` via the existing `:root` generator.)
- [ ] **Step 4:** Build: `npm run build`. Expected: no SCSS errors; generated CSS contains `--cream` and terracotta `--primary`.
- [ ] **Step 5:** Commit: `git add -A && git commit -m "feat(theme): terracotta + cream color tokens"`

---

### Task 4: Typography → Playfair Display headings + larger radius

**Files:**
- Modify: `resources/scss/utils/_typography.scss`
- Modify: `resources/scss/utils/_base.scss` (or `_root.scss`) for body bg + radius

- [ ] **Step 1:** In `_typography.scss`, add the Playfair import alongside the IBM Plex import:
  `@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&display=swap');`
- [ ] **Step 2:** Add `$font-family-display: "Playfair Display", serif;` and `$border-radius-2xl: 20px;`.
- [ ] **Step 3:** In `_base.scss`, set body background to `var(--cream)` and apply `$font-family-display` to `h1, h2, .section-title, .display-title` (do not change body text font).
- [ ] **Step 4:** Build: `npm run build`. Expected: no errors.
- [ ] **Step 5:** Render check: `php artisan serve` bg; `curl -s http://127.0.0.1:8000/ | grep -i "Playfair"` should appear in the compiled CSS link/output (or verify `style.css` contains the import). Stop server.
- [ ] **Step 6:** Commit: `git add -A && git commit -m "feat(theme): Playfair display headings, cream body, large radius"`

---

### Task 5: Reskin header (GOLDHOUSE wordmark + terracotta)

**Files:**
- Modify: `resources/views/layout/partials/header.blade.php`
- Modify: `resources/scss/layout/_header.scss`

- [ ] **Step 1:** Update header markup: GOLDHOUSE-style wordmark/logo block, nav items kept, language switcher (EN/HY/RU) kept, search + menu icons. Do not change route/locale logic — only classes/markup.
- [ ] **Step 2:** In `_header.scss`, style nav on cream/transparent, terracotta hover/active, rounded pill CTA.
- [ ] **Step 3:** Build: `npm run build`. Expected: no errors.
- [ ] **Step 4:** Render check: home loads `200`, header shows wordmark + nav. Stop server.
- [ ] **Step 5:** Commit: `git add -A && git commit -m "feat(header): GOLDHOUSE wordmark + terracotta nav"`

---

### Task 6: Reskin home hero + Find Properties filter

**Files:**
- Modify: `resources/views/index.blade.php` (hero section)
- Modify: `resources/scss/pages/_index.scss`

- [ ] **Step 1:** Rebuild hero markup: serif headline "Build Your Dream Home" (second line terracotta), left stats column bound to existing `$stats` (`activeProperties`, experience years, `happyClients`), "with Modern Design" search input + "Search Now" button, hero image, and bottom **Find Properties** panel (City / property type / bedroom + search button) wired to the existing property search form/action already in the template. Reuse existing form field names — do not invent new backend params.
- [ ] **Step 2:** In `_index.scss`, style hero: cream gradient bg, rounded hero card (`$border-radius-2xl`), serif headline, floating search pill, Find Properties panel as a rounded surface card.
- [ ] **Step 3:** Build: `npm run build`. Expected: no errors.
- [ ] **Step 4:** Render check: home `200`; hero headline + Find Properties panel visible. Stop server.
- [ ] **Step 5:** Commit: `git add -A && git commit -m "feat(home): GOLDHOUSE hero + find-properties panel"`

---

### Task 7: Reskin home — Trusted Advisors + Smart Technology sections

**Files:**
- Modify: `resources/views/index.blade.php`
- Modify: `resources/scss/pages/_index.scss`

- [ ] **Step 1:** Add/restyle "Your Trusted Real Estate Advisors" section: heading + copy, 4 stat cards (3 light surface + 1 terracotta-filled) bound to available stats, image cluster with circular logo badge (use existing `$cityImages`/`$topViewedImages` data where available, otherwise static placeholders from `resources/img`).
- [ ] **Step 2:** Add "Modern Homes Built with Smart Technology" heading block (serif) + supporting copy.
- [ ] **Step 3:** Style both in `_index.scss` (stat cards, terracotta fill, image cluster, badge).
- [ ] **Step 4:** Build: `npm run build`. Expected: no errors.
- [ ] **Step 5:** Render check: sections visible on home. Stop server.
- [ ] **Step 6:** Commit: `git add -A && git commit -m "feat(home): advisors + smart-technology sections"`

---

### Task 8: Reskin home — Our Services + Catalog carousel

**Files:**
- Modify: `resources/views/index.blade.php`
- Modify: `resources/scss/pages/_index.scss`
- Modify: `resources/views/components/property-card.blade.php` (visual only, if needed)

- [ ] **Step 1:** Add "Our Services" section: wide banner image + 4 terracotta service cards with white icon badges (Affordable Property, Guaranteed Quality, Fast and Easy Process, Property Insurance). Use existing `<x-icon>` component for icons.
- [ ] **Step 2:** Add "Catalog of Our Properties" horizontal carousel using the existing `property-card` component over `$saleProperties`/`$rentProperties`, with prev/next controls. Reuse any existing slider plugin already bundled (`resources/plugins`); if none, use simple CSS scroll-snap.
- [ ] **Step 3:** Restyle `property-card` visuals (rounded, terracotta accents) without changing its data contract.
- [ ] **Step 4:** Build: `npm run build`. Expected: no errors.
- [ ] **Step 5:** Render check: services cards + catalog carousel visible. Stop server.
- [ ] **Step 6:** Commit: `git add -A && git commit -m "feat(home): services cards + catalog carousel"`

---

### Task 9: Reskin footer (GOLDHOUSE 4-column)

**Files:**
- Modify: `resources/views/layout/partials/footer.blade.php`
- Modify: `resources/scss/layout/_footer.scss`

- [ ] **Step 1:** Rebuild footer: GOLDHOUSE logo + tagline ("Our vision is to make all people the best place to live for them."), four link columns (Quick Links / Company / Support / Movement) using existing localized link helpers/routes, bottom Terms & Privacy row. Keep existing route names.
- [ ] **Step 2:** Style in `_footer.scss`: cream/beige bg, muted link colors, terracotta hover.
- [ ] **Step 3:** Build: `npm run build`. Expected: no errors.
- [ ] **Step 4:** Render check: footer columns render on home. Stop server.
- [ ] **Step 5:** Commit: `git add -A && git commit -m "feat(footer): GOLDHOUSE four-column footer"`

---

### Task 10: Final verification pass

**Files:** none.

- [ ] **Step 1:** Clean build: `npm run build` → no errors/warnings about missing partials.
- [ ] **Step 2:** Boot and check all locale roots return 200: `/`, `/en`, `/ru`, `/hy`, plus `/property`, `/contact-us`, `/faq` (inherit new tokens, should not error). Use `curl -s -o /dev/null -w "%{http_code}\n"` for each.
- [ ] **Step 3:** Confirm property/listing/footer pages render with terracotta+cream theme (inherited tokens). Note any page needing later polish (out of scope for this plan).
- [ ] **Step 4:** Commit any final tweaks: `git add -A && git commit -m "chore: final reskin verification"`

---

## Self-Review

- **Spec coverage:** colors (T3), typography+radius (T4), home sections hero/advisors/smart-tech/services/catalog (T6–T8), header (T5), footer (T9), fork+build baseline (T1–T2), verification (T10). All spec sections mapped.
- **Placeholders:** none — each task has concrete files, exact tokens/values, and build+render checks.
- **Consistency:** token names (`--cream`, `--surface`, `--beige`, `$font-family-display`, `$border-radius-2xl`, primary `#A6644F`) used consistently across T3, T4, T6–T9.
- **Note:** TDD unit tests are not applicable to a CSS/Blade visual reskin; verification is build success + HTTP 200 render + visual confirmation, per Global Constraints.
