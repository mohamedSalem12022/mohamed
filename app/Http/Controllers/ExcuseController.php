<?php

namespace App\Http\Controllers;

use App\Models\ExcuseLog;
use App\Models\Habit;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExcuseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->excuseLogs()->with(['habit', 'task']);

        // Filter by excuse type
        if ($request->filled('type')) {
            $query->where('excuse_type', $request->input('type'));
        }

        // Filter by overcome status
        if ($request->filled('status')) {
            if ($request->input('status') === 'overcame') {
                $query->where('overcame', true);
            } elseif ($request->input('status') === 'not_overcame') {
                $query->where('overcame', false);
            }
        }

        $excuses = $query->paginate(15)->withQueryString();

        $habits = $user->habits()->where('is_archived', false)->get();
        $tasks = $user->tasks()->where('status', 'pending')->get();

        // Statistical breakdown
        $totalExcuses = $user->excuseLogs()->count();
        $overcameCount = $user->excuseLogs()->where('overcame', true)->count();
        $overcameRate = $totalExcuses > 0 ? round(($overcameCount / $totalExcuses) * 100) : 0;

        // Breakdown by type
        $breakdown = $user->excuseLogs()
            ->selectRaw('excuse_type, count(*) as count')
            ->groupBy('excuse_type')
            ->pluck('count', 'excuse_type')
            ->toArray();

        return view('excuses.index', compact(
            'excuses',
            'habits',
            'tasks',
            'totalExcuses',
            'overcameCount',
            'overcameRate',
            'breakdown'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'habit_id' => ['nullable', 'exists:habits,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'excuse_type' => ['required', 'string'],
            'excuse_details' => ['required', 'string'],
            'how_to_overcome' => ['nullable', 'string'],
            'overcame' => ['nullable', 'boolean'],
        ]);

        $excuse = $user->excuseLogs()->create([
            'date' => $validated['date'],
            'habit_id' => $validated['habit_id'] ?? null,
            'task_id' => $validated['task_id'] ?? null,
            'excuse_type' => $validated['excuse_type'],
            'excuse_details' => $validated['excuse_details'],
            'how_to_overcome' => $validated['how_to_overcome'] ?? null,
            'overcame' => $request->boolean('overcame'),
        ]);

        return redirect()->back()->with('success', 'تم تسجيل العذر وخطة التغلب عليه بنجاح!');
    }

    public function toggleOvercame(Request $request, ExcuseLog $excuse)
    {
        $user = Auth::user();
        if ($excuse->user_id !== $user->id) {
            abort(403);
        }

        $excuse->update([
            'overcame' => !$excuse->overcame,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'overcame' => $excuse->overcame,
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة التغلب على العذر!');
    }

    public function destroy(ExcuseLog $excuse)
    {
        $user = Auth::user();
        if ($excuse->user_id !== $user->id) {
            abort(403);
        }

        $excuse->delete();

        return redirect()->back()->with('success', 'تم حذف السجل بنجاح.');
    }
}
