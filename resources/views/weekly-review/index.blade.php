@extends('layouts.app')

@section('title', 'المراجعة الأسبوعية الذكية — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xl shadow-md shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        المراجعة الأسبوعية الذكية
                    </h1>
                    <span class="text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/70 px-2 py-0.5 rounded-full border border-brand-200 dark:border-brand-800">
                        Weekly Review
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    وقفة تأمل أسبوعية لتقييم الإنجاز الفعلي، استخلاص الدروس، والتخطيط الواعي للأسبوع القادم.
                </p>
            </div>
        </div>

        <!-- Week Navigator -->
        <div class="flex items-center gap-2">
            @php
                $prevWeekStr = $startDate->copy()->subWeek()->toDateString();
                $nextWeekStr = $startDate->copy()->addWeek()->toDateString();
                $isCurrentWeek = $startDate->isCurrentWeek();
            @endphp
            <a href="{{ route('weekly-review.index', ['start' => $prevWeekStr]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="الأسبوع السابق">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
            <div class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 text-xs font-bold text-slate-800 dark:text-slate-200">
                <span>{{ $startDate->locale('ar')->isoFormat('D MMMM') }}</span>
                <span class="text-slate-400 mx-1">—</span>
                <span>{{ $endDate->locale('ar')->isoFormat('D MMMM YYYY') }}</span>
                @if($isCurrentWeek)
                    <span class="mr-1 text-[10px] text-teal-600 font-black">(الحالي)</span>
                @endif
            </div>
            <a href="{{ route('weekly-review.index', ['start' => $nextWeekStr]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="الأسبوع التالي">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </a>
        </div>
    </div>

    <!-- 5 Real Weekly KPI Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Task Completion Rate -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">نسبة إنجاز المهام</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">{{ $stats['completion_rate'] }}%</span>
            </div>
            <div class="mt-2 w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                <div class="bg-teal-500 h-full rounded-full" style="width: {{ $stats['completion_rate'] }}%;"></div>
            </div>
        </div>

        <!-- 2. Completed Tasks -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">المهام المنجزة</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-teal-600 dark:text-teal-400 font-mono">{{ $stats['completed_count'] }}</span>
                <span class="text-xs text-slate-400">مهمة</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400 truncate">من أصل {{ $stats['completed_count'] + $stats['pending_count'] }} مهمة</p>
        </div>

        <!-- 3. Overdue Tasks -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">مهام متأخرة</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black {{ $stats['overdue_count'] > 0 ? 'text-rose-500' : 'text-slate-900 dark:text-white' }} font-mono">{{ $stats['overdue_count'] }}</span>
                <span class="text-xs text-slate-400">مهمة</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400 truncate">تحتاج إعادة جدولة</p>
        </div>

        <!-- 4. Focus Time -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">ساعات التركيز العميق</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ round($stats['focus_minutes'] / 60, 1) }}</span>
                <span class="text-xs text-slate-400">ساعة</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400 truncate">{{ $stats['focus_sessions_count'] }} جلسات تركيز</p>
        </div>

        <!-- 5. Habits Compliance -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 col-span-2 lg:col-span-1">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">التزام دائرة العادات</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ $stats['habits_rate'] }}%</span>
            </div>
            <div class="mt-2 w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                <div class="bg-brand-500 h-full rounded-full" style="width: {{ $stats['habits_rate'] }}%;"></div>
            </div>
        </div>
    </div>

    <!-- Main Content: Reflection Questions & AI Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Reflection Form (7 cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-700 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-pen-fancy text-brand-600"></i>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">تأملات المراجعة الأسبوعية</h3>
                </div>
                <span class="text-xs text-slate-400">5 أسئلة جوهرية لبناء الوعي والانضباط</span>
            </div>

            <form id="reflectionForm" onsubmit="submitWeeklyAnswers(event)" class="space-y-4">
                @php
                    $answers = $review ? ($review->reflection_answers ?? []) : [];
                @endphp

                <!-- Q1 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        1. ما هو أكبر إنجاز أو نجاح حققته خلال هذا الأسبوع؟ 🏆
                    </label>
                    <textarea name="q_best_accomplishment" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500" placeholder="أهم انتصار حققته أو مهمة صعبة تجاوزتها...">{{ $answers['q_best_accomplishment'] ?? '' }}</textarea>
                </div>

                <!-- Q2 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        2. ما الذي لم يسِر كما كان مخططاً له؟ وما السبب الحقيقي وراء التعثر؟ 🤔
                    </label>
                    <textarea name="q_what_went_wrong" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500" placeholder="تحديد أسباب التسويف، المقاطعات، أو سوء التقدير بدون لوم الذات...">{{ $answers['q_what_went_wrong'] ?? '' }}</textarea>
                </div>

                <!-- Q3 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        3. كيف كان انضباطك في دائرة العادات؟ أين كانت نقطة الضعف؟ 🎯
                    </label>
                    <textarea name="q_habits_discipline" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500" placeholder="العادات التي حافظت عليها وتلك التي تراجعت فيها...">{{ $answers['q_habits_discipline'] ?? '' }}</textarea>
                </div>

                <!-- Q4 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        4. ما هو الدرس الذهبي المستفاد من هذا الأسبوع؟ 💡
                    </label>
                    <textarea name="q_lessons_learned" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500" placeholder="قاعدة أو تصرف ستطبقه الأسبوع القادم لتجنب تكرار الخطأ...">{{ $answers['q_lessons_learned'] ?? '' }}</textarea>
                </div>

                <!-- Q5 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        5. ما هي أهم 3 أولويات حاسمة للأسبوع القادم؟ ⚡
                    </label>
                    <textarea name="q_next_week_focus" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500" placeholder="1- ... &#10;2- ... &#10;3- ...">{{ $answers['q_next_week_focus'] ?? '' }}</textarea>
                </div>

                <!-- Rating -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">تقييمك لمستوى انضباطك هذا الأسبوع:</span>
                        <select name="rating" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs font-bold text-amber-500 focus:ring-2 focus:ring-brand-500">
                            @for($r = 5; $r >= 1; $r--)
                                <option value="{{ $r }}" {{ ($review && $review->rating == $r) ? 'selected' : '' }}>
                                    {{ str_repeat('⭐', $r) }} ({{ $r }}/5)
                                </option>
                            @endfor
                        </select>
                    </div>

                    <button type="submit" id="btnSaveAnswers" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition">
                        <i class="fa-solid fa-floppy-disk ml-1"></i>
                        حفظ التأملات
                    </button>
                </div>
            </form>
        </div>

        <!-- AI Insights & Planning (5 cols) -->
        <div class="lg:col-span-5 space-y-5">

            <!-- AI Weekly Analysis Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xs shadow-sm">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">تحليل MO AI للأسبوع</h3>
                            <span class="text-[10px] text-slate-400">تقييم موضوعي ومحفّز مبني على إحصائياتك</span>
                        </div>
                    </div>

                    <button type="button" onclick="runAiWeeklyAnalysis()" id="btnRunAiAnalysis" class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition">
                        <i class="fa-solid fa-robot ml-1"></i>
                        تحليل بالذكاء الاصطناعي
                    </button>
                </div>

                <div id="aiAnalysisBox" class="text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                    @if($review && !empty($review->ai_analysis))
                        @php $analysis = $review->ai_analysis; @endphp
                        <div class="space-y-3">
                            @if(!empty($analysis['summary']))
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                                <h5 class="font-bold text-slate-900 dark:text-white mb-1">📌 الخلاصة التقييمية:</h5>
                                <p class="text-slate-600 dark:text-slate-300">{{ $analysis['summary'] }}</p>
                            </div>
                            @endif

                            @if(!empty($analysis['strengths']))
                            <div class="p-3 rounded-xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900/40">
                                <h5 class="font-bold text-teal-800 dark:text-teal-300 mb-1">✨ نقاط القوة والإنجاز:</h5>
                                <ul class="list-disc list-inside space-y-0.5 text-teal-900 dark:text-teal-200">
                                    @foreach($analysis['strengths'] as $st)
                                        <li>{{ $st }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if(!empty($analysis['improvements']))
                            <div class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40">
                                <h5 class="font-bold text-amber-800 dark:text-amber-300 mb-1">⚠️ فرص التحسين والتعثر:</h5>
                                <ul class="list-disc list-inside space-y-0.5 text-amber-900 dark:text-amber-200">
                                    @foreach($analysis['improvements'] as $im)
                                        <li>{{ $im }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if(!empty($analysis['recommendation']))
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-brand-200 dark:border-brand-800/60">
                                <h5 class="font-bold text-brand-700 dark:text-brand-300 mb-1">🎯 التوصية الذهبية للأسبوع القادم:</h5>
                                <p class="text-slate-800 dark:text-slate-200">{{ $analysis['recommendation'] }}</p>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-400">
                            <i class="fa-solid fa-brain text-2xl mb-2 block"></i>
                            <p>اضغط على «تحليل بالذكاء الاصطناعي» للحصول على تقرير مفصّل وموضوعي عن أدائك هذا الأسبوع.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Next Week AI Planning Proposal -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-compass text-teal-600"></i>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">مقترح خطة الأسبوع القادم</h3>
                            <span class="text-[10px] text-slate-400">توزيع ذكي للأولويات لمنع التراكم</span>
                        </div>
                    </div>

                    <button type="button" onclick="runAiNextWeekPlan()" id="btnPlanNextWeek" class="px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition">
                        <i class="fa-solid fa-wand-magic-sparkles ml-1"></i>
                        اقتراح الخطة
                    </button>
                </div>

                <div id="aiNextWeekBox" class="text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                    @if($review && !empty($review->next_week_plan))
                        @php $planNext = $review->next_week_plan; @endphp
                        <div class="space-y-3">
                            @if(!empty($planNext['focus_theme']))
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">شعار الأسبوع القادم</span>
                                <p class="text-sm font-black text-brand-600 dark:text-brand-400 mt-0.5">{{ $planNext['focus_theme'] }}</p>
                            </div>
                            @endif

                            @if(!empty($planNext['top_3_priorities']))
                            <div class="p-3 rounded-xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900/40">
                                <h5 class="font-bold text-teal-800 dark:text-teal-300 mb-1">أهم 3 أهداف حاسمة:</h5>
                                <ol class="list-decimal list-inside space-y-0.5 text-teal-900 dark:text-teal-200">
                                    @foreach($planNext['top_3_priorities'] as $tp)
                                        <li>{{ $tp }}</li>
                                    @endforeach
                                </ol>
                            </div>
                            @endif

                            @if(!empty($planNext['distribution_advice']))
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                                <h5 class="font-bold text-slate-800 dark:text-slate-200 mb-1">نصيحة توزيع الأيام:</h5>
                                <p class="text-slate-600 dark:text-slate-300">{{ $planNext['distribution_advice'] }}</p>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="py-6 text-center text-slate-400">
                            <i class="fa-solid fa-calendar-week text-2xl mb-2 block"></i>
                            <p>اضغط على «اقتراح الخطة» ليقترح عليك MO AI توزيعاً متوازناً للأسبوع القادم.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    const currentWeekStart = "{{ $startDate->toDateString() }}";

    function submitWeeklyAnswers(e) {
        e.preventDefault();
        const form = document.getElementById('reflectionForm');
        const formData = new FormData(form);

        const payload = {
            start_date: currentWeekStart,
            q_best_accomplishment: formData.get('q_best_accomplishment'),
            q_what_went_wrong: formData.get('q_what_went_wrong'),
            q_habits_discipline: formData.get('q_habits_discipline'),
            q_lessons_learned: formData.get('q_lessons_learned'),
            q_next_week_focus: formData.get('q_next_week_focus'),
            rating: formData.get('rating')
        };

        const btn = document.getElementById('btnSaveAnswers');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-1"></i> جاري الحفظ...';

        fetch('{{ route("weekly-review.save-answers") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk ml-1"></i> حفظ التأملات';
            if (data.success) {
                showToast(data.message || 'تم حفظ تأملات المراجعة الأسبوعية بنجاح ✓', 'success');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk ml-1"></i> حفظ التأملات';
            showToast('تعذر حفظ التأملات', 'error');
        });
    }

    function runAiWeeklyAnalysis() {
        const btn = document.getElementById('btnRunAiAnalysis');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-1"></i> تحليل Gemini...';

        fetch('{{ route("weekly-review.analyze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ start_date: currentWeekStart })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.success) {
                showToast('تم إتمام التحليل الأسبوعي بنجاح 🎯', 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                showToast(data.message || 'تعذر استكمال التحليل', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showToast('حدث خطأ أثناء معالجة الطلب', 'error');
        });
    }

    function runAiNextWeekPlan() {
        const btn = document.getElementById('btnPlanNextWeek');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-1"></i> إعداد الخطة...';

        fetch('{{ route("weekly-review.plan-next-week") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ start_date: currentWeekStart })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.success) {
                showToast('تم تجهيز مقترح خطة الأسبوع القادم بنجاح ✓', 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                showToast(data.message || 'تعذر تجهيز المقترح', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showToast('حدث خطأ أثناء معالجة الطلب', 'error');
        });
    }
</script>
@endpush
