<?php $page = 'error-500'; ?>
@section('title')
    Error 500
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="container-fuild">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100 z-1">
            <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                <div class="col-lg-6">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="error-images mb-4">
                            <img src="{{URL::asset('build/img/error/error-500.svg')}}" alt="image" class="img-fluid">
                        </div>
                        <div class="text-center">
                            <h4 class="mb-2">{{ __('errors.heading_500') }}</h4>
                            <p class="text-center">{{ __('errors.body_500') }}</p>
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