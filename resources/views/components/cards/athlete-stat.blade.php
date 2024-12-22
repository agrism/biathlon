@php
    /** @var \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject $athlete */
@endphp

@if(isset($athlete))
    <small class="text-gray-500 flex justify-center">
        <div class=" flex items-center gap-1">
            @if(!$athlete->stats?->statsSkiKmb && !$athlete->stats?->statShootingProne && !$athlete->stats?->statShootingStanding )
            @else
            <span>
                ({{$athlete->stats?->statsSkiKmb === null ? '-' : $athlete->stats->statsSkiKmb.'km/h' }}
                {{$athlete->stats?->statShootingProne === null ? '-' : $athlete->stats->statShootingProne.'%' }}
                {{$athlete->stats?->statShootingStanding === null ? '-' : $athlete->stats->statShootingStanding.'%' }})
            </span>
            <span class="relative group">
                <span
                    class="w-4 h-4 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-sm font-semibold cursor-pointer"
                    title="(Skiing, shooting prone, shooting standing)"
                >
                    i
                </span>
                <span
                   class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition bg-gray-800 text-white text-sm rounded py-1 px-2 -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap"
                >
                    (Skiing, shooting prone, shooting standing)
                </span>
            </span>
            @endif
        </div>
    </small>
@endif
