<div
    style="border: 1px solid black;padding: 10px; border-radius: 10px;"
>
    @if($helper->title())
        <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-5xl  text-center ">{{$helper->title()}}</h1>
    @endif

    @if($helper->getCloseRouteName())
    <button
        class="float-right bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded"
        type="button"
        hx-get="{{route($helper->getCloseRouteName(), $helper->data()?->id)}}"
        hx-target="#show"
    >close</button>
    @endif

    <table class="min-w-full divide-y divide-gray-200 dark:divide-grey-100">
        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
        @foreach($helper->dataKeys() as $index => $dataKey)
            <tr>
                <td class="px-2 py-1 whitespace-nowrap text-sm font-medium text-gray-800">{{data_get($helper->headers(), $index)}}</td>
                <td class="px-2 py-0 whitespace-nowrap text-sm font-medium text-gray-800">
                @if(in_array($dataKey, $helper->dataUrlKeys()))
                    <a style="color: blue;" href="{{data_get($helper->data(), $dataKey)}}" target="_blank">{{data_get($helper->data(), $dataKey)}}</a>
                @elseif(is_callable($dataKey))
                    {!! $dataKey() !!}
                @else
                    {{data_get($helper->data(), $dataKey)}}
                @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
