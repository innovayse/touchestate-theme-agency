@extends('layout.app')
@section('title', __('header.favorites') . ' — ' . ($workspace['name'] ?? ''))

@php $locale = app()->getLocale(); @endphp

@section('content')
<x-breadcrumb :title="__('header.favorites')" />

<section class="py-16"
    x-data="listLoader('{{ url('/'.$locale.'/favorites/load') }}', 'te_favorites')">

    <div class="container-x">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-neutral-500">
                <span x-text="count"></span> {{ __('property.properties') }}
            </p>
            <button x-show="slugs.length > 0" @click="clearAll()"
                    class="text-sm text-red-500 hover:underline">{{ __('favorites.clear_all') }}</button>
        </div>

        {{-- Loading --}}
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

        {{-- Results --}}
        <div x-show="!loading && count > 0" x-html="html"></div>

        {{-- Empty --}}
        <div x-show="!loading && count === 0"
             class="rounded-3xl border border-dashed border-sand bg-panel py-24 text-center">
            <svg class="mx-auto mb-4 text-brand-200" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 21s-7-4.35-9.5-8.5C.9 9.5 2.2 6 5.5 6 7.5 6 9 7.2 12 10c3-2.8 4.5-4 6.5-4 3.3 0 4.6 3.5 3 6.5C19 16.65 12 21 12 21z"/></svg>
            <p class="font-display text-xl text-neutral-500">{{ __('header.favorites_empty') }}</p>
            <p class="mt-1 text-sm text-neutral-400">{{ __('favorites.save_hint') }}</p>
            <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand mt-6">{{ __('index.explore_all_listings') }}</a>
        </div>
    </div>
</section>
@endsection
