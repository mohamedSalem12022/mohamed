<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\ExcuseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HabitController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $year = (int)$request->input('year', $now->year);
        $month = (int)$request->input('month', $now->month);

        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }

        $dateCarbon = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $dateCarbon->daysInMonth;
        $monthNameAr = $dateCarbon->locale('ar')->isoFormat('MMMM');
        $monthNameEn = $dateCarbon->format('F');

        $habits = $user->habits()
            ->where('is_archived', false)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Retrieve all logs for this month
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

        // Today's Date
        $todayDate = $now->toDateString();
        $todayDay = ($year == $now->year && $month == $now->month) ? $now->day : null;

        // Daily reflection for right column (matches reference image NOTE & REFLECTIONS)
        $reflection = $user->dailyReflections()->whereDate('date', $todayDate)->first();

        // Excuses overcome for right column (matches reference image Excuses I overcome)
        $excuses = $user->excuseLogs()->take(6)->get();

        return view('habits.index', compact(
            'habits',
            'year',
            'month',
            'daysInMonth',
            'monthNameAr',
            'monthNameEn',
            'logsMatrix',
            'todayDate',
            'todayDay',
            'reflection',
            'excuses'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
            'target_days_per_week' => ['required', 'integer', 'min:1', 'max:7'],
        ]);

        $maxOrder = $user->habits()->max('order') ?? 0;

        $habit = $user->habits()->create([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? null,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'],
            'icon' => $validated['icon'] ?? 'fa-check',
            'target_days_per_week' => $validated['target_days_per_week'],
            'order' => $maxOrder + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'habit' => $habit]);
        }

        return redirect()->back()->with('success', 'تمت إضافة العادة بنجاح!');
    }

    public function update(Request $request, Habit $habit)
    {
        $user = Auth::user();
        if ($habit->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
            'target_days_per_week' => ['required', 'integer', 'min:1', 'max:7'],
        ]);

        $habit->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'habit' => $habit]);
        }

        return redirect()->back()->with('success', 'تم تحديث العادة بنجاح!');
    }

    public function destroy(Habit $habit)
    {
        $user = Auth::user();
        if ($habit->user_id !== $user->id) {
            abort(403);
        }

        $deletedOrder = $habit->order;
        $habit->delete();

        if ($deletedOrder) {
            $user->habits()->where('order', '>', $deletedOrder)->decrement('order');
        }

        return redirect()->back()->with('success', 'تم حذف العادة بنجاح.');
    }

    /**
     * Reorder habits by updating the 'order' column.
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'habit_ids' => ['required', 'array'],
            'habit_ids.*' => ['required', 'integer'],
        ]);

        $habitIds = $validated['habit_ids'];
        $userHabitIds = $user->habits()->pluck('id')->toArray();

        foreach ($habitIds as $index => $id) {
            if (in_array($id, $userHabitIds)) {
                Habit::where('id', $id)
                    ->where('user_id', $user->id)
                    ->update(['order' => $index + 1]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث ترتيب العادات بنجاح! ✓',
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث ترتيب العادات بنجاح! ✓');
    }

    /**
     * AJAX endpoint to log status of a habit for a specific day.
     */
    public function logStatus(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'habit_id' => ['required', 'exists:habits,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:completed,missed,skipped,clear'],
            'note' => ['nullable', 'string'],
            'excuse_reason' => ['nullable', 'string'],
        ]);

        $habit = Habit::where('id', $validated['habit_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $date = Carbon::parse($validated['date'])->toDateString();

        if ($validated['status'] === 'clear') {
            HabitLog::where('habit_id', $habit->id)
                ->where('log_date', $date)
                ->delete();

            return response()->json([
                'success' => true,
                'status' => 'clear',
                'habit_id' => $habit->id,
                'date' => $date,
                'streak' => $habit->getCurrentStreak(),
                'monthly_rate' => $habit->getMonthlyCompletionRate(Carbon::parse($date)->year, Carbon::parse($date)->month),
            ]);
        }

        $log = HabitLog::updateOrCreate(
            [
                'habit_id' => $habit->id,
                'log_date' => $date,
            ],
            [
                'user_id' => $user->id,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
                'excuse_reason' => $validated['excuse_reason'] ?? null,
                'completed_at' => $validated['status'] === 'completed' ? Carbon::now() : null,
            ]
        );

        // If marked as missed with excuse, also optionally link or record in excuse_logs
        if ($validated['status'] === 'missed' && !empty($validated['excuse_reason'])) {
            ExcuseLog::create([
                'user_id' => $user->id,
                'habit_id' => $habit->id,
                'date' => $date,
                'excuse_type' => 'other',
                'excuse_details' => $validated['excuse_reason'],
                'how_to_overcome' => null,
                'overcame' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $log->status,
            'habit_id' => $habit->id,
            'date' => $date,
            'note' => $log->note,
            'excuse_reason' => $log->excuse_reason,
            'streak' => $habit->getCurrentStreak(),
            'monthly_rate' => $habit->getMonthlyCompletionRate(Carbon::parse($date)->year, Carbon::parse($date)->month),
        ]);
    }

    /**
     * AJAX endpoint to fetch wheel data for any month/year.
     */
    public function getMonthData(Request $request)
    {
        $user = Auth::user();
        $year = (int)$request->input('year', Carbon::now()->year);
        $month = (int)$request->input('month', Carbon::now()->month);

        $dateCarbon = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $dateCarbon->daysInMonth;

        $habits = $user->habits()
            ->where('is_archived', false)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $monthLogs = $user->habitLogs()
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $month)
            ->get();

        $matrix = [];
        foreach ($monthLogs as $log) {
            $dayNum = (int)Carbon::parse($log->log_date)->day;
            $matrix[$log->habit_id][$dayNum] = [
                'status' => $log->status,
                'note' => $log->note,
                'excuse_reason' => $log->excuse_reason,
            ];
        }

        return response()->json([
            'year' => $year,
            'month' => $month,
            'month_name_ar' => $dateCarbon->locale('ar')->isoFormat('MMMM'),
            'month_name_en' => $dateCarbon->format('F'),
            'days_in_month' => $daysInMonth,
            'habits' => $habits,
            'logs' => $matrix,
        ]);
    }
}
