@extends('layout.app')
@section('title', '404 — ' . ($workspace['name'] ?? ''))

@php $locale = app()->getLocale(); @endphp

@section('content')
<section class="flex min-h-[70vh] flex-col items-center justify-center py-20 text-center">
    <div class="font-display text-[8rem] font-extrabold leading-none text-brand-200">404</div>
    <h1 class="mt-4 font-display text-3xl font-bold text-ink">{{ __('error.404_title') }}</h1>
    <p class="mx-auto mt-3 max-w-md text-neutral-600">{{ __('error.404_description') }}</p>
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
        <a href="{{ url('/'.$locale) }}" class="btn-brand">{{ __('error.go_home') }}</a>
        <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline">{{ __('header.property') }}</a>
    </div>
</section>
@endsection
