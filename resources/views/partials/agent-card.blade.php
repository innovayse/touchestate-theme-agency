<div class="col-xl-3 col-lg-4 col-md-6 d-flex">
    <div class="agent-item flex-fill" style="cursor:pointer" onclick="window.location.href='/{{ app()->getLocale() }}/agent/{{ $agent['id'] }}'">
        <div class="agent-img">
            <a href="/{{ app()->getLocale() }}/agent/{{ $agent['id'] }}">
                @if($agent['avatarUrl'])
                <img src="{{ $agent['avatarUrl'] }}" class="img-fluid" alt="{{ $agent['fullName'] }}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'agent-avatar-placeholder\'><i class=\'material-icons-outlined\'>person</i></div>';">
                @else
                <div class="agent-avatar-placeholder">
                    <i class="material-icons-outlined">person</i>
                </div>
                @endif
            </a>
            @if($agent['propertyCount'] > 0)
            <div class="position-absolute top-0 end-0 p-3">
                <span class="badge bg-secondary">{{ $agent['propertyCount'] }} {{ __('agent.listings') }}</span>
            </div>
            @endif
        </div>
        <div class="agent-content">
            <h5 class="mb-1"><a href="/{{ app()->getLocale() }}/agent/{{ $agent['id'] }}">{{ $agent['fullName'] }}</a></h5>
            @php $roleKey = 'agent.role_' . strtolower($agent['role'] ?? ''); @endphp
            <p class="mb-1 fs-14 text-muted">{{ __($roleKey) !== $roleKey ? __($roleKey) : ($agent['role'] ?? '') }}</p>
            @if($agent['phone'])
            <p class="mb-0 fs-14 d-flex align-items-center justify-content-center gap-1">
                <i class="material-icons-outlined" style="font-size:16px">phone</i>
                {{ $agent['phone'] }}
            </p>
            @endif
        </div>
    </div>
</div>
