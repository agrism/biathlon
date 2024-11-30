<?php

namespace App\Models;

use App\Casts\ForecastDataCast;
use App\ValueObjects\Helpers\Forecasts\ForecastDataAbstractionValueObject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property integer $forecast_id
 * @property int $user_id
 * @property ForecastDataAbstractionValueObject $submitted_data
 * @property Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ForecastSubmittedData extends Model
{
    protected $casts = [
        'id' => 'integer',
        'forecast_id' => 'integer',
        'user_id' => 'integer',
        'submitted_data' => ForecastDataCast::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forecast(): BelongsTo
    {
        return $this->belongsTo(Forecast::class);
    }
}
