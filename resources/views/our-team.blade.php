<?php $page = 'our-team'; ?>
@section('title')
    {{ __('common.team') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.team') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.team') }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content">

            <div class="container">

                <!-- start row -->
                <div class="row row-gap-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Seth Franklin</a>
                                    <p class="mb-4">{{ __('our-team.ceo') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Carol Currie</a>
                                    <p class="mb-4">{{ __('our-team.marketing_head') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Daniel Moreno</a>
                                    <p class="mb-4">{{ __('our-team.marketing_head') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Linda Martin</a>
                                    <p class="mb-4">{{ __('our-team.developer') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Bonnie Scott</a>
                                    <p class="mb-4">{{ __('our-team.ceo') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-07.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Jacquelin Maldonado</a>
                                    <p class="mb-4">{{ __('our-team.marketing_head') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Peggy Smith</a>
                                    <p class="mb-4">{{ __('our-team.marketing_head') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-15.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Mary Oliver</a>
                                    <p class="mb-4">{{ __('our-team.developer') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-14.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Nicholas Massey</a>
                                    <p class="mb-4">{{ __('our-team.ceo') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-17.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Katherine Marker</a>
                                    <p class="mb-4">{{ __('our-team.marketing_head') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-18.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Kevin kent</a>
                                    <p class="mb-4">{{ __('our-team.marketing_head') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="javascript:void(0);"><img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="img" class="avatar avatar-xl rounded-circle mb-4"></a>
                                    <a href="javascript:void(0);" class="fw-semibold d-block">Richard Pearson</a>
                                    <p class="mb-4">{{ __('our-team.developer') }}</p>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-light rounded-2 p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
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
