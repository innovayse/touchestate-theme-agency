{{-- GOLDHOUSE brand mark — double-peak mountain + wordmark. Falls back to workspace name/logo when the API provides one. --}}
@if(!empty($workspace['logoUrl']))
    <img src="{{ $workspace['logoUrl'] }}" class="img-fluid" style="max-height:38px;width:auto" alt="{{ $workspace['name'] ?? 'GOLDHOUSE' }}">
    <span class="brand-name fw-semibold">{{ $workspace['name'] ?? 'GOLDHOUSE' }}</span>
@else
    <span class="brand-mark" aria-hidden="true">
        <svg width="30" height="24" viewBox="0 0 30 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 22 L11 4 L16.5 13.5 L20 8 L28 22 Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" fill="none"/>
        </svg>
    </span>
    <span class="brand-text">
        <span class="brand-name">GOLDHOUSE</span>
        <span class="brand-sub">REAL ESTATE</span>
    </span>
@endif
