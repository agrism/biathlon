<?php

namespace App\Models;

use App\Casts\ForecastFinalDataCast;
use App\Enums\Forecast\ForecastStatusEnum;
use App\Enums\Forecast\ForecastTypeEnum;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\FinalDataValueObject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $event_competition_id
 * @property string $name
 * @property ForecastTypeEnum $type
 * @property ForecastStatusEnum $status
 * @property Carbon $submit_deadline_at
 * @property FinalDataValueObject $final_data
 * @property Carbon $created_at
 * @property ?Carbon $updated_at
 *
 * @property EventCompetition $competition
 * @property Collection<ForecastSubmittedData> $submittedData
 */
class Forecast extends Model
{
    protected $casts = [
        'id' => 'integer',
        'event_competition_id' => 'integer',
        'name' => 'string',
        'type' => ForecastTypeEnum::class,
        'status' => ForecastStatusEnum::class,
        'submit_deadline_at' => 'datetime',
        'final_data' => ForecastFinalDataCast::class,
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(EventCompetition::class, 'event_competition_id');
    }

    public function submittedData(): HasMany
    {
        return $this->hasMany(ForecastSubmittedData::class, 'forecast_id');
    }
}
