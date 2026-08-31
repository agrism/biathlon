<div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-7 h-full flex flex-col justify-between">
    <div>
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-base flex-shrink-0">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Profile Information</h2>
                <p class="text-xs text-slate-400">Update your account name and email address</p>
            </div>
        </div>

        <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="name" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-user text-slate-400 text-[11px]"></i>
                    <span>{{ __('Display Name') }}</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') ?? auth()->user()->name }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                />
            </div>

            <div class="space-y-1.5">
                <label for="email" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-envelope text-slate-400 text-[11px]"></i>
                    <span>{{ __('Email Address') }}</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') ?? auth()->user()->email }}"
                    required
                    autocomplete="email"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                />
            </div>

            <div class="flex justify-end pt-3">
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-extrabold text-xs shadow-md shadow-sky-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-2 cursor-pointer"
                >
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>{{ __('Save Profile') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
