@extends('layout.app')
@section('title', __('header.compare') . ' — ' . ($workspace['name'] ?? ''))

@php $locale = app()->getLocale(); @endphp

@section('content')
<x-breadcrumb :title="__('header.compare')" />

<section class="py-16"
    x-data="listLoader('{{ url('/'.$locale.'/compare/load') }}', 'te_compare', { removeEvent: 'te-remove-compare', reExecScripts: true })">

    <div class="container-x">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-neutral-500">
                {{ __('compare.comparing') }} <span x-text="count"></span> {{ __('property.properties') }}
            </p>
            <button x-show="slugs.length > 0" @click="clearAll()"
                    class="text-sm text-red-500 hover:underline">{{ __('favorites.clear_all') }}</button>
        </div>

        {{-- Loading skeleton --}}
        <div x-show="loading" class="animate-pulse overflow-hidden rounded-3xl border border-sand bg-white">
            {{-- Tab bar --}}
            <div class="flex items-center gap-6 border-b border-sand px-4 py-3">
                <div class="h-4 w-24 rounded bg-sand"></div>
                <div class="h-4 w-20 rounded bg-sand/60"></div>
                <div class="h-4 w-28 rounded bg-sand/60"></div>
            </div>
            {{-- Card row --}}
            <div class="flex gap-px border-b-2 border-sand">
                <div class="w-[200px] shrink-0 bg-neutral-50 p-4">
                    <div class="h-3 w-20 rounded bg-sand"></div>
                </div>
                @for($i=0;$i<3;$i++)
                <div class="w-[280px] shrink-0 p-2">
                    <div class="h-[200px] rounded-2xl bg-sand"></div>
                </div>
                @endfor
            </div>
            {{-- Data rows --}}
            @for($r=0;$r<6;$r++)
            <div class="flex gap-px border-b border-sand">
                <div class="w-[200px] shrink-0 bg-neutral-50/60 px-4 py-3">
                    <div class="h-3 w-24 rounded bg-sand/70"></div>
                </div>
                @for($i=0;$i<3;$i++)
                <div class="w-[280px] shrink-0 px-4 py-3 flex justify-center">
                    <div class="h-3 w-16 rounded bg-sand/50"></div>
                </div>
                @endfor
            </div>
            @endfor
        </div>

        {{-- Table --}}
        <div x-show="!loading && count > 0" x-html="html"
             class="rounded-3xl border border-sand bg-white"></div>

        {{-- Empty --}}
        <div x-show="!loading && count === 0"
             class="rounded-3xl border border-dashed border-sand bg-panel py-24 text-center">
            <svg class="mx-auto mb-4 text-brand-200" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
            <p class="font-display text-xl text-neutral-500">{{ __('compare.empty') }}</p>
            <p class="mt-1 text-sm text-neutral-400">{{ __('compare.hint') }}</p>
            <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand mt-6">{{ __('index.explore_all_listings') }}</a>
        </div>
    </div>
</section>
@endsection
