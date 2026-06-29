@props(['title'])
@php $locale = app()->getLocale(); @endphp

<section class="bg-ink pt-28 pb-12">
    <div class="container-x text-center">
        <h1 class="font-display text-4xl font-bold text-white">{{ $title }}</h1>
        <nav class="mt-3 flex items-center justify-center gap-2 text-sm text-white/60">
            <a href="{{ url('/'.$locale) }}" class="text-brand-400 hover:text-brand-300">{{ __('header.home') }}</a>
            <span>›</span>
            <span>{{ $title }}</span>
        </nav>
    </div>
</section>
