<div class="border border-black-100 p-4">
    <div class="flex flex-col justify-between h-full">
        <div class="" style="min-height: 200px;">
            <strong style="font-size:32px;">
                {{\App\Enums\RankEnum::tryFrom($index+1)->getMedal()}}
            </strong>
            <div class="flex justify-center w-full">
                @php
                    /** @var \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject $athlete */
                @endphp
                @if($athlete->getModel())
                    {!! \App\Helpers\RemoteImageRenderHelper::instance()->getImageTag(url: $athlete->getModel()->photo_uri, attributes: ' width="200" height="200"') !!}
                @endif
            </div>
            <div class="px-2 py-2 text-center">
                @if($athlete->name)
                @if(in_array($athlete->id, $favoriteAthleteIds ?? []))
                    {{\App\Enums\FavoriteIconEnum::ENABLED->value}}
                @else
                    {{\App\Enums\FavoriteIconEnum::DISABLED->value}}
                @endif
                {{$athlete->name}}
                @endif
            </div>

            <div class="flex items-center justify-center gap-1 my-1">
                <img src="{{$athlete->flagUrl}}" style="height:20px;">&nbsp;
                <span class="ml-2">{{trim($athlete->getModel()?->nat)}}</span>
            </div>

            <div>
                @if($athlete->name)
                    <x-cards.athlete-stat :athlete="$athlete"></x-cards.athlete-stat>
                @endif
            </div>

        </div>
        <div class="text-center">
            @if(auth()->check())
                @if($forecast->submit_deadline_at->gt(now()))
                    @if($athlete->name)
                        <div class="flex justify-between items-center">
                            <!-- With styling example -->
                            @if($index !== 0)
                            <i class="fas fa-circle-arrow-up text-green-700 text-xl my-3 cursor-pointer pr-1"
                               hx-get="{{route('forecasts.select-athlete.place.move.up-down', ['id' => $forecast->id, 'place' =>$index, 'direction' => \App\Enums\MoveDirectionEnum::UP->value])}}"
                               hx-target="#selected-athletes"
                            ></i>
                            @else
                                <i class="my-3"></i>
                            @endif
                            <x-buttons.button
                                class="my-3"
                                hx-get="{{route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index])}}"
                                hx-target="#selected-athletes"
                            >
                                Edit
                            </x-buttons.button>
                            @if($index < 5)
                            <i class="fas fa-circle-arrow-down text-red-500 text-xl my-3 cursor-pointer pl-1"
                               hx-get="{{route('forecasts.select-athlete.place.move.up-down', ['id' => $forecast->id, 'place' =>$index, 'direction' => \App\Enums\MoveDirectionEnum::DOWN->value])}}"
                               hx-target="#selected-athletes"
                            ></i>
                            @else
                                <i class="my-3"></i>
                            @endif
                        </div>

                    @else
                        <x-buttons.button
                            class="my-3 md:text-sm"
                            hx-get="{{route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index])}}"
                            hx-target="#selected-athletes"
                        >
                            Choose Athlete
                        </x-buttons.button>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>

