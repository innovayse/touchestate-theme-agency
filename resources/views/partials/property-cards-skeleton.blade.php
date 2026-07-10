<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 animate-pulse">
    @for($i = 0; $i < 6; $i++)
    <div class="rounded-2xl border border-sand bg-white">
        <div class="h-56 rounded-t-2xl bg-sand"></div>
        <div class="p-5 space-y-3">
            <div class="h-5 w-3/4 rounded-full bg-sand"></div>
            <div class="h-4 w-1/2 rounded-full bg-sand/70"></div>
            <div class="flex gap-3 pt-1">
                <div class="h-4 w-20 rounded-full bg-sand/50"></div>
                <div class="h-4 w-16 rounded-full bg-sand/50"></div>
            </div>
            <div class="space-y-2 pt-2">
                <div class="h-6 w-2/5 rounded-full bg-sand"></div>
                <div class="h-4 w-1/4 rounded-full bg-sand/60"></div>
            </div>
        </div>
    </div>
    @endfor
</div>
