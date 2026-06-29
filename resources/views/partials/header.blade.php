@php
    $locale = app()->getLocale();
    $flags  = ['en' => '🇺🇸', 'ru' => '🇷🇺', 'hy' => '🇦🇲'];
    $langNames = ['en' => 'English', 'ru' => 'Русский', 'hy' => 'Հայերեն'];
    $rawPath = preg_replace('#^(en|ru|hy)(/|$)#', '', request()->path());
    $rawPath = $rawPath === '/' ? '' : $rawPath;
@endphp

<header x-data="{ scrolled: false, mobile: false }"
        @scroll.window="scrolled = window.scrollY > 20"
        :class="scrolled ? 'bg-cream/95 shadow-sm backdrop-blur' : 'bg-cream/80 backdrop-blur'"
        class="fixed inset-x-0 top-0 z-50 transition-all">
    <div class="container-x flex h-20 items-center justify-between gap-4">

        {{-- Brand --}}
        <a href="{{ url('/' . $locale) }}" class="flex items-center gap-2.5">
            @if(!empty($workspace['logoUrl']))
                <img src="{{ $workspace['logoUrl'] }}" alt="{{ $workspace['name'] ?? 'GOLDHOUSE' }}" class="h-9 w-auto">
            @else
                <svg width="30" height="24" viewBox="0 0 30 24" fill="none" class="text-ink">
                    <path d="M2 22 L11 4 L16.5 13.5 L20 8 L28 22 Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                </svg>
            @endif
            <span class="leading-none">
                <span class="block font-display text-xl font-bold tracking-[0.06em] text-ink">{{ $workspace['name'] ?? 'GOLDHOUSE' }}</span>
                <span class="block text-[9px] font-semibold tracking-[0.34em] text-brand-600">REAL ESTATE</span>
            </span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden items-center gap-8 lg:flex">
            <a href="{{ url('/' . $locale) }}" class="text-sm font-medium {{ request()->is($locale, '/') ? 'text-brand-600' : 'text-ink hover:text-brand-600' }}">{{ __('header.home') }}</a>
            <a href="{{ url('/' . $locale . '/property') }}" class="text-sm font-medium {{ request()->is($locale.'/property*') ? 'text-brand-600' : 'text-ink hover:text-brand-600' }}">{{ __('header.property') }}</a>
        </nav>

        {{-- Right actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ url('/' . $locale . '/favorites') }}" title="{{ __('header.favorites') }}" class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-panel text-ink transition hover:border-brand-400 hover:text-brand-600">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-4.35-9.5-8.5C.9 9.5 2.2 6 5.5 6 7.5 6 9 7.2 12 10c3-2.8 4.5-4 6.5-4 3.3 0 4.6 3.5 3 6.5C19 16.65 12 21 12 21z"/></svg>
            </a>
            <a href="{{ url('/' . $locale . '/compare') }}" title="{{ __('header.compare') }}" class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-panel text-ink transition hover:border-brand-400 hover:text-brand-600">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
            </a>

            {{-- Language switcher --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="flex h-10 items-center gap-1 rounded-full border border-sand bg-panel px-3 text-sm font-medium text-ink transition hover:border-brand-400">
                    <span>{{ $flags[$locale] ?? '🌐' }}</span>
                    <span class="hidden uppercase sm:inline">{{ $locale }}</span>
                    <svg width="12" height="12" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 1l5 5 5-5"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-40 overflow-hidden rounded-xl border border-sand bg-white py-1 shadow-lg">
                    @foreach(['en','ru','hy'] as $lng)
                        <a href="{{ '/' . $lng . ($rawPath ? '/' . $rawPath : '') }}" class="flex items-center gap-2 px-4 py-2 text-sm {{ $locale === $lng ? 'text-brand-600 font-semibold' : 'text-ink hover:bg-brand-50' }}">
                            <span>{{ $flags[$lng] }}</span> {{ $langNames[$lng] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Mobile menu toggle --}}
            <button @click="mobile = !mobile" class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-panel text-ink lg:hidden">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile nav --}}
    <div x-show="mobile" x-transition x-cloak class="border-t border-sand bg-cream lg:hidden">
        <nav class="container-x flex flex-col py-4">
            <a href="{{ url('/' . $locale) }}" class="py-2.5 text-sm font-medium text-ink">{{ __('header.home') }}</a>
            <a href="{{ url('/' . $locale . '/property') }}" class="py-2.5 text-sm font-medium text-ink">{{ __('header.property') }}</a>
        </nav>
    </div>
</header>
