import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import * as Turbo from '@hotwired/turbo';

window.Alpine = Alpine;
Alpine.plugin(collapse);

// Show progress bar immediately on click (default is 500ms delay)
Turbo.config.drive.progressBarDelay = 0;
// Disable Turbo's built-in hover prefetch — we use IntersectionObserver prefetch instead
Turbo.config.drive.prefetch = false;

// Fav/compare toggle — used on property cards and property-single hero
Alpine.data('propertyToggle', (slug) => ({
    slug,
    isFav: false, isCmp: false,
    popFav: false, popCmp: false,
    init() {
        this.refresh();
        this._teHandler = () => this.refresh();
        window.addEventListener('te-storage', this._teHandler);
    },
    destroy() {
        window.removeEventListener('te-storage', this._teHandler);
    },
    refresh() {
        try { this.isFav = JSON.parse(localStorage.getItem('te_favorites') || '[]').includes(this.slug); } catch(e) { this.isFav = false; }
        try { this.isCmp = JSON.parse(localStorage.getItem('te_compare') || '[]').includes(this.slug); } catch(e) { this.isCmp = false; }
    },
    _toggleStorageItem(storageKey, itemSlug) {
        let list = []; try { list = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch(e) {}
        const idx = list.indexOf(itemSlug);
        if (idx >= 0) list.splice(idx, 1); else list.push(itemSlug);
        localStorage.setItem(storageKey, JSON.stringify(list));
        window.dispatchEvent(new Event('te-storage'));
        return idx < 0; // true = added, false = removed
    },
    toggleFav(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        this.isFav = this._toggleStorageItem('te_favorites', this.slug);
        this.popFav = true; setTimeout(() => this.popFav = false, 300);
        if (this.isFav) {
            const prefix = window.location.pathname.slice(0, 3);
            const lang = ['/en', '/ru', '/hy'].includes(prefix) ? prefix : '';
            fetch(lang + '/property/' + this.slug, { priority: 'low' }).catch(() => {});
        }
    },
    toggleCmp(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        this.isCmp = this._toggleStorageItem('te_compare', this.slug);
        this.popCmp = true; setTimeout(() => this.popCmp = false, 300);
    },
}));

// Favorites / Compare page — loads rendered HTML from POST endpoint
Alpine.data('listLoader', (loadUrl, storageKey, options = {}) => {
    // ── fav-mode: DOM elements live OUTSIDE Alpine reactive scope ───────────
    // Storing DOM nodes inside Alpine reactive state causes Alpine to wrap them
    // in deep Proxy objects. Calling style.setProperty() on a proxied
    // CSSStyleDeclaration throws "TypeError: Illegal invocation" because the
    // method receives a Proxy as `this` instead of the real CSSStyleDeclaration.
    // Solution: keep DOM refs in a plain closure variable, never in `this.*`.
    let _favEls = [];          // raw DOM elements, index-aligned with this._cards
    let _matchingIndices = []; // last computed filter result, reused by goPage

    return {
    slugs: [],
    html: '',
    count: 0,
    loading: true,

    // ── fav-mode reactive state (metadata only, no DOM nodes) ───────────────
    query: '',
    _queryTimer: null,
    // [{title, address, code, visible}] — visible drives pagination+filter
    _cards: [],
    filteredCount: 0,
    page: 1,
    _perPage: 9,
    totalPages: 1,
    showDropdown: false,
    suggestions: [],
    recentSearches: [],

    init() {
        try { this.slugs = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch(e) { this.slugs = []; }
        if (options.removeEvent) {
            this._removeHandler = e => this.removeSlug(e.detail.slug);
            window.addEventListener(options.removeEvent, this._removeHandler);
        }
        if (options.favMode) {
            try {
                const raw = JSON.parse(localStorage.getItem('te_fav_searches') || '[]');
                // Migrate old format (plain strings) to objects
                this.recentSearches = raw.map(r => typeof r === 'string' ? { q: r, code: '' } : r);
            } catch(e) {}
            // Re-index cards whenever rendered HTML changes
            this.$watch('html', () => { this.$nextTick(() => this._indexCards()); });
            // Responsive per-page
            this._mq = window.matchMedia('(max-width: 767px)');
            this._onMqChange = () => {
                this._perPage = this._mq.matches
                    ? (options.perPageMobile || 6)
                    : (options.perPageDesktop || 9);
                this.page = 1;
                if (_favEls.length) this._applyPagination();
            };
            this._mq.addEventListener('change', this._onMqChange);
            this._onMqChange();
        }
        this.load();
    },
    destroy() {
        if (this._removeHandler) window.removeEventListener(options.removeEvent, this._removeHandler);
        if (options.favMode && this._mq) this._mq.removeEventListener('change', this._onMqChange);
        _favEls = []; _matchingIndices = [];
    },

    async _fetchSlugs(slugs) {
        const r = await fetch(loadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ slugs }),
        });
        return r.json();
    },
    _applyResult(j) {
        this.html = j.html || '';
        this.count = j.count || 0;
        // Avoid "no results" flash before _indexCards runs
        if (options.favMode) this.filteredCount = this.count;
        if (options.reExecScripts) {
            this.$nextTick(() => {
                const el = this.$el.querySelector('[x-html]');
                if (!el) return;
                el.querySelectorAll('script').forEach(old => {
                    const s = document.createElement('script');
                    s.textContent = old.textContent;
                    document.head.appendChild(s);
                    document.head.removeChild(s);
                });
            });
        }
    },
    async load() {
        this.loading = true;
        if (!this.slugs.length) { this.loading = false; this.html = ''; this.count = 0; this.filteredCount = 0; return; }
        try {
            const j = await this._fetchSlugs(this.slugs);
            this._applyResult(j);
        } catch(e) {}
        this.loading = false;
    },
    _uncache(slug) {
        const prefix = window.location.pathname.slice(0, 3);
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        fetch(prefix + '/property/uncache', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ slug }),
        }).catch(() => {});
    },
    removeSlug(slug) {
        this._uncache(slug);
        this.slugs = this.slugs.filter(s => s !== slug);
        localStorage.setItem(storageKey, JSON.stringify(this.slugs));
        window.dispatchEvent(new CustomEvent('te-storage'));
        if (!this.slugs.length) { this.html = ''; this.count = 0; this.filteredCount = 0; return; }
        this._fetchSlugs(this.slugs).then(j => this._applyResult(j)).catch(() => {});
    },
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

    // ── fav-mode: card indexing ─────────────────────────────────────────────
    _indexCards() {
        _favEls = Array.from(this.$el.querySelectorAll('[data-fav-card]'));
        this._cards = _favEls.map(el => ({
            title: el.dataset.title || '',
            address: el.dataset.address || '',
            code: el.dataset.code || '',
            visible: true, // true = passes filter AND is on current page
        }));
        if (this.query.trim()) {
            this._applyFilter();
        } else {
            this.filteredCount = this._cards.length;
            this.totalPages = Math.max(1, Math.ceil(this._cards.length / this._perPage));
            this._applyPagination();
        }
    },

    // ── fav-mode: fuzzy search ──────────────────────────────────────────────
    // Strips punctuation incl. Unicode, keeps all script letters + digits
    _norm(s) { return s.toLowerCase().replace(/[^\p{L}\p{N}]/gu, ''); },

    _fuzzyScore(text, query) {
        const t = this._norm(text);
        const q = this._norm(query);
        if (!q) return 1;
        let qi = 0, score = 0, lastMatch = -1;
        for (let ti = 0; ti < t.length && qi < q.length; ti++) {
            if (t[ti] === q[qi]) {
                score += 1 + (lastMatch === ti - 1 ? 2 : 0); // bonus for consecutive
                score += Math.max(0, 5 - ti);                 // bonus for early position
                lastMatch = ti;
                qi++;
            }
        }
        return qi < q.length ? 0 : score;
    },

    _escHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    _applyFilter() {
        const q = this.query.trim();
        const matchFlags = this._cards.map(c => {
            if (!q) return true;
            return Math.max(
                this._fuzzyScore(c.title, q),
                this._fuzzyScore(c.address, q),
                this._fuzzyScore(c.code, q),
            ) > 0;
        });
        _matchingIndices = matchFlags.reduce((acc, m, i) => { if (m) acc.push(i); return acc; }, []);
        this.filteredCount = _matchingIndices.length;
        this.totalPages = Math.max(1, Math.ceil(_matchingIndices.length / this._perPage));
        this.page = 1;
        this._applyPaginationWith(_matchingIndices);
        this._buildSuggestions(q);
    },

    _applyPagination() {
        _matchingIndices = this._cards.map((_, i) => i);
        this._applyPaginationWith(_matchingIndices);
    },

    _applyPaginationWith(matchingIndices) {
        const start = (this.page - 1) * this._perPage;
        const visibleSet = new Set(matchingIndices.slice(start, start + this._perPage));
        // Operate directly on raw DOM elements — never through Alpine Proxy
        _favEls.forEach((el, i) => {
            if (visibleSet.has(i)) {
                el.style.removeProperty('display');
            } else {
                el.style.setProperty('display', 'none', 'important');
            }
        });
    },

    goPage(p) {
        if (p < 1 || p > this.totalPages) return;
        this.page = p;
        this._applyPaginationWith(_matchingIndices);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    // ── fav-mode: search input events ───────────────────────────────────────
    onQueryInput() {
        clearTimeout(this._queryTimer);
        this._queryTimer = setTimeout(() => this._applyFilter(), 120);
    },

    commitSearch() {
        const q = this.query.trim();
        if (q) {
            // Try to grab code from top suggestion if it matches this query
            const topCode = this.suggestions.length > 0 ? this.suggestions[0].code : '';
            this._saveRecentSearch(q, topCode);
        }
        this.closeDropdown();
        this._applyFilter();
    },

    clearSearch() {
        this.query = '';
        this.closeDropdown();
        this._applyFilter();
    },

    // ── fav-mode: dropdown ──────────────────────────────────────────────────
    openDropdown() {
        if (!options.favMode) return;
        this.showDropdown = true;
        if (!this.query.trim()) this.suggestions = [];
    },

    closeDropdown() { this.showDropdown = false; },

    pickSuggestion(value, code) {
        this.query = value;
        this._saveRecentSearch(value, code);
        this.closeDropdown();
        this._applyFilter();
    },

    pickRecent(r) {
        this.query = r.q;
        this.closeDropdown();
        this._applyFilter();
    },

    _buildSuggestions(q) {
        if (!q) { this.suggestions = []; return; }
        this.suggestions = this._cards
            .map((c, i) => ({
                i,
                score: Math.max(
                    this._fuzzyScore(c.title, q),
                    this._fuzzyScore(c.code, q),
                ),
                title: c.title,
                code: c.code,
                address: c.address,
            }))
            .filter(x => x.score > 0)
            .sort((a, b) => b.score - a.score)
            .slice(0, 15)
            .map(x => {
                const titleScore = this._fuzzyScore(x.title, q);
                const codeScore  = this._fuzzyScore(x.code, q);
                // If code matched better (or title didn't match), use code as search term
                const raw = (codeScore > titleScore && codeScore > 0) ? x.code : (x.title || x.code);
                const shortTitle = x.title.length > 45 ? x.title.slice(0, 44) + '…' : x.title;
                return {
                    raw,
                    titleHtml: this._escHtml(shortTitle || x.code),
                    code: x.code,
                    address: x.address,
                };
            });
    },

    // ── fav-mode: recent searches ───────────────────────────────────────────
    _saveRecentSearch(q, code) {
        const entry = { q, code: code || '' };
        const deduped = [entry, ...this.recentSearches.filter(r => r.q.toLowerCase() !== q.toLowerCase())].slice(0, 3);
        this.recentSearches = deduped;
        localStorage.setItem('te_fav_searches', JSON.stringify(deduped));
    },
    };
});

