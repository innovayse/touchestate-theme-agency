@extends('layout.app')
@section('title', __('footer.testimonials') . ' — ' . ($workspace['name'] ?? ''))

@section('content')
<x-breadcrumb :title="__('footer.testimonials')" />

<section class="py-16">
    <div class="container-x">
        <div class="mb-12 text-center">
            <span class="text-sm font-semibold uppercase tracking-wider text-brand-600">{{ __('testimonial.badge') }}</span>
            <h1 class="mt-3 font-display text-4xl font-bold text-ink">{{ __('testimonial.title') }} <span class="text-brand-600">{{ __('testimonial.highlight') }}</span></h1>
            <p class="mx-auto mt-3 max-w-2xl text-neutral-600">{{ __('testimonial.description') }}</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(range(1,6) as $i)
                <div class="rounded-2xl border border-sand bg-white p-6 shadow-sm">
                    <div class="mb-4 flex gap-0.5">
                        @for($s=1;$s<=5;$s++)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#a6644f" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm leading-relaxed text-neutral-600">{{ __('testimonial.review_'.$i) }}</p>
                    <div class="mt-5 flex items-center gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-100 font-display text-sm font-bold text-brand-700">
                            {{ __('testimonial.initial_'.$i) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-ink">{{ __('testimonial.name_'.$i) }}</div>
                            <div class="text-xs text-neutral-500">{{ __('testimonial.role_'.$i) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
