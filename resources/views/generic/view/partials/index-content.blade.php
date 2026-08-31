@if($subTitle)
    <div class="mb-5 text-center">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{!! $subTitle !!}</h1>
    </div>
@endif

<!-- Top Action Bar (Filters, Export) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <!-- Filter Form -->
    @if($helper->filters())
        <form @if($helper->getFilterHtmxFormAttributes()) {!! $helper->getFilterHtmxFormAttributes() !!} @else method="GET" @endif class="w-full sm:w-auto">
            @csrf
            <div class="inline-flex flex-wrap items-center gap-2 p-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-sky-600 text-[10px]"></i>
                    <span>Filter</span>
                </span>
                
                @foreach($helper->filters() as $filter)
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-medium text-slate-600 px-1" for="{{ $filter->key }}">{{ $filter->title ?: $filter->key }}:</label>
                        {!! $filter->inputType->getElement(name: $filter->key, value: $filter->value, style: '', classes: 'text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition-all font-medium text-slate-800', options: $filter->options) !!}
                    </div>
                @endforeach

                <div class="flex items-center gap-1 ml-auto">
                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white shadow-2xs transition-colors cursor-pointer">
                        <i class="fa-solid fa-check text-[10px]"></i>
                        <span>Apply</span>
                    </button>
                    <button type="submit" name="clear" value="1" class="inline-flex items-center text-xs font-medium px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer">
                        Clear
                    </button>
                </div>
            </div>
        </form>
    @else
        <div></div>
    @endif

    <!-- Export Button -->
    @if($helper->isExportButtonVisible())
        <div class="flex justify-end">
            <a href="{{ request()->url() . '?export=excel' }}">
                <button
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 shadow-2xs transition-colors cursor-pointer"
                    type="button"
                    hx-target="#show"
                >
                    <i class="fa-solid fa-file-csv text-emerald-600 text-xs"></i>
                    <span>Export CSV</span>
                </button>
            </a>
        </div>
    @endif
</div>

<!-- Pagination (Top) -->
@if($helper->data() && $helper->data()->hasPages())
    <div class="mb-3">
        {{ $helper->data()->links('pagination::tailwind-white', ['useHtmx' => $useHtmx, 'htmxTargetElement' => $htmxTargetElement]) }}
    </div>
@endif

<!-- Business-Grade Clean Data Table -->
<div class="bg-white border-y border-slate-200 overflow-hidden mb-4">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-xs sm:text-sm border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    @foreach($helper->headers() as $name)
                        <th scope="col" class="py-3 px-4 font-bold">
                            {!! $name !!}
                        </th>
                    @endforeach

                    @if($helper->showRouteName())
                        <th scope="col" class="py-3 px-4 text-right font-bold">
                            Action
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($helper->data() as $dataItem)
                    <tr class="hover:bg-slate-50/70 transition-colors duration-100">
                        @foreach($helper->dataKeys() as $key)
                            <td class="py-2.5 px-4 whitespace-nowrap text-slate-700 font-medium align-middle">
                                @if(in_array($key, $helper->dataUrlKeys()))
                                    <a href="{{ data_get($dataItem, $key) }}" target="_blank" class="text-sky-600 hover:text-sky-800 hover:underline font-semibold">{{ data_get($dataItem, $key) }}</a>
                                @else
                                    @if(!is_string($key) && is_callable($key))
                                        {!! $key($dataItem) !!}
                                    @else
                                        {{ data_get($dataItem, $key) }}
                                    @endif
                                @endif
                            </td>
                        @endforeach

                        @if($routeName = $helper->showRouteName())
                            <td class="py-2.5 px-4 whitespace-nowrap text-right align-middle">
                                <button
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 text-xs font-semibold border border-slate-200 hover:border-sky-200 transition-colors cursor-pointer"
                                    type="button"
                                    hx-get="{{ route($routeName, $dataItem->id) }}"
                                    hx-target="#show"
                                    hx-boost="false"
                                >
                                    <span>View</span>
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <i class="fa-solid fa-inbox text-2xl text-slate-300"></i>
                                <span class="font-semibold text-slate-600 text-sm">No records found</span>
                                <span class="text-xs text-slate-400">Try adjusting your filters or search criteria.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination (Bottom) -->
@if($helper->data() && $helper->data()->hasPages())
    <div class="mt-3">
        {{ $helper->data()->links('pagination::tailwind-white', ['useHtmx' => $useHtmx, 'htmxTargetElement' => $htmxTargetElement]) }}
    </div>
@endif

<!-- HTMX Modal Container -->
<div
    id="show"
    class="hidden fixed inset-0 z-50 overflow-hidden bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 lg:p-8 flex items-center justify-center"
    onclick="if(event.target === this) closeAthleteModal();"
></div>
