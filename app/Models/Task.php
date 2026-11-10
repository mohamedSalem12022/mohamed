<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'due_date',
        'due_time',
        'priority',
        'status',
        'recurrence',
        'completed_at',
        'order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function excuses(): HasMany
    {
        return $this->hasMany(ExcuseLog::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    public function syncStatusWithSubtasks(): void
    {
        $total = $this->subtasks()->count();
        if ($total === 0) {
            return;
        }

        $completed = $this->subtasks()->where('is_completed', true)->count();
        if ($completed === $total && $this->status !== 'completed') {
            $this->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } elseif ($completed < $total && $this->status === 'completed') {
            $this->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
        }
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function dailyPlanItems(): HasMany
    {
        return $this->hasMany(DailyPlanItem::class);
    }
}
