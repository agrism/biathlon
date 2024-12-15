<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $name_remote
 * @property string $name
 */
class Season extends Model
{
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
