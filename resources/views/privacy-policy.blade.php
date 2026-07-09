@extends('layout.app')
@section('title', __('footer.privacy_policy') . ' — ' . ($workspace['name'] ?? ''))

@section('content')
<x-breadcrumb :title="__('footer.privacy_policy')" />

<section class="py-16">
    <div class="container-x">
        <article class="prose prose-neutral mx-auto max-w-3xl">
            <h2>{{ __('privacy.intro_title') }}</h2>
            <p>{{ __('privacy.intro', ['brand' => $workspace['name'] ?? 'TUN']) }}</p>

            <h2>{{ __('privacy.collect_title') }}</h2>
            <p>{{ __('privacy.collect') }}</p>

            <h2>{{ __('privacy.use_title') }}</h2>
            <p>{{ __('privacy.use') }}</p>

            <h2>{{ __('privacy.share_title') }}</h2>
            <p>{{ __('privacy.share') }}</p>

            <h2>{{ __('privacy.cookies_title') }}</h2>
            <p>{{ __('privacy.cookies') }}</p>

            <h2>{{ __('privacy.contact_title') }}</h2>
            <p>{{ __('privacy.contact', ['email' => $workspace['email'] ?? '']) }}</p>
        </article>
    </div>
</section>
@endsection
