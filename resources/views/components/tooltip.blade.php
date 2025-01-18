<div class="relative inline-block group">
    {{$slot}}
    <div
        class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-500 text-white text-xs rounded whitespace-nowrap">
        {{$text ?? ''}}
        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-500"></div>
    </div>
</div>
