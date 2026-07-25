@php
    $currentLocale = app()->getLocale();
    $flags = ['en' => 'us.svg', 'ru' => 'ru.svg', 'hy' => 'am.svg'];
    $names = ['en' => __('common.lang_english'), 'ru' => __('common.lang_russian'), 'hy' => __('common.lang_armenian')];
    $currentPath = request()->path();
    $path = preg_replace('/^(en|ru|hy)\//', '', $currentPath);
    if (in_array($path, ['en', 'ru', 'hy'])) {
        $path = '';
    }
    $isAuthPage = false;
    // Currency switcher
    $supportedCurrencies = config('currency.supported', []);
    $currencySymbols     = config('currency.symbols', []);
    $currentCurrency     = display_currency();
@endphp
<!-- Header Start -->
<header class="header fixed">
    <div class="container">

        <nav class="navbar navbar-expand-lg header-nav">
            <div class="navbar-header">
                <a href="{{url('/')}}" class="navbar-brand logo d-flex align-items-center gap-2">
                    @if(!empty($workspace['logoUrl']))
                        <img src="{{ $workspace['logoUrl'] }}" class="img-fluid" style="max-height:36px;width:auto" alt="{{ $workspace['name'] ?? 'Logo' }}">
                    @endif
                    <span class="fw-semibold fs-16">{{ !empty($workspace['name']) ? $workspace['name'] : 'TouchEstate' }}</span>
                </a>
                @if(!$isAuthPage)
                <div class="navbar-header-actions d-flex align-items-center d-lg-none">
                    <!-- Language switcher (mobile — next to burger) -->
                    @include('layout.partials.header.lang', ['menuEnd' => true, 'extraClass' => ' topbar-lang-mobile'])
                    <a id="mobile_btn" href="javascript:void(0);">
                        <x-icon name="menu"/>
                    </a>
                </div>
                @endif
            </div>
            @if(!$isAuthPage)
            <div class="main-menu-wrapper">

                <div class="menu-header">
                    <a href="{{url('/')}}" class="menu-logo d-flex align-items-center gap-2">
                        @if(!empty($workspace['logoUrl']))
                            <img src="{{ $workspace['logoUrl'] }}" class="img-fluid" style="max-height:36px;width:auto" alt="{{ $workspace['name'] ?? 'Logo' }}">
                        @endif
                        <span class="fw-semibold fs-16">{{ !empty($workspace['name']) ? $workspace['name'] : 'TouchEstate' }}</span>
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <x-icon name="close"/>
                    </a>
                </div>
                <div class="mobile-search">
                    <form action="/{{ $currentLocale }}/property" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-lg" placeholder="{{ __('header.search') }}" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary px-3" style="flex-shrink:0"><x-icon name="search" size="20" style="line-height:1"/></button>
                    </form>
                </div>

                <ul class="main-nav">
                    <li class="{{ Request::is('/', 'index', 'en', 'ru', 'hy') ? 'active' : '' }}">
                        <a href="{{url('/')}}">{{ __('header.home') }}</a>
                    </li>
                    <li class="{{ Request::is('property', '*/property', 'map', '*/map', '*/property/*') ? 'active' : '' }}">
                        <a href="{{'/'. $currentLocale .'/property'}}">{{ __('header.property') }}</a>
                    </li>
                </ul>

                <div class="menu-dropdown">
                    <!-- Currency switcher -->
                    @include('layout.partials.header.currency', ['menuEnd' => false])
                    @include('layout.partials.header.actions')
                    <a href="javascript:void(0);" class="topbar-link btn btn-light theme-toggle-single">
                        <x-icon name="wb_sunny"/>
                    </a>
                </div>

            </div>
            @endif

            <div class="nav header-items">

                @if(!$isAuthPage)
                @include('layout.partials.header.actions')
                @endif

                <!-- Currency switcher -->
                @include('layout.partials.header.currency', ['menuEnd' => true])

                @include('layout.partials.header.lang', ['menuEnd' => true])

                <a href="javascript:void(0);" class="topbar-link btn btn-light theme-toggle-single">
                    <x-icon name="wb_sunny"/>
                </a>


            </div>
        </nav>

    </div>
</header>
<!-- Header End -->
