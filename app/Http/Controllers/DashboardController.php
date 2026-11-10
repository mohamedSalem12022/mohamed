<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Task;
use App\Models\DailyReflection;
use App\Models\ExcuseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $todayDate = $now->toDateString();

        // Selected month & year (defaults to current)
        $year = (int)$request->input('year', $now->year);
        $month = (int)$request->input('month', $now->month);

        $dateCarbon = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $dateCarbon->daysInMonth;

        // User Habits
        $habits = $user->habits()->where('is_archived', false)->get();
        $totalHabitsCount = $habits->count();

        // Today's Logs
        $todayLogs = $user->habitLogs()
            ->whereDate('log_date', $todayDate)
            ->get()
            ->keyBy('habit_id');

        $completedTodayCount = $todayLogs->where('status', 'completed')->count();
        $todayRate = $totalHabitsCount > 0 ? round(($completedTodayCount / $totalHabitsCount) * 100, 1) : 0;

        // Month Completion Rate (only up to current day if current month)
        $applicableDays = ($year == $now->year && $month == $now->month)
            ? $now->day
            : ($year < $now->year || ($year == $now->year && $month < $now->month) ? $daysInMonth : 0);

        $monthCompletedLogsCount = $user->habitLogs()
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $month)
            ->where('status', 'completed')
            ->where('log_date', '<=', $now->toDateString())
            ->count();

        $totalPossibleMonth = ($totalHabitsCount * max(1, $applicableDays));
        $monthRate = $totalPossibleMonth > 0 ? round(($monthCompletedLogsCount / $totalPossibleMonth) * 100, 1) : 0;

        // Tasks Stats
        $pendingTasksCount = $user->tasks()->where('status', 'pending')->count();
        $completedTasksCount = $user->tasks()->where('status', 'completed')->count();
        $todayTasks = $user->tasks()
            ->where(function ($q) use ($todayDate) {
                $q->whereNull('due_date')->orWhereDate('due_date', $todayDate);
            })
            ->orderBy('status', 'asc')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->take(6)
            ->get();

        // Streak Calculation (max streak among habits)
        $maxStreak = 0;
        foreach ($habits as $habit) {
            $streak = $habit->getCurrentStreak();
            if ($streak > $maxStreak) {
                $maxStreak = $streak;
            }
        }

        // Weekly Overview (last 7 days completion rate)
        $weeklyDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayDate = $now->copy()->subDays($i);
            $dayStr = $dayDate->toDateString();
            $completedOnDay = $user->habitLogs()
                ->whereDate('log_date', $dayStr)
                ->where('status', 'completed')
                ->count();
            
            $dayRate = $totalHabitsCount > 0 ? round(($completedOnDay / $totalHabitsCount) * 100) : 0;
            $weeklyDays[] = [
                'day_name' => $dayDate->locale('ar')->isoFormat('ddd'),
                'day_num' => $dayDate->day,
                'date' => $dayStr,
                'rate' => $dayRate,
                'completed' => $completedOnDay,
                'total' => $totalHabitsCount,
                'is_today' => $dayStr === $todayDate,
            ];
        }

        // Current Month Logs Matrix for the Circular Wheel
        $monthLogs = $user->habitLogs()
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $month)
            ->get();

        $logsMatrix = [];
        foreach ($monthLogs as $log) {
            $dayNum = (int)Carbon::parse($log->log_date)->day;
            $logsMatrix[$log->habit_id][$dayNum] = [
                'status' => $log->status,
                'note' => $log->note,
                'excuse_reason' => $log->excuse_reason,
            ];
        }

        // Today's reflection
        $todayReflection = $user->dailyReflections()->whereDate('date', $todayDate)->first();

        // Today's Smart Daily Plan
        $todayPlan = $user->dailyPlans()->whereDate('plan_date', $todayDate)->with('items.task')->first();

        // Recent Excuses Overcome
        $recentExcuses = $user->excuseLogs()->take(4)->get();

        return view('dashboard', compact(
            'user',
            'habits',
            'todayLogs',
            'completedTodayCount',
            'totalHabitsCount',
            'todayRate',
            'monthRate',
            'maxStreak',
            'pendingTasksCount',
            'completedTasksCount',
            'todayTasks',
            'weeklyDays',
            'year',
            'month',
            'daysInMonth',
            'logsMatrix',
            'todayDate',
            'todayReflection',
            'todayPlan',
            'recentExcuses'
        ));
    }
}
