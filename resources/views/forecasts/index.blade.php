@extends('layouts.admin', ['heading' => ''])

@section('style')
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2" 
     x-data="{ 
         tab: 'upcoming', 
         discipline: 'all',
         expandAll: false,
         openStages: {
             @if($nextForecast)
                 '{{ $nextForecast->competition->event_id }}': true
             @endif
         },
         toggleStage(id) {
             this.openStages[id] = !this.openStages[id];
         },
         toggleAll() {
             this.expandAll = !this.expandAll;
             @foreach($upcomingByStage as $stageId => $races)
                 this.openStages['{{ $stageId }}'] = this.expandAll;
             @endforeach
         }
     }">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-200/80">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100/80 text-sky-800 text-xs font-bold uppercase tracking-wider mb-2">
                <span>🎯</span>
                <span>IBU World Cup Predictions</span>
                <span class="text-slate-400">•</span>
                <span class="text-sky-900 font-extrabold">Season {{ $season->name == '2627' ? '2026 / 2027' : ($season->name == '2526' ? '2025 / 2026' : $season->name) }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Race Predictions & Calendar
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Predict top 6 athletes for each race before the start deadline and compete on the leaderboard.
            </p>
        </div>

        <!-- Quick Actions & Leaderboard Link -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <a href="{{ route('forecasts.summary.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm hover:shadow transition-all">
                <i class="fa-solid fa-trophy"></i>
                <span>Leaderboard Standings</span>
            </a>

            <!-- Season Switcher Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" 
                        @click="open = !open"
                        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-300/80 shadow-2xs transition-all">
                    <i class="fa-solid fa-calendar-days text-slate-400"></i>
                    <span>{{ $season->name == '2627' ? '2026/27' : ($season->name == '2526' ? '2025/26' : '2024/25') }}</span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                </button>
                <div x-show="open" 
                     x-cloak
                     class="absolute right-0 mt-1 w-44 rounded-xl bg-white border border-slate-200 shadow-xl py-1 z-30">
                    @foreach($availableSeasons as $s)
                        <a href="{{ route('forecasts.index', ['season' => $s->name]) }}" 
                           class="flex items-center justify-between px-3.5 py-2 text-xs font-semibold {{ $s->name === $season->name ? 'bg-sky-50 text-sky-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span>Season {{ $s->name == '2627' ? '2026/27' : ($s->name == '2526' ? '2025/26' : '2024/25') }}</span>
                            @if($s->name === $season->name)
                                <i class="fa-solid fa-check text-sky-600"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- HERO CARD: NEXT RACE UP -->
    @if($nextForecast)
        @php
            $nextComp = $nextForecast->competition;
            $nextEvent = $nextComp?->event;
            $flagUrl = $nextEvent?->nat ? 'https://info.blob.core.windows.net/resources/bt/flags/' . strtolower($nextEvent->nat) . '.png' : null;
            $isToday = $nextForecast->submit_deadline_at?->isToday();
            $isTomorrow = $nextForecast->submit_deadline_at?->isTomorrow();
            $diffHours = now()->diffInHours($nextForecast->submit_deadline_at, false);
            $deadlineIso = $nextForecast->submit_deadline_at?->toIso8601String();
            $athletesCount = $nextComp?->results?->count() ?? 0;
        @endphp

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-sky-950 text-white shadow-xl border border-slate-700/60 p-5 sm:p-7 mb-8">
            <!-- Background glow decoration -->
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left: Race Information -->
                <div class="flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap mb-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                            Next Race Up
                        </span>

                        @if($isToday)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                Today!
                            </span>
                        @elseif($isTomorrow)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase bg-sky-400/20 text-sky-300 border border-sky-400/30">
                                Tomorrow
                            </span>
                        @endif

                        @if($nextEvent)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-300">
                                @if($flagUrl)
                                    <img src="{{ $flagUrl }}" alt="{{ $nextEvent->nat }}" class="w-4 h-3 object-cover rounded-2xs inline-block">
                                @endif
                                <span>{{ $nextEvent->short_description }}, {{ $nextEvent->nat_long }}</span>
                                <span class="text-slate-500">•</span>
                                <span class="text-slate-400">Stage {{ $nextEvent->event_series_no ?? '1' }}</span>
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-2">
                        {{ $nextComp?->description ?? 'Biathlon Race' }}
                    </h2>

                    <div class="flex items-center gap-4 text-xs text-slate-300 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-regular fa-clock text-sky-400"></i>
                            <span>Deadline: <strong class="text-white font-bold">{{ $nextForecast->submit_deadline_at?->format('D, d M • H:i') }} CET</strong></span>
                        </div>
                        @if($athletesCount > 0)
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-list-ol text-slate-400"></i>
                                <span>{{ $athletesCount }} athletes in start list</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-medal text-amber-400"></i>
                            <span>Dainis Matrix Scoring</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Countdown Timer & CTA -->
                <div class="flex flex-col sm:flex-row lg:flex-col items-start sm:items-center lg:items-end gap-4 shrink-0">
                    <!-- Alpine Countdown -->
                    <div x-data="countdown('{{ $deadlineIso }}')" class="bg-black/40 backdrop-blur-md px-4 py-2.5 rounded-xl border border-white/10 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Time Remaining</div>
                        <div class="flex items-center gap-2 text-white font-mono font-bold text-lg" x-show="!isExpired">
                            <div class="flex flex-col items-center">
                                <span class="leading-none text-xl text-sky-300" x-text="days">0</span>
                                <span class="text-[9px] text-slate-400 font-sans uppercase font-normal">days</span>
                            </div>
                            <span class="text-slate-500 pb-2">:</span>
                            <div class="flex flex-col items-center">
                                <span class="leading-none text-xl" x-text="pad(hours)">00</span>
                                <span class="text-[9px] text-slate-400 font-sans uppercase font-normal">hrs</span>
                            </div>
                            <span class="text-slate-500 pb-2">:</span>
                            <div class="flex flex-col items-center">
                                <span class="leading-none text-xl" x-text="pad(minutes)">00</span>
                                <span class="text-[9px] text-slate-400 font-sans uppercase font-normal">min</span>
                            </div>
                            <span class="text-slate-500 pb-2">:</span>
                            <div class="flex flex-col items-center">
                                <span class="leading-none text-xl text-amber-400" x-text="pad(seconds)">00</span>
                                <span class="text-[9px] text-slate-400 font-sans uppercase font-normal">sec</span>
                            </div>
                        </div>
                        <div class="text-sm font-bold text-rose-400" x-show="isExpired" x-cloak>
                            Race Started
                        </div>
                    </div>

                    <!-- User Prediction Status & CTA Button -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        @auth
                            @if($nextForecast->auth_is_ready)
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Prediction Ready (6/6)</span>
                                </div>
                                <a href="{{ route('forecasts.show', $nextForecast->id) }}" 
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-extrabold text-sm shadow-lg transition-all">
                                    <span>Edit Top 6</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            @elseif($nextForecast->auth_submitted_count > 0)
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Incomplete ({{ $nextForecast->auth_submitted_count }}/6)</span>
                                </div>
                                <a href="{{ route('forecasts.show', $nextForecast->id) }}" 
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-lg transition-all animate-pulse">
                                    <span>Complete Prediction</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-300 border border-sky-500/30 text-xs font-bold">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>Not Submitted Yet</span>
                                </div>
                                <a href="{{ route('forecasts.show', $nextForecast->id) }}" 
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-black text-sm shadow-lg shadow-sky-500/25 transition-all">
                                    <span>Predict Top 6 Now</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            @endif
                        @else
                            <a href="{{ route('forecasts.show', $nextForecast->id) }}" 
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-black text-sm shadow-lg transition-all">
                                <span>Predict Top 6</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MAIN SECTION TABS & DISCIPLINE FILTER BAR -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 bg-slate-200/80 p-1 rounded-xl w-fit">
            <button type="button" 
                    @click="tab = 'upcoming'"
                    :class="tab === 'upcoming' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs transition-all">
                <span>Open for Predictions</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                      :class="tab === 'upcoming' ? 'bg-sky-100 text-sky-800' : 'bg-slate-300/80 text-slate-700'">
                    {{ $stats['upcoming_count'] }}
                </span>
            </button>

            @if($stats['live_count'] > 0)
                <button type="button" 
                        @click="tab = 'live'"
                        :class="tab === 'live' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs transition-all">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Live / In Progress</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                        {{ $stats['live_count'] }}
                    </span>
                </button>
            @endif

            <button type="button" 
                    @click="tab = 'completed'"
                    :class="tab === 'completed' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs transition-all">
                <span>Completed Results</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                      :class="tab === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300/80 text-slate-700'">
                    {{ $stats['completed_count'] }}
                </span>
            </button>
        </div>

        <!-- Discipline Filter Chips (Shown when in 'upcoming' tab) -->
        <div class="flex items-center gap-2 flex-wrap" x-show="tab === 'upcoming'">
            <span class="text-xs font-bold text-slate-400 mr-1 hidden sm:inline">Filter:</span>
            
            <button type="button" 
                    @click="discipline = 'all'"
                    :class="discipline === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-600 border-slate-300/80 hover:bg-slate-50'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all">
                All
            </button>

            <button type="button" 
                    @click="discipline = 'men'"
                    :class="discipline === 'men' ? 'bg-sky-600 text-white border-sky-600 shadow-xs' : 'bg-white text-slate-600 border-slate-300/80 hover:bg-slate-50'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all">
                Men
            </button>

            <button type="button" 
                    @click="discipline = 'women'"
                    :class="discipline === 'women' ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-white text-slate-600 border-slate-300/80 hover:bg-slate-50'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all">
                Women
            </button>

            <button type="button" 
                    @click="discipline = 'relay'"
                    :class="discipline === 'relay' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-600 border-slate-300/80 hover:bg-slate-50'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all">
                Relays / Team
            </button>

            <button type="button" 
                    @click="toggleAll()"
                    class="ml-auto text-xs font-semibold text-slate-500 hover:text-slate-800 underline decoration-slate-300 pl-2 cursor-pointer">
                <span x-text="expandAll ? 'Collapse Stages' : 'Expand All Stages'"></span>
            </button>
        </div>
    </div>

    <!-- TAB 1: UPCOMING RACES GROUPED BY STAGE (ASCENDING CHRONOLOGICAL) -->
    <div x-show="tab === 'upcoming'" class="space-y-6">
        @if($upcomingByStage->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-slate-500">
                <i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-base font-bold text-slate-800">No Upcoming Races Scheduled</h3>
                <p class="text-xs text-slate-500 mt-1">All races for this season may have been completed.</p>
            </div>
        @else
            @foreach($upcomingByStage as $stageId => $races)
                @php
                    $firstRace = $races->first();
                    $stageEvent = $firstRace?->competition?->event;
                    $stageFlag = $stageEvent?->nat ? 'https://info.blob.core.windows.net/resources/bt/flags/' . strtolower($stageEvent->nat) . '.png' : null;
                    $isCurrentStage = ($nextForecast && $nextForecast->competition?->event_id === $stageId);
                    $firstDate = $stageEvent?->first_competition_date ?? $stageEvent?->start_date;
                    $endDate = $stageEvent?->end_date;
                @endphp

                <div class="rounded-2xl border transition-all overflow-hidden bg-white {{ $isCurrentStage ? 'border-sky-300 ring-2 ring-sky-500/20 shadow-md' : 'border-slate-200/90 shadow-2xs' }}"
                     x-data="{ 
                         racesCount: {{ $races->count() }},
                         matchesFilter() {
                             if (discipline === 'all') return true;
                             let men = {{ $races->where('category_type', 'men')->count() }};
                             let women = {{ $races->where('category_type', 'women')->count() }};
                             let relay = {{ $races->where('category_type', 'relay')->count() }};
                             if (discipline === 'men' && men > 0) return true;
                             if (discipline === 'women' && women > 0) return true;
                             if (discipline === 'relay' && relay > 0) return true;
                             return false;
                         }
                     }"
                     x-show="matchesFilter()">

                    <!-- Stage Header / Accordion Trigger -->
                    <div class="p-4 sm:p-5 flex items-center justify-between gap-4 cursor-pointer select-none transition-colors {{ $isCurrentStage ? 'bg-gradient-to-r from-sky-50/70 via-white to-slate-50' : 'bg-slate-50/70 hover:bg-slate-100/70' }}"
                         @click="toggleStage('{{ $stageId }}')">
                        <div class="flex items-center gap-3.5">
                            @if($stageFlag)
                                <img src="{{ $stageFlag }}" alt="{{ $stageEvent->nat }}" class="w-8 h-5 object-cover rounded-xs shadow-2xs">
                            @else
                                <div class="w-8 h-5 rounded-xs bg-slate-300 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                    {{ $stageEvent->nat ?? 'IBU' }}
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                                        {{ $stageEvent?->short_description ?? 'World Cup Stage' }}
                                    </h3>
                                    <span class="text-xs font-semibold text-slate-500">
                                        ({{ $stageEvent?->nat_long ?? $stageEvent?->nat }})
                                    </span>

                                    @if($isCurrentStage)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-500 text-white shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                            Current Stage
                                        </span>
                                    @endif

                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                        Stage {{ $stageEvent?->event_series_no ?? $loop->iteration }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                    @if($firstDate && $endDate)
                                        <span>{{ $firstDate->format('d.m') }} - {{ $endDate->format('d.m.Y') }}</span>
                                    @endif
                                    <span>•</span>
                                    <span>{{ $races->count() }} races</span>
                                </p>
                            </div>
                        </div>

                        <!-- Right Chevron -->
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200"
                               :class="openStages['{{ $stageId }}'] ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    <!-- Races List / Cards -->
                    <div x-show="openStages['{{ $stageId }}']" 
                         x-collapse 
                         class="border-t border-slate-200 divide-y divide-slate-100">
                        @foreach($races as $forecast)
                            @php
                                $comp = $forecast->competition;
                                $categoryType = $forecast->category_type;
                                $isNext = ($nextForecast && $nextForecast->id === $forecast->id);
                                $isRaceToday = $forecast->submit_deadline_at?->isToday();
                                $isRaceTomorrow = $forecast->submit_deadline_at?->isTomorrow();
                            @endphp

                            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors hover:bg-slate-50/80 {{ $isNext ? 'bg-sky-50/40' : '' }}"
                                 x-show="discipline === 'all' || discipline === '{{ $categoryType }}'">
                                <!-- Left: Race details -->
                                <div class="flex items-start gap-3 sm:gap-4">
                                    <!-- Category Pill / Icon -->
                                    <div class="shrink-0 mt-0.5">
                                        @if($categoryType === 'men')
                                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-black text-xs shadow-2xs" title="Men's Discipline">
                                                M
                                            </div>
                                        @elseif($categoryType === 'women')
                                            <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-black text-xs shadow-2xs" title="Women's Discipline">
                                                W
                                            </div>
                                        @else
                                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-xs shadow-2xs" title="Relay / Mixed Discipline">
                                                <i class="fa-solid fa-people-group text-xs"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-sm sm:text-base font-extrabold text-slate-900">
                                                {{ $comp?->description ?? 'Biathlon Race' }}
                                            </h4>

                                            @if($isNext)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-200">
                                                    Next Up
                                                </span>
                                            @elseif($isRaceToday)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                                                    Today
                                                </span>
                                            @elseif($isRaceTomorrow)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-sky-100 text-sky-800">
                                                    Tomorrow
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                                <i class="fa-regular fa-clock text-slate-400 text-[11px]"></i>
                                                <span>{{ $forecast->submit_deadline_at?->format('D, d M • H:i') }} CET</span>
                                            </div>
                                            <span>•</span>
                                            <span class="text-slate-400">
                                                {{ $forecast->submit_deadline_at?->diffForHumans() }}
                                            </span>
                                            @if(($comp?->results?->count() ?? 0) > 0)
                                                <span>•</span>
                                                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                                    <i class="fa-solid fa-list-check text-[10px]"></i>
                                                    {{ $comp->results->count() }} on start list
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: User Status & CTA Button -->
                                <div class="flex items-center gap-3 sm:justify-end shrink-0 pl-12 sm:pl-0">
                                    @auth
                                        @if($forecast->auth_is_ready)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                                <span>Ready (6/6)</span>
                                            </span>
                                            <a href="{{ route('forecasts.show', $forecast->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all">
                                                <span>Edit</span>
                                                <i class="fa-solid fa-pencil text-[10px]"></i>
                                            </a>
                                        @elseif($forecast->auth_submitted_count > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                                <span>{{ $forecast->auth_submitted_count }}/6</span>
                                            </span>
                                            <a href="{{ route('forecasts.show', $forecast->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition-all">
                                                <span>Complete</span>
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('forecasts.show', $forecast->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-2xs transition-all">
                                                <span>Predict Top 6</span>
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('forecasts.show', $forecast->id) }}" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-2xs transition-all">
                                            <span>Predict Top 6</span>
                                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- TAB 2: LIVE / IN PROGRESS RACES -->
    @if($stats['live_count'] > 0)
        <div x-show="tab === 'live'" class="space-y-4">
            @foreach($liveForecasts as $forecast)
                @php
                    $comp = $forecast->competition;
                    $event = $comp?->event;
                    $flag = $event?->nat ? 'https://info.blob.core.windows.net/resources/bt/flags/' . strtolower($event->nat) . '.png' : null;
                @endphp
                <div class="rounded-2xl border border-amber-300 bg-amber-50/40 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-stopwatch animate-spin"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-200 text-amber-900">
                                    In Progress / Underway
                                </span>
                                @if($flag)
                                    <img src="{{ $flag }}" class="w-4 h-3 rounded-2xs inline-block">
                                @endif
                                <span class="text-xs text-slate-500">{{ $event?->short_description }}</span>
                            </div>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">
                                {{ $comp?->description }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Race started {{ $forecast->submit_deadline_at?->diffForHumans() }}. Official results pending.
                            </p>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('forecasts.show', $forecast->id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all">
                            <span>View Live Predictions</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- TAB 3: COMPLETED RESULTS (DESCENDING CHRONOLOGICAL) -->
    <div x-show="tab === 'completed'" class="space-y-4">
        @if($completedForecasts->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-slate-500">
                <i class="fa-solid fa-medal text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-base font-bold text-slate-800">No Completed Races Yet</h3>
                <p class="text-xs text-slate-500 mt-1">Results and scores will be published as soon as races finish.</p>
            </div>
        @else
            @foreach($completedForecasts as $forecast)
                @php
                    $comp = $forecast->competition;
                    $event = $comp?->event;
                    $flag = $event?->nat ? 'https://info.blob.core.windows.net/resources/bt/flags/' . strtolower($event->nat) . '.png' : null;
                    $winner = $forecast->winner;
                @endphp

                <div class="rounded-2xl border border-slate-200 bg-white p-5 hover:border-slate-300 transition-all shadow-2xs">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        <!-- Left Info -->
                        <div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                                @if($flag)
                                    <img src="{{ $flag }}" class="w-4 h-3 rounded-2xs inline-block">
                                @endif
                                <span class="font-bold text-slate-700">{{ $event?->short_description }}, {{ $event?->nat_long }}</span>
                                <span>•</span>
                                <span>Stage {{ $event?->event_series_no ?? '1' }}</span>
                                <span>•</span>
                                <span>{{ $forecast->submit_deadline_at?->format('d M Y') }}</span>
                            </div>

                            <h4 class="text-base sm:text-lg font-extrabold text-slate-900">
                                {{ $comp?->description }}
                            </h4>

                            <!-- Winner Highlight Badge -->
                            @if($winner)
                                <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-xs font-bold text-slate-800">
                                    <span class="text-amber-500 font-black">🥇 Winner:</span>
                                    @if($winner->flagUrl)
                                        <img src="{{ $winner->flagUrl }}" class="w-4 h-3 rounded-2xs inline-block">
                                    @endif
                                    <span class="font-extrabold text-slate-900">{{ $winner->name }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Right Stats & Actions -->
                        <div class="flex items-center gap-4 flex-wrap lg:justify-end">
                            <!-- Top Community Score -->
                            <div class="bg-slate-50 rounded-xl px-3.5 py-2 border border-slate-200/80 text-center">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Top Score</div>
                                <div class="text-sm font-extrabold text-slate-800">
                                    {{ $forecast->top_score ?? 0 }} <span class="text-[11px] font-normal text-slate-500">pts</span>
                                </div>
                            </div>

                            <!-- User's Score if Logged in -->
                            @auth
                                @if($forecast->auth_points)
                                    <div class="bg-sky-50 rounded-xl px-3.5 py-2 border border-sky-200 text-center">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-sky-700">Your Score</div>
                                        <div class="text-sm font-black text-sky-900">
                                            {{ $forecast->auth_points['total'] }} <span class="text-[11px] font-bold text-sky-700">pts</span>
                                            <span class="text-[10px] font-normal text-sky-600 block sm:inline">
                                                ({{ $forecast->auth_points['regular'] }}+{{ $forecast->auth_points['bonus'] }})
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endauth

                            <a href="{{ route('forecasts.show', $forecast->id) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all">
                                <span>Results & Scores</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
    function countdown(targetDate) {
        return {
            target: new Date(targetDate).getTime(),
            now: new Date().getTime(),
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0,
            isExpired: false,
            timer: null,
            init() {
                this.update();
                this.timer = setInterval(() => this.update(), 1000);
            },
            update() {
                this.now = new Date().getTime();
                const diff = this.target - this.now;
                if (diff <= 0) {
                    this.isExpired = true;
                    clearInterval(this.timer);
                    return;
                }
                this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
            },
            pad(num) {
                return num < 10 ? '0' + num : num;
            }
        };
    }
</script>
@endsection
