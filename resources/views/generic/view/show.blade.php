<div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xl p-6 sm:p-7 relative max-w-2xl w-full">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
        @if($helper->title())
            <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">{{ $helper->title() }}</h2>
        @else
            <div></div>
        @endif

        @if($helper->getCloseRouteName())
            <button
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer"
                type="button"
                hx-get="{{ route($helper->getCloseRouteName(), $helper->data()?->id) }}"
                hx-target="#show"
                title="Close"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        @else
            <button
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer"
                type="button"
                onclick="closeAthleteModal()"
                title="Close"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs sm:text-sm">
            <tbody class="divide-y divide-slate-100">
                @foreach($helper->dataKeys() as $index => $dataKey)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-2.5 px-3 font-bold text-slate-500 w-1/3">{{ data_get($helper->headers(), $index) }}</td>
                        <td class="py-2.5 px-3 font-medium text-slate-800">
                            @if(in_array($dataKey, $helper->dataUrlKeys()))
                                <a href="{{ data_get($helper->data(), $dataKey) }}" target="_blank" class="text-sky-600 hover:underline font-bold">{{ data_get($helper->data(), $dataKey) }}</a>
                            @elseif(is_callable($dataKey))
                                {!! $dataKey() !!}
                            @else
                                {{ data_get($helper->data(), $dataKey) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