// Async contact / enquiry form
Alpine.data('contactForm', (submitUrl, errorMsg) => ({
    sent: false,
    loading: false,
    error: '',
    async send(form) {
        this.loading = true; this.error = '';
        try {
            const r = await fetch(submitUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const j = await r.json();
            if (j.ok) { this.sent = true; form.reset(); }
            else { this.error = errorMsg; }
        } catch(e) { this.error = errorMsg; }
        this.loading = false;
    },
}));

// Location autocomplete via Yandex Suggest proxy
Alpine.data('locationInput', (initialValue, lang) => ({
    query: initialValue,
    results: [],
    open: false,
    loading: false,
    timer: null,
    lang,
    fetch() {
        clearTimeout(this.timer);
        if (this.query.length < 2) { this.results = []; this.open = false; return; }
        this.timer = setTimeout(() => {
            this.loading = true;
            window.fetch('/api/suggest?q=' + encodeURIComponent(this.query) + '&lang=' + this.lang)
                .then(r => r.json())
                .then(d => { this.results = d.results ?? []; this.open = this.results.length > 0; })
                .catch(() => {})
                .finally(() => this.loading = false);
        }, 280);
    },
    pick(name) { this.query = name; this.open = false; },
}));

// Property single — gallery lightbox + enquiry form
Alpine.data('propertyGallery', (images, enquireUrl, errorMsg) => ({
    lightbox: false,
    current: 0,
    fading: false,
    images,
    enquirySent: false,
    enquiryLoading: false,
    enquiryError: '',
    goto(idx) {
        if (idx === this.current) return;
        this.fading = true;
        setTimeout(() => { this.current = idx; this.fading = false; }, 180);
        setTimeout(() => {
            const el = this.$refs['thumb' + idx];
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }, 220);
    },
    async sendEnquiry(form) {
        this.enquiryLoading = true; this.enquiryError = '';
        try {
            const r = await fetch(enquireUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const j = await r.json();
            if (j.ok) { this.enquirySent = true; form.reset(); }
            else { this.enquiryError = errorMsg; }
        } catch(e) { this.enquiryError = errorMsg; }
        this.enquiryLoading = false;
    },
}));

Alpine.data('nearbyMap', (lat, lng, locale) => ({
    lat,
    lng,
    locale,
    activeCategory: 'transport',
    cache: {},
    retries: {},
    loading: false,
    activeItems: [],

    init() {
        if (this.lat && this.lng) {
            this._startLoading();
        } else if (window._propMapCoords) {
            this.lat = window._propMapCoords[0];
            this.lng = window._propMapCoords[1];
            this._startLoading();
        } else {
            window._onCoordsReady = (lat, lng) => {
                this.lat = lat;
                this.lng = lng;
                this._startLoading();
            };
        }
    },

    _startLoading() {
        this.loadCategory('transport').then(() => {
            ['education', 'food', 'fitness'].forEach(cat => this.loadCategory(cat));
        });
    },

    haversine(lat2, lon2) {
        const R = 6371000, toRad = x => x * Math.PI / 180;
        const dLat = toRad(lat2 - this.lat), dLon = toRad(lon2 - this.lng);
        const a = Math.sin(dLat/2)**2 + Math.cos(toRad(this.lat)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
        return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    },

    formatDist(m) {
        return m >= 1000 ? (m/1000).toFixed(1).replace(/\.0$/, '') + ' км' : m + ' м';
    },

    async fetchCategory(cat) {
        const r = await fetch('/api/nearby?lat='+this.lat+'&lon='+this.lng+'&category='+cat+'&lang='+this.locale);
        const d = await r.json();
        return (d.results || [])
            .map(p => ({ ...p, dist: this.haversine(p.lat, p.lon) }))
            .sort((a, b) => a.dist - b.dist)
            .slice(0, 7);
    },

    async loadCategory(cat) {
        if (this.cache[cat] !== undefined) {
            if (cat === this.activeCategory) {
                this.activeItems = this.cache[cat] || [];
                if (window.updateNearbyMarkers) window.updateNearbyMarkers(this.activeItems, cat);
            }
            return;
        }
        if (cat === this.activeCategory) { this.loading = true; this.activeItems = []; }
        try {
            const items = await this.fetchCategory(cat);
            if (items.length > 0) {
                this.cache[cat] = items;
                if (cat === this.activeCategory) {
                    this.activeItems = items;
                    if (window.updateNearbyMarkers) window.updateNearbyMarkers(items, cat);
                }
            } else {
                const attempt = (this.retries[cat] || 0) + 1;
                this.retries[cat] = attempt;
                if (attempt < 2) {
                    this.cache[cat] = null;
                    const tid = setTimeout(() => { delete this.cache[cat]; this.loadCategory(cat); }, 30000);
                    this._retryTimers = this._retryTimers || [];
                    this._retryTimers.push(tid);
                } else {
                    this.cache[cat] = [];
                }
                if (cat === this.activeCategory) this.activeItems = [];
            }
        } catch(e) {
            this.cache[cat] = [];
            if (cat === this.activeCategory) this.activeItems = [];
        } finally {
            if (cat === this.activeCategory) this.loading = false;
        }
    },

    async setCategory(cat) {
        this.activeCategory = cat;
        await this.loadCategory(cat);
    },
    destroy() {
        if (this._retryTimers) this._retryTimers.forEach(clearTimeout);
        window._onCoordsReady = null;
    },
}));

Alpine.store('fx', {
    rates: (window.__FX_RATES__ && window.__FX_RATES__.USD) ? window.__FX_RATES__ : { USD: 390, EUR: 420, RUB: 4.3, AMD: 1 },
    currency: localStorage.getItem('te_currency') || 'USD',

    setCurrency(cur) {
        this.currency = cur;
        localStorage.setItem('te_currency', cur);
    },

    convert(amount, from) {
        if (!amount) return null;
        const amd = amount * (this.rates[from] ?? 1);
        return amd / (this.rates[this.currency] ?? 1);
    },

    format(amount, from) {
        const converted = this.convert(amount, from);
        if (converted === null) return '';
        return this.formatValue(converted, this.currency);
    },

    formatValue(value, cur) {
        const symbols = { USD: '$', EUR: '€', RUB: '₽', AMD: '֏' };
        const sym = symbols[cur] ?? cur;
        if (cur === 'AMD' && value >= 1_000_000) {
            return sym + ' ' + (value / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
        }
        return sym + ' ' + new Intl.NumberFormat().format(Math.round(value));
    },

    conversions(amount, originalCurrency) {
        const all = ['USD', 'EUR', 'AMD', 'RUB'];
        return all
            .filter(c => c !== this.currency)
            .map(c => ({
                currency: c,
                label: this.formatValue(
                    amount * (this.rates[originalCurrency] ?? 1) / (this.rates[c] ?? 1),
                    c
                ),
            }));
    },
});

Alpine.store('contactModal', { open: false });

// ── Property-single prefetch ──────────────────────────────────────────────
// When property cards scroll into view, fire <link rel="prefetch"> for each
// property-single URL. This warms the Laravel server-side cache (te_prop:{slug})
// so the server responds in ~50ms when the user actually clicks.
// Turbo's built-in hover prefetch (100ms delay, enabled by default in Turbo 8)
// then delivers the already-cached response instantly on click.
(function setupPropertyPrefetch() {
    const prefetched = new Set();
    const MAX = 8; // max concurrent prefetches per page

    function observe(root) {
        const links = root.querySelectorAll('a[href*="/property/"]');
        if (!links.length) return;

        const observer = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting || prefetched.size >= MAX) continue;
                const href = entry.target.href;
                if (!href) continue;
                try {
                    const path = new URL(href).pathname;
                    // Only property-single pages: /locale/property/{slug} or /property/{slug}
                    if (!/\/property\/[^/?]+$/.test(path)) continue;
                } catch { continue; }
                if (prefetched.has(href)) continue;
                prefetched.add(href);
                const el = document.createElement('link');
                el.rel = 'prefetch';
                el.href = href;
                document.head.appendChild(el);
                observer.unobserve(entry.target);
            }
        }, { rootMargin: '300px 0px' });

        links.forEach(a => observer.observe(a));
    }

    document.addEventListener('DOMContentLoaded', () => observe(document));
    document.addEventListener('turbo:render', () => {
        prefetched.clear(); // new page — reset so fresh cards can be prefetched
        observe(document);
    });
}());

// ── Turbo Drive + Alpine.js integration ──────────────────────────
document.addEventListener('turbo:before-render', function () {
    // Reset contact modal so it doesn't reopen on next page
    Alpine.store('contactModal').open = false;

    Alpine.destroyTree(document.body);

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
