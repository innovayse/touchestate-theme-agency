@extends('layout.app')
@section('title', __('footer.faq'))

@section('content')
<x-breadcrumb :title="__('footer.faq')" />

<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5">
        <div class="space-y-3">
            @foreach(['1','2','3','4','5'] as $i)
                <div class="overflow-hidden rounded-2xl border border-sand bg-white">
                    <div class="px-6 py-5">
                        <h3 class="font-semibold text-ink">{{ __('index.faq_q'.$i) }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">{{ __('index.faq_a'.$i) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
