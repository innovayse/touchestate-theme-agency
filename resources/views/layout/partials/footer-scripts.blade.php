    <!-- jQuery -->
    <script src="{{URL::asset('build/js/jquery-3.7.1.min.js')}}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{URL::asset('build/js/bootstrap.bundle.min.js')}}"></script>

@if (Route::is(['add-property-buy', 'add-property-rent', 'checkout', 'rent-details', 'rental-booking', 'rental-payment']))
	<!-- Datetimepicker JS -->
    <script src="{{URL::asset('build/js/moment.min.js')}}"></script>
    <script src="{{URL::asset('build/js/bootstrap-datetimepicker.min.js')}}"></script>
@endif

	<!-- Simplebar JS -->
    <script src="{{URL::asset('build/plugins/simplebar/simplebar.min.js')}}"></script>

@if (Route::is(['checkout', 'contact-us', 'rental-order-confirmation', 'rental-order-details']) || request()->is('contact-us') || request()->is('*/contact-us'))
    <!-- intel Input -->
    <script src="{{URL::asset('build/plugins/intltelinput/js/intlTelInput.js')}}"></script>
@endif

@if (!Route::is(['about-us']))
	<!-- Select2 JS -->
	<script src="{{URL::asset('build/plugins/select2/js/select2.min.js')}}"></script>
@endif

@if (Route::is(['rent-details', 'property.single']) || request()->is('property/*') || request()->is('*/property/*'))
    <!-- Fancybox v5 JS -->
    <script src="{{URL::asset('build/plugins/fancybox/fancybox.umd.js')}}"></script>
@endif

@if (Route::is(['index', 'rent-details', 'property.single']) || request()->is('/') || request()->is('property/*') || request()->is('*/property/*'))
	<!-- Slick Slider -->
	<script src="{{URL::asset('build/plugins/slick/slick.js')}}"></script>
@endif

@if (Route::is(['agent-details', 'index-2', 'index-3']))
	<!-- Swiper JS -->
	<script src="{{URL::asset('build/plugins/swiper/swiper-bundle.min.js')}}"></script>
@endif

@if (Route::is(['property-sidebar', 'buy-property-list-sidebar', 'rent-property-grid-sidebar', 'rent-property-list-sidebar']))
    <!-- Rangeslider JS -->
	<script src="{{URL::asset('build/plugins/ion-rangeslider/js/ion.rangeSlider.js')}}"></script>
	<script src="{{URL::asset('build/plugins/ion-rangeslider/js/custom-rangeslider.js')}}"></script>
	<script src="{{URL::asset('build/plugins/ion-rangeslider/js/ion.rangeSlider.min.js')}}"></script>
@endif

@if (Route::is(['gallery', 'privacy-policy', 'terms-condition']))
    <!-- Lightbox JS -->
    <script src="{{URL::asset('build/plugins/lightbox/glightbox.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/lightbox/lightbox.js')}}"></script>
@endif

<!-- Counter JS -->
<script src="{{URL::asset('build/js/waypoints.js')}}"></script>
<script src="{{URL::asset('build/js/jquery.counterup.min.js')}}"></script>

<!-- Aos JS -->
<script src="{{URL::asset('build/js/aos.js')}}"></script>

