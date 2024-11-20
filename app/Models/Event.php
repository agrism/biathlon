<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $season_id
 * @property string $trimester
 * @property string $event_remote_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon $first_competition_date
 * @property string $description
 * @property integer $event_series_no
 * @property integer $short_description
 * @property ?integer $altitude
 * @property ?string $organizer_remote_id
 * @property ?string $medal_set_id
 * @property ?string $organizer
 * @property ?string $nat
 * @property ?string $nat_long
 * @property ?string $event_classification_id
 * @property ?integer $level
 * @property ?integer $utc_offset
 * @property ?string $notes
 */
class Event extends Model
{
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'first_competition_date' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(EventCompetition::class, 'event_id', 'id');
    }
}
