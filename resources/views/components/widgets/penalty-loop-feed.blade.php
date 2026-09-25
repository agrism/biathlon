<!-- Biathlon News & Telemetry Stream -->
<div class="mb-12 w-full px-3 sm:px-6 lg:px-8 mx-auto" x-data="{
    viewMode: localStorage.getItem('biathlon_tweets_view_mode') || 'grid_auto',
    setView(mode) {
        this.viewMode = mode;
        localStorage.setItem('biathlon_tweets_view_mode', mode);
    }
}">
    <!-- Section Header with Title & View Mode Selector -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none bg-sky-50 text-sky-700 text-[11px] font-extrabold uppercase tracking-wider border border-sky-200/80">
                    <span class="w-1.5 h-1.5 rounded-none bg-sky-500 animate-pulse"></span>
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

        <!-- View Switcher Controls (Wide Auto, 4 Cards, 3 Cards, List) -->
        <div class="flex items-center gap-1 bg-slate-100/90 p-1 rounded-none self-start sm:self-auto border border-slate-200/60 shadow-inner">
            <!-- Auto Fluid Grid (scales from 1 up to 6/7 cards on wider monitors) -->
            <button
                type="button"
                @click="setView('grid_auto')"
                :class="(viewMode === 'grid_auto' || viewMode === 'grid_5' || !viewMode) ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-none text-xs transition-all cursor-pointer"
                title="Wide Fluid Grid (auto scales up to 5-7 cards on wide/ultrawide monitors)"
            >
                <i class="fa-solid fa-table-cells text-xs"></i>
                <span class="hidden sm:inline">Wide Auto</span>
            </button>

            <!-- 4 Cards -->
            <button
                type="button"
                @click="setView('grid_4')"
                :class="viewMode === 'grid_4' ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-none text-xs transition-all cursor-pointer"
                title="4 Cards per row"
            >
                <i class="fa-solid fa-border-all text-xs"></i>
                <span class="hidden sm:inline">4 Cards</span>
            </button>

            <!-- 3 Cards (Comfortable) -->
            <button
                type="button"
                @click="setView('grid_3')"
                :class="viewMode === 'grid_3' ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-none text-xs transition-all cursor-pointer"
                title="3 Cards per row"
            >
                <i class="fa-solid fa-table-cells-large text-xs"></i>
                <span class="hidden sm:inline">3 Cards</span>
            </button>

            <!-- 1 Column List -->
            <button
                type="button"
                @click="setView('list')"
                :class="viewMode === 'list' ? 'bg-white text-sky-600 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-none text-xs transition-all cursor-pointer"
                title="Compact single column list"
            >
                <i class="fa-solid fa-bars-staggered text-xs"></i>
                <span class="hidden sm:inline">List</span>
            </button>
        </div>
    </div>

    <!-- Feed Container -->
    @if(isset($tweets) && $tweets->isNotEmpty())
        <div
            id="tweets-grid"
            :class="{
                'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 3xl:grid-cols-6 4xl:grid-cols-7 gap-3 sm:gap-3.5 lg:gap-4': (viewMode === 'grid_auto' || viewMode === 'grid_5' || !viewMode),
                'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-3.5 lg:gap-4': viewMode === 'grid_4',
                'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-3.5 sm:gap-4 lg:gap-4.5': viewMode === 'grid_3',
                'flex flex-col gap-3 max-w-4xl mx-auto': viewMode === 'list'
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

    @if(auth()->check() && auth()->user()->isAdmin())
        <datalist id="admin-athletes-datalist">
            @foreach(app(\App\Services\BiathlonTweetService::class)->getAthletesWithPhotos() as $ath)
                <option value="{{ $ath->given_name }} {{ $ath->family_name }} ({{ $ath->nat }})">{{ $ath->given_name }} {{ $ath->family_name }}</option>
            @endforeach
        </datalist>
    @endif
</div>
