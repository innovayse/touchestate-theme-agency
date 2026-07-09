@php $locale = app()->getLocale(); @endphp
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($properties as $prop)
        <x-property-card :prop="$prop" />
    @endforeach
</div>
