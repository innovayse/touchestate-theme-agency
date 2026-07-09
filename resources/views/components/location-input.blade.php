{{-- Location autocomplete input with Yandex Suggest --}}
@props([
    'name'        => 'search',
    'value'       => '',
    'placeholder' => '',
    'class'       => '',
])

<div x-data="locationInput({{ Js::from($value) }}, '{{ app()->getLocale() }}')"
     @click.outside="open = false"
     class="relative {{ $class }}">

    <input type="text"
           name="{{ $name }}"
           x-model="query"
           @input="fetch()"
           @keydown.escape="open = false"
           @keydown.enter.prevent="open = false"
           placeholder="{{ $placeholder }}"
           autocomplete="off"
           class="field pr-9">

    {{-- spinner / clear --}}
    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
        <svg x-show="loading" class="h-4 w-4 animate-spin text-brand-400"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/>
        </svg>
        <button x-show="!loading && query.length > 0" type="button"
                @click="query=''; results=[]; open=false"
                class="pointer-events-auto text-neutral-400 hover:text-ink">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- suggestions dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak
         class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-2xl border border-sand bg-white shadow-lg">
        <div class="py-1.5">
            <template x-for="(item, i) in results" :key="i">
                <button type="button"
                        @click="pick(item.name)"
                        class="flex w-full flex-col px-4 py-2.5 text-left transition hover:bg-brand-50">
                    <span class="text-sm font-medium text-ink" x-text="item.name"></span>
                    <span x-show="item.desc" class="text-xs text-neutral-400" x-text="item.desc"></span>
                </button>
            </template>
        </div>
    </div>
</div>
