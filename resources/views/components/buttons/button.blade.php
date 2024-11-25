<button
    {{ $attributes->merge(['type' => 'button','class' => 'bg-transparent hover:bg-blue-400 text-blue-500 hover:text-white py-1 px-4 border border-blue-500 hover:border-transparent rounded']) }}>
    {{$slot}}
</button>
