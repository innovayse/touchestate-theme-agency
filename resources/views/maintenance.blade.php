<?php $page = 'maintenance'; ?>
@section('title')
    Maintenance
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="container-fuild bg-light">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100 z-1">
            <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                <div class="col-lg-6">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="error-images mb-4">
                            <img src="{{URL::asset('build/img/error/under-maintenance.svg')}}" alt="image" class="img-fluid">
                        </div>
                        <div class="text-center">
                            <h4 class="mb-2">{{ __('errors.heading_maintenance') }}</h4>
                            <p class="text-center mb-4">{{ __('errors.body_maintenance') }}</p>
                            <div class="d-flex align-items-center justify-content-center mb-4">
                                <a href="javascript:void(0);" class="btn btn-white rounded-circle p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-facebook"></i></a>
                                <a href="javascript:void(0);" class="btn btn-white rounded-circle p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-x-twitter"></i></a>
                                <a href="javascript:void(0);" class="btn btn-white rounded-circle p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-instagram"></i></a>
                                <a href="javascript:void(0);" class="btn btn-white rounded-circle p-2 d-inline-flex align-items-center justify-content-end border-0 me-2"><i class="fa-brands fa-linkedin"></i></a>
                                <a href="javascript:void(0);" class="btn btn-white rounded-circle p-2 d-inline-flex align-items-center justify-content-end border-0"><i class="fa-brands fa-pinterest"></i></a>
                            </div>
                            <div class="d-flex justify-content-center">
                                <a href="{{url('index')}}" class="btn btn-dark d-flex align-items-center">{{ __('errors.back_home') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->

@endsection