<?php

namespace App\Models;

use App\Enums\FavoriteTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property FavoriteTypeEnum $type
 * @property integer $type_id
 * @property Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Favorite extends Model
{
    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'type' => FavoriteTypeEnum::class,
        'type_id' => 'integer',
    ];
}
