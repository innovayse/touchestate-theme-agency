@props([
    'type',
    'label',
    'count' => 0,
    'images' => [],
    'fallback',
])

@php $locale = app()->getLocale(); @endphp

<div class="property-type-item h-100">
    <div class="property-img">
        <a href="/{{ $locale }}/property?propertyType={{ $type }}">
            @if(count($images) === 0)
                <img src="{{ URL::asset($fallback) }}" class="property-type-thumb" alt="{{ $label }}"
                     style="width:260px;height:260px;max-width:260px;min-width:260px;object-fit:cover;display:block;">
            @elseif(count($images) === 1)
                <img src="{{ $images[0] }}" class="property-type-thumb" alt="{{ $label }}"
                     style="width:260px;height:260px;max-width:260px;min-width:260px;object-fit:cover;display:block;">
            @else
                <div class="type-slideshow" style="width:260px;height:260px;position:relative;overflow:hidden;border-radius:5px;">
                    @foreach($images as $idx => $imgUrl)
                        <img src="{{ $imgUrl }}" alt="{{ $label }}"
                             style="width:260px;height:260px;object-fit:cover;position:absolute;top:0;left:0;opacity:{{ $idx === 0 ? '1' : '0' }};transition:opacity 0.8s ease;">
                    @endforeach
                </div>
            @endif
        </a>
        <a href="/{{ $locale }}/property?propertyType={{ $type }}" class="overlay-arrow">
            <i class="material-icons-outlined">north_east</i>
        </a>
    </div>
    <div class="text-center">
        <h5 class="mb-1">
            <a href="/{{ $locale }}/property?propertyType={{ $type }}">{{ $label }}</a>
        </h5>
        <p class="fs-14 mb-0">{{ $count }} {{ __('index.property_type_available') }}</p>
    </div>
</div>
