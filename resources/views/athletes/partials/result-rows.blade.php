@foreach($results as $index => $result)
    <tr class="hover:bg-slate-50/80 transition-colors">
        <td class="px-4 py-2.5 whitespace-nowrap">
            @if($result->rank == 1)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">🥇 1st</span>
            @elseif($result->rank == 2)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-slate-200 text-slate-800 border border-slate-300">🥈 2nd</span>
            @elseif($result->rank == 3)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-amber-200 text-amber-950 border border-amber-400">🥉 3rd</span>
            @elseif($result->rank)
                <span class="font-bold text-slate-700">#{{ $result->rank }}</span>
            @elseif($result->irm)
                @php
                    $irmClass = match($result->irm) {
                        'DSQ' => 'bg-rose-100 text-rose-800 border-rose-200 font-extrabold',
                        'DNF' => 'bg-amber-100 text-amber-800 border-amber-200 font-semibold',
                        'DNS' => 'bg-slate-100 text-slate-600 border-slate-200 font-semibold',
                        'LAP' => 'bg-purple-100 text-purple-800 border-purple-200 font-semibold',
                        default => 'bg-slate-100 text-slate-600 border-slate-200 font-semibold'
                    };
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs border {{ $irmClass }}">{{ $result->irm }}</span>
            @else
                <span class="text-xs text-slate-400 font-semibold">-</span>
            @endif
        </td>
        <td class="px-4 py-2.5 whitespace-nowrap font-medium text-slate-800">
            @if($result->competition)
                @php
                    $classification = $result->competition->event?->event_classification_id;
                    $level = $result->competition->event?->level;

                    $badge = match(true) {
                        $classification === 'BTSWRLOG' => ['label' => 'Olympics', 'class' => 'bg-indigo-100 text-indigo-900 border-indigo-200 font-extrabold'],
                        $classification === 'BTSWRLCH' => ['label' => 'World Champ', 'class' => 'bg-rose-100 text-rose-900 border-rose-200 font-extrabold'],
                        $classification === 'BTSWRLCP' || $level === 1 => ['label' => 'World Cup', 'class' => 'bg-sky-100 text-sky-800 border-sky-200 font-bold'],
                        $classification === 'BTSIBUCP' || $level === 2 || str_contains((string)$classification, 'IBUCP') || str_contains((string)$classification, 'CEUCH') => ['label' => 'IBU Cup', 'class' => 'bg-purple-100 text-purple-800 border-purple-200 font-bold'],
                        $level === 3 || str_contains((string)$classification, 'JWRL') || str_contains((string)$classification, 'JIBU') => ['label' => 'Junior', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200 font-semibold'],
                        str_starts_with((string)$classification, 'SB') => ['label' => 'Summer', 'class' => 'bg-orange-100 text-orange-800 border-orange-200 font-medium'],
                        default => ['label' => 'Level ' . ($level ?: 'Other'), 'class' => 'bg-slate-100 text-slate-600 border-slate-200 font-medium']
                    };
                @endphp
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] uppercase tracking-wider border {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                    <a href="{{ route('competitions.show', $result->competition->race_remote_id) }}" class="text-sky-600 hover:underline">
                        {{ $result->competition->description }}
                    </a>
                </div>
                <span class="text-xs text-slate-400 block mt-0.5">
                    {{ $result->competition->start_time?->format('d M Y') }}
                    @if($result->competition->event?->organizer)
                        &bull; {{ $result->competition->event->organizer }}
                    @endif
                </span>
            @else
                -
            @endif
        </td>
        <td class="px-4 py-2.5 whitespace-nowrap">
            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium text-xs">
                🎯 {{ $result->shootings ?: ($result->shooting_total ?? '-') }}
            </span>
        </td>
        <td class="px-4 py-2.5 whitespace-nowrap text-slate-500 font-medium text-xs">
            {{ $result->behind ? (str_starts_with($result->behind, '+') ? $result->behind : '+'.$result->behind) : '-' }}
        </td>
    </tr>
@endforeach

@if($results->hasMorePages())
    @php
        $loadedCount = min($results->currentPage() * $results->perPage(), $results->total());
    @endphp
    <tr
        id="results-loader-{{ $results->currentPage() }}"
        class="auto-load-trigger bg-slate-50/50"
        hx-get="{{ route('athletes.results', ['id' => $athlete->id, 'page' => $results->currentPage() + 1]) }}"
        hx-trigger="loadMore, intersect once"
        hx-target="#results-loader-{{ $results->currentPage() }}"
        hx-swap="outerHTML"
    >
        <td colspan="4" class="py-3 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100/90 border border-slate-200 text-slate-500 text-xs font-semibold shadow-2xs">
                <i class="fa-solid fa-spinner fa-spin text-sky-500 text-xs"></i>
                <span>Loaded {{ $loadedCount }} of {{ $results->total() }} races &bull; Scroll for more</span>
            </div>
        </td>
    </tr>
@else
    @if($results->total() > 0)
        <tr class="bg-slate-50/40">
            <td colspan="4" class="py-3 text-center">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 text-xs font-semibold shadow-2xs">
                    <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                    <span>All {{ $results->total() }} races loaded</span>
                </div>
            </td>
        </tr>
    @endif
@endif
