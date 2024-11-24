<?php

namespace App\Models;

use App\Casts\ForecastDataCast;
use App\ValueObjects\Helpers\Forecasts\ForecastDataAbstractionValueObject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $forecast_id
 * @property int $user_id
 * @property float $points
 * @property Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ForecastAward extends Model
{
    protected $casts = [
        'id' => 'integer',
        'forecast_id' => 'integer',
        'user_id' => 'integer',
        'points' => 'float',
    ];
}
