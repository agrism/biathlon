@if (session('status'))
    <div class="max-w-md mx-auto mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 shadow-2xs">
        <div class="flex items-center gap-2 text-emerald-800 text-xs font-bold">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            <span>{{ session('status') }}</span>
        </div>
    </div>
@endif
