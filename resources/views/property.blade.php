<?php $page = 'property'; ?>
@section('title')
    {{ __('property.title') }}
@endsection

@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">

        @component('components.breadcrumb')
            @slot('title')
                {{ __('property.title') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('property.title') }}
            @endslot
        @endcomponent

        <div class="content">
            <div class="container">

                <!-- Advanced Filter -->
                <form method="GET" action="/{{ app()->getLocale() }}/property" id="filterForm">
                <div class="advanced-filter">

                    <!-- Result + Search row -->
                    <p class="filter-result mb-3">{{ __('map.result') }} <span class="result-value" id="result-loaded">{{ count($properties['items'] ?? []) }}</span> / <span class="result-value" id="result-total">{{ $properties['totalCount'] ?? 0 }}</span></p>

                    @php
                        $filterCount = 0;
                        foreach(['propertyType','transactionType','city','district','currency','minPrice','maxPrice','minArea','maxArea','minRooms','maxRooms','minBedrooms','maxBedrooms','minBathrooms','maxBathrooms','minFloor','maxFloor','yearBuiltFrom','yearBuiltTo','minLandArea','maxLandArea','renovationType','constructionType','furnitureType','petsPolicy','childrenPolicy','balconyType','terraceType','code','search'] as $f) {
                            if(request($f) !== null && request($f) !== '') $filterCount++;
                        }
                        foreach(['amenities','features','appliances','utilities','heatingType','parkingType','windowView'] as $f) {
                            if(request($f)) $filterCount += count(request($f));
                        }
                        foreach(['isNewConstruction','isNegotiable','isFrontLine','noAgentCalls','isLongTermRental','isUninhabited','sunDirection'] as $f) {
                            if(request($f)) $filterCount++;
                        }
                    @endphp
                    <div class="filter-search-row">

                        {{-- Row 1: Search + Submit --}}
                        <div class="filter-search-input fsr-search">
                            <i class="material-icons-outlined">search</i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('map.search') }}" autocomplete="off">
                        </div>

                        <button type="submit" class="btn-filter-search fsr-submit">
                            <i class="material-icons-outlined">search</i>
                            {{ __('map.search') }}
                        </button>

                        {{-- Break: row2 starts here on tablet/mobile --}}
                        <div class="fsr-break"></div>

                        {{-- Row 2: Code + Submit (on mobile submit moves here) --}}
                        <div class="fsr-code-row">
                            <div class="filter-search-input fsr-code">
                                <i class="material-icons-outlined">tag</i>
                                <input type="text" name="code" value="{{ request('code') }}" placeholder="{{ __('map.search_code') }}">
                            </div>
                            <button type="submit" class="btn-filter-search fsr-submit-mobile">
                                <i class="material-icons-outlined">search</i>
                                {{ __('map.search') }}
                            </button>
                        </div>

                        {{-- Filter toggle --}}
                        <button type="button" class="btn-filter-toggle fsr-filter" id="advFilterToggle">
                            <i class="material-icons-outlined">filter_alt</i>
                            {{ __('map.filter') }}
                            @if($filterCount > 0)
                                <span class="filter-badge">{{ $filterCount }}</span>
                            @endif
                            <i class="material-icons-outlined arrow">expand_more</i>
                        </button>

                        {{-- Grid/Map switcher --}}
                        <ul class="grid-list-view d-flex align-items-center justify-content-center mb-0 fsr-views">
                            <li><a href="/{{ app()->getLocale() }}/property" class="list-icon active"><i class="material-icons">grid_view</i></a></li>
                            <li><a href="/{{ app()->getLocale() }}/map" class="list-icon"><i class="material-icons-outlined">location_on</i></a></li>
                        </ul>

                    </div>

                    <!-- Filter Panel -->
                    <div class="filter-panel" id="advFilterPanel">

                        <!-- Basic: Type + Location + Sort (always visible) -->
                        <div class="fs-basic">
                            <div class="row g-2">
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">{{ __('map.property_type') }}</label>
                                    <select name="propertyType" class="filter-select">
                                        <option value="">{{ __('map.any') }}</option>
                                        @foreach(['Apartment','House','Studio','Villa','Townhouse','Penthouse','Room','Complex','Land','Commercial','Office','Warehouse','Garage','Pavilion','EventVenue','Dacha','Cottage'] as $pt)
                                        <option value="{{ $pt }}" @selected(request('propertyType') === $pt)>{{ __('property.'.strtolower($pt)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">{{ __('map.transaction_type') }}</label>
                                    <select name="transactionType" class="filter-select">
                                        <option value="">{{ __('map.any') }}</option>
                                        <option value="Sale" @selected(request('transactionType') === 'Sale')>{{ __('map.sale') }}</option>
                                        <option value="Rent" @selected(request('transactionType') === 'Rent')>{{ __('map.rent_monthly') }}</option>
                                        <option value="RentDaily" @selected(request('transactionType') === 'RentDaily')>{{ __('map.rent_daily') }}</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6" style="position:relative">
                                    <label class="filter-label">{{ __('map.city') }}</label>
                                    <div style="position:relative">
                                        <input type="text" id="cityInput" value="{{ request('city') }}" placeholder="{{ __('map.enter_city') }}" class="filter-input" autocomplete="off">
                                        <input type="hidden" name="city" id="cityHidden" value="{{ request('city') }}">
                                        <button type="button" id="cityClearBtn" class="city-clear-btn"><i class="material-icons-outlined">close</i></button>
                                        <span id="citySpinner" class="city-loading-spinner"></span>
                                        <ul id="citySuggestions" class="city-suggestions"></ul>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">{{ __('map.district') }}</label>
                                    <input type="text" name="district" value="{{ request('district') }}" placeholder="{{ __('map.enter_district') }}" class="filter-input">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">{{ __('map.sort') }}</label>
                                    <select name="sortBy" class="filter-select">
                                        <option value="createdAt" @selected(request('sortBy','createdAt') === 'createdAt')>{{ __('map.sort_date') }}</option>
                                        <option value="price" @selected(request('sortBy') === 'price')>{{ __('map.sort_price') }}</option>
                                        <option value="area" @selected(request('sortBy') === 'area')>{{ __('map.sort_area') }}</option>
                                        <option value="rooms" @selected(request('sortBy') === 'rooms')>{{ __('map.sort_rooms') }}</option>
                                        <option value="viewCount" @selected(request('sortBy') === 'viewCount')>{{ __('map.sort_views') }}</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">{{ __('map.order') }}</label>
                                    <select name="sortOrder" class="filter-select">
                                        <option value="desc" @selected(request('sortOrder','desc') === 'desc')>{{ __('map.sort_desc') }}</option>
                                        <option value="asc" @selected(request('sortOrder') === 'asc')>{{ __('map.sort_asc') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 1: Price & Area -->
                        <div class="fs-card open" data-section="price">
                            <button type="button" class="fs-header" onclick="toggleSection(this)">
                                <span class="fs-icon" style="background:rgba(16,185,129,.12);color:#10b981"><i class="material-icons-outlined">attach_money</i></span>
                                <span class="fs-title">{{ __('map.price') }} &amp; {{ __('map.area') }}</span>
                                <i class="material-icons-outlined fs-arrow">expand_less</i>
                            </button>
                            <div class="fs-body">
                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.price_from') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="minPrice" value="{{ request('minPrice') }}" placeholder="0" min="0" step="1000">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.price_to') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="maxPrice" value="{{ request('maxPrice') }}" placeholder="∞" min="0" step="1000">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.area_from') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="minArea" value="{{ request('minArea') }}" placeholder="0" min="0" step="10">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.area_to') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="maxArea" value="{{ request('maxArea') }}" placeholder="∞" min="0" step="10">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.land_from') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="minLandArea" value="{{ request('minLandArea') }}" placeholder="0" min="0">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.land_to') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="maxLandArea" value="{{ request('maxLandArea') }}" placeholder="∞" min="0">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="filter-label">{{ __('map.currency') }}</label>
                                        <select name="currency" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            <option value="USD" @selected(request('currency') === 'USD')>USD ($)</option>
                                            <option value="AMD" @selected(request('currency') === 'AMD')>AMD (֏)</option>
                                            <option value="RUB" @selected(request('currency') === 'RUB')>RUB (₽)</option>
                                            <option value="EUR" @selected(request('currency') === 'EUR')>EUR (€)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Rooms & Bathrooms -->
                        <div class="fs-card" data-section="rooms">
                            <button type="button" class="fs-header" onclick="toggleSection(this)">
                                <span class="fs-icon" style="background:rgba(99,102,241,.12);color:#6366f1"><i class="material-icons-outlined">bed</i></span>
                                <span class="fs-title">{{ __('map.rooms') }} &amp; {{ __('map.bathrooms') }}</span>
                                <i class="material-icons-outlined fs-arrow">expand_more</i>
                            </button>
                            <div class="fs-body" style="display:none">
                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.rooms_from') }}</label>
                                        <select name="minRooms" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @for($i=1;$i<=10;$i++)<option value="{{ $i }}" @selected(request('minRooms') == $i)>{{ $i }}</option>@endfor
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.rooms_to') }}</label>
                                        <select name="maxRooms" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @for($i=1;$i<=10;$i++)<option value="{{ $i }}" @selected(request('maxRooms') == $i)>{{ $i }}</option>@endfor
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.bedrooms_from') }}</label>
                                        <select name="minBedrooms" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @for($i=1;$i<=10;$i++)<option value="{{ $i }}" @selected(request('minBedrooms') == $i)>{{ $i }}</option>@endfor
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.bedrooms_to') }}</label>
                                        <select name="maxBedrooms" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @for($i=1;$i<=10;$i++)<option value="{{ $i }}" @selected(request('maxBedrooms') == $i)>{{ $i }}</option>@endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.bathrooms_from') }}</label>
                                        <select name="minBathrooms" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @for($i=1;$i<=6;$i++)<option value="{{ $i }}" @selected(request('minBathrooms') == $i)>{{ $i }}</option>@endfor
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.bathrooms_to') }}</label>
                                        <select name="maxBathrooms" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @for($i=1;$i<=6;$i++)<option value="{{ $i }}" @selected(request('maxBathrooms') == $i)>{{ $i }}</option>@endfor
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.floor_from') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="minFloor" value="{{ request('minFloor') }}" placeholder="0" min="0">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.floor_to') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="maxFloor" value="{{ request('maxFloor') }}" placeholder="∞" min="0">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Building Parameters (collapsed by default) -->
                        <div class="fs-card" data-section="building">
                            <button type="button" class="fs-header" onclick="toggleSection(this)">
                                <span class="fs-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="material-icons-outlined">apartment</i></span>
                                <span class="fs-title">{{ __('map.construction_type') }}</span>
                                <i class="material-icons-outlined fs-arrow">expand_more</i>
                            </button>
                            <div class="fs-body" style="display:none">
                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.renovation_type') }}</label>
                                        <select name="renovationType" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @foreach(['Capital','Designer','Euro','Cosmetic','Partial','Old','Unrenovated'] as $v)
                                            <option value="{{ $v }}" @selected(request('renovationType') === $v)>{{ __('map.renovation_'.$v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.construction_type') }}</label>
                                        <select name="constructionType" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @foreach(['Wood','Strip','Brick','Monolithic','Panel','Stone'] as $v)
                                            <option value="{{ $v }}" @selected(request('constructionType') === $v)>{{ __('map.construction_'.$v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.furniture_type') }}</label>
                                        <select name="furnitureType" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @foreach(['Furnished','Partial','Unavailable','ByAgreement'] as $v)
                                            <option value="{{ $v }}" @selected(request('furnitureType') === $v)>{{ __('map.furniture_'.$v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.balcony_type') }}</label>
                                        <select name="balconyType" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @foreach(['Unavailable','Open','Closed'] as $v)
                                            <option value="{{ $v }}" @selected(request('balconyType') === $v)>{{ __('map.opentype_'.$v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.terrace_type') }}</label>
                                        <select name="terraceType" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            @foreach(['Unavailable','Open','Closed'] as $v)
                                            <option value="{{ $v }}" @selected(request('terraceType') === $v)>{{ __('map.opentype_'.$v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.pets_policy') }}</label>
                                        <select name="petsPolicy" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            <option value="Yes" @selected(request('petsPolicy') === 'Yes')>{{ __('map.policy_yes') }}</option>
                                            <option value="No" @selected(request('petsPolicy') === 'No')>{{ __('map.policy_no') }}</option>
                                            <option value="ByAgreement" @selected(request('petsPolicy') === 'ByAgreement')>{{ __('map.policy_by_agreement') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.children_policy') }}</label>
                                        <select name="childrenPolicy" class="filter-select">
                                            <option value="">{{ __('map.any') }}</option>
                                            <option value="Yes" @selected(request('childrenPolicy') === 'Yes')>{{ __('map.policy_yes') }}</option>
                                            <option value="No" @selected(request('childrenPolicy') === 'No')>{{ __('map.policy_no') }}</option>
                                            <option value="ByAgreement" @selected(request('childrenPolicy') === 'ByAgreement')>{{ __('map.policy_by_agreement') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="filter-label">{{ __('map.year_built') }}</label>
                                        <div class="filter-stepper">
                                            <input type="number" name="yearBuiltFrom" id="yearBuiltFrom" value="{{ request('yearBuiltFrom') }}" placeholder="1950" min="1800" max="{{ date('Y') }}" step="1">
                                            <div class="stepper-btns"><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepUp()">+</button><button type="button" onclick="this.closest('.filter-stepper').querySelector('input').stepDown()">−</button></div>
                                        </div>
                                        <input type="hidden" name="yearBuiltTo" id="yearBuiltTo" value="{{ request('yearBuiltTo') }}">
                                    </div>
                                </div>
                                <!-- Heating chip group -->
                                <p class="fs-sublabel">{{ __('map.heating_type') }}</p>
                                <div class="amenities-grid mb-3">
                                    @foreach([['Central','thermostat'],['Gas','local_fire_department'],['Electric','bolt'],['Autonomous','settings'],['Solar','wb_sunny'],['UnderfloorHeating','foundation']] as [$val, $icon])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="heatingType[]" value="{{ $val }}" @checked(in_array($val, request('heatingType', [])))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.heating_'.$val) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <!-- Parking chip group -->
                                <p class="fs-sublabel">{{ __('map.parking_type') }}</p>
                                <div class="amenities-grid mb-3">
                                    @foreach([['Open','local_parking'],['Covered','garage'],['Garage','directions_car'],['Barrier','fence']] as [$val, $icon])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="parkingType[]" value="{{ $val }}" @checked(in_array($val, request('parkingType', [])))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.parking_'.$val) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <!-- Window view chip group -->
                                <p class="fs-sublabel">{{ __('map.window_view') }}</p>
                                <div class="amenities-grid">
                                    @foreach([['Garden','yard'],['City','location_city'],['Street','add_road'],['Yard','home']] as [$val, $icon])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="windowView[]" value="{{ $val }}" @checked(in_array($val, request('windowView', [])))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.view_'.$val) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Features & Appliances (collapsed by default) -->
                        <div class="fs-card" data-section="features">
                            <button type="button" class="fs-header" onclick="toggleSection(this)">
                                <span class="fs-icon" style="background:rgba(236,72,153,.12);color:#ec4899"><i class="material-icons-outlined">star_outline</i></span>
                                <span class="fs-title">{{ __('map.features') }}</span>
                                <i class="material-icons-outlined fs-arrow">expand_more</i>
                            </button>
                            <div class="fs-body" style="display:none">
                                <p class="fs-sublabel">{{ __('map.features') }}</p>
                                <div class="amenities-grid mb-3">
                                    @foreach([['Elevator','elevator'],['Parking','local_parking'],['Balcony','balcony'],['Garage','garage'],['Pool','pool'],['Garden','yard'],['Basement','foundation'],['Gym','fitness_center'],['Security','security'],['PanoramicWindows','view_in_ar'],['Sauna','hot_tub'],['Fireplace','fireplace'],['Gazebo','cabin'],['BarbecueArea','outdoor_grill'],['SportsCourt','sports_esports'],['LoadingDock','local_shipping']] as [$val, $icon])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="features[]" value="{{ $val }}" @checked(in_array($val, request('features', [])))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.feature_'.strtolower($val)) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <p class="fs-sublabel">{{ __('map.appliances') }}</p>
                                <div class="amenities-grid mb-3">
                                    @foreach([['Washer','local_laundry_service'],['Dryer','dry_cleaning'],['Fridge','kitchen'],['Stove','outdoor_grill'],['Microwave','microwave'],['CoffeeMaker','free_breakfast'],['WaterHeater','water_drop'],['HairDryer','air'],['Iron','iron'],['Dishwasher','cleaning_services'],['VacuumCleaner','cleaning_services']] as [$val, $icon])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="appliances[]" value="{{ $val }}" @checked(in_array($val, request('appliances', [])))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.appliance_'.strtolower($val)) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <p class="fs-sublabel">{{ __('map.utilities') }}</p>
                                <div class="amenities-grid">
                                    @foreach([['Electricity','bolt'],['Water','water_drop'],['Gas','local_fire_department'],['Sewage','plumbing']] as [$val, $icon])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="utilities[]" value="{{ $val }}" @checked(in_array($val, request('utilities', [])))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.utility_'.strtolower($val)) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Additional / Boolean flags (collapsed by default) -->
                        <div class="fs-card" data-section="flags">
                            <button type="button" class="fs-header" onclick="toggleSection(this)">
                                <span class="fs-icon" style="background:rgba(99,102,241,.12);color:#6366f1"><i class="material-icons-outlined">tune</i></span>
                                <span class="fs-title">{{ __('map.flags') }}</span>
                                <i class="material-icons-outlined fs-arrow">expand_more</i>
                            </button>
                            <div class="fs-body" style="display:none">
                                <div class="amenities-grid">
                                    @foreach([
                                        ['isNewConstruction','apartment','new_construction'],
                                        ['isNegotiable','handshake','negotiable'],
                                        ['isFrontLine','waves','front_line'],
                                        ['noAgentCalls','phone_disabled','no_agent_calls'],
                                        ['isLongTermRental','calendar_month','long_term_rental'],
                                        ['isUninhabited','no_accounts','uninhabited'],
                                        ['sunDirection','wb_sunny','sun_direction'],
                                    ] as [$name, $icon, $key])
                                    <label class="amenity-item">
                                        <input type="checkbox" name="{{ $name }}" value="1" @checked(request($name))>
                                        <i class="material-icons-outlined">{{ $icon }}</i>
                                        <span>{{ __('map.'.$key) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>


                    </div><!-- end filter-panel -->

                    <!-- Sticky Actions Bar -->
                    <div class="fs-sticky-bar" id="fsStickyBar">
                        <button type="button" class="btn-reset" id="btnReset">{{ __('map.reset') }}</button>
                        <button type="submit" class="btn-apply">{{ __('map.apply_filter') }}</button>
                    </div>
                </div><!-- end advanced-filter -->
                </form>

                <!-- Skeleton Grid (shown while page loads) -->
                <div id="prop-grid-skeleton" class="row mb-4">
                    @for($i = 0; $i < 6; $i++)
                    <div class="col-lg-4 col-md-6 d-flex">
                        <div class="property-card flex-fill skeleton-card">
                            <div class="property-listing-item p-0 mb-0 shadow-none">
                                <div class="buy-grid-img mb-0 rounded-0" style="overflow:hidden">
                                    <span class="skeleton-block" style="width:100%;height:210px;border-radius:0"></span>
                                </div>
                                <div class="buy-grid-content">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="skeleton-block" style="width:80px;height:22px;border-radius:20px"></span>
                                        <span class="skeleton-block" style="width:55px;height:14px"></span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="skeleton-block mb-2" style="width:75%;height:20px"></span>
                                        <span class="skeleton-block" style="width:90%;height:14px"></span>
                                    </div>
                                    <span class="skeleton-block mb-3" style="width:100%;height:54px;border-radius:8px"></span>
                                    <div class="d-flex justify-content-between">
                                        <span class="skeleton-block" style="width:42%;height:13px"></span>
                                        <span class="skeleton-block" style="width:35%;height:13px"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- Property Grid (hidden until DOM ready) -->
                <div class="row mb-4" id="prop-grid" style="display:none;opacity:0;transition:opacity 0.35s ease">
                    @foreach($properties['items'] ?? [] as $prop)
                        <x-property-card :prop="$prop" />
                    @endforeach
                </div>

                <!-- Load More / Show Less -->
                <div class="text-center mb-4 d-flex align-items-center justify-content-center gap-3" id="grid-controls">
                    <button type="button" class="btn btn-dark d-inline-flex align-items-center gap-1" id="btnShowLess" style="display:none">
                        <i class="material-icons-outlined" style="font-size:18px">expand_less</i>
                        {{ __('property.show_less') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" id="btnLoadMore" style="display:none">
                        {{ __('property.load_more') }}
                        <i class="material-icons-outlined" style="font-size:18px">expand_more</i>
                    </button>
                </div>

            </div>
        </div>

    </div>

<script>
    function reinitLoadMore() {
        var grid = document.getElementById('prop-grid');
        var btnMore = document.getElementById('btnLoadMore');
        var btnLess = document.getElementById('btnShowLess');
        var resultLoaded = document.getElementById('result-loaded');
        if (!grid || !btnMore || !btnLess) return;

        // Remove old listeners by replacing buttons with clones
        var newBtnMore = btnMore.cloneNode(true);
        var newBtnLess = btnLess.cloneNode(true);
        btnMore.parentNode.replaceChild(newBtnMore, btnMore);
        btnLess.parentNode.replaceChild(newBtnLess, btnLess);
        btnMore = newBtnMore;
        btnLess = newBtnLess;

        var cards = grid.children;
        var INITIAL = 20;
        var STEP = 10;
        var visible = Math.min(INITIAL, cards.length);

        if (cards.length <= INITIAL) {
            btnMore.style.display = 'none';
            btnLess.style.display = 'none';
            if (resultLoaded) resultLoaded.textContent = cards.length;
            return;
        }

        function update() {
            for (var i = 0; i < cards.length; i++) {
                if (i < visible) {
                    cards[i].classList.remove('d-none');
                } else {
                    cards[i].classList.add('d-none');
                }
            }
            btnMore.style.display = visible < cards.length ? '' : 'none';
            btnLess.style.display = visible > INITIAL ? '' : 'none';
            if (resultLoaded) resultLoaded.textContent = visible;
        }

        btnMore.addEventListener('click', function () {
            var prevVisible = visible;
            visible = Math.min(visible + STEP, cards.length);
            for (var i = prevVisible; i < visible; i++) {
                cards[i].classList.remove('d-none');
                cards[i].style.opacity = '0';
                cards[i].style.transform = 'translateY(20px)';
                cards[i].style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                (function(el, delay) {
                    setTimeout(function() {
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }, delay);
                })(cards[i], (i - prevVisible) * 80);
            }
            btnMore.style.display = visible < cards.length ? '' : 'none';
            btnLess.style.display = visible > INITIAL ? '' : 'none';
            if (resultLoaded) resultLoaded.textContent = visible;
            if (cards[prevVisible]) setTimeout(function() { cards[prevVisible].scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        });

        btnLess.addEventListener('click', function () {
            visible = Math.max(visible - STEP, INITIAL);
            update();
            if (cards[visible - 1]) cards[visible - 1].scrollIntoView({ behavior: 'smooth', block: 'end' });
        });

        update();
    }

    document.addEventListener('DOMContentLoaded', function () {

        // ── Persist filters in sessionStorage ──────────────────────────────
        (function () {
            var form = document.getElementById('filterForm');
            if (!form) return;
            var key = 'propertyFilters';

            if (!window.location.search) {
                var saved = sessionStorage.getItem(key);
                if (saved) {
                    var params = new URLSearchParams(saved);
                    params.forEach(function (val, name) {
                        var el = form.querySelector('[name="' + name + '"]');
                        if (el) el.value = val;
                    });
                    // Update filter badge count
                    var count = 0;
                    params.forEach(function (val, name) {
                        if (val && name !== 'search' && name !== 'page') count++;
                    });
                    if (count > 0) {
                        var toggle = document.getElementById('advFilterToggle');
                        if (toggle && !toggle.querySelector('.filter-badge')) {
                            var badge = document.createElement('span');
                            badge.className = 'filter-badge';
                            badge.textContent = count;
                            toggle.insertBefore(badge, toggle.querySelector('.arrow'));
                        }
                    }
                }
            }

            function saveFilters() {
                var data = new URLSearchParams(new FormData(form)).toString();
                sessionStorage.setItem(key, '?' + data);
            }
            form.addEventListener('input', saveFilters);
            form.addEventListener('change', saveFilters);

            if (window.location.search) {
                sessionStorage.setItem(key, window.location.search);
            }
        }());

        // ── Custom select dropdowns (after session restore) ─────────────
        if (typeof initCustomSelects === 'function') initCustomSelects();

        // ── Reveal real grid, hide skeleton ────────────────────────────────
        var skeletonGrid = document.getElementById('prop-grid-skeleton');
        var realGrid     = document.getElementById('prop-grid');
        if (skeletonGrid) skeletonGrid.style.display = 'none';
        if (realGrid)    { realGrid.style.display = ''; requestAnimationFrame(function() { realGrid.style.opacity = '1'; }); }

        // Filter panel toggle (persist state in localStorage)
        var btn   = document.getElementById('advFilterToggle');
        var panel = document.getElementById('advFilterPanel');
        if (btn && panel) {
            function openPanel() {
                panel.style.overflow = 'hidden';
                panel.style.maxHeight = panel.scrollHeight + 'px';
                panel.style.opacity = '1';
                btn.classList.add('open');
                setTimeout(function () {
                    panel.classList.add('open');
                    panel.style.maxHeight = 'none';
                }, 250);
            }
            function closePanel() {
                panel.classList.remove('open');
                panel.style.overflow = 'hidden';
                panel.style.maxHeight = panel.scrollHeight + 'px';
                requestAnimationFrame(function () {
                    panel.style.maxHeight = '0';
                    panel.style.opacity = '0';
                });
                btn.classList.remove('open');
            }

            if (localStorage.getItem('filterOpen') === 'true') {
                panel.style.maxHeight = 'none';
                panel.style.opacity = '1';
                panel.classList.add('open');
                btn.classList.add('open');
            }

            btn.addEventListener('click', function () {
                var isOpen = panel.classList.contains('open');
                if (isOpen) {
                    closePanel();
                    localStorage.setItem('filterOpen', 'false');
                } else {
                    openPanel();
                    localStorage.setItem('filterOpen', 'true');
                }
            });
        }

        // Load More / Show Less
        reinitLoadMore();

        // ── AJAX filter submit ──────────────────────────────────────────────
        var filterFormEl = document.getElementById('filterForm');
        if (filterFormEl) {
            filterFormEl.addEventListener('submit', function (e) {
                e.preventDefault();
                var params = new URLSearchParams(new FormData(filterFormEl)).toString();
                var url = filterFormEl.action + '?' + params;

                var skeleton = document.getElementById('prop-grid-skeleton');
                var grid = document.getElementById('prop-grid');
                if (skeleton) skeleton.style.display = '';
                if (grid) { grid.style.display = 'none'; grid.style.opacity = '0'; }

                history.pushState(null, '', url);
                sessionStorage.setItem('propertyFilters', '?' + params);

                fetch(url)
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');

                        var newGrid = doc.getElementById('prop-grid');
                        if (grid && newGrid) grid.innerHTML = newGrid.innerHTML;

                        var newLoaded = doc.getElementById('result-loaded');
                        var newTotal = doc.getElementById('result-total');
                        if (newLoaded) document.getElementById('result-loaded').textContent = newLoaded.textContent;
                        if (newTotal) document.getElementById('result-total').textContent = newTotal.textContent;

                        if (skeleton) skeleton.style.display = 'none';
                        if (grid) {
                            grid.style.display = '';
                            requestAnimationFrame(function () { grid.style.opacity = '1'; });
                        }

                        reinitLoadMore();
                    })
                    .catch(function () {
                        filterFormEl.submit();
                    });
            });
        }

        // ── Reset button (no reload) ──────────────────────────────────────
        var btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                var form = document.getElementById('filterForm');
                if (!form) return;
                form.reset();
                form.querySelectorAll('input[type="text"], input[type="number"]').forEach(function (el) { el.value = ''; });
                form.querySelectorAll('input[type="checkbox"]').forEach(function (el) { el.checked = false; });
                form.querySelectorAll('select').forEach(function (el) { el.selectedIndex = 0; el.dispatchEvent(new Event('change', { bubbles: true })); });
                sessionStorage.removeItem('propertyFilters');
                var badge = document.querySelector('.filter-badge');
                if (badge) badge.remove();
                history.replaceState(null, '', window.location.pathname);
            });
        }
    });

    // ── City autocomplete ────────────────────────────────────────────────────
    (function () {
        var input    = document.getElementById('cityInput');
        var hidden   = document.getElementById('cityHidden');
        var list     = document.getElementById('citySuggestions');
        var clearBtn = document.getElementById('cityClearBtn');
        var spinner  = document.getElementById('citySpinner');
        if (!input || !list) return;

        // Portal: move list to <body> so no ancestor transform/overflow can misplace it
        document.body.appendChild(list);

        var timer = null;
        var lang  = '{{ app()->getLocale() }}';
        var shown = {};
        var districtAutoFilled = false; // true only when district was auto-filled by city selection

        // If user types in district manually — unmark auto-fill flag
        (function () {
            var d = document.querySelector('[name="district"]');
            if (d) d.addEventListener('input', function () { districtAutoFilled = false; });
        }());

        function resolveEnglishName(localName, cb) {
            if (lang === 'en') { cb(localName); return; }
            fetch('/api/suggest?q=' + encodeURIComponent(localName) + '&lang=en')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var first = (data.results || [])[0];
                    cb(first ? first.name : localName);
                })
                .catch(function () { cb(localName); });
        }

        function makeLi(name, desc) {
            var li = document.createElement('li');
            li.innerHTML = '<i class="material-icons-outlined city-item-icon">location_on</i>'
                + '<span class="city-item-text">'
                +   '<span class="city-item-name">' + name + '</span>'
                +   (desc ? '<span class="city-item-desc">' + desc + '</span>' : '')
                + '</span>';
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = name;
                if (hidden) hidden.value = name;
                if (clearBtn) clearBtn.style.display = 'none';
                if (spinner)  spinner.style.display  = 'block';
                list.style.display = 'none';
                list.innerHTML = '';
                shown = {};
                var districtEl = document.querySelector('[name="district"]');
                if (districtEl) districtEl.value = '';
                resolveEnglishName(name, function (enName) {
                    if (hidden) hidden.value = enName;
                    fetch('/api/central-district?city=' + encodeURIComponent(enName) + '&lang=' + encodeURIComponent(lang))
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.district && districtEl) {
                                districtEl.value = data.district;
                                districtAutoFilled = true;
                            }
                        })
                        .catch(function () {})
                        .finally(function () {
                            if (spinner)  spinner.style.display  = 'none';
                            if (clearBtn) clearBtn.style.display = 'flex';
                        });
                });
            });
            return li;
        }

        function positionList() {
            var rect = input.getBoundingClientRect();
            // absolute on body → add scroll offset so it tracks the input on scroll
            list.style.top   = (rect.bottom + window.scrollY) + 'px';
            list.style.left  = (rect.left   + window.scrollX) + 'px';
            list.style.width = rect.width + 'px';
        }

        function showSuggestions(items) {
            list.innerHTML = '';
            shown = {};
            if (!items.length) { list.style.display = 'none'; return; }
            positionList();
            items.forEach(function (it) {
                if (shown[it.name]) return;
                shown[it.name] = true;
                list.appendChild(makeLi(it.name, it.desc));
            });
            list.style.display = 'block';
        }


        function updateClearBtn() {
            if (clearBtn) clearBtn.style.display = input.value ? 'flex' : 'none';
        }

        input.addEventListener('input', function () {
            updateClearBtn();
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) { list.style.display = 'none'; list.innerHTML = ''; shown = {}; return; }

            timer = setTimeout(function () {
                var yLang = lang === 'en' ? 'en_US' : lang === 'hy' ? 'hy_AM' : 'ru_RU';
                fetch('https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&lang=' + yLang + '&results=7&highlight=0&v=9')
                    .then(function (r) { return r.text(); })
                    .then(function (body) {
                        var m = body.match(/^suggest\.apply\((.+)\)$/);
                        var data = m ? JSON.parse(m[1]) : {};
                        var results = (data.results || []).map(function (item) {
                            var title = (item.title || {}).text || '';
                            var where = ((item.log_id || {}).where) || {};
                            if (!title || title !== (where.title || '')) return null;
                            var parts = (where.name || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
                            var desc = parts.filter(function (p) { return p !== title; }).join(', ');
                            return { name: title, desc: desc };
                        }).filter(Boolean);
                        showSuggestions(results);
                    })
                    .catch(function () { list.style.display = 'none'; });
            }, 150);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                if (hidden) hidden.value = '';
                clearBtn.style.display = 'none';
                list.style.display = 'none';
                list.innerHTML = '';
                shown = {};
                if (districtAutoFilled) {
                    var districtEl = document.querySelector('[name="district"]');
                    if (districtEl) districtEl.value = '';
                    districtAutoFilled = false;
                }
                input.focus();
            });
        }

        input.addEventListener('blur', function () {
            setTimeout(function () { list.style.display = 'none'; }, 200);
        });

        updateClearBtn();
    }());
    // ── Year built: auto-set yearBuiltTo to current year ───────────────
    (function () {
        var from = document.getElementById('yearBuiltFrom');
        var to   = document.getElementById('yearBuiltTo');
        if (!from || !to) return;
        from.addEventListener('input', function () {
            to.value = from.value ? {{ date('Y') }} : '';
        });
    }());

    // ── Filter section accordion ────────────────────────────────────────
    function toggleSection(btn) {
        var card = btn.closest('.fs-card');
        var body = card.querySelector('.fs-body');
        var arrow = btn.querySelector('.fs-arrow');
        var open = card.classList.toggle('open');
        body.style.display = open ? '' : 'none';
        arrow.textContent = open ? 'expand_less' : 'expand_more';
    }

    // ── Sticky actions bar visibility ───────────────────────────────────
    (function () {
        var bar = document.getElementById('fsStickyBar');
        var panel = document.getElementById('advFilterPanel');
        if (!bar || !panel) return;
        function checkSticky() {
            var r = panel.getBoundingClientRect();
            bar.classList.toggle('fs-sticky-visible', r.bottom > window.innerHeight);
        }
        window.addEventListener('scroll', checkSticky, { passive: true });
        checkSticky();
    }());
</script>

<style>
/* ── Filter sections ──────────────────────────────────────────────── */
.fs-basic { margin-bottom: 5px; gap: 5px; }

/* Search row break helpers: hidden by default, shown per breakpoint via style.css */
.fsr-break { display: none; width: 100%; height: 0; }
.fs-card {
    border: 1px solid var(--bs-border-color, rgba(0,0,0,.1));
    border-radius: 10px;
    margin-bottom: 10px;
    background: var(--bs-body-bg, #fff);
}
.fs-card.open { border-color: rgba(16,185,129,.3); }
.fs-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    color: inherit;
}
.fs-header:hover { background: rgba(0,0,0,.03); }
.fs-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.fs-icon i { font-size: 18px; }
.fs-title { font-weight: 600; font-size: .9rem; flex: 1; }
.fs-arrow { margin-left: auto; font-size: 20px; opacity: .5; }
.fs-body { padding: 4px 16px 16px; }
.fs-sublabel {
    font-size: .75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    opacity: .5;
    margin: 12px 0 8px;
}

/* ── Sticky action bar ────────────────────────────────────────────── */
.fs-sticky-bar {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 12px 0 4px;
}
.fs-sticky-bar.fs-sticky-visible {
    position: sticky;
    bottom: 0;
    background: var(--bs-body-bg, #fff);
    border-top: 1px solid var(--bs-border-color, rgba(0,0,0,.1));
    padding: 12px 16px;
    margin: 0 -15px;
    z-index: 50;
    box-shadow: 0 -4px 16px rgba(0,0,0,.08);
}
</style>

@endsection
