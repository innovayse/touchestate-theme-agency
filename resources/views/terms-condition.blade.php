@extends('layout.app')
@section('title', __('footer.terms_conditions') . ' — ' . ($workspace['name'] ?? ''))

@section('content')
<x-breadcrumb :title="__('footer.terms_conditions')" />

<section class="py-16">
    <div class="container-x">
        <article class="prose prose-neutral mx-auto max-w-3xl">
            <h2>{{ __('terms.intro_title') }}</h2>
            <p>{{ __('terms.intro', ['brand' => $workspace['name'] ?? 'TUN']) }}</p>

            <h2>{{ __('terms.use_title') }}</h2>
            <p>{{ __('terms.use') }}</p>

            <h2>{{ __('terms.listings_title') }}</h2>
            <p>{{ __('terms.listings') }}</p>

            <h2>{{ __('terms.liability_title') }}</h2>
            <p>{{ __('terms.liability') }}</p>

            <h2>{{ __('terms.changes_title') }}</h2>
            <p>{{ __('terms.changes') }}</p>

            <h2>{{ __('terms.contact_title') }}</h2>
            <p>{{ __('terms.contact', ['email' => $workspace['email'] ?? '']) }}</p>
        </article>
    </div>
</section>
@endsection
