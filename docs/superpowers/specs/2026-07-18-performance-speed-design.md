# Performance & Speed Design — touchestate-demo

**Date:** 2026-07-18  
**Revised:** 2026-07-18 (incorporated colleague review)  
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

## Cache Key Versioning

All sessionStorage keys use a version prefix: `te_v1_*`.  
When a card template or compare table changes, bump the version (e.g. `te_v2_*`) — old cache is automatically ignored without any explicit invalidation code.

Current version: **v1**

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

## Fix 2 — sessionStorage Cache (Favorites + Compare)

### Why different strategies for each

- **Favorites cards** are independent of each other — each card renders the same regardless of what other cards exist. Per-slug HTML fragment caching is ideal: add 1 new item → only 1 slug fetched from server.
- **Compare table** is a cross-property structure (columns = properties, rows = attributes) with Laravel translations and PHP-computed highlights. Moving this to JS would require duplicating all translations and highlight logic client-side. Instead: cache the full rendered HTML blob keyed by the sorted slug list. Single source of truth stays in PHP.

---

### 2a — Favorites (per-slug HTML fragment cache)

**Files:** `app/Http/Controllers/FavoritesController.php`, `resources/js/app.js`

**Backend change:** `/favorites/load` returns per-slug HTML fragments instead of one HTML blob:
```json
{
  "items": [
    { "slug": "abc-123", "html": "<article>...</article>" },
    { "slug": "def-456", "html": "..." }
  ],
  "validSlugs": ["abc-123", "def-456"]
}
```

Each `html` fragment contains only the fields needed to render the card (title, price, image, address, area, rooms). The server does not include heavy nested data (full media arrays, SEO fields, history, etc.) in the fragment — it renders only what the card template uses.

**sessionStorage key:** `te_v1_card:{slug}` → stores `{html, ts}` where `ts` is `Date.now()`.  
**TTL:** 30 minutes (`Date.now() - ts < 1_800_000`). Expired entries are re-fetched.

**Client logic in `listLoader`:**
1. Read slugs from localStorage
2. For each slug check `te_v1_card:{slug}` in sessionStorage (skip if expired)
3. POST to `/favorites/load` with **only uncached slugs**
4. Cache each returned fragment as `te_v1_card:{slug}` in sessionStorage
5. Assemble final HTML in original slug order from cache + fresh fragments
6. Render assembled HTML

**Cache invalidation:** When a slug is removed from favorites, delete `te_v1_card:{slug}` from sessionStorage immediately.

**Result:** 5 favorites, add 1 new → only 1 slug goes to server, 5 are instant from sessionStorage.

---

### 2b — Compare (full HTML blob cache keyed by slug set)

**Files:** `app/Http/Controllers/CompareController.php`, `resources/js/app.js`

**Why full blob:** The compare table uses Laravel translations (`__('property.xxx')`) and PHP-computed highlights (renovation scores, construction scores, price normalization). Duplicating this in JS would create two sources of truth. Keeping all logic in PHP is correct.

**sessionStorage key:** `te_v1_cmp:{hash}` where `hash` = sorted slugs joined with `,` and hashed (or just used as-is if under 200 chars). Stores `{html, ts}`.  
**TTL:** 30 minutes.

**Client sends ALL slugs every time.** Server:
- Fetches all properties via `ParallelPropertyFetcher` (all hit `te_prop:{slug}` cache — instant)
- Computes highlights across all properties
- Renders full compare table HTML
- Returns `{ html, count, slugs }`

**Client logic:**
1. Read slugs from localStorage
2. Compute cache key from sorted slugs
3. Check `te_v1_cmp:{key}` in sessionStorage — if valid (not expired), use instantly (0ms, 0 requests)
4. Otherwise: POST all slugs → server renders → client caches result → displays
5. When any slug added or removed: new key → old cache ignored automatically

**Cache invalidation:** Automatic — key encodes the exact slug set. No manual deletion needed.

