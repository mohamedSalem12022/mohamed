<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcuseLog extends Model
{
    protected $fillable = [
        'user_id',
        'habit_id',
        'task_id',
        'date',
        'excuse_type',
        'excuse_details',
        'how_to_overcome',
        'overcame',
    ];

    protected $casts = [
        'date' => 'date',
        'overcame' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
