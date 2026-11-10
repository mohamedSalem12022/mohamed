<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->tasks()->with(['category', 'subtasks']);

        // Filter: Status
        $status = $request->input('status', 'all');
        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        }

        // Filter: Priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Filter: Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter: Date
        $dateFilter = $request->input('date_filter');
        $today = Carbon::today();
        if ($dateFilter === 'today') {
            $query->whereDate('due_date', $today);
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('due_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
        } elseif ($dateFilter === 'overdue') {
            $query->where('status', 'pending')->whereDate('due_date', '<', $today);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $tasks = $query->orderBy('status', 'asc')
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->paginate(15)
            ->withQueryString();

        $categories = $user->categories()->get();

        // Overall stats
        $totalCount = $user->tasks()->count();
        $completedCount = $user->tasks()->where('status', 'completed')->count();
        $pendingCount = $user->tasks()->where('status', 'pending')->count();
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        return view('tasks.index', compact(
            'tasks',
            'categories',
            'totalCount',
            'completedCount',
            'pendingCount',
            'completionRate',
            'status',
            'dateFilter'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'due_time' => ['nullable'],
            'priority' => ['required', 'in:low,medium,high'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'recurrence' => ['nullable', 'in:none,daily,weekly,monthly'],
        ]);

        $task = $user->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'priority' => $validated['priority'],
            'category_id' => $validated['category_id'] ?? null,
            'recurrence' => $validated['recurrence'] ?? 'none',
            'status' => 'pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task->load('category')]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المهمة بنجاح!');
    }

    public function update(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'due_time' => ['nullable'],
            'priority' => ['required', 'in:low,medium,high'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'recurrence' => ['nullable', 'in:none,daily,weekly,monthly'],
        ]);

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task->load('category')]);
        }

        return redirect()->back()->with('success', 'تم تعديل المهمة بنجاح.');
    }

    public function destroy(Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        $task->delete();

        return redirect()->back()->with('success', 'تم حذف المهمة بنجاح.');
    }

    /**
     * AJAX Toggle Task Completed / Pending
     */
    public function toggleStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        $totalSubtasks = $task->subtasks()->count();
        $incompleteSubtasks = $task->subtasks()->where('is_completed', false)->count();

        // If trying to complete the task, and there are incomplete subtasks:
        if ($task->status === 'pending' && $incompleteSubtasks > 0) {
            if (!$request->boolean('confirm_complete_subtasks')) {
                return response()->json([
                    'requires_confirmation' => true,
                    'message' => 'هذه المهمة تحتوي على مهام فرعية غير مكتملة. هل تريد إكمال جميع المهام الفرعية أيضاً؟',
                    'incomplete_count' => $incompleteSubtasks,
                ]);
            }

            // User confirmed completing all subtasks as well:
            $task->subtasks()->where('is_completed', false)->update([
                'is_completed' => true,
                'completed_at' => Carbon::now(),
            ]);
        }

        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        $completedAt = $newStatus === 'completed' ? Carbon::now() : null;

        $task->update([
            'status' => $newStatus,
            'completed_at' => $completedAt,
        ]);

        $totalCount = $user->tasks()->count();
        $completedCount = $user->tasks()->where('status', 'completed')->count();
        $rate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        $subtasksCount = $task->subtasks()->count();
        $completedSubtasksCount = $task->subtasks()->where('is_completed', true)->count();
        $subtasksRate = $subtasksCount > 0 ? round(($completedSubtasksCount / $subtasksCount) * 100) : 0;

        return response()->json([
            'success' => true,
            'status' => $task->status,
            'completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d H:i') : null,
            'total_completed' => $completedCount,
            'completion_rate' => $rate,
            'subtasks_count' => $subtasksCount,
            'completed_subtasks_count' => $completedSubtasksCount,
            'subtasks_rate' => $subtasksRate,
        ]);
    }

    /**
     * Store new Subtask
     */
    public function storeSubtask(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $subtask = $task->subtasks()->create([
            'title' => $validated['title'],
            'is_completed' => false,
            'order' => $task->subtasks()->count() + 1,
        ]);

        $task->syncStatusWithSubtasks();
        $task->refresh();

        if ($request->wantsJson()) {
            $totalSubtasks = $task->subtasks()->count();
            $completedSubtasks = $task->subtasks()->where('is_completed', true)->count();
            $rate = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;

            return response()->json([
                'success' => true,
                'subtask' => $subtask,
                'task_status' => $task->status,
                'total_subtasks' => $totalSubtasks,
                'completed_subtasks' => $completedSubtasks,
                'subtasks_rate' => $rate,
                'message' => 'تمت إضافة المهمة الفرعية بنجاح',
            ]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المهمة الفرعية بنجاح!');
    }

    /**
     * Update Subtask Title
     */
    public function updateSubtask(Request $request, Subtask $subtask)
    {
        $user = Auth::user();
        if ($subtask->task->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $subtask->update([
            'title' => $validated['title'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask,
                'message' => 'تم تعديل المهمة الفرعية بنجاح',
            ]);
        }

        return redirect()->back()->with('success', 'تم تعديل المهمة الفرعية بنجاح.');
    }

    /**
     * Delete Subtask
     */
    public function destroySubtask(Subtask $subtask)
    {
        $user = Auth::user();
        $task = $subtask->task;
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        $subtask->delete();
        $task->syncStatusWithSubtasks();
        $task->refresh();

        if (request()->wantsJson()) {
            $totalSubtasks = $task->subtasks()->count();
            $completedSubtasks = $task->subtasks()->where('is_completed', true)->count();
            $rate = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;

            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'task_status' => $task->status,
                'total_subtasks' => $totalSubtasks,
                'completed_subtasks' => $completedSubtasks,
                'subtasks_rate' => $rate,
                'message' => 'تم حذف المهمة الفرعية بنجاح',
            ]);
        }

        return redirect()->back()->with('success', 'تم حذف المهمة الفرعية بنجاح.');
    }

    /**
     * Toggle Subtask Completed / Pending
     */
    public function toggleSubtask(Request $request, Subtask $subtask)
    {
        $user = Auth::user();
        $task = $subtask->task;
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        $newCompleted = !$subtask->is_completed;
        $subtask->update([
            'is_completed' => $newCompleted,
            'completed_at' => $newCompleted ? Carbon::now() : null,
        ]);

        // Auto-sync main task status:
        // If all subtasks completed -> main task completed
        // If any subtask reopened -> main task pending
        $task->syncStatusWithSubtasks();
        $task->refresh();

        $totalSubtasks = $task->subtasks()->count();
        $completedSubtasks = $task->subtasks()->where('is_completed', true)->count();
        $rate = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;

        $overallTotal = $user->tasks()->count();
        $overallCompleted = $user->tasks()->where('status', 'completed')->count();
        $overallRate = $overallTotal > 0 ? round(($overallCompleted / $overallTotal) * 100) : 0;

        return response()->json([
            'success' => true,
            'subtask_id' => $subtask->id,
            'is_completed' => $subtask->is_completed,
            'task_id' => $task->id,
            'task_status' => $task->status,
            'task_completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d H:i') : null,
            'total_subtasks' => $totalSubtasks,
            'completed_subtasks' => $completedSubtasks,
            'subtasks_rate' => $rate,
            'overall_completed' => $overallCompleted,
            'overall_rate' => $overallRate,
        ]);
    }
}
