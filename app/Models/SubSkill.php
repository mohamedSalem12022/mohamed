<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SubSkill extends Model
{
    protected $fillable = [
        'skill_id',
        'title',
        'description',
        'status',
        'mastery_level',
        'order',
        'notes',
        'resources',
        'practical_tasks',
        'completed_at',
    ];

    protected $casts = [
        'mastery_level' => 'integer',
        'order' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Get Arabic ordinal label (e.g. المهارة الأولى، المهارة الثانية...)
     */
    public static function ordinalLabel(int $index): string
    {
        $ordinals = [
            1 => 'المهارة الأولى',
            2 => 'المهارة الثانية',
            3 => 'المهارة الثالثة',
            4 => 'المهارة الرابعة',
            5 => 'المهارة الخامسة',
            6 => 'المهارة السادسة',
            7 => 'المهارة السابعة',
            8 => 'المهارة الثامنة',
            9 => 'المهارة التاسعة',
            10 => 'المهارة العاشرة',
            11 => 'المهارة الحادية عشرة',
            12 => 'المهارة الثانية عشرة',
            13 => 'المهارة الثالثة عشرة',
            14 => 'المهارة الرابعة عشرة',
            15 => 'المهارة الخامسة عشرة',
            16 => 'المهارة السادسة عشرة',
            17 => 'المهارة السابعة عشرة',
            18 => 'المهارة الثامنة عشرة',
            19 => 'المهارة التاسعة عشرة',
            20 => 'المهارة العشرون',
        ];

        return $ordinals[$index] ?? "المهارة رقم {$index}";
    }

    /**
     * Get human-readable status in Arabic
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'mastered' => 'تم الإتقان ✓',
            'in_progress' => 'قيد التعلم ⚡',
            default => 'لم تبدأ بعد',
        };
    }

    /**
     * Badge styles based on status
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'mastered' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
            'in_progress' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
        };
    }

    /**
     * Parse resources into structured array of items (links, courses, books)
     */
    public function parsedResources(): array
    {
        if (empty($this->resources)) {
            return [];
        }

        // If it's valid JSON
        $decoded = json_decode($this->resources, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Otherwise split by newline
        $lines = preg_split('/\r\n|\r|\n/', $this->resources);
        $items = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed)) {
                // Remove bullet points if any
                $clean = ltrim($trimmed, "-*• \t");
                $items[] = $clean;
            }
        }
        return $items;
    }

    /**
     * Parse practical tasks / action items
     */
    public function parsedPracticalTasks(): array
    {
        if (empty($this->practical_tasks)) {
            return [];
        }

        $decoded = json_decode($this->practical_tasks, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $lines = preg_split('/\r\n|\r|\n/', $this->practical_tasks);
        $items = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed)) {
                $clean = ltrim($trimmed, "-*• \t");
                $items[] = $clean;
            }
        }
        return $items;
    }
}
