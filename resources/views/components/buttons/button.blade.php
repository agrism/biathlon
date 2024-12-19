<button
    {{ $attributes->merge(['type' => 'button','class' => 'bg-transparent hover:bg-blue-400 text-blue-500 hover:text-white py-0 px-2 border border-blue-500 hover:border-transparent rounded']) }}>
    {{$slot}}
</button>
