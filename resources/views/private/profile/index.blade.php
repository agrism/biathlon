@extends('layouts.admin', ['heading' => 'Account & Profile Settings'])

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <x-alerts.errors />
        <x-alerts.status />

        <!-- User Identity Hero Card -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 rounded-3xl p-6 sm:p-8 text-white shadow-md flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                <!-- User Avatar / Initials -->
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white font-black text-2xl flex items-center justify-center shadow-inner flex-shrink-0 border border-white/20">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center justify-center sm:justify-start gap-2">
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ auth()->user()->name }}</h2>
                        <span class="px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-[11px] font-bold">Player</span>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">{{ auth()->user()->email }}</p>
                    <div class="flex items-center justify-center sm:justify-start gap-4 mt-2 text-[11px] text-slate-400">
                        <span>Member since {{ auth()->user()->created_at?->format('M Y') ?? '2026' }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Status Badges -->
            <div class="flex items-center gap-3">
                <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-300 block">Security</span>
                    <span class="text-xs font-black text-amber-300">
                        @if(auth()->user()->two_factor_secret)
                            2FA Active
                        @else
                            Standard
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Settings Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Update Profile Info Card -->
            <div>
                @include('private.profile.update-profile-information')
            </div>

            <!-- Update Password Card -->
            <div>
                @include('private.profile.update-password')
            </div>

            <!-- Two Factor Authentication Card -->
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
                <div class="lg:col-span-2">
                    @include('private.profile.two-factor-authentication')
                </div>
            @endif
        </div>
    </div>
@endsection
