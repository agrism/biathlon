@if($subTitle)
    <h1 class="mb-4 mt-10 text-xl font-extrabold leading-none tracking-tight text-gray-900 md:text-xl lg:text-xl  text-center ">{{$subTitle}}</h1>
@endif


<div class="flex flex-row-reverse">
    @php /** @var \App\Helpers\Generic\GenericViewIndexHelper $helper */@endphp
    @if($helper->isExportButtonVisible())
        <a href="{{request()->url() . '?export=excel'}}">
            <button
                class="my-2 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-0 px-2 border border-blue-500 hover:border-transparent rounded"
                type="button"
                hx-target="#show"
            >Export to CSV
            </button>
        </a>
    @endif
</div>

@if($helper->filters())
    <form @if($helper->getFilterHtmxFormAttributes()) {!!$helper->getFilterHtmxFormAttributes()!!}  @else method="GET" @endif  >
        @csrf
        <div style="border:2px solid grey; padding: 10px 10px 10px 0;margin: 10px;display: inline-block; border-radius: 5px;">
            <div style="position: absolute;margin-left: 10px;margin-top: -20px; border: 2px solid grey;display: inline-block;background-color: white;padding: 0px 10px;border-radius: 5px;font-size: 12px;">Filter</div>
            <table>
                @foreach($helper->filters() as $filter)
                    <tr>
                        <td><label class="text-sm px-2" for="{{$filter->key}}">{{$filter->title ?: $filter->key}}:</label></td>
                        <td>
                            {!! $filter->inputType->getElement(name: $filter->key, value: $filter->value, style: 'padding-top: 0;padding-bottom: 0; border: 1px solid black;', classes: 'text-sm px-2', options: $filter->options) !!}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td>
                        <button class="text-sm bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-0 px-2 border border-blue-500 hover:border-transparent rounded">Apply</button>
                        <button class="text-sm bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-0 px-2 border border-red-500 hover:border-transparent rounded" name="clear" value="1">Clear</button>
                    </td>
                </tr>
            </table>
        </div>
    </form>
@endif
<div class="px-2 py-2">
    {{ $helper->data()->links('pagination::tailwind-white', ['useHtmx'=> $useHtmx, 'htmxTargetElement' => $htmxTargetElement]) }}
</div>
<table
    border="1"
    style="border-collapse: collapse;"
    class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700"
>
    <thead>
    <tr>
        @foreach($helper->headers() as $name)
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                {!! $name !!}
            </th>
        @endforeach

        @if($helper->showRouteName())
            <th scope="col"
                class="px-2 py-2 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                action
            </th>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach($helper->data() as $dataItem)
        <tr class="odd:bg-white even:bg-gray-100">
            @foreach($helper->dataKeys() as $key)
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                    @if(in_array($key, $helper->dataUrlKeys()))
                        <a href="{{data_get($dataItem, $key)}}" target="_blank" style="color: blue">Link</a>
                    @else
                        @if(!is_string($key) && is_callable($key))
                            {!! $key($dataItem) !!}
                        @else
                            {{data_get($dataItem, $key)}}
                        @endif
                    @endif
                </td>
            @endforeach

            @if($routeName = $helper->showRouteName())
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                    <button
                        class="float-right bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-0 px-2 border border-blue-500 hover:border-transparent rounded"
                        type="button"
                        hx-get="{{route($routeName, $dataItem->id)}}"
                        hx-target="#show"
                    >View
                    </button>
                </td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>

<div class="px-2 py-2">
    {{ $helper->data()->links('pagination::tailwind-white', ['useHtmx'=> $useHtmx, 'htmxTargetElement' => $htmxTargetElement]) }}
</div>

<div id="show"
     style="position: fixed;top:5px;left: 5px;right: 5px;background-color: white;width: 98%;"
>

</div>
