<?php $page = 'about-us'; ?>
@section('title')
    {{ __('common.about_us') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.about_us') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.about_us') }}
            @endslot
        @endcomponent

        <div class="about-us-item-06">

            <div class="container">
                <!-- start row -->
                <div class="row">
                    <div class="col-lg-12 mx-auto">

                        <div class="about-us-item-01">
                            <h2>{{ __('about-us.title_h2') }}</h2>
                            <p class="mb-0">{{ __('about-us.intro_text') }}</p>
                        </div>

                        <!-- start row -->
                        <div class="row row-gap-4 about-us-img-wrap">
                            <div class="col-md-4 col-lg-4">
                                <img src="{{URL::asset('build/img/about-us/about-us-01.jpg')}}" alt="img" class="img-fluid rounded">
                            </div><!-- end col -->
                            <div class="col-md-4 col-lg-4">
                                <img src="{{URL::asset('build/img/about-us/about-us-02.jpg')}}" alt="img" class="img-fluid rounded">
                            </div><!-- end col -->
                            <div class="col-md-4 col-lg-4">
                                <img src="{{URL::asset('build/img/about-us/about-us-03.jpg')}}" alt="img" class="img-fluid rounded">
                            </div><!-- end col -->
                        </div>
                        <!-- end row -->

                        <!-- start row -->
                        <div class="row row-gap-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="about-us-item-02">
                                    <div class="d-flex align-items-center">
                                        <img src="{{URL::asset('build/img/about-us/listing.svg')}}" alt="" class="img-fluid me-3">
                                        <div>
                                            <h4 class="mb-1">50K</h4>
                                            <p class="mb-0">{{ __('about-us.stat_listings') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-3">
                                <div class="about-us-item-02">
                                    <div class="d-flex align-items-center">
                                        <img src="{{URL::asset('build/img/about-us/agents.svg')}}" alt="" class="img-fluid me-3">
                                        <div>
                                            <h4 class="mb-1">3000+</h4>
                                            <p class="mb-0">{{ __('about-us.stat_agents') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-3">
                                <div class="about-us-item-02">
                                    <div class="d-flex align-items-center">
                                        <img src="{{URL::asset('build/img/about-us/sales.svg')}}" alt="" class="img-fluid me-3">
                                        <div>
                                            <h4 class="mb-1">2000+</h4>
                                            <p class="mb-0">{{ __('about-us.stat_sales') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-3">
                                <div class="about-us-item-02">
                                    <div class="d-flex align-items-center">
                                        <img src="{{URL::asset('build/img/about-us/users.svg')}}" alt="" class="img-fluid me-3">
                                        <div>
                                            <h4 class="mb-1">5000+</h4>
                                            <p class="mb-0">{{ __('about-us.stat_users') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                        </div>
                        <!-- end row -->

                    </div><!-- end col -->
                </div>
                <!-- end row -->
            </div>

        </div>

        <div class="about-us-item-03">
            <img src="{{URL::asset('build/img/bg/about-us-bg-01.png')}}" alt="" class="img-fluid about-us-bg-01 d-none d-lg-flex">
            <img src="{{URL::asset('build/img/bg/about-us-bg-02.png')}}" alt="" class="img-fluid about-us-bg-02 d-none d-lg-flex">
            <div class="container">

                <!-- start row -->
                <div class="row align-items-center row-gap-4 position-relative z-2">
                    <div class="col-xl-5">
                        <div class="me-3">
                            <h2 class="mb-4">{{ __('about-us.book_heading') }}</h2>
                            <img src="{{URL::asset('build/img/about-us/about-us-04.jpg')}}" alt="" class="img-fluid rounded w-100">
                        </div>
                    </div><!-- end col -->
                    <div class="col-xl-7">
                        <h5 class="mb-4">{{ __('about-us.book_subtitle') }}</h5>
                        <p>{{ __('about-us.book_text_1') }}</p>
                        <p class="mb-0">{{ __('about-us.book_text_2') }}</p>
                    </div><!-- end col -->
                </div>
                <!-- end row -->

            </div>
        </div>

        <div class="about-us-item-04">
            <div class="container">

                <!-- start row -->
                <div class="row">
                    <div class="col-lg-11 mx-auto">
                        <div class="text-center about-us-item-05">
                            <h2 class="mb-3">{{ __('about-us.partners_heading') }}</h2>
                            <p class="mb-0">{{ __('about-us.partners_text') }}</p>
                        </div>

                        <!-- start row -->
                        <div class="row align-items-center row-gap-4">
                            <div class="col-md-6 col-lg-2 d-flex">
                                <div class="card border-0 bg-light shadow-none flex-fill mb-0">
                                    <div class="card-body text-center">
                                        <img src="{{URL::asset('build/img/about-us/livechat.svg')}}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-2 d-flex">
                                <div class="card border-0 bg-light shadow-none flex-fill mb-0">
                                    <div class="card-body text-center">
                                        <img src="{{URL::asset('build/img/about-us/headspace.svg')}}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-2 d-flex">
                                <div class="card border-0 bg-light shadow-none flex-fill mb-0">
                                    <div class="card-body text-center">
                                        <img src="{{URL::asset('build/img/about-us/payehere.svg')}}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-2 d-flex">
                                <div class="card border-0 bg-light shadow-none flex-fill mb-0">
                                    <div class="card-body text-center">
                                        <img src="{{URL::asset('build/img/about-us/scapic.svg')}}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-2 d-flex">
                                <div class="card border-0 bg-light shadow-none flex-fill mb-0">
                                    <div class="card-body text-center">
                                        <img src="{{URL::asset('build/img/about-us/livechat.svg')}}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-2 d-flex">
                                <div class="card border-0 bg-light shadow-none flex-fill mb-0">
                                    <div class="card-body text-center">
                                        <img src="{{URL::asset('build/img/about-us/memberstack.svg')}}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div><!-- end col -->
                        </div>
                        <!-- end row -->

                    </div><!-- end col -->
                </div>
                <!-- end row -->

            </div>
        </div>

    </div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection
