@php
    $locale = app()->getLocale();
    $count = count($properties);
    $isHl = fn($slug, $key) => in_array($slug, $highlights[$key] ?? []);
    $txMap = ['Sale' => __('property.for_sale'), 'Rent' => __('property.rent_monthly'), 'RentDaily' => __('property.rent_daily')];

    $translateFeature = function (string $feat): string {
        $key = strtolower(str_replace([' ', '-'], '', $feat));
        foreach (['feature_', 'appliance_', 'utility_'] as $prefix) {
            $t = __('property.' . $prefix . $key);
            if ($t !== 'property.' . $prefix . $key)
                return $t;
        }
        return $feat;
    };

    foreach ($properties as &$prop) {
        if (!empty($prop['price']) && !empty($prop['areaTotal'])) {
            $prop['pricePerSqm'] = round((float) $prop['price'] / (float) $prop['areaTotal']);
        }
    }
    unset($prop);

    $formatVal = function ($p, $key) use ($txMap) {
        $val = $p[$key] ?? null;
        if ($val === null || $val === '')
            return null;
        return match ($key) {
            'transactionType' => $txMap[$val] ?? $val,
            'propertyType' => (($t = __('property.' . strtolower($val))) === 'property.' . strtolower($val)) ? $val : $t,
            'price' => number_format((float) $val) . ' ' . ($p['currency'] ?? ''),
            'pricePerSqm' => number_format((float) $val) . ' ' . ($p['currency'] ?? '') . '/м²',
            'areaTotal' => $val . ' ' . __('index.sq_ft'),
            'floor' => $val . (!empty($p['floorsTotal']) ? ' / ' . $p['floorsTotal'] : ''),
            'renovationType' => (($t = __('compare.renovation_' . strtolower($val))) === 'compare.renovation_' . strtolower($val)) ? $val : $t,
            'constructionType' => (($t = __('compare.construction_' . strtolower($val))) === 'compare.construction_' . strtolower($val)) ? $val : $t,
            'furnitureType' => (($t = __('compare.furniture_' . strtolower($val))) === 'compare.furniture_' . strtolower($val)) ? $val : $t,
            'status' => (($t = __('property.status_' . strtolower($val))) === 'property.status_' . strtolower($val)) ? $val : $t,
            default => $val,
        };
    };

    $isSame = function ($key) use ($properties, $formatVal) {
        $vals = array_filter(array_map(fn($p) => $formatVal($p, $key), $properties));
        if (count($vals) <= 1)
            return true;
        return count(array_unique(array_values($vals))) === 1;
    };

    $tab1Rows = [
        ['key' => 'transactionType', 'label' => __('compare.transaction_type')],
        ['key' => 'price', 'label' => __('compare.price')],
        ['key' => 'pricePerSqm', 'label' => __('compare.price_per_sqm')],
        ['key' => 'areaTotal', 'label' => __('compare.area')],
        ['key' => 'rooms', 'label' => __('compare.rooms')],
        ['key' => 'bedrooms', 'label' => __('compare.bedrooms')],
        ['key' => 'bathrooms', 'label' => __('compare.bathrooms')],
        ['key' => 'floor', 'label' => __('compare.floor')],
        ['key' => 'yearBuilt', 'label' => __('compare.year_built')],
        ['key' => 'renovationType', 'label' => __('compare.renovation')],
        ['key' => 'constructionType', 'label' => __('compare.construction')],
        ['key' => 'furnitureType', 'label' => __('property.furniture')],
        ['key' => 'status', 'label' => __('compare.status')],
        ['key' => 'viewsCount', 'label' => __('compare.views')],
    ];

    $tab2Rows = [
        ['key' => 'city', 'label' => __('compare.city')],
        ['key' => 'district', 'label' => __('compare.district')],
        ['key' => 'fullAddress', 'label' => __('compare.address')],
    ];

    $allFeatures = collect($properties)
        ->flatMap(fn($p) => array_merge((array) ($p['features'] ?? []), (array) ($p['appliances'] ?? []), (array) ($p['utilities'] ?? [])))
        ->unique()->filter()->sort()->values()->all();

    $tableWidth = 200 + $count * 280;
@endphp

