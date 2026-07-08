@php
    $ptKey   = 'property.' . strtolower($prop['propertyType'] ?? '');
    $ptLabel = __($ptKey) !== $ptKey ? __($ptKey) : ($prop['propertyType'] ?? '');
    $dateObj = \Carbon\Carbon::parse($prop['createdAt']);
    $dateStr = $dateObj->format('d') . ' ' . __('property.' . strtolower($dateObj->format('M'))) . ' ' . $dateObj->format('Y');
@endphp
<div class="col-lg-6 col-md-6 d-flex" data-slug="{{ $prop['slug'] }}" data-city="{{ $prop['city'] ?? '' }}">
    <div class="property-card flex-fill">
        <div class="property-listing-item p-0 mb-0 shadow-none">
            <div class="buy-grid-img mb-0 rounded-0">
                <a href="/{{ app()->getLocale() }}/property/{{ $prop['slug'] }}">
                    @if($prop['primaryImageUrl'])
                    <img class="img-fluid" src="{{ $prop['primaryImageUrl'] }}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'img-placeholder\'>832 x 472</div>';" alt="{{ $prop['title'] }}">
                    @else
                    <div class="img-placeholder">832 x 472</div>
                    @endif
                </a>
                <div class="d-flex align-items-center justify-content-between position-absolute top-0 start-0 end-0 p-3 z-1">
                    <div class="d-flex align-items-center gap-2">
                        @php $__tx = strtolower($prop['transactionType'] ?? ''); @endphp
                        @if($__tx === 'rentdaily')
                        <div class="badge badge-sm bg-danger d-flex align-items-center">
                            <i class="material-icons-outlined">offline_bolt</i>{{ __('index.popular_rent_daily') }}
                        </div>
                        @elseif(str_starts_with($__tx, 'rent'))
                        <div class="badge badge-sm bg-info d-flex align-items-center">
                            <i class="material-icons-outlined">calendar_month</i>{{ __('index.popular_rent_monthly') }}
                        </div>
                        @else
                        <div class="badge badge-sm bg-success d-flex align-items-center">
                            <i class="material-icons-outlined">sell</i>{{ __('index.popular_for_sale') }}
                        </div>
                        @endif
                    </div>
                    <span class="favourite">
                        <i class="material-icons-outlined">favorite_border</i>
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between position-absolute bottom-0 end-0 start-0 p-3 z-1">
                    <h6 class="text-white mb-0"><x-price :amount="$prop['price'] ?? 0" :currency="$prop['currency'] ?? null" /></h6>
                </div>
            </div>
            <div class="buy-grid-content">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-secondary">{{ $ptLabel }}</span>
                    @if($prop['city'])
                    <span class="ms-1 fs-14 text-muted">{{ $prop['city'] }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="title mb-1">
                            <a href="/{{ app()->getLocale() }}/property/{{ $prop['slug'] }}">{{ $prop['title'] }}</a>
                        </h6>
                        <p class="d-flex align-items-center fs-14 mb-0"><i class="material-icons-outlined me-1 ms-0">location_on</i>{{ $prop['fullAddress'] ?? $prop['city'] }}</p>
                    </div>
                </div>
                @if($prop['bedrooms'] || $prop['bathrooms'] || $prop['areaTotal'])
                <ul class="d-flex buy-grid-details d-flex mb-3 bg-light rounded p-3 justify-content-between align-items-center flex-wrap gap-1">
                    @if($prop['bedrooms'])
                    <li class="d-flex align-items-center gap-1">
                        <i class="material-icons-outlined bg-white text-secondary">bed</i>
                        {{ $prop['bedrooms'] }} {{ __('map.bedroom') }}
                    </li>
                    @endif
                    @if($prop['bathrooms'])
                    <li class="d-flex align-items-center gap-1">
                        <i class="material-icons-outlined bg-white text-secondary">bathtub</i>
                        {{ $prop['bathrooms'] }} {{ __('map.bath') }}
                    </li>
                    @endif
                    @if($prop['areaTotal'])
                    <li class="d-flex align-items-center gap-1">
                        <i class="material-icons-outlined bg-white text-secondary">straighten</i>
                        {{ $prop['areaTotal'] }} {{ __('map.sq_ft') }}
                    </li>
                    @endif
                </ul>
                @endif
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                    <p class="fs-14 fw-medium text-dark mb-0">{{ __('map.listed_on') }} : <span class="fw-medium text-body"> {{ $dateStr }}</span></p>
                    <p class="fs-14 fw-medium text-dark mb-0">{{ __('map.category') }} : <span class="fw-medium text-body"> {{ $ptLabel }}</span></p>
                </div>
            </div>
        </div>
    </div>
</div>
