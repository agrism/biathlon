@php
    /** @var \App\Models\EventCompetitionResult $eventCompetionResult */
@endphp

@if(isset($eventCompetionResult) && $eventCompetionResult->rank !== null)
    <div class="inline-flex items-center gap-1.5 text-[11px] mt-1">
        <span class="px-1.5 py-0.5 rounded bg-slate-800 text-white font-bold text-[10px]" title="Finish Rank">
            #{{ $eventCompetionResult->rank }}
        </span>
        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-medium" title="Penalties / Shooting Total">
            🎯 {{ $eventCompetionResult->shooting_total ?? '0' }}
        </span>
        @if($eventCompetionResult->behind)
            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-normal" title="Time Behind Leader">
                +{{ $eventCompetionResult->behind }}
            </span>
        @endif
    </div>
@endif
