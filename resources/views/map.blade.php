@extends('layout.app')
@section('title', __('header.property'))
@section('content')
<x-breadcrumb :title="__('header.property')" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p class="rounded-2xl border border-dashed border-sand bg-panel py-16 text-center">{{ __('index.coming_soon') }}</p>
    </div>
</section>
@endsection
