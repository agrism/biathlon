<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $event_id
 * @property ?string $race_remote_id
 * @property ?string $km
 * @property ?string $cat_remote_id
 * @property ?string $discipline_remote_id
 * @property ?Carbon $start_time
 * @property ?string $description
 * @property ?string $short_description
 * @property ?string $location
 * @property ?string $nr_shootings
 * @property ?string $nr_spare_rounds
 * @property ?boolean $has_spare_rounds
 * @property ?integer $penalty_seconds
 * @property ?integer $nr_legs
 * @property ?string $shooting_positions
 * @property ?integer $local_utc_offset
 * @property ?string $rsc
 */
class EventCompetition extends Model
{
    protected $casts = [
        'start_time' => 'datetime',
    ];
}
