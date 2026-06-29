<head>
	<!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="{{ $workspace['description'] ?? '' }}">
	<meta name="keywords" content="real estate, property listings, rentals, sales, apartments, houses, villas">
    <title> @yield('title') | {{ $workspace['name'] ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="{{ $workspace['name'] ?? '' }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layout.partials.head-css')
    @stack('head')

</head>