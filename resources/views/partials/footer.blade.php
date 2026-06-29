@php
    $locale = app()->getLocale();
    $brand  = $workspace['name'] ?? 'GOLDHOUSE';
    $waNumber    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
    $viberNumber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;
@endphp

<footer class="relative mt-20 bg-ink text-white/70">
    <span class="absolute inset-x-0 top-0 h-1 bg-brand-600"></span>

    <div class="container-x grid gap-10 py-16 md:grid-cols-2 lg:grid-cols-4">
        {{-- Brand --}}
        <div class="lg:col-span-1">
            <div class="flex items-center gap-2.5 text-white">
                <svg width="30" height="24" viewBox="0 0 30 24" fill="none"><path d="M2 22 L11 4 L16.5 13.5 L20 8 L28 22 Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                <span class="font-display text-xl font-bold tracking-[0.06em]">{{ $brand }}</span>
            </div>
            <p class="mt-4 max-w-xs text-sm leading-relaxed">
                {{ $workspace['description'] ?? __('index.hero_description') }}
            </p>
        </div>

        {{-- Our pages --}}
        <div>
            <h4 class="mb-4 font-display text-base font-semibold text-white">{{ __('footer.our_pages') }}</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ url('/'.$locale.'/property') }}" class="transition hover:text-brand-400">{{ __('footer.listings') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/faq') }}" class="transition hover:text-brand-400">{{ __('footer.faq') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/contact-us') }}" class="transition hover:text-brand-400">{{ __('footer.contact_us') }}</a></li>
            </ul>
        </div>

        {{-- Useful links --}}
        <div>
            <h4 class="mb-4 font-display text-base font-semibold text-white">{{ __('footer.useful_links') }}</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ url('/'.$locale.'/privacy-policy') }}" class="transition hover:text-brand-400">{{ __('footer.privacy_policy') }}</a></li>
                <li><a href="{{ url('/'.$locale.'/terms-condition') }}" class="transition hover:text-brand-400">{{ __('footer.terms_conditions') }}</a></li>
            </ul>
        </div>

        {{-- Contact --}}
        <div>
            <h4 class="mb-4 font-display text-base font-semibold text-white">{{ __('footer.contact_us') }}</h4>
            <ul class="space-y-2.5 text-sm">
                @if(!empty($workspace['phone']))
                    <li><a href="tel:{{ $workspace['phone'] }}" class="transition hover:text-brand-400">{{ $workspace['phone'] }}</a></li>
                @endif
                @if(!empty($workspace['email']))
                    <li><a href="mailto:{{ $workspace['email'] }}" class="transition hover:text-brand-400">{{ $workspace['email'] }}</a></li>
                @endif
                @if(!empty($workspace['address']))
                    <li>{{ $workspace['address'] }}</li>
                @endif
            </ul>
            <div class="mt-4 flex gap-2">
                @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-full bg-white/10 transition hover:bg-brand-600 hover:text-white">WA</a>@endif
                @if($viberNumber)<a href="viber://chat?number=%2B{{ $viberNumber }}" class="grid h-9 w-9 place-items-center rounded-full bg-white/10 transition hover:bg-brand-600 hover:text-white">V</a>@endif
                @if(!empty($workspace['socials']['facebook']))<a href="{{ $workspace['socials']['facebook'] }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-full bg-white/10 transition hover:bg-brand-600 hover:text-white">f</a>@endif
                @if(!empty($workspace['socials']['instagram']))<a href="{{ $workspace['socials']['instagram'] }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-full bg-white/10 transition hover:bg-brand-600 hover:text-white">ig</a>@endif
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-x py-5 text-center text-sm">
            {{ __('footer.copyright') }} © {{ date('Y') }}. {{ __('footer.all_rights', ['brand' => $brand]) }}
        </div>
    </div>
</footer>
