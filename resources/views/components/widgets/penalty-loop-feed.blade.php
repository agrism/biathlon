<!-- Biathlon News & Telemetry Stream -->
<div class="mb-12 w-full max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-6 overflow-hidden">
        <!-- Section Header -->
        <div class="pb-4 mb-3 border-b border-slate-100">
            <h2 class="text-base sm:text-lg font-black tracking-tight text-slate-900 leading-tight">
                Biathlon News & Telemetry
            </h2>
            <p class="text-xs text-slate-400 font-medium">Race commentary, shooting splits & analytics</p>
        </div>

        <!-- Native Feed Stream (Immune to ad-blockers, fast, reliable) -->
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
</div>
