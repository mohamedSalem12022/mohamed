<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'primary_color',
        'accent_color',
        'currency',
        'timezone',
        'week_starts_on',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
