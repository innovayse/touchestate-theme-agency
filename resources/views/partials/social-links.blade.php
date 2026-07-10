{{--
    Social messenger links partial.

    Variables inherited from parent scope:
      $waNumber    — WhatsApp number (digits only) or null
      $viberNumber — Viber number (digits only) or null
      $tgLink      — Telegram URL or null
      $workspace   — workspace array (may contain ['socials']['instagram'], ['socials']['facebook'])

    Optional:
      $variant — 'icon' (default, dark rounded icon buttons) | 'button' (outlined text buttons)
--}}
@php $variant = $variant ?? 'icon'; @endphp

@if($variant === 'icon')
    {{-- Icon variant: used in footer --}}
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
@else
    {{-- Button variant: used in contact-us --}}
    @if($waNumber)
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="btn-outline">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 2C6.51 2 2.04 6.47 2.04 11.975c0 1.757.46 3.47 1.33 4.978L2 22l5.2-1.36A10.01 10.01 0 0 0 12.017 22c5.506 0 9.977-4.47 9.977-9.975 0-2.664-1.04-5.17-2.92-7.05C17.19 3.09 14.68 2 12.017 2z"/></svg>
            {{ __('contact-us.whatsapp') }}
        </a>
    @endif
    @if($viberNumber)
        <a href="viber://chat?number=%2B{{ $viberNumber }}" class="btn-outline">{{ __('contact-us.viber') }}</a>
    @endif
    @if($tgLink)
        <a href="{{ $tgLink }}" target="_blank" rel="noopener" class="btn-outline">{{ __('contact-us.telegram') }}</a>
    @endif
@endif
