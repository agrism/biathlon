@php
    /** @var \App\Models\EventCompetitionResult $eventCompetionResult */
@endphp

@if(isset($eventCompetionResult))
    <small class="text-gray-500 flex justify-center">
        <div class=" flex items-center gap-1">
            @if($eventCompetionResult->total_time !== null)
                <x-tooltip text="(Shooting, behind)">
                    ({{$eventCompetionResult->shooting_total}}) ({{$eventCompetionResult->behind}})
                </x-tooltip>
            @endif
        </div>
    </small>
@endif
