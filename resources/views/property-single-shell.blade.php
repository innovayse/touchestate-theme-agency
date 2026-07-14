@extends('layout.app')
@section('title', '...')

@php $locale = app()->getLocale(); @endphp

@section('content')
{{-- Skeleton shell: shown while content loads async --}}
<div id="prop-shell-skeleton" class="animate-pulse">
<section class="bg-ink pt-28 pb-12">
    <div class="container-x text-center">
        <div class="mx-auto h-9 w-72 rounded-full bg-white/20"></div>
        <div class="mx-auto mt-3 h-4 w-48 rounded-full bg-white/10"></div>
    </div>
</section>
<div class="py-12">
    <div class="container-x">
        <div class="grid gap-10 lg:grid-cols-2">
            {{-- Image placeholder --}}
            <div class="h-80 rounded-2xl bg-sand lg:h-[480px]"></div>
            {{-- Details placeholder --}}
            <div class="space-y-4">
                <div class="h-8 w-3/4 rounded-full bg-sand"></div>
                <div class="h-5 w-1/2 rounded-full bg-sand/70"></div>
                <div class="h-10 w-2/5 rounded-full bg-brand-100 mt-4"></div>
                <div class="mt-6 space-y-2">
                    <div class="h-4 w-full rounded-full bg-sand/50"></div>
                    <div class="h-4 w-5/6 rounded-full bg-sand/50"></div>
                    <div class="h-4 w-4/6 rounded-full bg-sand/50"></div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-6">
                    @for($i = 0; $i < 6; $i++)
                    <div class="h-12 rounded-xl bg-sand/60"></div>
                    @endfor
                </div>
            </div>
        </div>
        {{-- Description placeholder --}}
        <div class="mt-10 space-y-3">
            <div class="h-6 w-1/3 rounded-full bg-sand"></div>
            <div class="h-4 w-full rounded-full bg-sand/50"></div>
            <div class="h-4 w-full rounded-full bg-sand/50"></div>
            <div class="h-4 w-4/5 rounded-full bg-sand/50"></div>
        </div>
    </div>
</div>
</div>{{-- /prop-shell-skeleton --}}

{{-- Real content: injected by JS --}}
<div id="prop-shell-content" style="display:none"></div>

<script>
(function () {
    function injectHtml(container, html) {
        container.innerHTML = html;
        // Re-execute scripts so Yandex Maps and other JS initializes correctly
        container.querySelectorAll('script').forEach(function (old) {
            var s = document.createElement('script');
            Array.from(old.attributes).forEach(function (a) { s.setAttribute(a.name, a.value); });
            s.textContent = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
    }

    var url = '{{ url('/'.$locale.'/property/'.$slug.'/content') }}';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) {
            if (!r.ok) { window.location.href = '{{ url('/'.$locale.'/error-404') }}'; return null; }
            return r.json();
        })
        .then(function (data) {
            if (!data) return;
            var skeleton = document.getElementById('prop-shell-skeleton');
            var content  = document.getElementById('prop-shell-content');
            if (!skeleton || !content) return;
            injectHtml(content, data.html || '');
            content.style.display = '';
            skeleton.style.display = 'none';
            if (data.title) {
                document.title = data.title + ' — ' + document.title.split(' — ').slice(-1)[0];
            }
            if (window.Alpine) Alpine.initTree(content);
        })
        .catch(function () {
            var skeleton = document.getElementById('prop-shell-skeleton');
            if (skeleton) skeleton.classList.remove('animate-pulse');
        });
})();
</script>
@endsection
