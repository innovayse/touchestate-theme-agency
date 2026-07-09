@php
    $commentItems = $comments['items'] ?? [];
@endphp
@if(empty($commentItems))
<div id="comments-container"></div>
@else
<div id="comments-container" class="space-y-4">
    @forelse($commentItems as $c)
        <div class="rounded-2xl border border-sand bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-100 font-semibold text-brand-700">
                    {{ mb_strtoupper(mb_substr($c['authorName'] ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-semibold text-ink">{{ $c['authorName'] ?? __('property.anonymous') }}</div>
                    @if(!empty($c['createdAt']))
                        <div class="text-xs text-neutral-400">{{ \Carbon\Carbon::parse($c['createdAt'])->format('d.m.Y') }}</div>
                    @endif
                </div>
            </div>
            @if(!empty($c['rating']))
                <div class="mt-2 flex gap-0.5">
                    @for($star = 1; $star <= 5; $star++)
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $star <= $c['rating'] ? '#a6644f' : 'none' }}" stroke="#a6644f" stroke-width="1.5">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    @endfor
                </div>
            @endif
            @if(!empty($c['body'] ?? $c['content'] ?? $c['text'] ?? ''))
                <p class="mt-3 text-sm leading-relaxed text-neutral-600">{{ $c['body'] ?? $c['content'] ?? $c['text'] ?? '' }}</p>
            @endif
        </div>
    @empty
    @endforelse
</div>
@endif
