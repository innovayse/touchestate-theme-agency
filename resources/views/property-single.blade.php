@extends('layout.app')
@section('title', $property['title'] ?? __('header.property'))

@php
    $locale = app()->getLocale();
    // Normalise image list into plain URLs (API shape varies)
    $images = [];
    foreach (($property['images'] ?? []) as $img) {
        if (is_string($img)) { $images[] = $img; }
        elseif (is_array($img)) { $images[] = $img['url'] ?? $img['imageUrl'] ?? $img['primaryImageUrl'] ?? null; }
    }
    $images = array_values(array_filter($images));
    if (!$images && !empty($property['primaryImageUrl'])) { $images = [$property['primaryImageUrl']]; }
    $price = isset($property['price']) ? number_format((float) $property['price']) : null;
    $waNumber    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
    $viberNumber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;
@endphp

@section('content')
<x-breadcrumb :title="$property['title'] ?? __('header.property')" />

<section class="py-14">
    <div class="container-x grid gap-10 lg:grid-cols-3">
        {{-- Main --}}
        <div class="lg:col-span-2">
            {{-- Gallery --}}
            @if($images)
                <div class="overflow-hidden rounded-3xl">
                    <img src="{{ $images[0] }}" alt="{{ $property['title'] ?? '' }}" class="h-[420px] w-full object-cover">
                </div>
                @if(count($images) > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3">
                        @foreach(array_slice($images, 1, 4) as $img)
                            <img src="{{ $img }}" alt="" class="h-24 w-full rounded-xl object-cover">
                        @endforeach
                    </div>
                @endif
            @else
                <div class="grid h-[420px] place-items-center rounded-3xl bg-sand text-brand-300">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg>
                </div>
            @endif

            <div class="mt-8">
                <h1 class="font-display text-3xl font-bold text-ink">{{ $property['title'] ?? '' }}</h1>
                <p class="mt-2 text-neutral-500">{{ $property['fullAddress'] ?? $property['city'] ?? '' }}</p>

                <div class="mt-6 flex flex-wrap gap-6 border-y border-sand py-5 text-sm text-neutral-700">
                    @if(!empty($property['bedrooms']))<span>🛏 {{ $property['bedrooms'] }} {{ __('index.bedroom') }}</span>@endif
                    @if(!empty($property['bathrooms']))<span>🛁 {{ $property['bathrooms'] }} {{ __('index.bath') }}</span>@endif
                    @if(!empty($property['areaTotal']))<span>📐 {{ $property['areaTotal'] }} {{ __('index.sq_ft') }}</span>@endif
                </div>

                @if(!empty($property['description']))
                    <div class="prose mt-6 max-w-none text-neutral-600">{!! $property['description'] !!}</div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="lg:col-span-1">
            <div class="sticky top-28 rounded-3xl border border-sand bg-panel p-6">
                @if($price)
                    <div class="font-display text-3xl font-bold text-brand-700">{{ $property['currency'] ?? '' }}{{ $price }}</div>
                @endif
                <div class="mt-5 space-y-2.5">
                    @if(!empty($workspace['phone']))
                        <a href="tel:{{ $workspace['phone'] }}" class="btn-brand w-full">{{ $workspace['phone'] }}</a>
                    @endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="btn-outline w-full">WhatsApp</a>@endif
                    @if($viberNumber)<a href="viber://chat?number=%2B{{ $viberNumber }}" class="btn-outline w-full">Viber</a>@endif
                    <a href="{{ url('/'.$locale.'/contact-us') }}" class="btn-outline w-full">{{ __('footer.contact_us') }}</a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
