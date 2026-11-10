<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailyReflection;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Habit;
use App\Models\ExcuseLog;
use App\Models\WeeklyReview;
use Carbon\Carbon;

class PersonalKnowledgeSearchService
{
    /**
     * Common Arabic stop words to omit when tokenizing search queries
     */
    protected array $stopWords = [
        'في', 'من', 'على', 'عن', 'إلى', 'الى', 'مع', 'هذا', 'هذه', 'ذلك', 'تلك',
        'التي', 'الذي', 'الذين', 'هو', 'هي', 'هم', 'هن', 'أنا', 'نحن', 'انت', 'انتم',
        'ما', 'ماذا', 'لماذا', 'كيف', 'متى', 'أين', 'اين', 'وين', 'شو', 'ايش', 'هل',
        'كان', 'يكون', 'عن', 'قد', 'ثم', 'أو', 'او', 'لا', 'لم', 'لن', 'إن', 'ان',
        'لو', 'كل', 'بعض', 'غير', 'فقط', 'ابحث', 'بحث', 'لي', 'عني', 'ملاحظاتي', 'بياناتي'
    ];

    /**
     * Search personal knowledge base of authenticated user
     *
     * @param User $user
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(User $user, string $query, int $limit = 10): array
    {
        $rawQuery = trim($query);
        if (empty($rawQuery)) {
            return [];
        }

        $normalizedQuery = $this->normalizeArabic($rawQuery);
        $tokens = $this->extractKeywords($normalizedQuery);

        $results = [];

        // 1. Daily Reflections
        $this->searchReflections($user, $rawQuery, $normalizedQuery, $tokens, $results);

        // 2. Tasks & Subtasks
        $this->searchTasks($user, $rawQuery, $normalizedQuery, $tokens, $results);
        $this->searchSubtasks($user, $rawQuery, $normalizedQuery, $tokens, $results);

        // 3. Habits
        $this->searchHabits($user, $rawQuery, $normalizedQuery, $tokens, $results);

        // 4. Excuses
        $this->searchExcuses($user, $rawQuery, $normalizedQuery, $tokens, $results);

        // 5. Weekly Reviews
        $this->searchWeeklyReviews($user, $rawQuery, $normalizedQuery, $tokens, $results);

        // Sort descending by score, then by date
        usort($results, function ($a, $b) {
            if ($b['score'] === $a['score']) {
                return strcmp($b['date_raw'] ?? '', $a['date_raw'] ?? '');
            }
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Search in Daily Reflections
     */
    protected function searchReflections(User $user, string $rawQuery, string $normalizedQuery, array $tokens, array &$results): void
    {
        $reflections = $user->dailyReflections()->latest('date')->get();

        foreach ($reflections as $r) {
            $fullContent = ($r->notes ?? '') . ' ' . ($r->best_win ?? '') . ' ' . ($r->lessons_learned ?? '');
            if (empty(trim($fullContent))) continue;

            $normContent = $this->normalizeArabic($fullContent);
            $score = $this->calculateScore($normContent, $normalizedQuery, $tokens);

            if ($score > 0) {
                $snippet = $this->createSnippet($fullContent, $normContent, $tokens);
                $dateFormatted = $r->date ? Carbon::parse($r->date)->locale('ar')->translatedFormat('d M Y') : 'بدون تاريخ';

                $results[] = [
                    'id' => $r->id,
                    'type' => 'reflection',
                    'type_label' => 'تأمل / ملاحظة يومية',
                    'icon' => 'fa-book-open',
                    'title' => 'تأمل يوم: ' . $dateFormatted,
                    'snippet' => $snippet,
                    'date' => $dateFormatted,
                    'date_raw' => $r->date ? $r->date->toDateString() : '',
                    'url' => route('reflections.index') . '?date=' . ($r->date ? $r->date->format('Y-m-d') : ''),
                    'score' => $score + 5, // slightly boost detailed personal notes
                ];
            }
        }
    }

