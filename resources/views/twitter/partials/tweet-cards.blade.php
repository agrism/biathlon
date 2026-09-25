@foreach($tweets as $tweet)
    @include('twitter.partials.single-card', ['tweet' => $tweet])
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
