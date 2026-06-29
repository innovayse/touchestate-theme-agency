@props([
    'name'  => '',
    'size'  => null,        // px or any css length
    'class' => '',
])
@php
    $style = $size ? 'width:' . (is_numeric($size) ? $size.'px' : $size) . ';height:' . (is_numeric($size) ? $size.'px' : $size) . ';' : '';
@endphp
<svg {{ $attributes->class(['i-icon', $class])->merge(['style' => $style, 'aria-hidden' => 'true', 'focusable' => 'false', 'fill' => 'currentColor']) }}><use href="{{ asset('img/icons.svg') }}#icon-{{ $name }}"/></svg>
