@if($subTitle)
    <div class="mb-6 text-center">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{!! $subTitle !!}</h1>
    </div>
@endif

<!-- Top Action Bar (Export, Filters) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
    <!-- Filter Form -->
    @if($helper->filters())
        <form @if($helper->getFilterHtmxFormAttributes()) {!! $helper->getFilterHtmxFormAttributes() !!} @else method="GET" @endif class="w-full sm:w-auto">
            @csrf
            <div class="inline-flex flex-wrap items-center gap-2.5 p-2 rounded-2xl bg-white border border-slate-200 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2">
                    <i class="fa-solid fa-filter text-sky-500 mr-1"></i> Filter
                </span>
                
                @foreach($helper->filters() as $filter)
                    <div class="flex items-center gap-1">
                        <label class="text-xs font-semibold text-slate-600 px-1" for="{{ $filter->key }}">{{ $filter->title ?: $filter->key }}:</label>
                        {!! $filter->inputType->getElement(name: $filter->key, value: $filter->value, style: '', classes: 'text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-transparent outline-none transition-all', options: $filter->options) !!}
                    </div>
                @endforeach

                <div class="flex items-center gap-1.5 ml-auto">
                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-xs transition-colors">
                        Apply
                    </button>
                    <button type="submit" name="clear" value="1" class="inline-flex items-center text-xs font-semibold px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
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
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 shadow-xs transition-colors"
                    type="button"
                    hx-target="#show"
                >
                    <i class="fa-solid fa-file-csv text-emerald-600"></i>
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

<!-- Main Table Card Container -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-4">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    @foreach($helper->headers() as $name)
                        <th scope="col" class="px-4 py-3 font-bold">
                            {!! $name !!}
                        </th>
                    @endforeach

                    @if($helper->showRouteName())
                        <th scope="col" class="px-4 py-3 text-right font-bold">
                            Action
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($helper->data() as $dataItem)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        @foreach($helper->dataKeys() as $key)
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700 font-medium">
                                @if(in_array($key, $helper->dataUrlKeys()))
                                    <a href="{{ data_get($dataItem, $key) }}" target="_blank" class="text-sky-600 hover:underline font-semibold">Link</a>
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
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <button
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 font-semibold text-xs border border-slate-200 hover:border-sky-200 transition-colors"
                                    type="button"
                                    hx-get="{{ route($routeName, $dataItem->id) }}"
                                    hx-target="#show"
                                >
                                    <span>View</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-8 text-center text-slate-400 italic">
                            No records found.
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

<div id="show" class="fixed inset-x-4 top-4 z-50 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 max-h-[90vh] overflow-y-auto hidden empty:hidden">
</div>
