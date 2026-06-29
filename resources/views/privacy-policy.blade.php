@extends('layout.app')
@section('title', __('footer.privacy_policy'))
@section('content')
<x-breadcrumb :title="__('footer.privacy_policy')" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p>{{ $workspace['name'] ?? 'GOLDHOUSE' }} — {{ __('footer.privacy_policy') }}.</p>
    </div>
</section>
@endsection