    /**
     * Search in Tasks
     */
    protected function searchTasks(User $user, string $rawQuery, string $normalizedQuery, array $tokens, array &$results): void
    {
        $tasks = $user->tasks()->with('category')->latest()->get();

        foreach ($tasks as $t) {
            $content = $t->title . ' ' . ($t->description ?? '');
            $normContent = $this->normalizeArabic($content);
            $score = $this->calculateScore($normContent, $normalizedQuery, $tokens);

            if ($score > 0) {
                $snippet = $this->createSnippet($content, $normContent, $tokens);
                $statusLabel = $t->status === 'completed' ? 'مكتملة ✓' : 'معلقة';
                $priorityLabel = $t->priority === 'high' ? 'أولوية عالية' : ($t->priority === 'medium' ? 'متوسطة' : 'منخفضة');

                $results[] = [
                    'id' => $t->id,
                    'type' => 'task',
                    'type_label' => 'مهمة (' . $statusLabel . ')',
                    'icon' => 'fa-list-check',
                    'title' => $t->title,
                    'snippet' => $snippet ?: ($priorityLabel . ($t->category ? ' | تصنيف: ' . $t->category->name : '')),
                    'date' => $t->due_date ? Carbon::parse($t->due_date)->locale('ar')->translatedFormat('d M Y') : $t->created_at->locale('ar')->translatedFormat('d M Y'),
                    'date_raw' => $t->created_at->toDateString(),
                    'url' => route('tasks.index'),
                    'score' => $score + 3,
                ];
            }
        }
    }

    /**
     * Search in Subtasks
     */
    protected function searchSubtasks(User $user, string $rawQuery, string $normalizedQuery, array $tokens, array &$results): void
    {
        $subtasks = Subtask::whereHas('task', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('task')->latest()->get();

        foreach ($subtasks as $st) {
            $normTitle = $this->normalizeArabic($st->title);
            $score = $this->calculateScore($normTitle, $normalizedQuery, $tokens);

            if ($score > 0) {
                $parentTitle = $st->task?->title ?? 'مهمة رئيسية';
                $status = $st->is_completed ? 'منجزة ✓' : 'غير منجزة';

                $results[] = [
                    'id' => $st->id,
                    'type' => 'subtask',
                    'type_label' => 'مهمة فرعية (' . $status . ')',
                    'icon' => 'fa-check-double',
                    'title' => $st->title,
                    'snippet' => 'تابعة للمهمة: «' . $parentTitle . '»',
                    'date' => $st->created_at ? $st->created_at->locale('ar')->translatedFormat('d M Y') : '',
                    'date_raw' => $st->created_at ? $st->created_at->toDateString() : '',
                    'url' => route('tasks.index'),
                    'score' => $score + 1,
                ];
            }
        }
    }

    /**
     * Search in Habits
     */
    protected function searchHabits(User $user, string $rawQuery, string $normalizedQuery, array $tokens, array &$results): void
    {
        $habits = $user->habits()->where('is_archived', false)->get();

        foreach ($habits as $h) {
            $content = $h->name . ' ' . ($h->name_en ?? '') . ' ' . ($h->description ?? '');
            $normContent = $this->normalizeArabic($content);
            $score = $this->calculateScore($normContent, $normalizedQuery, $tokens);

            if ($score > 0) {
                $snippet = $this->createSnippet($content, $normContent, $tokens);
                $streak = $h->getCurrentStreak();

                $results[] = [
                    'id' => $h->id,
                    'type' => 'habit',
                    'type_label' => 'عادة يومية',
                    'icon' => 'fa-repeat',
                    'title' => $h->name,
                    'snippet' => $snippet ?: ('الهدف: ' . $h->target_days_per_week . ' أيام/أسبوع | الالتزام الحالي: ' . $streak . ' أيام متتالية'),
                    'date' => $h->created_at ? $h->created_at->locale('ar')->translatedFormat('d M Y') : '',
                    'date_raw' => $h->created_at ? $h->created_at->toDateString() : '',
                    'url' => route('habits.index'),
                    'score' => $score + 2,
                ];
            }
        }
    }

    /**
     * Search in Excuse Logs
     */
    protected function searchExcuses(User $user, string $rawQuery, string $normalizedQuery, array $tokens, array &$results): void
    {
        $excuses = $user->excuseLogs()->get();

        foreach ($excuses as $e) {
            $content = $e->excuse_details . ' ' . ($e->how_to_overcome ?? '');
            $normContent = $this->normalizeArabic($content);
            $score = $this->calculateScore($normContent, $normalizedQuery, $tokens);

            if ($score > 0) {
                $snippet = $this->createSnippet($content, $normContent, $tokens);
                $status = $e->overcame ? 'تم التغلب عليه ✓' : 'قيد المعالجة';

                $results[] = [
                    'id' => $e->id,
                    'type' => 'excuse',
                    'type_label' => 'عذر وتحدي (' . $status . ')',
                    'icon' => 'fa-shield-halved',
                    'title' => 'عذر: ' . mb_substr($e->excuse_details, 0, 45) . '...',
                    'snippet' => 'طريقة التغلب المقترحة: ' . ($e->how_to_overcome ?: 'غير محددة'),
                    'date' => $e->date ? Carbon::parse($e->date)->locale('ar')->translatedFormat('d M Y') : '',
                    'date_raw' => $e->date ? Carbon::parse($e->date)->toDateString() : '',
                    'url' => route('excuses.index'),
                    'score' => $score + 2,
                ];
            }
        }
    }

    /**
     * Search in Weekly Reviews
     */
    protected function searchWeeklyReviews(User $user, string $rawQuery, string $normalizedQuery, array $tokens, array &$results): void
    {
        $reviews = $user->weeklyReviews()->get();

        foreach ($reviews as $rev) {
            $content = ($rev->biggest_achievement ?? '') . ' ' .
                       ($rev->biggest_obstacle ?? '') . ' ' .
                       ($rev->delay_reasons ?? '') . ' ' .
                       ($rev->improvement_goals ?? '') . ' ' .
                       ($rev->top_3_outcomes ?? '') . ' ' .
                       ($rev->analysis ?? '');

            if (empty(trim($content))) continue;

            $normContent = $this->normalizeArabic($content);
            $score = $this->calculateScore($normContent, $normalizedQuery, $tokens);

            if ($score > 0) {
                $snippet = $this->createSnippet($content, $normContent, $tokens);
                $start = Carbon::parse($rev->week_start_date)->locale('ar')->translatedFormat('d M');
                $end = Carbon::parse($rev->week_end_date)->locale('ar')->translatedFormat('d M Y');

                $results[] = [
                    'id' => $rev->id,
                    'type' => 'weekly_review',
                    'type_label' => 'مراجعة أسبوعية',
                    'icon' => 'fa-chart-pie',
                    'title' => "مراجعة الأسبوع ({$start} - {$end})",
                    'snippet' => $snippet,
                    'date' => $end,
                    'date_raw' => Carbon::parse($rev->week_end_date)->toDateString(),
                    'url' => route('weekly-review.index'),
                    'score' => $score + 4,
                ];
            }
        }
    }

    /**
     * Normalize Arabic text for robust fuzzy & full-text matching
     */
    public function normalizeArabic(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        // Remove diacritics / tashkeel
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        // Normalize Alef variations
        $text = preg_replace('/[أإآٱ]/u', 'ا', $text);

        // Normalize Yaa & Alif Maqsura
        $text = preg_replace('/[ىي]/u', 'ي', $text);

        // Normalize Taa Marbuta & Haa
        $text = preg_replace('/[ةه]/u', 'ه', $text);

        // Remove repeated letters (e.g. سلاااام -> سلام)
        $text = preg_replace('/(.)\1{2,}/u', '$1', $text);

        // Normalize spaces
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Extract meaningful search tokens
     */
    protected function extractKeywords(string $normalized): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        $filtered = [];

        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 2 && !in_array($w, $this->stopWords, true)) {
                $filtered[] = $w;
            }
        }

