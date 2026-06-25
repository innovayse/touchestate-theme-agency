<?php $page = 'contact-us'; ?>
@section('title')
    {{ __('common.contact_us') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.contact_us') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.contact_us') }}
            @endslot
        @endcomponent

        <div class="contact-us-wrap-02">

            <div class="container">

                <!-- start row -->
                <div class="row">
                    <div class="col-lg-12 mx-auto">

                        <!-- start row -->
                        <div class="row align-items-center justify-content-center mb-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="contact-us-item-01">
                                    <div class="d-flex align-items-center">
                                        <span class="material-icons-outlined">mail</span>
                                        <div>
                                            <h6 class="mb-2">{{ __('contact-us.email_address') }}</h6>
                                            <p class="mb-0">{{ $workspace['email'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-4">
                                <div class="contact-us-item-01">
                                    <div class="d-flex align-items-center">
                                        <span class="material-icons-outlined">call</span>
                                        <div>
                                            <h6 class="mb-2">{{ __('contact-us.phone_number') }}</h6>
                                            <p class="mb-0">{{ $workspace['phone'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-4">
                                <div class="contact-us-item-01">
                                    <div class="d-flex align-items-center">
                                        <span class="material-icons-outlined">location_on</span>
                                        <div>
                                            <h6 class="mb-2">{{ __('contact-us.address') }}</h6>
                                            <p class="mb-0">{{ $workspace['address'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                        </div>
                        @if(!empty($workspace['workingHours']))
                        @php
                            $dayMap = ['Mon' => __('common.mon'), 'Tue' => __('common.tue'), 'Wed' => __('common.wed'), 'Thu' => __('common.thu'), 'Fri' => __('common.fri'), 'Sat' => __('common.sat'), 'Sun' => __('common.sun')];
                            $entries = explode('|', $workspace['workingHours']);
                            $groups = [];
                            foreach ($entries as $entry) {
                                [$day, $time] = explode(':', $entry, 2);
                                $translatedDay = $dayMap[$day] ?? $day;
                                if (!empty($groups) && end($groups)['time'] === $time) {
                                    $groups[array_key_last($groups)]['endDay'] = $translatedDay;
                                } else {
                                    $groups[] = ['startDay' => $translatedDay, 'endDay' => null, 'time' => $time];
                                }
                            }
                            $parsed = [];
                            foreach ($groups as $g) {
                                $days = $g['endDay'] ? $g['startDay'] . '–' . $g['endDay'] : $g['startDay'];
                                $parsed[] = $days . ': ' . $g['time'];
                            }
                        @endphp
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="contact-us-item-01">
                                    <div class="d-flex align-items-center">
                                        <span class="material-icons-outlined">schedule</span>
                                        <div>
                                            <h6 class="mb-2">{{ __('contact-us.working_hours') }}</h6>
                                            <p class="mb-0">{{ implode(' · ', $parsed) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- end row -->

                    </div><!-- end col -->
                </div>
                <!-- end row -->

                <!-- start row -->
                <div class="row align-items-center row-gap-3">
                    <div class="col-lg-6">
                        <img src="{{URL::asset('build/img/contact-us/contact-us-img-02.webp')}}" alt="img" class="img-fluid">
                    </div><!-- end col -->
                    <div class="col-lg-6">
                        <div class="contact-us-item-02">
                            <h2>{{ __('contact-us.get_in_touch') }}</h2>
                            <!-- start row -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('contact-us.your_name') }}</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div><!-- end col -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('contact-us.phone_number') }}</label>
                                        <input type="text" class="form-control" id="phone">
                                    </div>
                                </div><!-- end col -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('contact-us.email') }}</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div><!-- end col -->
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('contact-us.subject') }}</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div><!-- end col -->
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('contact-us.description') }}</label>
                                        <textarea class="form-control" rows="3" placeholder="{{ __('contact-us.comments') }}"></textarea>
                                    </div>
                                </div><!-- end col -->
                                <div class="col-md-12">
                                    <a href="javascript:void(0);" class="btn btn-lg btn-dark">{{ __('contact-us.submit_enquiry') }}</a>
                                </div><!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>
                    </div>
                </div>
                <!-- end row -->

            </div>

        </div>

        @php
            $mapLon = $workspace['longitude'] ?? '44.515200';
            $mapLat = $workspace['latitude'] ?? '40.187200';
        @endphp
        <div class="yandex-map">
            <div id="contact-map" style="width:100%;height:450px;"></div>
        </div>

        <script>
        window.addEventListener('load', function () { ymaps.ready(function () {
            var coords = [{{ $mapLat }}, {{ $mapLon }}];
            var map = new ymaps.Map('contact-map', {
                center: coords,
                zoom: 14,
                controls: ['zoomControl']
            });

            var officeSvg = [
                '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="62" viewBox="0 0 48 62">',
                '  <path d="M24 0C10.745 0 0 10.745 0 24c0 18 24 38 24 38s24-20 24-38C48 10.745 37.255 0 24 0z" fill="#dd1111"/>',
                '  <circle cx="24" cy="22" r="14" fill="#fff"/>',
                '  <path d="M16 28V16l8-4 8 4v12h-5v-5h-6v5h-5zm3-7h2v-2h-2v2zm0-4h2v-2h-2v2zm5 4h2v-2h-2v2zm0-4h2v-2h-2v2zm5 4h2v-2h-2v2zm0-4h2v-2h-2v2z" fill="#dd1111"/>',
                '</svg>'
            ].join('');

            var placemark = new ymaps.Placemark(coords, {}, {
                iconLayout: 'default#imageWithContent',
                iconImageHref: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(officeSvg),
                iconImageSize: [48, 62],
                iconImageOffset: [-24, -62]
            });

            placemark.events.add('click', function () {
                map.setCenter(coords, 17, { duration: 500 });
            });

            map.geoObjects.add(placemark);
            map.behaviors.disable('scrollZoom');
        }); });
        </script>

    </div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection
