@foreach($tweets as $tweet)
    <div class="py-4 sm:py-5 border-b border-slate-100 last:border-b-0 flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-6 transition-colors hover:bg-slate-50/60 -mx-3 px-3 rounded-xl">
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
        <div class="flex-1 min-w-0">
            <p class="text-slate-800 text-xs sm:text-sm leading-relaxed font-normal">
                {!! $tweet->getFormattedContent() !!}
            </p>
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
