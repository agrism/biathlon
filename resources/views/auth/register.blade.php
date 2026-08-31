@extends('layouts.admin', ['heading' => ''])

@section('content')
    <div class="max-w-md mx-auto my-4 sm:my-8">
        <x-alerts.errors />
        <x-alerts.status />

        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-blue-700 text-white shadow-md shadow-sky-500/20 mb-3 p-2">
                    <img src="{{ asset('img.png') }}" alt="Biathlon" class="w-full h-full object-contain brightness-0 invert">
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Create Account</h1>
                <p class="text-xs text-slate-500 mt-1">Join the community, make predictions, and compete on the leaderboards</p>
            </div>

            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Name Input -->
                <div class="space-y-1.5">
                    <label for="name" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <i class="fa-solid fa-user text-slate-400 text-[11px]"></i>
                        <span>{{ __('Full Name / Nickname') }}</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Johannes Boe"
                        class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                    />
                </div>

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <i class="fa-solid fa-envelope text-slate-400 text-[11px]"></i>
                        <span>{{ __('Email Address') }}</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white transition-all"
                    />
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <i class="fa-solid fa-lock text-slate-400 text-[11px]"></i>
                        <span>{{ __('Password') }}</span>
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

                <!-- Password Confirmation Input -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <i class="fa-solid fa-shield-halved text-slate-400 text-[11px]"></i>
                        <span>{{ __('Confirm Password') }}</span>
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

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-3 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-extrabold text-sm shadow-md shadow-sky-500/20 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        <span>{{ __('Create Account') }}</span>
                    </button>
                </div>
            </form>

            <!-- Sign In Link -->
            @if (Route::has('login'))
                <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
                    <span>Already registered?</span>
                    <a href="{{ route('login') }}" class="font-bold text-sky-600 hover:text-sky-700 transition-colors ml-1">
                        {{ __('Sign In') }} &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
