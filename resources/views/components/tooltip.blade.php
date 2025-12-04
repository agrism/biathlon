@php
    $position = $position ?? 'top';
    if(!in_array($position, ['top', 'right'])){
        $position = 'top';
    }

    $show = $show ?? true;
@endphp

@if($show)

<div class="relative inline-block group">
    {{$slot}}

    @if($position === 'top')
        <div
            class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-500 text-white text-xs rounded whitespace-nowrap z-10">
            {{$text ?? ''}}
            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-500"></div>
        </div>

        @elseif($position == 'right')
        <div
            class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition left-full top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-500 text-white text-xs rounded whitespace-nowrap z-10">
            {{$text ?? ''}}
            <!-- Arrow pointing left instead of down -->
            <div class="absolute top-1/2 right-full -translate-y-1/2 border-4 border-transparent border-r-gray-500"></div>
        </div>
    @endif


</div>

@else
    {{$slot}}
@endif
