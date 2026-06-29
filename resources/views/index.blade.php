@extends('layout.app')
@section('title', __('header.home'))

@php $locale = app()->getLocale(); @endphp

@section('content')

{{-- ───────────────────────── Hero ───────────────────────── --}}
<section class="relative bg-gradient-to-b from-panel via-cream to-sand pt-28 pb-40">
    <div class="container-x grid items-center gap-10 lg:grid-cols-2">
        <div class="relative z-10">
            <span class="font-display text-5xl/none font-extrabold text-brand-600">*</span>
            <h1 class="mt-4 font-display text-5xl font-extrabold leading-[1.05] text-ink sm:text-6xl">
                {{ __('index.hero_title') }}
                <span class="text-brand-600">{{ __('index.hero_title_highlight') }}</span>
                {{ __('index.hero_title_end') }}
            </h1>
            <p class="mt-5 max-w-md text-base leading-relaxed text-neutral-600">{{ __('index.hero_description') }}</p>

            <div class="mt-8 flex flex-wrap items-center gap-8">
                <div>
                    <div class="font-display text-3xl font-bold text-ink">{{ $stats['activeProperties'] ?? 0 }}+</div>
                    <div class="text-sm text-neutral-500">{{ __('index.counter_active') }}</div>
                </div>
                <div>
                    <div class="font-display text-3xl font-bold text-ink">{{ $stats['successfulDeals'] ?? 0 }}+</div>
                    <div class="text-sm text-neutral-500">{{ __('index.counter_deals') }}</div>
                </div>
            </div>

            <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand mt-8">{{ __('index.hero_buy_property') }}</a>
        </div>

        <div class="relative lg:h-[460px]">
            <div class="absolute -right-10 top-6 hidden h-72 w-72 rounded-full bg-brand-200/50 blur-3xl lg:block"></div>
            <img src="{{ asset('build/img/section-bg/banner-bg-02.png') }}" alt="" class="relative z-10 mx-auto w-full max-w-xl drop-shadow-2xl">
        </div>
    </div>

    {{-- Find Properties panel --}}
    <div class="container-x relative z-20 -mb-56 mt-10">
        <form action="{{ url('/'.$locale.'/property') }}" method="GET"
              class="rounded-3xl border border-sand bg-panel/95 p-6 shadow-[0_24px_60px_-28px_rgba(94,51,39,0.35)] backdrop-blur sm:p-7">
            <h2 class="mb-5 font-display text-xl font-bold text-ink">{{ __('index.search_location') }}</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-brand-600">{{ __('index.search_buy_sell') }}</span>
                    <select name="transactionType" class="w-full rounded-xl border border-sand bg-white px-4 py-3 text-sm text-ink focus:border-brand-500 focus:outline-none">
                        <option value="">{{ __('index.search_select') }}</option>
                        <option value="Sale">{{ __('property.for_sale') }}</option>
                        <option value="Rent">{{ __('property.rent_monthly') }}</option>
                        <option value="RentDaily">{{ __('property.rent_daily') }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-brand-600">{{ __('index.search_type') }}</span>
                    <select name="propertyType" class="w-full rounded-xl border border-sand bg-white px-4 py-3 text-sm text-ink focus:border-brand-500 focus:outline-none">
                        <option value="">{{ __('index.search_select') }}</option>
                        @foreach($availableTypes as $pt)
                            @php $k = 'property.'.strtolower($pt); $lbl = __($k); if($lbl===$k){$lbl=$pt;} @endphp
                            <option value="{{ $pt }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-brand-600">{{ __('index.search_location') }}</span>
                    <input type="text" name="search" placeholder="{{ __('index.search_location') }}" class="w-full rounded-xl border border-sand bg-white px-4 py-3 text-sm text-ink focus:border-brand-500 focus:outline-none">
                </label>
                <div class="flex items-end">
                    <button type="submit" class="btn-brand w-full py-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                        {{ __('header.search') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- ───────────────────── Trusted advisors / stats ───────────────────── --}}
<section class="pt-72 pb-20">
    <div class="container-x grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
            <span class="text-sm font-semibold uppercase tracking-wider text-brand-600">{{ __('index.about_badge') }}</span>
            <h2 class="mt-3 font-display text-4xl font-bold leading-tight text-ink">{{ __('index.about_title') }}</h2>
            <p class="mt-4 max-w-lg text-neutral-600">{{ __('index.about_description') }}</p>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:max-w-md">
                <div class="rounded-2xl border border-sand bg-panel p-5">
                    <div class="font-display text-3xl font-bold text-brand-700">{{ $stats['activeProperties'] ?? 0 }}+</div>
                    <div class="text-sm text-neutral-500">{{ __('index.counter_active') }}</div>
                </div>
                <div class="rounded-2xl bg-brand-600 p-5 text-white">
                    <div class="font-display text-3xl font-bold">{{ $stats['successfulDeals'] ?? 0 }}+</div>
                    <div class="text-sm text-white/80">{{ __('index.counter_deals') }}</div>
                </div>
            </div>
            <div class="mt-6 flex gap-4">
                <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand">{{ __('index.about_find_property') }}</a>
                <a href="{{ url('/'.$locale.'/contact-us') }}" class="btn-outline">{{ __('index.about_contact') }}</a>
            </div>
        </div>

        <div class="relative">
            <img src="{{ asset('build/img/section-bg/section-bg-03.jpg') }}" alt="" class="h-[420px] w-full rounded-3xl object-cover">
            <div class="absolute -bottom-6 -left-6 hidden rounded-2xl bg-brand-600 px-6 py-5 text-white shadow-xl sm:block">
                <div class="font-display text-3xl font-bold">{{ $stats['satisfactionRate'] ?? 98 }}%</div>
                <div class="text-sm text-white/80">{{ __('index.about_badge') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- ───────────────────────── Services ───────────────────────── --}}
<section class="bg-sand/60 py-20">
    <div class="container-x text-center">
        <h2 class="font-display text-4xl font-bold text-ink">{{ __('index.benefits_title') }} <span class="text-brand-600">{{ __('index.benefits_highlight') }}</span> {{ __('index.benefits_end') }}</h2>
        <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('index.benefits_description') }}</p>
    </div>

    <div class="container-x mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @php
            $benefits = [
                ['benefits_verified','benefits_verified_desc'],
                ['benefits_reach','benefits_reach_desc'],
                ['benefits_communication','benefits_communication_desc'],
                ['benefits_expert','benefits_expert_desc'],
                ['benefits_tailored','benefits_tailored_desc'],
                ['benefits_seamless','benefits_seamless_desc'],
            ];
        @endphp
        @foreach($benefits as $b)
            <div class="rounded-2xl bg-brand-600 p-7 text-white">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white text-brand-600">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                <h3 class="mt-5 font-display text-lg font-semibold">{{ __('index.'.$b[0]) }}</h3>
                <p class="mt-2 text-sm text-white/80">{{ __('index.'.$b[1]) }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ───────────────────── Popular listings (tabs) ───────────────────── --}}
