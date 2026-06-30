@props(['prop'])

@php
    $txType  = strtolower($prop['transactionType'] ?? '');
    $locale  = app()->getLocale();
    $slug    = $prop['slug'] ?? '';
    $imgUrl  = $prop['primaryImageUrl'] ?? null;
    $txBadge = match($txType) {
        'sale'      => __('compare.sale'),
        'rent'      => __('compare.rent'),
        'rentdaily' => __('compare.rent_daily'),
        default     => null,
    };
@endphp

<div class="compare-card">

    {{-- Image --}}
    <a href="/{{ $locale }}/property/{{ $slug }}" class="compare-card-img-wrap" tabindex="-1">
        @if($imgUrl)
            <img src="{{ $imgUrl }}"
                 alt="{{ $prop['title'] ?? '' }}"
                 class="compare-card-img"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="compare-card-img-placeholder" style="display:none">
                <i class="material-icons-outlined">image_not_supported</i>
            </div>
        @else
            <div class="compare-card-img-placeholder">
                <i class="material-icons-outlined">image_not_supported</i>
            </div>
        @endif
        @if($txBadge)
            <span class="compare-card-badge {{ $txType === 'sale' ? 'ccb-sale' : 'ccb-rent' }}">{{ $txBadge }}</span>
        @endif
    </a>

    {{-- Body --}}
    <div class="compare-card-body">
        <h6 class="compare-card-title">
            <a href="/{{ $locale }}/property/{{ $slug }}">{{ $prop['title'] ?? '' }}</a>
        </h6>

        <div class="compare-card-price">
            {{ number_format($prop['price'] ?? 0, 0) }} {{ $prop['currency'] ?? '' }}
            @if($txType === 'rentdaily')
                <span class="compare-card-price-period">/{{ __('property-single.per_day') }}</span>
            @elseif(str_starts_with($txType, 'rent'))
                <span class="compare-card-price-period">/{{ __('property-single.per_month') }}</span>
            @endif
        </div>

        <div class="compare-card-actions">
            <a href="/{{ $locale }}/property/{{ $slug }}" class="btn btn-sm btn-primary flex-fill">
                {{ __('property.view_details') }}
            </a>
            <button class="compare-remove btn btn-sm btn-outline-secondary flex-shrink-0"
                    data-slug="{{ $slug }}"
                    title="{{ __('compare.remove') }}">
                <i class="material-icons-outlined" style="font-size:16px;line-height:1.4">close</i>
            </button>
        </div>
    </div>

</div>
