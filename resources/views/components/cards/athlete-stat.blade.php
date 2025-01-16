@php
    /** @var \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject $athlete */
@endphp

@if(isset($athlete))
    <small class="text-gray-500 flex justify-center">
        <div class=" flex items-center gap-1">
            @if(!$athlete->stats?->statsSkiKmb && !$athlete->stats?->statShootingProne && !$athlete->stats?->statShootingStanding )
            @else
            <span class="relative group">
                ({!!$athlete->stats?->statsSkiKmb === null ? '-' : '-'.$athlete->stats->statsSkiKmb.'<span class="text-xs">s/km</span>' !!}
                {{$athlete->stats?->statShootingProne === null ? '-' : $athlete->stats->statShootingProne.'%' }}/{{$athlete->stats?->statShootingStanding === null ? '-' : $athlete->stats->statShootingStanding.'%' }})
                <span
                    class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition bg-gray-800 text-white text-sm rounded py-1 px-2 -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap"
                >
                (Skiing, shooting prone/standing)
                </span>
            </span>
            @endif
        </div>
    </small>
@endif
