<div class="space-y-6">
    <div id="selected-athletes">
        <!-- Competition Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 text-white rounded-2xl p-6 shadow-xl relative overflow-hidden mb-6">
            <div class="absolute -right-8 -bottom-8 opacity-10 text-9xl">
                <i class="fa-solid fa-person-skiing"></i>
            </div>
            
            <div class="relative z-10">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 rounded-full bg-sky-500/20 text-sky-300 border border-sky-400/30 text-xs font-bold uppercase tracking-wider">
                        {{ $forecast->competition->event->short_description ?? 'World Cup' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-white/10 text-slate-200 text-xs font-medium">
                        {{ $forecast->competition->discipline_remote_id }} &bull; {{ $forecast->competition->km ?? '' }}
                    </span>
                    @if($forecast->submit_deadline_at)
                        <span class="px-2.5 py-1 rounded-full {{ $forecast->submit_deadline_at->gt(now()) ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30' }} text-xs font-semibold">
                            <i class="fa-regular fa-clock mr-1"></i>
                            {{ $forecast->submit_deadline_at->gt(now()) ? 'Deadline: '.$forecast->submit_deadline_at->format('d M H:i') : 'Closed' }}
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                    {!! $forecast?->competition->getTitle() !!}
                </h1>
            </div>
        </div>

        <!-- 6-Place Prediction Podium Cards Grid -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Your Prediction Podium</h2>
                    <p class="text-xs text-slate-500">Select top 6 athletes before the race start deadline.</p>
                </div>
                <div class="text-xs font-semibold px-3 py-1 rounded-xl bg-slate-100 text-slate-700">
                    Rule: {{ $forecast->type?->name === 'FORECAST_DAINIS_SCHEMA' ? 'Dainis Matrix (Delta)' : 'Classic Places' }}
                </div>
            </div>

            @include('forecasts.partials.user-selected-athletes')
        </div>

        <!-- Bid Summary / Results Section -->
        @if(!$forecast->final_data->results)
            <!-- Bid Summary (Pre-Race) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-8">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-users text-sky-500"></i> All Predictions
                        </h3>
                        <p class="text-xs text-slate-400">Locked predictions will be revealed after race start.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3">Player</th>
                                <th scope="col" class="px-3 py-3">🥇 1st (Gold)</th>
                                <th scope="col" class="px-3 py-3">🥈 2nd (Silver)</th>
                                <th scope="col" class="px-3 py-3">🥉 3rd (Bronze)</th>
                                <th scope="col" class="px-3 py-3">4th Place</th>
                                <th scope="col" class="px-3 py-3">5th Place</th>
                                <th scope="col" class="px-3 py-3">6th Place</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($forecast->final_data->users as $user)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold uppercase border border-slate-200">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                            <span>{{ $user->name }}</span>
                                        </div>
                                    </td>

                                    @foreach($user->getAthletes() as $athlete)
                                        <td class="px-3 py-3 whitespace-nowrap @if(!in_array($athlete->tempId, $startingUserTempIds ?? [])) bg-rose-50/30 @endif">
                                            @if($forecast->submit_deadline_at->gt(now()) && $athlete->isHidden)
                                                <div class="flex items-center gap-1.5 text-xs text-slate-400 italic">
                                                    <i class="fa fa-eye-slash text-slate-400 text-xs"></i>
                                                    <span>Hidden</span>
                                                </div>
                                            @elseif($athlete->name)
                                                <div class="flex flex-col">
                                                    <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                                                        @if($athlete->flagUrl)
                                                            <img src="{{ $athlete->flagUrl }}" class="h-3.5 rounded-xs shadow-2xs inline-block">
                                                        @endif
                                                        <span class="truncate max-w-[120px]">{{ $athlete->getShortName() }}</span>
                                                    </div>
                                                    <x-cards.athlete-stat :athlete="$athlete"></x-cards.athlete-stat>
                                                </div>
                                            @else
                                                <span class="text-slate-300 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Bid Results (Post-Race) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-8">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-trophy text-amber-500"></i> Official Race & Forecast Results
                        </h3>
                        <p class="text-xs text-slate-400">Official IBU finish ranks compared against user forecasts.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3">Participant</th>
                                <th scope="col" class="px-3 py-3">🥇 1st</th>
                                <th scope="col" class="px-3 py-3">🥈 2nd</th>
                                <th scope="col" class="px-3 py-3">🥉 3rd</th>
                                <th scope="col" class="px-3 py-3">4th</th>
                                <th scope="col" class="px-3 py-3">5th</th>
                                <th scope="col" class="px-3 py-3">6th</th>
                                <th scope="col" class="px-3 py-3 text-center">Regular</th>
                                <th scope="col" class="px-3 py-3 text-center">Bonus</th>
                                <th scope="col" class="px-4 py-3 text-center font-extrabold text-sky-700 bg-sky-50/50">Total Pts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <!-- Official IBU Results Row -->
                            @foreach([[0,6], [6,6]] as $slices)
                                <tr class="bg-slate-900 text-white font-semibold {{ $slices[0] != 0 ? 'opacity-40 text-xs' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 text-xs font-bold uppercase">
                                            IBU {{ $slices[0] == 0 ? '(1–6)' : '(7–12)' }}
                                        </span>
                                    </td>

                                    @foreach(collect($forecast->final_data->results)->slice($slices[0], $slices[1])->toArray() as $index => $athlete)
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                @if($athlete->flagUrl)
                                                    <img src="{{ $athlete->flagUrl }}" class="h-3 rounded-xs inline-block">
                                                @endif
                                                <span class="font-bold text-white truncate max-w-[120px]">{{ $athlete->getShortName() }}</span>
                                            </div>
                                            <x-cards.athlete-result-data :eventCompetionResult="$isTeamDiscipline ? null : $forecast->competition->results->where('athlete_id', $athlete->id)->first()"></x-cards.athlete-result-data>
                                        </td>
                                    @endforeach
                                    <td colspan="3" class="px-3 py-3 text-center text-slate-400 text-xs italic">Official Ranks</td>
                                </tr>
                            @endforeach

                            <!-- User Forecast Rows -->
                            @foreach($forecast->final_data->users as $user)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold uppercase border border-slate-200">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                            <span>{{ $user->name }}</span>
                                        </div>
                                    </td>

                                    @foreach($user->getAthletes() as $index => $athlete)
                                        <td class="px-3 py-3 whitespace-nowrap @if(!in_array($athlete->tempId, $startingUserTempIds ?? [])) bg-rose-50/30 @endif">
                                            @if($athlete->name)
                                                <div class="flex flex-col">
                                                    <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                                                        @if($athlete->flagUrl)
                                                            <img src="{{ $athlete->flagUrl }}" class="h-3.5 rounded-xs shadow-2xs inline-block">
                                                        @endif
                                                        <span class="truncate max-w-[120px]">{{ $athlete->getShortName() }}</span>
                                                    </div>
                                                    <x-cards.athlete-result-data :eventCompetionResult="$isTeamDiscipline ? null : $forecast->competition->results->where('athlete_id', $athlete->id)->first()"></x-cards.athlete-result-data>
                                                </div>
                                            @else
                                                <span class="text-slate-300 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-3 py-3 whitespace-nowrap text-center font-bold text-slate-700">
                                        {{ $regularPoints = $user->getPointsByType(type: \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT) }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-center font-bold text-amber-600">
                                        +{{ $bonusPoints = $user->getPointsByType(type: \App\Enums\Forecast\AwardPointEnum::BONUS_POINT) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center font-black text-base text-sky-600 bg-sky-50/40">
                                        {{ $regularPoints + $bonusPoints }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
