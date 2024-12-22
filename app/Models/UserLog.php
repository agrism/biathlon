<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $method
 * @property string $path
 */
class UserLog extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'method' => 'string',
        'path' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
