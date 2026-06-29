@extends('layout.app')
@section('title', '404')
@section('content')
<x-breadcrumb :title="'404'" />
<section class="py-16">
    <div class="mx-auto w-full max-w-3xl px-5 text-neutral-600">
        <p class="text-center">Page not found.</p>
    </div>
</section>
@endsection
