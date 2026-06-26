{{--
    Property media beyond photos: Video / Panorama(360) / FloorPlan / Document.
    Driven by $property['media'][] where type ∈ Photo|Video|Panorama|FloorPlan|Document.
    Every block is defensive: renders nothing when its type is absent, so the page is
    unaffected until such media is added in TouchEstate and arrives via the API.
    Format is auto-detected per URL (uploaded file / YouTube|Vimeo / external link / image).
--}}
@php
    $__byType = fn ($t) => collect($property['media'] ?? [])
        ->filter(fn ($m) => strcasecmp($m['type'] ?? '', $t) === 0 && !empty(trim($m['url'] ?? '')))
        ->values();

    $__videos     = $__byType('Video');
    $__panoramas  = $__byType('Panorama');
    $__floorplans = $__byType('FloorPlan');
    $__documents  = $__byType('Document');

    // Classify a URL → embed (YouTube/Vimeo) | videofile | image | iframe (external)
    $__kind = function (string $url): string {
        $u = strtolower($url);
        if (preg_match('~youtube\.com|youtu\.be|vimeo\.com~', $u))            return 'embed';
        if (preg_match('~\.(mp4|webm|ogv|ogg|mov|m4v)(\?|\#|$)~', $u))         return 'videofile';
        if (preg_match('~\.(jpe?g|png|webp|gif|avif)(\?|\#|$)~', $u))          return 'image';
        return 'iframe';
    };
    $__embed = function (string $url): string {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $url, $m)) return 'https://www.youtube.com/embed/' . $m[1];
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m))                                          return 'https://player.vimeo.com/video/' . $m[1];
        return $url;
    };
    $__hasPanoImage = $__panoramas->contains(fn ($p) => $__kind($p['url']) === 'image');
@endphp

{{-- ── Video ─────────────────────────────────────────────── --}}
@if($__videos->isNotEmpty())
<div class="accordion-item">
    <div class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-video" aria-expanded="true">
            {{ __('property-single.video') }}
        </button>
    </div>
    <div id="accordion-video" class="accordion-collapse collapse show">
        <div class="accordion-body">
            <div class="row row-gap-3">
                @foreach($__videos as $v)
                @php $k = $__kind($v['url']); @endphp
                <div class="col-12 {{ $__videos->count() > 1 ? 'col-md-6' : '' }}">
                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark">
                        @if($k === 'videofile')
                            <video controls preload="metadata" playsinline @if(!empty($v['thumbnailUrl'])) poster="{{ $v['thumbnailUrl'] }}" @endif>
                                <source src="{{ $v['url'] }}">
                            </video>
                        @else
                            <iframe src="{{ $__embed($v['url']) }}" title="{{ __('property-single.video') }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Panorama / 360 ─────────────────────────────────────── --}}
@if($__panoramas->isNotEmpty())
<div class="accordion-item">
    <div class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-pano" aria-expanded="true">
            {{ __('property-single.panorama_360') }}
        </button>
    </div>
    <div id="accordion-pano" class="accordion-collapse collapse show">
        <div class="accordion-body">
            @foreach($__panoramas as $p)
            @php $k = $__kind($p['url']); @endphp
            <div class="mb-3">
                @if($k === 'image')
                    {{-- Equirectangular 360 photo → Pannellum viewer --}}
                    <div class="ps-pano" data-pano-url="{{ $p['url'] }}" style="width:100%;height:440px;border-radius:10px;overflow:hidden"></div>
                @elseif($k === 'videofile')
                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark"><video controls preload="metadata" playsinline><source src="{{ $p['url'] }}"></video></div>
                @else
                    {{-- External 360 tour (Matterport/Kuula/…) --}}
                    <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $__embed($p['url']) }}" title="{{ __('property-single.panorama_360') }}" allowfullscreen loading="lazy" allow="xr-spatial-tracking; gyroscope; accelerometer"></iframe></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Floor plan ─────────────────────────────────────────── --}}
@if($__floorplans->isNotEmpty())
<div class="accordion-item">
    <div class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-floorplan" aria-expanded="true">
            {{ __('property-single.floor_plan') }}
        </button>
    </div>
    <div id="accordion-floorplan" class="accordion-collapse collapse show">
        <div class="accordion-body">
            <div class="row row-gap-3">
                @foreach($__floorplans as $fp)
                <div class="col-md-6">
                    <a href="{{ $fp['url'] }}" data-fancybox="floorplan" data-caption="{{ __('property-single.floor_plan') }}" class="d-block rounded overflow-hidden border">
                        <img src="{{ $fp['thumbnailUrl'] ?? $fp['url'] }}" alt="{{ __('property-single.floor_plan') }}" class="img-fluid w-100" style="object-fit:contain;background:var(--white)" loading="lazy">
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Documents ──────────────────────────────────────────── --}}
@if($__documents->isNotEmpty())
<div class="accordion-item">
    <div class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-docs" aria-expanded="true">
            {{ __('property-single.documents') }}
        </button>
    </div>
    <div id="accordion-docs" class="accordion-collapse collapse show">
        <div class="accordion-body">
            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                @foreach($__documents as $d)
                @php $__name = $d['title'] ?? rawurldecode(basename(parse_url($d['url'], PHP_URL_PATH) ?? $d['url'])); @endphp
                <li>
                    <a href="{{ $d['url'] }}" target="_blank" rel="noopener" download class="d-flex align-items-center gap-2 p-2 rounded border text-body text-decoration-none">
                        <x-icon name="receipt_long" class="text-primary flex-shrink-0"/>
                        <span class="flex-fill text-truncate">{{ $__name }}</span>
                        <span class="badge bg-primary">{{ __('property-single.download') }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

{{-- ── Viewers init (only when needed) ────────────────────── --}}
@if($__hasPanoImage)
<link rel="stylesheet" href="{{ asset('build/plugins/pannellum/pannellum.css') }}">
<script src="{{ asset('build/plugins/pannellum/pannellum.js') }}"></script>
@endif
@if($__panoramas->isNotEmpty() || $__floorplans->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($__hasPanoImage)
    if (typeof pannellum !== 'undefined') {
        document.querySelectorAll('.ps-pano[data-pano-url]').forEach(function (el) {
            pannellum.viewer(el, { type: 'equirectangular', panorama: el.dataset.panoUrl, autoLoad: true });
        });
    }
    @endif
    @if($__floorplans->isNotEmpty())
    if (typeof Fancybox !== 'undefined') Fancybox.bind('[data-fancybox="floorplan"]');
    @endif
});
</script>
@endif
