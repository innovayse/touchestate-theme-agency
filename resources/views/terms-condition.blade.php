@extends('layout.app')
@section('title', __('footer.terms_conditions'))
@section('content')
<x-breadcrumb :title="__('footer.terms_conditions')" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p>{{ $workspace['name'] ?? 'GOLDHOUSE' }} — {{ __('footer.terms_conditions') }}.</p>
    </div>
</section>
@endsection
