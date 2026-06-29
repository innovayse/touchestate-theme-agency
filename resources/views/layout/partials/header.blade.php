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
                <a id="mobile_btn" href="javascript:void(0);">
                    <x-icon name="menu"/>
                </a>
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
                    <div class="dropdown">
                        <a href="#" class="topbar-link btn btn-light" data-bs-toggle="dropdown">
                            <img src="{{URL::asset('build/img/flags/' . ($flags[$currentLocale] ?? 'us.svg'))}}" alt="Language" height="20" width="20" style="border-radius:50%;object-fit:cover">
                        </a>
                        <div class="dropdown-menu">
                            <a href="/en{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === 'en' ? ' active' : '' }}">
                                <img src="{{URL::asset('build/img/flags/us.svg')}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">English</span>@if($currentLocale === 'en')<x-icon name="check" class="ms-2" size="16"/>@endif
                            </a>
                            <a href="/ru{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === 'ru' ? ' active' : '' }}">
                                <img src="{{URL::asset('build/img/flags/ru.svg')}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">Русский</span>@if($currentLocale === 'ru')<x-icon name="check" class="ms-2" size="16"/>@endif
                            </a>
                            <a href="/hy{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === 'hy' ? ' active' : '' }}">
                                <img src="{{URL::asset('build/img/flags/am.svg')}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">Հայերեն</span>@if($currentLocale === 'hy')<x-icon name="check" class="ms-2" size="16"/>@endif
                            </a>
                        </div>
                    </div>
                    <a href="/{{ $currentLocale }}/favorites" class="topbar-link btn btn-light" title="{{ __('header.favorites') }}" style="position:relative">
                        <x-icon name="favorite_border"/>
                        <span class="fav-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#e53935;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;line-height:16px;text-align:center;border-radius:50%;padding:0 3px;pointer-events:none"></span>
                    </a>
                    <a href="/{{ $currentLocale }}/compare" class="topbar-link btn btn-light header-compare-btn" title="{{ __('header.compare') }}" style="position:relative">
                        <x-icon name="balance" size="20"/>
                        <span class="compare-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#1565c0;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;line-height:16px;text-align:center;border-radius:50%;padding:0 3px;pointer-events:none"></span>
                    </a>
                    <a href="javascript:void(0);" class="topbar-link btn btn-light theme-toggle-single">
                        <x-icon name="wb_sunny"/>
                    </a>
                </div>

            </div>
            @endif

            <div class="nav header-items">

                @if(!$isAuthPage)
                <a href="/{{ $currentLocale }}/favorites" class="topbar-link btn btn-light header-fav-btn" title="{{ __('header.favorites') }}" style="position:relative">
                    <x-icon name="favorite_border"/>
                    <span class="fav-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#e53935;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;line-height:16px;text-align:center;border-radius:50%;padding:0 3px;pointer-events:none"></span>
                </a>
                <a href="/{{ $currentLocale }}/compare" class="topbar-link btn btn-light header-compare-btn" title="{{ __('header.compare') }}" style="position:relative">
                    <x-icon name="balance" size="20"/>
                    <span class="compare-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#1565c0;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;line-height:16px;text-align:center;border-radius:50%;padding:0 3px;pointer-events:none"></span>
                </a>
                @endif

                <div class="dropdown topbar-lang">
                    <a href="#" class="topbar-link btn btn-light" data-bs-toggle="dropdown">
                        <img src="{{URL::asset('build/img/flags/' . ($flags[$currentLocale] ?? 'us.svg'))}}" alt="Language" height="20" width="20" style="border-radius:50%;object-fit:cover">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="/en{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === 'en' ? ' active' : '' }}">
                            <img src="{{URL::asset('build/img/flags/us.svg')}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">English</span>@if($currentLocale === 'en')<x-icon name="check" class="ms-2" size="16"/>@endif
                        </a>
                        <a href="/ru{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === 'ru' ? ' active' : '' }}">
                            <img src="{{URL::asset('build/img/flags/ru.svg')}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">Русский</span>@if($currentLocale === 'ru')<x-icon name="check" class="ms-2" size="16"/>@endif
                        </a>
                        <a href="/hy{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === 'hy' ? ' active' : '' }}">
                            <img src="{{URL::asset('build/img/flags/am.svg')}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">Հայերեն</span>@if($currentLocale === 'hy')<x-icon name="check" class="ms-2" size="16"/>@endif
                        </a>
                    </div>
                </div>

                <a href="javascript:void(0);" class="topbar-link btn btn-light theme-toggle-single">
                    <x-icon name="wb_sunny"/>
                </a>


            </div>
        </nav>

    </div>
</header>
<!-- Header End -->
