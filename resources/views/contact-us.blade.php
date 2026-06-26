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

                        <!-- Contact details: email / phone / address -->
                        <div class="row g-3 justify-content-center mb-3">
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
                        <!-- Working hours -->
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

                <!-- Get in touch: form + image -->
                <div class="row align-items-center row-gap-3">
                    <div class="col-lg-6">
                        <img src="{{URL::asset('build/img/contact-us/contact-us-img-02.jpg')}}" alt="img" class="img-fluid">
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
                                        <input type="text" class="form-control" id="phone" data-search-placeholder="{{ __('contact-us.search_country') }}">
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
                                    <a href="javascript:void(0);" class="btn btn-lg btn-primary">{{ __('contact-us.submit_enquiry') }}</a>
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
        <!-- Office location map (Yandex) -->
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

            // Marker — same teardrop pin with house as used on property pages
            var homeIconSvg = 'data:image/svg+xml,' + encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="52" viewBox="0 0 40 52">' +
                '<path d="M20 0C8.95 0 0 8.95 0 20c0 14 20 32 20 32s20-18 20-32C40 8.95 31.05 0 20 0z" fill="#E74C3C"/>' +
                '<path d="M20 2C9.51 2 1 10.51 1 20c0 13.37 19 30.5 19 30.5S39 33.37 39 20C39 10.51 30.49 2 20 2z" fill="#FF6B6B"/>' +
                '<path d="M20 5C11.18 5 4 12.18 4 21c0 11.8 16 27 16 27s16-15.2 16-27C36 12.18 28.82 5 20 5z" fill="#E74C3C"/>' +
                '<g transform="translate(20,20) scale(0.72) translate(-12,-12)">' +
                '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="#fff"/>' +
                '</g></svg>'
            );

            var placemark = new ymaps.Placemark(coords, {}, {
                iconLayout: 'default#image',
                iconImageHref: homeIconSvg,
                iconImageSize: [38, 50],
                iconImageOffset: [-19, -50]
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