<style>
    .cmp-tab-btn {
        border-bottom: 2px solid transparent;
        transition: color .15s, border-color .15s;
        color: var(--color-ink);
        font-weight: 600;
    }

    .cmp-tab-btn.active {
        color: var(--color-brand-600);
        border-bottom-color: var(--color-brand-600);
    }

    .cmp-tab-btn:hover {
        color: var(--color-brand-600);
    }

    .cmp-pane {
        display: none;
    }

    .cmp-pane.active {
        display: table-row-group;
    }

    .cmp-pane.active tr {
        animation: cmpFadeIn .2s ease;
    }

    @keyframes cmpFadeIn {
        from {
            opacity: 0;
            transform: translateY(4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .cmp-row:hover td {
        background-color: rgb(245 245 244 / 0.7);
    }

    .cmp-row:hover td.cmp-label {
        background-color: rgb(238 236 233);
    }

    .cmp-amenity-row:hover td {
        background-color: rgb(245 245 244 / 0.7);
    }

    .cmp-amenity-row:hover td.cmp-label {
        background-color: rgb(238 236 233);
    }

    .cmp-open-btn {
        border-color: var(--color-brand-600);
        color: var(--color-brand-700);
    }

    .cmp-open-btn:hover {
        background-color: var(--color-brand-600);
        color: #fff;
    }

    .cmp-remove-btn {
        border: 2px solid rgb(239 68 68 / 0.25);
        border-radius: 12px;
        color: rgb(239 68 68 / 0.75);
        transition: background-color .15s, color .15s, border-color .15s;
    }

    .cmp-remove-btn:hover {
        background-color: rgb(239 68 68);
        border-color: rgb(239 68 68);
        color: #fff;
    }

    #cmp-diffs-btn:hover {
        color: var(--color-brand-600);
        background-color: var(--color-brand-50);
    }
    #cmp-diffs-btn.is-active {
        color: var(--color-brand-700);
        background-color: var(--color-brand-50);
        border-color: var(--color-brand-200) !important;
    }
    #cmp-toast {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%) translateY(12px);
        background: rgba(30,30,30,.92);
        color: #fff;
        font-size: 13px;
        padding: 8px 18px;
        border-radius: 20px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .22s, transform .22s;
        z-index: 9000;
        white-space: nowrap;
        backdrop-filter: blur(6px);
    }
    #cmp-toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    @media (min-width: 1024px) { #cmp-toast { display: none; } }
    #cmp-diffs-label { display: none; }
    @media (min-width: 1024px) { #cmp-diffs-label { display: inline; } }

    /* ── Label column toggle ── */
    .cmp-lbl-text {
        display: block;
        transition: opacity 0.2s ease;
    }
    #cmp-wrap.labels-collapsed .cmp-lbl-text {
        opacity: 0;
        pointer-events: none;
    }
    .cmp-label {
        transition: width 0.3s cubic-bezier(.4,0,.2,1), padding 0.3s cubic-bezier(.4,0,.2,1);
    }
    #cmp-wrap.labels-collapsed .cmp-label {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }
    .cmp-toggle-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid var(--color-sand);
        background: white;
        color: var(--color-neutral-400, #a3a3a3);
        cursor: pointer;
        transition: background-color 0.15s, color 0.15s, border-color 0.15s;
        z-index: 11;
        flex-shrink: 0;
    }
    .cmp-toggle-btn:hover {
        background: var(--color-brand-50);
        color: var(--color-brand-600);
        border-color: var(--color-brand-300);
    }
    .cmp-toggle-arrow {
        transition: transform 0.3s ease;
    }
    #cmp-wrap.labels-collapsed .cmp-toggle-arrow {
        transform: rotate(180deg);
    }
</style>

{{-- ── Tab bar — sticky, outside scroll container ── --}}
<div class="sticky z-20 bg-white border-b border-sand shadow-sm rounded-t-2xl top-20 flex items-center gap-2 px-2">
    <div class="flex flex-1 overflow-x-auto scrollbar-none gap-1">
        <button onclick="cmpTab(0,this)"
            class="cmp-tab-btn active px-4 py-3 text-sm whitespace-nowrap">{{ __('compare.tab_main') }}</button>
        <button onclick="cmpTab(1,this)"
            class="cmp-tab-btn px-4 py-3 text-sm whitespace-nowrap">{{ __('compare.tab_location') }}</button>
        <button onclick="cmpTab(2,this)"
            class="cmp-tab-btn px-4 py-3 text-sm whitespace-nowrap">{{ __('compare.tab_amenities') }}</button>
    </div>
    <div id="cmp-toast"></div>
    <button id="cmp-diffs-btn" onclick="cmpToggleDiffs()"
        class="flex items-center gap-1.5 text-xs text-neutral-500 transition py-2 px-3 rounded-lg whitespace-nowrap shrink-0 ml-1 pl-3"
        style="border-left: 2px solid var(--color-sand)">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18" />
        </svg>
        <span id="cmp-diffs-label">{{ __('compare.show_diffs') }}</span>
    </button>
