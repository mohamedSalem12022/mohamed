<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReflection extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'notes',
        'best_win',
        'lessons_learned',
        'rating',
        'tomorrow_improvements',
    ];

    protected $casts = [
        'date' => 'date',
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
