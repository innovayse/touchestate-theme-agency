@extends('layout.app')
@section('title', __('header.favorites'))
@section('content')
<x-breadcrumb :title="__('header.favorites')" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p class="rounded-2xl border border-dashed border-sand bg-panel py-16 text-center">{{ __('header.favorites_empty') }}</p>
    </div>
</section>
@endsection
