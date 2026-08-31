<div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] flex flex-col my-auto" onclick="event.stopPropagation()">
    <!-- Modal Sticky Header / Close Bar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/80 backdrop-blur-xs sticky top-0 z-20 shrink-0">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Athlete Profile</span>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('athletes.show', $athlete->id) }}?full_page=1"
                target="_blank"
                class="p-1.5 rounded-xl text-slate-400 hover:text-sky-600 hover:bg-slate-200/60 transition-colors"
                title="Open in new tab"
            >
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
            <button
                type="button"
                onclick="closeAthleteModal();"
                class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-200/80 hover:bg-rose-100 hover:text-rose-600 text-slate-600 transition-colors cursor-pointer"
                title="Close"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Modal Scrollable Body -->
    <div id="athlete-modal-body" class="flex-1 p-6 space-y-6 overflow-y-auto overscroll-contain" onscroll="handleModalScroll(this)">
        <!-- Athlete Profile Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 text-white rounded-3xl p-6 sm:p-7 shadow-lg relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
                <!-- Athlete Headshot -->
                <div class="w-28 h-28 rounded-2xl bg-white/10 border-2 border-white/20 p-1 flex items-center justify-center overflow-hidden shadow-2xl flex-shrink-0">
                    @if($athlete->photo_uri)
                        {!! \App\Helpers\RemoteImageRenderHelper::instance()->getImageTag(url: $athlete->photo_uri, attributes: 'class="w-full h-full object-cover rounded-xl" width="112" height="112"') !!}
                    @else
                        <i class="fa-solid fa-person-skiing text-4xl text-slate-400"></i>
                    @endif
                </div>

                <!-- Bio Info -->
                <div class="text-center sm:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        @if($athlete->flag_uri)
                            <img src="{!! $athlete->flag_uri !!}" class="h-4 rounded-xs shadow-xs">
                        @endif
                        <span class="px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-xs font-extrabold uppercase tracking-wider">
                            {{ $athlete->nat }}
                        </span>
                        @if($athlete->birth_date)
                            <span class="px-2.5 py-0.5 rounded-full bg-white/10 text-slate-300 text-xs">
                                Age {{ $athlete->birth_date->age }} &bull; {{ $athlete->birth_date->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        {{ $athlete->given_name }} <span class="uppercase font-black">{{ $athlete->family_name }}</span>
                    </h2>

                    <!-- Stats Strip -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mt-3">
                        @if($athlete->stat_p_total !== null)
                            <div class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-center">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">WC Points</div>
                                <div class="text-sm font-black text-sky-300">{{ floatval($athlete->stat_p_total) }}</div>
                            </div>
                        @endif
                        @if($athlete->stat_shooting !== null)
                            <div class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-center">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Accuracy</div>
                                <div class="text-sm font-black text-emerald-400">{{ floatval($athlete->stat_shooting) }}%</div>
                            </div>
                        @endif
                        @if($athlete->stat_ski_kmb !== null)
                            <div class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-center">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Ski Speed</div>
                                <div class="text-sm font-black text-amber-300">-{{ floatval($athlete->stat_ski_kmb) }}s/km</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Career / Season Results Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-sky-500"></i> Competition Results
                </h3>
                <span class="text-xs font-bold text-slate-400">{{ $resultsCount ?? count($results) }} races</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-4 py-2.5">Rank</th>
                            <th scope="col" class="px-4 py-2.5">Competition</th>
                            <th scope="col" class="px-4 py-2.5">Shooting</th>
                            <th scope="col" class="px-4 py-2.5">Behind</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @include('athletes.partials.result-rows', ['results' => $results, 'athlete' => $athlete])
                        @if($results->isEmpty())
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 italic">No competition results recorded yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
