<div class="border border-black-100 p-4">
    @if($athlete->name)
        <strong style="font-size:32px;">
            {{\App\Enums\RankEnum::tryFrom($index+1)->getMedal()}}
        </strong>
        <div class="flex justify-center w-full">
            {!! \App\Helpers\RemoteImageRenderHelper::instance()->getImageTag(url: $athlete->getModel()->photo_uri, attributes: ' width="200" height="200"') !!}
        </div>
        <div class="px-2 py-2 text-center">

            @if(in_array($athlete->id, $favoriteAthleteIds ?? []))
                {{\App\Enums\FavoriteIconEnum::ENABLED->value}}
            @else
                {{\App\Enums\FavoriteIconEnum::DISABLED->value}}
            @endif

            {{$athlete->name}}
        </div>

        <div class="flex items-center justify-center gap-1">
            <img src="{{$athlete->flagUrl}}" style="height:20px;">&nbsp;
            <span class="ml-2">{{trim($athlete->getModel()?->nat)}}</span>
        </div>

        <div class="px-2 py-2 text-center cursor-pointer">
            <form><input type="checkbox"> Private</form>
        </div>
        @if($forecast->submit_deadline_at->gt(now()))
            <div class="px-2 py-2 text-center">
                <x-buttons.button
                    hx-get="{{route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index])}}"
                    hx-target="#selected-athletes"
                >
                    Edit
                </x-buttons.button>
            </div>
        @endif
    @else
        <div class="px-2 py-2 text-center flex flex-col justify-between border border-blue-500 h-full">
            <div>
                <strong style="font-size:32px;">
                    {{\App\Enums\RankEnum::tryFrom($index+1)->getMedal()}}
                </strong>
                <div class="text-center">
                    {{\App\Helpers\NumberHelper::instance()->ordinal($index+1)}}
                </div>
            </div>
            <div>
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
        </div>

    @endif

</div>

