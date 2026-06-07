<?php $page = 'terms-condition'; ?>
@section('title')
    Terms & Conditions
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.terms_condition') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.terms_condition') }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content">

            <div class="container">

                <!-- start row -->
                <div class="row">
                    <div class="col-lg-12">
                        <h6 class="mb-3">{{ __('terms-condition.section_intro') }}</h6>
                        <p class="mb-3">{{ __('terms-condition.intro_body') }}</p>
                        <h6 class="mb-3">{{ __('terms-condition.section_acceptance') }}</h6>
                        <p class="mb-3">{{ __('terms-condition.acceptance_body') }}</p>
                        <h6 class="mb-3">{{ __('terms-condition.section_booking') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('terms-condition.booking_1') }}</li>
                            <li class="mb-2">{{ __('terms-condition.booking_2') }}</li>
                            <li class="mb-0">{{ __('terms-condition.booking_3') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('terms-condition.section_use') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('terms-condition.use_1') }}</li>
                            <li class="mb-2">{{ __('terms-condition.use_2') }}</li>
                            <li class="mb-0">{{ __('terms-condition.use_3') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('terms-condition.section_conduct') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('terms-condition.conduct_1') }}</li>
                            <li class="mb-2">{{ __('terms-condition.conduct_2') }}</li>
                            <li class="mb-0">{{ __('terms-condition.conduct_3') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('terms-condition.section_damages') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('terms-condition.damages_1') }}</li>
                            <li class="mb-0">{{ __('terms-condition.damages_2') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('terms-condition.section_cancellation') }}</h6>
                        <ul class="list-styled mb-3">
                            <li class="mb-2">{{ __('terms-condition.cancellation_1') }}</li>
                            <li class="mb-0">{{ __('terms-condition.cancellation_2') }}</li>
                        </ul>
                        <h6 class="mb-3">{{ __('terms-condition.section_force') }}</h6>
                        <p class="mb-3">{{ __('terms-condition.force_body') }}</p>
                        <h6 class="mb-3">{{ __('terms-condition.section_insurance') }}</h6>
                        <p class="mb-3">{{ __('terms-condition.insurance_body') }}</p>
                        <h6 class="mb-3">{{ __('terms-condition.section_law') }}</h6>
                        <p class="mb-0">{{ __('terms-condition.law_body') }}</p>
                    </div>
                </div>
                <!-- end row -->

            </div>

        </div>
        <!-- End Content -->

    </div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection
