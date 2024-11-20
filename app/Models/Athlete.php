<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
 */
class Athlete extends Model
{
    protected $casts = [
        'birth_date' => 'datetime',
    ];
}
