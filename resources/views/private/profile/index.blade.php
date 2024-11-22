<x-layouts.app>

        {{-- EXAMPLE: this section contains forms and routes not readily accesible upon intital installation. It is meant to be repalced --}}
        <section class="grid grid-cols-2 gap-2 px-4 mx-auto">
            @php
            $views['profile.update-profile-information'] = config('fortify-ui.views.update-profile-information');
            $views['profile.update-password'] = config('fortify-ui.views.update-password');
            $views['profile.two-factor-authentication'] = config('fortify-ui.views.two-factor-authentication');
            @endphp

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div>
                @include($views['profile.update-password'])
            </div>
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updateProfileInformation()))
            <div>
                @include($views['profile.update-profile-information'])
            </div>
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
            <div class="px-6 py-4 border border-gray-300 rounded">
                <h2 class="mb-4 font-semibold text-blue-400 uppercase">Two factor codes</h2>
                @if (auth()->user()->two_factor_recovery_codes)
                <div>
                    <a href="{{ url('/user/two-factor-qr-code') }}"
                        class="text-blue-600 transition duration-150 ease-in-out hover:text-blue-400 focus:outline-none focus:underline">
                        {{ __('Two factor QR code') }}
                    </a>
                </div>
                <div>
                    <a href="{{ url('/user/two-factor-recovery-codes') }}"
                        class="text-blue-600 transition duration-150 ease-in-out hover:text-blue-400 focus:outline-none focus:underline">
                        {{ __('Two factor recovery codes') }}
                    </a>
                </div>
                @endif
                <div>
                    @include($views['profile.two-factor-authentication'])
                </div>
            </div>
            @endif

            <div class="px-6 py-4 border border-gray-300 rounded">
                <h2 class="mb-4 font-semibold text-blue-400 uppercase">Confirm and Verify</h2>
                @if (Route::has('password.confirm'))
                <div>
                    <a href="{{ route('password.confirm') }}"
                        class="text-blue-600 transition duration-150 ease-in-out hover:text-blue-400 focus:outline-none focus:underline">
                        {{ __('Confirm your password?') }}
                    </a>
                </div>
                @endif
                @if (Route::has('password.confirmation'))
                <div>
                    <a href="{{ route('password.confirmation') }}"
                        class="text-blue-600 transition duration-150 ease-in-out hover:text-blue-400 focus:outline-none focus:underline">
                        {{ __('Password confirmation status') }}
                    </a>
                </div>
                @endif
                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()))
                <div>
                    <a href="{{ route('verification.notice') }}"
                        class="text-blue-600 transition duration-150 ease-in-out hover:text-blue-400 focus:outline-none focus:underline">
                        {{ __('Email verification') }}
                    </a>
                </div>
                @endif
            </div>

        </section>
</x-layouts.app>
