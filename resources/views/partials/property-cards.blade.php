{{-- Property card list — shared by the initial grid and the "Load More" AJAX response. --}}
@foreach($properties['items'] ?? [] as $prop)
    <x-property-card :prop="$prop" />
@endforeach
