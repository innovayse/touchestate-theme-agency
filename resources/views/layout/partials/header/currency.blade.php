{{-- Header currency switcher. $menuEnd → right-align the dropdown (desktop bar). --}}
@php($menuEnd = $menuEnd ?? false)
<div class="dropdown topbar-currency">
    <a href="#" class="topbar-link btn btn-light" data-bs-toggle="dropdown">
        <span class="currency-symbol-trigger fw-semibold">{{ $currencySymbols[$currentCurrency] ?? $currentCurrency }}</span>
    </a>
    <div class="dropdown-menu{{ $menuEnd ? ' dropdown-menu-end' : '' }}">
        @foreach($supportedCurrencies as $cur)
        <a href="{{ url('/currency/' . $cur) }}" data-no-instant data-currency="{{ $cur }}" class="dropdown-item currency-switch d-flex align-items-center{{ $currentCurrency === $cur ? ' active' : '' }}">
            <span class="currency-symbol me-2">{{ $currencySymbols[$cur] ?? $cur }}</span> <span class="align-middle flex-grow-1">{{ $cur }}</span><x-icon name="check" class="ms-2 currency-check" size="16"/>
        </a>
        @endforeach
    </div>
</div>
