<?php $page = 'favorites'; ?>
@section('title')
    {{ __('header.favorites') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('header.favorites') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('header.favorites') }}
            @endslot
        @endcomponent

        <div class="content">
            <div class="container">

                <!-- Result counter -->
                <div class="advanced-filter mb-4 d-flex align-items-center justify-content-between">
                    <p class="filter-result mb-0">
                        {{ __('header.favorites') }}: <span class="result-value" id="result-loaded">0</span>
                        <span id="favorites-page-info" class="text-muted fw-normal ms-2" style="display:none">· {{ __('header.favorites_page') }} <span id="fav-page-cur">1</span> / <span id="fav-page-total">1</span></span>
                    </p>
                    <button type="button" id="favorites-clear" class="btn btn-sm btn-outline-danger" style="display:none">
                        <i class="material-icons-outlined" style="font-size:18px;vertical-align:middle">delete_sweep</i>
                        <span class="d-none d-md-inline ms-1">{{ __('header.favorites_clear') }}</span>
                    </button>
                </div>

                <!-- Search within saved favorites (title / address / code) -->
                <div id="favorites-search-wrap" class="favorites-search-wrap position-relative mb-4" style="display:none">
                    <i class="material-icons-outlined favorites-search-icon">search</i>
                    <input type="text" id="favorites-search" class="form-control favorites-search-input" placeholder="{{ __('header.favorites_search') }}" autocomplete="off">
                    <button type="button" id="favorites-search-clear" class="favorites-search-clear" style="display:none" aria-label="clear">
                        <i class="material-icons-outlined" style="font-size:18px">close</i>
                    </button>
                    <ul id="favorites-suggest" class="favorites-suggest" style="display:none"></ul>
                </div>

                <!-- No search results -->
                <div id="favorites-no-results" class="text-center py-5" style="display:none">
                    <i class="material-icons-outlined" style="font-size:64px;color:#ccc">search_off</i>
                    <h6 class="mt-3 text-muted">{{ __('header.favorites_no_results') }}</h6>
                </div>

                <!-- Empty state -->
                <div id="favorites-empty" class="text-center py-5" style="display:none">
                    <i class="material-icons-outlined" style="font-size:72px;color:#ccc">favorite_border</i>
                    <h5 class="mt-3 text-muted">{{ __('header.favorites_empty') }}</h5>
                    <a href="/{{ app()->getLocale() }}/property" class="btn btn-primary mt-3">
                        {{ __('property.title') }}
                    </a>
                </div>

                <!-- Load error state (favorites exist in storage but couldn't be fetched) -->
                <div id="favorites-error" class="text-center py-5" style="display:none">
                    <i class="material-icons-outlined" style="font-size:72px;color:#ccc">error_outline</i>
                    <h5 class="mt-3 text-muted">{{ __('header.favorites_error') }}</h5>
                    <button type="button" id="favorites-retry" class="btn btn-primary mt-3">
                        {{ __('header.favorites_retry') }}
                    </button>
                </div>

                <!-- Skeleton (shown while loading) -->
                <div class="row mb-4" id="prop-grid-skeleton">
                    @for($i = 0; $i < 6; $i++)
                    <div class="col-xl-4 col-lg-6 col-md-6 d-flex">
                        <div class="property-card flex-fill skeleton-card">
                            <div class="property-listing-item p-0 mb-0 shadow-none">
                                <div class="buy-grid-img mb-0 rounded-0" style="overflow:hidden">
                                    <span class="skeleton-block" style="width:100%;height:210px;border-radius:0"></span>
                                </div>
                                <div class="buy-grid-content">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="skeleton-block" style="width:80px;height:22px;border-radius:20px"></span>
                                        <span class="skeleton-block" style="width:55px;height:14px"></span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="skeleton-block mb-2" style="width:75%;height:20px"></span>
                                        <span class="skeleton-block" style="width:90%;height:14px"></span>
                                    </div>
                                    <span class="skeleton-block mb-3" style="width:100%;height:54px;border-radius:8px"></span>
                                    <div class="d-flex justify-content-between">
                                        <span class="skeleton-block" style="width:42%;height:13px"></span>
                                        <span class="skeleton-block" style="width:35%;height:13px"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- Property Grid (filled by AJAX) -->
                <div class="row mb-4" id="prop-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                </div>

                <!-- Pagination (client-side: 9 cards per page on desktop, 6 on mobile) -->
                <div id="favorites-pagination" class="d-flex flex-wrap justify-content-center align-items-center gap-1 mb-4" style="display:none"></div>

            </div>
        </div>

    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var STORAGE_KEY = 'te_favorites';
        var skeletonGrid = document.getElementById('prop-grid-skeleton');
        var grid         = document.getElementById('prop-grid');
        var emptyState   = document.getElementById('favorites-empty');
        var errorState   = document.getElementById('favorites-error');
        var counter      = document.getElementById('result-loaded');

        function getFavs() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
            catch (e) { return []; }
        }

        var clearBtn = document.getElementById('favorites-clear');

        // Retry simply reloads — re-runs the whole load flow (cache is warmer by then).
        var retryBtn = document.getElementById('favorites-retry');
        if (retryBtn) retryBtn.addEventListener('click', function () { window.location.reload(); });

        // Clear all favorites at once (mirrors the compare page's "clear all").
        if (clearBtn) clearBtn.addEventListener('click', function () {
            localStorage.removeItem(STORAGE_KEY);
            if (searchInput) searchInput.value = '';
            if (grid) grid.innerHTML = '';
            if (suggestEl) suggestEl.innerHTML = '';
            hideAllControls();
            if (emptyState) emptyState.style.display = 'block';
            if (counter) counter.textContent = '0';
            if (typeof window.updateHeaderFav === 'function') window.updateHeaderFav();
            if (typeof window.applyFavIcons === 'function') window.applyFavIcons();
        });

        // Hide the grid and every control (search / pagination / clear / dropdown / states).
        // Callers then reveal the single state they want (empty, error or no-results).
        function hideAllControls() {
            if (grid) grid.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';
            if (searchWrap) searchWrap.style.display = 'none';
            if (pagEl) { pagEl.innerHTML = ''; pagEl.style.display = 'none'; }
            if (pageInfo) pageInfo.style.display = 'none';
            if (suggestEl) suggestEl.style.display = 'none';
            if (noResultsEl) noResultsEl.style.display = 'none';
            if (errorState) errorState.style.display = 'none';
        }

        // Terminal state when nothing rendered: if favorites still exist in storage we just
        // couldn't load them (error + retry) — NOT the "no favorites" empty state.
        function showEmptyOrError() {
            hideAllControls();
            var el = getFavs().length > 0 ? errorState : emptyState;
            if (el) el.style.display = 'block';
        }

        // Sync localStorage without clobbering favorites added while the request was in
        // flight: keep every slug the server kept, plus any slug we did NOT send (added
        // meanwhile). Drop only slugs we sent that the server didn't return (genuine 404/
        // ghost). data.slugs includes transient `pending`, so those survive.
        function syncKeptFavs(sent, kept) {
            var keptSet = {};
            (kept || []).forEach(function (s) { keptSet[s] = true; });
            var next = getFavs().filter(function (s) {
                return keptSet[s] || sent.indexOf(s) === -1;
            });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
            if (typeof window.updateHeaderFav === 'function') window.updateHeaderFav();
        }

        // ── Client-side pagination: 9 cards per page on desktop, 6 on mobile ──
        var pagEl = document.getElementById('favorites-pagination');
        var pageInfo = document.getElementById('favorites-page-info');
        var pageCur = document.getElementById('fav-page-cur');
        var pageTotal = document.getElementById('fav-page-total');
        var currentPage = 1;

        // In-page search over the loaded favorites (title / address / code)
        var searchWrap  = document.getElementById('favorites-search-wrap');
        var searchInput = document.getElementById('favorites-search');
        var searchClear = document.getElementById('favorites-search-clear');
        var suggestEl   = document.getElementById('favorites-suggest');
        var noResultsEl = document.getElementById('favorites-no-results');

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
        }
        function currentQuery() {
            return ((searchInput && searchInput.value) || '').trim().toLowerCase();
        }
        function cardHaystack(c) {
            var code = c.getAttribute('data-code') || '';
            return ((c.getAttribute('data-title') || '') + ' ' +
                    (c.getAttribute('data-address') || '') + ' ' +
                    code + ' #' + code).toLowerCase();   // match code with or without the leading #
        }
        // Fuzzy subsequence score (fzf-style): query chars must appear IN ORDER in the text.
        // Rewards adjacency + early position, so the best match ranks highest. -1 = no match.
        function fuzzyScore(text, qn) {
            var ti = 0, score = 0, prev = -2, consec = 0;
            for (var i = 0; i < qn.length; i++) {
                var idx = text.indexOf(qn[i], ti);
                if (idx === -1) return -1;
                if (idx === prev + 1) { consec += 1; score += 6 + consec * 2; }  // contiguous run bonus
                else { consec = 0; score += 1; }
                if (idx === 0) score += 5;                                        // matches at start
                prev = idx;
                ti = idx + 1;
            }
            return score;
        }
        // Match against title + address + code (punctuation in the query is ignored). Returns a
        // score (higher = better) or -1 when the query can't be found as a subsequence.
        function matchScore(c, q) {
            // Keep letters of ANY script (Armenian/Cyrillic/Latin) + digits; drop only
            // punctuation/whitespace (#, -, spaces). Using [^a-z0-9] here silently erased
            // Armenian/Russian queries → empty → matched everything.
            var qn = (q || '').replace(/[^\p{L}\p{N}]/gu, '').toLowerCase();
            if (!qn) return 0;                     // empty query → neutral match (show all)
            return fuzzyScore(cardHaystack(c), qn);
        }
        function liveCards() {
            return Array.prototype.slice.call(grid.querySelectorAll(':scope > [data-slug]'))
                .filter(function (c) { return !c.classList.contains('d-none'); });
        }

        function favPageSize() {
            return window.matchMedia('(max-width: 767px)').matches ? 6 : 9;
        }

        function renderPageControls(totalPages) {
            if (!pagEl) return;
            if (totalPages <= 1) { pagEl.innerHTML = ''; pagEl.style.display = 'none'; return; }
            var html = '<button type="button" class="fav-pg-btn fav-pg-nav" data-pg="prev"' + (currentPage === 1 ? ' disabled' : '') + '><i class="material-icons-outlined">chevron_left</i></button>';
            for (var p = 1; p <= totalPages; p++) {
                html += '<button type="button" class="fav-pg-btn' + (p === currentPage ? ' active' : '') + '" data-pg="' + p + '">' + p + '</button>';
            }
            html += '<button type="button" class="fav-pg-btn fav-pg-nav" data-pg="next"' + (currentPage === totalPages ? ' disabled' : '') + '><i class="material-icons-outlined">chevron_right</i></button>';
            pagEl.innerHTML = html;
            pagEl.style.display = '';
        }

        // Single source of truth: filter by search query, paginate, counter, states.
        // Cards carry .d-flex (display:flex !important) → hide via setProperty(...,'important'),
        // show via removeProperty.
        function paginate() {
            if (!grid) return;
            var all = liveCards();
            var q = currentQuery();
            var matching = [];
            all.forEach(function (c) {
                if (matchScore(c, q) >= 0) matching.push(c);
                else c.style.setProperty('display', 'none', 'important');
            });
            var total = matching.length;
            if (counter) counter.textContent = total;

            // No favorites at all → empty state.
            if (all.length === 0) {
                hideAllControls();
                if (emptyState) emptyState.style.display = 'block';
                return;
            }
            if (searchWrap) searchWrap.style.display = '';
            if (clearBtn) clearBtn.style.display = '';
            if (emptyState) emptyState.style.display = 'none';

            // Favorites exist but the query matches none → "nothing found".
            if (total === 0) {
                grid.style.display = 'none';
                if (pagEl) { pagEl.innerHTML = ''; pagEl.style.display = 'none'; }
                if (pageInfo) pageInfo.style.display = 'none';
                if (noResultsEl) noResultsEl.style.display = 'block';
                return;
            }
            if (noResultsEl) noResultsEl.style.display = 'none';
            grid.style.display = '';

            var size = favPageSize();
            var totalPages = Math.ceil(total / size);
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;
            matching.forEach(function (c, i) {
                if (Math.floor(i / size) === currentPage - 1) c.style.removeProperty('display');
                else c.style.setProperty('display', 'none', 'important');
            });
            if (pageInfo) {
                if (totalPages > 1) {
                    if (pageCur) pageCur.textContent = currentPage;
                    if (pageTotal) pageTotal.textContent = totalPages;
                    pageInfo.style.display = '';
                } else {
                    pageInfo.style.display = 'none';
                }
            }
            renderPageControls(totalPages);
        }

        if (pagEl) pagEl.addEventListener('click', function (e) {
            var b = e.target.closest('.fav-pg-btn');
            if (!b || b.disabled) return;
            var v = b.getAttribute('data-pg');
            if (v === 'prev') currentPage--;
            else if (v === 'next') currentPage++;
            else currentPage = parseInt(v, 10) || 1;
            paginate();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // ── Recent searches (last 3, localStorage) + suggestions dropdown ──
        var RECENT_KEY = 'te_fav_searches';
        function getRecent() {
            try { return JSON.parse(localStorage.getItem(RECENT_KEY)) || []; } catch (e) { return []; }
        }
        function pushRecent(q) {
            q = (q || '').trim();
            if (!q) return;
            var list = getRecent().filter(function (x) { return x.toLowerCase() !== q.toLowerCase(); });
            list.unshift(q);
            localStorage.setItem(RECENT_KEY, JSON.stringify(list.slice(0, 3)));
        }

        function renderDropdown() {
            if (!suggestEl) return;
            var q = currentQuery();
            if (searchClear) searchClear.style.display = q ? '' : 'none';
            var recent = getRecent();
            var matches = [];
            if (q) {
                matches = liveCards().map(function (c) { return { c: c, s: matchScore(c, q) }; })
                    .filter(function (x) { return x.s >= 0; })
                    .sort(function (a, b) { return b.s - a.s; })   // best match on top, weakest at the bottom
                    .slice(0, 15)
                    .map(function (x) { return x.c; });
            }
            if (!recent.length && !matches.length) { suggestEl.style.display = 'none'; suggestEl.innerHTML = ''; return; }
            var html = '';
            recent.forEach(function (r, idx) {
                var div = (idx === recent.length - 1 && matches.length) ? ' fav-divider' : '';
                html += '<li class="favorites-suggest-item favorites-recent-item' + div + '" data-q="' + escapeHtml(r) + '">' +
                    '<span class="fs-suggest-title"><i class="material-icons-outlined fs-suggest-ic">history</i>' + escapeHtml(r) + '</span>' +
                    '</li>';
            });
            matches.forEach(function (c) {
                var t = c.getAttribute('data-title') || '';
                var code = c.getAttribute('data-code') || '';
                html += '<li class="favorites-suggest-item" data-title="' + escapeHtml(t) + '">' +
                    '<span class="fs-suggest-title">' + escapeHtml(t) + '</span>' +
                    (code ? '<span class="fs-suggest-code">#' + escapeHtml(code) + '</span>' : '') +
                    '</li>';
            });
            suggestEl.innerHTML = html;
            suggestEl.style.display = '';
        }

        if (searchInput) {
            var __searchTimer;
            searchInput.addEventListener('input', function () {
                if (searchClear) searchClear.style.display = currentQuery() ? '' : 'none';  // instant
                clearTimeout(__searchTimer);
                __searchTimer = setTimeout(function () {   // debounce heavy work (matches project's city-autocomplete)
                    currentPage = 1;
                    paginate();
                    renderDropdown();
                }, 120);
            });
            searchInput.addEventListener('focus', renderDropdown);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { pushRecent(currentQuery()); if (suggestEl) suggestEl.style.display = 'none'; }
                else if (e.key === 'Escape' && suggestEl) { suggestEl.style.display = 'none'; searchInput.blur(); }
            });
        }
        if (suggestEl) suggestEl.addEventListener('click', function (e) {
            var item = e.target.closest('.favorites-suggest-item');
            if (!item) return;
            var val = item.getAttribute('data-q') || item.getAttribute('data-title') || '';
            searchInput.value = val;
            pushRecent(val);
            suggestEl.style.display = 'none';
            if (searchClear) searchClear.style.display = val ? '' : 'none';
            currentPage = 1;
            paginate();
        });
        if (searchClear) searchClear.addEventListener('click', function () {
            searchInput.value = '';
            searchClear.style.display = 'none';
            if (suggestEl) { suggestEl.style.display = 'none'; suggestEl.innerHTML = ''; }
            currentPage = 1;
            paginate();
            searchInput.focus();
        });
        // Close the dropdown on an outside click.
        document.addEventListener('click', function (e) {
            if (suggestEl && searchWrap && !searchWrap.contains(e.target)) {
                suggestEl.style.display = 'none';
            }
        });

        var __favPgTimer;
        window.addEventListener('resize', function () {
            clearTimeout(__favPgTimer);
            __favPgTimer = setTimeout(paginate, 150);   // re-slice when crossing the mobile breakpoint
        });

        var favs = getFavs();

        if (favs.length === 0) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        // Un-favoriting on this page: fade the card out and hide it. Attached once (outside
        // the load callbacks) so the transient-retry re-load can't stack duplicate handlers.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.favourite');
            if (!btn) return;
            setTimeout(function () {
                var card = btn.closest('[data-slug]');
                if (!card || !grid) return;
                if (getFavs().indexOf(card.dataset.slug) === -1) {
                    card.style.transition = 'opacity 0.3s';
                    card.style.opacity = '0';
                    setTimeout(function () {
                        card.classList.add('d-none');
                        card.style.opacity = '';
                        card.style.transition = '';
                        paginate();   // recount + re-fill the current page after removal
                    }, 300);
                }
            }, 50);
        });

        function requestFavs(slugList) {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            return fetch('/{{ app()->getLocale() }}/favorites/load', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                },
                body: JSON.stringify({ slugs: slugList })
            }).then(function (res) { return res.json(); });
        }

        function appendCards(html) {
            if (!html || !grid) return;
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            while (tmp.firstElementChild) grid.appendChild(tmp.firstElementChild);
        }

        // Reveal freshly-appended cards. paginate() is the single source of truth for the
        // counter / grid visibility / clear button / empty state / pages.
        function showCards() {
            if (grid) requestAnimationFrame(function () { grid.style.opacity = '1'; });
            if (typeof window.applyFavIcons === 'function') window.applyFavIcons();
            // Cards also carry a compare button — reflect its pressed state for slugs already
            // in te_compare (these cards are inserted after DOMContentLoaded, so the initial
            // applyCompareIcons() pass never saw them).
            if (typeof window.applyCompareIcons === 'function') window.applyCompareIcons();
            paginate();   // slice the freshly-rendered cards into pages
        }

        // Initial load
        requestFavs(favs).then(function (data) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';

            // Sync localStorage: drop only slugs the server didn't keep; preserve anything
            // added during the request and the transient `pending` ones (still in data.slugs).
            if (data.slugs !== undefined) {
                syncKeptFavs(favs, data.slugs);
            }

            var pending = data.pending || [];

            if (data.count === 0 && pending.length === 0) {
                showEmptyOrError();
                return;
            }

            appendCards(data.html);
            showCards();

            // Some slugs failed transiently (rate limit / 5xx / network) and were kept in
            // favorites but NOT rendered. Re-request just those once, after a short pause, so
            // the cards appear instead of silently going missing while still counted.
            if (pending.length) {
                setTimeout(function () {
                    requestFavs(pending).then(function (retry) {
                        if (retry && retry.count) {
                            appendCards(retry.html);
                            showCards();
                        }
                    }).catch(function () {}).then(function () {
                        if (liveCards().length === 0) showEmptyOrError();
                    });
                }, 800);
            }
        }).catch(function () {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            showEmptyOrError();
        });
    });
</script>

@endsection
