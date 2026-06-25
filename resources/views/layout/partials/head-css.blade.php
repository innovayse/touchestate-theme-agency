    <!-- Favicon -->
    <link rel="shortcut icon" href="{{URL::asset('build/img/favicon.png')}}">

    <!-- Apple Icon -->
    <link rel="apple-touch-icon" href="{{URL::asset('build/img/apple-icon.png')}}">

	<!-- Theme Settings Js -->
	<script src="{{URL::asset('build/js/theme-script.js')}}"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/bootstrap.min.css')}}">

	<!-- Material Icon CSS — skipped on property single pages that use the local SVG icon sprite (/img/icons.svg) -->
	@if (!Request::is('property/*', '*/property/*'))
	<link rel="stylesheet" href="{{URL::asset('build/plugins/material-icon/material-icon.css')}}">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">
	@endif

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="{{URL::asset('build/plugins/fontawesome/css/fontawesome.min.css')}}">
	<link rel="stylesheet" href="{{URL::asset('build/plugins/fontawesome/css/all.min.css')}}">

@if (Route::is(['faq']))
    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/tabler-icons/tabler-icons.min.css')}}">
@endif

@if (Route::is(['add-property-buy', 'add-property-rent', 'checkout', 'rent-details', 'rental-booking', 'rental-payment']))
	<!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/bootstrap-datetimepicker.min.css')}}">
@endif

	<!-- Simplebar CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/simplebar/simplebar.min.css')}}">

@if (Route::is(['checkout', 'contact-us', 'rental-order-confirmation', 'rental-order-details']))
    <!-- intel input -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/intltelinput/css/intlTelInput.css')}}">
@endif

@if (!Route::is(['about-us']))
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="{{URL::asset('build/plugins/select2/css/select2.min.css')}}">
@endif

@if (Route::is(['buy-details', 'rent-details', 'property.single']) || request()->is('property/*') || request()->is('*/property/*'))
	<!-- Fancybox v5 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/fancybox/fancybox.css')}}">
@endif

@if (Route::is(['index', 'rent-details', 'property.single']) || request()->is('/') || request()->is('property/*') || request()->is('*/property/*'))
	<!-- Slick CSS -->
	<link rel="stylesheet" href="{{URL::asset('build/plugins/slick/slick.css')}}">
	<link rel="stylesheet" href="{{URL::asset('build/plugins/slick/slick-theme.css')}}">
@endif

@if (Route::is(['agent-details', 'index-2', 'index-3']))
	<!-- Swiper CSS -->
	<link rel="stylesheet" href="{{URL::asset('build/plugins/swiper/swiper-bundle.min.css')}}">
@endif

@if (Route::is(['property-sidebar', 'buy-property-list-sidebar', 'rent-property-grid-sidebar', 'rent-property-list-sidebar']))
    <!-- Rangeslider CSS -->
	<link rel="stylesheet" href="{{URL::asset('build/plugins/ion-rangeslider/css/ion.rangeSlider.css')}}">
	<link rel="stylesheet" href="{{URL::asset('build/plugins/ion-rangeslider/css/ion.rangeSlider.min.css')}}">
@endif

@if (Route::is(['gallery', 'privacy-policy', 'terms-condition']))
    <!-- Glightbox CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/lightbox/glightbox.min.css')}}">
@endif

@if (Route::is(['index', 'index-2', 'index-3']))
	<!-- Aos CSS -->
	<link rel="stylesheet" href="{{URL::asset('build/css/aos.css')}}">
@endif

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/style.css')}}?v={{ filemtime(public_path('build/css/style.css')) }}" id="app-style">

    <!-- Custom Language Spacing CSS -->
    <style>
        .main-nav > li {
            margin-right: 1.5rem;
        }
        .main-nav > li > a {
            white-space: nowrap;
            padding: 0.5rem 0.75rem;
        }
        /* Make all language flag icons circular */
        .dropdown-menu img[src*="flags/"],
        .dropdown-toggle img[src*="flags/"],
        img[src*="flags/"] {
            border-radius: 50%;
            object-fit: cover;
        }
        @if(app()->getLocale() === 'hy')
        .main-nav > li {
            margin-right: 0.5rem;
        }
        .main-nav > li > a {
            padding: 0.5rem 0.4rem;
            font-size: 14px;
        }
        .header-items .btn {
            padding: 0.5rem 0.75rem;
            font-size: 14px;
        }
        @endif
    </style>