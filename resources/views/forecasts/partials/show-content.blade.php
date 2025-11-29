

<div class="px-2 py-2">
    <div id="selected-athletes">
        <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
            {!!$forecast?->competition->getTitle()!!}</h1>
        @include('forecasts.partials.user-selected-athletes')

        @if(!$forecast->final_data->results)

        <h2 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
            Bid summary
        </h2>

        <table border="1" style="border-collapse: collapse;"
               class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead>
            <tr>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    User
                </th>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    Gold medal
                </th>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    Silver medal
                </th>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    Bronze medal
                </th>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    4th place
                </th>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    5th place
                </th>
                <th scope="col"
                    class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                    6th place
                </th>
            </tr>
            </thead>
            <tbody>

            @foreach($forecast->final_data->users as $user)
                <tr class="odd:bg-white even:bg-gray-100">
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                        <strong>{{$user->name}}</strong>
                    </td>

                    @foreach($user->getAthletes() as $athlete)
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium @if(!in_array($athlete->tempId, $startingUserTempIds ??[])) bg-red-100 @endif">
                            @if($forecast->submit_deadline_at->gt(now()) && $athlete->isHidden)
                                <div class="flex justify-center items-center w-full h-full text-lg">
                                    <x-tooltip text="This prediction is hidden by owner">
                                        <i class="fa fa-eye-slash text-gray-400"></i>
                                    </x-tooltip>
                                </div>
                            @else
                                <img src="{{$athlete->flagUrl}}"
                                     style="height:20px;display:inline-block;"
                                >&nbsp{{$athlete->name}}</a>
                                <x-cards.athlete-stat :athlete="$athlete"></x-cards.athlete-stat>
                            @endif

                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>

        @else

            <h2 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
                Bid results
            </h2>

            <table border="1" style="border-collapse: collapse;"
                   class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead>
                <tr>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        User
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        Gold medal
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        Silver medal
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        Bronze medal
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        4th place
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        5th place
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        6th place
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        Earned points
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        Bonus points
                    </th>
                    <th scope="col"
                        class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        Total
                    </th>
                </tr>
                </thead>
                <tbody>

                @foreach([[0,6], [5,6]] as $slices)
                <tr class="odd:bg-white even:bg-gray-100 @if($slices[0] != 0) opacity-30 @endif">
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                        <strong>IBU (@if($slices[0] == 0) 1-6 @else 7-12 @endif)</strong>
                    </td>

                    @foreach(collect($forecast->final_data->results)->slice($slices[0], $slices[1])->toArray() as $index => $athlete)
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            <img src="{{$athlete->flagUrl}}" style="height:20px;display:inline-block;">&nbsp;<strong>{{$athlete->name}}</strong>
                            <x-cards.athlete-stat :athlete="$athlete"></x-cards.athlete-stat>
                        </td>
                    @endforeach
                </tr>
                @endforeach

                @foreach($forecast->final_data->users as $user)
                    <tr class="odd:bg-white even:bg-gray-100">
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            {{$user->name}}
                        </td>
                        @foreach($user->getAthletes() as $index => $athlete)
                            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium  @if(!in_array($athlete->tempId, $startingUserTempIds ??[])) bg-red-100 @endif">
                                <img src="{{$athlete->flagUrl}}" style="height:20px;display:inline-block;">&nbsp;
                                @if(isset($user->pointDetails[$index]) && is_array($arr = $user->pointDetails[$index]))
                                    @php
                                    $tooltipText = collect($arr)->map(function(\App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\PointValueObject $point): string {
                                        if($point->type == \App\Enums\Forecast\AwardPointEnum::BONUS_POINT){
                                            return 'Bonus: '. $point->value;
                                        }
                                        if($point->type == \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT){
                                            return 'Regular: '. $point->value;
                                        }
                                        return '';
                                    })->join(', ');
                                    @endphp
                                    <x-tooltip :text="$tooltipText">
                                        {{$athlete->name}}
                                    </x-tooltip>
                                @else
                                    {{$athlete->name}}
                                @endif
                                <x-cards.athlete-stat :athlete="$athlete"></x-cards.athlete-stat>
                            </td>
                        @endforeach

                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium" style="text-align:center;">
                            {{ $regularPoints = $user->getPointsByType(type: \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT)}}
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium" style="text-align:center;">
                            {{ $bonusPoints = $user->getPointsByType(type: \App\Enums\Forecast\AwardPointEnum::BONUS_POINT)}}
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium" style="text-align:center;">
                            {{$regularPoints + $bonusPoints}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endif
    </div>
</div>
