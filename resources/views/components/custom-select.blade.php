{{--
    Custom Select Component
    Props:
      name        — form field name
      options     — assoc array ['value' => 'Label', ...]
      selected    — currently selected value (string)
      placeholder — placeholder label
      class       — extra wrapper classes
      autosubmit  — if true, submits parent form on change
--}}
@props([
    'name',
    'options'     => [],
    'selected'    => '',
    'placeholder' => __('index.search_select'),
    'class'       => '',
    'style'       => '',
    'autosubmit'  => false,
])

@php
    $initLabel = $selected && isset($options[$selected]) ? $options[$selected] : null;
@endphp

<div x-data="{
        open: false,
        val: '{{ $selected }}',
        lbl: {{ $initLabel ? Js::from($initLabel) : 'null' }},
        options: {{ Js::from($options) }},
        placeholder: {{ Js::from($placeholder) }},
        choose(v) {
            this.val = v;
            this.lbl = v === '' ? null : (this.options[v] ?? v);
            this.open = false;
            @if($autosubmit)
            this.$el.closest('form').submit();
            @endif
        }
     }"
     @click.outside="open = false"
     class="relative {{ $class }}"
     @if($style) style="{{ $style }}" @endif>

    {{-- hidden native input for form submission --}}
    <input type="hidden" name="{{ $name }}" :value="val">

    {{-- trigger button --}}
    <button type="button"
            @click="open = !open"
            class="field flex w-full items-center justify-between gap-2 text-left">
        <span x-text="lbl ?? placeholder"
              :class="lbl ? 'text-ink' : 'text-neutral-400'"></span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2"
             class="shrink-0 text-brand-600 transition-transform duration-200"
             :class="open ? 'rotate-180' : ''">
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </button>

    {{-- dropdown panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         x-cloak
         class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-2xl border border-sand bg-white shadow-lg">
        <div class="x-custom-select-list max-h-60 overflow-y-auto py-1.5" style="scrollbar-width:thin;scrollbar-color:#e8c5b8 transparent">
            {{-- blank / placeholder option --}}
            <button type="button"
                    @click="choose('')"
                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm transition hover:bg-brand-50"
                    :class="val === '' ? 'text-brand-700 font-semibold' : 'text-neutral-400'">
                <span>{{ $placeholder }}</span>
                <svg x-show="val === ''" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     class="ml-auto shrink-0 text-brand-600">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </button>
            @foreach($options as $val => $lbl)
                <button type="button"
                        @click="choose('{{ $val }}')"
                        class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm transition hover:bg-brand-50"
                        :class="val === '{{ $val }}' ? 'text-brand-700 font-semibold bg-brand-50' : 'text-ink'">
                    <span>{{ $lbl }}</span>
                    <svg x-show="val === '{{ $val }}'" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         class="ml-auto shrink-0 text-brand-600">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </button>
            @endforeach
        </div>
    </div>
</div>
