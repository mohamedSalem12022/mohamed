<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SubSkill;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SkillController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Display skills dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $statusFilter = $request->get('status', 'all');
        $search = trim($request->get('search', ''));

        $query = $user->skills()->with('subSkills');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('goal', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($statusFilter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($statusFilter === 'paused') {
            $query->where('status', 'paused');
        }

        $skills = $query->get();

        // Overall stats across all user skills
        $allSkills = $user->skills()->with('subSkills')->get();
        $totalSkills = $allSkills->count();
        $completedSkills = $allSkills->filter(fn($s) => $s->progressPercentage() === 100 || $s->status === 'completed')->count();
        $inProgressSkills = $allSkills->filter(fn($s) => $s->progressPercentage() < 100 && $s->status !== 'completed')->count();

        $allSubSkills = $allSkills->flatMap->subSkills;
        $totalSubSkills = $allSubSkills->count();
        $masteredSubSkills = $allSubSkills->where('status', 'mastered')->count();
        $averageProgress = $totalSkills > 0 ? (int) round($allSkills->avg(fn($s) => $s->progressPercentage())) : 0;

        return view('skills.index', compact(
            'skills',
            'statusFilter',
            'search',
            'totalSkills',
            'completedSkills',
            'inProgressSkills',
            'totalSubSkills',
            'masteredSubSkills',
            'averageProgress'
        ));
    }

    /**
     * Store a new main skill
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'goal' => ['nullable', 'string', 'max:1500'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:in_progress,completed,paused'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('skills', 'public');
        }

        $skill = $user->skills()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'goal' => $validated['goal'] ?? null,
            'icon' => $validated['icon'] ?? 'fa-solid fa-graduation-cap',
            'color' => $validated['color'] ?? '#0d9488',
            'image' => $imagePath,
            'start_date' => $validated['start_date'] ?? Carbon::now(),
            'target_date' => $validated['target_date'] ?? null,
            'status' => $validated['status'] ?? 'in_progress',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المهارة بنجاح ✓',
                'skill' => $skill,
                'redirect' => route('skills.show', $skill),
            ]);
        }

        return redirect()->route('skills.show', $skill)->with('success', 'تم إنشاء المهارة بنجاح! يمكنك الآن تقسيمها إلى مهارات فرعية.');
    }

    /**
     * Display a specific skill details workspace
     */
    public function show(Skill $skill)
    {
        $this->authorizeAccess($skill);

        $skill->load('subSkills');

        $totalSubSkills = $skill->totalSubSkillsCount();
        $masteredCount = $skill->masteredCount();
        $inProgressCount = $skill->inProgressCount();
        $notStartedCount = $skill->notStartedCount();
        $progress = $skill->progressPercentage();
        $nextSkill = $skill->nextRecommendedSubSkill();
        $timeline = $skill->timelineStatus();

        return view('skills.show', compact(
            'skill',
            'totalSubSkills',
            'masteredCount',
            'inProgressCount',
            'notStartedCount',
            'progress',
            'nextSkill',
            'timeline'
        ));
    }

    /**
     * Update main skill details
     */
    public function update(Request $request, Skill $skill)
    {
        $this->authorizeAccess($skill);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'goal' => ['nullable', 'string', 'max:1500'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:in_progress,completed,paused'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
        ]);

        if ($request->hasFile('image')) {
            if ($skill->image && Storage::disk('public')->exists($skill->image)) {
                Storage::disk('public')->delete($skill->image);
            }
            $validated['image'] = $request->file('image')->store('skills', 'public');
        }

        $skill->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المهارة بنجاح ✓',
                'skill' => $skill,
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات المهارة بنجاح ✓');
    }

    /**
     * Delete a main skill
     */
    public function destroy(Skill $skill)
    {
        $this->authorizeAccess($skill);

        if ($skill->image && Storage::disk('public')->exists($skill->image)) {
            Storage::disk('public')->delete($skill->image);
        }

        $skill->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المهارة بنجاح.',
            ]);
        }

        return redirect()->route('skills.index')->with('success', 'تم حذف المهارة وكافة متطلباتها بنجاح.');
    }

    /**
     * Add a sub-skill to the main skill
     */
    public function storeSubSkill(Request $request, Skill $skill)
    {
        $this->authorizeAccess($skill);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:not_started,in_progress,mastered'],
            'mastery_level' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'resources' => ['nullable', 'string', 'max:2000'],
            'practical_tasks' => ['nullable', 'string', 'max:2000'],
        ]);

        $maxOrder = $skill->subSkills()->max('order') ?? 0;

        $status = $validated['status'] ?? 'not_started';
        $level = $validated['mastery_level'] ?? ($status === 'mastered' ? 100 : ($status === 'in_progress' ? 50 : 0));
        $completedAt = $status === 'mastered' ? Carbon::now() : null;

        $subSkill = $skill->subSkills()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'mastery_level' => $level,
            'order' => $maxOrder + 1,
            'notes' => $validated['notes'] ?? null,
            'resources' => $validated['resources'] ?? null,
            'practical_tasks' => $validated['practical_tasks'] ?? null,
            'completed_at' => $completedAt,
        ]);

        $skill->load('subSkills');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة المهارة الفرعية بنجاح ✓',
                'sub_skill' => $subSkill,
                'overall_progress' => $skill->progressPercentage(),
                'mastered_count' => $skill->masteredCount(),
                'total_count' => $skill->totalSubSkillsCount(),
            ]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المهارة الفرعية بنجاح ✓');
    }

    /**
     * Update a sub-skill
     */
    public function updateSubSkill(Request $request, Skill $skill, SubSkill $subSkill)
    {
        $this->authorizeAccess($skill);
        abort_if($subSkill->skill_id !== $skill->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:not_started,in_progress,mastered'],
            'mastery_level' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'resources' => ['nullable', 'string', 'max:2000'],
            'practical_tasks' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'mastered') {
            $validated['completed_at'] = $subSkill->completed_at ?? Carbon::now();
            $validated['mastery_level'] = 100;
        } else {
            $validated['completed_at'] = null;
            if ($validated['status'] === 'not_started') {
                $validated['mastery_level'] = 0;
            }
        }

        $subSkill->update($validated);

        $skill->load('subSkills');
        if ($skill->progressPercentage() === 100 && $skill->status !== 'completed') {
            $skill->update(['status' => 'completed']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المهارة الفرعية بنجاح ✓',
                'sub_skill' => $subSkill,
                'overall_progress' => $skill->progressPercentage(),
                'mastered_count' => $skill->masteredCount(),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث المهارة الفرعية بنجاح ✓');
    }

    /**
     * Fast toggle status of a sub-skill
     */
    public function toggleSubSkillStatus(Request $request, Skill $skill, SubSkill $subSkill)
    {
        $this->authorizeAccess($skill);
        abort_if($subSkill->skill_id !== $skill->id, 404);

        $requestedStatus = $request->input('status');

        if ($requestedStatus) {
            $newStatus = $requestedStatus;
        } else {
            // Cycle: not_started -> in_progress -> mastered -> not_started
            $newStatus = match ($subSkill->status) {
                'not_started' => 'in_progress',
                'in_progress' => 'mastered',
                default => 'not_started',
            };
        }

        $newLevel = match ($newStatus) {
            'mastered' => 100,
            'in_progress' => ($subSkill->mastery_level > 0 && $subSkill->mastery_level < 100) ? $subSkill->mastery_level : 50,
            default => 0,
        };

        $completedAt = $newStatus === 'mastered' ? Carbon::now() : null;

        $subSkill->update([
            'status' => $newStatus,
            'mastery_level' => $newLevel,
            'completed_at' => $completedAt,
        ]);

        $skill->load('subSkills');
        if ($skill->progressPercentage() === 100 && $skill->status !== 'completed') {
            $skill->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'status_label' => $subSkill->statusLabel(),
            'status_badge_class' => $subSkill->statusBadgeClass(),
            'mastery_level' => $newLevel,
            'overall_progress' => $skill->progressPercentage(),
            'mastered_count' => $skill->masteredCount(),
            'in_progress_count' => $skill->inProgressCount(),
            'not_started_count' => $skill->notStartedCount(),
            'next_skill' => $skill->nextRecommendedSubSkill()?->title,
        ]);
    }

    /**
     * Fast update mastery level (0-100%) slider
     */
    public function updateMasteryLevel(Request $request, Skill $skill, SubSkill $subSkill)
    {
        $this->authorizeAccess($skill);
        abort_if($subSkill->skill_id !== $skill->id, 404);

        $validated = $request->validate([
            'mastery_level' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $level = (int) $validated['mastery_level'];

        if ($level === 100) {
            $status = 'mastered';
            $completedAt = Carbon::now();
        } elseif ($level > 0) {
            $status = 'in_progress';
            $completedAt = null;
        } else {
            $status = 'not_started';
            $completedAt = null;
        }

        $subSkill->update([
            'mastery_level' => $level,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        $skill->load('subSkills');
        if ($skill->progressPercentage() === 100 && $skill->status !== 'completed') {
            $skill->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'mastery_level' => $level,
            'status' => $status,
            'status_label' => $subSkill->statusLabel(),
            'status_badge_class' => $subSkill->statusBadgeClass(),
            'overall_progress' => $skill->progressPercentage(),
            'mastered_count' => $skill->masteredCount(),
            'in_progress_count' => $skill->inProgressCount(),
            'not_started_count' => $skill->notStartedCount(),
        ]);
    }

    /**
     * Reorder sub-skills list
     */
    public function reorderSubSkills(Request $request, Skill $skill)
    {
        $this->authorizeAccess($skill);

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:sub_skills,id'],
        ]);

        foreach ($validated['order'] as $index => $id) {
            $skill->subSkills()->where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة ترتيب المهارات بنجاح ✓',
        ]);
    }

    /**
     * Delete a sub-skill
     */
    public function destroySubSkill(Skill $skill, SubSkill $subSkill)
    {
        $this->authorizeAccess($skill);
        abort_if($subSkill->skill_id !== $skill->id, 404);

        $subSkill->delete();

        $skill->load('subSkills');

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المهارة الفرعية بنجاح.',
                'overall_progress' => $skill->progressPercentage(),
                'mastered_count' => $skill->masteredCount(),
                'total_count' => $skill->totalSubSkillsCount(),
            ]);
        }

        return redirect()->back()->with('success', 'تم حذف المهارة الفرعية بنجاح.');
    }

    /**
     * AI Sub-skills Breakdown generator
     */
    public function generateAiBreakdown(Request $request, Skill $skill)
    {
        $this->authorizeAccess($skill);

        $aiResult = $this->aiService->generateSkillBreakdown($skill);
        $autoSave = filter_var($request->input('auto_save', false), FILTER_VALIDATE_BOOLEAN);

        $savedCount = 0;
        if ($autoSave && !empty($aiResult['sub_skills'])) {
            $maxOrder = $skill->subSkills()->max('order') ?? 0;
            foreach ($aiResult['sub_skills'] as $idx => $item) {
                $skill->subSkills()->create([
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'status' => 'not_started',
                    'mastery_level' => 0,
                    'order' => $maxOrder + $idx + 1,
                    'resources' => $item['resources'] ?? null,
                    'practical_tasks' => $item['practical_tasks'] ?? null,
                ]);
                $savedCount++;
            }
            $skill->load('subSkills');
        }

        return response()->json([
            'success' => true,
            'sub_skills' => $aiResult['sub_skills'],
            'provider' => $aiResult['provider'],
            'auto_saved' => $autoSave,
            'saved_count' => $savedCount,
            'overall_progress' => $skill->progressPercentage(),
            'message' => $autoSave ? "تم توليد وإضافة {$savedCount} مهارات فرعية بنجاح ✓" : "تم توليد المهارات المقترحة بنجاح",
        ]);
    }

    /**
     * AI Roadmap & Evaluation guidance
     */
    public function generateAiRoadmap(Request $request, Skill $skill)
    {
        $this->authorizeAccess($skill);

        $aiResult = $this->aiService->generateSkillRoadmap($skill);

        return response()->json([
            'success' => true,
            'roadmap' => $aiResult['roadmap'],
            'provider' => $aiResult['provider'],
        ]);
    }

    /**
     * Authorize that the current user owns this skill
     */
    protected function authorizeAccess(Skill $skill): void
    {
        abort_if($skill->user_id !== Auth::id(), 403, 'غير مصرح لك بالوصول لهذه المهارة.');
    }
}
