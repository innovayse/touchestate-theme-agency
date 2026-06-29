@extends('layout.app')
@section('title', 'Testimonials')
@section('content')
<x-breadcrumb :title="'Testimonials'" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p class="text-center">{{ __('index.coming_soon') }}</p>
    </div>
</section>
@endsection
