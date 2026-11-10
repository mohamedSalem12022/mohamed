<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Skill extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'goal',
        'icon',
        'color',
        'image',
        'start_date',
        'target_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subSkills(): HasMany
    {
        return $this->hasMany(SubSkill::class)->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    public function totalSubSkillsCount(): int
    {
        return $this->subSkills->count();
    }

    public function masteredCount(): int
    {
        return $this->subSkills->where('status', 'mastered')->count();
    }

    public function inProgressCount(): int
    {
        return $this->subSkills->where('status', 'in_progress')->count();
    }

    public function notStartedCount(): int
    {
        return $this->subSkills->where('status', 'not_started')->count();
    }

    /**
     * Calculate overall mastery percentage (0-100%)
     */
    public function progressPercentage(): int
    {
        $count = $this->subSkills->count();
        if ($count === 0) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $avgLevel = $this->subSkills->avg('mastery_level') ?? 0;
        return (int) round($avgLevel);
    }

    /**
     * Get the next recommended sub-skill to work on
     */
    public function nextRecommendedSubSkill(): ?SubSkill
    {
        // First try to find one that is already in progress
        $inProgress = $this->subSkills->firstWhere('status', 'in_progress');
        if ($inProgress) {
            return $inProgress;
        }

        // Otherwise return the first not started
        return $this->subSkills->firstWhere('status', 'not_started');
    }

    /**
     * Human-readable remaining days or timeline status
     */
    public function timelineStatus(): array
    {
        if (!$this->target_date) {
            return ['text' => 'بدون موعد نهائي', 'class' => 'text-slate-500 bg-slate-100 dark:bg-slate-800'];
        }

        $now = Carbon::now()->startOfDay();
        $target = $this->target_date->copy()->startOfDay();

        if ($this->progressPercentage() === 100 || $this->status === 'completed') {
            return ['text' => 'تم الإتقان بالكامل ✓', 'class' => 'text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800'];
        }

        if ($now->greaterThan($target)) {
            $daysPast = $now->diffInDays($target);
            return ['text' => "متأخر {$daysPast} يوم", 'class' => 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800'];
        }

        $daysLeft = $now->diffInDays($target);
        if ($daysLeft === 0) {
            return ['text' => 'الموعد المستهدف اليوم!', 'class' => 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800'];
        }

        return ['text' => "متبقي {$daysLeft} يوم", 'class' => 'text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800'];
    }
}
