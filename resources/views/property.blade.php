@extends('layout.app')
@section('title', __('header.property'))

@php
    $locale = app()->getLocale();
    $items  = $properties['items'] ?? [];
    $total  = $properties['totalCount'] ?? count($items);
@endphp

@section('content')
<x-breadcrumb :title="__('header.property')" />

<section class="py-14">
    <div class="container-x">
        {{-- Filter bar --}}
        <form action="{{ url('/'.$locale.'/property') }}" method="GET"
              class="mb-10 grid gap-4 rounded-3xl border border-sand bg-panel p-6 md:grid-cols-2 lg:grid-cols-4">
            <select name="transactionType" class="rounded-xl border border-sand bg-white px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">{{ __('index.search_buy_sell') }}</option>
                <option value="Sale" @selected(request('transactionType')==='Sale')>{{ __('property.for_sale') }}</option>
                <option value="Rent" @selected(request('transactionType')==='Rent')>{{ __('property.rent_monthly') }}</option>
                <option value="RentDaily" @selected(request('transactionType')==='RentDaily')>{{ __('property.rent_daily') }}</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('index.search_location') }}"
                   class="rounded-xl border border-sand bg-white px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
            <input type="text" name="propertyType" value="{{ request('propertyType') }}" placeholder="{{ __('index.search_type') }}"
                   class="rounded-xl border border-sand bg-white px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
            <button type="submit" class="btn-brand">{{ __('header.search') }}</button>
        </form>

        <p class="mb-6 text-sm text-neutral-500">{{ __('property.showing_result') }} <span class="font-semibold text-ink">{{ count($items) }}</span> {{ __('property.of') }} {{ $total }}</p>

        @if(count($items))
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($items as $prop)
                    <x-property-card :prop="$prop" />
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-sand bg-panel py-24 text-center">
                <p class="font-display text-xl text-neutral-500">{{ __('index.coming_soon') }}</p>
                <p class="mt-1 text-sm text-neutral-400">{{ __('index.coming_soon_sub') }}</p>
            </div>
        @endif
    </div>
</section>
@endsection
