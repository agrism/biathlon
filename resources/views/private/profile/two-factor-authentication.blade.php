<div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-7 h-full flex flex-col justify-between">
    <div>
        <div class="flex items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Two-Factor Authentication</h2>
                    <p class="text-xs text-slate-400">Add extra security to your prediction account</p>
                </div>
            </div>

            @if(auth()->user()->two_factor_secret)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Enabled
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                    Disabled
                </span>
            @endif
        </div>

        @if (! auth()->user()->two_factor_secret)
            <div class="mb-4 text-xs text-slate-600 leading-relaxed">
                When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication from your mobile authenticator app.
            </div>

            <form method="POST" action="{{ url('user/two-factor-authentication') }}">
                @csrf
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-extrabold text-xs shadow-md shadow-emerald-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-2 cursor-pointer"
                >
                    <i class="fa-solid fa-shield-check text-xs"></i>
                    <span>{{ __('Enable 2FA') }}</span>
                </button>
            </form>
        @else
            @if (session('status') == 'two-factor-authentication-enabled')
                <div class="mb-4 p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs leading-relaxed">
                    {{ __('Two-factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application.') }}
                </div>

                <div class="my-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 flex justify-center">
                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                </div>
            @endif

            <div class="mb-4 text-xs text-slate-600 leading-relaxed">
                {{ __('Store these recovery codes in a secure password manager. They can be used to recover access if your authenticator device is lost.') }}
            </div>

            @if(auth()->user()->two_factor_recovery_codes)
                <div class="my-4 p-4 rounded-2xl bg-slate-900 text-slate-100 font-mono text-xs grid grid-cols-2 gap-2">
                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                        <div class="tracking-wider">{{ $code }}</div>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <form method="POST" action="{{ url('user/two-factor-recovery-codes') }}">
                    @csrf
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-colors cursor-pointer"
                    >
                        {{ __('Regenerate Recovery Codes') }}
                    </button>
                </form>

                <form method="POST" action="{{ url('user/two-factor-authentication') }}">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-colors cursor-pointer"
                    >
                        {{ __('Disable 2FA') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