        return array_unique($filtered);
    }

    /**
     * Calculate relevance score based on query and keyword matches
     */
    protected function calculateScore(string $normContent, string $normQuery, array $tokens): int
    {
        $score = 0;

        // Exact substring match of full query gets high score
        if (!empty($normQuery) && mb_strpos($normContent, $normQuery) !== false) {
            $score += 30;
        }

        // Each token occurrence adds score
        foreach ($tokens as $token) {
            $count = mb_substr_count($normContent, $token);
            if ($count > 0) {
                $score += min(15, $count * 5);
            }
        }

        return $score;
    }

    /**
     * Extract an informative excerpt snippet around the matched tokens
     */
    protected function createSnippet(string $rawContent, string $normContent, array $tokens): string
    {
        $rawContent = strip_tags($rawContent);
        $firstPos = false;

        foreach ($tokens as $token) {
            $pos = mb_strpos($normContent, $token);
            if ($pos !== false) {
                if ($firstPos === false || $pos < $firstPos) {
                    $firstPos = $pos;
                }
            }
        }

        if ($firstPos === false) {
            return mb_substr($rawContent, 0, 140) . (mb_strlen($rawContent) > 140 ? '...' : '');
        }

        $start = max(0, $firstPos - 40);
        $length = 150;
        $snippet = mb_substr($rawContent, $start, $length);

        $prefix = ($start > 0) ? '... ' : '';
        $suffix = (mb_strlen($rawContent) > ($start + $length)) ? ' ...' : '';

        return $prefix . trim($snippet) . $suffix;
    }
}
