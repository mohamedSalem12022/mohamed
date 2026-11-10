<?php

namespace App\Http\Controllers;

use App\Models\DailyReflection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReflectionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        $reflection = $user->dailyReflections()
            ->whereDate('date', $selectedDate)
            ->first();

        // Recent reflections history
        $history = $user->dailyReflections()
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('reflections.index', compact('reflection', 'selectedDate', 'history'));
    }

    public function storeOrUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'best_win' => ['nullable', 'string', 'max:500'],
            'lessons_learned' => ['nullable', 'string', 'max:500'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'tomorrow_improvements' => ['nullable', 'string', 'max:500'],
        ]);

        $reflection = DailyReflection::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $validated['date'],
            ],
            [
                'notes' => $validated['notes'],
                'best_win' => $validated['best_win'],
                'lessons_learned' => $validated['lessons_learned'],
                'rating' => $validated['rating'] ?? 5,
                'tomorrow_improvements' => $validated['tomorrow_improvements'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'reflection' => $reflection]);
        }

        return redirect()->route('reflections.index', ['date' => $validated['date']])
            ->with('success', 'تم حفظ التأمل والملاحظات بنجاح!');
    }
}
