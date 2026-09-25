<!-- Biathlon News & Telemetry Stream -->
<div class="mb-12 w-full mx-auto" x-data="{
    viewMode: localStorage.getItem('biathlon_tweets_view_mode') || 'grid_4',
    setView(mode) {
        this.viewMode = mode;
        localStorage.setItem('biathlon_tweets_view_mode', mode);
    }
}">
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-7 overflow-hidden">
        <!-- Section Header with Title & View Mode Selector -->
        <div class="pb-5 mb-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 text-[11px] font-extrabold uppercase tracking-wider border border-sky-200/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                        Live Telemetry & News
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-tight">
                    Biathlon News & Social Stream
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 font-medium mt-0.5">
                    Live race commentary, athlete interviews, shooting analytics & international press
                </p>
            </div>

            <!-- View Switcher Controls (4 cards, 3 cards, list) -->
            <div class="flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-2xl self-start sm:self-auto border border-slate-200/60 shadow-inner">
                <!-- 4 Cards (Wide Grid) -->
                <button
                    type="button"
                    @click="setView('grid_4')"
                    :class="viewMode === 'grid_4' ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer"
                    title="4 Cards per row on wide screens"
                >
                    <i class="fa-solid fa-table-cells text-xs"></i>
                    <span class="hidden md:inline">4 Cards</span>
                </button>

                <!-- 3 Cards (Comfortable) -->
                <button
                    type="button"
                    @click="setView('grid_3')"
                    :class="viewMode === 'grid_3' ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer"
                    title="3 Cards per row"
                >
                    <i class="fa-solid fa-table-cells-large text-xs"></i>
                    <span class="hidden md:inline">3 Cards</span>
                </button>

                <!-- 1 Column List -->
                <button
                    type="button"
                    @click="setView('list')"
                    :class="viewMode === 'list' ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer"
                    title="Compact single column list"
                >
                    <i class="fa-solid fa-bars-staggered text-xs"></i>
                    <span class="hidden md:inline">List</span>
                </button>
            </div>
        </div>

        <!-- Feed Container -->
        @if(isset($tweets) && $tweets->isNotEmpty())
            <div
                id="tweets-grid"
                :class="{
                    'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5': viewMode === 'grid_4' || (!viewMode || viewMode === 'grid_5'),
                    'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6': viewMode === 'grid_3',
                    'flex flex-col gap-3.5 max-w-3xl mx-auto': viewMode === 'list'
                }"
                class="transition-all duration-300"
            >
                @include('twitter.partials.tweet-cards', ['tweets' => $tweets])
            </div>
        @else
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fa-solid fa-satellite-dish text-2xl text-slate-300 mb-2 block"></i>
                No updates available right now.
            </div>
        @endif
    </div>
</div>
