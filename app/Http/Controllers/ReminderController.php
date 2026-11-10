<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\InAppNotification;
use App\Models\Task;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $activeReminders = $user->reminders()
            ->with(['task', 'calendarEvent'])
            ->where('status', 'pending')
            ->orderBy('remind_at', 'asc')
            ->get();

        $notifications = $user->inAppNotifications()
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'reminders' => $activeReminders,
                'notifications' => $notifications,
                'unread_count' => $user->inAppNotifications()->where('is_read', false)->count(),
            ]);
        }

        return view('reminders.index', compact('activeReminders', 'notifications'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'remind_type' => ['required', 'string', 'in:custom,before_10m,before_30m,before_1h,before_1d,overdue,daily_review,weekly_review'],
            'remind_at' => ['nullable', 'date'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'calendar_event_id' => ['nullable', 'exists:calendar_events,id'],
            'is_recurring' => ['nullable', 'boolean'],
        ]);

        $task = null;
        if (!empty($validated['task_id'])) {
            $task = $user->tasks()->findOrFail($validated['task_id']);
        }

        $event = null;
        if (!empty($validated['calendar_event_id'])) {
            $event = $user->calendarEvents()->findOrFail($validated['calendar_event_id']);
        }

        // Calculate remind_at if preset selected
        $remindAt = null;
        if (!empty($validated['remind_at'])) {
            $remindAt = Carbon::parse($validated['remind_at']);
        } elseif ($validated['remind_type'] === 'before_10m') {
            $baseTime = $event ? $event->start_time : ($task && $task->due_date ? Carbon::parse($task->due_date->format('Y-m-d') . ' ' . ($task->due_time ?? '09:00:00')) : now()->addMinutes(10));
            $remindAt = $baseTime->copy()->subMinutes(10);
        } elseif ($validated['remind_type'] === 'before_30m') {
            $baseTime = $event ? $event->start_time : ($task && $task->due_date ? Carbon::parse($task->due_date->format('Y-m-d') . ' ' . ($task->due_time ?? '09:00:00')) : now()->addMinutes(30));
            $remindAt = $baseTime->copy()->subMinutes(30);
        } elseif ($validated['remind_type'] === 'before_1h') {
            $baseTime = $event ? $event->start_time : ($task && $task->due_date ? Carbon::parse($task->due_date->format('Y-m-d') . ' ' . ($task->due_time ?? '09:00:00')) : now()->addHour());
            $remindAt = $baseTime->copy()->subHour();
        } elseif ($validated['remind_type'] === 'before_1d') {
            $baseTime = $event ? $event->start_time : ($task && $task->due_date ? Carbon::parse($task->due_date->format('Y-m-d') . ' ' . ($task->due_time ?? '09:00:00')) : now()->addDay());
            $remindAt = $baseTime->copy()->subDay();
        } else {
            $remindAt = Carbon::now()->addHour();
        }

        // Ensure remind_at is not in the deep past
        if ($remindAt->isPast()) {
            $remindAt = Carbon::now()->addMinutes(1);
        }

        $reminder = $user->reminders()->create([
            'task_id' => $task?->id,
            'calendar_event_id' => $event?->id,
            'title' => $validated['title'],
            'remind_at' => $remindAt,
            'remind_type' => $validated['remind_type'],
            'is_recurring' => $validated['is_recurring'] ?? false,
            'status' => 'pending',
            'channel' => 'in_app',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم ضبط التذكير بنجاح! ✓',
            'reminder' => $reminder,
        ]);
    }

    public function cancel(Request $request, Reminder $reminder)
    {
        $user = Auth::user();
        if ($reminder->user_id !== $user->id) {
            abort(403);
        }

        $reminder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التذكير بنجاح.',
        ]);
    }

    /**
     * Poll for due notifications (used by header bell & browser notification)
     */
    public function getDue(Request $request)
    {
        $user = Auth::user();

        // 1. Trigger pending reminders that just passed remind_at
        $now = Carbon::now();
        $due = $user->reminders()
            ->with(['task', 'calendarEvent'])
            ->where('status', 'pending')
            ->where('remind_at', '<=', $now)
            ->get();

        foreach ($due as $r) {
            $title = $r->title;
            $body = 'حان موعد التذكير الآن.';
            $link = route('dashboard');

            if ($r->task) {
                if ($r->task->isCompleted()) {
                    $r->update(['status' => 'cancelled']);
                    continue;
                }
                $body = "تذكير بمهمة: «{$r->task->title}»";
                $link = route('tasks.index');
            } elseif ($r->calendarEvent) {
                $body = "تذكير بحدث: «{$r->calendarEvent->title}»";
                $link = route('calendar.index');
            }

            $user->inAppNotifications()->create([
                'reminder_id' => $r->id,
                'title' => $title,
                'body' => $body,
                'link' => $link,
                'type' => 'reminder',
                'is_read' => false,
            ]);

            $r->update(['status' => 'sent', 'sent_at' => $now]);
        }

        // 2. Fetch unread notifications
        $unread = $user->inAppNotifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        $unreadCount = $user->inAppNotifications()->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $unread,
        ]);
    }

    public function markRead(Request $request, InAppNotification $notification)
    {
        $user = Auth::user();
        if ($notification->user_id !== $user->id) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        $user->inAppNotifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين جميع الإشعارات كمقروءة ✓',
        ]);
    }
}
