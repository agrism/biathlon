<div id="tweet-hide-toggle-{{ $tweet->id }}" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-3 select-none">
    <!-- Left: Status Badge & HTMX Loader Indicator -->
    <div class="flex items-center gap-2">
        @if($tweet->should_hide)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                <i class="fa-solid fa-eye-slash text-[10px] text-rose-500"></i>
                <span>Hidden from clients</span>
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                <i class="fa-solid fa-eye text-[10px] text-emerald-500"></i>
                <span>Visible to clients</span>
            </span>
        @endif

        <!-- Local HTMX Loader Spinner -->
        <span id="hide-loader-{{ $tweet->id }}" class="indicator inline-flex items-center gap-1.5 text-xs text-sky-600 font-medium ml-1">
            <i class="fa-solid fa-circle-notch fa-spin text-sky-500 text-xs"></i>
            <span class="text-[11px]">Updating...</span>
        </span>
    </div>

    <!-- Right: Interactive Toggle Switch Button -->
    <button
        type="button"
        hx-post="{{ route('tweets.toggle-hide', $tweet->id) }}"
        hx-target="#tweet-hide-toggle-{{ $tweet->id }}"
        hx-swap="outerHTML"
        hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
        hx-indicator="#hide-loader-{{ $tweet->id }}"
        class="inline-flex items-center gap-2 cursor-pointer group focus:outline-hidden py-1 px-2 rounded-lg hover:bg-slate-100/80 transition-colors"
        title="{{ $tweet->should_hide ? 'Click to make visible to clients' : 'Click to hide from clients' }}"
    >
        <span class="text-xs font-semibold text-slate-500 group-hover:text-slate-800 transition-colors">
            {{ $tweet->should_hide ? 'Unhide' : 'Hide' }}
        </span>

        <!-- Switch Track -->
        <div class="w-10 h-5.5 rounded-full transition-colors duration-200 ease-in-out p-0.5 flex items-center {{ $tweet->should_hide ? 'bg-rose-500' : 'bg-slate-300 group-hover:bg-slate-400' }}">
            <!-- Switch Knob -->
            <div class="w-4.5 h-4.5 bg-white rounded-full shadow-xs transform transition-transform duration-200 ease-in-out flex items-center justify-center {{ $tweet->should_hide ? 'translate-x-4.5' : 'translate-x-0' }}">
                @if($tweet->should_hide)
                    <i class="fa-solid fa-eye-slash text-[8px] text-rose-600"></i>
                @else
                    <i class="fa-solid fa-check text-[8px] text-slate-400"></i>
                @endif
            </div>
        </div>
    </button>
</div>
