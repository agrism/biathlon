@php
    $currentAthlete = $tweet->mentionedAthlete ?: $tweet->findFirstMentionedAthlete();
@endphp

<div
    id="tweet-hide-toggle-{{ $tweet->id }}"
    class="flex flex-wrap items-center justify-between gap-2 select-none"
    x-data="{
        editingAthlete: false,
        query: '{{ $currentAthlete ? ($currentAthlete->given_name . ' ' . $currentAthlete->family_name) : '' }}'
    }"
>
    <!-- Left: Status Badge & HTMX Loader Indicator -->
    <div class="flex items-center gap-1.5 flex-shrink-0">
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
        <span id="hide-loader-{{ $tweet->id }}" class="indicator inline-flex items-center gap-1 text-[11px] text-sky-600 font-medium ml-0.5">
            <i class="fa-solid fa-circle-notch fa-spin text-sky-500 text-xs"></i>
        </span>
    </div>

    <!-- Middle: Admin Athlete Selector / Submitter -->
    <div class="flex items-center gap-1">
        <!-- Display / Toggle Button when not editing -->
        <div x-show="!editingAthlete" class="flex items-center gap-1">
            @if($currentAthlete)
                <button
                    type="button"
                    @click="editingAthlete = true; $nextTick(() => $refs.athInput{{ $tweet->id }}.focus())"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 hover:border-sky-300 transition-all truncate shadow-2xs group/btn cursor-pointer max-w-[140px] sm:max-w-[170px]"
                    title="Click to change athlete picture"
                >
                    <i class="fa-solid fa-user text-[10px] text-sky-500"></i>
                    <span class="truncate">{{ $currentAthlete->given_name }} {{ $currentAthlete->family_name }}</span>
                    <i class="fa-solid fa-pen text-[9px] text-sky-400 group-hover/btn:text-sky-600 ml-0.5"></i>
                </button>

                <button
                    type="button"
                    hx-post="{{ route('tweets.set-athlete', $tweet->id) }}"
                    hx-vals='{"athlete_name": ""}'
                    hx-target="#tweet-card-{{ $tweet->id }}"
                    hx-swap="outerHTML"
                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                    hx-indicator="#hide-loader-{{ $tweet->id }}"
                    class="w-5 h-5 flex items-center justify-center rounded-[3px] text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                    title="Remove athlete picture"
                >
                    <i class="fa-solid fa-xmark text-[10px]"></i>
                </button>
            @else
                <button
                    type="button"
                    @click="editingAthlete = true; $nextTick(() => $refs.athInput{{ $tweet->id }}.focus())"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 border border-slate-200 hover:border-sky-300 transition-all shadow-2xs cursor-pointer"
                    title="Assign athlete picture to this tweet"
                >
                    <i class="fa-solid fa-user-plus text-[10px] text-slate-400 hover:text-sky-500"></i>
                    <span>+ Athlete</span>
                </button>
            @endif
        </div>

        <!-- Inline Athlete Name Input Form when editing -->
        <form
            x-show="editingAthlete"
            x-cloak
            hx-post="{{ route('tweets.set-athlete', $tweet->id) }}"
            hx-target="#tweet-card-{{ $tweet->id }}"
            hx-swap="outerHTML"
            hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
            hx-indicator="#hide-loader-{{ $tweet->id }}"
            @submit="editingAthlete = false"
            class="flex items-center gap-1"
        >
            <input
                x-ref="athInput{{ $tweet->id }}"
                type="text"
                name="athlete_name"
                x-model="query"
                placeholder="Athlete name & surname..."
                list="admin-athletes-datalist"
                autocomplete="off"
                class="px-2 py-0.5 text-xs text-slate-800 bg-white rounded-[4px] border border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-500 w-32 sm:w-40 shadow-2xs"
                @keydown.escape="editingAthlete = false"
            >
            <button
                type="submit"
                class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-[4px] text-[11px] font-bold shadow-2xs cursor-pointer transition-colors"
                title="Save athlete"
            >
                <i class="fa-solid fa-check text-[10px]"></i>
            </button>
            <button
                type="button"
                @click="editingAthlete = false"
                class="px-1.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-600 rounded-[4px] text-[11px] cursor-pointer transition-colors"
                title="Cancel"
            >
                <i class="fa-solid fa-xmark text-[10px]"></i>
            </button>
        </form>
    </div>

    <!-- Right: Interactive Toggle Switch Button -->
    <button
        type="button"
        hx-post="{{ route('tweets.toggle-hide', $tweet->id) }}"
        hx-target="#tweet-hide-toggle-{{ $tweet->id }}"
        hx-swap="outerHTML"
        hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
        hx-indicator="#hide-loader-{{ $tweet->id }}"
        class="inline-flex items-center gap-1.5 cursor-pointer group focus:outline-hidden py-0.5 px-1.5 rounded-lg hover:bg-slate-100/80 transition-colors flex-shrink-0"
        title="{{ $tweet->should_hide ? 'Click to make visible to clients' : 'Click to hide from clients' }}"
    >
        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-slate-800 transition-colors">
            {{ $tweet->should_hide ? 'Unhide' : 'Hide' }}
        </span>

        <!-- Switch Track -->
        <div class="w-8 h-4.5 rounded-full transition-colors duration-200 ease-in-out p-0.5 flex items-center {{ $tweet->should_hide ? 'bg-rose-500' : 'bg-slate-300 group-hover:bg-slate-400' }}">
            <!-- Switch Knob -->
            <div class="w-3.5 h-3.5 bg-white rounded-full shadow-xs transform transition-transform duration-200 ease-in-out flex items-center justify-center {{ $tweet->should_hide ? 'translate-x-3.5' : 'translate-x-0' }}">
                @if($tweet->should_hide)
                    <i class="fa-solid fa-eye-slash text-[7px] text-rose-600"></i>
                @else
                    <i class="fa-solid fa-check text-[7px] text-slate-400"></i>
                @endif
            </div>
        </div>
    </button>
</div>
