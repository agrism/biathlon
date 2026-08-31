@extends('layouts.admin', ['heading' => ''])

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Athlete Profile Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
                <!-- Athlete Headshot -->
                <div class="w-32 h-32 rounded-2xl bg-white/10 border-2 border-white/20 p-1 flex items-center justify-center overflow-hidden shadow-2xl flex-shrink-0">
                    @if($athlete->photo_uri)
                        {!! \App\Helpers\RemoteImageRenderHelper::instance()->getImageTag(url: $athlete->photo_uri, attributes: 'class="w-full h-full object-cover rounded-xl" width="128" height="128"') !!}
                    @else
                        <i class="fa-solid fa-person-skiing text-5xl text-slate-400"></i>
                    @endif
                </div>

                <!-- Bio Info -->
                <div class="text-center sm:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        @if($athlete->flag_uri)
                            <img src="{!! $athlete->flag_uri !!}" class="h-4 rounded-xs shadow-xs">
                        @endif
                        <span class="px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-xs font-extrabold uppercase tracking-wider">
                            {{ $athlete->nat }}
                        </span>
                        @if($athlete->birth_date)
                            <span class="px-2.5 py-0.5 rounded-full bg-white/10 text-slate-300 text-xs">
                                Age {{ $athlete->birth_date->age }} &bull; {{ $athlete->birth_date->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        {{ $athlete->given_name }} <span class="uppercase font-black">{{ $athlete->family_name }}</span>
                    </h1>

                    <!-- Stats Strip -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-4">
                        @if($athlete->stat_p_total !== null)
                            <div class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-center">
                                <div class="text-[10px] text-slate-400 uppercase font-bold">WC Points</div>
                                <div class="text-base font-black text-sky-300">{{ floatval($athlete->stat_p_total) }}</div>
                            </div>
                        @endif
                        @if($athlete->stat_shooting !== null)
                            <div class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-center">
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Accuracy</div>
                                <div class="text-base font-black text-emerald-400">{{ floatval($athlete->stat_shooting) }}%</div>
                            </div>
                        @endif
                        @if($athlete->stat_ski_kmb !== null)
                            <div class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-center">
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Ski Speed</div>
                                <div class="text-base font-black text-amber-300">-{{ floatval($athlete->stat_ski_kmb) }}s/km</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Career / Season Results Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-sky-500"></i> Competition Results
                </h2>
                <span class="text-xs font-bold text-slate-400">{{ count($athlete->results) }} entries</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-4 py-3">Rank</th>
                            <th scope="col" class="px-4 py-3">Competition</th>
                            <th scope="col" class="px-4 py-3">Shooting</th>
                            <th scope="col" class="px-4 py-3">Behind</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($athlete->results as $result)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($result->rank == 1)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">🥇 1st</span>
                                    @elseif($result->rank == 2)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-slate-200 text-slate-800 border border-slate-300">🥈 2nd</span>
                                    @elseif($result->rank == 3)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-amber-200 text-amber-950 border border-amber-400">🥉 3rd</span>
                                    @elseif($result->rank)
                                        <span class="font-bold text-slate-700">#{{ $result->rank }}</span>
                                    @else
                                        <span class="text-xs text-rose-500 font-semibold">DNF</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-800">
                                    @if($result->competition)
                                        <a href="{{ route('competitions.show', $result->competition->race_remote_id) }}" class="text-sky-600 hover:underline">
                                            {{ $result->competition->description }}
                                        </a>
                                        <span class="text-xs text-slate-400 block">{{ $result->competition->start_time?->format('d M Y') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium text-xs">
                                        🎯 {{ $result->shootings ?: ($result->shooting_total ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500 font-medium text-xs">
                                    {{ $result->behind ? '+'.$result->behind : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400 italic">No competition results recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
