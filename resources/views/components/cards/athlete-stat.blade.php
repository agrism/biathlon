@php
    /** @var \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject $athlete */
@endphp

@if(isset($athlete))
    <small class="text-gray-500" style="display:block;text-align:center;"
           title="Skiing, shooting prone, shooting standing">
        <div class="relative inline-block group">
        <span>
            ({{$athlete->stats?->statSkiing === null ? '-' : $athlete->stats->statSkiing.'%' }}
            {{$athlete->stats?->statShootingProne === null ? '-' : $athlete->stats->statShootingProne.'%' }}
            {{$athlete->stats?->statShootingStanding === null ? '-' : $athlete->stats->statShootingStanding.'%' }})
        </span>
            <span
                class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition bg-gray-800 text-white text-sm rounded py-1 px-2 -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap">
        (skiing, shooting prone, shooting standing)
    </span>
        </div>
    </small>
@endif
