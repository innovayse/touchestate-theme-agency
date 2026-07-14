@if(array_sum($typeCounts) > 0)
<div class="container-x mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @php $shownTypes = 0; @endphp
    @foreach($typeCounts as $type => $cnt)
        @continue($cnt === 0)
        @break($shownTypes >= 4)
        @php $shownTypes++; $k='property.'.strtolower($type); $lbl=__($k); if($lbl===$k){$lbl=$type;} $img=$typeImages[$type][0] ?? null; @endphp
        <a href="{{ url('/'.$locale.'/property?propertyType='.$type) }}" class="group relative overflow-hidden rounded-2xl border border-sand bg-white">
            <div class="h-44 overflow-hidden bg-sand">
                @if($img)<img src="{{ $img }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">@endif
            </div>
            <div class="p-5">
                <h3 class="font-display text-lg font-semibold text-ink">{{ $lbl }}</h3>
                <p class="text-sm text-neutral-500">{{ $cnt }} {{ __('index.property_type_available') }}</p>
            </div>
        </a>
    @endforeach
</div>
<div class="container-x mt-8 text-center">
    <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline">
        {{ __('index.property_type_view_more') }}
    </a>
</div>
@endif
