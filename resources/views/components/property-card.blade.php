@props(['prop', 'col' => 'col-xl-4 col-md-6'])

@php
    $txType = strtolower($prop['transactionType'] ?? '');
    $status = ucfirst(strtolower($prop['status'] ?? ''));
    $ptKey = 'property.' . strtolower($prop['propertyType'] ?? '');
    $locale = app()->getLocale();
@endphp

<div class="{{ $col }} d-flex mb-4" data-slug="{{ $prop['slug'] }}" data-code="{{ $prop['code'] ?? '' }}"
    data-title="{{ $prop['title'] ?? '' }}" data-address="{{ $prop['fullAddress'] ?? $prop['city'] ?? '' }}">
    <div class="pc-card d-flex flex-column flex-fill"
        style="cursor:pointer;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);background:var(--white);border:1px solid var(--gray-100);"
        onclick="if(!event.target.closest('.favourite') && !event.target.closest('.compare-btn')){window.location.href='/{{ $locale }}/property/{{ $prop['slug'] }}'}">

        {{-- Image --}}
        <div class="position-relative" style="height:220px;overflow:hidden;flex-shrink:0;">
            <a href="/{{ $locale }}/property/{{ $prop['slug'] }}" style="display:block;height:100%;">
                @if(!empty($prop['primaryImageUrl']))
                    <img src="{{ $prop['primaryImageUrl'] }}" alt="{{ $prop['title'] ?? '' }}"
                        style="width:100%;height:220px;object-fit:cover;display:block;transition:transform 0.4s ease;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
                        onerror="this.style.display='none'">
                @else
                    <div style="height:220px;background:#e9ecef;display:flex;align-items:center;justify-content:center;">
                        <x-icon name="image" size="48" class="text-muted" />
                    </div>
                @endif
            </a>

            {{-- Top badges --}}
            <div class="d-flex align-items-center justify-content-between position-absolute top-0 start-0 end-0 p-3">
                <div class="d-none d-sm-flex align-items-center gap-2">
                    @if($txType === 'rentdaily')
                        <span class="badge badge-sm bg-danger d-flex align-items-center gap-1">
                            <x-icon name="bolt" size="13" />{{ __('index.popular_rent_daily') }}
                        </span>
                    @elseif(str_starts_with($txType, 'rent'))
                        <span class="badge badge-sm bg-info d-flex align-items-center gap-1">
                            <x-icon name="calendar_today" size="13" />{{ __('index.popular_rent_monthly') }}
                        </span>
                    @else
                        <span class="badge badge-sm bg-success d-flex align-items-center gap-1">
                            <x-icon name="sell" size="13" />{{ __('index.popular_for_sale') }}
                        </span>
                    @endif

                    @if($status === 'Draft')
                        <span class="badge badge-sm bg-secondary">{{ __('property.status_draft') }}</span>
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
                        style="background:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.15);">
                        <x-icon name="balance" size="17" style="color:#555" />
                    </a>
                    <a href="javascript:void(0)" class="favourite"
                        style="background:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.15);">
                        <x-icon name="favorite_border" size="17" />
                    </a>
                </div>
            </div>

            {{-- City badge bottom --}}
            <div class="position-absolute bottom-0 start-0 p-3">
                <span class="badge bg-light text-dark">{{ localized_city($prop['city'] ?? '') }}</span>
            </div>
        </div>

        {{-- Content --}}
        <div class="d-flex flex-column flex-fill p-3">

            {{-- Title + address + type --}}
            <div class="mb-3">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                    <h6 class="mb-0"
                        style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4;min-width:0;flex:1;">
                        <a href="/{{ $locale }}/property/{{ $prop['slug'] ?? '' }}"
                            class="text-dark text-decoration-none">{{ $prop['title'] ?? '' }}</a>
                    </h6>
                    <span
                        class="badge bg-secondary flex-shrink-0">{{ __($ptKey) !== $ptKey ? __($ptKey) : ($prop['propertyType'] ?? '') }}</span>
                </div>
                <p class="fs-14 mb-0 text-muted d-flex align-items-center"
                    style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <x-icon name="location_on" size="14" class="me-1" style="flex-shrink:0" />
                    <span style="overflow:hidden;text-overflow:ellipsis;">{{ localized_address($prop) }}</span>
                </p>
            </div>

            {{-- Price row --}}
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3"
                style="border-bottom:1px solid var(--gray-100);">
                <div class="d-flex align-items-center gap-1">
                    <x-icon name="visibility" size="17" class="text-muted" />
                    <span class="fs-14 text-muted">{{ $prop['viewCount'] ?? 0 }}</span>
                </div>
                <div class="text-end">
                    <div class="text-primary fw-semibold">
                        <x-price :amount="$prop['price'] ?? 0" :currency="$prop['currency'] ?? null" />

                    </div>
                </div>
            </div>

            {{-- Details — stick to bottom --}}
            <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0 mt-auto">
                @if(!empty($prop['bedrooms']))
                    <li class="d-flex align-items-center gap-1 fs-13 text-muted">
                        <x-icon name="bed" size="16" style="background:var(--gray-100);border-radius:4px;padding:2px" />
                        {{ $prop['bedrooms'] }} {{ __('property.bedroom') }}
                    </li>
                @endif
                @if(!empty($prop['bathrooms']))
                    <li class="d-flex align-items-center gap-1 fs-13 text-muted">
                        <x-icon name="bathtub" size="16" style="background:var(--gray-100);border-radius:4px;padding:2px" />
                        {{ $prop['bathrooms'] }} {{ __('property.bath') }}
                    </li>
                @endif
                @if(!empty($prop['areaTotal']))
                    <li class="d-flex align-items-center gap-1 fs-13 text-muted">
                        <x-icon name="straighten" size="16"
                            style="background:var(--gray-100);border-radius:4px;padding:2px" />
                        {{ $prop['areaTotal'] }} {{ __('property.sq_ft') }}
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>