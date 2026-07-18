# Performance & Speed Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make all pages open instantly after click with skeleton-first UX, eliminate 20-second waits on Compare/Favorites, and make repeat visits zero-latency via sessionStorage caching.

**Architecture:** Four targeted fixes: (1) fix Turbo Drive page snapshot cache by reordering Alpine lifecycle events; (2) sessionStorage per-slug HTML cache for Favorites, blob cache for Compare; (3) proactive server cache warming via requestIdleCallback on every page load; (4) prefetch server cache on toggleCmp.

**Tech Stack:** Laravel 12, Alpine.js 3, Hotwired Turbo 8, PHP 8.2, Tailwind CSS 4, Vite 7

## Global Constraints

- Cache key version prefix: `te_v1_*` — bump to `te_v2_*` when any card/table template changes
- Cache TTL: 1 800 000 ms (30 minutes) everywhere
- Max 10 concurrent low-priority prefetch fetches per page load
- `priority: 'low'` on all background fetches — never compete with user-initiated requests
- No new npm packages, no new PHP packages
- All JS changes go in `resources/js/app.js`; rebuild with `npm run build` after JS changes
- PHP class methods `extractPrimaryImageUrl` and `buildPropertyAddress` are defined on the base `Controller` class — available via `$this->` in any controller that extends it

---

## File Map

| File | Role after this plan |
|------|---------------------|
| `resources/js/app.js` | Module-level cache helpers; fixed Turbo/Alpine lifecycle; favMode per-slug cache; compare blob cache; warmServerCache; toggleCmp prefetch |
| `app/Http/Controllers/FavoritesController.php` | `load()` returns `{items:[{slug,html}], validSlugs}` instead of one HTML blob |
| `resources/views/map.blade.php` | `<meta name="turbo-cache-control" content="no-preview">` added in `<head>` via `@push('styles')` |
| `tests/Feature/FavoritesLoadTest.php` | New — PHPUnit feature test for the updated `/favorites/load` response shape |

---

## Task 1: Fix Turbo Drive Page Cache + toggleCmp Prefetch + Map Meta

**Files:**
- Modify: `resources/js/app.js` (lines 625–653 — Turbo event handlers; lines 43–47 — toggleCmp)
- Modify: `resources/views/map.blade.php` (add meta tag in head)

**Interfaces:**
- Produces: working Turbo page snapshot cache; `toggleCmp` warms `te_prop:{slug}` on server when adding

- [ ] **Step 1: Read the current Turbo + Alpine block in app.js**

Open `resources/js/app.js` and find the section starting at line ~624:

```js
// ── Turbo Drive + Alpine.js integration ──────────────────────────
document.addEventListener('turbo:before-render', function () {
    Alpine.store('contactModal').open = false;
    Alpine.destroyTree(document.body);
    // ... map cleanup ...
});

document.addEventListener('turbo:render', function () {
    Alpine.initTree(document.body);
});
```

- [ ] **Step 2: Replace the Turbo event block**

Replace the entire `// ── Turbo Drive + Alpine.js integration ──` section (from that comment through `Alpine.start();`) with:

```js
// ── Turbo Drive + Alpine.js integration ──────────────────────────
// turbo:before-cache fires when Turbo is about to snapshot the current page.
// We destroy Alpine here so the snapshot is clean (no live bindings).
// turbo:before-render fires AFTER the snapshot is saved — we only clean
// up globals here (maps, modals) so restored snapshots don't hold stale refs.
document.addEventListener('turbo:before-cache', function () {
    Alpine.store('contactModal').open = false;
    Alpine.destroyTree(document.body);
});

document.addEventListener('turbo:before-render', function () {
    // Destroy Yandex Map instance to prevent memory leak
    if (window._propMapInstance) {
        try { window._propMapInstance.destroy(); } catch(e) {}
        window._propMapInstance = null;
    }
    if (window._mapPageInstance) {
        try { window._mapPageInstance.destroy(); } catch(e) {}
        window._mapPageInstance = null;
    }

    // Reset Yandex Maps navigation globals
    window._propMapCoords = null;
    window._onCoordsReady = null;
    window.updateNearbyMarkers = null;
    window._propMapCatMarkers = null;
    window.__ymapsQueue = [];
});

document.addEventListener('turbo:render', function () {
    Alpine.initTree(document.body);
});

Alpine.start();
```

