@extends('layouts.app')

@section('title', 'المساعد الذكي — MO AI Assistant')

@section('content')
<div class="space-y-4 max-w-5xl mx-auto">

    <!-- Top Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-brand-500/20 text-xl shrink-0">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        المساعد الذكي <span class="text-brand-600 dark:text-brand-400">MO AI</span>
                    </h1>
                    @if($hasApiKey)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                        AI سحابي متصل
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600" title="يمكنك وضع GEMINI_API_KEY في ملف .env لتفعيل الذكاء الاصطناعي التوليدي الكامل">
                        <i class="fa-solid fa-circle-info text-[10px]"></i>
                        المحرك المحلي نشط
                    </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    مركزك الموحد لإدارة المهام، البحث في الملاحظات، تسجيل المصروفات، والبحث في الويب.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- TTS Global Audio Toggle -->
            <button type="button" id="ttsToggleBtn" onclick="toggleAutoTts()" class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 flex items-center gap-1.5 transition hover:border-slate-300" title="تشغيل أو كتم قراءة الإجابات صوتياً">
                <i class="fa-solid fa-volume-xmark text-slate-400 text-xs" id="ttsToggleIcon"></i>
                <span id="ttsToggleLabel">الرد الصوتي: صامت</span>
            </button>

            <!-- Clear Chat Button -->
            <button type="button" onclick="clearChatHistory()" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700/60 rounded-xl transition flex items-center gap-1.5" title="مسح المحادثة">
                <i class="fa-regular fa-trash-can"></i>
                <span class="hidden sm:inline">مسح السجل</span>
            </button>
        </div>
    </div>

    <!-- Quick Action Pills (Buttons) -->
    <div class="flex flex-wrap items-center gap-2 pt-1">
        <button type="button" onclick="triggerQuickAction('distracted')" class="group flex items-center gap-2 px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-500/20 hover:scale-[1.02] transition">
            <i class="fa-solid fa-bolt text-teal-200 text-xs group-hover:rotate-12 transition-transform"></i>
            <span>أنا مشتت، ساعدني!</span>
        </button>

        <button type="button" onclick="triggerQuickAction('plan_day')" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-brand-500 text-slate-700 dark:text-slate-200 hover:text-brand-600 text-xs font-bold shadow-xs transition hover:scale-[1.02]">
            <i class="fa-solid fa-calendar-check text-brand-600 text-xs"></i>
            <span>رتّب يومي</span>
        </button>

        <button type="button" onclick="triggerKnowledgeSearchPrompt()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-brand-500 text-slate-700 dark:text-slate-200 hover:text-brand-600 text-xs font-bold shadow-xs transition hover:scale-[1.02]">
            <i class="fa-solid fa-book-bookmark text-brand-600 text-xs"></i>
            <span>ابحث في ملاحظاتي</span>
        </button>

        <button type="button" onclick="triggerFinancePrompt()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-brand-500 text-slate-700 dark:text-slate-200 hover:text-brand-600 text-xs font-bold shadow-xs transition hover:scale-[1.02]">
            <i class="fa-solid fa-wallet text-brand-600 text-xs"></i>
            <span>تسجيل مصروف</span>
        </button>

        <button type="button" onclick="openBreakdownModal()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-brand-500 text-slate-700 dark:text-slate-200 hover:text-brand-600 text-xs font-bold shadow-xs transition hover:scale-[1.02]">
            <i class="fa-solid fa-layer-group text-brand-600 text-xs"></i>
            <span>قسّم مهمة</span>
        </button>

        <button type="button" onclick="triggerWebSearchPrompt()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-brand-500 text-slate-700 dark:text-slate-200 hover:text-brand-600 text-xs font-bold shadow-xs transition hover:scale-[1.02]">
            <i class="fa-solid fa-globe text-brand-600 text-xs"></i>
            <span>ابحث على الويب</span>
        </button>
    </div>

    <!-- Chat Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col h-[65vh] min-h-[520px]">
        
        <!-- Messages Scroll Area -->
        <div id="chatMessages" class="flex-1 overflow-y-auto space-y-4 pr-1 pl-1">
            
            @if($messages->isEmpty())
            <!-- Welcome Empty State -->
            <div id="welcomeMessage" class="text-center py-10 space-y-3 max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-3xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">
                    أهلاً بك يا {{ Auth::user()->name }}! أنا MO AI
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    مركزك المساعد المتكامل لإدارة الوقت والمعلومات والمال.
                    يمكنك التحدث إليّ صوتياً أو كتابياً عبر الميكروفون أو الاختيار من الأزرار السريعة.
                </p>
                <div class="flex flex-wrap justify-center gap-2 pt-2">
                    <button type="button" onclick="sendCustomPrompt('شو مهامي وأولوياتي اليوم؟')" class="text-[11px] font-semibold px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-600 transition">
                        «شو مهامي وأولوياتي اليوم؟»
                    </button>
                    <button type="button" onclick="sendCustomPrompt('سجل مصروف 20 دولار للمواصلات اليوم')" class="text-[11px] font-semibold px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-600 transition">
                        «سجل مصروف 20 دولار للمواصلات»
                    </button>
                    <button type="button" onclick="sendCustomPrompt('ابحث في ملاحظاتي عن ميزانية')" class="text-[11px] font-semibold px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-600 transition">
                        «ابحث في ملاحظاتي عن ميزانية»
                    </button>
                    <button type="button" onclick="sendCustomPrompt('ابحث لي عن أفضل دورة Laravel للمبتدئين')" class="text-[11px] font-semibold px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-600 transition">
                        «ابحث لي عن أفضل دورة Laravel»
                    </button>
                </div>
            </div>
            @endif

            <!-- Loop Existing Messages -->
            @foreach($messages as $msg)
                @if($msg->role === 'user')
                <!-- User Message Bubble -->
                <div class="flex items-start justify-end gap-2.5">
                    <div class="max-w-[85%] sm:max-w-[70%] bg-brand-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-xs sm:text-sm font-medium shadow-xs leading-relaxed whitespace-pre-line">
                        {{ $msg->content }}
                    </div>
                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
                @else
                <!-- Assistant Message Bubble -->
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-xs">
                        <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i>
                    </div>
                    <div class="max-w-[90%] sm:max-w-[80%] space-y-2">
                        <div class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl rounded-tl-sm px-4 py-3 text-xs sm:text-sm leading-relaxed whitespace-pre-line shadow-2xs relative group">
                            @php
                                $formattedMsg = e($msg->content);
                                $formattedMsg = preg_replace('/\*\*(.*?)\*\*/s', '<strong class="font-bold text-slate-900 dark:text-white">$1</strong>', $formattedMsg);
                                $formattedMsg = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em class="italic">$1</em>', $formattedMsg);
                            @endphp
                            <span class="message-text">{!! nl2br($formattedMsg) !!}</span>
                            
                            <!-- TTS Speaker Button -->
                            <button type="button" onclick="speakMessageBubble(this)" class="mt-2 text-slate-400 hover:text-brand-600 text-xs flex items-center gap-1 font-semibold transition" title="استمع إلى الرد صوتياً">
                                <i class="fa-solid fa-volume-high text-[11px]"></i>
                                <span class="text-[10px]">استمع</span>
                            </button>
                        </div>

                        <!-- 1. Distracted Task Action Card -->
                        @if($msg->action_type === 'distracted_task' && !empty($msg->action_data['task_id']))
                        <div class="p-3.5 rounded-2xl bg-brand-50 dark:bg-brand-950/40 border-2 border-brand-400 dark:border-brand-700 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-brand-800 dark:text-brand-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-bullseye text-brand-600"></i>
                                    المهمة المختارة للبدء فوراً:
                                </span>
                                <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-md bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300">
                                    ⏱️ {{ $msg->action_data['duration_minutes'] ?? 20 }} دقيقة
                                </span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ $msg->action_data['task_title'] ?? 'مهمتك المستهدفة' }}
                            </h4>
                            @if(!empty($msg->action_data['first_step']))
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                <strong>الخطوة الأولى:</strong> {{ $msg->action_data['first_step'] }}
                            </p>
                            @endif
                            <div class="pt-1">
                                <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition">
                                    <i class="fa-solid fa-play text-[10px]"></i>
                                    <span>ابدأ المهمة الآن</span>
                                </a>
                            </div>
                        </div>

                        <!-- 2. Subtasks Breakdown Action Card -->
                        @elseif($msg->action_type === 'subtask_breakdown' && !empty($msg->action_data['subtasks']))
                        <div class="p-3.5 rounded-2xl bg-brand-50/50 dark:bg-brand-950/40 border-2 border-brand-300 dark:border-brand-800 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-brand-900 dark:text-brand-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-list-check text-brand-600"></i>
                                    المهام الفرعية المقترحة:
                                </span>
                                <span class="text-[11px] text-brand-600 dark:text-brand-300 font-semibold">
                                    {{ count($msg->action_data['subtasks']) }} خطوات
                                </span>
                            </div>
                            <div class="space-y-1">
                                @foreach($msg->action_data['subtasks'] as $st)
                                <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 px-2.5 py-1.5 rounded-lg border border-brand-100 dark:border-brand-900">
                                    <i class="fa-regular fa-square-check text-brand-600"></i>
                                    <span>{{ $st }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="pt-1">
                                @if($msg->is_action_executed)
                                <button disabled class="px-3.5 py-1.5 rounded-xl bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 font-bold text-xs flex items-center gap-1.5 cursor-default">
                                    <i class="fa-solid fa-check"></i>
                                    <span>تمت إضافة المهام الفرعية للمهمة ✓</span>
                                </button>
                                @else
                                <button type="button" onclick="applyAddSubtasks({{ $msg->id }}, {{ $msg->action_data['task_id'] ?? 0 }}, {{ json_encode($msg->action_data['subtasks']) }}, this)" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 hover:scale-[1.01]">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    <span>إضافة إلى مهامي (موافقة)</span>
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- 3. Web Search Action Card -->
                        @elseif($msg->action_type === 'web_search' && !empty($msg->action_data['results']))
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-globe text-brand-600"></i>
                                    <span>مصادر البحث على الويب:</span>
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    مصادر خارجية
                                </span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($msg->action_data['results'] as $res)
                                <a href="{{ $res['url'] }}" target="_blank" rel="noopener noreferrer" class="block p-2.5 rounded-xl bg-white dark:bg-slate-900 hover:border-brand-400 border border-slate-200 dark:border-slate-800 text-xs transition group">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 truncate flex items-center gap-1.5">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400"></i>
                                        <span>{{ $res['title'] }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">
                                        {{ $res['snippet'] }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1 text-[10px]">
                                        <span class="text-brand-600 dark:text-brand-400 font-mono font-medium">{{ $res['source'] }}</span>
                                        <span class="text-slate-400">رابط مباشر</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 4. Personal Knowledge Search Action Card -->
                        @elseif($msg->action_type === 'knowledge_search' && !empty($msg->action_data['results']))
                        <div class="p-3.5 rounded-2xl bg-teal-50/50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-teal-800 dark:text-teal-200">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-book-bookmark text-teal-600"></i>
                                    <span>نتائج البحث في ملاحظاتك وبياناتك الشخصية:</span>
                                </span>
                                <span class="text-[11px] text-teal-600 dark:text-teal-400 font-semibold">
                                    {{ count($msg->action_data['results']) }} نتائج
                                </span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($msg->action_data['results'] as $res)
                                <a href="{{ $res['url'] }}" class="block p-2.5 rounded-xl bg-white dark:bg-slate-900 hover:border-teal-500 border border-slate-200 dark:border-slate-800 text-xs transition group">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 flex items-center gap-1.5 truncate">
                                            <i class="fa-solid {{ $res['icon'] ?? 'fa-circle-dot' }} text-[10px] text-teal-600"></i>
                                            <span>{{ $res['title'] }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                                            {{ $res['type_label'] }}
                                        </span>
                                    </div>
                                    @if(!empty($res['snippet']))
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                        {{ $res['snippet'] }}
                                    </p>
                                    @endif
                                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400">
                                        <span>{{ $res['date'] }}</span>
                                        <span class="text-teal-600 dark:text-teal-400 font-semibold flex items-center gap-0.5 group-hover:underline">
                                            <span>عرض السجل</span>
                                            <i class="fa-solid fa-arrow-left text-[9px]"></i>
                                        </span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 5. Confirm Financial Transaction Action Card -->
                        @elseif($msg->action_type === 'confirm_transaction' && !empty($msg->action_data['amount']))
                        <div class="p-4 rounded-2xl bg-teal-50/60 dark:bg-teal-950/40 border-2 border-teal-500/60 dark:border-teal-700 space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold text-teal-900 dark:text-teal-100">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-wallet text-teal-600 dark:text-teal-400"></i>
                                    <span>تأكيد تسجيل معاملة مالية:</span>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full {{ ($msg->action_data['type'] ?? 'expense') === 'income' ? 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200' : 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-200' }} font-bold text-[11px]">
                                    {{ ($msg->action_data['type'] ?? 'expense') === 'income' ? 'دخل وارد +' : 'مصروف صادر -' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs bg-white dark:bg-slate-900 p-3 rounded-xl border border-teal-100 dark:border-teal-900/60">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">المبلغ</span>
                                    <strong class="text-sm font-black text-slate-800 dark:text-slate-100">
                                        {{ number_format($msg->action_data['amount'], 2) }} {{ $msg->action_data['currency'] ?? 'USD' }}
                                    </strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">التصنيف</span>
                                    <strong class="font-bold text-slate-700 dark:text-slate-200">{{ $msg->action_data['category_name'] ?? 'أخرى' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">الوصف</span>
                                    <strong class="font-bold text-slate-700 dark:text-slate-200 truncate block">{{ $msg->action_data['description'] ?? 'معاملة' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">التاريخ</span>
                                    <strong class="font-bold text-slate-700 dark:text-slate-200">{{ $msg->action_data['date'] ?? date('Y-m-d') }}</strong>
                                </div>
                            </div>

                            <div class="pt-1">
                                @if($msg->is_action_executed)
                                    <button disabled class="px-3.5 py-1.5 rounded-xl bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 font-bold text-xs flex items-center gap-1.5 cursor-default">
                                        <i class="fa-solid fa-check"></i>
                                        <span>تم تأكيد وحفظ المعاملة في حسابك بنجاح ✓</span>
                                    </button>
                                @else
                                    <button type="button" onclick='confirmAiTransaction({{ $msg->id }}, {{ json_encode($msg->action_data) }}, this)' class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 hover:scale-[1.01]">
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>تأكيد وحفظ المعاملة في حسابي</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif
            @endforeach

            <!-- Typing Indicator (Hidden by default) -->
            <div id="typingIndicator" class="hidden flex items-start gap-2.5">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-wand-magic-sparkles text-[11px] animate-spin"></i>
                </div>
                <div class="bg-slate-100 dark:bg-slate-900 text-slate-500 rounded-2xl px-4 py-3 text-xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-bounce"></span>
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-bounce [animation-delay:0.4s]"></span>
                    <span class="text-[11px] font-semibold mr-1">MO AI يفكّر...</span>
                </div>
            </div>

        </div>

        <!-- Voice Live Recording Banner (Hidden by default) -->
        <div id="recordingIndicator" class="hidden items-center justify-between px-4 py-2.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs mb-3 shadow-xs">
            <div class="flex items-center gap-2.5 font-bold">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
                </span>
                <span>الميكروفون نشط: جارٍ الاستماع باللغة العربية... تكلّم بوضوح</span>
            </div>
            <button type="button" onclick="stopVoiceRecording()" class="px-3 py-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition">
                إيقاف التسجيل
            </button>
        </div>

        <!-- Chat Input Form -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-700/80">
            <form onsubmit="handleChatSubmit(event)" class="relative flex items-center gap-2">
                <input type="text" id="chatInput" placeholder="تحدث أو اكتب إلى MO AI... (مثال: سجل مصروف 20 دولار للمواصلات، أو ابحث في ملاحظاتي)" autocomplete="off" class="w-full pl-24 pr-4 py-3 text-xs sm:text-sm rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-brand-500">
                
                <div class="absolute left-2 flex items-center gap-1.5">
                    <!-- Voice Microphone Button -->
                    <button type="button" id="voiceMicBtn" onclick="toggleVoiceRecording()" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all" title="تحدث بالأوامر الصوتية (Speech-to-Text)">
                        <i class="fa-solid fa-microphone text-xs"></i>
                    </button>

                    <!-- Send Button -->
                    <button type="submit" id="sendBtn" class="w-9 h-9 rounded-xl bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-500/20 transition-all hover:scale-105" title="إرسال">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </div>
            </form>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2 px-1">
                <span>MO AI مرتبط بمهامك وملاحظاتك وسجلك المالي الحقيقي في Laravel.</span>
                <span class="hidden sm:inline">يدعم الأوامر الصوتية العربية</span>
            </div>
        </div>

    </div>

</div>

<!-- Modal: Select Task to Breakdown -->
<div id="breakdownTaskModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-brand-600"></i>
                اختر مهمة لتقسيمها إلى مهام فرعية
            </h3>
            <button type="button" onclick="closeBreakdownModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            سيقوم الذكاء الاصطناعي بتحليل المهمة واقتراح 4-6 مهام فرعية متسلسلة، ولن يتم حفظها إلا بعد موافقتك.
        </p>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اختر من مهامك الحالية المعلقة:</label>
            <select id="selectedTaskForBreakdown" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-brand-500">
                @forelse($pendingTasks as $pt)
                <option value="{{ $pt->id }}">{{ $pt->title }} ({{ $pt->priority === 'high' ? 'عالية 📌' : 'عادية' }})</option>
                @empty
                <option value="" disabled>لا توجد مهام معلقة حالياً</option>
                @endforelse
            </select>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
            <button type="button" onclick="closeBreakdownModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                إلغاء
            </button>
            <button type="button" onclick="confirmBreakdownSelectedTask()" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                تقسيم المهمة الآن
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const chatContainer = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const typingIndicator = document.getElementById('typingIndicator');
    const sendBtn = document.getElementById('sendBtn');
    const voiceMicBtn = document.getElementById('voiceMicBtn');
    const recordingIndicator = document.getElementById('recordingIndicator');

    // Auto-scroll chat to bottom
    function scrollToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    scrollToBottom();

    // Submit custom chat message
    function handleChatSubmit(e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        chatInput.value = '';
        sendChatMessage(text);
    }

    // Trigger quick action button
    function triggerQuickAction(mode) {
        sendChatMessage('', mode);
    }

    // Trigger prompt with preset text
    function sendCustomPrompt(text) {
        sendChatMessage(text);
    }

    // Open Web search prompt
    function triggerWebSearchPrompt() {
        const query = prompt('ما الذي ترغب في البحث عنه في الويب؟', 'أفضل كورس لتعلم Laravel');
        if (query && query.trim()) {
            sendChatMessage(`ابحث لي عن: ${query.trim()}`, 'web_search');
        }
    }

    // Open Knowledge Search prompt
    function triggerKnowledgeSearchPrompt() {
        const query = prompt('ما الذي تبحث عنه داخل ملاحظاتك ودفاترك وسجلاتك؟', 'ميزانية');
        if (query && query.trim()) {
            sendChatMessage(`ابحث في ملاحظاتي عن: ${query.trim()}`, 'knowledge_search');
        }
    }

    // Trigger Finance prompt
    function triggerFinancePrompt() {
        const query = prompt('سجل معاملتك المالية (مثال: سجل مصروف 25 دولار للمواصلات أو أضف دخل 400 دولار):', 'سجل مصروف 20 دولار للمواصلات');
        if (query && query.trim()) {
            sendChatMessage(query.trim());
        }
    }

    // Open Breakdown modal
    function openBreakdownModal() {
        document.getElementById('breakdownTaskModal').classList.remove('hidden');
    }
    function closeBreakdownModal() {
        document.getElementById('breakdownTaskModal').classList.add('hidden');
    }

    // Confirm Breakdown
    function confirmBreakdownSelectedTask() {
        const select = document.getElementById('selectedTaskForBreakdown');
        const taskId = select.value;
        const taskText = select.options[select.selectedIndex]?.text || '';
        closeBreakdownModal();

        if (taskId) {
            sendChatMessage(`قسّم لي مهمة: «${taskText}» إلى مهام فرعية صغيرة`, 'breakdown_task', taskId);
        }
    }

    // Main Send Chat Message Function
    function sendChatMessage(message, mode = null, taskId = null) {
        // Hide welcome if shown
        const welcome = document.getElementById('welcomeMessage');
        if (welcome) welcome.style.display = 'none';

        // Render user message bubble
        const displayText = message || (mode === 'distracted' ? 'أنا مشتت، ساعدني!' : (mode === 'plan_day' ? 'رتّب لي يومي' : 'مساعدة'));
        appendUserBubble(displayText);

        // Show typing indicator
        typingIndicator.classList.remove('hidden');
        scrollToBottom();
        sendBtn.disabled = true;

        fetch('{{ route("ai.chat") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                mode: mode,
                task_id: taskId
            })
        })
        .then(r => r.json())
        .then(data => {
            typingIndicator.classList.add('hidden');
            sendBtn.disabled = false;

            if (data.success && data.assistant_message) {
                appendAssistantBubble(data.assistant_message);
                if (isAutoTtsEnabled) {
                    speakText(data.assistant_message.content);
                }
            } else {
                showToast('حدث خطأ أثناء معالجة الرد، يرجى المحاولة ثانية', 'error');
            }
            scrollToBottom();
        })
        .catch(err => {
            typingIndicator.classList.add('hidden');
            sendBtn.disabled = false;
            showToast('تعذر الاتصال بالمساعد الذكي', 'error');
            scrollToBottom();
        });
    }

    // Append User Bubble to DOM
    function appendUserBubble(text) {
        const bubble = document.createElement('div');
        bubble.className = 'flex items-start justify-end gap-2.5';
        bubble.innerHTML = `
            <div class="max-w-[85%] sm:max-w-[70%] bg-brand-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-xs sm:text-sm font-medium shadow-xs leading-relaxed whitespace-pre-line">
                ${escapeHtml(text)}
            </div>
            <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                {{ mb_substr(Auth::user()->name, 0, 1) }}
            </div>
        `;
        chatContainer.insertBefore(bubble, typingIndicator);
    }

    // Append Assistant Bubble to DOM
    function appendAssistantBubble(msg) {
        const bubble = document.createElement('div');
        bubble.className = 'flex items-start gap-2.5';

        let actionCardHtml = '';

        if (msg.action_type === 'distracted_task' && msg.action_data) {
            actionCardHtml = `
                <div class="p-3.5 rounded-2xl bg-brand-50 dark:bg-brand-950/40 border-2 border-brand-400 dark:border-brand-700 space-y-2 mt-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-brand-800 dark:text-brand-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-bullseye text-brand-600"></i>
                            المهمة المختارة للبدء فوراً:
                        </span>
                        <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-md bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300">
                            ⏱️ ${msg.action_data.duration_minutes || 20} دقيقة
                        </span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                        ${escapeHtml(msg.action_data.task_title || 'مهمتك المستهدفة')}
                    </h4>
                    ${msg.action_data.first_step ? `<p class="text-xs text-slate-600 dark:text-slate-300"><strong>الخطوة الأولى:</strong> ${escapeHtml(msg.action_data.first_step)}</p>` : ''}
                    <div class="pt-1">
                        <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition">
                            <i class="fa-solid fa-play text-[10px]"></i>
                            <span>ابدأ المهمة الآن</span>
                        </a>
                    </div>
                </div>
            `;
        } else if (msg.action_type === 'subtask_breakdown' && msg.action_data && msg.action_data.subtasks) {
            const subtasksList = msg.action_data.subtasks.map(st => `
                <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 px-2.5 py-1.5 rounded-lg border border-brand-100 dark:border-brand-900">
                    <i class="fa-regular fa-square-check text-brand-600"></i>
                    <span>${escapeHtml(st)}</span>
                </div>
            `).join('');

            actionCardHtml = `
                <div class="p-3.5 rounded-2xl bg-brand-50/50 dark:bg-brand-950/40 border-2 border-brand-300 dark:border-brand-800 space-y-2.5 mt-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-brand-900 dark:text-brand-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-list-check text-brand-600"></i>
                            المهام الفرعية المقترحة:
                        </span>
                        <span class="text-[11px] text-brand-600 dark:text-brand-300 font-semibold">
                            ${msg.action_data.subtasks.length} خطوات
                        </span>
                    </div>
                    <div class="space-y-1">
                        ${subtasksList}
                    </div>
                    <div class="pt-1">
                        <button type="button" onclick='applyAddSubtasks(${msg.id}, ${msg.action_data.task_id || 0}, ${JSON.stringify(msg.action_data.subtasks)}, this)' class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 hover:scale-[1.01]">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>إضافة إلى مهامي (موافقة)</span>
                        </button>
                    </div>
                </div>
            `;
        } else if (msg.action_type === 'web_search' && msg.action_data && msg.action_data.results) {
            const resultsHtml = msg.action_data.results.map(r => `
                <a href="${escapeHtml(r.url)}" target="_blank" rel="noopener noreferrer" class="block p-2.5 rounded-xl bg-white dark:bg-slate-900 hover:border-brand-400 border border-slate-200 dark:border-slate-800 text-xs transition group">
                    <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 truncate flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400"></i>
                        ${escapeHtml(r.title)}
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">
                        ${escapeHtml(r.snippet)}
                    </p>
                    <div class="flex items-center justify-between mt-1 text-[10px]">
                        <span class="text-brand-600 dark:text-brand-400 font-mono font-medium">${escapeHtml(r.source)}</span>
                        <span class="text-slate-400">رابط مباشر</span>
                    </div>
                </a>
            `).join('');

            actionCardHtml = `
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2 mt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-globe text-brand-600"></i>
                            <span>مصادر البحث على الويب:</span>
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            مصادر خارجية
                        </span>
                    </div>
                    <div class="space-y-1.5">
                        ${resultsHtml}
                    </div>
                </div>
            `;
        } else if (msg.action_type === 'knowledge_search' && msg.action_data && msg.action_data.results) {
            const resultsHtml = msg.action_data.results.map(r => `
                <a href="${escapeHtml(r.url)}" class="block p-2.5 rounded-xl bg-white dark:bg-slate-900 hover:border-teal-500 border border-slate-200 dark:border-slate-800 text-xs transition group">
                    <div class="flex items-center justify-between gap-2">
                        <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 flex items-center gap-1.5 truncate">
                            <i class="fa-solid ${escapeHtml(r.icon || 'fa-circle-dot')} text-[10px] text-teal-600"></i>
                            <span>${escapeHtml(r.title)}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                            ${escapeHtml(r.type_label)}
                        </span>
                    </div>
                    ${r.snippet ? `<p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">${escapeHtml(r.snippet)}</p>` : ''}
                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400">
                        <span>${escapeHtml(r.date)}</span>
                        <span class="text-teal-600 dark:text-teal-400 font-semibold flex items-center gap-0.5 group-hover:underline">
                            <span>عرض السجل</span>
                            <i class="fa-solid fa-arrow-left text-[9px]"></i>
                        </span>
                    </div>
                </a>
            `).join('');

            actionCardHtml = `
                <div class="p-3.5 rounded-2xl bg-teal-50/50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 space-y-2 mt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-teal-800 dark:text-teal-200">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-book-bookmark text-teal-600"></i>
                            <span>نتائج البحث في ملاحظاتك وبياناتك الشخصية:</span>
                        </span>
                        <span class="text-[11px] text-teal-600 dark:text-teal-400 font-semibold">
                            ${msg.action_data.results.length} نتائج
                        </span>
                    </div>
                    <div class="space-y-1.5">
                        ${resultsHtml}
                    </div>
                </div>
            `;
        } else if (msg.action_type === 'confirm_transaction' && msg.action_data && msg.action_data.amount) {
            actionCardHtml = `
                <div class="p-4 rounded-2xl bg-teal-50/60 dark:bg-teal-950/40 border-2 border-teal-500/60 dark:border-teal-700 space-y-3 mt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-teal-900 dark:text-teal-100">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-wallet text-teal-600 dark:text-teal-400"></i>
                            <span>تأكيد تسجيل معاملة مالية:</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full ${msg.action_data.type === 'income' ? 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200' : 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-200'} font-bold text-[11px]">
                            ${msg.action_data.type === 'income' ? 'دخل وارد +' : 'مصروف صادر -'}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs bg-white dark:bg-slate-900 p-3 rounded-xl border border-teal-100 dark:border-teal-900/60">
                        <div>
                            <span class="text-[10px] text-slate-400 block">المبلغ</span>
                            <strong class="text-sm font-black text-slate-800 dark:text-slate-100">
                                ${parseFloat(msg.action_data.amount).toFixed(2)} ${escapeHtml(msg.action_data.currency || 'USD')}
                            </strong>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">التصنيف</span>
                            <strong class="font-bold text-slate-700 dark:text-slate-200">${escapeHtml(msg.action_data.category_name || 'أخرى')}</strong>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">الوصف</span>
                            <strong class="font-bold text-slate-700 dark:text-slate-200 truncate block">${escapeHtml(msg.action_data.description || 'معاملة')}</strong>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">التاريخ</span>
                            <strong class="font-bold text-slate-700 dark:text-slate-200">${escapeHtml(msg.action_data.date || '')}</strong>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="button" onclick='confirmAiTransaction(${msg.id}, ${JSON.stringify(msg.action_data)}, this)' class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 hover:scale-[1.01]">
                            <i class="fa-solid fa-check text-[10px]"></i>
                            <span>تأكيد وحفظ المعاملة في حسابي</span>
                        </button>
                    </div>
                </div>
            `;
        }

        bubble.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-xs">
                <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i>
            </div>
            <div class="max-w-[90%] sm:max-w-[80%] space-y-2">
                <div class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl rounded-tl-sm px-4 py-3 text-xs sm:text-sm leading-relaxed whitespace-pre-line shadow-2xs relative">
                    <span class="message-text">${formatMarkdownMessage(msg.content)}</span>
                    <button type="button" onclick="speakMessageBubble(this)" class="mt-2 text-slate-400 hover:text-brand-600 text-xs flex items-center gap-1 font-semibold transition" title="استمع إلى الرد صوتياً">
                        <i class="fa-solid fa-volume-high text-[11px]"></i>
                        <span class="text-[10px]">استمع</span>
                    </button>
                </div>
                ${actionCardHtml}
            </div>
        `;

        chatContainer.insertBefore(bubble, typingIndicator);
    }

    // Confirm and Save Financial Transaction proposed by AI
    function confirmAiTransaction(msgId, data, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> جارٍ الحفظ...';

        fetch('{{ route("ai.confirm-transaction") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message_id: msgId,
                type: data.type,
                amount: data.amount,
                currency: data.currency,
                category_id: data.category_id,
                description: data.description,
                date: data.date
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                btn.className = 'px-3.5 py-1.5 rounded-xl bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 font-bold text-xs flex items-center gap-1.5 cursor-default';
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>تم تأكيد وحفظ المعاملة في حسابك بنجاح ✓</span>';
                showToast(res.message, 'success');
            } else {
                btn.disabled = false;
                btn.textContent = 'تأكيد وحفظ المعاملة';
                showToast(res.message || 'حدث خطأ أثناء حفظ المعاملة', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'تأكيد وحفظ المعاملة';
            showToast('تعذر الاتصال بالخادم', 'error');
        });
    }

    // Apply adding suggested subtasks after approval
    function applyAddSubtasks(msgId, taskId, subtasks, btn) {
        if (!taskId || !subtasks || subtasks.length === 0) {
            showToast('لا توجد مهمة محددة لإضافة المهام الفرعية إليها', 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> جاري الإضافة...';

        fetch('{{ route("ai.apply-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message_id: msgId,
                action: 'add_subtasks',
                task_id: taskId,
                subtasks: subtasks
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.className = 'px-3.5 py-1.5 rounded-xl bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 font-bold text-xs flex items-center gap-1.5 cursor-default';
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>تمت إضافة المهام الفرعية للمهمة بنجاح ✓</span>';
                showToast(data.message, 'success');
            } else {
                btn.disabled = false;
                btn.textContent = 'إضافة إلى مهامي (موافقة)';
                showToast('حدث خطأ أثناء إضافة المهام الفرعية', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'إضافة إلى مهامي (موافقة)';
            showToast('تعذر الاتصال بالخادم', 'error');
        });
    }

    // Clear Chat History
    function clearChatHistory() {
        if (!confirm('هل تريد مسح سجل المحادثة مع المساعد الذكي؟')) return;

        fetch('{{ route("ai.clear-history") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    // ==========================================
    // VOICE SPEECH-TO-TEXT (STT) IMPLEMENTATION
    // ==========================================
    let recognition = null;
    let isRecording = false;

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRec();
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.lang = 'ar-SA'; // Native Arabic recognition

        recognition.onstart = function() {
            isRecording = true;
            recordingIndicator.classList.remove('hidden');
            recordingIndicator.classList.add('flex');
            voiceMicBtn.classList.add('bg-rose-100', 'text-rose-600', 'animate-pulse');
        };

        recognition.onresult = function(event) {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }
            chatInput.value = transcript;
        };

        recognition.onerror = function(event) {
            console.warn('Speech recognition error:', event.error);
            stopVoiceRecording();
            if (event.error === 'not-allowed') {
                showToast('يرجى السماح بالوصول إلى الميكروفون من إعدادات المتصفح', 'error');
            }
        };

        recognition.onend = function() {
            stopVoiceRecording();
        };
    }

    function toggleVoiceRecording() {
        if (!recognition) {
            alert('خاصية التعرف الصوتي غير مدعومة في متصفحك الحالي. يمكنك كتابة الأوامر مباشرة في مربع النص.');
            return;
        }

        if (isRecording) {
            recognition.stop();
        } else {
            try {
                recognition.start();
            } catch (e) {
                console.error(e);
            }
        }
    }

    function stopVoiceRecording() {
        isRecording = false;
        recordingIndicator.classList.add('hidden');
        recordingIndicator.classList.remove('flex');
        voiceMicBtn.classList.remove('bg-rose-100', 'text-rose-600', 'animate-pulse');
    }

    // ==========================================
    // VOICE TEXT-TO-SPEECH (TTS) IMPLEMENTATION
    // ==========================================
    let isAutoTtsEnabled = false;

    function toggleAutoTts() {
        isAutoTtsEnabled = !isAutoTtsEnabled;
        const icon = document.getElementById('ttsToggleIcon');
        const label = document.getElementById('ttsToggleLabel');

        if (isAutoTtsEnabled) {
            icon.className = 'fa-solid fa-volume-high text-teal-600 dark:text-teal-400 text-xs';
            label.textContent = 'الرد الصوتي: مفعّل';
            showToast('تم تفعيل القراءة الصوتية التلقائية للإجابات', 'info');
        } else {
            icon.className = 'fa-solid fa-volume-xmark text-slate-400 text-xs';
            label.textContent = 'الرد الصوتي: صامت';
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            showToast('تم كتم الردود الصوتية', 'info');
        }
    }

    function speakMessageBubble(btn) {
        const bubble = btn.closest('.space-y-2');
        const textSpan = bubble.querySelector('.message-text');
        if (textSpan) {
            speakText(textSpan.innerText);
        }
    }

    function speakText(text) {
        if (!('speechSynthesis' in window)) {
            showToast('خاصية قراءة الصوت غير مدعومة في هذا المتصفح', 'info');
            return;
        }

        // If currently speaking, stop
        if (window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
            return;
        }

        // Clean text of markdown asterisks and URLs for fluent reading
        const cleanText = text
            .replace(/\*\*(.*?)\*\*/g, '$1')
            .replace(/https?:\/\/\S+/g, '')
            .replace(/[#*_`]/g, '')
            .trim();

        if (!cleanText) return;

        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'ar-SA';
        utterance.rate = 1.0;
        utterance.pitch = 1.0;

        // Try to pick Arabic voice if available
        const voices = window.speechSynthesis.getVoices();
        const arabicVoice = voices.find(v => v.lang.startsWith('ar') || v.name.toLowerCase().includes('arabic'));
        if (arabicVoice) {
            utterance.voice = arabicVoice;
        }

        window.speechSynthesis.speak(utterance);
    }

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatMarkdownMessage(text) {
        if (!text) return '';
        let escaped = escapeHtml(text);
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900 dark:text-white">$1</strong>');
        escaped = escaped.replace(/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/g, '<em class="italic">$1</em>');
        return escaped;
    }
</script>
@endpush
@endsection
