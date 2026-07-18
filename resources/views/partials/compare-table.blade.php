@php
$rows = [
    ['label' => __('compare.price'),            'custom' => 'price',            'highlight' => 'price',           'icon' => 'payments'],
    ['label' => __('compare.transaction_type'), 'custom' => 'transaction_type',                                   'icon' => 'swap_horiz'],
    ['label' => __('compare.property_type'),    'custom' => 'property_type',                                      'icon' => 'home_work'],
    ['label' => __('compare.area'),             'key'    => 'areaTotal',         'highlight' => 'areaTotal',       'suffix' => ' м²', 'icon' => 'square_foot'],
    ['label' => __('compare.rooms'),            'key'    => 'rooms',             'highlight' => 'rooms',           'icon' => 'meeting_room'],
    ['label' => __('compare.bedrooms'),         'key'    => 'bedrooms',          'highlight' => 'bedrooms',        'icon' => 'bed'],
    ['label' => __('compare.bathrooms'),        'key'    => 'bathrooms',         'highlight' => 'bathrooms',       'icon' => 'bathtub'],
    ['label' => __('compare.floor'),            'custom' => 'floor',             'highlight' => 'floor',           'icon' => 'layers'],
    ['label' => __('compare.year_built'),       'key'    => 'yearBuilt',         'highlight' => 'yearBuilt',       'icon' => 'calendar_today'],
    ['label' => __('compare.renovation'),       'custom' => 'renovation',        'highlight' => 'renovationType',  'icon' => 'construction'],
    ['label' => __('compare.construction'),     'custom' => 'construction',      'highlight' => 'constructionType','icon' => 'domain'],
    ['label' => __('compare.address'),          'custom' => 'address',                                            'icon' => 'place'],
    ['label' => __('compare.district'),         'key'    => 'district',                                           'icon' => 'map'],
];

$highlights = $highlights ?? [];

$propRows = [];
foreach ($properties as $pi => $prop) {
    $txType = strtolower($prop['transactionType'] ?? '');
    $vals = [];
    foreach ($rows as $row) {
        $val = '—';
        if (!empty($row['custom'])) {
            switch ($row['custom']) {
                case 'price':
                    // js-price span (mirrors <x-price>) so JS reconverts on currency switch.
                    $np     = convert_price($prop['price'] ?? 0, $prop['currency'] ?? null);
                    $native = ($prop['currency'] ?? null) ?: display_currency();
                    $p = '<span class="js-price" data-amount="' . (float) ($prop['price'] ?? 0)
                       . '" data-currency="' . e($native) . '">'
                       . number_format($np['amount'], 0) . ' ' . e($np['currency']) . '</span>';
                    if ($txType === 'rentdaily')               $p .= ' /' . __('property-single.per_day');
                    elseif (str_starts_with($txType, 'rent')) $p .= ' /' . __('property-single.per_month');
                    $val = $p; break;
                case 'transaction_type':
                    $ttMap = ['sale' => __('compare.sale'), 'rent' => __('compare.rent'), 'rentdaily' => __('compare.rent_daily')];
                    $val = $ttMap[$txType] ?? ($prop['transactionType'] ?? '—'); break;
                case 'property_type':
                    $ptKey = 'property.' . strtolower($prop['propertyType'] ?? '');
                    $val = __($ptKey) !== $ptKey ? __($ptKey) : ($prop['propertyType'] ?? '—'); break;
                case 'floor':
                    $f = $prop['floor'] ?? null; $ft = $prop['floorsTotal'] ?? null;
                    $val = $f !== null ? ($ft ? $f . '/' . $ft : $f) : '—'; break;
                case 'renovation':
                    $rt = $prop['renovationType'] ?? null;
                    $rKey = 'compare.renovation_' . strtolower((string) $rt);
                    $val = $rt ? (__($rKey) !== $rKey ? __($rKey) : $rt) : '—'; break;
                case 'construction':
                    $ct = $prop['constructionType'] ?? null;
                    $cKey = 'compare.construction_' . strtolower((string) $ct);
                    $val = $ct ? (__($cKey) !== $cKey ? __($cKey) : $ct) : '—'; break;
                case 'address':
                    $val = $prop['fullAddress'] ?? '—'; break;
            }
        } elseif (!empty($row['key'])) {
            $v = $prop[$row['key']] ?? null;
            if ($v !== null && $v !== '') $val = $v . ($row['suffix'] ?? '');
        }
        $vals[] = $val;
    }
    $propRows[$pi] = $vals;
}
@endphp

