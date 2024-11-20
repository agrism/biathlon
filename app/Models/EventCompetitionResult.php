<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer $id
 * @property integer $event_competition_id
 * @property ?integer $start_order
 * @property ?integer $result_order
 * @property ?string $irm
 * @property ?integer $athlete_id
 * @property ?string $bib
 * @property ?integer $leg
 * @property ?string $rank
 * @property ?string $shootings
 * @property ?string $shooting_total
 * @property ?string $run_time
 * @property ?string $total_time
 * @property ?string $wc
 * @property ?string $nc
 * @property ?string $noc
 * @property ?string $start_time
 * @property ?string $start_info
 * @property ?string $start_row
 * @property ?string $start_lane
 * @property ?string $bib_color
 * @property ?string $behind
 * @property ?string $start_group
 * @property ?string $team_id
 * @property ?string $pursuit_start_distance
 * @property ?string $result
 * @property ?string $leg_rank
 * @property ?string $team_rank_after_leg
 * @property ?string $start_confirmed
 */
class EventCompetitionResult extends Model
{
    protected $casts = [

    ];

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'athlete_id');
    }
}
