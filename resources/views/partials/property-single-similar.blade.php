{{-- Similar properties — loaded async via /property/{slug}/extras (skeleton-first). --}}
@if(!empty($similar))
<h4 class="mt-5 mb-4 ps-2">{{ __('property-single.similar_properties') }}</h4>
<div class="row row-gap-4 custom-properties-items">
    @foreach($similar as $sim)
        <x-property-card :prop="$sim" />
    @endforeach
</div>
@endif
