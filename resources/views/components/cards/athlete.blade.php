@php
    $isPlace1 = ($index === 0);
    $isPlace2 = ($index === 1);
    $isPlace3 = ($index === 2);
    
    $podiumClass = match($index) {
        0 => 'border-amber-300 ring-1 ring-amber-400/30 bg-gradient-to-b from-amber-50/40 via-white to-white shadow-podium-gold',
        1 => 'border-slate-300 ring-1 ring-slate-300/40 bg-gradient-to-b from-slate-50/40 via-white to-white shadow-podium-silver',
        2 => 'border-amber-600/30 ring-1 ring-amber-600/20 bg-gradient-to-b from-amber-50/30 via-white to-white shadow-podium-bronze',
        default => 'border-slate-200 bg-white hover:border-slate-300 shadow-sm',
    };

    $badgeClass = match($index) {
        0 => 'bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-xs',
        1 => 'bg-gradient-to-r from-slate-400 to-slate-500 text-white shadow-xs',
        2 => 'bg-gradient-to-r from-amber-700 to-amber-600 text-white shadow-xs',
        default => 'bg-slate-100 text-slate-700 font-semibold',
    };

    $placeTitles = [
        0 => '1st • Gold',
        1 => '2nd • Silver',
        2 => '3rd • Bronze',
        3 => '4th Place',
        4 => '5th Place',
        5 => '6th Place',
    ];
@endphp

