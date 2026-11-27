@extends('layouts.admin', ['heading' => ''])

@section('style')
<style>[x-cloak] { display: none !important; }</style>
@endsection

@section('content')
    <div class="mb-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100/80 text-sky-800 text-xs font-bold uppercase tracking-wider mb-2">
            🏆 All-Time Prediction Standings
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Prediction Leaderboards
        </h1>
        <p class="text-sm text-slate-500 max-w-xl mx-auto mt-1">
            Track user performance, regular accuracy points, podium bonuses, and stage victories across IBU World Cup seasons.
        </p>
    </div>

    @php
        $currentSeason = $seasons[0] ?? null;
        $pastSeasons = array_slice($seasons, 1);
    @endphp

    @if($currentSeason)
        <!-- Current / Active Season Leaderboard -->
        @include('forecasts.summary.partials.season-table', [
            'season' => $currentSeason,
            'sId' => data_get($currentSeason, 'id', 's_current'),
            'canClose' => false,
        ])

        <!-- Detail Event Inspection Container -->
        <div
            id="user-event"
            class="mt-6 mb-12"
            @if(isset($autoOpenUrl) && $autoOpenUrl)
                hx-get="{{ $autoOpenUrl }}"
                hx-trigger="load"
            @endif
        ></div>
    @endif

    @if(!empty($pastSeasons))
        <!-- Previous Seasons Section (Collapsed by Default with Buttons for Each Season) -->
        <div class="mt-14 pt-8 border-t border-slate-200" x-data="{ openSeasons: {} }">
            <div class="bg-gradient-to-r from-slate-100 via-white to-slate-100 rounded-2xl border border-slate-200/90 p-5 mb-8 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center text-lg shadow-sm">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Iepriekšējās sezonas</h3>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Arhīvs</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Skatīt iepriekšējo sezonu un turnīru kopvērtējumus</p>
                        </div>
                    </div>

                    <!-- Season Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($pastSeasons as $season)
                            @php
                                $sId = data_get($season, 'id', 's_'.$loop->index);
                                $btnTitle = data_get($season, 'button_title') ?? data_get($season, 'title');
                            @endphp
                            <button
                                type="button"
                                @click="openSeasons['{{ $sId }}'] = !openSeasons['{{ $sId }}']; if (openSeasons['{{ $sId }}']) { $nextTick(() => document.getElementById('season-block-{{ $sId }}')?.scrollIntoView({ behavior: 'smooth', block: 'start' })) }"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black transition-all border cursor-pointer select-none"
                                :class="openSeasons['{{ $sId }}'] 
                                    ? 'bg-slate-900 text-white border-slate-900 shadow-md ring-2 ring-slate-900/10' 
                                    : 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border-slate-300/80 shadow-2xs'"
                            >
                                <span>{{ data_get($season, 'icon', '🏆') }}</span>
                                <span>{{ $btnTitle }}</span>
                                <i class="fa-solid text-[10px] transition-transform duration-200" :class="openSeasons['{{ $sId }}'] ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Past Seasons Content Cards (shown when opened) -->
            @foreach($pastSeasons as $season)
                @php
                    $sId = data_get($season, 'id', 's_'.$loop->index);
                @endphp
                <div
                    x-show="openSeasons['{{ $sId }}']"
                    x-cloak
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.99]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.99]"
                >
                    @include('forecasts.summary.partials.season-table', [
                        'season' => $season,
                        'sId' => $sId,
                        'canClose' => true,
                    ])
                </div>
            @endforeach
        </div>
    @endif
@endsection
