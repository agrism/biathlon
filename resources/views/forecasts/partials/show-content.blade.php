

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
                        {{$user->name}}
                    </td>

                    @foreach($user->getAthletes() as $athlete)
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            <img src="{{$athlete->flagUrl}}"
                                 style="height:20px;display:inline-block;">&nbsp;{{$athlete->name}}</a>
                            <small class="text-gray-500" style="display:block;text-align:center;">(-5% 90% 90%)</small>
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

                <tr class="odd:bg-white even:bg-gray-100">
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                        <strong>IBU</strong>
                    </td>

                    @foreach($forecast->final_data->results as $athlete)
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            <img src="{{$athlete->flagUrl}}" style="height:20px;display:inline-block;">&nbsp;<strong>{{$athlete->name}}</strong>
                        </td>
                    @endforeach

                </tr>

                @foreach($forecast->final_data->users as $user)
                    <tr class="odd:bg-white even:bg-gray-100">
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            {{$user->name}}
                        </td>
                        @foreach($user->getAthletes() as $athlete)
                            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                <img src="{{$athlete->flagUrl}}" style="height:20px;display:inline-block;">&nbsp;{{$athlete->name}}
                                <small class="text-gray-500" style="display:block;text-align:center;"></small>
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
