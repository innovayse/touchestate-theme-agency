@php
    // Messengers come as raw phone numbers (+374…), not URLs — build proper deep-links
    $waNumber    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
    $viberNumber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;

    // "Touch Estate" brand link — points to touchestate.<tld> based on current host, fallback to config base_url
    $teHost  = request()->getHost();
    $teTld   = ($p = strrpos($teHost, '.')) !== false ? strtolower(substr($teHost, $p + 1)) : '';
    $teUrl   = ctype_alpha($teTld) ? "https://touchestate.$teTld" : config('touchestate.base_url', 'https://touchestate.io');
    $teBrand = '<a href="'.e($teUrl).'" target="_blank" rel="noopener">Touch Estate</a>';
@endphp
<!-- Start Footer -->
<footer class="footer footer-dark">
    <div class="footer-bg">
        <img src="{{URL::asset('build/img/bg/footer-bg-01.png')}}" class="bg-1" alt="">
        <img src="{{URL::asset('build/img/bg/footer-bg-02.png')}}" class="bg-2" alt="">
    </div>

    <!-- Footer Top -->
    <div class="footer-top">
        <div class="container">
            <div class="row justify-content-center footer-row">
                <div class="col-lg-4 col-12">
                    <div class="footer-widget footer-about">
                        <a href="{{url('/')}}" class="footer-logo">
                            @if(!empty($workspace['logoUrl']))
                                <img src="{{ $workspace['logoUrl'] }}" alt="{{ $workspace['name'] ?? 'Logo' }}">
                            @endif
                            @if(!empty($workspace['name']))
                                <span>{{ $workspace['name'] }}</span>
                            @endif
                        </a>
                        <div class="social-links">
                            <div class="social-icon">
                                @if(!empty($workspace['socials']['facebook']))<a href="{{ $workspace['socials']['facebook'] }}" target="_blank"><i class="fa-brands fa-facebook"></i></a>@endif
                                @if(!empty($workspace['socials']['instagram']))<a href="{{ $workspace['socials']['instagram'] }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>@endif
                                @if(!empty($workspace['messengers']['telegram']))<a href="{{ $workspace['messengers']['telegram'] }}" target="_blank"><i class="fa-brands fa-telegram"></i></a>@endif
                                @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>@endif
                                @if($viberNumber)<a href="viber://chat?number=+{{ $viberNumber }}"><i class="fa-brands fa-viber"></i></a>@endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="footer-widget">
                        <h5 class="footer-title">{{ __('footer.our_pages') }}</h5>
                        <ul class="footer-menu">
                            <li><a href="{{'/'.app()->getLocale().'/property'}}">{{ __('footer.listings') }}</a></li>
                            <li><a href="{{url('faq')}}">{{ __('footer.faq') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="footer-widget">
                        <h5 class="footer-title">{{ __('footer.useful_links') }}</h5>
                        <ul class="footer-menu">
                            <li><a href="{{url('privacy-policy')}}">{{ __('footer.privacy_policy') }}</a></li>
                            <li><a href="{{url('terms-condition')}}">{{ __('footer.terms_conditions') }}</a></li>
                            <li><a href="{{url('contact-us')}}">{{ __('footer.contact_us') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Social for mobile <550px -->
        <div class="footer-social-mobile">
            <div class="social-icon">
                @if(!empty($workspace['socials']['facebook']))<a href="{{ $workspace['socials']['facebook'] }}" target="_blank"><i class="fa-brands fa-facebook"></i></a>@endif
                @if(!empty($workspace['socials']['instagram']))<a href="{{ $workspace['socials']['instagram'] }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>@endif
                @if(!empty($workspace['messengers']['telegram']))<a href="{{ $workspace['messengers']['telegram'] }}" target="_blank"><i class="fa-brands fa-telegram"></i></a>@endif
                @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>@endif
                @if($viberNumber)<a href="viber://chat?number=+{{ $viberNumber }}"><i class="fa-brands fa-viber"></i></a>@endif
            </div>
        </div>
    </div>
    <!-- /Footer Top -->

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="copyright text-center">
                <p>{{ __('footer.copyright') }} &copy; <script>document.write(new Date().getFullYear())</script>. {!! __('footer.all_rights', ['brand' => $teBrand]) !!}</p>
            </div>
        </div>
    </div>
    <!-- /Footer Bottom -->

</footer>
<!-- End Footer -->