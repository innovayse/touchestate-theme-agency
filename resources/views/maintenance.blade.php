@extends('layout.app')
@section('title', __('errors.heading_maintenance'))
@section('content')
<x-breadcrumb :title="__('errors.heading_maintenance')" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p class="text-center">{{ __('errors.body_maintenance') }}</p>
    </div>
</section>
@endsection
