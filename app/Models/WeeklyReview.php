<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReview extends Model
{
    protected $fillable = [
        'user_id',
        'week_start_date',
        'week_end_date',
        'stats',
        'answers',
        'ai_analysis',
        'next_week_plan',
        'is_completed',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'week_end_date' => 'date',
            'stats' => 'array',
            'answers' => 'array',
            'next_week_plan' => 'array',
            'is_completed' => 'boolean',
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
