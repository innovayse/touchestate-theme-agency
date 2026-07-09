@php
    $locale    = app()->getLocale();
    $flags     = ['en' => '<span class="fi fi-us rounded-sm"></span>', 'ru' => '<span class="fi fi-ru rounded-sm"></span>', 'hy' => '<span class="fi fi-am rounded-sm"></span>'];
    $langNames = ['en' => 'English', 'ru' => 'Русский', 'hy' => 'Հայերեն'];
    $rawPath   = preg_replace('#^(en|ru|hy)(/|$)#', '', request()->path());
    $rawPath   = $rawPath === '/' ? '' : $rawPath;

    $navLinks = [
        ['href' => url('/'.$locale),               'label' => __('header.home'),       'match' => fn() => request()->is($locale, '/')],
        ['href' => url('/'.$locale.'/property'),    'label' => __('header.property'),   'match' => fn() => request()->is($locale.'/property*')],
        ['href' => url('/'.$locale.'/map'),         'label' => __('header.map'),        'match' => fn() => request()->is($locale.'/map*')],
        ['href' => url('/'.$locale.'/faq'),         'label' => __('footer.faq'),        'match' => fn() => request()->is($locale.'/faq*')],
        ['href' => url('/'.$locale.'/contact-us'),  'label' => __('footer.contact_us'), 'match' => fn() => request()->is($locale.'/contact-us*')],
    ];
@endphp

