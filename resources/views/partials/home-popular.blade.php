@php $initTab = count($saleProperties) ? 'sale' : 'rent'; @endphp
<div x-data="{ tab: '{{ $initTab }}' }">
    <div class="mt-7 inline-flex rounded-full border border-sand bg-panel p-1 mx-auto block w-fit">
        <button @click="tab='sale'" :class="tab==='sale' ? 'bg-brand-600 text-white' : 'text-ink'" class="rounded-full px-6 py-2 text-sm font-semibold transition">{{ __('index.popular_for_sale') }}</button>
        <button @click="tab='rent'" :class="tab==='rent' ? 'bg-brand-600 text-white' : 'text-ink'" class="rounded-full px-6 py-2 text-sm font-semibold transition">{{ __('index.popular_for_rent') }}</button>
    </div>

    @if(count($saleProperties) || count($rentProperties))
        <div class="container-x mt-12">
            <div x-show="tab==='sale'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($saleProperties as $prop)
                    <x-property-card :prop="$prop" />
                @empty
                    <p class="col-span-full text-center text-neutral-500">{{ __('index.coming_soon') }}</p>
                @endforelse
            </div>
            <div x-show="tab==='rent'" x-cloak class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($rentProperties as $prop)
                    <x-property-card :prop="$prop" />
                @empty
                    <p class="col-span-full text-center text-neutral-500">{{ __('index.coming_soon') }}</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="container-x mt-12">
            <div class="rounded-3xl border border-dashed border-sand bg-panel py-20 text-center">
                <p class="font-display text-xl text-neutral-500">{{ __('index.coming_soon') }}</p>
                <p class="mt-1 text-sm text-neutral-400">{{ __('index.coming_soon_sub') }}</p>
            </div>
        </div>
    @endif

    <div class="mt-10 text-center">
        <a href="{{ url('/'.$locale.'/property') }}" class="btn-outline">{{ __('index.explore_all_listings') }}</a>
    </div>
</div>
