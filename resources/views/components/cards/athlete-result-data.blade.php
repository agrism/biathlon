@php
    /** @var \App\Models\EventCompetitionResult $eventCompetionResult */
@endphp

@if(isset($eventCompetionResult) && $eventCompetionResult->rank !== null)
    @php
        $misses = $eventCompetionResult->shooting_total ?? '0';
        $hasMisses = is_numeric($misses) && (int)$misses > 0;
    @endphp
    <div class="inline-flex items-center gap-1.5 text-[11px] mt-1">
        <span class="px-1.5 py-0.5 rounded bg-slate-800 text-white font-bold text-[10px]" title="Finish Rank">
            #{{ $eventCompetionResult->rank }}
        </span>
        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded {{ $hasMisses ? 'bg-rose-50 text-rose-800 border border-rose-200/70' : 'bg-slate-100 text-slate-700' }} font-medium" title="Penalties / Shooting Misses">
            <i class="fa-solid fa-circle-xmark {{ $hasMisses ? 'text-rose-500' : 'text-slate-400' }} text-[10px]"></i>
            <span>{{ $misses }}</span>
        </span>
        @if($eventCompetionResult->behind)
            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-normal" title="Time Behind Leader">
                {{ str_starts_with($eventCompetionResult->behind, '+') ? $eventCompetionResult->behind : '+'.$eventCompetionResult->behind }}
            </span>
        @endif
    </div>
@endif