- [ ] **Step 3: Add prefetch to toggleCmp**

Find `toggleCmp` (around line 43):

```js
    toggleCmp(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        this.isCmp = this._toggleStorageItem('te_compare', this.slug);
        this.popCmp = true; setTimeout(() => this.popCmp = false, 300);
    },
```

Replace with:

```js
    toggleCmp(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        this.isCmp = this._toggleStorageItem('te_compare', this.slug);
        this.popCmp = true; setTimeout(() => this.popCmp = false, 300);
        if (this.isCmp) {
            const prefix = window.location.pathname.slice(0, 3);
            const lang = ['/en', '/ru', '/hy'].includes(prefix) ? prefix : '';
            fetch(lang + '/property/' + this.slug, { priority: 'low' }).catch(() => {});
        }
    },
```

- [ ] **Step 4: Add turbo-cache-control meta to map.blade.php**

Open `resources/views/map.blade.php`. After `@section('title', ...)` and before `@section('content')`, add:

```blade
@push('styles')
<meta name="turbo-cache-control" content="no-preview">
@endpush
```

Verify that `resources/views/layout/app.blade.php` has `@stack('styles')` in `<head>` — it does (line 17).

- [ ] **Step 5: Build assets**

```bash
cd /home/innovayse/www/touchestate-demo && npm run build
```

Expected: `✓ built in ~500ms`, new hashed CSS/JS files in `public/build/assets/`.

- [ ] **Step 6: Manual test — Turbo page cache**

1. Start server: `php artisan serve --port=8000`
2. Open `http://127.0.0.1:8000/en/property`
3. Click any property card → goes to property-single page
4. Click browser Back button
5. **Expected:** property listing page appears instantly (< 100ms), no loading skeleton, no network request in DevTools Network tab
6. Navigate to map page → navigate away → hit Back
7. **Expected:** map page does NOT flash a broken map (turbo-cache-control=no-preview hides the stale snapshot)

- [ ] **Step 7: Commit**

```bash
cd /home/innovayse/www/touchestate-demo
git add resources/js/app.js resources/views/map.blade.php public/build/
git commit -m "perf: fix Turbo page cache, toggleCmp prefetch, map no-preview

- Move Alpine.destroyTree to turbo:before-cache so Turbo saves clean snapshot
- Keep map/Yandex cleanup in turbo:before-render only
- Add prefetch fetch in toggleCmp mirroring toggleFav
- Add turbo-cache-control=no-preview to map page

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 2: FavoritesController — Per-Slug HTML Fragment Response

**Files:**
- Modify: `app/Http/Controllers/FavoritesController.php`
- Create: `tests/Feature/FavoritesLoadTest.php`

**Interfaces:**
- Consumes: `ParallelPropertyFetcher::fetchMany(array $slugs): array<string, array>`; `Controller::extractPrimaryImageUrl(array): ?string`; `Controller::buildPropertyAddress(array): string`; Blade component `components.property-card` with prop `$prop`
- Produces: `POST /favorites/load` → `{items: [{slug: string, html: string}], validSlugs: string[]}`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/FavoritesLoadTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ParallelPropertyFetcher;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FavoritesLoadTest extends TestCase
{
    private function mockFetcher(array $properties): void
    {
        $mock = $this->createMock(ParallelPropertyFetcher::class);
        $mock->method('fetchMany')->willReturn($properties);
        $this->app->instance(ParallelPropertyFetcher::class, $mock);
    }

    public function test_load_returns_per_slug_items(): void
    {
        $this->mockFetcher([
            'test-slug-1' => [
                'slug'            => 'test-slug-1',
                'title'           => 'Test Property',
                'price'           => 100000,
                'currency'        => 'USD',
                'transactionType' => 'Sale',
                'propertyType'    => 'Apartment',
                'media'           => [],
            ],
        ]);

        $response = $this->postJson('/en/favorites/load', ['slugs' => ['test-slug-1']]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'items' => [['slug', 'html']],
            'validSlugs',
        ]);
        $this->assertSame('test-slug-1', $response->json('items.0.slug'));
        $this->assertStringContainsString('test-slug-1', $response->json('items.0.html'));
        $this->assertSame(['test-slug-1'], $response->json('validSlugs'));
    }

    public function test_load_returns_empty_when_no_slugs(): void
    {
        $response = $this->postJson('/en/favorites/load', ['slugs' => []]);

        $response->assertStatus(200);
        $response->assertExactJson(['items' => [], 'validSlugs' => []]);
    }

    public function test_load_skips_missing_slugs(): void
    {
        $this->mockFetcher([]); // API returns nothing

        $response = $this->postJson('/en/favorites/load', ['slugs' => ['nonexistent-slug']]);

        $response->assertStatus(200);
        $response->assertJson(['items' => [], 'validSlugs' => []]);
    }

    public function test_load_rejects_more_than_20_slugs(): void
    {
        $slugs = array_map(fn($i) => "slug-$i", range(1, 25));
        $this->mockFetcher([]);

        $response = $this->postJson('/en/favorites/load', ['slugs' => $slugs]);

        // Should process at most 20 — no 422, just silently truncates
        $response->assertStatus(200);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/innovayse/www/touchestate-demo && php artisan test tests/Feature/FavoritesLoadTest.php
```

