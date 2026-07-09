@extends('layout.app')
@section('title', '500 — ' . ($workspace['name'] ?? ''))

@php $locale = app()->getLocale(); @endphp

@section('content')
<section class="flex min-h-[70vh] flex-col items-center justify-center py-20 text-center">
    <div class="font-display text-[8rem] font-extrabold leading-none text-brand-200">500</div>
    <h1 class="mt-4 font-display text-3xl font-bold text-ink">{{ __('error.500_title') }}</h1>
    <p class="mx-auto mt-3 max-w-md text-neutral-600">{{ __('error.500_description') }}</p>
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
        <a href="{{ url('/'.$locale) }}" class="btn-brand">{{ __('error.go_home') }}</a>
    </div>
</section>
@endsection
