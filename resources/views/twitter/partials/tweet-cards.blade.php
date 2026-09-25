@foreach($tweets as $tweet)
    <div
        id="tweet-card-{{ $tweet->id }}"
        class="bg-white rounded-[5px] border border-slate-200 hover:border-sky-400 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between overflow-hidden group {{ $tweet->should_hide ? 'bg-rose-50/40 border-rose-300 ring-1 ring-rose-300' : '' }}"
        @if($tweet->hasTranslation()) x-data="{ showOriginal: false }" @endif
    >
        <div class="flex-1 flex flex-col">
            @php
                $hasMedia = !empty($tweet->media_urls) && is_array($tweet->media_urls) && count($tweet->media_urls) > 0;
                $mentionedAthlete = null;
                if (!$hasMedia) {
                    $mentionedAthlete = $tweet->findFirstMentionedAthlete();
                }
            @endphp

            @if($hasMedia)
                <!-- Top Media Container (Image from tweet / web card) -->
                <div class="w-full aspect-[16/10] sm:h-48 xl:h-52 2xl:h-56 overflow-hidden relative bg-slate-100 flex-shrink-0">
                    <a href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                        <img
                            src="{{ $tweet->media_urls[0] }}"
                            alt="Media for {{ $tweet->author_handle }}"
                            class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        >
                    </a>
                    <!-- Top subtle dark vignette for author and date pill readability -->
                    <div class="absolute inset-x-0 top-0 h-14 bg-gradient-to-b from-black/45 via-black/15 to-transparent pointer-events-none"></div>

                    <!-- Bottom smooth gradual transition from image to light white card background -->
                    <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-white via-white/80 to-transparent pointer-events-none"></div>

                    <!-- Top Left Author Pill Overlay -->
                    <div class="absolute top-2.5 left-2.5 z-10">
                        <a
                            href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold border border-white/20 shadow-xs hover:bg-sky-600 transition-colors"
                        >
                            @if($tweet->author_avatar)
                                <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-3.5 h-3.5 rounded-full object-cover">
                            @else
                                <i class="fa-brands fa-x-twitter text-[10px]"></i>
                            @endif
                            <span>{{ '@' . $tweet->author_handle }}</span>
                        </a>
                    </div>

                    <!-- Top Right Date Badge Overlay -->
                    <div class="absolute top-2.5 right-2.5 z-10">
                        @if($tweet->published_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-extrabold uppercase tracking-wider {{ $tweet->published_at->isToday() ? 'bg-sky-500 text-white shadow-xs' : 'bg-slate-900/80 backdrop-blur-md text-slate-200 border border-white/20' }}">
                                {{ $tweet->published_at->isToday() ? 'Today' : $tweet->published_at->tz('Europe/Riga')->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    @if(count($tweet->media_urls) > 1)
                        <div class="absolute bottom-2.5 right-2.5 bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-bold px-2 py-0.5 rounded-[4px] border border-white/20 z-10">
                            +{{ count($tweet->media_urls) - 1 }}
                        </div>
                    @endif
                </div>
            @elseif($mentionedAthlete && !empty($mentionedAthlete->photo_uri))
                <!-- Top Media Container (First Mentioned Athlete Portrait - Crisp Light Winter Snow Theme) -->
                <div class="w-full aspect-[16/10] sm:h-48 xl:h-52 2xl:h-56 overflow-hidden relative bg-gradient-to-b from-sky-200 via-sky-100 to-slate-100 flex-shrink-0 group/athlete">
                    <!-- Crisp Alpine Snowy Mountain Silhouettes -->
                    <div class="absolute inset-0 pointer-events-none opacity-35">
                        <svg class="w-full h-full" viewBox="0 0 400 240" preserveAspectRatio="none" fill="none">
                            <path d="M0,170 L75,105 L145,145 L235,75 L315,135 L400,85 L400,240 L0,240 Z" fill="#38bdf8" opacity="0.35"/>
                            <path d="M0,185 L55,145 L135,175 L215,125 L295,165 L400,130 L400,240 L0,240 Z" fill="#7dd3fc" opacity="0.45"/>
                            <path d="M0,205 L95,175 L195,195 L295,170 L400,185 L400,240 L0,240 Z" fill="#ffffff" opacity="0.9"/>
                        </svg>
                    </div>

                    <!-- Subtle Snowflakes / Frost Sparkle Texture -->
                    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(#0284c7_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-32 bg-sky-300/30 rounded-full blur-2xl pointer-events-none"></div>

                    <a href="{{ route('athletes.show', $mentionedAthlete->id) }}" class="block w-full h-full relative z-10">
                        <img
                            src="{{ $mentionedAthlete->photo_uri }}"
                            alt="{{ $mentionedAthlete->given_name }} {{ $mentionedAthlete->family_name }}"
                            class="w-full h-full object-cover object-top transition-transform duration-500 group-hover/athlete:scale-105 drop-shadow-[0_10px_20px_rgba(15,23,42,0.18)]"
                            loading="lazy"
                        >
                    </a>

                    <!-- Top subtle vignette for badge contrast -->
                    <div class="absolute inset-x-0 top-0 h-14 bg-gradient-to-b from-slate-900/35 to-transparent pointer-events-none z-15"></div>

                    <!-- Bottom smooth gradual transition from image to light white card background -->
                    <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-white via-white/80 to-transparent pointer-events-none z-15"></div>

                    <!-- Top Left Author Pill Overlay -->
                    <div class="absolute top-2.5 left-2.5 z-20">
                        <a
                            href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold border border-white/20 shadow-xs hover:bg-sky-600 transition-colors"
                        >
                            @if($tweet->author_avatar)
                                <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-3.5 h-3.5 rounded-full object-cover">
                            @else
                                <i class="fa-brands fa-x-twitter text-[10px]"></i>
                            @endif
                            <span>{{ '@' . $tweet->author_handle }}</span>
                        </a>
                    </div>

                    <!-- Top Right Date Badge Overlay -->
                    <div class="absolute top-2.5 right-2.5 z-20">
                        @if($tweet->published_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-extrabold uppercase tracking-wider {{ $tweet->published_at->isToday() ? 'bg-sky-500 text-white shadow-xs' : 'bg-slate-900/80 backdrop-blur-md text-slate-200 border border-white/20' }}">
                                {{ $tweet->published_at->isToday() ? 'Today' : $tweet->published_at->tz('Europe/Riga')->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <!-- Bottom Athlete Badge Overlay -->
                    <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between pointer-events-none z-20">
                        <a href="{{ route('athletes.show', $mentionedAthlete->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-slate-900/85 backdrop-blur-md text-white text-[11px] font-bold border border-white/20 shadow-md pointer-events-auto hover:bg-sky-600 transition-colors">
                            @if($mentionedAthlete->nat)
                                <span class="px-1.5 py-0.2 rounded-[2px] bg-sky-500 text-white font-black text-[10px] shadow-2xs">{{ $mentionedAthlete->nat }}</span>
                                <span class="text-slate-400 text-[10px]">&bull;</span>
                            @endif
                            <span class="truncate max-w-[160px]">{{ $mentionedAthlete->given_name }} {{ $mentionedAthlete->family_name }}</span>
                        </a>
                    </div>
                </div>
            @else
                <!-- Thematic Author Brand Visual Header (Crisp Light Winter Theme) -->
                @php
                    $tagline = match(strtolower($tweet->author_handle)) {
                        'penaltyloop' => 'Penalty Loop &bull; Biathlon Insights',
                        'biathstats' => 'BiathlonStats &bull; Telemetry & Analytics',
                        'biathlonworld' => 'IBU &bull; World Cup Official',
                        'ibu_newsroom' => 'IBU &bull; Press & Newsroom',
                        'nordicmag' => 'Nordic Magazine &bull; Ski & Biathlon',
                        'biathlonlivefr' => 'Biathlon Live &bull; Actualités',
                        default => $tweet->author_name,
                    };
                @endphp
                <div class="w-full aspect-[16/10] sm:h-48 xl:h-52 2xl:h-56 overflow-hidden relative bg-gradient-to-b from-sky-100 via-sky-50 to-white flex flex-col justify-between p-3.5 flex-shrink-0 select-none">
                    <!-- Mountain Silhouettes & Snow Texture -->
                    <div class="absolute inset-0 pointer-events-none opacity-30">
                        <svg class="w-full h-full" viewBox="0 0 400 240" preserveAspectRatio="none" fill="none">
                            <path d="M0,170 L75,105 L145,145 L235,75 L315,135 L400,85 L400,240 L0,240 Z" fill="#38bdf8" opacity="0.3"/>
                            <path d="M0,195 L60,150 L140,180 L220,130 L300,175 L400,140 L400,240 L0,240 Z" fill="#7dd3fc" opacity="0.4"/>
                            <path d="M0,215 L100,190 L200,210 L300,185 L400,200 L400,240 L0,240 Z" fill="#ffffff" opacity="0.9"/>
                        </svg>
                    </div>
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#0284c7_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

                    <!-- Bottom smooth gradual transition to white -->
                    <div class="absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-white via-white/70 to-transparent pointer-events-none"></div>

                    <!-- Top Bar: Author Pill & Date Badge -->
                    <div class="flex items-center justify-between gap-2 relative z-10">
                        <a
                            href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold border border-white/20 shadow-xs hover:bg-sky-600 transition-colors"
                        >
                            @if($tweet->author_avatar)
                                <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-3.5 h-3.5 rounded-full object-cover">
                            @else
                                <i class="fa-brands fa-x-twitter text-[10px]"></i>
                            @endif
                            <span>{{ '@' . $tweet->author_handle }}</span>
                        </a>

                        @if($tweet->published_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-extrabold uppercase tracking-wider {{ $tweet->published_at->isToday() ? 'bg-sky-500 text-white shadow-xs' : 'bg-slate-900/80 backdrop-blur-md text-slate-200 border border-white/20' }}">
                                {{ $tweet->published_at->isToday() ? 'Today' : $tweet->published_at->tz('Europe/Riga')->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <!-- Center Brand Focus -->
                    <div class="my-auto text-center relative z-10 py-1">
                        <a href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}" target="_blank" rel="noopener noreferrer" class="inline-flex flex-col items-center group/author">
                            <div class="w-13 h-13 rounded-full p-1 bg-white/80 backdrop-blur-md border border-slate-200/80 shadow-md group-hover/author:scale-105 transition-transform mb-1.5">
                                @if($tweet->author_avatar)
                                    <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-full h-full rounded-full object-cover">
                                @else
                                    <div class="w-full h-full rounded-full bg-sky-600 flex items-center justify-center text-white text-base font-bold">
                                        <i class="fa-solid fa-crosshairs"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs font-black text-slate-900 tracking-wide block group-hover/author:text-sky-600 transition-colors">
                                {{ $tweet->author_name }}
                            </span>
                            <span class="text-[10px] font-bold text-sky-700 mt-0.5 tracking-wider uppercase block">
                                {!! $tagline !!}
                            </span>
                        </a>
                    </div>

                    <!-- Bottom Accent Bar -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 relative z-10 pt-1 border-t border-slate-200/80">
                        <span class="font-semibold text-slate-600"><i class="fa-solid fa-bullhorn text-sky-600 mr-1"></i>Trackside Bulletin</span>
                        <span class="font-medium text-slate-500">{{ $tweet->published_at ? $tweet->published_at->tz('Europe/Riga')->format('H:i') : '' }}</span>
                    </div>
                </div>
            @endif

            <!-- Card Body / Content -->
            <div class="p-4 flex-1 flex flex-col justify-between">
                <div>
                    @if($hasMedia || $mentionedAthlete)
                        <div class="flex items-center justify-between gap-2 mb-1.5 text-[11px] text-slate-400">
                            <span class="font-semibold text-slate-600 truncate">{{ $tweet->author_name }}</span>
                            <span>{{ $tweet->published_at ? $tweet->published_at->tz('Europe/Riga')->format('H:i') : '' }}</span>
                        </div>
                    @endif

                    @if($tweet->hasTranslation())
                        <div class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal" x-show="!showOriginal">
                            {!! $tweet->getFormattedTranslatedContent() !!}
                        </div>
                        <div class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal" x-show="showOriginal" x-cloak>
                            {!! $tweet->getFormattedContent() !!}
                        </div>

                        <!-- Translation Toggle Button -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <button
                                type="button"
                                @click="showOriginal = !showOriginal"
                                class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-sky-600 hover:text-sky-700 transition-colors cursor-pointer"
                            >
                                <i class="fa-solid fa-language text-xs"></i>
                                <span x-show="!showOriginal">Translated from {{ strtoupper($tweet->source_language) }} &bull; <span class="underline underline-offset-2">Show original</span></span>
                                <span x-show="showOriginal" x-cloak class="underline underline-offset-2">Show translation</span>
                            </button>
                        </div>
                    @else
                        <div class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal">
                            {!! $tweet->getFormattedContent() !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card Admin Footer (Only for Admins) -->
        @if(auth()->check() && auth()->user()->isAdmin())
            <div class="px-4 py-2.5 bg-slate-50/75 border-t border-slate-100">
                @include('twitter.partials.hide-toggle', ['tweet' => $tweet])
            </div>
        @endif
    </div>
@endforeach

@if($tweets->hasMorePages())
    <div
        id="tweets-loader-{{ $tweets->currentPage() }}"
        class="col-span-full w-full py-8 flex items-center justify-center border-t border-slate-100/80 mt-2"
        hx-get="{{ route('tweets.index', ['page' => $tweets->currentPage() + 1]) }}"
        hx-trigger="revealed"
        hx-target="#tweets-loader-{{ $tweets->currentPage() }}"
        hx-swap="outerHTML"
        hx-indicator="#tweet-loader-indicator-{{ $tweets->currentPage() }}"
    >
        <div id="tweet-loader-indicator-{{ $tweets->currentPage() }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-[5px] bg-slate-100 text-slate-600 text-xs font-bold shadow-2xs">
            <i class="fa-solid fa-circle-notch fa-spin text-sky-500 text-xs"></i>
            <span>Loading more stories...</span>
        </div>
    </div>
@endif
