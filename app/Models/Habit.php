<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Habit extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'name_en',
        'description',
        'color',
        'icon',
        'target_days_per_week',
        'order',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'target_days_per_week' => 'integer',
        'order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    public function excuses(): HasMany
    {
        return $this->hasMany(ExcuseLog::class);
    }

    /**
     * Get completion rate for a specific month (year, month).
     */
    public function getMonthlyCompletionRate(int $year, int $month): float
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $now = Carbon::now();

        // If it's the current month, only count days up to today
        $applicableDays = ($year == $now->year && $month == $now->month)
            ? $now->day
            : ($year < $now->year || ($year == $now->year && $month < $now->month) ? $daysInMonth : 0);

        if ($applicableDays === 0) {
            return 0.0;
        }

        $completedCount = $this->logs()
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $month)
            ->where('status', 'completed')
            ->where('log_date', '<=', $now->toDateString())
            ->count();

        return round(($completedCount / $applicableDays) * 100, 1);
    }

    /**
     * Calculate current consecutive streak in days.
     */
    public function getCurrentStreak(): int
    {
        $today = Carbon::today();
        $streak = 0;
        $currentDate = $today->copy();

        // Check if completed today, or start from yesterday if today isn't logged yet
        $hasToday = $this->logs()->whereDate('log_date', $today)->where('status', 'completed')->exists();
        if (!$hasToday) {
            $currentDate = $today->copy()->subDay();
        }

        while (true) {
            $log = $this->logs()
                ->whereDate('log_date', $currentDate)
                ->first();

            if ($log && $log->status === 'completed') {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}
