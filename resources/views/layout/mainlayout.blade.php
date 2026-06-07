<!DOCTYPE html>
<html lang="en">

@include('layout.partials.title-meta')

<body>

    @if (!Route::is(['signup', 'signin', 'forgot-password', 'reset-password', 'coming-soon']))
    <!-- Start Main Wrapper -->
    <div class="main-wrapper">
    @endif

    @if (Route::is(['signup', 'signin', 'forgot-password', 'reset-password', 'coming-soon']))
    <!-- Start Main Wrapper -->
	<div class="main-wrapper auth-cover">
    @endif

    @if (!Route::is(['error-404', 'error-500', 'maintenance', 'coming-soon']))
        @include('layout.partials.header')
    @endif

        @yield('content')

    @if (!Route::is(['error-404', 'error-500', 'maintenance', 'coming-soon']))
        @include('layout.partials.footer')

        @if (!Route::is(['signup', 'signin', 'forgot-password', 'reset-password']))
        @component('components.modal-popup')
        @endcomponent
        @endif
    @endif

    </div>
    <!-- End Main Wrapper -->

    @include('layout.partials.footer-scripts')

</body>

</html>