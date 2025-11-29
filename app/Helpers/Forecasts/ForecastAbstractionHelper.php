<?php

namespace App\Helpers\Forecasts;

use App\Helpers\InstanceTrait;
use App\Models\Forecast;
use App\Models\User;

abstract class ForecastAbstractionHelper
{
    use InstanceTrait;

    abstract public function calculateUserPoints(Forecast $forecast, User $user): self;

    abstract public function getMainPoints(): float;

    abstract public function getBonusPoints(): float;
    abstract public function getPointDetails(): array;
}
