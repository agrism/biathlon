@if ($errors->any())
    <div class="max-w-md mx-auto mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 shadow-2xs">
        <div class="flex items-center gap-2 text-rose-800 text-xs font-bold mb-2">
            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
            <span>{{ __('Please check the form for errors:') }}</span>
        </div>

        <ul class="space-y-1 text-xs text-rose-700 pl-5 list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
