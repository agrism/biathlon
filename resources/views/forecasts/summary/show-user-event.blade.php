<div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 mb-6">
    <div class="mb-5 pb-3 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>{{ $user->name }}: {{ $event->title() }}</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                <tr>
                    @if(auth()->check())
                        <th class="px-3 py-3 text-center">
                            <x-tooltip text="Is your prediction submitted?">
                                <span>READY?</span>
                            </x-tooltip>
                        </th>
                    @endif
                    <th class="px-4 py-3 font-black">Competition</th>
                    <th class="px-4 py-3 text-right font-black">Points</th>
                    <th class="px-4 py-3 text-right font-black">Bonus</th>
                    <th class="px-4 py-3 text-right font-black">Total</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                @php
                    $totalRegularPoints = 0;
                    $totalBonusPoints = 0;
                    $totalPoints = 0;
                @endphp
                @foreach($event->competitions->sortBy('start_time') as $competition)
                    <tr class="hover:bg-sky-50/40 transition-colors duration-150">
                        @if(auth()->check())
                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                <x-tooltip :text="$competition->forecast?->isAllAthletesSubmitted ? 'You are good!' : 'Submit your predictions!'">
                                    <div
                                        hx-get="{{ route('forecasts.submit-status', ['id' => $competition->forecast->id]) }}"
                                        hx-trigger="getIsUserCompletedForecastData-{{ $competition->forecast->id }} from:body"
                                    >
                                        <x-status-is-customer-completed-forecast-data :isCompleted="$competition->forecast?->isAllAthletesSubmitted" />
                                    </div>
                                </x-tooltip>
                            </td>
                        @endif

                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-800">
                            <div class="flex items-center justify-between gap-3">
                                @if($competition->forecast)
                                    <a
                                        hx-get="{{ route('forecasts.show', ['id' => $competition->forecast->id, 'showContentOnly' => 1]) }}?user_id={{ $user->id }}"
                                        hx-target="#forecast"
                                        class="hover:text-sky-600 hover:underline font-bold transition-colors cursor-pointer"
                                    >
                                        {!! $competition->getTitle() !!}
                                    </a>
                                @else
                                    <span class="font-bold text-slate-700">{!! $competition->getTitle() !!}</span>
                                @endif

                                @if($competition->results->count())
                                    <a
                                        class="cursor-pointer"
                                        hx-get="{{ route('competitions.show', ['id' => $competition->race_remote_id, 'showContentOnly' => 1]) }}"
                                        hx-target="#results"
                                    >
                                        @if($competition->results_handled_at)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200/60">
                                                Finish Protocol
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-bold border border-amber-200/60">
                                                Start List
                                            </span>
                                        @endif
                                    </a>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-slate-700">
                            {{ $regular = $competition->forecast?->awards->where('type', \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT)->first()?->points ?? 0 }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-slate-700">
                            {{ $bonus = $competition->forecast?->awards->where('type', \App\Enums\Forecast\AwardPointEnum::BONUS_POINT)->first()?->points ?? 0 }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right font-black text-sky-600">
                            {{ $total = $regular + $bonus }}
                        </td>

                        @php
                            $totalRegularPoints += $regular;
                            $totalBonusPoints += $bonus;
                            $totalPoints += $total;
                        @endphp
                    </tr>
                @endforeach
            </tbody>

            <tfoot class="bg-slate-50/90 border-t-2 border-slate-200 font-bold text-slate-900">
                <tr>
                    @if(auth()->check())
                        <th class="px-3 py-3"></th>
                    @endif
                    <th class="px-4 py-3 text-right uppercase text-xs tracking-wider text-slate-500">Stage Total</th>
                    <th class="px-4 py-3 text-right font-black text-slate-800">{{ $totalRegularPoints }}</th>
                    <th class="px-4 py-3 text-right font-black text-slate-800">{{ $totalBonusPoints }}</th>
                    <th class="px-4 py-3 text-right font-black text-sky-700 text-base">{{ $totalPoints }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div id="forecast" class="mt-4"></div>
    <div id="results" class="mt-4"></div>
</div>