</div>

{{-- ── Table (horizontal scroll, sticky first column) ── --}}
<div class="overflow-x-auto scrollbar-brand">
    <table id="cmp-wrap" class="border-collapse" style="table-layout:fixed;width:{{ $tableWidth }}px">
        <colgroup>
            <col style="width:200px">
            @for($i = 0; $i < $count; $i++)
                <col style="width:280px">
            @endfor
        </colgroup>

        {{-- ── Property cards ── --}}
        <thead>
            <tr>
                <th
                    class="cmp-label sticky left-0 z-10 w-[200px] bg-neutral-50 border-b-2 border-r border-sand px-4 pt-5 pb-3 text-left align-top font-normal relative">
                    <button onclick="cmpToggleLabels()" class="cmp-toggle-btn" title="Скрыть/показать">
                        <svg class="cmp-toggle-arrow" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <span class="cmp-lbl-text text-[10px] font-bold uppercase tracking-widest text-neutral-400">{{ __('compare.comparing') }}</span>
                </th>
                @foreach($properties as $p)
                    @php
                        $slug = $p['slug'] ?? '';
                        $imgs = array_values(array_column($p['media'] ?? [], 'url'));
                        if (empty($imgs) && !empty($p['primaryImageUrl'])) $imgs = [$p['primaryImageUrl']];
                    @endphp
                    <td class="w-[280px] border-b-2 border-r border-sand p-2 align-top">
                        <button type="button"
                                onclick="cmpLightbox({{ json_encode($imgs) }}, 0)"
                                class="block h-[200px] w-full relative overflow-hidden rounded-[14px] cursor-zoom-in">
                            @if(!empty($p['primaryImageUrl']))
                                <img src="{{ $p['primaryImageUrl'] }}" alt="{{ $p['title'] ?? '' }}"
                                    class="absolute inset-0 w-full h-full object-cover transition duration-200 hover:scale-105">
                            @else
                                <div class="absolute inset-0 bg-sand grid place-items-center text-brand-300">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.4">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <path d="M3 15l5-5 4 4 3-3 6 6" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/[.72] via-black/10 to-transparent"></div>
                            <div class="absolute inset-0 flex flex-col justify-end p-[14px] pointer-events-none">
                                <h3 class="text-[0.8rem] font-semibold text-white leading-[1.3] line-clamp-2">{{ $p['title'] ?? '' }}</h3>
                            </div>
                        </button>
                    </td>
                @endforeach
            </tr>
        </thead>

        {{-- ── Tab 1: Main ── --}}
        <tbody class="cmp-pane active" id="cmp-pane-0">
            @foreach($tab1Rows as $row)
                @php
                    $hasAny = collect($properties)->some(fn($p) => !empty($p[$row['key']]));
                    $same = $isSame($row['key']);
                @endphp
                @if($hasAny)
                    <tr class="cmp-row border-b border-sand" data-same="{{ $same ? '1' : '0' }}">
                        <td
                            class="cmp-label sticky left-0 z-10 bg-neutral-50/90 px-4 py-3 text-sm font-medium text-neutral-500 border-b border-r border-sand">
                            <span class="cmp-lbl-text">{{ $row['label'] }}</span></td>
                        @foreach($properties as $p)
                                        @php
                                            $slug = $p['slug'] ?? '';
                                            $val = $formatVal($p, $row['key']);
                                            $hl = $isHl($slug, $row['key']);
                                        @endphp
                             <td
                                            class="px-3 py-3 text-center text-sm border-b border-r border-sand {{ $hl ? 'font-semibold text-green-800 bg-green-50' : 'text-[#2b2a26]' }}">
                                            @if($val)
                                                {{ $val }}@if($hl)<span class="ml-1 text-[11px] text-green-600">★</span>@endif
                                            @else
                                                <span class="text-neutral-300">—</span>
                                            @endif
                                        </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>

        {{-- ── Tab 2: Location ── --}}
        <tbody class="cmp-pane" id="cmp-pane-1">
            @foreach($tab2Rows as $row)
                @php
                    $hasAny = collect($properties)->some(fn($p) => !empty($p[$row['key']]));
                    $same = $isSame($row['key']);
                @endphp
                @if($hasAny)
                    <tr class="cmp-row" data-same="{{ $same ? '1' : '0' }}">
                        <td
                            class="cmp-label sticky left-0 z-10 bg-neutral-50/90 px-4 py-3 text-sm font-medium text-neutral-500 border-b border-r border-sand">
                            <span class="cmp-lbl-text">{{ $row['label'] }}</span></td>
                        @foreach($properties as $p)
                            @php $val = $formatVal($p, $row['key']); @endphp
                            <td class="px-3 py-3 text-center text-sm text-ink border-b border-r border-sand">
                                @if($val) {{ $val }} @else <span class="text-neutral-300">—</span> @endif
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>

        {{-- ── Tab 3: Amenities ── --}}
        <tbody class="cmp-pane" id="cmp-pane-2">
            @if(!empty($allFeatures))
                @foreach($allFeatures as $feat)
                    @php
                        $propFeatures = fn($p) => array_merge((array) ($p['features'] ?? []), (array) ($p['appliances'] ?? []), (array) ($p['utilities'] ?? []));
                        $featCount = collect($properties)->filter(fn($p) => in_array($feat, $propFeatures($p)))->count();
                        $isExclusive = $featCount === 1 && $count > 1;
                    @endphp
                    <tr class="cmp-amenity-row">
                        <td
                            class="cmp-label sticky left-0 z-10 bg-neutral-50/90 px-4 py-3 text-sm font-medium text-neutral-500 border-b border-r border-sand">
                            <span class="cmp-lbl-text">{{ $translateFeature($feat) }}</span></td>
                        @foreach($properties as $p)
                            @php $hasIt = in_array($feat, $propFeatures($p)); @endphp
                            <td
                                class="px-3 py-3 text-center border-b border-r border-sand {{ ($hasIt && $isExclusive) ? 'bg-green-50' : '' }}">
                                @if($hasIt)
                                    <span
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ $isExclusive ? 'bg-green-200 text-green-700' : 'bg-brand-100 text-brand-700' }}">
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="3">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </span>
                                    @if($isExclusive)<span class="ml-1 text-[11px] text-green-600">★</span>@endif
                                @else
                                    <span class="text-neutral-300">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ $count + 1 }}" class="py-12 text-center text-sm text-neutral-400">—</td>
                </tr>
            @endif
        </tbody>

        {{-- ── Bottom action row ── --}}
        <tfoot>
            <tr>
                <td class="cmp-label sticky left-0 z-10 bg-neutral-50/40 p-4 border-t-2 border-r border-sand"></td>
                @foreach($properties as $p)
                    @php $slug = $p['slug'] ?? ''; @endphp
                    <td class="p-3 border-t-2 border-r border-sand bg-neutral-50/40">
                        <div class="grid grid-cols-1 gap-2">
                            <a href="{{ url('/' . $locale . '/property/' . $slug) }}"
                                class="cmp-open-btn flex h-9 w-full items-center justify-center gap-1 rounded-xl border-2 bg-white px-2 text-xs font-semibold transition">
                                {{ __('compare.open_property') }}
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                    <polyline points="15 3 21 3 21 9" />
                                    <line x1="10" y1="14" x2="21" y2="3" />
                                </svg>
                            </a>
                            <button
                                onclick="window.dispatchEvent(new CustomEvent('te-remove-compare',{detail:{slug:'{{ $slug }}'}}))"
                                class="cmp-remove-btn flex h-9 w-full items-center justify-center gap-1 bg-white px-2 text-xs font-semibold">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    <path d="M10 11v6M14 11v6" />
                                </svg>
                                {{ __('compare.remove') }}
                            </button>
                        </div>
                    </td>
                @endforeach
            </tr>
        </tfoot>

    </table>
