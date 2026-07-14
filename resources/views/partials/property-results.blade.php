@php
    $locale     = app()->getLocale();
    $items      = $properties['items'] ?? [];
    $total      = $properties['totalCount'] ?? count($items);
    $curPage    = (int) request('page', 1);
    $totalPages = $properties['totalPages'] ?? 1;
    // Base URL for pagination — always /property, not /property/results
    $basePropertyUrl = url('/'.$locale.'/property');
    $pageParams = request()->except(['page']);

    $sortOptions = [
        'viewCount_desc' => __('property.featured'),
        'price_asc'      => __('property.low_to_high'),
        'price_desc'     => __('property.high_to_low'),
        'createdAt_desc' => __('property.newest'),
        'areaTotal_desc' => __('property.largest'),
    ];
    if (request()->filled('sortByFull')) {
        $curSort = request('sortByFull');
    } elseif (request()->filled('sortBy')) {
        $curSort = request('sortBy') . '_' . request('sortOrder', 'desc');
    } else {
        $curSort = 'viewCount_desc';
    }
@endphp

{{-- Top bar --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <p class="text-sm text-neutral-500">
        {{ __('property.showing_result') }}
        <span class="font-semibold text-ink">{{ count($items) }}</span>
        {{ __('property.of') }}
        <span class="font-semibold text-ink">{{ $total }}</span>
        {{ __('property.properties') }}
    </p>
    <div class="flex items-center gap-3">
        <form id="sort-form" action="{{ url('/'.$locale.'/property') }}" method="GET">
            @foreach(request()->except(['sortBy','sortOrder','page']) as $k=>$v)
                @if(is_array($v))
                    @foreach($v as $vi)<input type="hidden" name="{{ $k }}[]" value="{{ $vi }}">@endforeach
                @else
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach
            <x-custom-select name="sortByFull"
                :selected="$curSort"
                :options="$sortOptions"
                :autosubmit="true"
                :placeholder="__('property.default')"
                class="w-full sm:w-auto sm:min-w-[220px]" />
        </form>
    </div>
</div>

@if(count($items))
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($items as $prop)
            <x-property-card :prop="$prop" />
        @endforeach
    </div>

    {{-- Pagination — links always point to /property, not /property/results --}}
    @if($totalPages > 1)
        @php
            $pageUrl = fn(int $p) => $basePropertyUrl . '?' . http_build_query(array_merge($pageParams, ['page' => $p]));
        @endphp
        <nav class="mt-10 flex flex-wrap items-center justify-center gap-2">
            @if($curPage > 1)
                <a href="{{ $pageUrl($curPage - 1) }}"
                   class="flex h-10 w-10 items-center justify-center rounded-full border border-sand bg-white text-sm text-ink transition hover:border-brand-500 hover:text-brand-600">
                    ‹
                </a>
            @endif
            @for($p = max(1, $curPage-3); $p <= min($totalPages, $curPage+3); $p++)
                <a href="{{ $pageUrl($p) }}"
                   class="flex h-10 w-10 items-center justify-center rounded-full border text-sm transition
                          {{ $p === $curPage ? 'border-brand-600 bg-brand-600 text-white' : 'border-sand bg-white text-ink hover:border-brand-500 hover:text-brand-600' }}">
                    {{ $p }}
                </a>
            @endfor
            @if($curPage < $totalPages)
                <a href="{{ $pageUrl($curPage + 1) }}"
                   class="flex h-10 w-10 items-center justify-center rounded-full border border-sand bg-white text-sm text-ink transition hover:border-brand-500 hover:text-brand-600">
                    ›
                </a>
            @endif
        </nav>
    @endif
@else
    <div class="rounded-3xl border border-dashed border-sand bg-panel py-24 text-center">
        <svg class="mx-auto mb-4 text-brand-200" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg>
        <p class="font-display text-xl text-neutral-500">{{ __('index.coming_soon') }}</p>
        <p class="mt-1 text-sm text-neutral-400">{{ __('index.coming_soon_sub') }}</p>
        <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline mt-6">{{ __('property.reset') }}</a>
    </div>
@endif
