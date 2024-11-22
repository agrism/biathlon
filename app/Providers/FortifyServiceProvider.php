<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginViewResponse::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.password.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.password.reset-password', ['request' => $request]);
        });


        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

//        RateLimiter::for('two-factor', function (Request $request) {
//            return Limit::perMinute(5)->by($request->session()->get('login.id'));
//        });

//        Fortify::registerUserUsing(function ($request) {
//            $request->validate([
//                'name' => ['required', 'string', 'max:255'],
//                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
//                'password' => [
//                    'required',
//                    'string',
//                    'confirmed',
//                    Password::min(5) // Set minimum length
//                    ->letters() // Ensure at least one letter
////                    ->mixedCase() // Require upper and lower case
//                    ->numbers() // Require at least one number
////                    ->symbols(), // Require at least one symbol
//                ],
//            ]);
//
//            return User::create([
//                'name' => $request->name,
//                'email' => $request->email,
//                'password' => Hash::make($request->password),
//            ]);
//        });


    }
}