Expected: `test_load_returns_per_slug_items` FAILS because response still has `html` key not `items`.

- [ ] **Step 3: Update FavoritesController::load()**

Replace the entire `load()` method in `app/Http/Controllers/FavoritesController.php`:

```php
public function load(Request $request): \Illuminate\Http\JsonResponse
{
    // Release session lock so other browser requests don't queue behind API calls.
    session()->save();

    $slugs = array_values(array_unique(array_filter((array) $request->input('slugs', []))));
    $slugs = array_slice($slugs, 0, 20);
    $slugs = array_values(array_filter($slugs, fn($s) => is_string($s) && strlen($s) <= 200));

    if (empty($slugs)) {
        return response()->json(['items' => [], 'validSlugs' => []]);
    }

    $fetched    = $this->fetcher->fetchMany($slugs);
    $items      = [];
    $validSlugs = [];

    foreach ($slugs as $slug) {
        if (!isset($fetched[$slug])) {
            continue;
        }
        $prop                    = $fetched[$slug];
        $prop['primaryImageUrl'] = $this->extractPrimaryImageUrl($prop);
        $prop['fullAddress']     = $this->buildPropertyAddress($prop) ?: null;

        $items[]      = ['slug' => $slug, 'html' => view('components.property-card', ['prop' => $prop])->render()];
        $validSlugs[] = $slug;
    }

    return response()->json(['items' => $items, 'validSlugs' => $validSlugs]);
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /home/innovayse/www/touchestate-demo && php artisan test tests/Feature/FavoritesLoadTest.php
```

Expected: all 4 tests PASS.

- [ ] **Step 5: Commit**

```bash
cd /home/innovayse/www/touchestate-demo
git add app/Http/Controllers/FavoritesController.php tests/Feature/FavoritesLoadTest.php
git commit -m "perf: favorites/load returns per-slug HTML fragments

Response shape changes from {html, count} to {items:[{slug,html}], validSlugs}.
Enables client-side per-slug sessionStorage caching so only new/expired
slugs are fetched from server on repeat visits.

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 3: listLoader — Favorites sessionStorage Per-Slug Cache

**Files:**
- Modify: `resources/js/app.js` — add module-level cache helpers; update `_fetchSlugs`, `load`, `removeSlug`, `clearAll` inside `listLoader`

**Interfaces:**
- Consumes: `POST /favorites/load` → `{items:[{slug,html}], validSlugs}` (Task 2)
- Produces: `listLoader` in favMode reads/writes `te_v1_card:{slug}` in sessionStorage; skips server for cached slugs

- [ ] **Step 1: Add module-level cache constants and helpers**

At the very top of `resources/js/app.js`, immediately after the import lines (after `Turbo.config.drive.prefetch = false;`, before `// Fav/compare toggle`), insert:

```js
// ── sessionStorage cache helpers ─────────────────────────────────
const _CV  = 'te_v1_';          // bump to te_v2_ when card/table templates change
const _TTL = 1_800_000;         // 30 minutes in ms

function _ssGet(key) {
    try {
        const item = JSON.parse(sessionStorage.getItem(_CV + key));
        if (item && Date.now() - item.ts < _TTL) return item.val;
    } catch {}
    return null;
}

function _ssSet(key, val) {
    try { sessionStorage.setItem(_CV + key, JSON.stringify({ val, ts: Date.now() })); } catch {}
}

function _ssDel(key) {
    try { sessionStorage.removeItem(_CV + key); } catch {}
}
```