**Result:** Same slug set → 0ms, 0 requests. Add/remove any slug → fast server render (< 200ms with nginx + cached te_prop data).

---

## Fix 3 — Proactive Server Cache Warming

**File:** `resources/js/app.js`

**When:** After page becomes fully interactive, using `requestIdleCallback` (fallback: `setTimeout(fn, 200)`) so user interactions always take priority over background work.

**Logic:**
```js
function warmServerCache() {
    const favSlugs = JSON.parse(localStorage.getItem('te_favorites') || '[]');
    const cmpSlugs = JSON.parse(localStorage.getItem('te_compare')  || '[]');
    const allSlugs = [...new Set([...favSlugs, ...cmpSlugs])];

    const V = 'te_v1_';
    const toWarm = allSlugs.filter(slug => {
        const card = sessionStorage.getItem(V + 'card:' + slug);
        const data = sessionStorage.getItem(V + 'data:' + slug);
        if (!card && !data) return true;
        // also re-warm if sessionStorage entry is expired
        try {
            const ts = JSON.parse(card || data).ts;
            return Date.now() - ts > 1_800_000;
        } catch { return true; }
    });

    const lang = ['/en', '/ru', '/hy'].includes(window.location.pathname.slice(0, 3))
        ? window.location.pathname.slice(0, 3) : '';

    // Max 10 concurrent low-priority prefetches
    toWarm.slice(0, 10).forEach(slug =>
        fetch(lang + '/property/' + slug, { priority: 'low' }).catch(() => {})
    );
}

// Run after page is idle — never block user interactions
if ('requestIdleCallback' in window) {
    requestIdleCallback(warmServerCache, { timeout: 3000 });
} else {
    setTimeout(warmServerCache, 200);
}
```

**Trigger:** `turbo:load` event (fires after every Turbo navigation).

**Result:** Even after browser restart (sessionStorage cleared), first visit to Compare/Favorites is fast because `te_prop:{slug}` (24h TTL) is already warm on the server.

---

## Fix 4 — Prefetch on toggleCmp

**File:** `resources/js/app.js`

Mirror the existing `toggleFav` prefetch for `toggleCmp`. When a property is added to compare, immediately fire a low-priority background fetch to warm `te_prop:{slug}` on the server:

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

**Result:** `te_prop:{slug}` is warmed the moment user adds a property to compare.

---

## Files Changed

| File | Change |
|------|--------|
| `resources/js/app.js` | Fix Turbo/Alpine lifecycle; sessionStorage cache for favorites + compare; warmServerCache with requestIdleCallback; toggleCmp prefetch |
| `app/Http/Controllers/FavoritesController.php` | Return `{items:[{slug,html}]}` per-slug fragments |
| `app/Http/Controllers/CompareController.php` | Return `{html, count, slugs}` full blob (no structural change, just response format stays same) |
| `resources/views/map.blade.php` | Add `<meta name="turbo-cache-control" content="no-preview">` |

---

## What Does NOT Change

- Skeleton-first rendering pattern on all pages
- Server-side cache keys and TTLs (`te_prop`, `te_list`, `te_workspace` etc.)
- `ParallelPropertyFetcher` service
- All routes
- Compare highlight logic (`CompareController::computeHighlights`) — PHP stays single source of truth
- All Blade views except `map.blade.php`
- `lang/` translations
- `partials/compare-table.blade.php`

---

## Success Criteria

- Back/forward navigation between any two pages: instant (Turbo snapshot)
- Second visit to Compare with same slugs: 0 server requests, < 10ms render
- Second visit to Favorites with same slugs: 0 server requests per cached slug
- Adding 1 new item to 4 existing favorites: only 1 slug fetched from server
- Adding 1 new item to 3 existing compare items: server renders full table fast (< 200ms, all te_prop cached)
- First visit after browser restart: fast (server cache pre-warmed by Fix 3 + Fix 4)
- Changing card/table template: bump cache version → stale cache auto-invalidated
