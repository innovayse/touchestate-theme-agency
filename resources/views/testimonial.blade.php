<?php $page = 'testimonial'; ?>
@section('title')
    {{ __('testimonial.title') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('testimonial.title') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('testimonial.title') }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content">

            <div class="container">

                <!-- start row -->
                <div class="row row-gap-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_1') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-18.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Robert King</a>
                                        <p class="mb-0">Harlingen</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_2') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-17.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Erin Hawkins</a>
                                        <p class="mb-0">Penns Neck.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_3') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Margaret Buchanan</a>
                                        <p class="mb-0">Wausau.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_4') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">George William</a>
                                        <p class="mb-0">Memphis.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_5') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Kent Lintz</a>
                                        <p class="mb-0">San Jose.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_6') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Margaret Lee</a>
                                        <p class="mb-0">Montgomery.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_7') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Amanda Krahn</a>
                                        <p class="mb-0">Dallas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_8') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">David Marx</a>
                                        <p class="mb-0">Aberdeen.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_9') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Billy Davis</a>
                                        <p class="mb-0">Allen.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_10') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Robert King</a>
                                        <p class="mb-0">Harlingen</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_11') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Martina Smith</a>
                                        <p class="mb-0">Glendale.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star</i></span>
                                    <span class="text-warning"><i class="material-icons-outlined">star_half</i></span>
                                </div>
                                <p>{{ __('testimonial.review_12') }}</p>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="img" class="avatar avatar-lg rounded-circle me-2"></a>
                                    <div>
                                        <a href="javascript:void(0);" class="fw-semibold mb-1">Roy Pasco</a>
                                        <p class="mb-0">Lompoc, London.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
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