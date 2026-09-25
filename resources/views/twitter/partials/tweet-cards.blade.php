@foreach($tweets as $tweet)
    <div class="py-4 sm:py-5 border-b border-slate-100 last:border-b-0 flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-6 transition-colors hover:bg-slate-50/60 -mx-3 px-3 rounded-xl {{ $tweet->should_hide ? 'bg-rose-50/30 border-rose-100/60' : '' }}">
        <!-- Date & Provider Author on Left -->
        <div class="sm:w-36 flex-shrink-0 flex items-center sm:items-start justify-between sm:justify-start sm:flex-col gap-1">
            @if($tweet->published_at)
                @if($tweet->published_at->isToday())
                    <span class="text-xs font-black text-sky-600 uppercase tracking-wider">Today</span>
                @else
                    <span class="text-xs font-bold text-slate-700 tracking-tight">{{ $tweet->published_at->tz('Europe/Riga')->format('d M Y') }}</span>
                @endif
            @else
                <span class="text-xs text-slate-400">-</span>
            @endif

            @if($tweet->author_handle)
                <a href="{{ $tweet->tweet_url ?: ('https://x.com/' . $tweet->author_handle) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 hover:text-sky-600 transition-colors">
                    @if($tweet->author_avatar)
                        <img src="{{ $tweet->author_avatar }}" alt="{{ $tweet->author_name }}" class="w-3.5 h-3.5 rounded-full object-cover">
                    @endif
                    <span>{{ '@' . $tweet->author_handle }}</span>
                </a>
            @endif
        </div>

        <!-- News Content on Right -->
        <div class="flex-1 min-w-0" @if($tweet->hasTranslation()) x-data="{ showOriginal: false }" @endif>
            @if($tweet->hasTranslation())
                <p class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal" x-show="!showOriginal">
                    {!! $tweet->getFormattedTranslatedContent() !!}
                </p>
                <p class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal" x-show="showOriginal" x-cloak>
                    {!! $tweet->getFormattedContent() !!}
                </p>
                <div class="mt-1.5 flex items-center gap-2">
                    <button
                        type="button"
                        @click="showOriginal = !showOriginal"
                        class="inline-flex items-center gap-1.5 text-[11px] font-medium text-sky-600 hover:text-sky-700 transition-colors cursor-pointer py-0.5"
                    >
                        <i class="fa-solid fa-language text-xs"></i>
                        <span x-show="!showOriginal">Translated from {{ strtoupper($tweet->source_language) }} &bull; <span class="underline underline-offset-2">Show original</span></span>
                        <span x-show="showOriginal" x-cloak class="underline underline-offset-2">Show translation</span>
                    </button>
                </div>
            @else
                <p class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal">
                    {!! $tweet->getFormattedContent() !!}
                </p>
            @endif

            @if(!empty($tweet->media_urls) && is_array($tweet->media_urls))
                <div class="mt-3 grid {{ count($tweet->media_urls) > 1 ? 'grid-cols-2 gap-2.5' : 'grid-cols-1' }} max-w-lg">
                    @foreach($tweet->media_urls as $mediaUrl)
                        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-2xl border border-slate-200/80 group shadow-2xs hover:shadow-xs transition-shadow">
                            <img src="{{ $mediaUrl }}" alt="Tweet media" class="w-full max-h-64 object-cover transition-transform duration-300 group-hover:scale-103" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif

            @if(auth()->check() && auth()->user()->isAdmin())
                @include('twitter.partials.hide-toggle', ['tweet' => $tweet])
            @endif
        </div>
    </div>
@endforeach

@if($tweets->hasMorePages())
    <div
        id="tweets-loader-{{ $tweets->currentPage() }}"
        class="w-full py-4 flex items-center justify-center border-t border-slate-100/60 mt-1"
        hx-get="{{ route('tweets.index', ['page' => $tweets->currentPage() + 1]) }}"
        hx-trigger="revealed"
        hx-target="#tweets-loader-{{ $tweets->currentPage() }}"
        hx-swap="outerHTML"
        hx-indicator="#tweet-loader-indicator-{{ $tweets->currentPage() }}"
    >
        <div id="tweet-loader-indicator-{{ $tweets->currentPage() }}" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-400 text-[11px] font-medium">
            <i class="fa-solid fa-circle-notch fa-spin text-sky-500 text-[10px]"></i>
            <span>Loading older updates...</span>
        </div>
    </div>
@endif
