@php
    $locale  = app()->getLocale();
    $brand   = $workspace['name'] ?? 'GOLDHOUSE';
    $waNumber    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
    $viberNumber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;
    $tgLink      = $workspace['messengers']['telegram'] ?? null;
@endphp

<footer class="relative mt-20 bg-ink text-white/70">
    <span class="absolute inset-x-0 top-0 h-1 bg-brand-600"></span>

    <div class="container-x grid gap-10 py-16 md:grid-cols-2 lg:grid-cols-4">
        {{-- Brand --}}
        <div class="lg:col-span-1">
            <a href="{{ url('/'.$locale) }}" class="flex items-center gap-2.5 text-white">
                @if(!empty($workspace['logoUrl']))
                    <img src="{{ $workspace['logoUrl'] }}" alt="{{ $brand }}" class="h-8 w-auto brightness-0 invert">
                @else
                    <svg width="30" height="24" viewBox="0 0 30 24" fill="none"><path d="M2 22 L11 4 L16.5 13.5 L20 8 L28 22 Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                @endif
                <span class="font-display text-xl font-bold tracking-[0.06em]">{{ $brand }}</span>
            </a>
            <p class="mt-4 max-w-xs text-sm leading-relaxed">
                {{ $workspace['description'] ?? __('index.hero_description') }}
            </p>
            <div class="mt-5 flex flex-wrap justify-evenly gap-2 sm:justify-start">
                @if($waNumber)
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" title="WhatsApp"
                       class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-brand-600 hover:text-white">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 2C6.51 2 2.04 6.47 2.04 11.975c0 1.757.46 3.47 1.33 4.978L2 22l5.2-1.36A10.01 10.01 0 0 0 12.017 22c5.506 0 9.977-4.47 9.977-9.975 0-2.664-1.04-5.17-2.92-7.05C17.19 3.09 14.68 2 12.017 2z"/></svg>
                    </a>
                @endif
                @if($viberNumber)
                    <a href="viber://chat?number=%2B{{ $viberNumber }}" title="Viber"
                       class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-brand-600 hover:text-white">V</a>
                @endif
                @if($tgLink)
                    <a href="{{ $tgLink }}" target="_blank" rel="noopener" title="Telegram"
                       class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-brand-600 hover:text-white">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.94 8.2l-2.02 9.53c-.15.68-.54.84-1.09.52l-3-2.21-1.45 1.4c-.16.16-.3.3-.61.3l.22-3.07 5.59-5.05c.24-.22-.05-.34-.38-.12L7.08 14.07 4.13 13.1c-.63-.2-.64-.63.13-.93l11.6-4.47c.53-.2.99.12.08 1.5z"/></svg>
                    </a>
                @endif
                @if(!empty($workspace['socials']['instagram']))
                    <a href="{{ $workspace['socials']['instagram'] }}" target="_blank" rel="noopener" title="Instagram"
                       class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-brand-600 hover:text-white">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                @endif
                @if(!empty($workspace['socials']['facebook']))
                    <a href="{{ $workspace['socials']['facebook'] }}" target="_blank" rel="noopener" title="Facebook"
                       class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-brand-600 hover:text-white">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                @endif
            </div>
        </div>

        {{-- Our pages --}}
        <div>
            <h4 class="mb-4 font-display text-base font-semibold text-white">{{ __('footer.our_pages') }}</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ url('/'.$locale.'/property') }}" class="transition hover:text-brand-400">{{ __('footer.listings') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/map') }}" class="transition hover:text-brand-400">{{ __('header.map') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/favorites') }}" class="transition hover:text-brand-400">{{ __('header.favorites') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/compare') }}" class="transition hover:text-brand-400">{{ __('header.compare') }}</a></li>
                {{-- <li><a href="{{ url('/'.$locale.'/testimonial') }}" class="transition hover:text-brand-400">{{ __('footer.testimonials') }}</a></li> --}}
            </ul>
        </div>

        {{-- Useful links --}}
        <div>
            <h4 class="mb-4 font-display text-base font-semibold text-white">{{ __('footer.useful_links') }}</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ url('/'.$locale.'/faq') }}" class="transition hover:text-brand-400">{{ __('footer.faq') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/contact-us') }}" class="transition hover:text-brand-400">{{ __('footer.contact_us') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/privacy-policy') }}" class="transition hover:text-brand-400">{{ __('footer.privacy_policy') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/terms-condition') }}" class="transition hover:text-brand-400">{{ __('footer.terms_conditions') }}</a></li>
            </ul>
        </div>

        {{-- Contact --}}
        <div>
            <h4 class="mb-4 font-display text-base font-semibold text-white">{{ __('footer.contact_us') }}</h4>
            <ul class="space-y-3 text-sm">
                @if(!empty($workspace['phone']))
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 shrink-0 text-brand-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.62 19a19.45 19.45 0 0 1-6-6 19.79 19.79 0 0 1-2.91-7.18A2 2 0 0 1 4.71 3.73h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11l-1.27 1.27a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7a2 2 0 0 1 1.72 2.02z"/></svg>
                        <a href="tel:{{ $workspace['phone'] }}" class="transition hover:text-brand-400">{{ $workspace['phone'] }}</a>
                    </li>
                @endif
                @if(!empty($workspace['email']))
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 shrink-0 text-brand-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
                        <a href="mailto:{{ $workspace['email'] }}" class="transition hover:text-brand-400">{{ $workspace['email'] }}</a>
                    </li>
                @endif
                @if(!empty($workspace['address']))
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 shrink-0 text-brand-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        <span>{{ $workspace['address'] }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-x py-5 text-center text-sm">
            {{ __('footer.copyright') }} © {{ date('Y') }}. {{ __('footer.all_rights', ['brand' => $brand]) }}
        </div>
    </div>
</footer>
