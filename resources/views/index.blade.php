@extends('layout.app')
@section('title', __('header.home'))

@php $locale = app()->getLocale(); @endphp

@section('content')

{{-- ───────────────────────── Hero ───────────────────────── --}}
<section class="relative bg-gradient-to-b from-panel via-cream to-sand pt-20 pb-16 sm:pt-24 sm:pb-24 lg:pb-40">
    {{-- Mobile background photo --}}
    <div class="absolute inset-0 overflow-hidden lg:hidden">
        <img src="{{ asset('build/img/section-bg/banner-bg-02.png') }}" alt=""
             class="h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-b from-panel/80 via-cream/75 to-sand/90"></div>
    </div>
    <div class="container-x grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
        <div class="relative z-10">
            <span class="font-display text-5xl/none font-extrabold text-brand-600">*</span>
            <h1 class="mt-4 font-display text-[25px] font-extrabold leading-[1.05] text-ink sm:text-5xl lg:text-6xl">
                {{ __('index.hero_title') }}
                <span class="text-brand-600">{{ __('index.hero_title_highlight') }}</span>
                {{ __('index.hero_title_end') }}
            </h1>
            <p class="mt-5 max-w-md text-base leading-relaxed text-neutral-600">{{ __('index.hero_description') }}</p>

            <div class="mt-8 flex flex-wrap justify-center gap-6 sm:justify-start">
                <div class="flex items-center gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-600">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                            <path d="M9 21V12h6v9"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-3xl font-bold text-ink">{{ $stats['activeProperties'] ?? 0 }}+</div>
                        <div class="text-sm text-neutral-500">{{ __('index.counter_active') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-600">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-3xl font-bold text-ink">{{ $stats['successfulDeals'] ?? 0 }}+</div>
                        <div class="text-sm text-neutral-500">{{ __('index.counter_deals') }}</div>
                    </div>
                </div>
            </div>

            <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand mt-8 w-full sm:w-auto">{{ __('index.hero_buy_property') }}</a>
        </div>

        <div class="relative hidden items-center justify-center sm:flex lg:h-[500px]">
            {{-- Soft glow blob --}}
            <div class="absolute inset-0 rounded-[3rem] bg-brand-100/60 blur-2xl"></div>
            {{-- Rounded card frame --}}
            <div class="relative z-10 w-full overflow-hidden rounded-[2.5rem] shadow-2xl">
                <img src="{{ asset('build/img/section-bg/banner-bg-02.png') }}" alt=""
                     class="h-64 w-full object-cover sm:h-auto">
            </div>
        </div>
    </div>

    {{-- Find Properties panel --}}
    <div class="container-x relative z-20 mt-10 lg:-mb-56">
        <form action="{{ url('/'.$locale.'/property') }}" method="GET"
              class="rounded-3xl border border-sand bg-panel/95 p-6 shadow-[0_24px_60px_-28px_rgba(94,51,39,0.35)] backdrop-blur sm:p-7">
            <h2 class="mb-5 font-display text-xl font-bold text-ink">{{ __('index.search_location') }}</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-brand-600">{{ __('index.search_buy_sell') }}</span>
                    <x-custom-select name="transactionType" :options="[
                        'Sale'      => __('property.for_sale'),
                        'Rent'      => __('property.rent_monthly'),
                        'RentDaily' => __('property.rent_daily'),
                    ]" />
                </div>
                <div class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-brand-600">{{ __('index.search_type') }}</span>
                    @php
                        $typeOptions = [];
                        foreach($availableTypes as $pt) {
                            $k = 'property.'.strtolower($pt);
                            $typeOptions[$pt] = ($l = __($k)) === $k ? $pt : $l;
                        }
                    @endphp
                    <x-custom-select name="propertyType" :options="$typeOptions" />
                </div>
                <div class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-brand-600">{{ __('index.search_location') }}</span>
                    <x-location-input name="search" placeholder="{{ __('index.search_location') }}" />
                </div>
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
<section class="pt-10 pb-12 lg:pt-72 lg:pb-20">
    <div class="container-x grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
            <span class="text-sm font-semibold uppercase tracking-wider text-brand-600">{{ __('index.about_badge') }}</span>
            <h2 class="mt-3 font-display text-2xl font-bold sm:text-4xl leading-tight text-ink">{{ __('index.about_title') }}</h2>
            <p class="mt-4 max-w-lg text-neutral-600">{{ __('index.about_description') }}</p>


            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ url('/'.$locale.'/property') }}" class="btn-brand w-full sm:w-auto">{{ __('index.about_find_property') }}</a>
                <a href="{{ url('/'.$locale.'/contact-us') }}" class="btn-outline w-full sm:w-auto">{{ __('index.about_contact') }}</a>
            </div>
        </div>

        <div class="relative hidden sm:block">
            <img src="{{ asset('build/img/section-bg/section-bg-03.jpg') }}" alt="" class="h-[420px] w-full rounded-3xl object-cover">
            <div class="absolute -bottom-6 -left-6 hidden rounded-2xl bg-brand-600 px-6 py-5 text-white shadow-xl sm:block">
                <div class="font-display text-3xl font-bold">{{ $stats['satisfactionRate'] ?? 98 }}%</div>
                <div class="text-sm text-white/80">{{ __('index.about_badge') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- ───────────────────────── Services ───────────────────────── --}}
