<button
    {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-semibold text-xs shadow-sm shadow-sky-500/20 hover:shadow-sky-500/30 transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