<section class="py-20" x-data="{ tab: '{{ count($saleProperties) ? 'sale' : 'rent' }}' }">
    <div class="container-x text-center">
        <h2 class="font-display text-4xl font-bold text-ink">{{ __('index.popular_title') }} <span class="text-brand-600">{{ __('index.popular_highlight') }}</span> {{ __('index.popular_end') }}</h2>
        <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('index.popular_description') }}</p>

        <div class="mt-7 inline-flex rounded-full border border-sand bg-panel p-1">
            <button @click="tab='sale'" :class="tab==='sale' ? 'bg-brand-600 text-white' : 'text-ink'" class="rounded-full px-6 py-2 text-sm font-semibold transition">{{ __('index.popular_for_sale') }}</button>
            <button @click="tab='rent'" :class="tab==='rent' ? 'bg-brand-600 text-white' : 'text-ink'" class="rounded-full px-6 py-2 text-sm font-semibold transition">{{ __('index.popular_for_rent') }}</button>
        </div>
    </div>

    @if(count($saleProperties) || count($rentProperties))
        <div class="container-x mt-12">
            <div x-show="tab==='sale'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($saleProperties as $prop)
                    <x-property-card :prop="$prop" />
                @empty
                    <p class="col-span-full text-center text-neutral-500">{{ __('index.coming_soon') }}</p>
                @endforelse
            </div>
            <div x-show="tab==='rent'" x-cloak class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($rentProperties as $prop)
                    <x-property-card :prop="$prop" />
                @empty
                    <p class="col-span-full text-center text-neutral-500">{{ __('index.coming_soon') }}</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="container-x mt-12 rounded-3xl border border-dashed border-sand bg-panel py-20 text-center">
            <p class="font-display text-xl text-neutral-500">{{ __('index.coming_soon') }}</p>
            <p class="mt-1 text-sm text-neutral-400">{{ __('index.coming_soon_sub') }}</p>
        </div>
    @endif

    <div class="mt-10 text-center">
        <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline">{{ __('index.explore_all_listings') }}</a>
    </div>
