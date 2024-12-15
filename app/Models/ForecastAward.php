<?php

namespace App\Models;

use App\Enums\Forecast\AwardPointEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer $id
 * @property integer $forecast_id
 * @property int $user_id
 * @property AwardPointEnum $type
 * @property float $points
 * @property Carbon $created_at
 * @property ?Carbon $updated_at
 *
 * @property User $user
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
