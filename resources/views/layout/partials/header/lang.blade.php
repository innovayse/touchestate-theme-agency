{{-- Header language switcher. $menuEnd → right-align dropdown; $extraClass → extra wrapper class (mobile). --}}
@php($menuEnd = $menuEnd ?? false)
@php($extraClass = $extraClass ?? '')
<div class="dropdown topbar-lang{{ $extraClass }}">
    <a href="#" class="topbar-link btn btn-light" data-bs-toggle="dropdown">
        <img src="{{URL::asset('build/img/flags/' . ($flags[$currentLocale] ?? 'us.svg'))}}" alt="Language" height="20" width="20" style="border-radius:50%;object-fit:cover">
    </a>
    <div class="dropdown-menu{{ $menuEnd ? ' dropdown-menu-end' : '' }}">
        @foreach(['en' => ['us.svg', 'English'], 'ru' => ['ru.svg', 'Русский'], 'hy' => ['am.svg', 'Հայերեն']] as $lc => $meta)
        <a href="/{{ $lc }}{{ $path ? '/' . $path : '' }}" class="dropdown-item d-flex align-items-center{{ $currentLocale === $lc ? ' active' : '' }}">
            <img src="{{URL::asset('build/img/flags/' . $meta[0])}}" alt="" class="me-2" height="20" width="20" style="border-radius:50%;object-fit:cover"> <span class="align-middle flex-grow-1">{{ $meta[1] }}</span>@if($currentLocale === $lc)<x-icon name="check" class="ms-2" size="16"/>@endif
        </a>
        @endforeach
    </div>
</div>
