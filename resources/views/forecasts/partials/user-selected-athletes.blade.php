@if(auth()->check())
    <table style="width: 100%;table-layout: fixed;">
        <tbody>
        <tr>
            @foreach($forecast->final_data->getUserByUserModel(auth()?->user())->getAthletes() ?? [] as $index => $athlete)

                <td style="justify-content: center;position: relative;">

                    <div
                        style=";justify-content: center; border: 0px solid lightgrey;border-radius: 10px;padding: 5px;min-height: 100%;">

                        @if($athlete->name)
                            <strong style="font-size:32px;">
                                {{\App\Enums\RankEnum::tryFrom($index+1)->getMedal()}}
                            </strong>
                            <div class="px-2 py-2" style="height: 100%;">
                                {!! \App\Helpers\RemoteImageRenderHelper::instance()->getImageTag(url: $athlete->getModel()->photo_uri, attributes: 'width="200" height="200"') !!}
                            </div>
                            <div class="px-2 py-2 text-center">

                                @if(in_array($athlete->id, $favoriteAthleteIds ?? []))
                                    {{\App\Enums\FavoriteIconEnum::ENABLED->value}}
                                @else
                                    {{\App\Enums\FavoriteIconEnum::DISABLED->value}}
                                @endif

                                {{$athlete->name}}
                            </div>

                            <div class="px-2 py-2 text-center text-sm">
                                <img src="{{$athlete->flagUrl}}" style="height:20px;display:inline-block;">&nbsp;
                                <span style="line-height: 20px;">{{trim($athlete->getModel()?->nat)}}</span>
                            </div>


                            <div class="px-2 py-2">
                                <table border="1" style="border-collapse: collapse;"
                                       class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                    <thead>
                                    <tr>
                                        <th scope="col"
                                            class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            Speed
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            Stand.
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            Prone
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="odd:bg-white even:bg-gray-100">
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">0%</td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">90%</td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">90%</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-2 py-2 text-center cursor-pointer">
                                <form><input type="checkbox"> Private</form>
                            </div>
                            {{--                    @if($forecast->submit_deadline_at->gt(now()))--}}
                            <div class="px-2 py-2 text-center">
                                <x-buttons.button
                                    hx-get="{{route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index])}}"
                                    hx-target="#selected-athletes"
                                >
                                    Edit
                                </x-buttons.button>
                            </div>
                            {{--                    @endif--}}
                        @else
                            <div class="px-2 py-2 text-center">
                                <strong style="font-size:32px;">
                                    {{\App\Enums\RankEnum::tryFrom($index+1)->getMedal()}}
                                </strong>
                                <div
                                    class="text-center">{{\App\Helpers\NumberHelper::instance()->ordinal($index+1)}}</div>
                                @if(auth()->check())
                                  @if($forecast->submit_deadline_at->gt(now()))
                                    <x-buttons.button
                                        class="my-3"
                                        hx-get="{{route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index])}}"
                                        hx-target="#selected-athletes"
                                    >
                                        Choose Athlete
                                    </x-buttons.button>
                                  @endif
                                @else
                                    <x-buttons.button>
                                        <a href="{{route('login')}}">Login first</a>
                                    </x-buttons.button>
                                @endif
                            </div>

                        @endif

                    </div>

                </td>
            @endforeach
        </tr>
        </tbody>
    </table>
@endif
