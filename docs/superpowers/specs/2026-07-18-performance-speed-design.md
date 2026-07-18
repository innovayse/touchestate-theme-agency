# Performance & Speed Design — touchestate-demo

**Date:** 2026-07-18
**Approach:** Variant A — Targeted fixes, no architecture change
**Goal:** All pages open instantly after click (skeleton-first), repeat visits are instant, no 20-second waits on Compare/Favorites

---

## Context

The site is a Laravel 12 + Alpine.js + Hotwired Turbo multi-page app consuming the TouchEstate external API. It will be served by nginx + PHP-FPM in production. The current bottlenecks are:

1. Turbo Drive page cache is broken due to Alpine lifecycle ordering
2. Compare/Favorites HTML is never cached client-side — every visit POSTs to server
3. No prefetch when adding to Compare
4. No proactive server-cache warming for existing localStorage slugs

All pages already use skeleton-first rendering (shell → async content). This pattern is kept.

---

## Fix 1 — Turbo Drive Page Cache

**File:** `resources/js/app.js`

**Problem:** `Alpine.destroyTree(document.body)` is called in `turbo:before-render`. Turbo saves the page snapshot *before* firing `turbo:before-render`, so the snapshot contains live Alpine bindings — but when Turbo *restores* that snapshot, Alpine is not re-initialized on the stale DOM, leaving the page broken.

**Fix:** Move `Alpine.destroyTree` to `turbo:before-cache`. This event fires exactly when Turbo is about to save the snapshot. The cleaned DOM is what gets stored.

**Event lifecycle after fix:**
```
turbo:before-cache  → Alpine.destroyTree(body)   [Turbo saves clean snapshot]
turbo:before-render → cleanup globals only        [maps, modals, Yandex instances]
turbo:render        → Alpine.initTree(body)       [works for both fresh + restored pages]
```

**Additional:** Add `<meta name="turbo-cache-control" content="no-preview">` to `map.blade.php` to prevent Turbo from flashing a stale map snapshot while the real map loads.

**Result:** Back/forward navigation between all pages is instant — served from Turbo's in-memory snapshot cache, zero server requests.

---

## Fix 2 — Per-slug sessionStorage Cache (Favorites + Compare)

### 2a — Favorites

**Files:** `app/Http/Controllers/FavoritesController.php`, `resources/js/app.js`

**Backend change:** `/favorites/load` returns per-slug HTML fragments instead of one HTML blob:
```json
{
  "items": [
    { "slug": "abc-123", "html": "<div class=\"property-card\">...</div>" },
    { "slug": "def-456", "html": "..." }
  ],
  "validSlugs": ["abc-123", "def-456"]
}
```

**Client logic in `listLoader`:**
1. Read slugs from localStorage
2. For each slug check sessionStorage for `te_card:{slug}`
3. POST to `/favorites/load` with **only uncached slugs**
4. Cache each returned fragment as `te_card:{slug}` in sessionStorage (TTL: 30 min via timestamp)
5. Assemble final HTML in original slug order from cache + fresh fragments
6. Render assembled HTML

**Cache invalidation:** When a slug is removed from favorites, delete `te_card:{slug}` from sessionStorage immediately.

**Result:** Adding 1 new favorite to 5 existing → only 1 slug goes to server, 5 are instant from sessionStorage.

### 2b — Compare

**Files:** `app/Http/Controllers/CompareController.php`, `resources/js/app.js`

**Backend change:** `/compare/load` returns per-slug JSON data instead of rendered HTML:
```json
{
  "items": [
    { "slug": "abc-123", "data": { ...full property object... } },
    { "slug": "def-456", "data": { ... } }
  ],
  "validSlugs": ["abc-123", "def-456"]
}
```

**Client logic in `listLoader` (compare mode):**
1. Read slugs from localStorage
2. For each slug check sessionStorage for `te_data:{slug}`
3. POST to `/compare/load` with **only uncached slugs**
4. Cache each returned data object as `te_data:{slug}` in sessionStorage (TTL: 30 min)
5. Assemble full property data array in original slug order
6. Compute highlights in JS (port of `CompareController::computeHighlights` logic)
7. Render compare table using Alpine client-side template

