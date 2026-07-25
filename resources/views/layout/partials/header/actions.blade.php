{{-- Header favourites + compare buttons (shared by mobile drawer + desktop bar). --}}
<a href="/{{ $currentLocale }}/favorites" class="topbar-link btn btn-light header-fav-btn" title="{{ __('header.favorites') }}" style="position:relative">
    <x-icon name="favorite_border"/>
    <span class="fav-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#e53935;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;line-height:16px;text-align:center;border-radius:50%;padding:0 3px;pointer-events:none"></span>
</a>
<a href="/{{ $currentLocale }}/compare" class="topbar-link btn btn-light header-compare-btn" title="{{ __('header.compare') }}" style="position:relative">
    <x-icon name="balance" size="20"/>
    <span class="compare-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#1565c0;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;line-height:16px;text-align:center;border-radius:50%;padding:0 3px;pointer-events:none"></span>
</a>