</section>

{{-- ───────────────────── Property types ───────────────────── --}}
@if(array_sum($typeCounts) > 0)
<section class="bg-sand/60 py-20">
    <div class="container-x text-center">
        <h2 class="font-display text-4xl font-bold text-ink">{{ __('index.property_type_title') }} <span class="text-brand-600">{{ __('index.property_type_highlight') }}</span> {{ __('index.property_type_end') }}</h2>
        <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('index.property_type_description') }}</p>
    </div>
    <div class="container-x mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($typeCounts as $type => $cnt)
            @continue($cnt === 0)
            @php $k='property.'.strtolower($type); $lbl=__($k); if($lbl===$k){$lbl=$type;} $img=$typeImages[$type][0] ?? null; @endphp
            <a href="{{ url('/'.$locale.'/property?propertyType='.$type) }}" class="group relative overflow-hidden rounded-2xl border border-sand bg-white">
                <div class="h-44 overflow-hidden bg-sand">
                    @if($img)<img src="{{ $img }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">@endif
                </div>
                <div class="p-5">
                    <h3 class="font-display text-lg font-semibold text-ink">{{ $lbl }}</h3>
                    <p class="text-sm text-neutral-500">{{ $cnt }} {{ __('index.property_type_available') }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ───────────────────── How it works ───────────────────── --}}
<section class="py-20">
    <div class="container-x grid gap-12 lg:grid-cols-2 lg:items-center">
        <div class="relative flex min-h-[440px] flex-col justify-between overflow-hidden rounded-3xl bg-brand-600 p-10 text-white">
            <span class="font-display text-7xl font-bold text-white/30">“</span>
            <div>
                <p class="font-display text-2xl font-semibold leading-snug">{{ __('index.work_title') }}</p>
                <p class="mt-4 text-sm text-white/80">{{ __('index.work_description') }}</p>
                <a href="{{ url('/'.$locale.'/property') }}" class="mt-6 inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ __('index.work_find_property') }}</a>
            </div>
        </div>
        <div>
            <span class="text-sm font-semibold uppercase tracking-wider text-brand-600">{{ __('index.work_how_badge') }}</span>
            <h2 class="mt-3 font-display text-4xl font-bold leading-tight text-ink">{{ __('index.work_how_title') }}</h2>

            <div class="mt-8 space-y-5">
                @foreach(['1','2','3'] as $i)
                    <div class="rounded-2xl border border-sand bg-panel p-5">
                        <h3 class="font-display text-lg font-semibold text-brand-700">0{{ $i }}. {{ __('index.work_step'.$i) }}</h3>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('index.work_step'.$i.'_desc') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ───────────────────────── FAQ ───────────────────────── --}}
<section class="bg-sand/60 py-20">
    <div class="container-x">
        <div class="text-center">
            <h2 class="font-display text-4xl font-bold text-ink">{{ __('index.faq_title') }} <span class="text-brand-600">{{ __('index.faq_highlight') }}</span></h2>
            <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('index.faq_description') }}</p>
        </div>

        <div class="mx-auto mt-10 max-w-3xl space-y-3" x-data="{ open: 1 }">
            @foreach(['1','2','3','4','5'] as $i)
                <div class="overflow-hidden rounded-2xl border border-sand bg-white">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left">
                        <span class="font-medium text-ink">{{ __('index.faq_q'.$i) }}</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-brand-600 transition" :class="open==={{ $i }} ? 'rotate-45' : ''"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-6 pb-5 text-sm leading-relaxed text-neutral-600">
                        {{ __('index.faq_a'.$i) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
