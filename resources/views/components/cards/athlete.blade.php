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
                        <x-buttons.button
                            class="my-3"
                            hx-get="{{route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index])}}"
                            hx-target="#selected-athletes"
                        >
                            Edit
                        </x-buttons.button>
                    @else
                        <x-buttons.button
                            class="my-3"
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