{{-- ============================================================ --}}
{{-- Mobile: horizontal swipe cards (< md)                        --}}
{{-- ============================================================ --}}
<div class="compare-mobile-list d-md-none">
    @foreach($properties as $pi => $prop)
    @php
        $txType  = strtolower($prop['transactionType'] ?? '');
        $ptKey   = 'property.' . strtolower($prop['propertyType'] ?? '');
        $ptLabel = __($ptKey) !== $ptKey ? __($ptKey) : ($prop['propertyType'] ?? '');
        $txLabel = $propRows[$pi][1];
    @endphp
    <div class="compare-mobile-card">
        <div class="cmc-img-wrap">
            @if($prop['primaryImageUrl'] ?? null)
                <img src="{{ $prop['primaryImageUrl'] }}" alt="{{ $prop['title'] ?? '' }}" class="cmc-img">
            @else
                <div class="cmc-img-placeholder"><span class="material-icons-outlined">image_not_supported</span></div>
            @endif
            <span class="cmc-counter">{{ $pi + 1 }} / {{ count($properties) }}</span>
            @if($txLabel && $txLabel !== '—')<span class="cmc-tx-badge">{{ $txLabel }}</span>@endif
        </div>
        <div class="cmc-body">
            <a href="/{{ app()->getLocale() }}/property/{{ $prop['slug'] ?? '' }}" class="cmc-title">{{ $prop['title'] ?? '' }}</a>
            @if($ptLabel)<div class="cmc-tags"><span class="cmc-badge">{{ $ptLabel }}</span></div>@endif
            @php $priceBest = !empty($highlights['price']) && in_array($prop['slug'] ?? '', $highlights['price']); @endphp
            <div class="cmc-price{{ $priceBest ? ' cmc-best-value' : '' }}">
                {!! $propRows[$pi][0] !!}@if($priceBest)<span class="cmc-best-star">★</span>@endif
            </div>
            <div class="cmc-stats-grid">
                @foreach($rows as $ri => $row)
                @if($ri >= 3)
                @php
                    $isWide     = (($row['custom'] ?? '') === 'address') || (($row['key'] ?? '') === 'district');
                    $isBestStat = !empty($row['highlight']) && in_array($prop['slug'] ?? '', $highlights[$row['highlight']] ?? []);
                @endphp
                <div class="cmc-stat{{ $isWide ? ' cmc-stat-wide' : '' }}{{ $isBestStat ? ' cmc-stat-best' : '' }}">
                    <span class="cmc-stat-label">{{ $row['label'] }}</span>
                    <span class="cmc-stat-value">{{ $propRows[$pi][$ri] }}@if($isBestStat)<span class="cmc-best-star">★</span>@endif</span>
                </div>
                @endif
                @endforeach
            </div>
            <div class="cmc-actions">
                <a href="/{{ app()->getLocale() }}/property/{{ $prop['slug'] ?? '' }}" class="btn btn-primary w-100">{{ __('compare.open_property') }}</a>
                <button class="compare-remove btn btn-outline-secondary w-100 mt-2 btn-sm" data-slug="{{ $prop['slug'] ?? '' }}">{{ __('compare.remove') }}</button>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ============================================================ --}}
{{-- Desktop: comparison table (>= md)                            --}}
{{-- ============================================================ --}}
@php $tableWidth = 210 + count($properties) * 320; @endphp
<div class="compare-table-wrap d-none d-md-block">
    <table class="compare-table" style="table-layout:fixed;width:{{ $tableWidth }}px">
        <colgroup>
            <col style="width:210px">
            @foreach($properties as $_) <col style="width:320px"> @endforeach
        </colgroup>
        <thead>
            <tr>
                <th class="compare-label-th"></th>
                @foreach($properties as $prop)
                <th class="compare-prop-th"><x-compare-card :prop="$prop" /></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $ri => $row)
            <tr>
                <td class="compare-row-label">
                    <span class="crl-icon"><i class="material-icons-outlined">{{ $row['icon'] }}</i></span>
                    <span class="crl-text">{{ $row['label'] }}</span>
                </td>
                @foreach($properties as $pi => $prop)
                @php $isBest = !empty($row['highlight']) && in_array($prop['slug'] ?? '', $highlights[$row['highlight']] ?? []); @endphp
                <td class="compare-row-value{{ $isBest ? ' compare-best' : '' }}">
                    @if($isBest)
                        <span class="compare-best-pill">★ @if($ri === 0){!! $propRows[$pi][$ri] !!}@else{{ $propRows[$pi][$ri] }}@endif</span>
                    @else
                        @if($ri === 0){!! $propRows[$pi][$ri] !!}@else{{ $propRows[$pi][$ri] }}@endif
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
