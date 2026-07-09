@if(!empty($similar))
@php $locale = app()->getLocale(); @endphp
<div id="similar-wrap" class="mt-12">
    <h2 class="font-display text-xl font-semibold text-ink mt-6 mb-4">{{ __('property.similar_properties') }}</h2>
    <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($similar as $prop)
            <div class="{{ $loop->index >= 4 ? 'hidden sm:block' : '' }}">
                <x-property-card :prop="$prop" />
            </div>
        @endforeach
    </div>
</div>
@endif
