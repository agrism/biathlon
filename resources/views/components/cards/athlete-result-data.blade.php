@php
    /** @var \App\Models\EventCompetitionResult $eventCompetionResult */
    $showBadgeTitles = $showBadgeTitles ?? true;
@endphp

@if(isset($eventCompetionResult) && $eventCompetionResult->rank !== null)
    @php
        $misses = $eventCompetionResult->shooting_total ?? '0';
    @endphp
    <div class="inline-flex items-center gap-1.5 text-[11px] mt-1">
        <span class="px-1.5 py-0.5 rounded bg-slate-800 text-white font-bold text-[10px]" @if($showBadgeTitles) title="Finish Rank" @endif>
            #{{ $eventCompetionResult->rank }}
        </span>
        <span class="w-[19px] h-[19px] min-w-[19px] rounded-full bg-slate-900 text-white border border-white shadow-2xs font-bold {{ strlen((string)$misses) > 1 ? 'text-[9px]' : 'text-[10px]' }} flex items-center justify-center leading-none" @if($showBadgeTitles) title="Penalties / Shooting Misses" @endif>
            {{ $misses }}
        </span>
        @if($eventCompetionResult->behind)
            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-normal" @if($showBadgeTitles) title="Time Behind Leader" @endif>
                {{ str_starts_with($eventCompetionResult->behind, '+') ? $eventCompetionResult->behind : '+'.$eventCompetionResult->behind }}
            </span>
        @endif
    </div>
@endif