@if (Route::is(['map', 'rent-details', 'property.single', 'contact-us']) || request()->is('map') || request()->is('*/map') || request()->is('property/*') || request()->is('*/property/*') || request()->is('contact-us') || request()->is('*/contact-us'))
    @php
        $ymapsLang = match(app()->getLocale()) {
            'ru'    => 'ru_RU',
            'tr'    => 'tr_TR',
            'uk'    => 'uk_UA',
            default => 'en_US',
        };
    @endphp
    <!-- Yandex Map JS -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('services.yandex.maps_key') }}&lang={{ $ymapsLang }}" type="text/javascript"></script>
    <script>
    // Laptop trackpad pinch fires ctrl+wheel, which the browser treats as PAGE zoom and
    // Yandex does not intercept. Forward it to the map so pinch zooms the map, not the page.
    window.enableMapPinchZoom = function (map, el) {
        if (!map || !el || el.__pinchZoom) return;
        el.__pinchZoom = true;
        var last = 0;
        el.addEventListener('wheel', function (e) {
            if (!e.ctrlKey) return;                 // only pinch; plain scroll stays page scroll
            e.preventDefault();
            var now = e.timeStamp || 0;
            if (now - last < 90) return;            // throttle: one pinch step = one zoom level
            last = now;
            var dir = e.deltaY < 0 ? 1 : -1;        // spread = zoom in, pinch together = zoom out
            map.setZoom(map.getZoom() + dir, { duration: 100, checkZoomRange: true });
        }, { passive: false });
    };
    </script>
@endif
@if (Route::is(['map']) || request()->is('map') || request()->is('*/map'))
    <script src="{{URL::asset('build/js/map-grid.js')}}"></script>
@endif

@if (Route::is(['agent-details', 'agent.single', 'agent-grid-sidebar', 'agent-list-sidebar', 'property-sidebar', 'buy-property-list-sidebar', 'cart', 'checkout', 'rent-details', 'rent-property-grid-sidebar', 'rent-property-list-sidebar', 'property.single', 'map']) || request()->is('property/*') || request()->is('*/property/*') || request()->is('map') || request()->is('*/map') || request()->is('agent/*') || request()->is('*/agent/*'))
	<!-- Sticky Sidebar JS -->
	<script src="{{URL::asset('build/plugins/theia-sticky-sidebar/ResizeSensor.js')}}"></script>
	<script src="{{URL::asset('build/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js')}}"></script>
@endif

