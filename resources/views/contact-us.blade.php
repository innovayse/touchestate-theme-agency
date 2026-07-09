@extends('layout.app')
@section('title', __('footer.contact_us') . ' — ' . ($workspace['name'] ?? ''))

@php
    $locale      = app()->getLocale();
    $waNumber    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
    $viberNumber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;
    $tgLink      = $workspace['messengers']['telegram'] ?? null;
    $enquireUrl  = url('/api/property/general/enquire');
@endphp

@section('content')
<x-breadcrumb :title="__('footer.contact_us')" />

<section class="py-16">
    <div class="container-x">
        {{-- Info cards --}}
        <div class="grid gap-5 sm:grid-cols-3">
            @if(!empty($workspace['phone']))
                <a href="tel:{{ $workspace['phone'] }}" class="group rounded-2xl border border-sand bg-white p-6 text-center transition hover:border-brand-400 hover:shadow-lg">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.62 19a19.45 19.45 0 0 1-6-6 19.79 19.79 0 0 1-2.91-7.18A2 2 0 0 1 4.71 3.73h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11l-1.27 1.27a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7a2 2 0 0 1 1.72 2.02z"/></svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ __('property.phone') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600 group-hover:text-brand-700">{{ $workspace['phone'] }}</p>
                </a>
            @endif
            @if(!empty($workspace['email']))
                <a href="mailto:{{ $workspace['email'] }}" class="group rounded-2xl border border-sand bg-white p-6 text-center transition hover:border-brand-400 hover:shadow-lg">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ __('contact-us.email') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600 group-hover:text-brand-700">{{ $workspace['email'] }}</p>
                </a>
            @endif
            @if(!empty($workspace['address']))
                <div class="rounded-2xl border border-sand bg-white p-6 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-600">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ __('property.address') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ $workspace['address'] }}</p>
                </div>
            @endif
        </div>

        {{-- Messengers --}}
        @if($waNumber || $viberNumber || $tgLink)
            <div class="mt-8 flex flex-wrap justify-center gap-4">
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
            </div>
        @endif

        {{-- Contact form + working hours --}}
        <div class="mt-14 grid gap-10 lg:grid-cols-2"
             x-data="contactForm('{{ url('/api/contact') }}', {{ Js::from(__('contact.error')) }})">
            {{-- Form --}}
            <div>
                <h2 class="font-display text-2xl font-bold text-ink">{{ __('contact.send_message') }}</h2>
                <p class="mt-2 text-neutral-600">{{ __('contact.form_description') }}</p>

                <div x-show="sent" class="mt-6 rounded-2xl bg-green-50 p-5 text-green-700">
                    <strong>{{ __('contact.sent_title') }}</strong>
                    <p class="mt-1 text-sm">{{ __('contact.sent_description') }}</p>
                </div>
                <div x-show="error" x-text="error" class="mt-6 rounded-2xl bg-red-50 p-5 text-sm text-red-700"></div>

                <form x-show="!sent" class="mt-6 space-y-4" @submit.prevent="send($el)">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-brand-700">{{ __('property.your_name') }} *</label>
                            <input type="text" name="name" required placeholder="{{ __('property.your_name') }}"
                                   class="field">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-brand-700">{{ __('property.your_phone') }}</label>
                            <input type="tel" name="phone" placeholder="{{ __('contact-us.phone_placeholder') }}"
                                   class="field">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-brand-700">{{ __('property.your_email') }} *</label>
                        <input type="email" name="email" required placeholder="{{ __('contact-us.email_placeholder') }}"
                               class="field">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-brand-700">{{ __('property.your_message') }}</label>
                        <textarea name="message" rows="5" placeholder="{{ __('contact.message_placeholder') }}"
                                  class="field resize-none"></textarea>
                    </div>
                    <button type="submit" class="btn-brand" :disabled="loading">
                        <span x-show="!loading">{{ __('property.send') }}</span>
                        <span x-show="loading">…</span>
                    </button>
                </form>
            </div>

            {{-- Working hours --}}
            <div class="rounded-3xl border border-sand bg-panel p-8">
                <h2 class="font-display text-2xl font-bold text-ink">{{ __('contact.working_hours') }}</h2>
                @if(!empty($workspace['workingHours']))
                    @php
                        $dayNames = ['Mon'=>__('contact.mon'),'Tue'=>__('contact.tue'),'Wed'=>__('contact.wed'),'Thu'=>__('contact.thu'),'Fri'=>__('contact.fri'),'Sat'=>__('contact.sat'),'Sun'=>__('contact.sun')];
                        $hours = [];
                        foreach(explode('|', $workspace['workingHours']) as $segment) {
                            if (preg_match('/^([A-Za-z]+):(.+)$/', trim($segment), $m)) {
                                $hours[$m[1]] = $m[2];
                            }
                        }
                    @endphp
                    <div class="mt-5 space-y-3">
                        @foreach($hours as $day => $time)
                            <div class="flex items-center justify-between border-b border-sand pb-3 text-sm last:border-0">
                                <span class="font-medium text-ink">{{ $dayNames[$day] ?? $day }}</span>
                                <span class="text-neutral-600">{{ $time }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-neutral-600">{{ __('contact.contact_for_hours') }}</p>
                @endif

                @if(!empty($workspace['socials']))
                    <div class="mt-8">
                        <h3 class="font-display text-base font-semibold text-ink">{{ __('contact.social_media') }}</h3>
                        <div class="mt-3 flex gap-3">
                            @if(!empty($workspace['socials']['instagram']))
                                <a href="{{ $workspace['socials']['instagram'] }}" target="_blank" rel="noopener"
                                   class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-white text-ink transition hover:border-brand-400 hover:text-brand-600">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                                </a>
                            @endif
                            @if(!empty($workspace['socials']['facebook']))
                                <a href="{{ $workspace['socials']['facebook'] }}" target="_blank" rel="noopener"
                                   class="grid h-10 w-10 place-items-center rounded-full border border-sand bg-white text-ink transition hover:border-brand-400 hover:text-brand-600">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
