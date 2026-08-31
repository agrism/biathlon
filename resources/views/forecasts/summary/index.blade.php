@extends('layouts.admin', ['heading' => ''])

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

    @foreach($seasons as $index => $season)
        <div class="mb-12" x-data="{ showDetails: false }">
            <!-- Season Header & Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 text-white p-5 rounded-2xl shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-400/30 flex items-center justify-center text-amber-400 font-black text-lg shadow-inner">
                        🏆
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-white">
                            {{ data_get($season, 'title') }}
                        </h2>
                        <span class="text-xs text-sky-300 font-medium">Official World Cup Season Matrix</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showDetails = !showDetails"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all border"
                        :class="showDetails ? 'bg-sky-500 text-white border-sky-400 shadow-sm' : 'bg-white/10 text-slate-200 border-white/10 hover:bg-white/20'"
                    >
                        <i class="fa-solid fa-list-ol text-xs"></i>
                        <span x-text="showDetails ? 'Hide Split Points' : 'Show Split Points (Reg+Bon)'"></span>
                    </button>
                </div>
            </div>

            <!-- Leaderboard Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div id="totals" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="sticky left-0 bg-slate-50 z-20 px-4 py-3 min-w-[140px] shadow-sm">
                                    Player
                                </th>
                                @foreach(array_slice($season['data']['events'] ?? [], 1, -4) as $eventTitle)
                                    <th scope="col" class="px-2.5 py-3 min-w-[90px] text-center font-bold">
                                        {{ $eventTitle }}
                                    </th>
                                @endforeach
                                <th scope="col" class="px-3 py-3 text-center bg-slate-100/80 font-extrabold text-slate-700">Reg.</th>
                                <th scope="col" class="px-3 py-3 text-center bg-amber-50/80 font-extrabold text-amber-800">Bonus</th>
                                <th scope="col" class="px-4 py-3 text-center bg-sky-100/80 font-black text-sky-900">Total</th>
                                <th scope="col" class="px-3 py-3 text-right bg-slate-100/80 font-bold text-slate-600">Diff</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($season['data']['users'] ?? [] as $i => $user)
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $i === 0 ? 'bg-amber-50/20' : '' }}">
                                    <!-- Sticky Player Name & Rank -->
                                    <td class="sticky left-0 bg-white z-10 px-4 py-3 whitespace-nowrap font-bold text-slate-900 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            @if($i === 0)
                                                <span class="w-6 h-6 rounded-full bg-amber-400 text-amber-950 flex items-center justify-center text-xs font-black shadow-xs">1</span>
                                            @elseif($i === 1)
                                                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-800 flex items-center justify-center text-xs font-bold shadow-xs">2</span>
                                            @elseif($i === 2)
                                                <span class="w-6 h-6 rounded-full bg-amber-700 text-amber-100 flex items-center justify-center text-xs font-bold shadow-xs">3</span>
                                            @else
                                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-medium">{{ $i + 1 }}</span>
                                            @endif
                                            <span>{{ $user['name'] ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <!-- Event Columns -->
                                    @foreach($user['events'] as $userEvent)
                                        <td class="px-2 py-2 whitespace-nowrap text-center">
                                            <div
                                                hx-get="{{ route('forecasts.summary.user-event', ['userId' => $user['id'] ?? 'y', 'eventId' => $userEvent['eventId'] ?? 'x']) }}"
                                                hx-target="#user-event"
                                                class="cursor-pointer inline-flex flex-col items-center justify-center py-1 px-2 rounded-lg hover:bg-sky-50 transition-colors group"
                                            >
                                                @php $isWinner = ($userEvent['winner'] ?? false) == true; @endphp
                                                <span class="font-bold text-xs {{ $isWinner ? 'px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 shadow-xs' : 'text-slate-700 group-hover:text-sky-600' }}">
                                                    @if($isWinner)<i class="fa-solid fa-crown text-[10px] text-amber-500 mr-0.5"></i>@endif
                                                    {{ ($userEvent['regular'] ?? 0) + ($userEvent['bonus'] ?? 0) }}
                                                </span>
                                                <span x-show="showDetails" class="text-[10px] text-slate-400 font-medium mt-0.5 block">
                                                    {{ $userEvent['regular'] ?? 0 }}+{{ $userEvent['bonus'] ?? 0 }}
                                                </span>
                                            </div>
                                        </td>
                                    @endforeach

                                    <!-- Regular Points -->
                                    <td class="px-3 py-3 whitespace-nowrap text-center font-bold text-slate-700 bg-slate-50/50">
                                        {{ $user['total']['regular'] ?? 0 }}
                                    </td>

                                    <!-- Bonus Points -->
                                    <td class="px-3 py-3 whitespace-nowrap text-center font-bold text-amber-600 bg-amber-50/30">
                                        +{{ $user['total']['bonus'] ?? 0 }}
                                    </td>

                                    <!-- Total Points -->
                                    <td class="px-4 py-3 whitespace-nowrap text-center font-black text-base text-sky-600 bg-sky-50/60">
                                        {{ $user['total']['total'] ?? 0 }}
                                    </td>

                                    <!-- Diff Column -->
                                    <td class="px-3 py-3 whitespace-nowrap text-right bg-slate-50/50">
                                        @if($i === 0)
                                            <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-xs font-bold">Leader</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 text-xs font-semibold">
                                                -{{ $user['total']['diff'] ?? 0 }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Event Inspection Container -->
            @if($index === 0)
                <div id="user-event" class="mt-6"></div>
            @endif
        </div>
    @endforeach
@endsection
