@php
    /** @var \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject $athlete */
@endphp

@if(isset($athlete) && $athlete->stats)
    <div class="flex flex-wrap items-center justify-center gap-1.5 text-[11px]">
        @if($athlete->stats->statsSkiKmb !== null)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium" title="Skiing speed difference (s/km)">
                <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i>
                <span>-{{ $athlete->stats->statsSkiKmb }}<span class="text-[9px] text-slate-400">s/km</span></span>
            </span>
        @endif

        @if($athlete->stats->statShootingProne !== null || $athlete->stats->statShootingStanding !== null)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200/60" title="Shooting Accuracy (Prone / Standing)">
                <i class="fa-solid fa-bullseye text-emerald-600 text-[10px]"></i>
                <span>{{ $athlete->stats->statShootingProne ?? '-' }}% / {{ $athlete->stats->statShootingStanding ?? '-' }}%</span>
            </span>
        @endif
    </div>
@endif
