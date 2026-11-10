<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyPlan extends Model
{
    protected $fillable = [
        'user_id',
        'plan_date',
        'summary',
        'start_hour',
        'end_hour',
        'is_accepted',
    ];

    protected function casts(): array
    {
        return [
            'plan_date' => 'date',
            'is_accepted' => 'boolean',
        ];
    }

    public function getIsApprovedAttribute(): bool
    {
        return (bool)$this->is_accepted;
    }

    public function setIsApprovedAttribute($value): void
    {
        $this->attributes['is_accepted'] = (bool)$value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyPlanItem::class)->orderBy('start_time', 'asc')->orderBy('order', 'asc');
    }
}