<div class="relative flex flex-col justify-between rounded-2xl border p-4 transition-all duration-200 card-hover {{ $podiumClass }}">
    <!-- Top Header: Place Badge & Privacy Toggle -->
    <div class="flex justify-between items-center mb-3">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-extrabold tracking-wide uppercase {{ $badgeClass }}">
            <span>{{\App\Enums\RankEnum::tryFrom($index+1)?->getMedal() ?? ($index+1)}}</span>
            <span>{{ $placeTitles[$index] ?? ($index+1).'th Place' }}</span>
        </span>

        @if(auth()->check() && $forecast->submit_deadline_at->gt(now()) && $athlete->name)
            <x-tooltip :text="$athlete->isHidden ? 'Hidden from other players' : 'Publicly visible'">
                <button
                    type="button"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                    hx-get="{{ route('forecasts.select-athlete.place.hide', ['id' => $forecast->id, 'place' => $index]) }}"
                    hx-target="#selected-athletes"
                >
                    <i class="fa {{ $athlete->isHidden ? 'fa-eye-slash text-rose-500' : 'fa-eye text-slate-400' }} text-sm"></i>
                </button>
            </x-tooltip>
        @endif
    </div>

    <!-- Athlete Content -->
    @if($athlete->name)
        <div class="flex flex-col items-center text-center">
            @php
                $isTeam = ($athlete->getModel() && $athlete->getModel()->is_team)
                    || (isset($forecast->competition) && \App\Enums\DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)?->isTeamDiscipline())
                    || (isset($isTeamDiscipline) && $isTeamDiscipline);
                $countryCode = $athlete->getModel()?->nat ?: ($athlete->tempId && strlen($athlete->tempId) === 3 ? $athlete->tempId : null);
                $flagUrl = $athlete->flagUrl ?: ($athlete->getModel()?->flag_uri ?: ($countryCode ? 'https://info.blob.core.windows.net/resources/bt/flags/' . strtolower($countryCode) . '.png' : null));
                $hasPhoto = $athlete->getModel() && !empty($athlete->getModel()->photo_uri);
            @endphp

            <!-- Athlete / Team Photo Frame -->
            <div class="relative my-1">
                <!-- Circular Headshot Frame -->
                <div class="w-28 h-28 rounded-full p-1 bg-gradient-to-tr from-slate-100 to-slate-200 border border-slate-200 shadow-inner flex items-center justify-center overflow-hidden">
                    @if($isTeam && $flagUrl)
                        <!-- Large Team Flag inside Circle -->
                        <div class="w-full h-full rounded-full overflow-hidden flex items-center justify-center bg-white p-2">
                            <img src="{{ $flagUrl }}" alt="{{ $athlete->name }}" class="w-16 h-auto max-h-12 object-contain rounded-xs shadow-md">
                        </div>
                    @elseif($hasPhoto)
                        {!! \App\Helpers\RemoteImageRenderHelper::instance()->getImageTag(url: $athlete->getModel()->photo_uri, attributes: 'class="w-full h-full object-cover rounded-full" width="112" height="112"') !!}
                    @elseif($flagUrl)
                        <div class="w-full h-full rounded-full overflow-hidden flex items-center justify-center bg-white p-2">
                            <img src="{{ $flagUrl }}" alt="{{ $athlete->name }}" class="w-16 h-auto max-h-12 object-contain rounded-xs shadow-md">
                        </div>
                    @else
                        <i class="fa-solid fa-person-skiing text-3xl text-slate-300"></i>
                    @endif
                </div>

                <!-- Unclipped Country Flag Badge -->
                @if(!$isTeam && $flagUrl)
                    <div class="absolute bottom-0 right-0 z-10 w-7 h-5 rounded-md shadow-md overflow-hidden border-2 border-white ring-1 ring-slate-200/80 bg-white">
                        <img src="{{ $flagUrl }}" alt="{{ $athlete->getModel()?->nat }}" class="w-full h-full object-cover">
                    </div>
                @endif
            </div>

            <!-- Athlete Name & Country -->
            <div class="mt-2 w-full">
                <div class="font-bold text-slate-900 text-sm leading-snug truncate" title="{{ $athlete->name }}">
                    @if(in_array($athlete->id, $favoriteAthleteIds ?? []))
                        <span class="text-amber-400 mr-0.5">★</span>
                    @endif
                    {{ $athlete->name }}
                </div>
                <div class="flex items-center justify-center gap-1.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1">
                    @if($flagUrl)
                        <img src="{{ $flagUrl }}" class="h-3 rounded-xs shadow-2xs inline-block">
                    @endif
                    <span>{{ trim($athlete->getModel()?->nat ?: $countryCode) }}</span>
                </div>
            </div>

            <!-- Athlete Stats Chips -->
            <div class="w-full mt-2">
                <x-cards.athlete-stat :athlete="$athlete"></x-cards.athlete-stat>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-1">
            @if(auth()->check() && $forecast->submit_deadline_at->gt(now()))
                <!-- Move Up Button -->
                @if($index !== 0)
                    <x-tooltip text="Move up">
                        <button
                            type="button"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors"
                            hx-get="{{ route('forecasts.select-athlete.place.move.up-down', ['id' => $forecast->id, 'place' => $index, 'direction' => \App\Enums\MoveDirectionEnum::UP->value]) }}"
                            hx-target="#selected-athletes"
                        >
                            <i class="fa-solid fa-arrow-up text-xs"></i>
                        </button>
                    </x-tooltip>
                @else
                    <span class="w-7"></span>
                @endif

                <!-- Edit Button -->
                <button
                    type="button"
                    class="flex-1 py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 font-semibold text-xs border border-slate-200/80 hover:border-sky-200 transition-all text-center"
                    hx-get="{{ route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index]) }}"
                    hx-target="#selected-athletes"
                >
                    <i class="fa-solid fa-pen text-[10px] mr-1"></i> Edit
                </button>

                <!-- Move Down Button -->
                @if($index < 5)
                    <x-tooltip text="Move down">
                        <button
                            type="button"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors"
                            hx-get="{{ route('forecasts.select-athlete.place.move.up-down', ['id' => $forecast->id, 'place' => $index, 'direction' => \App\Enums\MoveDirectionEnum::DOWN->value]) }}"
                            hx-target="#selected-athletes"
                        >
                            <i class="fa-solid fa-arrow-down text-xs"></i>
                        </button>
                    </x-tooltip>
                @else
                    <span class="w-7"></span>
                @endif
            @endif
        </div>
    @else
        <!-- Empty Slot Dropzone -->
        <div class="flex-1 flex flex-col items-center justify-center my-4">
            @if(auth()->check() && $forecast->submit_deadline_at->gt(now()))
                <button
                    type="button"
                    class="w-full flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-sky-300 hover:border-sky-500 bg-sky-50/40 hover:bg-sky-50 text-sky-600 transition-all group cursor-pointer"
                    hx-get="{{ route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $index]) }}"
                    hx-target="#selected-athletes"
                >
                    <div class="w-12 h-12 rounded-full bg-white shadow-xs border border-sky-200 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-plus text-sky-500 text-base"></i>
                    </div>
                    <span class="text-xs font-bold text-sky-700 tracking-wide uppercase">Select Athlete</span>
                    <span class="text-[10px] text-sky-500/80 mt-0.5">Click to choose</span>
                </button>
            @else
                <div class="text-center py-8 text-slate-300 text-xs italic">
                    Not selected
                </div>
            @endif
        </div>
        <div class="h-6"></div>
    @endif
</div>
