@props(['prop', 'col' => 'col-xl-4 col-md-6'])

@php
    $txType = strtolower($prop['transactionType'] ?? '');
    $status  = ucfirst(strtolower($prop['status'] ?? ''));
    $ptKey   = 'property.' . strtolower($prop['propertyType'] ?? '');
    $locale  = app()->getLocale();
@endphp

<div class="{{ $col }} d-flex mb-4" data-slug="{{ $prop['slug'] }}">
    <div class="pc-card d-flex flex-column flex-fill"
         style="cursor:pointer;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);background:var(--white);border:1px solid var(--gray-100);"
         onclick="if(!event.target.closest('.favourite') && !event.target.closest('.compare-btn')){window.location.href='/{{ $locale }}/property/{{ $prop['slug'] }}'}">

        {{-- Image --}}
        <div class="position-relative" style="height:220px;overflow:hidden;flex-shrink:0;">
            <a href="/{{ $locale }}/property/{{ $prop['slug'] }}" style="display:block;height:100%;">
                @if(!empty($prop['primaryImageUrl']))
                    <img src="{{ $prop['primaryImageUrl'] }}"
                         alt="{{ $prop['title'] }}"
                         style="width:100%;height:220px;object-fit:cover;display:block;transition:transform 0.4s ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'"
                         onerror="this.style.display='none'">
                @else
                    <div style="height:220px;background:#e9ecef;display:flex;align-items:center;justify-content:center;">
                        <i class="material-icons-outlined text-muted" style="font-size:48px">image_not_supported</i>
                    </div>
                @endif
            </a>

            {{-- Top badges --}}
            <div class="d-flex align-items-center justify-content-between position-absolute top-0 start-0 end-0 p-3">
                <div class="d-flex align-items-center gap-2">
                    @if($txType === 'rentdaily')
                        <span class="badge badge-sm bg-danger d-flex align-items-center gap-1">
                            <i class="material-icons-outlined" style="font-size:13px">offline_bolt</i>{{ __('index.popular_rent_daily') }}
                        </span>
                    @elseif(str_starts_with($txType, 'rent'))
                        <span class="badge badge-sm bg-info d-flex align-items-center gap-1">
                            <i class="material-icons-outlined" style="font-size:13px">calendar_month</i>{{ __('index.popular_rent_monthly') }}
                        </span>
                    @else
                        <span class="badge badge-sm bg-success d-flex align-items-center gap-1">
                            <i class="material-icons-outlined" style="font-size:13px">sell</i>{{ __('index.popular_for_sale') }}
                        </span>
                    @endif

                    @if($status === 'Draft')
                        <span class="badge badge-sm bg-secondary">{{ __('property.status_draft') }}</span>
                    @elseif($status === 'Active')
                        <span class="badge badge-sm bg-success">{{ __('property.status_active') }}</span>
                    @elseif($status === 'Sold')
                        <span class="badge badge-sm bg-dark">{{ __('property.status_sold') }}</span>
                    @elseif($status === 'Rented')
                        <span class="badge badge-sm bg-dark">{{ __('property.status_rented') }}</span>
                    @elseif($status === 'Reserved')
                        <span class="badge badge-sm" style="background:#f59e0b">{{ __('property.status_reserved') }}</span>
                    @elseif($status === 'Inactive')
                        <span class="badge badge-sm bg-secondary">{{ __('property.status_inactive') }}</span>
                    @endif
                </div>
                <div class="d-flex gap-1">
                    <a href="javascript:void(0)" class="compare-btn"
                       style="border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.15);">
                        <i class="material-icons-outlined" style="font-size:17px">balance</i>
                    </a>
                    <a href="javascript:void(0)" class="favourite"
                       style="background:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.15);">
                        <i class="material-icons-outlined" style="font-size:17px;color:#555">favorite_border</i>
                    </a>
                </div>
            </div>

            {{-- City badge bottom --}}
            <div class="position-absolute bottom-0 start-0 p-3">
                <span class="badge bg-light text-dark">{{ $prop['city'] ?? '' }}</span>
            </div>
        </div>

        {{-- Content --}}
        <div class="d-flex flex-column flex-fill p-3">

            {{-- Title + address + type --}}
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                <div style="min-width:0;flex:1;">
                    <h6 class="mb-1" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4">
                        <a href="/{{ $locale }}/property/{{ $prop['slug'] }}" class="text-dark text-decoration-none">{{ $prop['title'] }}</a>
                    </h6>
                    <p class="fs-14 mb-0 text-muted d-flex align-items-center" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <i class="material-icons-outlined me-1" style="font-size:14px;flex-shrink:0">location_on</i>
                        <span style="overflow:hidden;text-overflow:ellipsis;">{{ $prop['fullAddress'] ?? $prop['city'] ?? '' }}</span>
                    </p>
                </div>
                <span class="badge bg-secondary flex-shrink-0">{{ __($ptKey) !== $ptKey ? __($ptKey) : ($prop['propertyType'] ?? '') }}</span>
            </div>

            {{-- Price row --}}
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3" style="border-bottom:1px solid var(--gray-100);">
                <div class="d-flex align-items-center gap-1">
                    <i class="material-icons-outlined text-muted" style="font-size:17px">visibility</i>
                    <span class="fs-14 text-muted">{{ $prop['viewCount'] ?? 0 }}</span>
                </div>
                <div class="text-end">
                    <div class="fs-12 text-muted">{{ __('property.starts_from') }}</div>
                    <div class="text-primary fw-semibold">
                        {{ number_format($prop['price'] ?? 0, 0) }} {{ $prop['currency'] ?? '' }}
                        @if($txType === 'rentdaily')
                            <span class="fs-12 fw-normal text-muted">/{{ __('property-single.per_day') }}</span>
                        @elseif(str_starts_with($txType, 'rent'))
                            <span class="fs-12 fw-normal text-muted">/{{ __('property-single.per_month') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Details — stick to bottom --}}
            <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0 mt-auto">
                @if(!empty($prop['bedrooms']))
                    <li class="d-flex align-items-center gap-1 fs-13 text-muted">
                        <i class="material-icons-outlined" style="font-size:16px;background:var(--gray-100);border-radius:4px;padding:2px">bed</i>
                        {{ $prop['bedrooms'] }} {{ __('property.bedroom') }}
                    </li>
                @endif
                @if(!empty($prop['bathrooms']))
                    <li class="d-flex align-items-center gap-1 fs-13 text-muted">
                        <i class="material-icons-outlined" style="font-size:16px;background:var(--gray-100);border-radius:4px;padding:2px">bathtub</i>
                        {{ $prop['bathrooms'] }} {{ __('property.bath') }}
                    </li>
                @endif
                @if(!empty($prop['areaTotal']))
                    <li class="d-flex align-items-center gap-1 fs-13 text-muted">
                        <i class="material-icons-outlined" style="font-size:16px;background:var(--gray-100);border-radius:4px;padding:2px">straighten</i>
                        {{ $prop['areaTotal'] }} {{ __('property.sq_ft') }}
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
