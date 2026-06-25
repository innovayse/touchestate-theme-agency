{{-- Comments accordion — loaded async via /property/{slug}/extras (skeleton-first). Shown only when there is at least one comment. --}}
@if(!empty($comments['items']))
<div class="accordion-item mb-xl-0">
    <div class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-9" aria-expanded="true">
            {{ __('property-single.comments') }}
        </button>
    </div>
    <div id="accordion-9" class="accordion-collapse collapse show">
        <div class="accordion-body">
            <div class="sub-head mb-4">
                <h6 class="fs-16 fw-semibold"> {{ __('property-single.comments') }} ({{ $comments['totalCount'] ?? 0 }}) </h6>
            </div>

            @foreach($comments['items'] as $comment)
            @php
                $__rName = $comment['authorName'] ?? __('property-single.anonymous');
                $__rInitials = collect(explode(' ', $__rName))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                $__rDate = \Carbon\Carbon::parse($comment['createdAt'] ?? now());
                $__rDateStr = $__rDate->format('d') . ' ' . __('property.' . strtolower($__rDate->format('M'))) . ', ' . $__rDate->format('Y');
            @endphp
            <div class="card shadow-none review-items">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle bg-info d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:48px;height:48px;font-size:16px;">
                            {{ $__rInitials }}
                        </div>
                        <div class="flex-fill">
                            <h6 class="fs-16 fw-semibold mb-0">{{ $__rName }}</h6>
                            <p class="fs-13 text-muted mb-0">{{ $__rDateStr }}</p>
                        </div>
                        @if(!empty($comment['authorType']))
                        <span class="badge bg-primary fs-11">{{ $comment['authorType'] }}</span>
                        @endif
                    </div>

                    @if(!empty($comment['commentText']))
                    <p class="mb-2 text-body">{{ $comment['commentText'] }}</p>
                    @endif

                    @if(!empty($comment['replies']))
                    <div class="mt-3 ms-4 border-start ps-3">
                        @foreach($comment['replies'] as $__reply)
                        @php
                            $__aName = $__reply['authorName'] ?? '';
                            $__aInitials = collect(explode(' ', $__aName))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                            $__aDate = \Carbon\Carbon::parse($__reply['createdAt'] ?? now());
                            $__aDateStr = $__aDate->format('d') . ' ' . __('property.' . strtolower($__aDate->format('M'))) . ', ' . $__aDate->format('Y');
                        @endphp
                        <div class="d-flex align-items-start gap-3 {{ !$loop->last ? 'mb-3' : '' }}">
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;font-size:14px;">
                                {{ $__aInitials }}
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h6 class="fs-14 fw-semibold mb-0">{{ $__aName }}</h6>
                                    @if(!empty($__reply['isOwnerReply']))
                                    <span class="badge bg-primary fs-11">{{ __('property-single.owner_reply') }}</span>
                                    @endif
                                </div>
                                <p class="fs-12 text-muted mb-1">{{ $__aDateStr }}</p>
                                <p class="mb-0 fs-14 text-body">{{ $__reply['replyText'] ?? '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>
@endif
