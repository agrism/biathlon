<!-- Penalty Loop Biathlon News & X Stream -->
<div class="mb-12 w-full max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-6 overflow-hidden">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-black text-white flex items-center justify-center font-bold shadow-xs">
                    <i class="fa-brands fa-x-twitter text-base"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-black tracking-tight text-slate-900 leading-tight">
                        Biathlon News & Telemetry
                    </h2>
                    <p class="text-xs text-slate-400 font-medium">Race commentary, shooting splits & insights from @penaltyloop</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="https://x.com/penaltyloop"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs"
                >
                    <i class="fa-brands fa-x-twitter text-xs"></i>
                    <span>@penaltyloop on X</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400"></i>
                </a>
            </div>
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
