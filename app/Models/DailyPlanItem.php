<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyPlanItem extends Model
{
    protected $fillable = [
        'daily_plan_id',
        'task_id',
        'title',
        'start_time',
        'end_time',
        'duration_minutes',
        'type',
        'priority',
        'notes',
        'is_break',
        'is_buffer',
        'is_locked',
        'is_completed',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_break' => 'boolean',
            'is_buffer' => 'boolean',
            'is_locked' => 'boolean',
            'is_completed' => 'boolean',
            'duration_minutes' => 'integer',
            'order' => 'integer',
        ];
    }

    public function dailyPlan(): BelongsTo
    {
        return $this->belongsTo(DailyPlan::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
