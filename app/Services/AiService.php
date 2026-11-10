<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\AiMessage;
use App\Models\Skill;
use App\Models\SubSkill;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiService
{
    public function __construct(
        protected WebSearchService $searchService,
        protected PersonalKnowledgeSearchService $knowledgeSearchService
    ) {}

    /**
     * Generate response for user chat message or quick action.
     */
    public function respond(User $user, string $message, ?string $mode = null, ?int $targetTaskId = null): array
    {
        $message = trim($message);

        // 1. Finance Intent Check (سجل مصروف / أضف دخل / صرفت ...)
        $financeIntent = $this->detectFinanceIntent($message, $user);
        if ($financeIntent !== null) {
            if (isset($financeIntent['needs_clarification'])) {
                return [
                    'content' => $financeIntent['message'],
                    'action_type' => null,
                    'action_data' => null,
                    'provider' => 'نظام المعاملات المالية MO AI',
                ];
            }

            return [
                'content' => "حللت طلبك لتسجيل المعاملة المالية. يرجى مراجعة التفاصيل أدناه وتأكيدها لحفظها في حسابك:",
                'action_type' => 'confirm_transaction',
                'action_data' => $financeIntent,
                'provider' => 'نظام المعاملات المالية MO AI',
            ];
        }

        // 2. Personal Knowledge Intent Check (ابحث في ملاحظاتي / وين كتبت ...)
        $knowledgeQuery = $this->detectKnowledgeSearchIntent($message, $mode);
        if ($knowledgeQuery !== null) {
            $results = $this->knowledgeSearchService->search($user, $knowledgeQuery, 6);
            $msgContent = !empty($results)
                ? "وجدت لك النتائج التالية من ملاحظاتك وسجلاتك الشخصية المحفوظة:"
                : "بحثت في ملاحظاتك وتأملاتك وسجلاتك المحفوظة عن «{$knowledgeQuery}»، لكن لم أجد أي نتائج مطابقة. يمكنك إضافة تأمل جديد في صفحة الملاحظات والتأملات.";

            return [
                'content' => $msgContent,
                'action_type' => 'knowledge_search',
                'action_data' => [
                    'query' => $knowledgeQuery,
                    'results' => $results,
                ],
                'provider' => 'محرك البحث في المعرفة الشخصية',
            ];
        }

        // 3. Web Search Intent Check
        $webSearchResults = [];
        $isWebSearch = ($mode === 'web_search') || $this->shouldSearchWeb($message);
        if ($isWebSearch) {
            $searchQuery = $this->extractSearchQuery($message);
            $webSearchResults = $this->searchService->search($searchQuery, 4);
        }

        // 4. Prepare User's real Context from Database
        $context = $this->buildUserContext($user, $targetTaskId);

        // 5. Build System & User Prompt
        $prompt = $this->buildPrompt($user, $message, $mode, $context, $webSearchResults, $targetTaskId);

        // 6. Call Real AI Model (Gemini / OpenAI) or Fallback Engine
        $aiResponse = $this->callModel($prompt);

        // 7. Parse any structured action from AI response
        $parsed = $this->parseResponseActions($aiResponse['text'], $mode, $user, $targetTaskId, $webSearchResults);

        return [
            'content' => $parsed['clean_text'],
            'action_type' => $parsed['action_type'],
            'action_data' => $parsed['action_data'],
            'provider' => $aiResponse['provider'],
        ];
    }

    /**
     * Generate a contextual, inspiring motivation using AI
     */
    public function generateMotivation(User $user, ?string $topic = null): array
    {
        $context = $this->buildUserContext($user);
        $topTaskTitle = $context['pending_tasks']->first()?->title ?? 'أهدافك الحالية';
        $pendingCount = $context['total_pending'];
        $streak = 0;
        foreach ($user->habits as $h) {
            $s = $h->getCurrentStreak();
            if ($s > $streak) $streak = $s;
        }

        $topicText = $topic ? "موضوع محدد طلبه المستخدم: «{$topic}»" : "بناءً على واقعه اليومي (لديه {$pendingCount} مهام غير مكتملة، أبرزها: «{$topTaskTitle}»، وسلسلة التزام بالعادات: {$streak} أيام)";

        $prompt = <<<EOT
أنت المساعد الذكي لنظام «MO Habit Tracker & Life OS».
مهمتك: توليد محفّز شخصي استثنائي، عميق، وقصير يلهم المستخدم ({$user->name}) للبقاء منضبطاً وإنجاز أهدافه دون تسويف.
المعطيات: {$topicText}.

قواعد صارمة:
1. العبارة التحفيزية يجب أن تكون مركزة وقوية وملهمة جداً باللغة العربية (جملة أو جملتان قصيرتان بحد أقصى).
2. العنوان: عنوان جذاب وموجز جداً (كلمتان أو ثلاث).
3. التصنيف: اختر تصنيفاً مناسباً (مثل: انضباط، تركيز، استمرار، إنجاز، طاقة، وضوح).
4. الأيقونة: اختر أيقونة FontAwesome من بين: fa-bolt, fa-lightbulb, fa-bullseye, fa-play, fa-arrow-trend-up, fa-compass, fa-rocket, fa-crown, fa-star, fa-sun, fa-quote-right.

أجب بكود JSON فقط دون أي نصوص إضافية قبله أو بعده:
```json
{
  "title": "عنوان المحفّز",
  "quote": "نص العبارة التحفيزية هنا",
  "category": "انضباط",
  "icon": "fa-bolt"
}
```
EOT;

        $res = $this->callModel($prompt);
        $text = $res['text'] ?? '';

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (is_array($decoded) && !empty($decoded['title']) && !empty($decoded['quote'])) {
                $icon = trim($decoded['icon'] ?? 'fa-bolt');
                if ($icon === 'fa-fire' || $icon === 'fa-calendar-check') $icon = 'fa-bolt';
                return [
                    'title' => trim($decoded['title']),
                    'quote' => trim($decoded['quote']),
                    'category' => trim($decoded['category'] ?? 'انضباط'),
                    'icon' => $icon,
                    'provider' => $res['provider'],
                ];
            }
        }

        $decodedDirect = json_decode(trim($text), true);
        if (is_array($decodedDirect) && !empty($decodedDirect['title']) && !empty($decodedDirect['quote'])) {
            $icon = trim($decodedDirect['icon'] ?? 'fa-bolt');
            if ($icon === 'fa-fire' || $icon === 'fa-calendar-check') $icon = 'fa-bolt';
            return [
                'title' => trim($decodedDirect['title']),
                'quote' => trim($decodedDirect['quote']),
                'category' => trim($decodedDirect['category'] ?? 'انضباط'),
                'icon' => $icon,
                'provider' => $res['provider'],
            ];
        }

        // Dynamic heuristic fallback
        $fallbacks = [
            [
                'title' => 'قوة البداية',
                'quote' => "لا تنتظر المزاج المثالي لتبدأ يا {$user->name}؛ الفعل هو من يصنع الرغبة والشغف الحقيقي.",
                'category' => 'انضباط',
                'icon' => 'fa-bolt',
            ],
            [
                'title' => 'التركيز الصافي',
                'quote' => 'تجاوز ضجيج الأفكار وركّز فقط على ما يمكنك إنجازه في الـ 20 دقيقة القادمة.',
                'category' => 'تركيز',
                'icon' => 'fa-bullseye',
            ],
            [
                'title' => 'عزم لا يلين',
                'quote' => 'الفرق بين ما أنت عليه وما تريده، هو الالتزام بالخطوات الصغيرة كل يوم.',
                'category' => 'استمرار',
                'icon' => 'fa-arrow-trend-up',
            ],
            [
                'title' => 'قهر التسويف',
                'quote' => 'خمس دقائق من العمل الفعلي كفيلة بكسر حاجز المماطلة وتوليد الزخم المطلوب.',
                'category' => 'إنجاز',
                'icon' => 'fa-bolt',
            ],
        ];

        $picked = $fallbacks[array_rand($fallbacks)];
        $picked['provider'] = 'محرك MO AI التحفيزي';
        return $picked;
    }

    /**
     * Gather user's actual database context (tasks, subtasks, habits)
     */
    public function buildUserContext(User $user, ?int $targetTaskId = null): array
    {
        $today = Carbon::today()->toDateString();

        // Target task if specified
        $targetTask = null;
        if ($targetTaskId) {
            $targetTask = $user->tasks()->with('subtasks')->find($targetTaskId);
        }

        // Uncompleted tasks
        $pendingTasks = $user->tasks()
            ->with(['category', 'subtasks'])
            ->where('status', 'pending')
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->take(15)
            ->get();

        // Tasks overdue
        $overdueTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->get();

        // Tasks due today
        $dueTodayTasks = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', $today)
            ->get();

        // Today's Habits Status
        $habits = $user->habits()->where('is_archived', false)->get();
        $todayLogs = $user->habitLogs()->whereDate('log_date', $today)->get()->keyBy('habit_id');

        $habitsSummary = [];
        foreach ($habits as $h) {
            $log = $todayLogs->get($h->id);
            $habitsSummary[] = [
                'name' => $h->name,
                'status' => $log ? ($log->status === 'completed' ? 'تم الإنجاز ✓' : 'لم يتم الإنجاز') : 'بانتظار التسجيل',
            ];
        }

        // Phase 2: Today's Smart Daily Plan & Calendar Events
        $todayPlan = $user->dailyPlans()
            ->whereDate('plan_date', $today)
            ->with('items')
            ->first();

        $planItemsSummary = [];
        if ($todayPlan && $todayPlan->items) {
            foreach ($todayPlan->items->sortBy('start_time') as $pi) {
                $status = $pi->is_completed ? 'منجزة ✓' : 'معلقة';
                $planItemsSummary[] = "{$pi->start_time}-{$pi->end_time}: «{$pi->title}» [نوع: {$pi->type} | {$status}]";
            }
        }

        $todayCalendarEvents = $user->calendarEvents()
            ->whereDate('start_time', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $calendarSummary = [];
        foreach ($todayCalendarEvents as $ce) {
            $time = $ce->is_all_day ? 'طوال اليوم' : $ce->start_time->format('H:i');
            $calendarSummary[] = "{$time}: «{$ce->title}» ({$ce->event_type})";
        }

        return [
            'user_name' => $user->name,
            'today_date' => Carbon::now()->locale('ar')->translatedFormat('l d F Y'),
            'current_time' => Carbon::now()->format('H:i'),
            'target_task' => $targetTask,
            'pending_tasks' => $pendingTasks,
            'overdue_count' => $overdueTasks->count(),
            'due_today_count' => $dueTodayTasks->count(),
            'total_pending' => $pendingTasks->count(),
            'habits' => $habitsSummary,
            'plan_items' => $planItemsSummary,
            'calendar_events' => $calendarSummary,
        ];
    }

    /**
     * Construct the full prompt
     */
    protected function buildPrompt(User $user, string $message, ?string $mode, array $context, array $webSearchResults, ?int $targetTaskId): string
    {
        $systemInstructions = <<<EOT
أنت «MO AI Assistant» — المساعد الشخصي الذكي لإدارة المهام والانضباط في نظام MO Habit Tracker.
مهمتك:
1. مساعدة المستخدم ({$context['user_name']}) على تنظيم يومه، إنجاز مهامه، التغلب على التشتت، والتركيز على خطوة واحدة واضحة في كل مرة.
2. تتحدث باللغة العربية الواضحة بأسلوب راقٍ وودود وعملي، وتفهم وتستوعب تماماً اللهجة الفلسطينية والشامية (مثل: شو أعمل، مشتت، هسة، رتّبلي، قسّملي، مش عارف من وين أبدأ).
3. قواعد صارمة:
- لا تخترع مهاماً غير موجودة في قاعدة بيانات المستخدم عندما يسأل عن مهامه الحالية.
- لا تشتته بقوائم طويلة ومعقدة؛ قدم إجابات مباشرة، مركزة، وعملية.
- في وضع «أنا مشتت»: ركز على مهمة واحدة محددة فقط وابدأ بخطوة صغيرة (15-20 دقيقة).
- عند سؤال المستخدم عن «شو خطتي اليوم» أو «شو المهمة الجاية»: استند إلى جدول خطة اليوم ومواعيد التقويم المعروضة أدناه بدقة.
- عند سؤال المستخدم عن «تأخرت اليوم ومش رح ألحق»: قدّم له «خطة إنقاذ (Rescue Plan)» فورية ومهدئة: اقترح تأجيل المهام غير الحرجة إلى الغد، وحصر ما تبقى من اليوم في أهم مهمة واحدة تحقق أعلى أثر دون توتر.
- عند طلب «رتّب لي بكرة» أو «ملخص أسبوعي»: قدّم إجابة ملهمة، محددة، ومستندة للواقع.
- عند اقتراح مهام فرعية: اعرضها بوضوح في نقاط موجزة وقابلة للتنفيذ.
- عند البحث في الويب: ميّز بوضوح بين نتائج الويب الخارجية وبيانات المستخدم في الموقع.
EOT;

        // Context summary string
        $tasksText = "المهام الحالية غير المكتملة للمستخدم في قاعدة البيانات:\n";
        if ($context['pending_tasks']->isEmpty()) {
            $tasksText .= "- لا توجد مهام معلقة حالياً.\n";
        } else {
            foreach ($context['pending_tasks'] as $idx => $t) {
                $subTotal = $t->subtasks->count();
                $subDone = $t->subtasks->where('is_completed', true)->count();
                $due = $t->due_date ? $t->due_date->format('Y-m-d') : 'بدون موعد';
                $priority = $t->priority === 'high' ? 'عالية 🔥' : ($t->priority === 'medium' ? 'متوسطة' : 'منخفضة');
                $subInfo = $subTotal > 0 ? " (مهام فرعية: {$subDone}/{$subTotal})" : "";
                $tasksText .= ($idx + 1) . ". [معرف: {$t->id}] «{$t->title}» | الأولوية: {$priority} | الموعد: {$due}{$subInfo}\n";
            }
        }

        $habitsText = "حالة العادات اليومية لليوم ({$context['today_date']}):\n";
        foreach ($context['habits'] as $h) {
            $habitsText .= "- {$h['name']}: {$h['status']}\n";
        }

        $planText = "\nجدول خطة اليوم الذكية (Smart Daily Plan) [الوقت الحالي: {$context['current_time']}]:\n";
        if (empty($context['plan_items'])) {
            $planText .= "- لم يتم توليد أو اعتماد خطة يومية لليوم بعد.\n";
        } else {
            foreach ($context['plan_items'] as $pi) {
                $planText .= "- {$pi}\n";
            }
        }

        $calText = "\nمواعيد التقويم لليوم:\n";
        if (empty($context['calendar_events'])) {
            $calText .= "- لا توجد مواعيد مسجلة في التقويم لليوم.\n";
        } else {
            foreach ($context['calendar_events'] as $ce) {
                $calText .= "- {$ce}\n";
            }
        }

        $webText = "";
        if (!empty($webSearchResults)) {
            $webText = "\nنتائج البحث الحقيقي في الويب (مصادر خارجية):\n";
            foreach ($webSearchResults as $r) {
                $webText .= "• العنوان: {$r['title']}\n  المصدر: {$r['source']} ({$r['url']})\n  الملخص: {$r['snippet']}\n";
            }
        }

        // Special mode instructions
        $modeInstructions = "";
        if ($mode === 'distracted') {
            $modeInstructions = <<<EOT
[وضع: أنا مشتت]:
المستخدم يشعر بالتشتت الشديد الآن.
1. راجع مهامه غير المكتملة واختر مهمة واحدة فقط هي الأكثر أهمية واستعجالاً للبدء بها فوراً.
2. اشرح بجملة واحدة قصيرة لماذا اخترت هذه المهمة.
3. قسّمها إلى خطوة أولى بسيطة جداً قابلة للتنفيذ في 15 إلى 25 دقيقة.
4. شجعه بأسلوب دافئ وموجز.
5. في نهاية ردك، أضف السطر البرمجي التالي بصيغة JSON فقط:
```json
{"action": "distracted_task", "task_id": المعرف_الرقمي_للمهمة, "first_step": "اسم الخطوة الأولى المقترحة", "duration_minutes": 20}
```
EOT;
        } elseif ($mode === 'plan_day') {
            $modeInstructions = <<<EOT
[وضع: رتّب يومي]:
أنشئ خطة يومية واقعية ومتوازنة مبنية على مهامه غير المكتملة وعاداته، مقسمة إلى (صباحاً / بعد الظهر / مساءً) مع فترات راحة قصيرة لتجنب الإرهاق.
في نهاية ردك أضف السطر التالي:
```json
{"action": "daily_plan"}
```
EOT;
        } elseif ($mode === 'breakdown_task' || $targetTaskId) {
            $modeInstructions = <<<EOT
[وضع: تقسيم المهمة]:
المهمة المستهدفة: «{$context['target_task']?->title}» (المعرف: {$targetTaskId})
اقترح 4 إلى 6 مهام فرعية واضحة ومتسلسلة ومنطقية لإنجاز هذه المهمة.
اعرضها للمستخدم في نقاط واضحة، وفي نهاية ردك أضف كود JSON التالي بدقة لتفعيل زر الإضافة التلقائية:
```json
{"action": "subtask_breakdown", "task_id": {$targetTaskId}, "subtasks": ["مهمة فرعية 1", "مهمة فرعية 2", "مهمة فرعية 3", "مهمة فرعية 4"]}
```
EOT;
        }

        return "{$systemInstructions}\n\n{$tasksText}\n{$habitsText}{$planText}{$calText}{$webText}\n{$modeInstructions}\n\nرسالة المستخدم: {$message}";
    }

    /**
     * Call AI Model (Gemini / OpenAI) with graceful fallback
     */
    protected function callModel(string $prompt): array
    {
        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $openaiKey = env('OPENAI_API_KEY');

        // 1. Google Gemini API
        if (!empty($geminiKey)) {
            $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash');
            $geminiRes = $this->callGemini($geminiKey, $model, $prompt);
            if ($geminiRes) {
                return [
                    'text' => $geminiRes,
                    'provider' => "Google Gemini ({$model})",
                ];
            }
        }

        // 2. OpenAI / Groq Compatible API
        if (!empty($openaiKey)) {
            $openaiRes = $this->callOpenAI($openaiKey, $prompt);
            if ($openaiRes) {
                return [
                    'text' => $openaiRes,
                    'provider' => 'OpenAI / Compatible API',
                ];
            }
        }

        // 3. Fallback Smart Local Engine (offline heuristic assistant)
        return [
            'text' => $this->fallbackSmartEngine($prompt),
            'provider' => 'محرك MO AI المحلي (أضف GEMINI_API_KEY لتفعيل الذكاء الاصطناعي السحابي)',
        ];
    }

    /**
     * Call Google Gemini REST API with automatic model resiliency
     */
    protected function callGemini(string $apiKey, string $model, string $prompt): ?string
    {
        $modelsToTry = array_unique([
            $model,
            'gemini-2.0-flash',
            'gemini-1.5-flash-latest',
            'gemini-1.5-flash',
            'gemini-flash-latest',
        ]);

        foreach ($modelsToTry as $targetModel) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$targetModel}:generateContent";

                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'x-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(10)
                    ->post($url, [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 1200,
                        ]
                    ]);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    if (!empty($text)) {
                        return trim($text);
                    }
                } else {
                    $status = $response->status();
                    Log::warning("Gemini API ({$targetModel}) status: {$status}");
                }
            } catch (\Throwable $e) {
                Log::error("Gemini API ({$targetModel}) exception: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Test Gemini Connection
     */
    public function testGeminiConnection(?string $apiKey = null, ?string $model = null): array
    {
        $key = $apiKey ?: (config('services.gemini.key') ?: env('GEMINI_API_KEY'));
        $targetModel = $model ?: (config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash'));

        if (empty($key)) {
            return [
                'success' => false,
                'message' => 'مفتاح GEMINI_API_KEY غير موجود في ملف .env.',
            ];
        }

        $testPrompt = 'أجب بكلمة واحدة فقط بالعربية: "متصل"';
        $reply = $this->callGemini($key, $targetModel, $testPrompt);

        if ($reply) {
            return [
                'success' => true,
                'model' => $targetModel,
                'reply' => $reply,
                'message' => "تم الاتصال بنجاح بنموذج Google Gemini ({$targetModel})!",
            ];
        }

        return [
            'success' => false,
            'message' => 'فشل الاتصال بـ Gemini API. يرجى التأكد من صلاحية المفتاح والاتصال بالإنترنت.',
        ];
    }

    /**
     * Call OpenAI / Groq Compatible API
     */
    protected function callOpenAI(string $apiKey, string $prompt): ?string
    {
        try {
            $baseUrl = env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $model = env('OPENAI_MODEL', 'gpt-4o-mini');

            $response = Http::withoutVerifying()
                ->timeout(25)
                ->withToken($apiKey)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1200,
                ]);

            if ($response->successful()) {
                $text = $response->json('choices.0.message.content');
                if (!empty($text)) {
                    return trim($text);
                }
            }
        } catch (\Throwable $e) {
            Log::error('OpenAI API exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Fallback Smart Logic when no API key is provided
     */
    protected function fallbackSmartEngine(string $prompt): string
    {
        if (str_contains($prompt, '[وضع: أنا مشتت]')) {
            return "سلامتك من التشتت! لا تقلق، خذ نفساً عميقاً، مش مطلوب تنجز كل شيء اليوم هسة.\n\n" .
                   "🌟 **خطوتك الوحيدة الآن:**\n" .
                   "راجع أعلى مهمة في أولوياتك اليوم، وافتحها فقط لمدة 20 دقيقة.\n" .
                   "أغلق كل الإشعارات والتطبيقات المشتتة، وابدأ فقط بالخطوة الأولى البسيطة (مثل قراءة أو كتابة أول نقطة).\n\n" .
                   "تذكر: **خطوة واحدة صغيرة منجزة أفضل من عشر أفكار في الرأس.** ابدأ الآن!";
        }

        if (str_contains($prompt, '[وضع: رتّب يومي]')) {
            return "إليك خطة يومية مقترحة لتنظيم يومك بتوازن وبدون إرهاق:\n\n" .
                   "🌅 **الفترة الأولى (صباحاً / بداية اليوم):**\n" .
                   "• إنجاز عادة الفجر وشرب الماء والاستيقاظ بنشاط.\n" .
                   "• التركيز على المهمة الأولى ذات الأولوية العالية.\n\n" .
                   "☀️ **فترة الظهيرة:**\n" .
                   "• متابعة المهام ذات الأولوية المتوسطة وإنهاء المهام المعلقة الصغيرة.\n" .
                   "• استراحة خفيفة وأكل صحي لتجديد الطاقة.\n\n" .
                   "🌙 **فترة المساء:**\n" .
                   "• ساعة رياضة أو نشاط بدني بدون هواتف وسوشيال ميديا.\n" .
                   "• مراجعة إنجازات اليوم وتدوين التأملات في صفحة الملاحظات.\n\n" .
                   "جاهز تبدأ بالخطوة الأولى؟";
        }

        if (str_contains($prompt, '[وضع: تقسيم المهمة]')) {
            return "تقسيم المهام الكبيرة إلى خطوات صغيرة هو المفتاح الأساسي للتنفيذ السريع وتجنب التسويف.\n\n" .
                   "المراحل الأساسية المقترحة لأي مهمة:\n" .
                   "1. تحديد متطلبات المهمة وكتابة النقاط الأساسية (15 دقيقة).\n" .
                   "2. تجهيز الأدوات والبيئة اللازمة للعمل.\n" .
                   "3. البدء بتنفيذ الجزء الأول الأساسي.\n" .
                   "4. مراجعة واختبار ما تم إنجازه.\n" .
                   "5. اللمسات النهائية وحفظ الإنجاز.";
        }

        if (str_contains($prompt, 'نتائج البحث الحقيقي في الويب')) {
            return "بناءً على نتائج البحث التي وجدناها لك على الويب، قمنا بإدراج أهم المصادر والمراجع المباشرة أدناه لتطلع عليها فوراً وتستفيد منها.";
        }

        return "أهلاً بك! أنا معك لمساعدتك على ترتيب مهامك وأولوياتك. يمكنك اختياري لمساعدتك في:\n" .
               "• الضغط على **«أنا مشتت»** إذا كنت لا تعرف من أين تبدأ.\n" .
               "• الضغط على **«رتّب يومي»** للحصول على خطة يومية ذكية.\n" .
               "• تقسيم أي مهمة إلى مهام فرعية سهلة.\n" .
               "• البحث في الويب عن أي معلومة أو مصادر تعليمية.\n\n" .
               "💡 *لتفعيل نموذج الذكاء الاصطناعي التوليدي الكامل (Gemini)، ما عليك سوى وضع مفتاح GEMINI_API_KEY في ملف .env.*";
    }

    /**
     * Parse structured actions from AI response text
     */
    protected function parseResponseActions(string $text, ?string $mode, User $user, ?int $targetTaskId, array $webSearchResults): array
    {
        $actionType = null;
        $actionData = null;

        // 1. Look for ```json { ... } ``` block in text
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            $jsonStr = $matches[1];
            $decoded = json_decode($jsonStr, true);
            if (is_array($decoded) && isset($decoded['action'])) {
                $actionType = $decoded['action'];
                $actionData = $decoded;
                // Clean the json code block from visible message
                $text = trim(str_replace($matches[0], '', $text));
            }
        }

        // 2. If mode is web search and we got search results
        if (!empty($webSearchResults)) {
            $actionType = 'web_search';
            $actionData = [
                'query' => $this->extractSearchQuery($text),
                'results' => $webSearchResults,
            ];
        }

        // 3. Fallback action detection for distracted mode
        if ($mode === 'distracted' && !$actionType) {
            $topTask = $user->tasks()->where('status', 'pending')
                ->orderByRaw("due_date IS NULL, due_date ASC")
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
                ->first();

            if ($topTask) {
                $actionType = 'distracted_task';
                $actionData = [
                    'task_id' => $topTask->id,
                    'task_title' => $topTask->title,
                    'duration_minutes' => 20,
                    'first_step' => 'التركيز على هذه المهمة لمدة 20 دقيقة بدون تشتت',
                ];
            }
        }

        return [
            'clean_text' => $text,
            'action_type' => $actionType,
            'action_data' => $actionData,
        ];
    }

    /**
     * Detect if message is a Personal Finance logging intent
     */
    protected function detectFinanceIntent(string $message, User $user): ?array
    {
        $isFinance = preg_match('/(?:سجل|سجّل|أضف|اضف|صرفت|دفعت|اشتريت|دفع|شراء|قيد|استلمت|جاني|قبضت)\s*(?:لي)?\s*(?:مصروف|نفقة|تكلفة|دخل|راتب|مبلغ)?/ui', $message)
                  || preg_match('/(?:مصروف|مصاريف|نفقات|دخل|راتب)\s*(?:بقيمة|بمبلغ|\d+)/ui', $message);

        if (!$isFinance) {
            return null;
        }

        // Amount extraction
        if (!preg_match('/(?:بقيمة|بمبلغ|قيمة|مبلغ|\$|₪)?\s*([0-9]+(?:\.[0-9]+)?)\s*(دولار(?:اً)?|شيكل(?:اً)?|يورو|ريال(?:اً)?|جنيه(?:اً)?|دينار(?:اً)?|USD|ILS|EUR|SAR|EGP|JOD)?/ui', $message, $matches)) {
            return [
                'needs_clarification' => true,
                'message' => "أهلاً بك! لتسجيل المعاملة بدقة، كم المبلغ والعملة؟ (مثال: «سجل مصروف 25 دولار للمواصلات» أو «أضف دخل 500 دولار»)",
            ];
        }

        $amount = (float) $matches[1];
        if ($amount <= 0) {
            return null;
        }

        $rawCurrency = $matches[2] ?? '';
        $currencyMap = [
            'دولار' => 'USD', 'دولاراً' => 'USD', 'USD' => 'USD', '$' => 'USD',
            'شيكل' => 'ILS', 'شيكلاً' => 'ILS', 'ILS' => 'ILS', '₪' => 'ILS',
            'يورو' => 'EUR', 'EUR' => 'EUR', '€' => 'EUR',
            'ريال' => 'SAR', 'ريالاً' => 'SAR', 'SAR' => 'SAR',
            'جنيه' => 'EGP', 'جنيهاً' => 'EGP', 'EGP' => 'EGP',
            'دينار' => 'JOD', 'ديناراً' => 'JOD', 'JOD' => 'JOD',
        ];

        $defaultCurrency = $user->settings?->currency ?: 'USD';
        $currency = $currencyMap[$rawCurrency] ?? $defaultCurrency;

        $isIncome = preg_match('/(?:دخل|استلمت|جاني|قبضت|راتب|أرباح|تحويل وارد)/ui', $message);
        $type = $isIncome ? 'income' : 'expense';

        $categories = $user->financialCategories()->get();
        $matchedCategory = null;

        // Try exact name match
        foreach ($categories as $cat) {
            $words = preg_split('/[\s،,]+/u', $cat->name);
            foreach ($words as $w) {
                if (mb_strlen($w) >= 3 && mb_stripos($message, $w) !== false) {
                    $matchedCategory = $cat;
                    break 2;
                }
            }
        }

        // Try category heuristics
        if (!$matchedCategory) {
            if (preg_match('/(?:سوبرماركت|مطعم|غداء|عشاء|فطور|قهوة|أكل|طعام|وجبة)/ui', $message)) {
                $matchedCategory = $categories->firstWhere('name', 'طعام ومشروبات');
            } elseif (preg_match('/(?:مواصلات|تاكسي|بنزين|أجرة|سيارة|باص|وقود)/ui', $message)) {
                $matchedCategory = $categories->firstWhere('name', 'مواصلات ونقل');
            } elseif (preg_match('/(?:فاتورة|كهرباء|ماء|نت|إنترنت|اشتراك)/ui', $message)) {
                $matchedCategory = $categories->firstWhere('name', 'فواتير والتزامات');
            } elseif (preg_match('/(?:دورة|كورس|كتاب|تعليم|دراسة)/ui', $message)) {
                $matchedCategory = $categories->firstWhere('name', 'تعليم ودورات');
            } elseif (preg_match('/(?:جيم|رياضة|تمرين|حديد)/ui', $message)) {
                $matchedCategory = $categories->firstWhere('name', 'جيم ورياضة');
            } elseif (preg_match('/(?:طبيب|صيدلية|دواء|علاج|مستشفى)/ui', $message)) {
                $matchedCategory = $categories->firstWhere('name', 'صحة وعلاج');
            } elseif ($type === 'income') {
                $matchedCategory = $categories->firstWhere('name', 'راتب ودخل أساسي');
            }
        }

        // Clean description
        $desc = preg_replace('/(سجل|سجّل|أضف|اضف|صرفت|دفعت|اشتريت|دفع|شراء|دخل|مصروف|اليوم|أمس|امس|بقيمة|بمبلغ|قيمة|مبلغ|\b\d+(?:\.\d+)?\b|دولار|شيكل|يورو|ريال|جنيه|دينار|USD|ILS|EUR|SAR|EGP|JOD|[0-9]+)/ui', '', $message);
        $desc = preg_replace('/^(?:لـ|بـ|في|من|عن)\s*/u', '', trim($desc));
        $desc = trim(preg_replace('/\s+/', ' ', $desc));

        if (empty($desc) || mb_strlen($desc) < 2) {
            $desc = $matchedCategory ? $matchedCategory->name : ($type === 'income' ? 'دخل جديد' : 'مصروف جديد');
        }

        return [
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'category_id' => $matchedCategory?->id,
            'category_name' => $matchedCategory?->name ?? 'أخرى',
            'description' => $desc,
            'date' => Carbon::today()->toDateString(),
        ];
    }

    /**
     * Detect if message is a Personal Knowledge Search intent
     */
    protected function detectKnowledgeSearchIntent(string $message, ?string $mode): ?string
    {
        if ($mode === 'knowledge_search') {
            return !empty($message) ? $message : 'ملاحظاتي';
        }

        if (preg_match('/(?:ابحث|وين|أين|فين|دور|فتش|شو مسجل|ماذا كتبت|ماذا سجلت|استرجع)\s*(?:لي)?\s*(?:في|عن)?\s*(?:ملاحظاتي|دفاتري|تأملاتي|بياناتي|الملاحظات|التأملات)(?:\s*(?:عن|في|بخصوص)?\s*(.*))?/ui', $message, $matches)) {
            $q = trim($matches[1] ?? '');
            return !empty($q) ? $q : 'ملاحظاتي';
        }

        if (preg_match('/(?:وين كتبت|أين كتبت|أين دُوّن|وين مسجل)\s*(.*)/ui', $message, $matches)) {
            $q = trim($matches[1] ?? '');
            return !empty($q) ? $q : 'ملاحظاتي';
        }

        return null;
    }

    /**
     * Detect if user explicitly wants to search the external web
     */
    protected function shouldSearchWeb(string $message): bool
    {
        $explicitPatterns = [
            '/(?:ابحث|دور|فتش)\s*(?:لي)?\s*(?:في|على)?\s*(?:الويب|النت|الانترنت|جوجل|google)/ui',
            '/(?:أفضل|احسن|اقترح)\s*(?:كورس|دورة|موقع|أداة|برنامج|مكتبة|طريقة|حل)/ui',
            '/(?:ما هو|ما هي|كيف يعمل|طريقة حل|شرح)\s+[a-zA-Z\p{Arabic}]+/ui',
            '/(?:بحث في الويب|web search)/ui',
        ];

        foreach ($explicitPatterns as $p) {
            if (preg_match($p, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clean and extract search query from user message
     */
    protected function extractSearchQuery(string $message): string
    {
        $cleaned = preg_replace('/(ابحث لي عن|ابحث عن|ابحث في جوجل عن|ابحث في الويب عن|ابحث على google عن|ابحث في النت عن|ابحث|search for|google)/ui', '', $message);
        $cleaned = trim($cleaned);
        return !empty($cleaned) ? $cleaned : $message;
    }

    /**
     * Generate an AI-powered or Smart Heuristic Daily Plan Proposal
     */
    public function generateDailyPlanProposal(User $user, array $options = []): array
    {
        $date = $options['date'] ?? Carbon::today()->toDateString();
        $startHour = $options['start_hour'] ?? '08:30';
        $endHour = $options['end_hour'] ?? '21:30';
        $includeBreaks = $options['include_breaks'] ?? true;
        $bufferMinutes = $options['buffer_minutes'] ?? 30;

        // Pending tasks ordered by priority & due date
        $tasks = $user->tasks()
            ->where('status', 'pending')
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->take(12)
            ->get();

        // Calendar events for the day
        $events = $user->calendarEvents()
            ->whereDate('start_time', $date)
            ->get();

        $tasksSummary = "";
        foreach ($tasks as $t) {
            $due = $t->due_date ? $t->due_date->format('Y-m-d') : 'بدون موعد';
            $p = $t->priority === 'high' ? 'عالية' : ($t->priority === 'medium' ? 'متوسطة' : 'منخفضة');
            $tasksSummary .= "- [ID: {$t->id}] «{$t->title}» | الأولوية: {$p} | الاستحقاق: {$due}\n";
        }

        $eventsSummary = "";
        foreach ($events as $e) {
            $eventsSummary .= "- موعد محجوز: «{$e->title}» من {$e->start_time->format('H:i')} إلى {$e->end_time->format('H:i')}\n";
        }

        $prompt = <<<EOT
أنت خبير التخطيط والإنتاجية في نظام MO Life OS.
المطلوب: إنشاء خطة يومية واقعية ومتوازنة لليوم ({$date}) للفترة من {$startHour} إلى {$endHour}.
قواعد التخطيط الصارمة:
1. البدء بالمهام ذات الأولوية العالية والمستحقة اليوم.
2. تجنب التكديس غير الواقعي: إذا كان الوقت المتاح لا يتسع لجميع المهام، اختر أهم 3 إلى 5 مهام فقط للجدول، وضع الباقي في قائمة المهام غير المجدولة مع ذكر سبب التأجيل.
3. المواعيد المحجوزة مسبقاً (إن وجدت): لا تضع أي مهمة في أوقات المواعيد التالية:
{$eventsSummary}
4. فترات الراحة: أضف فترات راحة قصيرة (10-15 دقيقة) بين فترات العمل (كل 45-60 دقيقة عمل).
5. وقت احتياطي: خصص فترة احتياطية (30 دقيقة) للمهام الطارئة.
6. أعِد الرد بصيغة JSON حصراً بدون أي نصوص إضافية خارج كود الـ JSON بالشكل التالي:
```json
{
  "summary": "ملخص الخطة اليومية بأسلوب مشجع وموجز بالعربية",
  "items": [
    {
      "task_id": 1,
      "title": "عنوان المهمة أو الاستراحة",
      "start_time": "08:30",
      "end_time": "09:30",
      "duration_minutes": 60,
      "is_break": false,
      "is_buffer": false
    }
  ],
  "unscheduled_tasks": [
    {
      "task_id": 2,
      "title": "اسم المهمة",
      "reason": "تأجيل لتجنب الإرهاق والتركيز على أولوية اليوم"
    }
  ]
}
```

قائمة مهام المستخدم الفعلية:
{$tasksSummary}
EOT;

        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.5-flash');

        if (!empty($geminiKey)) {
            $raw = $this->callGemini($geminiKey, $model, $prompt);
            if ($raw && preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $raw, $m)) {
                $decoded = json_decode($m[1], true);
                if (is_array($decoded) && !empty($decoded['items'])) {
                    return [
                        'summary' => $decoded['summary'] ?? 'خطة يومية ذكية تم توليدها بالذكاء الاصطناعي.',
                        'items' => $decoded['items'],
                        'unscheduled_tasks' => $decoded['unscheduled_tasks'] ?? [],
                        'provider' => "Google Gemini ({$model})",
                    ];
                }
            }
        }

        // Fallback smart heuristic scheduler
        return $this->fallbackDeterministicPlanner($tasks, $startHour, $endHour, $includeBreaks, $bufferMinutes);
    }

    /**
     * Deterministic realistic schedule builder when offline
     */
    protected function fallbackDeterministicPlanner($tasks, string $startHour, string $endHour, bool $includeBreaks, int $bufferMinutes): array
    {
        $current = Carbon::createFromFormat('H:i', $startHour);
        $end = Carbon::createFromFormat('H:i', $endHour);

        $items = [];
        $unscheduled = [];

        foreach ($tasks as $idx => $t) {
            $duration = ($t->priority === 'high') ? 60 : 45;
            $next = $current->copy()->addMinutes($duration);

            if ($next->greaterThan($end)) {
                $unscheduled[] = [
                    'task_id' => $t->id,
                    'title' => $t->title,
                    'reason' => 'انتهاء الوقت المتاح لليوم؛ مقترحة للتنفيذ غداً لتجنب الإرهاق.',
                ];
                continue;
            }

            $items[] = [
                'task_id' => $t->id,
                'title' => $t->title,
                'start_time' => $current->format('H:i'),
                'end_time' => $next->format('H:i'),
                'duration_minutes' => $duration,
                'is_break' => false,
                'is_buffer' => false,
                'is_locked' => false,
            ];

            $current = $next;

            // Insert 15m break every 2 tasks if breaks enabled
            if ($includeBreaks && ($idx % 2 === 1) && $current->copy()->addMinutes(15)->lessThanOrEqualTo($end)) {
                $breakEnd = $current->copy()->addMinutes(15);
                $items[] = [
                    'task_id' => null,
                    'title' => 'استراحة قصيرة وتجديد طاقة ☕',
                    'start_time' => $current->format('H:i'),
                    'end_time' => $breakEnd->format('H:i'),
                    'duration_minutes' => 15,
                    'is_break' => true,
                    'is_buffer' => false,
                    'is_locked' => false,
                ];
                $current = $breakEnd;
            }
        }

        // Add 30m buffer time if room remains
        if ($bufferMinutes > 0 && $current->copy()->addMinutes($bufferMinutes)->lessThanOrEqualTo($end)) {
            $bufEnd = $current->copy()->addMinutes($bufferMinutes);
            $items[] = [
                'task_id' => null,
                'title' => 'وقت احتياطي للمهام الطارئة والمتابعات 🛡️',
                'start_time' => $current->format('H:i'),
                'end_time' => $bufEnd->format('H:i'),
                'duration_minutes' => $bufferMinutes,
                'is_break' => false,
                'is_buffer' => true,
                'is_locked' => false,
            ];
        }

        return [
            'summary' => 'خطة يومية واقعية ومتوازنة مبنية على أولويات مهامك المتاحة لليوم مع فترات راحة لتجنب الإرهاق.',
            'items' => $items,
            'unscheduled_tasks' => $unscheduled,
            'provider' => 'محرك التخطيط الذكي المحلي',
        ];
    }

    /**
     * Generate Gemini AI Weekly Review Analysis
     */
    public function generateWeeklyAnalysis(User $user, array $stats, array $answers, Carbon $start, Carbon $end): array
    {
        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.5-flash');

        $startStr = $start->locale('ar')->isoFormat('D MMMM YYYY');
        $endStr = $end->locale('ar')->isoFormat('D MMMM YYYY');

        $completedTitles = !empty($stats['completed_tasks_titles']) ? implode('، ', $stats['completed_tasks_titles']) : 'لا توجد مهام مكتملة';
        $pendingTitles = !empty($stats['pending_tasks_titles']) ? implode('، ', $stats['pending_tasks_titles']) : 'لا توجد مهام معلقة';

        $answersStr = "";
        if (!empty($answers)) {
            $answersStr = "إجابات وتأملات المستخدم لهذا الأسبوع:\n" .
                "- أهم إنجاز: " . ($answers['biggest_achievement'] ?? 'لم يذكر') . "\n" .
                "- أكبر عائق: " . ($answers['biggest_obstacle'] ?? 'لم يذكر') . "\n" .
                "- أسباب التأجيل: " . ($answers['delay_reasons'] ?? 'لم يذكر') . "\n" .
                "- ما يريد تحسينه: " . ($answers['improvement_goals'] ?? 'لم يذكر') . "\n" .
                "- أهم 3 نتائج مستهدفة: " . ($answers['top_3_outcomes'] ?? 'لم يذكر') . "\n";
        }

        $prompt = <<<EOT
أنت مستشار الانضباط والإنتاجية الشخصية «MO AI».
قم بإجراء مراجعة أسبوعية ذكية وبناءة للأسبوع من {$startStr} إلى {$endStr} للمستخدم ({$user->name}).

بيانات الأسبوع الفعلية من النظام (اعتمد عليها بدقة ولا تخترع أرقاماً):
- المهام المكتملة: {$stats['completed_count']} مهام (أهمها: {$completedTitles})
- المهام المعلقة والمتبقية: {$stats['pending_count']} مهام (أهمها: {$pendingTitles})
- المهام المتأخرة: {$stats['overdue_count']}
- نسبة إنجاز المهام: {$stats['completion_rate']}%
- إجمالي وقت التركيز المسجل: {$stats['focus_minutes']} دقيقة عبر {$stats['focus_sessions_count']} جلسة تركيز.
- نسبة الالتزام بالعادات: {$stats['habits_rate']}%

{$answersStr}

المطلوب تقديم تحليل موجز، محفز، وعملي باللغة العربية مقسم إلى:
1. 🌟 **ملخص إنجازات الأسبوع:** إبراز ما تم إنجازه بنبرة إيجابية وداعمة.
2. 🔍 **ملاحظات حول المهام المعلقة:** تحليل أسباب التأخير إن وجدت دون إصدار أحكام سلبية، مع التركيز على الحلول.
3. 💡 **تحسينات عملية مقترحة:** 2 إلى 3 نصائح واقعية للأسبوع القادم.
4. 🎯 **أهم 3 أولويات للأسبوع المقبل:** خطوات واضحة ومحددة للبدء بها.

قدم التقرير بأسلوب راقٍ ومباشر وداعم.
EOT;

        if (!empty($geminiKey)) {
            $analysis = $this->callGemini($geminiKey, $model, $prompt);
            if (!empty($analysis)) {
                return [
                    'analysis' => $analysis,
                    'provider' => "Google Gemini ({$model})",
                ];
            }
        }

        // Fallback analysis
        $fallbackAnalysis = "### 🌟 ملخص إنجازات الأسبوع\n" .
            "أحسنت العمل خلال هذا الأسبوع بإنجاز {$stats['completed_count']} مهمة بنسبة إنجاز بلغت {$stats['completion_rate']}%. الاستمرارية هي جوهر بناء العادات.\n\n" .
            "### 🔍 ملاحظات حول المهام المعلقة\n" .
            "لديك {$stats['pending_count']} مهمة متبقية (منها {$stats['overdue_count']} مهام متأخرة). تقسيم هذه المهام إلى خطوات صغيرة مدتها 20 دقيقة سيساعدك على التغلب على عائق البدء.\n\n" .
            "### 💡 تحسينات عملية مقترحة للأسبوع القادم\n" .
            "• ركّز على مهمة واحدة عالية الأولوية كل صباح قبل الانشغال بالمهام الصغيرة.\n" .
            "• خصص وقتاً احتياطياً يومياً للطوارئ حتى لا تتأخر بقية المهام.\n\n" .
            "### 🎯 أهم 3 أولويات للأسبوع المقبل\n" .
            "1. إنهاء المهام المتأخرة ذات الأولوية العالية.\n" .
            "2. الالتزام بالعادات اليومية الصباحية.\n" .
            "3. مراجعة الخطة اليومية كل صباح لمدة 5 دقائق.";

        return [
            'analysis' => $fallbackAnalysis,
            'provider' => 'محرك التحليل الذكي المحلي',
        ];
    }

    /**
     * Suggest next week's plan draft
     */
    public function generateNextWeekPlanProposal(User $user, Carbon $start, Carbon $end): array
    {
        $tasks = $user->tasks()
            ->where('status', 'pending')
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->take(8)
            ->get();

        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $plan = [];

        foreach ($days as $idx => $dayName) {
            $dayTask = $tasks->get($idx);
            $plan[] = [
                'day' => $dayName,
                'main_focus' => $dayTask ? $dayTask->title : 'متابعة المهام واستكمال العادات اليومية',
                'task_id' => $dayTask?->id,
                'priority' => $dayTask?->priority ?? 'medium',
            ];
        }

        return [
            'plan' => $plan,
            'summary' => 'خطة أسبوعية مقترحة لتوزيع المهام على مدار أيام العمل بتوازن.',
            'provider' => 'MO Life OS Planner',
        ];
    }

    /**
     * Generate structured sub-skills breakdown for a skill using AI
     */
    public function generateSkillBreakdown(Skill $skill): array
    {
        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash');

        $prompt = <<<EOT
أنت خبير تعليمي ومستشار تدريب محترف ومتخصص في منهجية التفكيك المهاري السريع (Skill Deconstruction).
المطلوب:
تقسيم المهارة الرئيسية التالية إلى 5 إلى 8 مهارات فرعية متدرجة منطقياً من الأساسيات حتى الإتقان المتقدم، مع مقترحات لتطبيقات ومشاريع عملية ومصادر تعليمية.

معلومات المهارة:
- اسم المهارة الرئيسية: «{$skill->title}»
- الوصف: «{$skill->description}»
- الهدف المنشود من تعلمها: «{$skill->goal}»

أجب بصيغة JSON خالصة فقط (بدون أي علامات أو نصوص إضافية قبل أو بعد الكود)، وتكون مصفوفة العناصر بالشكل التالي:
```json
[
  {
    "title": "اسم المهارة الفرعية (مثال: كتابة السيناريو والهيكل)",
    "description": "شرح موجز لأهمية هذه المهارة الفرعية وما تشمله",
    "practical_tasks": "تطبيق عملي: كتابة سيناريو متكامل لفيديو قصير مدته دقيقة واحدة",
    "resources": "دورة يوتيوب في أساسيات السيناريو - مقالات وقوالب مجانية"
  }
]
```
EOT;

        if (!empty($geminiKey)) {
            $raw = $this->callGemini($geminiKey, $model, $prompt);
            if (!empty($raw)) {
                if (preg_match('/```json\s*(.*?)\s*```/s', $raw, $matches)) {
                    $jsonText = $matches[1];
                } else {
                    $jsonText = $raw;
                }

                $items = json_decode(trim($jsonText), true);
                if (is_array($items) && !empty($items)) {
                    return [
                        'sub_skills' => $items,
                        'provider' => "Google Gemini ({$model})",
                    ];
                }
            }
        }

        // Smart Heuristic Fallback Breakdown if AI offline
        $title = $skill->title;
        $fallbackItems = [
            [
                'title' => "أساسيات ومفاهيم {$title}",
                'description' => "فهم المصطلحات الأساسية، خريطة المجال، والأدوات الضرورية للبدء.",
                'practical_tasks' => "تلخيص أهم 5 مفاهيم أساسية وتجهيز بيئة العمل.",
                'resources' => "فيديوهات تعريفية ومقالات تمهيدية في المجال.",
            ],
            [
                'title' => "التطبيقات والتمارين الأولية",
                'description' => "ممارسة المهارة من خلال تدريبات صغيرة ومباشرة بدون تعقيد.",
                'practical_tasks' => "تنفيذ أول تدريب عملي مصغر وتسجيل الملاحظات.",
                'resources' => "أدلة تدريبية ودروس تطبيقية خطوة بخطوة.",
            ],
            [
                'title' => "التقنيات المتوسطة وحل المشكلات",
                'description' => "التعمق في تفاصيل المهارة وتجاوز العقبات والأخطاء الشائعة.",
                'practical_tasks' => "مراجعة عمل سابق وتصحيح العيوب وإعادة تنفيذه بجودة أعلى.",
                'resources' => "دورات متوسطة ونماذج احترافية للمقارنة والتعلم.",
            ],
            [
                'title' => "إنجاز مشاريع متكاملة",
                'description' => "بناء مشروع كامل من البداية حتى النهاية يجمع كل ما تم تعلمه.",
                'practical_tasks' => "تسليم مشروع واقعي ونشره أو عرضه للحصول على تغذية راجعة.",
                'resources' => "دراسات حالة وأمثلة لمشاريع حقيقية ناجحة.",
            ],
            [
                'title' => "الإتقان المتقدم والسرعة",
                'description' => "تحسين جودة المخرجات، زيادة الكفاءة، وابتكار أسلوب شخصي مميز.",
                'practical_tasks' => "تكرار إنجاز المهام في نصف الوقت مع الحفاظ على أعلى جودة.",
                'resources' => "نصائح المحترفين، مراجع متقدمة، وأفضل الممارسات المهنية.",
            ],
        ];

        return [
            'sub_skills' => $fallbackItems,
            'provider' => 'محرك التفكيك المهاري الذكي MO Life OS',
        ];
    }

    /**
     * Generate customized roadmap and actionable guidance for a skill
     */
    public function generateSkillRoadmap(Skill $skill): array
    {
        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash');

        $subSkills = $skill->subSkills;
        $subSkillsList = "";
        foreach ($subSkills as $idx => $ss) {
            $status = $ss->statusLabel();
            $subSkillsList .= ($idx + 1) . ". {$ss->title} [الحالة: {$status} - الإتقان: {$ss->mastery_level}%]\n";
        }

        $progress = $skill->progressPercentage();
        $targetStr = $skill->target_date ? $skill->target_date->format('Y-m-d') : 'غير محدد';

        $prompt = <<<EOT
أنت مستشار التطوير المهني وخبير التعليم «MO AI».
المطلوب تقديم خطة تعلم تدريجية شاملة، تقييم لمستوى التقدم، واقتراح الخطوات العملية القادمة للمستخدم لإتقان مهارة: «{$skill->title}».

بيانات المهارة الحالية:
- المهارة الرئيسية: «{$skill->title}»
- الوصف: «{$skill->description}»
- الهدف المنشود: «{$skill->goal}»
- نسبة الإتقان الحالية الكلية: {$progress}%
- الموعد المستهدف: {$targetStr}

المهارات الفرعية الحالية:
{$subSkillsList}

المطلوب تقديم تحليل وإرشاد عملي بأسلوب Markdown غني باللغة العربية يشمل:
1. 🎯 **تقييم مستوى التقدم الحالي:** قراءة موضوعية للوضع الحالي وما تم إنجازه.
2. 🚀 **الخطوة العملية القادمة فوراً (Next Action):** المهارة المحددة التي يجب التركيز عليها الآن وكيف يبدأ فيها اليوم.
3. 🗺️ **خطة التعلم والمسار المقترح:** خطوات مرحلية واضحة (مرحلة التأسيس، مرحلة الممارسة، مرحلة الإتقان).
4. 💡 **أفكار مشاريع وتطبيقات عملية:** 2 إلى 3 مشاريع واقعية تثبت إتقان المهارة.
5. 📚 **مصادر مقترحة:** دورات، كتب، قنوات يوتيوب، أو أدوات موصى بها.

اكتب الإجابة بأسلوب محفز، عملي، ومباشر.
EOT;

        if (!empty($geminiKey)) {
            $raw = $this->callGemini($geminiKey, $model, $prompt);
            if (!empty($raw)) {
                return [
                    'roadmap' => $raw,
                    'provider' => "Google Gemini ({$model})",
                ];
            }
        }

        $nextSkill = $skill->nextRecommendedSubSkill();
        $nextSkillTitle = $nextSkill ? $nextSkill->title : 'المهارة القادمة';

        $fallbackRoadmap = "### 🎯 تقييم التقدم الحالي\n" .
            "لقد حققت نسبة إتقان تبلغ **{$progress}%** في مهارة «{$skill->title}». الاستمرارية والممارسة اليومية هما مفتاح الوصول إلى الاحترافية.\n\n" .
            "### 🚀 الخطوة العملية القادمة فوراً\n" .
            "التركيز الموصى به الآن هو على مهارة: **«{$nextSkillTitle}»**.\n" .
            "- خصص من 30 إلى 45 دقيقة يومياً للتطبيق والتدريب العملي وليس المشاهدة النظرية فقط.\n" .
            "- دوّن ملاحظاتك وإنجازك اليومي أولاً بأول في سجل المهارة.\n\n" .
            "### 🗺️ مسار التعلم المقترح\n" .
            "1. **مرحلة التأسيس (20%):** استيعاب المبادئ والمصطلحات الأساسية.\n" .
            "2. **مرحلة الممارسة والتكرار (60%):** إنجاز التطبيقات والتمارين في كل مهارة فرعية.\n" .
            "3. **مرحلة الإتقان والإنتاج (100%):** تنفيذ مشروع حقيقي كامل يجمع كافة المهارات الفرعية ونشره.\n\n" .
            "### 💡 نصائح ذهبية للإتقان السريع\n" .
            "- قاعدة الـ 20 ساعة الأولى: التركيز المكثف لمدة 20 ساعة يعطيك كفاءة عالية في أي مهارة جديدة.\n" .
            "- تخلص من المشتتات أثناء وقت التعلم واجعل التدريب عادة يومية ثابتة.";

        return [
            'roadmap' => $fallbackRoadmap,
            'provider' => 'محرك MO Life OS الإرشادي',
        ];
    }
}
