@extends('layout.app')
@section('title', __('footer.contact_us'))

@section('content')
<x-breadcrumb :title="__('footer.contact_us')" />

<section class="py-16">
    <div class="container-x grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-sand bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-600">@</div>
            <h3 class="mt-4 font-display text-lg font-semibold text-ink">Email</h3>
            <p class="mt-1 text-sm text-neutral-600">{{ $workspace['email'] ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-sand bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-600">☎</div>
            <h3 class="mt-4 font-display text-lg font-semibold text-ink">Phone</h3>
            <p class="mt-1 text-sm text-neutral-600">{{ $workspace['phone'] ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-sand bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-600">⌖</div>
            <h3 class="mt-4 font-display text-lg font-semibold text-ink">Address</h3>
            <p class="mt-1 text-sm text-neutral-600">{{ $workspace['address'] ?? '—' }}</p>
        </div>
    </div>
</section>
@endsection
