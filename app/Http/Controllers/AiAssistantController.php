<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use App\Models\AiMessage;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    public function index()
    {
        $user = Auth::user();

        $messages = $user->aiMessages()
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingTasks = $user->tasks()
            ->where('status', 'pending')
            ->orderBy('priority', 'asc')
            ->get();

        $hasApiKey = !empty(env('GEMINI_API_KEY')) || !empty(env('OPENAI_API_KEY'));

        return view('ai.index', compact('messages', 'pendingTasks', 'hasApiKey'));
    }

    public function chat(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
            'mode' => ['nullable', 'string', 'in:distracted,plan_day,breakdown_task,web_search'],
            'task_id' => ['nullable', 'integer'],
        ]);

        $mode = $validated['mode'] ?? null;
        $taskId = $validated['task_id'] ?? null;
        $message = trim($validated['message'] ?? '');

        // Generate friendly default prompts if triggered from quick action buttons
        if (empty($message)) {
            if ($mode === 'distracted') {
                $message = 'أنا مشتت ومش عارف من وين أبدأ، رتّب لي وضعي وساعدني أبدأ بمهمة واحدة.';
            } elseif ($mode === 'plan_day') {
                $message = 'رتّب لي يومي بخطة ذكية وواقعية بناءً على مهامي وعاداتي اليوم.';
            } elseif ($mode === 'breakdown_task') {
                $task = $taskId ? $user->tasks()->find($taskId) : null;
                $taskTitle = $task ? "«{$task->title}»" : 'هذه المهمة';
                $message = "قسّم لي {$taskTitle} إلى مهام فرعية صغيرة ومرتبة.";
            } elseif ($mode === 'web_search') {
                $message = 'ابحث في الويب عن أفضل الطرق لتنظيم الوقت وزيادة الإنتاجية.';
            }
        }

        // 1. Save user's question to DB
        $userMsg = $user->aiMessages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        // 2. Process via AiService
        $aiResult = $this->aiService->respond($user, $message, $mode, $taskId);

        // 3. Save assistant reply to DB
        $assistantMsg = $user->aiMessages()->create([
            'role' => 'assistant',
            'content' => $aiResult['content'],
            'action_type' => $aiResult['action_type'],
            'action_data' => $aiResult['action_data'],
            'is_action_executed' => false,
        ]);

        return response()->json([
            'success' => true,
            'user_message' => $userMsg,
            'assistant_message' => $assistantMsg,
            'provider' => $aiResult['provider'],
        ]);
    }

    /**
     * User confirmation for executing AI suggested action (e.g. adding subtasks)
     */
    public function applyAction(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'message_id' => ['required', 'exists:ai_messages,id'],
            'action' => ['required', 'string', 'in:add_subtasks'],
            'task_id' => ['required', 'integer'],
            'subtasks' => ['required', 'array', 'min:1'],
            'subtasks.*' => ['required', 'string', 'max:255'],
        ]);

        $aiMessage = $user->aiMessages()->findOrFail($validated['message_id']);
        $task = $user->tasks()->findOrFail($validated['task_id']);

        $createdSubtasks = [];
        $order = $task->subtasks()->count();
        foreach ($validated['subtasks'] as $title) {
            $order++;
            $createdSubtasks[] = $task->subtasks()->create([
                'title' => trim($title),
                'is_completed' => false,
                'order' => $order,
            ]);
        }

        $task->syncStatusWithSubtasks();

        $aiMessage->update(['is_action_executed' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة ' . count($createdSubtasks) . ' مهام فرعية إلى المهمة بنجاح! ✓',
            'task_id' => $task->id,
            'subtasks_count' => count($createdSubtasks),
        ]);
    }

    /**
     * Search personal knowledge base (reflections, tasks, habits, excuses)
     */
    public function searchKnowledge(Request $request)
    {
        $user = Auth::user();
        $query = trim($request->input('q', ''));
        if (empty($query)) {
            return response()->json(['success' => true, 'results' => []]);
        }

        $results = app(\App\Services\PersonalKnowledgeSearchService::class)->search($user, $query, 10);

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $results,
        ]);
    }

    /**
     * User confirmation for executing AI suggested financial transaction
     */
    public function confirmTransaction(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'message_id' => ['nullable', 'integer'],
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'currency' => ['required', 'string', 'max:10'],
            'category_id' => ['nullable', 'exists:financial_categories,id'],
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if (!empty($validated['category_id'])) {
            $cat = $user->financialCategories()->find($validated['category_id']);
            if (!$cat) {
                return response()->json(['success' => false, 'message' => 'التصنيف المختار غير صالح'], 422);
            }
        }

        $tx = $user->financialTransactions()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'category_id' => $validated['category_id'] ?? null,
            'date' => $validated['date'],
            'description' => $validated['description'],
        ]);

        if (!empty($validated['message_id'])) {
            $user->aiMessages()->where('id', $validated['message_id'])->update(['is_action_executed' => true]);
        }

        $typeLabel = $tx->type === 'income' ? 'دخل' : 'مصروف';
        $message = "تم حفظ ال{$typeLabel} بقيمة " . number_format($tx->amount, 2) . " {$tx->currency} بنجاح! ✓";

        return response()->json([
            'success' => true,
            'message' => $message,
            'transaction' => $tx,
        ]);
    }

    /**
     * Clear Chat History
     */
    public function clearHistory()
    {
        $user = Auth::user();
        $user->aiMessages()->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم مسح سجل المحادثة بنجاح']);
        }

        return redirect()->back()->with('success', 'تم مسح سجل المحادثة بنجاح.');
    }
}
