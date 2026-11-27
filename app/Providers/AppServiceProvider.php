<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::macro('toLatviaTime', function (string $format = 'D, d M • H:i') {
            /** @var Carbon $this */
            $tz = $this->copy()->tz('Europe/Riga');
            $offset = $tz->offsetHours;
            $sign = $offset >= 0 ? '+' : '';
            return $tz->format($format) . ' UTC' . $sign . $offset;
        });

        Carbon::macro('toRiga', function () {
            /** @var Carbon $this */
            return $this->copy()->tz('Europe/Riga');
        });
    }
}