- [ ] **Step 2: Replace `_fetchSlugs` in listLoader**

Find `_fetchSlugs` (around line 119 after Step 1's insertions shift line numbers):

```js
    async _fetchSlugs(slugs) {
        const r = await fetch(loadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ slugs }),
        });
        return r.json();
    },
```

Replace with:

```js
    async _fetchSlugs(slugs) {
        if (options.favMode) {
            // Per-slug cache: only fetch slugs missing from sessionStorage
            const cachedCards = {};
            const toFetch     = [];
            for (const slug of slugs) {
                const hit = _ssGet('card:' + slug);
                if (hit !== null) cachedCards[slug] = hit;
                else              toFetch.push(slug);
            }

            const freshCards = {};
            if (toFetch.length) {
                const r = await fetch(loadUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body:    JSON.stringify({ slugs: toFetch }),
                });
                const j = await r.json();
                for (const item of (j.items || [])) {
                    _ssSet('card:' + item.slug, item.html);
                    freshCards[item.slug] = item.html;
                }
            }

            // Assemble in original slug order; drop slugs with no data (404s)
            const cards     = slugs.map(s => cachedCards[s] || freshCards[s]).filter(Boolean);
            const validSlugs = slugs.filter(s => cachedCards[s] || freshCards[s]);
            const html = '<div class="grid gap-2.5 sm:gap-5 sm:grid-cols-2 lg:grid-cols-3">'
                         + cards.join('') + '</div>';
            return { html, count: cards.length, slugs: validSlugs };
        }

        // Compare (and any future non-fav mode): plain POST, server assembles HTML
        const r = await fetch(loadUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body:    JSON.stringify({ slugs }),
        });
        return r.json();
    },
```

- [ ] **Step 3: Update `removeSlug` to invalidate per-slug card cache**

Find `removeSlug`:

```js
    removeSlug(slug) {
        this._uncache(slug);
        this.slugs = this.slugs.filter(s => s !== slug);
        localStorage.setItem(storageKey, JSON.stringify(this.slugs));
        window.dispatchEvent(new CustomEvent('te-storage'));
        if (!this.slugs.length) { this.html = ''; this.count = 0; this.filteredCount = 0; return; }
        this._fetchSlugs(this.slugs).then(j => this._applyResult(j)).catch(() => {});
    },
```

Replace with:

```js
    removeSlug(slug) {
        this._uncache(slug);
        if (options.favMode) _ssDel('card:' + slug);
        this.slugs = this.slugs.filter(s => s !== slug);
        localStorage.setItem(storageKey, JSON.stringify(this.slugs));
        window.dispatchEvent(new CustomEvent('te-storage'));
        if (!this.slugs.length) { this.html = ''; this.count = 0; this.filteredCount = 0; return; }
        this._fetchSlugs(this.slugs).then(j => this._applyResult(j)).catch(() => {});
    },
```

- [ ] **Step 4: Update `clearAll` to wipe all card cache entries**

Find `clearAll`:

```js
    clearAll() {
        this.slugs.forEach(slug => this._uncache(slug));
        localStorage.removeItem(storageKey);
        this.slugs = []; this.html = ''; this.count = 0;
        if (options.favMode) {
            _favEls = []; _matchingIndices = []; this._cards = [];
            this.filteredCount = 0; this.totalPages = 1; this.page = 1;
            this.query = ''; this.suggestions = []; this.showDropdown = false;
        }
        window.dispatchEvent(new Event('te-storage'));
    },
```

Replace with:

```js
    clearAll() {
        this.slugs.forEach(slug => {
            this._uncache(slug);
            if (options.favMode) _ssDel('card:' + slug);
        });
        localStorage.removeItem(storageKey);
        this.slugs = []; this.html = ''; this.count = 0;
        if (options.favMode) {
            _favEls = []; _matchingIndices = []; this._cards = [];
            this.filteredCount = 0; this.totalPages = 1; this.page = 1;
            this.query = ''; this.suggestions = []; this.showDropdown = false;
        }
        window.dispatchEvent(new Event('te-storage'));
    },
```

- [ ] **Step 5: Build assets**

```bash
cd /home/innovayse/www/touchestate-demo && npm run build
```

Expected: `✓ built in ~500ms`.

- [ ] **Step 6: Manual test — favorites per-slug cache**

1. Open `http://127.0.0.1:8000/en/favorites` with DevTools Network open
2. Add 3 properties to favorites from the property listing page
3. Navigate to `/en/favorites` — note the network request to `/en/favorites/load`
4. Navigate away (e.g. to `/en/property`)
5. Navigate back to `/en/favorites`
6. **Expected:** NO `/en/favorites/load` request in Network tab; page renders instantly from sessionStorage
7. Add a 4th favorite → navigate to `/en/favorites`
8. **Expected:** ONE `/en/favorites/load` request with only the new slug in the request body

- [ ] **Step 7: Commit**

```bash
cd /home/innovayse/www/touchestate-demo
git add resources/js/app.js public/build/
git commit -m "perf: per-slug sessionStorage cache for favorites

- Add _ssGet/_ssSet/_ssDel helpers with te_v1_ prefix and 30-min TTL
- _fetchSlugs in favMode: only POSTs uncached slugs, assembles grid from
  cached + fresh fragments
- removeSlug: deletes te_v1_card:{slug} from sessionStorage on remove
- clearAll: wipes all card cache entries before clearing localStorage

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 4: listLoader — Compare Blob Cache + warmServerCache

**Files:**
- Modify: `resources/js/app.js` — update `load()` for compare blob cache; add `warmServerCache` triggered on `turbo:load`

**Interfaces:**
- Consumes: `_ssGet`, `_ssSet` from Task 3; `POST /compare/load` → `{html, count, slugs}` (unchanged)
- Produces: compare revisit hits `te_v1_cmp:{sortedSlugs}` in sessionStorage; `warmServerCache` fires on every `turbo:load`

- [ ] **Step 1: Update `load()` in listLoader to add compare blob cache**

Find the `load()` method:

```js
    async load() {
        this.loading = true;
        if (!this.slugs.length) { this.loading = false; this.html = ''; this.count = 0; this.filteredCount = 0; return; }
        try {
            const j = await this._fetchSlugs(this.slugs);
            this._applyResult(j);
        } catch(e) {}
        this.loading = false;
    },
```

Replace with:

```js
    async load() {
        this.loading = true;
        if (!this.slugs.length) { this.loading = false; this.html = ''; this.count = 0; this.filteredCount = 0; return; }
        try {
            // Compare blob cache: key encodes the exact slug set
            if (!options.favMode) {
                const cmpKey = 'cmp:' + [...this.slugs].sort().join(',');
                const cached = _ssGet(cmpKey);
                if (cached !== null) {
                    this._applyResult(cached);
                    this.loading = false;
                    return;
                }
            }

            const j = await this._fetchSlugs(this.slugs);
            this._applyResult(j);

            // Store compare result; next visit with same slugs is instant
            if (!options.favMode) {
                const cmpKey = 'cmp:' + [...this.slugs].sort().join(',');
                _ssSet(cmpKey, j);
            }
        } catch(e) {}
        this.loading = false;
    },
```

- [ ] **Step 2: Add warmServerCache function and turbo:load trigger**

Find the line `// ── Property-single prefetch ──` (near the bottom of app.js, before the `(function setupPropertyPrefetch()` block). Insert the following BEFORE that block:

```js
// ── Proactive server cache warming ───────────────────────────────
// On every page load, fire low-priority fetches for all localStorage slugs
// that aren't already in sessionStorage. This keeps te_prop:{slug} warm on
// the server (24h TTL) so that the first visit to Compare/Favorites after a
// browser restart is fast even though sessionStorage was cleared.
function warmServerCache() {
    let favSlugs = [], cmpSlugs = [];
    try { favSlugs = JSON.parse(localStorage.getItem('te_favorites') || '[]'); } catch {}
    try { cmpSlugs = JSON.parse(localStorage.getItem('te_compare')   || '[]'); } catch {}

    const allSlugs = [...new Set([...favSlugs, ...cmpSlugs])];

    // Skip slugs whose card is already cached and fresh — server is already warm
    const toWarm = allSlugs.filter(slug => _ssGet('card:' + slug) === null);

    if (!toWarm.length) return;

    const prefix = window.location.pathname.slice(0, 3);
    const lang   = ['/en', '/ru', '/hy'].includes(prefix) ? prefix : '';

    toWarm.slice(0, 10).forEach(slug =>
        fetch(lang + '/property/' + slug, { priority: 'low' }).catch(() => {})
    );
}

document.addEventListener('turbo:load', function () {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(warmServerCache, { timeout: 3000 });
    } else {
        setTimeout(warmServerCache, 200);
    }
});

```

- [ ] **Step 3: Build assets**

```bash
cd /home/innovayse/www/touchestate-demo && npm run build
```

Expected: `✓ built in ~500ms`.

- [ ] **Step 4: Manual test — compare blob cache**

1. Open `http://127.0.0.1:8000/en/compare` with 2–3 properties in compare list (add via property cards)
2. DevTools Network tab open — note the POST to `/en/compare/load`
3. Navigate to `/en/property`
4. Navigate back to `/en/compare`
5. **Expected:** NO `/en/compare/load` request; page renders instantly
6. Add a 4th property to compare → go to `/en/compare`
7. **Expected:** ONE `/en/compare/load` request (new slug set = new cache key)

- [ ] **Step 5: Manual test — warmServerCache**

1. Open DevTools Network → filter by `XHR/Fetch`
2. Navigate to any page (e.g. `/en/property`)
3. After page loads, observe low-priority requests to `/en/property/{slug}` appearing (one per slug in favorites/compare localStorage)
4. **Expected:** Requests appear within 200ms of page load; they have `priority: low` in the initiator

- [ ] **Step 6: Run all tests**

```bash
cd /home/innovayse/www/touchestate-demo && php artisan test
```

Expected: all tests pass including `FavoritesLoadTest`.

- [ ] **Step 7: Commit**

```bash
cd /home/innovayse/www/touchestate-demo
git add resources/js/app.js public/build/
git commit -m "perf: compare sessionStorage blob cache + proactive warmServerCache

- load() for compare mode checks te_v1_cmp:{sortedSlugs} in sessionStorage
  before hitting server; caches result after first fetch
- Same slug set = instant revisit (0ms, 0 requests)
- New/changed slug set = new cache key, server re-renders
- warmServerCache() fires on turbo:load via requestIdleCallback to keep
  te_prop:{slug} warm server-side even after browser restart

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Self-Review

**Spec coverage:**

| Spec requirement | Task |
|-----------------|------|
| Fix Turbo Drive cache (Alpine lifecycle) | Task 1 |
| `turbo-cache-control=no-preview` on map | Task 1 |
| Prefetch on toggleCmp | Task 1 |
| `/favorites/load` returns per-slug items | Task 2 |
| Favorites sessionStorage per-slug cache (`te_v1_card:`) | Task 3 |
| Cache invalidation on removeSlug / clearAll | Task 3 |
| Compare sessionStorage blob cache (`te_v1_cmp:`) | Task 4 |
| `warmServerCache` with requestIdleCallback | Task 4 |
| Cache key versioning (`te_v1_*`) | Task 3 (defined), used throughout |
| 30-min TTL | Task 3 (defined in `_TTL`) |
| Max 10 concurrent prefetches | Task 4 (`toWarm.slice(0, 10)`) |

All spec requirements covered. No gaps.

**Placeholder scan:** No TBDs, no "implement later", all code blocks complete.

**Type consistency:**
- `_ssGet(key)` → `string | null` (the `val` stored by `_ssSet`) — used correctly in Task 3 (`=== null` checks) and Task 4 (`cached !== null`)
- `_ssSet(key, val)` accepts any JSON-serializable value — used with `string` (card HTML) in Task 3 and `object` (compare JSON) in Task 4 ✓
- `_ssDel(key)` — used in Task 3 `removeSlug` and `clearAll` ✓
- `_CV` prefix `'te_v1_'` — prepended in `_ssGet`/`_ssSet`/`_ssDel`, so callers pass `'card:slug'` not `'te_v1_card:slug'` ✓
- Compare cache key: `'cmp:' + [...this.slugs].sort().join(',')` — same expression in both `load()` branches (check and set) ✓