<section class="bg-sand/60 py-8 sm:py-14">
    <div class="container-x text-center">
        <h2 class="font-display text-2xl font-bold sm:text-4xl text-ink">{{ __('index.benefits_title') }} <span class="text-brand-600">{{ __('index.benefits_highlight') }}</span> {{ __('index.benefits_end') }}</h2>
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
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-brand-600">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <h3 class="font-display text-lg font-semibold leading-tight">{{ __('index.'.$b[0]) }}</h3>
                </div>
                <p class="mt-4 text-sm text-white/80">{{ __('index.'.$b[1]) }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ───────────────────── Popular listings (tabs) ───────────────────── --}}
<section class="py-8 sm:py-14" id="home-popular-section">
    <div class="container-x text-center">
        <h2 class="font-display text-2xl font-bold sm:text-4xl text-ink">{{ __('index.popular_title') }} <span class="text-brand-600">{{ __('index.popular_highlight') }}</span> {{ __('index.popular_end') }}</h2>
        <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('index.popular_description') }}</p>
    </div>

    {{-- Skeleton: visible until async data arrives --}}
    <div class="container-x mt-12" id="home-popular-skeleton">
        @include('partials.property-cards-skeleton')
    </div>

    {{-- Real content: injected by JS --}}
    <div id="home-popular-content" style="display:none"></div>
</section>

{{-- ───────────────────── How it works ───────────────────── --}}
<section class="py-8 sm:py-14">
    <div class="container-x grid gap-6 lg:grid-cols-2 lg:items-center lg:gap-12">
        <div class="relative flex min-h-[260px] flex-col justify-between overflow-hidden rounded-3xl bg-brand-600 p-8 text-white lg:min-h-[440px]">
            <span class="font-display text-7xl font-bold text-white/30">"</span>
            <div>
                <p class="font-display text-2xl font-semibold leading-snug">{{ __('index.work_title') }}</p>
                <p class="mt-4 text-sm text-white/80">{{ __('index.work_description') }}</p>
                <a href="{{ url('/'.$locale.'/property') }}" class="mt-6 inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ __('index.work_find_property') }}</a>
            </div>
        </div>
        <div>
            <span class="text-sm font-semibold uppercase tracking-wider text-brand-600">{{ __('index.work_how_badge') }}</span>
            <h2 class="mt-3 font-display text-2xl font-bold sm:text-4xl leading-tight text-ink">{{ __('index.work_how_title') }}</h2>

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

{{-- ───────────────────── Property types ───────────────────── --}}
<section class="bg-sand/60 py-8 sm:py-14" id="home-types-section" style="display:none">
    <div class="container-x text-center">
        <h2 class="font-display text-2xl font-bold sm:text-4xl text-ink">{{ __('index.property_type_title') }} <span class="text-brand-600">{{ __('index.property_type_highlight') }}</span> {{ __('index.property_type_end') }}</h2>
        <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('index.property_type_description') }}</p>
    </div>
    <div id="home-types-content"></div>
</section>

<script>
(function () {
    var url = '{{ url('/'.$locale.'/home/data') }}';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var skeleton = document.getElementById('home-popular-skeleton');
            var popular  = document.getElementById('home-popular-content');
            var typesSection = document.getElementById('home-types-section');
            var typesContent = document.getElementById('home-types-content');

            if (skeleton && popular) {
                popular.innerHTML = data.popularHtml || '';
                popular.style.display = '';
                skeleton.style.display = 'none';
                if (window.Alpine) Alpine.initTree(popular);
            }
            if (typesContent && data.typesHtml) {
                typesContent.innerHTML = data.typesHtml;
                typesSection.style.display = '';
                if (window.Alpine) Alpine.initTree(typesSection);
            }
        })
        .catch(function () {
            var skeleton = document.getElementById('home-popular-skeleton');
            if (skeleton) skeleton.classList.remove('animate-pulse');
        });
})();
</script>


{{-- ───────────────────────── FAQ ───────────────────────── --}}
<section class="bg-sand/60 py-8 sm:py-14">
    <div class="container-x">
        <div class="text-center">
            <h2 class="font-display text-2xl font-bold sm:text-4xl text-ink">{{ __('index.faq_title') }} <span class="text-brand-600">{{ __('index.faq_highlight') }}</span></h2>
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
