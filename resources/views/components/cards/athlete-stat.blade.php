@php
    /** @var \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject $athlete */
@endphp

@if(isset($athlete))
    <small class="text-gray-500 flex justify-center">
        <div class=" flex items-center gap-1">
            @if(!$athlete->stats?->statsSkiKmb && !$athlete->stats?->statShootingProne && !$athlete->stats?->statShootingStanding )
            @else
                <x-tooltip text="(Skiing, shooting prone/standing)">
                    ({!!$athlete->stats?->statsSkiKmb === null ? '-' : '-'.$athlete->stats->statsSkiKmb.'<span class="text-xs">s/km</span>' !!}
                    {{$athlete->stats?->statShootingProne === null ? '-' : $athlete->stats->statShootingProne.'%' }}/{{$athlete->stats?->statShootingStanding === null ? '-' : $athlete->stats->statShootingStanding.'%' }})
                </x-tooltip>
            @endif
        </div>
    </small>
@endif
