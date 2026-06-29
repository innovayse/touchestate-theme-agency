@extends('layout.app')
@section('title', __('footer.faq'))

@section('content')
<x-breadcrumb :title="__('footer.faq')" />

<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5">
        <div class="space-y-3" x-data="{ open: 1 }">
            @foreach(['1','2','3','4','5'] as $i)
                <div class="overflow-hidden rounded-2xl border border-sand bg-white">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left">
                        <span class="font-medium text-ink">{{ __('index.faq_q'.$i) }}</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-brand-600 transition" :class="open==={{ $i }} ? 'rotate-45' : ''"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-6 pb-5 text-sm leading-relaxed text-neutral-600">
                        {{ __('index.faq_a'.$i) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
