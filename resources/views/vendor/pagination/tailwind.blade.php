@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-1 py-2 select-none">
        <!-- Results Summary (Mobile & Desktop) -->
        <div class="text-xs text-slate-500 font-medium">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-black text-slate-800">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-black text-slate-800">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-black text-slate-800">{{ $paginator->count() }}</span>
            @endif
            {!! __('of') !!}
            <span class="font-black text-slate-800">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </div>

        <!-- Navigation Buttons -->
        <div class="inline-flex items-center gap-1 flex-wrap justify-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center gap-1.5 px-3 h-8.5 rounded-xl border border-slate-100 bg-slate-50 text-slate-300 text-xs font-semibold cursor-not-allowed select-none">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Prev</span>
                </span>
            @else
                <a
                    @isset($htmxTargetElement) hx-target="{{$htmxTargetElement}}" @endif
                    @if($useHtmx ?? false) hx-get @else href @endif="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="inline-flex items-center gap-1.5 px-3 h-8.5 rounded-xl border border-slate-200/90 bg-white text-slate-700 hover:text-sky-600 hover:border-sky-300 hover:bg-sky-50/50 text-xs font-bold transition-all shadow-2xs cursor-pointer"
                    aria-label="{{ __('pagination.previous') }}"
                >
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Prev</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[28px] h-8.5 text-slate-400 font-bold text-xs select-none">
                        •••
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center min-w-[34px] h-8.5 px-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 text-white font-extrabold text-xs shadow-xs shadow-sky-500/20 select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a
                                @isset($htmxTargetElement) hx-target="{{$htmxTargetElement}}" @endif
                                @if($useHtmx ?? false) hx-get @else href @endif="{{ $url }}"
                                class="inline-flex items-center justify-center min-w-[34px] h-8.5 px-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-700 hover:text-sky-600 hover:border-sky-300 hover:bg-sky-50/50 font-bold text-xs transition-all shadow-2xs cursor-pointer"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a
                    @isset($htmxTargetElement) hx-target="{{$htmxTargetElement}}" @endif
                    @if($useHtmx ?? false) hx-get @else href @endif="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="inline-flex items-center gap-1.5 px-3 h-8.5 rounded-xl border border-slate-200/90 bg-white text-slate-700 hover:text-sky-600 hover:border-sky-300 hover:bg-sky-50/50 text-xs font-bold transition-all shadow-2xs cursor-pointer"
                    aria-label="{{ __('pagination.next') }}"
                >
                    <span>Next</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center gap-1.5 px-3 h-8.5 rounded-xl border border-slate-100 bg-slate-50 text-slate-300 text-xs font-semibold cursor-not-allowed select-none">
                    <span>Next</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
