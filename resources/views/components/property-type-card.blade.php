@props([
    'type',
    'label',
    'count' => 0,
    'images' => [],
    'fallback',
    'icon' => 'home',
])

@php $locale = app()->getLocale(); @endphp

<a href="/{{ $locale }}/property?propertyType={{ $type }}" class="pt-card" style="text-decoration:none;">
    <div class="pt-card-img">
        @if(count($images) === 0)
            <img src="{{ URL::asset($fallback) }}" alt="{{ $label }}">
        @elseif(count($images) === 1)
            <img src="{{ $images[0] }}" alt="{{ $label }}">
        @else
            <div class="type-slideshow" style="position:relative;width:100%;height:100%;">
                @foreach($images as $idx => $imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $label }}"
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:{{ $idx === 0 ? '1' : '0' }};transition:opacity 0.8s ease;">
                @endforeach
            </div>
        @endif
        <div class="pt-card-overlay"></div>
        <div class="pt-card-badge">
            <x-icon name="{{ $icon }}" size="20"/>
        </div>
        <div class="pt-card-arrow">
            <x-icon name="north_east" size="18"/>
        </div>
    </div>
    <div class="pt-card-body">
        <h5 class="pt-card-label">{{ $label }}</h5>
        <span class="pt-card-count">{{ $count }} {{ __('index.property_type_available') }}</span>
    </div>
</a>
