@foreach($tweets as $tweet)
    <div
        id="tweet-card-{{ $tweet->id }}"
        class="bg-white rounded-sm border border-slate-200 hover:border-sky-400 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between overflow-hidden group {{ $tweet->should_hide ? 'bg-rose-50/40 border-rose-300 ring-1 ring-rose-300' : '' }}"
        @if($tweet->hasTranslation()) x-data="{ showOriginal: false }" @endif
    >
        <div class="flex-1 flex flex-col">
            <!-- Top Media Container (Image if available) -->
            @if(!empty($tweet->media_urls) && is_array($tweet->media_urls) && count($tweet->media_urls) > 0)
                <div class="w-full aspect-[16/10] sm:h-48 xl:h-52 2xl:h-56 overflow-hidden relative bg-slate-900 flex-shrink-0">
                    <a href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                        <img
                            src="{{ $tweet->media_urls[0] }}"
                            alt="Media for {{ $tweet->author_handle }}"
                            class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        >
                    </a>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-black/25 pointer-events-none"></div>

                    <!-- Top Left Author Pill Overlay -->
                    <div class="absolute top-2.5 left-2.5">
                        <a
                            href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-sm bg-slate-900/85 backdrop-blur-xs text-white text-[11px] font-bold shadow-xs hover:bg-sky-600 transition-colors"
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
                    <div class="absolute top-2.5 right-2.5">
                        @if($tweet->published_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-extrabold uppercase tracking-wider {{ $tweet->published_at->isToday() ? 'bg-sky-500 text-white shadow-xs' : 'bg-slate-900/80 backdrop-blur-xs text-slate-200' }}">
                                {{ $tweet->published_at->isToday() ? 'Today' : $tweet->published_at->tz('Europe/Riga')->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    @if(count($tweet->media_urls) > 1)
                        <div class="absolute bottom-2.5 right-2.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-sm">
                            +{{ count($tweet->media_urls) - 1 }}
                        </div>
                    @endif
                </div>
            @else
                <!-- Thematic Author Brand Visual Header for text posts -->
                @php
                    $themeClasses = match(strtolower($tweet->author_handle)) {
                        'penaltyloop' => 'from-slate-950 via-slate-900 to-sky-950',
                        'biathstats' => 'from-slate-950 via-slate-900 to-emerald-950',
                        'biathlonworld', 'ibu_newsroom' => 'from-slate-950 via-slate-900 to-blue-950',
                        'nordicmag' => 'from-slate-950 via-slate-900 to-cyan-950',
                        'biathlonlivefr' => 'from-slate-950 via-slate-900 to-indigo-950',
                        default => 'from-slate-950 via-slate-900 to-sky-950',
                    };
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
                <div class="w-full aspect-[16/10] sm:h-48 xl:h-52 2xl:h-56 overflow-hidden relative bg-gradient-to-tr {{ $themeClasses }} flex flex-col justify-between p-3.5 flex-shrink-0 select-none">
                    <!-- Subtle dot matrix pattern -->
                    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

                    <!-- Top Bar: Author Pill & Date Badge -->
                    <div class="flex items-center justify-between gap-2 relative z-10">
                        <a
                            href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-sm bg-slate-900/85 backdrop-blur-xs text-white text-[11px] font-bold shadow-xs hover:bg-sky-600 transition-colors"
                        >
                            @if($tweet->author_avatar)
                                <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-3.5 h-3.5 rounded-full object-cover">
                            @else
                                <i class="fa-brands fa-x-twitter text-[10px]"></i>
                            @endif
                            <span>{{ '@' . $tweet->author_handle }}</span>
                        </a>

                        @if($tweet->published_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-extrabold uppercase tracking-wider {{ $tweet->published_at->isToday() ? 'bg-sky-500 text-white shadow-xs' : 'bg-slate-900/80 backdrop-blur-xs text-slate-200' }}">
                                {{ $tweet->published_at->isToday() ? 'Today' : $tweet->published_at->tz('Europe/Riga')->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <!-- Center Brand Focus -->
                    <div class="my-auto text-center relative z-10 py-1">
                        <a href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}" target="_blank" rel="noopener noreferrer" class="inline-flex flex-col items-center group/author">
                            <div class="w-12 h-12 rounded-full p-1 bg-white/10 backdrop-blur-md border border-white/20 shadow-lg group-hover/author:scale-105 transition-transform mb-1.5">
                                @if($tweet->author_avatar)
                                    <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-full h-full rounded-full object-cover">
                                @else
                                    <div class="w-full h-full rounded-full bg-sky-600 flex items-center justify-center text-white text-base font-bold">
                                        <i class="fa-solid fa-crosshairs"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs font-black text-white tracking-wide block drop-shadow-xs group-hover/author:text-sky-300 transition-colors">
                                {{ $tweet->author_name }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-300/80 mt-0.5 tracking-wider uppercase block">
                                {!! $tagline !!}
                            </span>
                        </a>
                    </div>

                    <!-- Bottom Accent Bar -->
                    <div class="flex items-center justify-between text-[10px] text-slate-400 relative z-10 pt-1 border-t border-white/10">
                        <span class="font-semibold text-slate-300"><i class="fa-solid fa-bullhorn text-sky-400 mr-1"></i>Trackside Bulletin</span>
                        <span>{{ $tweet->published_at ? $tweet->published_at->tz('Europe/Riga')->format('H:i') : '' }}</span>
                    </div>
                </div>
            @endif

            <!-- Card Body / Content -->
            <div class="p-4 flex-1 flex flex-col justify-between">
                <div>
                    @if(!empty($tweet->media_urls) && count($tweet->media_urls) > 0)
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
        <div id="tweet-loader-indicator-{{ $tweets->currentPage() }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-sm bg-slate-100 text-slate-600 text-xs font-bold shadow-2xs">
            <i class="fa-solid fa-circle-notch fa-spin text-sky-500 text-xs"></i>
            <span>Loading more stories...</span>
        </div>
    </div>
@endif