<header x-data="{ scrolled: false, mobile: false }"
        @scroll.window="scrolled = window.scrollY > 20"
        :class="scrolled ? 'bg-cream/95 shadow-sm backdrop-blur' : 'bg-cream/80 backdrop-blur'"
        class="fixed inset-x-0 top-0 z-50 transition-all">
    <div class="container-x flex h-20 items-center justify-between gap-4">

        {{-- Brand --}}
        <a href="{{ url('/'.$locale) }}" class="flex shrink-0 items-center gap-2.5">
            @if(!empty($workspace['logoUrl']))
                <img src="{{ $workspace['logoUrl'] }}" alt="{{ $workspace['name'] ?? '' }}" class="h-9 w-auto">
            @else
                <svg width="30" height="24" viewBox="0 0 30 24" fill="none" class="text-ink">
                    <path d="M2 22 L11 4 L16.5 13.5 L20 8 L28 22 Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                </svg>
            @endif
            <span class="font-display text-xl font-bold tracking-[0.06em] text-ink">{{ $workspace['name'] ?? 'GOLDHOUSE' }}</span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden items-center gap-7 lg:flex">
            @foreach($navLinks as $link)
                <a href="{{ $link['href'] }}"
                   class="text-sm font-medium transition {{ $link['match']() ? 'text-brand-600' : 'text-ink hover:text-brand-600' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Right actions --}}
        <div class="flex items-center gap-2">
            {{-- Favorites (desktop only) --}}
            <a href="{{ url('/'.$locale.'/favorites') }}" title="{{ __('header.favorites') }}"
               class="relative hidden h-10 w-10 place-items-center rounded-full border transition lg:grid"
               :class="count > 0 ? 'border-red-300 bg-red-50 text-red-500' : 'border-sand bg-panel text-ink hover:border-brand-400 hover:text-brand-600'"
               x-data="{ count: 0 }"
               x-init="count = (JSON.parse(localStorage.getItem('te_favorites') || '[]')).length"
               @te-storage.window="count = (JSON.parse(localStorage.getItem('te_favorites') || '[]')).length">
                <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" :fill="count > 0 ? 'currentColor' : 'none'">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span x-show="count > 0" x-text="count"
                      class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-600 text-[9px] font-bold text-white"></span>
            </a>

            {{-- Compare (desktop only) --}}
            <a href="{{ url('/'.$locale.'/compare') }}" title="{{ __('header.compare') }}"
               class="relative hidden h-10 w-10 place-items-center rounded-full border transition lg:grid"
               :class="count > 0 ? 'border-brand-400 bg-brand-50 text-brand-600' : 'border-sand bg-panel text-ink hover:border-brand-400 hover:text-brand-600'"
               x-data="{ count: 0 }"
               x-init="count = (JSON.parse(localStorage.getItem('te_compare') || '[]')).length"
               @te-storage.window="count = (JSON.parse(localStorage.getItem('te_compare') || '[]')).length">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" :stroke-width="count > 0 ? '2.5' : '1.8'">
                    <path d="M18 20V10M12 20V4M6 20v-6"/>
                </svg>
                <span x-show="count > 0" x-text="count"
                      class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-600 text-[9px] font-bold text-white"></span>
            </a>

            {{-- Language switcher --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex h-10 items-center gap-1 rounded-full border border-sand bg-panel px-3 text-sm font-medium text-ink transition hover:border-brand-400">
                    {!! $flags[$locale] ?? '<span class="fi fi-xx rounded-sm"></span>' !!}
                    <svg width="12" height="12" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.5" :class="open?'rotate-180':''"><path d="M1 1l5 5 5-5"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border border-sand bg-white py-1 shadow-lg">
                    @foreach(['en','ru','hy'] as $lng)
                        <a href="{{ '/' . $lng . ($rawPath ? '/' . $rawPath : '') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm {{ $locale === $lng ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-ink hover:bg-brand-50' }}">
                            {!! $flags[$lng] !!}
                            <span class="font-sans">{{ $langNames[$lng] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Hamburger (mobile only) --}}
            <button @click="mobile = true" class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-panel text-ink lg:hidden">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    {{-- ── Mobile modal ────────────────────────────────── --}}
    {{-- Backdrop --}}
    <div x-show="mobile"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         @click="mobile = false"
         class="fixed inset-0 z-[60] bg-ink/50 backdrop-blur-sm lg:hidden">
    </div>

    {{-- Drawer panel --}}
    <div x-show="mobile"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         x-cloak
         @click.outside="mobile = false"
         class="fixed right-0 top-0 z-[61] flex h-screen w-72 flex-col bg-cream shadow-2xl lg:hidden">

        {{-- Drawer header --}}
        <div class="flex h-20 shrink-0 items-center justify-between border-b border-sand px-6">
            <a href="{{ url('/'.$locale) }}" class="flex items-center gap-2.5" @click="mobile = false">
                @if(!empty($workspace['logoUrl']))
                    <img src="{{ $workspace['logoUrl'] }}" alt="{{ $workspace['name'] ?? '' }}" class="h-8 w-auto">
                @else
                    <svg width="26" height="21" viewBox="0 0 30 24" fill="none" class="text-ink">
                        <path d="M2 22 L11 4 L16.5 13.5 L20 8 L28 22 Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    </svg>
                @endif
                <span class="font-display text-lg font-bold tracking-[0.06em] text-ink">{{ $workspace['name'] ?? 'GOLDHOUSE' }}</span>
            </a>
            <button @click="mobile = false"
                    class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-panel text-ink">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav links --}}
        <nav class="flex flex-1 flex-col overflow-y-auto px-6 py-6">
            @foreach($navLinks as $link)
                <a href="{{ $link['href'] }}"
                   @click="mobile = false"
                   class="flex items-center border-b border-sand/60 py-4 text-base font-semibold transition
                          {{ $link['match']() ? 'text-brand-600' : 'text-ink hover:text-brand-600' }}">
                    {{ $link['label'] }}
                    <svg class="ml-auto shrink-0 {{ $link['match']() ? 'text-brand-600' : 'text-neutral-300' }}"
                         width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
            @endforeach
        </nav>

        {{-- Bottom bar: icons + language --}}
        <div class="shrink-0 border-t border-sand px-6 py-5 flex items-center justify-between">
            <div class="flex gap-2.5">
                {{-- Favorites --}}
                <a href="{{ url('/'.$locale.'/favorites') }}"
                   class="relative flex h-11 w-11 items-center justify-center rounded-full border transition"
                   :class="count > 0 ? 'border-red-300 bg-red-50 text-red-500' : 'border-sand bg-panel text-ink'"
                   x-data="{ count: 0 }"
                   x-init="count = (JSON.parse(localStorage.getItem('te_favorites') || '[]')).length"
                   @te-storage.window="count = (JSON.parse(localStorage.getItem('te_favorites') || '[]')).length">
                    <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" :fill="count > 0 ? 'currentColor' : 'none'">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <span x-show="count > 0" x-text="count"
                          class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-600 text-[9px] font-bold text-white"></span>
                </a>
                {{-- Compare --}}
                <a href="{{ url('/'.$locale.'/compare') }}"
                   class="relative flex h-11 w-11 items-center justify-center rounded-full border transition"
                   :class="count > 0 ? 'border-brand-400 bg-brand-50 text-brand-600' : 'border-sand bg-panel text-ink'"
                   x-data="{ count: 0 }"
                   x-init="count = (JSON.parse(localStorage.getItem('te_compare') || '[]')).length"
                   @te-storage.window="count = (JSON.parse(localStorage.getItem('te_compare') || '[]')).length">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" :stroke-width="count > 0 ? '2.5' : '1.8'">
                        <path d="M18 20V10M12 20V4M6 20v-6"/>
                    </svg>
                    <span x-show="count > 0" x-text="count"
                          class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-600 text-[9px] font-bold text-white"></span>
                </a>
            </div>

            {{-- Language switcher in drawer --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex h-10 items-center gap-1.5 rounded-full border border-sand bg-panel px-3 text-sm font-medium text-ink">
                    {!! $flags[$locale] ?? '<span class="fi fi-xx rounded-sm"></span>' !!}
                    <svg width="11" height="11" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.5" :class="open?'rotate-180':''"><path d="M1 1l5 5 5-5"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak
                     class="absolute bottom-12 right-0 w-44 overflow-hidden rounded-xl border border-sand bg-white py-1 shadow-lg">
                    @foreach(['en','ru','hy'] as $lng)
                        <a href="{{ '/' . $lng . ($rawPath ? '/' . $rawPath : '') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm {{ $locale === $lng ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-ink hover:bg-brand-50' }}">
                            {!! $flags[$lng] !!}
                            <span class="font-sans">{{ $langNames[$lng] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>
