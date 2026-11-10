<?php

namespace App\Http\Controllers;

use App\Models\Motivation;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MotivationController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Generate motivation with AI
     */
    public function generateAi(Request $request)
    {
        $validated = $request->validate([
            'topic' => ['nullable', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        $topic = !empty($validated['topic']) ? trim($validated['topic']) : null;

        $generated = $this->aiService->generateMotivation($user, $topic);

        return response()->json([
            'success' => true,
            'motivation' => $generated,
            'message' => 'تم توليد محفّز مخصص بنجاح عبر الذكاء الاصطناعي ✨',
        ]);
    }

    /**
     * Get all motivations for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $motivations = $user->motivations()
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $motivations->count(),
            'motivations' => $motivations,
        ]);
    }

    /**
     * Store a new motivation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'quote' => ['required', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $motivation = Auth::user()->motivations()->create([
            'title' => trim($validated['title']),
            'quote' => trim($validated['quote']),
            'category' => !empty($validated['category']) ? trim($validated['category']) : 'عام',
            'icon' => !empty($validated['icon']) ? trim($validated['icon']) : 'fa-lightbulb',
            'is_pinned' => (bool) ($validated['is_pinned'] ?? false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة المحفّز بنجاح ✓',
            'motivation' => $motivation,
        ], 201);
    }

    /**
     * Update an existing motivation
     */
    public function update(Request $request, Motivation $motivation)
    {
        if ($motivation->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بتعديل هذا المحفّز.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'quote' => ['required', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $motivation->update([
            'title' => trim($validated['title']),
            'quote' => trim($validated['quote']),
            'category' => !empty($validated['category']) ? trim($validated['category']) : $motivation->category,
            'icon' => !empty($validated['icon']) ? trim($validated['icon']) : $motivation->icon,
            'is_pinned' => isset($validated['is_pinned']) ? (bool)$validated['is_pinned'] : $motivation->is_pinned,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل المحفّز بنجاح ✓',
            'motivation' => $motivation,
        ]);
    }

    /**
     * Delete a motivation
     */
    public function destroy(Motivation $motivation)
    {
        if ($motivation->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بحذف هذا المحفّز.');
        }

        $motivation->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المحفّز بنجاح ✓',
        ]);
    }

    /**
     * Toggle pinned status
     */
    public function togglePin(Motivation $motivation)
    {
        if ($motivation->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بتعديل هذا المحفّز.');
        }

        $newPinned = !$motivation->is_pinned;
        $motivation->update(['is_pinned' => $newPinned]);

        return response()->json([
            'success' => true,
            'is_pinned' => $newPinned,
            'message' => $newPinned ? 'تم تثبيت المحفّز في الأعلى 📌' : 'تم إلغاء تثبيت المحفّز',
            'motivation' => $motivation,
        ]);
    }

    /**
     * Get a random motivation
     */
    public function random()
    {
        $user = Auth::user();
        $random = $user->motivations()->inRandomOrder()->first();

        return response()->json([
            'success' => true,
            'motivation' => $random,
        ]);
    }

    /**
     * Seed default starter inspirations if user has none
     */
    public function seedDefaults()
    {
        $user = Auth::user();
        if ($user->motivations()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لديك محفزات موجودة بالفعل.',
            ]);
        }

        $defaults = [
            [
                'title' => 'الخطوة الأولى',
                'quote' => 'ابدأ الآن، حتى لو بخطوة صغيرة.',
                'category' => 'انضباط',
                'icon' => 'fa-play',
                'is_pinned' => true,
            ],
            [
                'title' => 'قوة الإنجاز',
                'quote' => 'أنجز مهمة واحدة أفضل من تأجيل عشر مهام.',
                'category' => 'تركيز',
                'icon' => 'fa-bullseye',
                'is_pinned' => true,
            ],
            [
                'title' => 'الاستمرار',
                'quote' => 'استمر، فالتقدم أهم من الكمال.',
                'category' => 'استمرار',
                'icon' => 'fa-arrow-trend-up',
                'is_pinned' => false,
            ],
            [
                'title' => 'صفاء الذهن',
                'quote' => 'ركّز على هدفك ولا تشتت نفسك.',
                'category' => 'تركيز',
                'icon' => 'fa-compass',
                'is_pinned' => false,
            ],
            [
                'title' => 'بداية جديدة',
                'quote' => 'كل يوم فرصة جديدة للتقدم.',
                'category' => 'إلهام',
                'icon' => 'fa-sun',
                'is_pinned' => false,
            ],
        ];

        foreach ($defaults as $d) {
            $user->motivations()->create($d);
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة باقة المحفزات التأسيسية بنجاح ✓',
            'count' => count($defaults),
        ]);
    }
}
