<!-- Penalty Loop Biathlon News Stream -->
<div class="mb-12 max-w-4xl mx-auto">
    <!-- Clean Section Header -->
    <div class="flex items-center gap-2 pb-3 mb-2 border-b border-slate-200">
        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
        <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900">
            Biathlon News & Telemetry
        </h2>
    </div>

    <!-- News List -->
    @if(isset($tweets) && $tweets->isNotEmpty())
        <div id="tweets-grid" class="flex flex-col">
            @include('twitter.partials.tweet-cards', ['tweets' => $tweets])
        </div>
    @else
        <div class="text-center py-8 text-slate-400 text-xs">
            No updates available.
        </div>
    @endif
</div>
