<div class="px-2 py-2">
    <div id="selected-athletes">

        @if(!$forecast->competition?->results->count())

        @include('forecasts.partials.user-selected-athletes')


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

            @foreach($forecast->submittedData as $submittedData)
                <tr class="odd:bg-white even:bg-gray-100">
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                        {{$submittedData->user->name}}
                    </td>

                    @foreach($submittedData->submitted_data?->athletes ?? [] as $athlete)
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            <img src="{{$athlete->flag_uri}}"
                                 style="height:20px;display:inline-block;">&nbsp;{{$athlete->given_name}} {{$athlete->family_name}}</a>
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

                    @foreach($forecast->competition?->results ?? [] as $result)
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            <img src="{{$result->athlete->flag_uri}}" style="height:20px;display:inline-block;"><strong>{{$result->athlete->given_name}} {{$result->athlete->family_name}}</strong>
                        </td>
                    @endforeach

                </tr>

                @foreach([] as $i)
                    <tr class="odd:bg-white even:bg-gray-100">
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">Dainis</a>

                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            {{--                    <img src="athletes_files/nor.png" style="height:20px;display:inline-block;">--}}
                            Margita Parce
                            <small class="text-gray-500" style="display:block;text-align:center;">(0)</small>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            {{--                    <img src="athletes_files/nor.png"  style="height:20px;display:inline-block;">--}}
                            Madara Līduma
                            <small class="text-gray-500" style="display:block;text-align:center;">(0)</small>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">

                            {{--                        <img src="athletes_files/nor.png" style="height:20px;display:inline-block;">--}}
                            Ieva Cederštrēma
                            <small class="text-gray-500" style="display:block;text-align:center;">(0)</small>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            {{--                    <img src="athletes_files/nor.png" style="height:20px;display:inline-block;">--}}
                            Vineta Laiva
                            <small class="text-gray-500" style="display:block;text-align:center;">(0)</small>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                            {{--                    <img src="athletes_files/nor.png"   style="height:20px;display:inline-block;">--}}
                            Anžela Brice
                            <small class="text-gray-500" style="display:block;text-align:center;">(0)</small>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">

                            {{--                        <img src="athletes_files/nor.png"  style="height:20px;display:inline-block;">--}}
                            Jeanmonnot
                            <small class="text-gray-500" style="display:block;text-align:center;">(+5)</small>

                        </td>

                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium" style="text-align:center;">
                            5
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium" style="text-align:center;">
                            0
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium" style="text-align:center;">
                            5
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endif
    </div>
</div>
