<?php

namespace App\Http\Controllers;

use App\Models\WeeklyReview;
use App\Models\Task;
use App\Models\CalendarEvent;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyReviewController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        // Target week
        $startParam = $request->query('start', Carbon::now()->startOfWeek()->toDateString());
        $startDate = Carbon::parse($startParam)->startOfDay();
        $endDate = $startDate->copy()->endOfWeek()->endOfDay();

        // Calculate real stats for this week
        $stats = $this->calculateWeeklyStats($user, $startDate, $endDate);

        // Fetch or create weekly review record
        $review = $user->weeklyReviews()
            ->whereDate('week_start_date', $startDate->toDateString())
            ->first();

        // Next week dates
        $nextWeekStart = $endDate->copy()->addDay()->startOfDay();
        $nextWeekEnd = $nextWeekStart->copy()->endOfWeek()->endOfDay();

        $upcomingTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereBetween('due_date', [$nextWeekStart->toDateString(), $nextWeekEnd->toDateString()])
            ->get();

        return view('weekly-review.index', compact('user', 'startDate', 'endDate', 'stats', 'review', 'upcomingTasks'));
    }

    public function saveAnswers(Request $request)
    {
        $user = Auth::user();

        $startStr = $request->input('start_date', $request->input('week_start_date', Carbon::now()->startOfWeek()->toDateString()));
        $startDate = Carbon::parse($startStr)->startOfDay();
        $endStr = $request->input('end_date', $request->input('week_end_date', $startDate->copy()->endOfWeek()->toDateString()));
        $endDate = Carbon::parse($endStr)->endOfDay();

        $answers = $request->input('answers', [
            'q_best_accomplishment' => $request->input('q_best_accomplishment'),
            'q_what_went_wrong' => $request->input('q_what_went_wrong'),
            'q_habits_discipline' => $request->input('q_habits_discipline'),
            'q_lessons_learned' => $request->input('q_lessons_learned'),
            'q_next_week_focus' => $request->input('q_next_week_focus'),
        ]);

        $rating = (int)$request->input('rating', 5);

        $stats = $this->calculateWeeklyStats($user, $startDate, $endDate);

        $review = $user->weeklyReviews()->updateOrCreate(
            ['week_start_date' => $startDate->toDateString()],
            [
                'week_end_date' => $endDate->toDateString(),
                'stats' => $stats,
                'answers' => $answers,
                'reflection_answers' => $answers,
                'rating' => $rating,
                'is_completed' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ إجابات وتأملات المراجعة الأسبوعية بنجاح! ✓',
            'review' => $review,
        ]);
    }

    /**
     * Run Gemini AI analysis on real weekly stats
     */
    public function analyze(Request $request)
    {
        $user = Auth::user();

        $startStr = $request->input('start_date', $request->input('week_start_date', Carbon::now()->startOfWeek()->toDateString()));
        $startDate = Carbon::parse($startStr)->startOfDay();
        $endStr = $request->input('end_date', $request->input('week_end_date', $startDate->copy()->endOfWeek()->toDateString()));
        $endDate = Carbon::parse($endStr)->endOfDay();

        $stats = $this->calculateWeeklyStats($user, $startDate, $endDate);

        $review = $user->weeklyReviews()
            ->whereDate('week_start_date', $startDate->toDateString())
            ->first();

        $answers = $review?->reflection_answers ?? $review?->answers ?? [];

        // Ask AiService to generate Gemini Analysis
        $analysisResult = $this->aiService->generateWeeklyAnalysis($user, $stats, $answers, $startDate, $endDate);

        // Save AI analysis to review
        $review = $user->weeklyReviews()->updateOrCreate(
            ['week_start_date' => $startDate->toDateString()],
            [
                'week_end_date' => $endDate->toDateString(),
                'stats' => $stats,
                'ai_analysis' => $analysisResult['analysis'],
            ]
        );

        return response()->json([
            'success' => true,
            'analysis' => $analysisResult['analysis'],
            'review' => $review,
            'provider' => $analysisResult['provider'],
        ]);
    }

    /**
     * Suggest next week's plan draft based on unfinished and high priority tasks
     */
    public function planNextWeek(Request $request)
    {
        $user = Auth::user();

        $startStr = $request->input('start_date', $request->input('week_start_date', Carbon::now()->startOfWeek()->toDateString()));
        $startDate = Carbon::parse($startStr)->startOfDay();
        $endStr = $request->input('end_date', $request->input('week_end_date', $startDate->copy()->endOfWeek()->toDateString()));
        $endDate = Carbon::parse($endStr)->endOfDay();

        $nextStart = $endDate->copy()->addDay()->startOfDay();
        $nextEnd = $nextStart->copy()->endOfWeek()->endOfDay();

        $planResult = $this->aiService->generateNextWeekPlanProposal($user, $nextStart, $nextEnd);

        // Save to review
        $review = $user->weeklyReviews()->updateOrCreate(
            ['week_start_date' => $startDate->toDateString()],
            [
                'week_end_date' => $endDate->toDateString(),
                'next_week_plan' => $planResult['plan'],
            ]
        );

        return response()->json([
            'success' => true,
            'next_week_plan' => $planResult['plan'],
            'summary' => $planResult['summary'],
            'review' => $review,
            'provider' => $planResult['provider'],
        ]);
    }

    /**
     * Compute actual stats from database without guessing
     */
    protected function calculateWeeklyStats($user, Carbon $startDate, Carbon $endDate): array
    {
        // 1. Completed tasks in this range
        $completedTasks = $user->tasks()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->get();

        // 2. Pending / Uncompleted tasks due in or before this week
        $pendingTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $endDate->toDateString())
            ->get();

        // 3. Overdue tasks
        $overdueTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', Carbon::today()->toDateString())
            ->get();

        $totalTasks = $completedTasks->count() + $pendingTasks->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks->count() / $totalTasks) * 100) : 0;

        // 4. Focus Sessions recorded in Calendar
        $focusEvents = $user->calendarEvents()
            ->where('event_type', 'focus')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();

        $totalFocusMinutes = 0;
        foreach ($focusEvents as $fe) {
            $totalFocusMinutes += $fe->start_time->diffInMinutes($fe->end_time);
        }

        // 5. Habits compliance in this week
        $habitsCount = $user->habits()->where('is_archived', false)->count();
        $habitLogs = $user->habitLogs()
            ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'completed')
            ->count();
        $possibleLogs = $habitsCount * 7;
        $habitsRate = $possibleLogs > 0 ? round(($habitLogs / $possibleLogs) * 100) : 0;

        return [
            'completed_count' => $completedTasks->count(),
            'pending_count' => $pendingTasks->count(),
            'overdue_count' => $overdueTasks->count(),
            'completion_rate' => $completionRate,
            'focus_sessions_count' => $focusEvents->count(),
            'focus_minutes' => $totalFocusMinutes,
            'habits_rate' => $habitsRate,
            'completed_tasks_titles' => $completedTasks->pluck('title')->take(10)->toArray(),
            'pending_tasks_titles' => $pendingTasks->pluck('title')->take(10)->toArray(),
        ];
    }
}