**Cache invalidation:** When a slug is removed from compare, delete `te_data:{slug}` from sessionStorage immediately.

**Client-side highlight logic (JS port of PHP):**
- Renovation score map: `{Designer:10, Capital:9, Euro:8, Cosmetic:6, Partial:4, Old:2, Unrenovated:1}`
- Construction score map: `{Monolithic:10, Stone:9, Brick:8, Strip:5, Panel:4, Wood:3}`
- Price normalization: daily rent × 30 for cross-type comparison
- `bestSlugs(properties, getValue, 'min'|'max')` → array of winning slugs
- Output: same `highlights` object structure as current PHP

**Compare table template:** A JS function `renderCompareTable(properties, highlights)` that generates the table HTML string and sets it via `innerHTML`. This mirrors the structure of `partials/compare-table.blade.php` but runs entirely client-side. The Blade partial is kept but no longer called from CompareController — it becomes dead code that can be removed later.

**Result:** Adding 1 new property to compare with 3 existing → only 1 slug fetched from server, table rebuilt instantly from 4 data objects.

---

## Fix 3 — Proactive Server Cache Warming

**File:** `resources/js/app.js`

**When:** On every page load (`turbo:load` event), after Alpine initializes.

**Logic:**
```js
function warmServerCache() {
    const favSlugs  = JSON.parse(localStorage.getItem('te_favorites') || '[]');
    const cmpSlugs  = JSON.parse(localStorage.getItem('te_compare')  || '[]');
    const allSlugs  = [...new Set([...favSlugs, ...cmpSlugs])];

    // Skip slugs already cached client-side (no need to warm server for those)
    const toWarm = allSlugs.filter(slug =>
        !sessionStorage.getItem('te_card:' + slug) &&
        !sessionStorage.getItem('te_data:' + slug)
    );

    // Fire max 10 concurrent low-priority prefetches
    const lang   = ['/en', '/ru', '/hy'].includes(window.location.pathname.slice(0, 3))
        ? window.location.pathname.slice(0, 3) : '';
    toWarm.slice(0, 10).forEach(slug =>
        fetch(lang + '/property/' + slug, { priority: 'low' }).catch(() => {})
    );
}
```

**Result:** Even after browser restart (sessionStorage cleared), the first visit to Compare/Favorites is fast because Laravel's `te_prop:{slug}` (24h TTL) was already warmed in the background.

---

## Fix 4 — Prefetch on toggleCmp

**File:** `resources/js/app.js`

Mirror the existing `toggleFav` prefetch for `toggleCmp`:

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

**Result:** Server cache `te_prop:{slug}` is warmed the moment user adds a property to compare.

---

## Files Changed

| File | Change |
|------|--------|
| `resources/js/app.js` | Fix Turbo/Alpine lifecycle; add sessionStorage cache logic; add warmServerCache(); fix toggleCmp prefetch |
| `app/Http/Controllers/FavoritesController.php` | Return per-slug `{items:[{slug,html}]}` instead of HTML blob |
| `app/Http/Controllers/CompareController.php` | Return per-slug `{items:[{slug,data}]}` JSON instead of rendered HTML |
| `resources/views/compare.blade.php` | Add client-side compare table Alpine template |
| `resources/views/map.blade.php` | Add `<meta name="turbo-cache-control" content="no-preview">` |

---

## What Does NOT Change

- Skeleton-first rendering pattern on all pages — kept as-is
- Server-side cache keys and TTLs (`te_prop`, `te_list`, `te_workspace` etc.) — unchanged
- `ParallelPropertyFetcher` service — unchanged
- All routes — unchanged
- All Blade views except compare.blade.php and map.blade.php — unchanged
- `lang/` translations — unchanged

---

## Success Criteria

- Navigating back/forward between any two pages: instant (Turbo snapshot)
- Second visit to Compare with same slugs: 0 server requests, < 10ms render
- Second visit to Favorites with same slugs: 0 server requests, < 10ms render
- Adding 1 new object to 4 existing in Compare: only 1 slug fetched from server
- Adding 1 new object to 4 existing in Favorites: only 1 slug fetched from server
- First visit after browser restart: fast (server cache pre-warmed by Fix 3 + Fix 4)
