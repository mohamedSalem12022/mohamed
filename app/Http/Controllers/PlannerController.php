<?php

namespace App\Http\Controllers;

use App\Models\DailyPlan;
use App\Models\DailyPlanItem;
use App\Models\Task;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlannerController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        $dateParam = $request->query('date', Carbon::today()->toDateString());
        $planDate = Carbon::parse($dateParam)->toDateString();

        $plan = $user->dailyPlans()
            ->with(['items.task'])
            ->whereDate('plan_date', $planDate)
            ->first();

        $date = $planDate;

        $pendingTasks = $user->tasks()
            ->with('subtasks')
            ->where('status', 'pending')
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->get();

        // Calculate conflicts
        $conflicts = [];
        if ($plan && $plan->items) {
            $conflicts = $this->detectConflicts($plan->items->toArray());
        }

        // Unscheduled tasks
        $scheduledTaskIds = $plan ? $plan->items->pluck('task_id')->filter()->toArray() : [];
        $unscheduledTasks = [];
        foreach ($pendingTasks as $pt) {
            if (!in_array($pt->id, $scheduledTaskIds)) {
                $unscheduledTasks[] = [
                    'id' => $pt->id,
                    'title' => $pt->title,
                    'priority' => $pt->priority,
                    'estimated_minutes' => 45,
                    'reason' => 'بانتظار جدولتها',
                ];
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'plan' => $plan,
                'items' => $plan?->items ?? [],
                'pending_tasks' => $pendingTasks,
                'conflicts' => $conflicts,
                'unscheduled_tasks' => $unscheduledTasks,
            ]);
        }

        return view('planner.index', compact('user', 'plan', 'date', 'planDate', 'pendingTasks', 'conflicts', 'unscheduledTasks'));
    }

    /**
     * Generate an AI-powered or Smart Heuristic Daily Plan Proposal
     */
    public function generate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'start_hour' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'end_hour' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'include_breaks' => ['nullable', 'boolean'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $startHour = $validated['start_hour'] ?? '08:30';
        $endHour = $validated['end_hour'] ?? '21:30';
        $includeBreaks = $validated['include_breaks'] ?? true;
        $bufferMinutes = $validated['buffer_minutes'] ?? 30;

        // Call AiService to build plan proposal
        $proposal = $this->aiService->generateDailyPlanProposal($user, [
            'date' => $date,
            'start_hour' => $startHour,
            'end_hour' => $endHour,
            'include_breaks' => $includeBreaks,
            'buffer_minutes' => $bufferMinutes,
        ]);

        // Run conflict detection on the generated proposal
        $conflicts = $this->detectConflicts($proposal['items']);

        // Persist proposal into daily_plans and items
        $plan = $user->dailyPlans()->updateOrCreate(
            ['plan_date' => $date],
            [
                'start_hour' => $startHour,
                'end_hour' => $endHour,
                'summary' => $proposal['summary'] ?? null,
                'ai_proposal' => $proposal,
                'is_approved' => false,
            ]
        );

        $plan->items()->where('is_locked', false)->delete();
        foreach ($proposal['items'] as $order => $it) {
            $taskId = (!empty($it['task_id']) && Task::where('id', $it['task_id'])->where('user_id', $user->id)->exists())
                ? $it['task_id']
                : null;

            $plan->items()->create([
                'task_id' => $taskId,
                'title' => $it['title'],
                'start_time' => $it['start_time'] ?? null,
                'end_time' => $it['end_time'] ?? null,
                'duration_minutes' => $it['duration_minutes'] ?? 30,
                'type' => $it['type'] ?? 'task',
                'priority' => $it['priority'] ?? 'medium',
                'is_locked' => false,
                'is_completed' => false,
                'order' => $order,
            ]);
        }

        return response()->json([
            'success' => true,
            'plan_id' => $plan->id,
            'date' => $date,
            'start_hour' => $startHour,
            'end_hour' => $endHour,
            'summary' => $proposal['summary'],
            'items' => $proposal['items'],
            'unscheduled_tasks' => $proposal['unscheduled_tasks'] ?? [],
            'conflicts' => $conflicts,
            'provider' => $proposal['provider'] ?? 'MO Smart Planner',
        ]);
    }

    /**
     * Accept & Save Daily Plan
     */
    public function save(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'start_hour' => ['nullable', 'string'],
            'end_hour' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $targetDate = Carbon::parse($validated['date'])->toDateString();
        $plan = $user->dailyPlans()->whereDate('plan_date', $targetDate)->first();

        if ($plan) {
            $plan->update([
                'summary' => $validated['summary'] ?? $plan->summary,
                'start_hour' => $validated['start_hour'] ?? $plan->start_hour,
                'end_hour' => $validated['end_hour'] ?? $plan->end_hour,
                'is_accepted' => $request->has('is_approved') ? $request->boolean('is_approved') : true,
            ]);
        } else {
            $plan = $user->dailyPlans()->create([
                'plan_date' => $targetDate,
                'summary' => $validated['summary'] ?? null,
                'start_hour' => $validated['start_hour'] ?? '08:30',
                'end_hour' => $validated['end_hour'] ?? '21:30',
                'is_accepted' => $request->has('is_approved') ? $request->boolean('is_approved') : true,
            ]);
        }

        if (!empty($validated['items'])) {
            $plan->items()->where('is_locked', false)->delete();

            foreach ($validated['items'] as $order => $it) {
                $plan->items()->create([
                    'title' => $it['title'],
                    'task_id' => !empty($it['task_id']) ? $it['task_id'] : null,
                    'start_time' => $it['start_time'] ?? null,
                    'end_time' => $it['end_time'] ?? null,
                    'duration_minutes' => $it['duration_minutes'] ?? 30,
                    'type' => $it['type'] ?? 'task',
                    'is_break' => !empty($it['is_break']),
                    'is_buffer' => !empty($it['is_buffer']),
                    'is_locked' => !empty($it['is_locked']),
                    'order' => $order,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم اعتماد وحفظ خطة اليوم بنجاح! ✓',
            'plan_id' => $plan->id,
        ]);
    }

    /**
     * Add manual item to plan
     */
    public function addItem(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'plan_id' => ['nullable', 'exists:daily_plans,id'],
            'date' => ['nullable', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'duration_minutes' => ['nullable', 'integer'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'type' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_locked' => ['nullable', 'boolean'],
            'is_break' => ['nullable', 'boolean'],
            'is_buffer' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['plan_id'])) {
            $plan = $user->dailyPlans()->findOrFail($validated['plan_id']);
        } else {
            $targetDate = Carbon::parse($validated['date'] ?? Carbon::today())->toDateString();
            $plan = $user->dailyPlans()->whereDate('plan_date', $targetDate)->first();
            if (!$plan) {
                $plan = $user->dailyPlans()->create([
                    'plan_date' => $targetDate,
                    'start_hour' => '08:30',
                    'end_hour' => '21:30',
                    'is_approved' => false,
                ]);
            }
        }

        $duration = $validated['duration_minutes'] ?? max(15, Carbon::parse($validated['end_time'])->diffInMinutes(Carbon::parse($validated['start_time'])));

        $item = $plan->items()->create([
            'title' => $validated['title'],
            'task_id' => $validated['task_id'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $duration,
            'type' => $validated['type'] ?? 'task',
            'priority' => $validated['priority'] ?? 'medium',
            'notes' => $validated['notes'] ?? null,
            'is_break' => !empty($validated['is_break']) || ($validated['type'] ?? '') === 'break',
            'is_buffer' => !empty($validated['is_buffer']) || ($validated['type'] ?? '') === 'buffer',
            'is_locked' => !empty($validated['is_locked']),
            'order' => $plan->items()->count() + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة العنصر للجدول بنجاح! ✓',
            'item' => $item,
        ]);
    }

    /**
     * Update a plan item
     */
    public function updateItem(Request $request, DailyPlanItem $item)
    {
        $user = Auth::user();
        if ($item->dailyPlan->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'start_time' => ['nullable', 'string'],
            'end_time' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer'],
            'type' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_locked' => ['nullable', 'boolean'],
            'is_completed' => ['nullable', 'boolean'],
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل العنصر في الجدول بنجاح! ✓',
            'item' => $item,
        ]);
    }

    /**
     * Delete an item from plan
     */
    public function deleteItem(Request $request, DailyPlanItem $item)
    {
        $user = Auth::user();
        if ($item->dailyPlan->user_id !== $user->id) {
            abort(403);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنصر من الجدول.',
        ]);
    }

    /**
     * Sync plan times to actual tasks' due_time (Only with user explicit request!)
     */
    public function syncTasks(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'plan_id' => ['nullable', 'exists:daily_plans,id'],
            'date' => ['nullable', 'date'],
        ]);

        if (!empty($validated['plan_id'])) {
            $plan = $user->dailyPlans()->with('items.task')->findOrFail($validated['plan_id']);
        } else {
            $targetDate = $validated['date'] ?? Carbon::today()->toDateString();
            $plan = $user->dailyPlans()->with('items.task')->whereDate('plan_date', $targetDate)->firstOrFail();
        }

        $updatedCount = 0;
        foreach ($plan->items as $item) {
            if ($item->task && !in_array($item->type, ['break', 'buffer'])) {
                $item->task->update([
                    'due_date' => $plan->plan_date,
                    'due_time' => $item->start_time ? substr($item->start_time, 0, 5) . ':00' : null,
                ]);
                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تمت مزامنة مواعيد ({$updatedCount}) مهمة مع جدول اليوم بنجاح! ✓",
        ]);
    }

    /**
     * Helper to detect overlapping time conflicts between scheduled items
     */
    protected function detectConflicts(array $items): array
    {
        $conflicts = [];
        $count = count($items);

        for ($i = 0; $i < $count; $i++) {
            $aStart = $items[$i]['start_time'] ?? '';
            $aEnd = $items[$i]['end_time'] ?? '';
            if (!$aStart || !$aEnd) continue;

            for ($j = $i + 1; $j < $count; $j++) {
                $bStart = $items[$j]['start_time'] ?? '';
                $bEnd = $items[$j]['end_time'] ?? '';
                if (!$bStart || !$bEnd) continue;

                // Overlap check: (StartA < EndB) and (EndA > StartB)
                if ($aStart < $bEnd && $aEnd > $bStart) {
                    $conflicts[] = [
                        'item_a' => $items[$i]['title'] ?? 'عنصر 1',
                        'item_b' => $items[$j]['title'] ?? 'عنصر 2',
                        'time_a' => "{$aStart} - {$aEnd}",
                        'time_b' => "{$bStart} - {$bEnd}",
                        'message' => "تعارض زمني بين «{$items[$i]['title']}» و«{$items[$j]['title']}».",
                    ];
                }
            }
        }

        return $conflicts;
    }
}
