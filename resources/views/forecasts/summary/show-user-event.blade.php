<div>

    <div>
        <h3 class="mb-4 mt-4 text-lg font-extrabold leading-none tracking-tight text-gray-900  text-center ">{{$user->name}}: {{$event->title()}}</h3>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead>
            <tr>
                @if(auth()->check())
                    <th class="pl-1 py-2 text-start text-xs font-medium  text-gray-500 dark:text-neutral-500"><x-tooltip text="Is your bids submitted?"><span class="uppercase">Ready?</span></x-tooltip></th>
                @endif
                <th class="px-1 py-2 text-center text-xs font-medium  text-gray-500 uppercase dark:text-neutral-500">Competition</th>
                <th class="px-1 py-2 text-start text-xs font-medium  text-gray-500 uppercase dark:text-neutral-500">Points</th>
                <th class="px-1 py-2 text-start text-xs font-medium  text-gray-500 uppercase dark:text-neutral-500">Bonus</th>
                <th class="px-1 py-2 text-start text-xs font-medium  text-gray-500 uppercase dark:text-neutral-500">Total</th>
            </tr>
            </thead>

            <tbody>
            @php
                $totalRegularPoints = 0;
                $totalBonusPoints = 0;
                $totalPoints = 0;
            @endphp
            @foreach($event->competitions as $competition)

                <tr class="odd:bg-white even:bg-gray-100">
                    @if(auth()->check())
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-center">
                            <x-tooltip :text="$competition->forecast?->isAllAthletesSubmitted ? 'You are good!' : 'Common, you can do it!'">
                                <div
                                    hx-get="{{route('forecasts.submit-status', ['id' => $competition->forecast->id])}}"
                                    hx-trigger="getIsUserCompletedForecastData-{{$competition->forecast->id}}  from:body"
                                >
                                    <x-status-is-customer-completed-forecast-data :isCompleted="$competition->forecast?->isAllAthletesSubmitted"></x-status-is-customer-completed-forecast-data>
                                </div>
                            </x-tooltip>
                        </td>
                    @endif
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                        <a
                            hx-get="{{route('forecasts.show', ['id' => $competition->forecast->id, 'showContentOnly' => 1])}}"
                            hx-target="#forecast"
                            class="cursor-pointer"
                        >{!! $competition->getTitle() !!}</a>
                        @if($competition->results->count())
                            <a
                                class="text-blue-400 cursor-pointer"
                                hx-get="{{route('competitions.show', ['id' => $competition->race_remote_id, 'showContentOnly' => 1])}}"
                                hx-target="#results"
                            >
                                @if($competition->results_handled_at)
                                    <div class="float-right px-2 py-0.5 rounded bg-green-200 text-green-600 text-xs font-semibold">
                                        Finish protocol
                                    </div>
                                @else
                                    <div class="float-right px-2 py-0.5 rounded bg-yellow-200 text-yellow-600 text-xs font-semibold">
                                        Start list
                                    </div>
                                @endif

                            </a>
                        @endif
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-right">{{$regular = $competition->forecast->awards->where('type', \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT)->first()?->points ?? 0}}</td>
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-right">{{$bonus = $competition->forecast->awards->where('type', \App\Enums\Forecast\AwardPointEnum::BONUS_POINT)->first()?->points ?? 0}}</td>
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-right">{{$total = $regular + $bonus}}</td>
                    @php
                        $totalRegularPoints += $regular;
                        $totalBonusPoints += $bonus;
                        $totalPoints += $total;
                    @endphp
                </tr>

            @endforeach
            </tbody>
            <tfoot>
            <tr>
                @if(auth()->check())
                    <th class="px-1 py-2 text-end text-sm font-medium  text-gray-500 uppercase dark:text-neutral-500"></th>
                @endif
                <th class="px-1 py-2 text-end text-sm font-medium  text-gray-500 uppercase dark:text-neutral-500"></th>
                <th class="px-1 py-2 text-end text-sm font-medium  text-gray-500 uppercase dark:text-neutral-500">{{$totalRegularPoints}}</th>
                <th class="px-1 py-2 text-end text-sm font-medium  text-gray-500 uppercase dark:text-neutral-500">{{$totalBonusPoints}}</th>
                <th class="px-1 py-2 text-end text-sm font-medium  text-gray-500 uppercase dark:text-neutral-500">{{$totalPoints}}</th>
            </tr>
            </tfoot>
        </table>
    </div>


    <div id="forecast">
    </div>
    <div id="results">
    </div>
</div>
