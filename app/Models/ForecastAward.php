<?php

namespace App\Models;

use App\Enums\Forecast\AwardPointEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $forecast_id
 * @property int $user_id
 * @property AwardPointEnum $type
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
        'type' => AwardPointEnum::class,
    ];
}
