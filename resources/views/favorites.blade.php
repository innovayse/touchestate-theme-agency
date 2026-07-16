@extends('layout.app')
@section('title', __('header.favorites') . ' — ' . ($workspace['name'] ?? ''))

@php $locale = app()->getLocale(); @endphp

@section('content')
<x-breadcrumb :title="__('header.favorites')" />

<section class="py-16"
    x-data="listLoader('{{ url('/'.$locale.'/favorites/load') }}', 'te_favorites', { favMode: true, perPageDesktop: 9, perPageMobile: 6 })">

    <div class="container-x">

        {{-- Top bar --}}
        <div class="mb-6 flex flex-col gap-2">

            {{-- Row 1: count + page indicator + clear all --}}
            <div x-show="!loading && count > 0" class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <p class="text-sm text-neutral-500">
                        <span x-text="query.trim() ? filteredCount : count"></span> {{ __('property.properties') }}
                    </p>
                    <span x-show="totalPages > 1"
                          class="text-xs text-neutral-400"
                          x-text="'{{ __('favorites.page_indicator') }}'.replace('{n}', page).replace('{m}', totalPages)"></span>
                </div>
                <button x-show="slugs.length > 0" @click="clearAll()"
                        class="shrink-0 rounded-xl border border-red-200 px-3 py-2 text-sm text-red-500 transition hover:border-red-300 hover:bg-red-50">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                        </svg>
                        <span class="hidden sm:inline">{{ __('favorites.clear_all') }}</span>
                    </span>
                </button>
            </div>

            {{-- Row 2: search (full width) --}}
            <div class="flex items-center gap-2">

                {{-- Search with dropdown --}}
                <div x-show="!loading && count > 0" class="relative min-w-0 flex-1" @click.outside="closeDropdown()">
                    <div class="flex w-full items-center overflow-hidden rounded-xl border border-sand bg-panel pl-3 pr-1 transition focus-within:border-brand-400">
                        <svg class="mr-2 h-4 w-4 shrink-0 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input type="text"
                               x-model="query"
                               @input="onQueryInput()"
                               @focus="openDropdown()"
                               @keydown.escape.prevent="closeDropdown()"
                               @keydown.enter.prevent="commitSearch()"
                               placeholder="{{ __('favorites.search_placeholder') }}"
                               class="min-w-0 flex-1 bg-transparent py-2 text-sm text-ink outline-none placeholder:text-neutral-400 sm:w-72">
                        <button x-show="query.length > 0"
                                @click="clearSearch()"
                                class="ml-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-neutral-400 hover:text-neutral-700">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Dropdown --}}
                    <div x-show="showDropdown && (recentSearches.length > 0 || suggestions.length > 0)"
                         class="absolute left-0 z-50 mt-1.5 w-full overflow-hidden rounded-xl border border-sand bg-panel shadow-xl"
                         style="display:none">

                        {{-- Recent searches (always shown) --}}
                        <template x-if="recentSearches.length > 0">
                            <div>
                                <p class="px-3 pb-1 pt-2.5 text-xs font-semibold uppercase tracking-wide text-neutral-400">{{ __('favorites.recent_searches') }}</p>
                                <template x-for="(r, idx) in recentSearches" :key="idx">
                                    <button type="button"
                                            @click="pickRecent(r)"
                                            class="flex w-full items-baseline justify-between gap-2 px-3 py-2 text-left hover:bg-sand">
                                        <span class="truncate text-sm font-medium text-ink" x-text="r.q"></span>
                                        <span class="shrink-0 text-xs text-neutral-400" x-show="r.code" x-text="r.code"></span>
                                    </button>
                                </template>
                                <div class="border-t border-sand"></div>
                            </div>
                        </template>

                        {{-- Live suggestions --}}
                        <template x-if="suggestions.length > 0">
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="(s, idx) in suggestions" :key="idx">
                                    <button type="button"
                                            @click="pickSuggestion(s.raw, s.code)"
                                            class="flex w-full items-baseline justify-between gap-2 px-3 py-2 text-left hover:bg-sand">
                                        <span class="truncate text-sm font-medium text-ink" x-html="s.titleHtml"></span>
                                        <span class="shrink-0 text-xs text-neutral-400" x-text="s.code"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- Dropdown empty state (query typed but nothing found) --}}
                        <template x-if="query.trim().length > 0 && suggestions.length === 0">
                            <p class="px-3 py-3 text-sm text-neutral-400">{{ __('favorites.no_results') }}</p>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        {{-- Loading skeleton --}}
        <div x-show="loading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @for($i=0;$i<3;$i++)
                <div class="animate-pulse rounded-2xl border border-sand bg-panel">
                    <div class="h-56 rounded-t-2xl bg-sand"></div>
                    <div class="space-y-3 p-5">
                        <div class="h-4 w-3/4 rounded bg-sand"></div>
                        <div class="h-3 w-1/2 rounded bg-sand"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Results grid (cards are shown/hidden by JS for pagination & search) --}}
        <div x-show="!loading && count > 0" x-html="html"></div>

        {{-- No search results (cards exist but none match current query) --}}
        <div x-show="!loading && _cards.length > 0 && filteredCount === 0 && query.trim().length > 0"
             class="rounded-3xl border border-dashed border-sand bg-panel py-16 text-center">
            <svg class="mx-auto mb-4 text-neutral-300" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6M11 8v6"/>
            </svg>
            <p class="text-neutral-500">{{ __('favorites.no_results') }}</p>
        </div>

        {{-- Pagination --}}
        <nav x-show="!loading && totalPages > 1"
             class="mt-8 flex items-center justify-center gap-1.5"
             aria-label="Pagination">
            <button @click="goPage(page - 1)"
                    :disabled="page === 1"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-sand text-sm text-ink hover:bg-sand disabled:pointer-events-none disabled:opacity-40">‹</button>
            <template x-for="p in totalPages" :key="p">
                <button @click="goPage(p)"
                        :class="p === page ? 'bg-brand-600 text-white border-brand-600' : 'border-sand text-ink hover:bg-sand'"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm transition">
                    <span x-text="p"></span>
                </button>
            </template>
            <button @click="goPage(page + 1)"
                    :disabled="page === totalPages"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-sand text-sm text-ink hover:bg-sand disabled:pointer-events-none disabled:opacity-40">›</button>
        </nav>

        {{-- Empty state (no favorites at all) --}}
        <div x-show="!loading && count === 0"
             class="rounded-3xl border border-dashed border-sand bg-panel py-24 text-center">
            <svg class="mx-auto mb-4 text-brand-200" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M12 21s-7-4.35-9.5-8.5C.9 9.5 2.2 6 5.5 6 7.5 6 9 7.2 12 10c3-2.8 4.5-4 6.5-4 3.3 0 4.6 3.5 3 6.5C19 16.65 12 21 12 21z"/>
            </svg>
            <p class="font-display text-xl text-neutral-500">{{ __('header.favorites_empty') }}</p>
            <p class="mt-1 text-sm text-neutral-400">{{ __('favorites.save_hint') }}</p>
            <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand mt-6">{{ __('index.explore_all_listings') }}</a>
        </div>

    </div>
</section>
@endsection
