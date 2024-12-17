<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
 * @property ?Carbon $results_handled_at
 *
 * @property Collection<EventCompetitionResult> $results
 * @property Event $event
 * @property Forecast forecast
 */
class EventCompetition extends Model
{
    protected $casts = [
        'start_time' => 'datetime',
        'results_handled_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(EventCompetitionResult::class, 'event_competition_id', 'id');
    }

    public function forecast(): HasOne
    {
        return $this->hasOne(Forecast::class);
    }

    /**
     * @return Collection<EventCompetitionResult>
     */
    public function getAthletesByRank(bool $isTeamDiscipline, ?int $limit = null): Collection
    {
        $return =  $this->results
            ->filter(function (EventCompetitionResult $result):bool{
                return $result->rank != null;
            })
            ->sortBy('rank')
            ->filter(function (EventCompetitionResult $result) use ($isTeamDiscipline): bool {
                if ($isTeamDiscipline) {
                    return $result->athlete->is_team;
                }
                return !$result->athlete->is_team;
            })
            ->map(function(EventCompetitionResult $result) use($isTeamDiscipline): EventCompetitionResult{
                $result->athlete?->attachTempId(isTeamDiscipline: $isTeamDiscipline);
                return $result;
            });

        if($limit){
            $return = $return->take($limit);
        }

        return $return->values();
    }

    public function getTitle(): string
    {
        $title = [];
        $title[] = $this->description;
        $title[] = $this->event->description;
        if ($this->event->event_series_no) {
            if ($no = trim($this->event->event_series_no)) {
                $title[] = 'stage ' . $no;
            }
        }
        $title[] = $this->event->organizer;
        $title[] = $this->event->nat_long;
        $title[] = $this->start_time?->setTimeZone('Europe/RIga')->format('d F Y, H:i');
        $title = array_filter($title);

        $title = array_map(function ($item) {
            return str_replace(' ', '&nbsp;', $item);
        }, $title);

        return implode(', ', $title);
    }
}
