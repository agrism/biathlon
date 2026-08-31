<div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-7 h-full flex flex-col justify-between">
    <div>
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base flex-shrink-0">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Update Password</h2>
                <p class="text-xs text-slate-400">Ensure your account uses a strong, unique password</p>
            </div>
        </div>

        <form method="POST" action="{{ route('user-password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="current_password" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-lock text-slate-400 text-[11px]"></i>
                    <span>{{ __('Current Password') }}</span>
                </label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                />
            </div>

            <div class="space-y-1.5">
                <label for="password" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-key text-slate-400 text-[11px]"></i>
                    <span>{{ __('New Password') }}</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                />
            </div>

            <div class="space-y-1.5">
                <label for="password_confirmation" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-shield-halved text-slate-400 text-[11px]"></i>
                    <span>{{ __('Confirm New Password') }}</span>
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                />
            </div>

            <div class="flex justify-end pt-3">
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-extrabold text-xs shadow-md shadow-sky-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-2 cursor-pointer"
                >
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>{{ __('Update Password') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
