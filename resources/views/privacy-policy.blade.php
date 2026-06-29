<?php $page = 'privacy-policy'; ?>
@section('title')
    Privacy Policy
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.privacy_policy') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.privacy_policy') }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content">

            <div class="container">

                <!-- start row -->
                <div class="row">
                    <div class="col-lg-12">
                        <p class="mb-3">{{ __('privacy-policy.intro_text') }}</p>
                        <h6 class="mb-3">{{ __('privacy-policy.section_intro') }}</h6>
                        <p>{{ __('privacy-policy.intro_body') }}</p>
                        <h6 class="mb-3">{{ __('privacy-policy.section_acceptance') }}</h6>
                        <p>{{ __('privacy-policy.acceptance_body') }}</p>
                        <h6 class="mb-3">{{ __('privacy-policy.section_use') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('privacy-policy.use_1') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.use_2') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.use_3') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.use_4') }}</li>
                            <li>{{ __('privacy-policy.use_5') }}</li>
                        </ul>
                        <h6 class="mb-1">{{ __('privacy-policy.section_sharing') }}</h6>
                        <p class="mb-3">{{ __('privacy-policy.sharing_intro') }}</p>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('privacy-policy.sharing_1') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.sharing_2') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.sharing_3') }}</li>
                            <li>{{ __('privacy-policy.sharing_4') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('privacy-policy.section_choices') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('privacy-policy.choices_1') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.choices_2') }}</li>
                            <li class="mb-2">{{ __('privacy-policy.choices_3') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('privacy-policy.section_security') }}</h6>
                        <p class="mb-3">{{ __('privacy-policy.security_body') }}</p>
                        <h6 class="mb-3">{{ __('privacy-policy.section_retention') }}</h6>
                        <p class="mb-3">{{ __('privacy-policy.retention_body') }}</p>
                        <h6 class="mb-3">{{ __('privacy-policy.section_international') }}</h6>
                        <p class="mb-3">{{ __('privacy-policy.international_body') }}</p>
                        <h6 class="mb-3">{{ __('privacy-policy.section_changes') }}</h6>
                        <p class="mb-0">{{ __('privacy-policy.changes_body') }}</p>
                    </div>
                </div>
                <!-- end row -->

            </div>

        </div>
        <!-- End Content -->

    <!-- ========================
        End Page Content
    ========================= -->

@endsection
