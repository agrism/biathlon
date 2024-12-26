<?php

namespace App\Models;

use App\Casts\AthleteDetailsCast;
use App\ValueObjects\Athletes\AthleteDetailsValueObject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property ?string $ibu_id
 * @property ?boolean $is_team
 * @property ?string $family_name
 * @property ?string $given_name
 * @property ?string $other_family_names
 * @property ?string $other_given_names
 * @property ?string $nat
 * @property ?string $nf
 * @property ?Carbon $birth_date
 * @property ?string $gender_id
 * @property ?string $functions
 * @property ?string $badge_value
 * @property ?string $badge_order
 * @property ?string $photo_uri
 * @property ?string $flag_uri
 *
 * @property ?AthleteDetailsValueObject $details
 * @property ?Carbon $details_updated_at
 *
 * @property ?string $stat_season
 * @property ?float $stat_p_total
 * @property ?float $stat_p_sprint
 * @property ?float $stat_p_individual
 * @property ?float $stat_p_pursuit
 * @property ?float $stat_p_mass
 * @property ?float $stat_ski_kmb
 * @property ?float $stat_skiing
 * @property ?float $stat_shooting
 * @property ?float $stat_shooting_prone
 * @property ?float $stat_shooting_standing
 *
 * @property Collection<EventCompetitionResult> $results
 */
class Athlete extends Model
{
    protected $casts = [
        'birth_date' => 'datetime',
        'details' => AthleteDetailsCast::class,
        'details_updated_at' => 'datetime',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(EventCompetitionResult::class);
    }

    public function getFullName(): string
    {
        return implode(', ', array_filter([$this->given_name, $this->family_name]));
    }

    public function attachTempId(bool $isTeamDiscipline): self
    {
        $this->temp_id = $isTeamDiscipline ? $this->nat : $this->id;
        return $this;
    }
}
