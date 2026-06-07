<div class="col-xl-4 col-md-6" data-slug="{{ $prop['slug'] }}">
    <div class="property-listing-item" style="cursor:pointer" onclick="if(!event.target.closest('.favourite')){window.location.href='/{{ app()->getLocale() }}/property/{{ $prop['slug'] }}'}">
        <div class="buy-grid-img">
            <a href="/{{ app()->getLocale() }}/property/{{ $prop['slug'] }}">
                @if($prop['primaryImageUrl'])
                <img class="img-fluid rounded" src="{{ $prop['primaryImageUrl'] }}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'img-placeholder\'>832 x 472</div>';" alt="{{ $prop['title'] }}">
                @else
                <div class="img-placeholder">832 x 472</div>
                @endif
            </a>
            <div class="d-flex align-items-center justify-content-between position-absolute top-0 start-0 end-0 p-3">
                <div class="d-flex align-items-center gap-2">
                    @php $txType = strtolower($prop['transactionType'] ?? ''); @endphp
                    @if($txType === 'rentdaily')
                    <span class="badge badge-sm bg-danger d-flex align-items-center">
                        <i class="material-icons-outlined">offline_bolt</i>{{ __('index.popular_rent_daily') }}
                    </span>
                    @elseif(str_starts_with($txType, 'rent'))
                    <span class="badge badge-sm bg-info d-flex align-items-center">
                        <i class="material-icons-outlined">calendar_month</i>{{ __('index.popular_rent_monthly') }}
                    </span>
                    @else
                    <span class="badge badge-sm bg-success d-flex align-items-center">
                        <i class="material-icons-outlined">sell</i>{{ __('index.popular_for_sale') }}
                    </span>
                    @endif
                    @php $status = ucfirst(strtolower($prop['status'] ?? '')); @endphp
                    @if($status === 'Draft')
                    <span class="badge badge-sm bg-secondary d-flex align-items-center">{{ __('property.status_draft') }}</span>
                    @elseif($status === 'Active')
                    <span class="badge badge-sm bg-success d-flex align-items-center">{{ __('property.status_active') }}</span>
                    @elseif($status === 'Sold')
                    <span class="badge badge-sm bg-dark d-flex align-items-center">{{ __('property.status_sold') }}</span>
                    @elseif($status === 'Rented')
                    <span class="badge badge-sm bg-dark d-flex align-items-center">{{ __('property.status_rented') }}</span>
                    @elseif($status === 'Reserved')
                    <span class="badge badge-sm d-flex align-items-center" style="background:#f59e0b">{{ __('property.status_reserved') }}</span>
                    @elseif($status === 'Inactive')
                    <span class="badge badge-sm bg-secondary d-flex align-items-center">{{ __('property.status_inactive') }}</span>
                    @endif
                </div>
                <a href="javascript:void(0)" class="favourite">
                    <i class="material-icons-outlined">favorite_border</i>
                </a>
            </div>
            <div class="d-flex align-items-center justify-content-between position-absolute bottom-0 end-0 start-0 p-3">
                <span class="badge bg-light text-dark">{{ $prop['city'] }}</span>
            </div>
        </div>
        <div class="buy-grid-content">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="title">
                        <a href="/{{ app()->getLocale() }}/property/{{ $prop['slug'] }}">{{ $prop['title'] }}</a>
                    </h6>
                    <p class="d-flex align-items-center fs-14 mb-0"><i class="material-icons-outlined me-1">location_on</i>{{ $prop['fullAddress'] ?? $prop['city'] }}</p>
                </div>
                @php $__ptKey = 'property.' . strtolower($prop['propertyType'] ?? ''); @endphp
                <span class="badge bg-secondary">{{ __($__ptKey) !== $__ptKey ? __($__ptKey) : ($prop['propertyType'] ?? '') }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="material-icons-outlined" style="color: var(--body-color)">visibility</i>
                    <span class="ms-1 fs-14">{{ $prop['viewCount'] ?? 0 }}</span>
                </div>
                <div class="d-flex align-items-center">
                    <span>{{ __('property.starts_from') }}</span>
                    <h6 class="text-primary mb-0 ms-1">
                        {{ number_format($prop['price'] ?? 0, 0) }} {{ $prop['currency'] ?? '' }}
                        @if($txType === 'rentdaily')
                            <span class="fs-12 fw-normal text-muted">{{ __('property-single.per_day') }}</span>
                        @elseif(str_starts_with($txType, 'rent'))
                            <span class="fs-12 fw-normal text-muted">{{ __('property-single.per_month') }}</span>
                        @endif
                    </h6>
                </div>
            </div>
            <ul class="d-flex buy-grid-details justify-content-between align-items-center flex-wrap gap-1">
                @if($prop['bedrooms'])
                <li class="d-flex align-items-center gap-1">
                    <i class="material-icons-outlined bg-light text-dark">bed</i>
                    {{ $prop['bedrooms'] }} {{ __('property.bedroom') }}
                </li>
                @endif
                @if($prop['bathrooms'])
                <li class="d-flex align-items-center gap-1">
                    <i class="material-icons-outlined bg-light text-dark">bathtub</i>
                    {{ $prop['bathrooms'] }} {{ __('property.bath') }}
                </li>
                @endif
                @if($prop['areaTotal'])
                <li class="d-flex align-items-center gap-1">
                    <i class="material-icons-outlined bg-light text-dark">straighten</i>
                    {{ $prop['areaTotal'] }} {{ __('property.sq_ft') }}
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
