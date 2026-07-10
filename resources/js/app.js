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
    },
    toggleCmp(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        this.isCmp = this._toggleStorageItem('te_compare', this.slug);
        this.popCmp = true; setTimeout(() => this.popCmp = false, 300);
    },
}));

// Favorites / Compare page — loads rendered HTML from POST endpoint
Alpine.data('listLoader', (loadUrl, storageKey, options = {}) => ({
    slugs: [],
    html: '',
    count: 0,
    loading: true,
    init() {
        try { this.slugs = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch(e) { this.slugs = []; }
        if (options.removeEvent) {
            this._removeHandler = e => this.removeSlug(e.detail.slug);
            window.addEventListener(options.removeEvent, this._removeHandler);
        }
        this.load();
    },
    destroy() {
        if (this._removeHandler) window.removeEventListener(options.removeEvent, this._removeHandler);
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
        if (!this.slugs.length) { this.loading = false; this.html = ''; this.count = 0; return; }
        try {
            const j = await this._fetchSlugs(this.slugs);
            this._applyResult(j);
        } catch(e) {}
        this.loading = false;
    },
    removeSlug(slug) {
        this.slugs = this.slugs.filter(s => s !== slug);
        localStorage.setItem(storageKey, JSON.stringify(this.slugs));
        window.dispatchEvent(new CustomEvent('te-storage'));
        if (!this.slugs.length) { this.html = ''; this.count = 0; return; }
        this._fetchSlugs(this.slugs).then(j => this._applyResult(j)).catch(() => {});
    },
    clearAll() {
        localStorage.removeItem(storageKey);
        this.slugs = []; this.html = ''; this.count = 0;
    },
}));

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
