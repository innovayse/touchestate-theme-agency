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

<form method="GET" action="{{ $filterAction }}" id="filterForm">
<div class="advanced-filter">

    <!-- Result + Search row -->
    <p class="filter-result mb-3">{{ __('map.result') }} <span class="result-value" id="result-loaded">{{ count($properties['items'] ?? []) }}</span> / <span class="result-value" id="result-total">{{ $properties['totalCount'] ?? 0 }}</span></p>

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
            <li><a href="/{{ app()->getLocale() }}/property" class="list-icon {{ ($filterPage ?? 'property') === 'property' ? 'active' : '' }}"><i class="material-icons">grid_view</i></a></li>
            <li><a href="/{{ app()->getLocale() }}/map" class="list-icon {{ ($filterPage ?? 'property') === 'map' ? 'active' : '' }}"><i class="material-icons-outlined">location_on</i></a></li>
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

        <!-- Section 3: Building Parameters -->
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

        <!-- Section 4: Features & Appliances -->
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

        <!-- Section 5: Boolean flags -->
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

<script>
    // ── On-load: resolve pre-filled city to English ──────────────────────────
    (function () {
        var h    = document.getElementById('cityHidden');
        var lang = '{{ app()->getLocale() }}';
        if (!h || !h.value || lang === 'en') return;
        var localCity = h.value;
        fetch('https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(localCity) + '&lang=en_US&results=1&highlight=0&v=9')
            .then(function (r) { return r.text(); })
            .then(function (body) {
                var m = body.trim().match(/suggest\.apply\(([\s\S]+)\)/);
                if (!m) return;
                var data = JSON.parse(m[1]);
                var first = (data.results || [])[0];
                if (first) h.value = (first.title || {}).text || localCity;
            })
            .catch(function () {});
    }());

    // ── City autocomplete ────────────────────────────────────────────────────
    (function () {
        var input    = document.getElementById('cityInput');
        var hidden   = document.getElementById('cityHidden');
        var list     = document.getElementById('citySuggestions');
        var clearBtn = document.getElementById('cityClearBtn');
        var spinner  = document.getElementById('citySpinner');
        if (!input || !list) return;

        document.body.appendChild(list);

        var timer = null;
        var lang  = '{{ app()->getLocale() }}';
        var shown = {};
        var districtAutoFilled = false;

        (function () {
            var d = document.querySelector('[name="district"]');
            if (d) d.addEventListener('input', function () { districtAutoFilled = false; });
        }());

        function parseYandex(body) {
            var m = (body || '').trim().match(/suggest\.apply\(([\s\S]+)\)/);
            if (!m) return [];
            try { var data = JSON.parse(m[1]); } catch (e) { return []; }
            return (data.results || []).map(function (item) {
                var title = (item.title || {}).text || '';
                var where = ((item.log_id || {}).where) || {};
                if (!title || title !== (where.title || '')) return null;
                var parts = (where.name || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
                var desc = parts.filter(function (p) { return p !== title; }).join(', ');
                return { name: title, desc: desc };
            }).filter(Boolean);
        }

        function makeLi(name, desc, enName) {
            var li = document.createElement('li');
            li.innerHTML = '<i class="material-icons-outlined city-item-icon">location_on</i>'
                + '<span class="city-item-text">'
                +   '<span class="city-item-name">' + name + '</span>'
                +   (desc ? '<span class="city-item-desc">' + desc + '</span>' : '')
                + '</span>';
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = name;
                var cityEn = enName || name;
                if (hidden) hidden.value = cityEn;
                if (typeof window.panMapToLocation === 'function') {
                    window.panMapToLocation(name);
                }
                if (clearBtn) clearBtn.style.display = 'none';
                if (spinner)  spinner.style.display  = 'block';
                list.style.display = 'none';
                list.innerHTML = '';
                shown = {};
                var districtEl = document.querySelector('[name="district"]');
                if (districtEl) districtEl.value = '';
                fetch('/api/central-district?city=' + encodeURIComponent(cityEn) + '&lang=' + encodeURIComponent(lang))
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
            return li;
        }

        function positionList() {
            var rect = input.getBoundingClientRect();
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
                list.appendChild(makeLi(it.name, it.desc, it.enName));
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
                var base  = 'https://suggest-maps.yandex.ru/suggest-geo?apikey={{ config('services.yandex.maps_key') }}&text=' + encodeURIComponent(q) + '&results=7&highlight=0&v=9';
                var pLocal = fetch(base + '&lang=' + yLang).then(function (r) { return r.text(); }).catch(function () { return ''; });
                var pEn    = lang === 'en' ? Promise.resolve(null) : fetch(base + '&lang=en_US').then(function (r) { return r.text(); }).catch(function () { return ''; });
                Promise.all([pLocal, pEn]).then(function (texts) {
                    var local = parseYandex(texts[0]);
                    var en    = texts[1] !== null ? parseYandex(texts[1]) : local;
                    var combined = local.map(function (it, i) {
                        return { name: it.name, desc: it.desc, enName: (en[i] || {}).name || it.name };
                    });
                    showSuggestions(combined);
                }).catch(function () { list.style.display = 'none'; });
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

    // ── Year built: auto-set yearBuiltTo to current year ────────────────────
    (function () {
        var from = document.getElementById('yearBuiltFrom');
        var to   = document.getElementById('yearBuiltTo');
        if (!from || !to) return;
        from.addEventListener('input', function () {
            to.value = from.value ? {{ date('Y') }} : '';
        });
    }());

    // ── Filter section accordion ────────────────────────────────────────────
    function toggleSection(btn) {
        var card = btn.closest('.fs-card');
        var body = card.querySelector('.fs-body');
        var arrow = btn.querySelector('.fs-arrow');
        var open = card.classList.toggle('open');
        body.style.display = open ? '' : 'none';
        arrow.textContent = open ? 'expand_less' : 'expand_more';
    }

    document.addEventListener('DOMContentLoaded', function () {

        // ── Filter panel toggle (persist state in localStorage) ─────────────
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

    });
</script>

<style>
/* ── Filter sections ──────────────────────────────────────────────── */
.fs-basic { margin-bottom: 5px; gap: 5px; }

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
