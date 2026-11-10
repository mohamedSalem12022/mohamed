<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $selectedYear = (int)$request->input('year', $now->year);
        $selectedMonth = (int)$request->input('month', $now->month);

        $habits = $user->habits()->where('is_archived', false)->get();

        // 1. Per-habit stats for selected month
        $habitsStats = [];
        foreach ($habits as $habit) {
            $rate = $habit->getMonthlyCompletionRate($selectedYear, $selectedMonth);
            $streak = $habit->getCurrentStreak();
            $completedDays = $habit->logs()
                ->whereYear('log_date', $selectedYear)
                ->whereMonth('log_date', $selectedMonth)
                ->where('status', 'completed')
                ->count();

            $habitsStats[] = [
                'habit' => $habit,
                'rate' => $rate,
                'streak' => $streak,
                'completed_days' => $completedDays,
            ];
        }

        // Sort by rate to identify best and needing improvement
        usort($habitsStats, fn($a, $b) => $b['rate'] <=> $a['rate']);
        $bestHabits = array_slice($habitsStats, 0, 3);
        $needsImprovement = array_slice(array_reverse($habitsStats), 0, 3);

        // 2. Month-over-Month Trend (Last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mDate = $now->copy()->subMonths($i);
            $mYear = $mDate->year;
            $mMonth = $mDate->month;
            $daysInM = $mDate->daysInMonth;

            $applicableDays = ($mYear == $now->year && $mMonth == $now->month)
                ? $now->day
                : $daysInM;

            $totalPossible = $habits->count() * max(1, $applicableDays);

            $completedCount = $user->habitLogs()
                ->whereYear('log_date', $mYear)
                ->whereMonth('log_date', $mMonth)
                ->where('status', 'completed')
                ->where('log_date', '<=', $now->toDateString())
                ->count();

            $rate = $totalPossible > 0 ? round(($completedCount / $totalPossible) * 100, 1) : 0;

            $monthlyTrend[] = [
                'label' => $mDate->locale('ar')->isoFormat('MMM YYYY'),
                'label_en' => $mDate->format('M Y'),
                'rate' => $rate,
                'completed' => $completedCount,
            ];
        }

        // 3. Task Completion Stats
        $tasksTotal = $user->tasks()->count();
        $tasksCompleted = $user->tasks()->where('status', 'completed')->count();
        $tasksPending = $user->tasks()->where('status', 'pending')->count();
        $tasksRate = $tasksTotal > 0 ? round(($tasksCompleted / $tasksTotal) * 100, 1) : 0;

        // Tasks by Priority
        $tasksByPriority = [
            'high' => $user->tasks()->where('priority', 'high')->count(),
            'medium' => $user->tasks()->where('priority', 'medium')->count(),
            'low' => $user->tasks()->where('priority', 'low')->count(),
        ];

        // Tasks by Category
        $categoriesStats = [];
        $categories = $user->categories()->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
            $q->where('status', 'completed');
        }])->get();

        foreach ($categories as $cat) {
            $categoriesStats[] = [
                'name' => $cat->name,
                'color' => $cat->color,
                'total' => $cat->tasks_count,
                'completed' => $cat->completed_tasks_count,
                'rate' => $cat->tasks_count > 0 ? round(($cat->completed_tasks_count / $cat->tasks_count) * 100) : 0,
            ];
        }

        // Excuse Analysis
        $totalExcuses = $user->excuseLogs()->count();
        $overcameExcuses = $user->excuseLogs()->where('overcame', true)->count();
        $overcameRate = $totalExcuses > 0 ? round(($overcameExcuses / $totalExcuses) * 100) : 0;

        return view('analytics.index', compact(
            'habitsStats',
            'bestHabits',
            'needsImprovement',
            'monthlyTrend',
            'tasksTotal',
            'tasksCompleted',
            'tasksPending',
            'tasksRate',
            'tasksByPriority',
            'categoriesStats',
            'totalExcuses',
            'overcameExcuses',
            'overcameRate',
            'selectedYear',
            'selectedMonth'
        ));
    }
}