</div>

<script>
    (function () {
        var diffsOn = false;
        window.cmpTab = function (n, btn) {
            document.querySelectorAll('.cmp-tab-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            document.querySelectorAll('.cmp-pane').forEach(function (p, i) { p.classList.toggle('active', i === n); });
        };
        window.cmpToggleLabels = function () {
            var table = document.getElementById('cmp-wrap');
            var isCollapsing = !table.classList.contains('labels-collapsed');
            var col = table.querySelector('colgroup col');
            var cols = table.querySelectorAll('colgroup col');
            var propCount = cols.length - 1;
            if (isCollapsing) {
                table.classList.add('labels-collapsed');
                setTimeout(function () {
                    col.style.width = '36px';
                    table.style.width = (36 + propCount * 280) + 'px';
                }, 180);
            } else {
                col.style.width = '200px';
                table.style.width = (200 + propCount * 280) + 'px';
                setTimeout(function () {
                    table.classList.remove('labels-collapsed');
                }, 10);
            }
        };
        var _toastTimer = null;
        window.cmpToggleDiffs = function () {
            diffsOn = !diffsOn;
            document.querySelectorAll('#cmp-wrap .cmp-row[data-same="1"]').forEach(function (row) {
                row.style.display = diffsOn ? 'none' : '';
            });
            var txtOn  = '{{ __('compare.show_all') }}';
            var txtOff = '{{ __('compare.show_diffs') }}';
            var txt = diffsOn ? txtOn : txtOff;
            var l = document.getElementById('cmp-diffs-label');
            if (l) l.textContent = txt;
            var btn = document.getElementById('cmp-diffs-btn');
            if (btn) btn.classList.toggle('is-active', diffsOn);
            // Mobile toast (hidden on lg+)
            var toast = document.getElementById('cmp-toast');
            if (toast) {
                var toastMsg = diffsOn ? '{{ __('compare.toast_diffs_on') }}' : '{{ __('compare.toast_diffs_off') }}';
                toast.textContent = toastMsg;
                toast.classList.add('show');
                clearTimeout(_toastTimer);
                _toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 2200);
            }
        };
    })();

    // ── Image lightbox ──────────────────────────────────────────────────────
    window.cmpLightbox = function (urls, startIdx) {
        if (!urls || !urls.length) return;
        var _urls = urls, _idx = startIdx || 0;
        var isMobile = window.innerWidth <= 1024;

        var el = document.getElementById('cmp-lightbox');
        if (!el) {
            el = document.createElement('div');
            el.id = 'cmp-lightbox';
            el.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.92);display:flex;flex-direction:column;align-items:center;justify-content:center;';

            var btnStyle = 'color:#fff;background:rgba(255,255,255,.18);backdrop-filter:blur(8px);border:1.5px solid rgba(255,255,255,.3);border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;flex-shrink:0;';

            el.innerHTML =
                // close
                '<button id="cmp-lb-close" style="position:absolute;top:16px;right:16px;' + btnStyle + 'width:44px;height:44px;font-size:24px;z-index:2">×</button>' +
                // image wrapper (holds img + pinch zoom)
                '<div id="cmp-lb-wrap" style="flex:1;display:flex;align-items:center;justify-content:center;width:100%;overflow:hidden;padding:60px 0 52px;">' +
                  '<img id="cmp-lightbox-img" style="max-width:100%;max-height:100%;border-radius:10px;object-fit:contain;box-shadow:0 8px 48px rgba(0,0,0,.7);touch-action:none;transform-origin:center;" alt="">' +
                '</div>' +
                // bottom bar: prev · counter · next
                '<div id="cmp-lb-bar" style="position:absolute;bottom:16px;left:0;right:0;display:flex;align-items:center;justify-content:center;gap:16px;">' +
                  '<button id="cmp-lb-prev" style="' + btnStyle + 'width:44px;height:44px;font-size:26px;">‹</button>' +
                  '<div id="cmp-lb-counter" style="color:#fff;font-size:13px;background:rgba(0,0,0,.45);padding:4px 14px;border-radius:20px;min-width:54px;text-align:center;"></div>' +
                  '<button id="cmp-lb-next" style="' + btnStyle + 'width:44px;height:44px;font-size:26px;">›</button>' +
                '</div>';
            document.body.appendChild(el);
        }

        var img      = document.getElementById('cmp-lightbox-img');
        var btnPrev  = document.getElementById('cmp-lb-prev');
        var btnNext  = document.getElementById('cmp-lb-next');
        var counter  = document.getElementById('cmp-lb-counter');
        var btnClose = document.getElementById('cmp-lb-close');
        var bar      = document.getElementById('cmp-lb-bar');

        // Preload all images so switching is instant
        urls.forEach(function(u) { var i = new Image(); i.src = u; });

        // reset pinch scale
        img.style.transform = 'scale(1)';

        var render = function (initial) {
            var multi = _urls.length > 1;
            bar.style.display = multi ? 'flex' : 'none';
            if (multi) counter.textContent = (_idx + 1) + ' / ' + _urls.length;
            if (initial) {
                img.style.transition = 'none';
                img.style.opacity = '1';
                img.style.transform = 'scale(1)';
                img.src = _urls[_idx];
                return;
            }
            img.style.transition = 'opacity .18s ease';
            img.style.opacity = '0';
            setTimeout(function() {
                img.style.transform = 'scale(1)';
                img.src = _urls[_idx];
                img.style.opacity = '1';
            }, 150);
        };

        var close = function () {
            el.style.display = 'none';
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            document.removeEventListener('keydown', onKey);
        };

        var onKey = function (e) {
            if (e.key === 'Escape')      { close(); return; }
            if (e.key === 'ArrowLeft')   { _idx = (_idx - 1 + _urls.length) % _urls.length; render(false); }
            if (e.key === 'ArrowRight')  { _idx = (_idx + 1) % _urls.length; render(false); }
        };

        el.onclick      = function (e) { if (e.target === el || e.target.id === 'cmp-lb-wrap') close(); };
        btnClose.onclick = function (e) { e.stopPropagation(); close(); };
        btnPrev.onclick  = function (e) { e.stopPropagation(); _idx = (_idx - 1 + _urls.length) % _urls.length; render(false); };
        btnNext.onclick  = function (e) { e.stopPropagation(); _idx = (_idx + 1) % _urls.length; render(false); };

        [btnPrev, btnNext, btnClose].forEach(function(b) {
            b.onmouseover = function() { this.style.background = 'rgba(255,255,255,.32)'; };
            b.onmouseout  = function() { this.style.background = 'rgba(255,255,255,.18)'; };
        });

        // ── Touch: swipe to navigate + pinch to zoom ───────────────────────
        // Handlers on the wrap div (not img) so touch-action:none can work
        var wrap = document.getElementById('cmp-lb-wrap');
        wrap.style.touchAction = 'none'; // let JS handle all touches

        var _scale = 1, _pinchStart = 0, _swipeStartX = 0, _swipeStartY = 0, _swipeActive = false;

        wrap.ontouchstart = function (e) {
            _scale = _scale || 1;
            if (e.touches.length === 2) {
                _swipeActive = false;
                _pinchStart = Math.hypot(
                    e.touches[1].clientX - e.touches[0].clientX,
                    e.touches[1].clientY - e.touches[0].clientY
                );
                e.preventDefault();
            } else if (e.touches.length === 1 && _scale <= 1.05) {
                _swipeStartX = e.touches[0].clientX;
                _swipeStartY = e.touches[0].clientY;
                _swipeActive = true;
            }
        };
        wrap.ontouchmove = function (e) {
            if (e.touches.length === 2 && _pinchStart > 0) {
                var dist = Math.hypot(
                    e.touches[1].clientX - e.touches[0].clientX,
                    e.touches[1].clientY - e.touches[0].clientY
                );
                _scale = Math.min(4, Math.max(1, _scale * (dist / _pinchStart)));
                _pinchStart = dist;
                img.style.transition = 'none';
                img.style.transform = 'scale(' + _scale + ')';
                e.preventDefault();
            } else if (_swipeActive && e.touches.length === 1) {
                // prevent vertical scroll inside lightbox while swiping
                var dy = Math.abs(e.touches[0].clientY - _swipeStartY);
                var dx = Math.abs(e.touches[0].clientX - _swipeStartX);
                if (dx > dy) e.preventDefault();
            }
        };
        wrap.ontouchend = function (e) {
            if (_swipeActive && e.changedTouches.length === 1 && _scale <= 1.05) {
                var dx = e.changedTouches[0].clientX - _swipeStartX;
                if (Math.abs(dx) > 48) {
                    _idx = dx < 0
                        ? (_idx + 1) % _urls.length
                        : (_idx - 1 + _urls.length) % _urls.length;
                    render(false);
                }
            }
            if (e.touches.length < 2) {
                _pinchStart = 0;
                img.style.transition = 'opacity .18s ease, transform .2s ease';
                if (_scale < 1) { _scale = 1; img.style.transform = 'scale(1)'; }
            }
            _swipeActive = false;
        };

        _urls = urls; _idx = startIdx || 0;
        render(true);
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        document.addEventListener('keydown', onKey);
    };
</script>