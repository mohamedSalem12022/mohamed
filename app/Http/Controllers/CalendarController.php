<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $view = $request->query('view', 'month'); // month, week, day
        $dateParam = $request->query('date', Carbon::today()->toDateString());
        $currentDate = Carbon::parse($dateParam);

        $pendingTasks = $user->tasks()
            ->where('status', 'pending')
            ->orderBy('priority', 'asc')
            ->get();

        return view('calendar.index', compact('user', 'view', 'currentDate', 'pendingTasks'));
    }

    /**
     * Return JSON events for the calendar (merges CalendarEvents and Tasks with due dates)
     */
    public function events(Request $request)
    {
        $user = Auth::user();

        $start = $request->query('start') ? Carbon::parse($request->query('start'))->startOfDay() : Carbon::now()->startOfMonth()->subDays(7);
        $end = $request->query('end') ? Carbon::parse($request->query('end'))->endOfDay() : Carbon::now()->endOfMonth()->addDays(7);
        $typeFilter = $request->query('type', 'all');

        $eventsList = [];

        // 1. Calendar Events
        $query = $user->calendarEvents()
            ->whereBetween('start_time', [$start, $end]);

        if ($typeFilter !== 'all') {
            $query->where('event_type', $typeFilter);
        }

        $calEvents = $query->get();

        foreach ($calEvents as $ce) {
            $typeColors = [
                'personal' => '#0d9488', // Turquoise
                'meeting' => '#3b82f6',  // Blue
                'focus' => '#8b5cf6',    // Purple
                'deadline' => '#ef4444', // Red
                'work' => '#f59e0b',     // Amber
            ];

            $color = $ce->color ?: ($typeColors[$ce->event_type] ?? '#0d9488');

            $eventsList[] = [
                'id' => $ce->id,
                'event_id' => 'ce_' . $ce->id,
                'real_id' => $ce->id,
                'title' => $ce->title,
                'description' => $ce->description,
                'start' => $ce->start_time->toIso8601String(),
                'end' => $ce->end_time->toIso8601String(),
                'color' => $color,
                'event_type' => $ce->event_type,
                'is_task' => false,
                'is_recurring' => $ce->is_recurring,
                'task_id' => $ce->task_id,
            ];
        }

        // 2. Tasks with due dates (if filter allows)
        if ($typeFilter === 'all' || $typeFilter === 'tasks' || $typeFilter === 'deadline') {
            $tasks = $user->tasks()
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
                ->with('category')
                ->get();

            foreach ($tasks as $t) {
                $timeStr = $t->due_time ? $t->due_time : '09:00:00';
                $taskStart = Carbon::parse($t->due_date->format('Y-m-d') . ' ' . $timeStr);
                $taskEnd = $taskStart->copy()->addHour();

                $pColors = [
                    'high' => '#ef4444',
                    'medium' => '#f59e0b',
                    'low' => '#10b981',
                ];

                $color = $t->isCompleted() ? '#94a3b8' : ($pColors[$t->priority] ?? '#f59e0b');

                $eventsList[] = [
                    'id' => 'task_' . $t->id,
                    'real_id' => $t->id,
                    'title' => ($t->isCompleted() ? '✓ ' : '□ ') . $t->title,
                    'description' => $t->description ?: ('الأولوية: ' . ($t->priority === 'high' ? 'عالية 🔥' : 'عادية')),
                    'start' => $taskStart->toIso8601String(),
                    'end' => $taskEnd->toIso8601String(),
                    'color' => $color,
                    'event_type' => 'task',
                    'is_task' => true,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'task_id' => $t->id,
                ];
            }
        }

        return response()->json($eventsList);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'event_type' => ['required', 'string', 'in:personal,meeting,focus,deadline,work'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'color' => ['nullable', 'string', 'max:30'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence' => ['nullable', 'string', 'in:none,daily,weekly,monthly'],
            'reminder_preset' => ['nullable', 'string'],
            'reminder_minutes_before' => ['nullable'],
        ]);

        $event = $user->calendarEvents()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'],
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => Carbon::parse($validated['end_time']),
            'color' => $validated['color'] ?? null,
            'task_id' => $validated['task_id'] ?? null,
            'is_recurring' => $validated['is_recurring'] ?? false,
            'recurrence' => $validated['recurrence'] ?? 'none',
        ]);

        // Optional Reminder Creation
        $preset = $request->input('reminder_preset');
        $minBefore = $request->input('reminder_minutes_before');

        if (!empty($minBefore) || (!empty($preset) && $preset !== 'none')) {
            $minutes = 30;
            if (!empty($minBefore)) {
                $minutes = (int)$minBefore;
            } elseif ($preset === 'before_10m') {
                $minutes = 10;
            } elseif ($preset === 'before_30m') {
                $minutes = 30;
            } elseif ($preset === 'before_1h') {
                $minutes = 60;
            } elseif ($preset === 'before_1d') {
                $minutes = 1440;
            }

            $remindAt = $event->start_time->copy()->subMinutes($minutes);

            $user->reminders()->create([
                'calendar_event_id' => $event->id,
                'title' => "تذكير بـ: {$event->title}",
                'remind_at' => $remindAt,
                'remind_type' => (!empty($preset) && $preset !== 'none') ? $preset : 'custom',
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة الموعد إلى التقويم بنجاح! ✓',
            'event' => $event,
        ]);
    }

    public function update(Request $request, CalendarEvent $event)
    {
        $user = Auth::user();
        if ($event->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'event_type' => ['required', 'string', 'in:personal,meeting,focus,deadline,work'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'color' => ['nullable', 'string', 'max:30'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence' => ['nullable', 'string', 'in:none,daily,weekly,monthly'],
        ]);

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'],
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => Carbon::parse($validated['end_time']),
            'color' => $validated['color'] ?? $event->color,
            'task_id' => $validated['task_id'] ?? null,
            'is_recurring' => $validated['is_recurring'] ?? false,
            'recurrence' => $validated['recurrence'] ?? 'none',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الموعد بنجاح! ✓',
            'event' => $event,
        ]);
    }

    public function destroy(Request $request, CalendarEvent $event)
    {
        $user = Auth::user();
        if ($event->user_id !== $user->id) {
            abort(403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الموعد من التقويم بنجاح.',
        ]);
    }
}