@if (Route::is(['coming-soon']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/coming-soon.js')}}"></script>
@endif

    <!-- Main JS -->
    <script src="{{URL::asset('build/js/script.js')}}?v={{ filemtime(public_path('build/js/script.js')) }}"></script>

    <!-- instant.page — prefetch internal links on hover/mousedown → near-instant navigation -->
    <script src="{{URL::asset('build/js/instantpage.js')}}?v={{ filemtime(public_path('build/js/instantpage.js')) }}" type="module"></script>

    <!-- Property Share -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shareBtn = document.getElementById('sharePropertyBtn');
            const __shareMsgs = {
                copied: @json(__('property-single.link_copied')),
                copyFail: @json(__('property-single.copy_failed')),
                copyError: @json(__('property-single.copy_error')),
                shareText: @json(__('property-single.share_text')),
            };

            if (shareBtn) {
                shareBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Share button clicked');

                    const url = window.location.href;
                    const shareData = {
                        title: document.title,
                        text: __shareMsgs.shareText,
                        url: url
                    };

                    // Try native share first (mobile)
                    if (navigator.share) {
                        console.log('Using Web Share API');
                        navigator.share(shareData)
                            .then(() => console.log('Share successful'))
                            .catch((err) => {
                                if (err.name !== 'AbortError') {
                                    console.log('Share failed:', err);
                                    fallbackCopy(url);
                                }
                            });
                        return;
                    }

                    // Desktop: just copy to clipboard
                    console.log('Using fallback copy');
                    fallbackCopy(url);
                });
            }

            function fallbackCopy(url) {
                // Try modern clipboard API
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url)
                        .then(() => {
                            console.log('Copied via Clipboard API');
                            showToast(__shareMsgs.copied);
                        })
                        .catch(err => {
                            console.log('Clipboard API failed:', err);
                            oldSchoolCopy(url);
                        });
                } else {
                    oldSchoolCopy(url);
                }
            }

            function oldSchoolCopy(url) {
                console.log('Using execCommand copy');
                const tempInput = document.createElement('textarea');
                tempInput.value = url;
                tempInput.style.position = 'fixed';
                tempInput.style.left = '-9999px';
                tempInput.style.top = '0';
                document.body.appendChild(tempInput);
                tempInput.focus();
                tempInput.select();

                try {
                    const successful = document.execCommand('copy');
                    console.log('execCommand result:', successful);
                    if (successful) {
                        showToast(__shareMsgs.copied);
                    } else {
                        showToast(__shareMsgs.copyFail);
                    }
                } catch (err) {
                    console.error('execCommand failed:', err);
                    showToast(__shareMsgs.copyError);
                }

                document.body.removeChild(tempInput);
            }

            function showToast(message) {
                console.log('Showing toast:', message);
                const toast = document.createElement('div');
                toast.textContent = message;
                toast.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#323232;color:white;padding:12px 24px;border-radius:25px;z-index:10000;font-size:14px;box-shadow:0 3px 6px rgba(0,0,0,0.3);';
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.3s';
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            document.body.removeChild(toast);
                        }
                    }, 300);
                }, 2000);
            }
        });
    </script>

    <!-- Currency: rates + config for client-side conversion -->
    <script>
        window.__CURRENCY__ = {
            rates:     @json(app(\App\Services\ExchangeRateService::class)->rates()),
            symbols:   @json(config('currency.symbols')),
            supported: @json(array_values(config('currency.supported'))),
            current:   @json(display_currency()),
            default:   @json(config('currency.default')),
        };
    </script>

    <!-- Currency (client-side conversion, no page reload) -->
    <script>
    (function () {
        var CFG = window.__CURRENCY__ || { rates: { AMD: 1 }, symbols: {}, supported: ['AMD'], current: 'AMD', default: 'AMD' };
        var KEY       = 'te_currency';
        var rates     = CFG.rates || { AMD: 1 };
        var symbols   = CFG.symbols || {};
        var supported = CFG.supported || ['AMD'];

        function isSupported(c) { return supported.indexOf(c) !== -1; }

        function stored() {
            try { var v = localStorage.getItem(KEY); return isSupported(v) ? v : null; }
            catch (e) { return null; }
        }

        // Active display currency: localStorage wins, else the server-rendered value.
        var current = stored() || (isSupported(CFG.current) ? CFG.current : CFG.default);

        function convert(amount, from, to) {
            amount = parseFloat(amount) || 0;
            if (from === to) return amount;
            var rf = rates[from], rt = rates[to];
            if (!rf || !rt) return null;                       // rates unavailable
            return (amount * rf) / rt;
        }

        function fmtNumber(n) {
            return Math.round(n).toLocaleString('en-US');      // matches PHP number_format(n, 0)
        }

        function format(amount, from) {
            from = isSupported(from) ? from : current;
            var v = convert(amount, from, current);
            if (v === null) return fmtNumber(amount) + ' ' + from;   // fallback: original
            return fmtNumber(v) + ' ' + current;
        }

        function applyPrices() {
            document.querySelectorAll('.js-price[data-amount][data-currency]').forEach(function (el) {
                el.textContent = format(el.dataset.amount, el.dataset.currency);
            });
        }

        // property-single: rebuild the "all other currencies" list under the headline price.
        function applyAltBlocks() {
            document.querySelectorAll('.js-price-alt').forEach(function (box) {
                var amount = box.dataset.amount, from = box.dataset.currency, parts = [];
                supported.forEach(function (c) {
                    if (c === current) return;                 // skip the one shown as main price
                    var v = convert(amount, from, c);
                    if (v !== null) parts.push('<span class="price-alt-currency">' + fmtNumber(v) + ' ' + c + '</span>');
                });
                if (parts.length) { box.innerHTML = parts.join('<span class="price-alt-sep">·</span>'); box.style.display = ''; }
                else { box.style.display = 'none'; }
            });
        }

        function applySymbols() {
            var sym = symbols[current] || current;
            document.querySelectorAll('[data-currency-symbol]').forEach(function (el) { el.setAttribute('placeholder', sym); });
            document.querySelectorAll('.js-currency-symbol').forEach(function (el) { el.textContent = sym; });
            document.querySelectorAll('.js-currency-code').forEach(function (el) { el.textContent = current; });
        }

        function applyHeader() {
            var sym = symbols[current] || current;
            document.querySelectorAll('.topbar-currency .currency-symbol-trigger').forEach(function (el) { el.textContent = sym; });
            document.querySelectorAll('.currency-switch').forEach(function (a) {
                a.classList.toggle('active', a.dataset.currency === current);
            });
        }

        function apply() { applyPrices(); applyAltBlocks(); applySymbols(); applyHeader(); }

        // Fire-and-forget: keep the server session in sync for the next full page load.
        function persist(c) {
            try { fetch('/currency/' + c, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }); } catch (e) {}
        }

        function setCurrency(c) {
            if (!isSupported(c) || c === current) return;
            current = c;
            try { localStorage.setItem(KEY, c); } catch (e) {}
            apply();
            persist(c);
        }

        // Expose for map balloons (map-grid.js) and any external caller.
        window.CurrencyManager = {
            format: format,
            apply: apply,
            set: setCurrency,
            get current() { return current; }
        };

        // Click any currency switch link → convert in place, no reload.
        document.addEventListener('click', function (e) {
            var a = e.target.closest('.currency-switch');
            if (!a) return;
            e.preventDefault();
            setCurrency(a.dataset.currency);
        });

        document.addEventListener('DOMContentLoaded', function () {
            apply();
            if (current !== CFG.current) persist(current);     // session lagged behind localStorage → sync
        });
    }());
    </script>

    <!-- Compare (localStorage) -->
    <script>
    (function () {
        var COMPARE_KEY = 'te_compare';
        var MAX_COMPARE = 4;
        var __maxMsg   = @json(__('compare.max_reached', ['max' => 4]));

        function getSlugs() {
            try { return JSON.parse(localStorage.getItem(COMPARE_KEY)) || []; }
            catch (e) { return []; }
        }

        function setSlugs(slugs) {
            localStorage.setItem(COMPARE_KEY, JSON.stringify(slugs));
        }

        function getSlug(btn) {
            if (btn.dataset && btn.dataset.slug) return btn.dataset.slug;
            var card = btn.closest('[data-slug]');
            return card ? card.dataset.slug : null;
        }

        function applyCompareIcon(btn, isIn) {
            btn.classList.toggle('is-compared', isIn);
            btn.setAttribute('aria-pressed', isIn ? 'true' : 'false');
        }

        function applyCompareIcons() {
            var slugs = getSlugs();
            document.querySelectorAll('.compare-btn').forEach(function (btn) {
                var slug = getSlug(btn);
                if (slug) applyCompareIcon(btn, slugs.indexOf(slug) !== -1);
            });
            // Lock buttons when limit reached
            var atMax = slugs.length >= MAX_COMPARE;
            document.querySelectorAll('.compare-btn').forEach(function (btn) {
                var slug = getSlug(btn);
                var isIn = slug && slugs.indexOf(slug) !== -1;
                btn.disabled = atMax && !isIn;
                btn.title    = btn.disabled ? __maxMsg : '';
            });
        }

        function updateHeaderCompare() {
            var count = getSlugs().length;
            document.querySelectorAll('.header-compare-btn').forEach(function (btn) {
                var badge = btn.querySelector('.compare-badge');
                var svg   = btn.querySelector('svg.i-icon');
                if (badge) {
                    if (count > 0) {
                        badge.textContent  = count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                if (svg) {
                    svg.style.color = count > 0 ? '#1565c0' : '';
                }
            });
        }

        // Click handler — event delegation
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.compare-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();

            var slug = getSlug(btn);
            if (!slug) return;

            var slugs = getSlugs();
            var idx   = slugs.indexOf(slug);

            if (idx !== -1) {
                slugs.splice(idx, 1);
            } else {
                if (slugs.length >= MAX_COMPARE) {
                    showCompareToast(__maxMsg);
                    return;
                }
                slugs.push(slug);
            }

            setSlugs(slugs);
            updateHeaderCompare();
            applyCompareIcons();
        });

        function showCompareToast(msg) {
            var toast = document.createElement('div');
            toast.textContent = msg;
            toast.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#323232;color:#fff;padding:10px 22px;border-radius:25px;z-index:10000;font-size:14px;box-shadow:0 3px 8px rgba(0,0,0,.3);';
            document.body.appendChild(toast);
            setTimeout(function () {
                toast.style.transition = 'opacity 0.3s';
                toast.style.opacity = '0';
                setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
            }, 2000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            applyCompareIcons();
            updateHeaderCompare();
        });

        window.applyCompareIcons    = applyCompareIcons;
        window.updateHeaderCompare  = updateHeaderCompare;
    }());
    </script>

    <!-- Favourites (localStorage) -->
    <script>
    (function () {
        var STORAGE_KEY = 'te_favorites';

        function getFavs() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
            catch (e) { return []; }
        }

        function setFavs(favs) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(favs));
        }

        var spriteBase = "{{ asset('img/icons.svg') }}";

        function getSlug(btn) {
            if (btn.dataset && btn.dataset.slug) return btn.dataset.slug;
            var card = btn.closest('[data-slug]');
            return card ? card.dataset.slug : null;
        }

        function applyFavIcon(btn, isFav) {
            // SVG-icon variant (x-icon component) — swap use href to filled/border sprite id
            var svg = btn.querySelector('svg.i-icon');
            var use = svg ? svg.querySelector('use') : null;
            if (use) {
                use.setAttribute('href', spriteBase + (isFav ? '#icon-favorite_filled' : '#icon-favorite_border'));
            }
            // Material Icons ligature variant (<i>) — kept as fallback for legacy markup
            var i = btn.querySelector('i');
            if (i) {
                i.textContent = isFav ? 'favorite' : 'favorite_border';
                i.classList.toggle('filled', isFav);
            }
            btn.classList.toggle('is-favorited', isFav);
            btn.setAttribute('aria-pressed', isFav ? 'true' : 'false');
        }

        // Mark all visible favourite buttons based on localStorage
        function applyFavIcons() {
            var favs = getFavs();
            document.querySelectorAll('.favourite').forEach(function (btn) {
                var slug = getSlug(btn);
                if (slug) applyFavIcon(btn, favs.indexOf(slug) !== -1);
            });
        }

        // Update header heart icon badge and color
        function updateHeaderFav() {
            var favs = getFavs();
            var count = favs.length;
            document.querySelectorAll('.header-fav-btn').forEach(function (btn) {
                var svg  = btn.querySelector('svg.i-icon');
                var use  = svg ? svg.querySelector('use') : null;
                var badge = btn.querySelector('.fav-badge');
                if (use) {
                    if (count > 0) {
                        use.setAttribute('href', spriteBase + '#icon-favorite_filled');
                        svg.style.color = '#e53935';
                    } else {
                        use.setAttribute('href', spriteBase + '#icon-favorite_border');
                        svg.style.color = '';
                    }
                }
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
        }

        // Event delegation — works for dynamically loaded cards (Load More)
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.favourite');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var slug = getSlug(btn);
            if (!slug) return;
            var favs = getFavs();
            var idx = favs.indexOf(slug);
            if (idx === -1) {
                favs.push(slug);
                applyFavIcon(btn, true);
            } else {
                favs.splice(idx, 1);
                applyFavIcon(btn, false);
            }
            setFavs(favs);
            updateHeaderFav();
        });

        document.addEventListener('DOMContentLoaded', function () {
            applyFavIcons();
            updateHeaderFav();
        });

        // Expose so Load More handlers can re-apply after inserting new cards
        window.applyFavIcons = applyFavIcons;
        window.updateHeaderFav = updateHeaderFav;
    }());
    </script>
