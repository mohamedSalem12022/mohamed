@extends('layouts.app')

@section('title', 'الجدول الذكي لليوم — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xl shadow-md shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        خطتي الذكية لليوم
                    </h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/70 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                        Smart Daily Planner
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    جدول واقعي ومنظم مبني على مهامك الفعلية، يمنع التكدس ويوفر فترات راحة وطوارئ.
                </p>
            </div>
        </div>

        <!-- Date navigation & Actions on the EXACT SAME LEVEL -->
        <div class="flex items-center gap-2 shrink-0 flex-nowrap overflow-x-auto pb-1 lg:pb-0">
            <form method="GET" action="{{ route('planner.index') }}" class="flex items-center gap-1.5 shrink-0">
                <a href="{{ route('planner.index', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition shrink-0" title="اليوم السابق">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="h-9 px-3 py-1.5 text-xs sm:text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-500 shrink-0">
                <a href="{{ route('planner.index', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition shrink-0" title="اليوم التالي">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
            </form>

            <button type="button" onclick="generateSmartPlan()" id="btnGeneratePlan" class="h-9 flex items-center gap-2 px-4 py-1.5 rounded-xl bg-gradient-to-r from-brand-600 to-teal-500 hover:from-brand-500 hover:to-teal-400 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-105 shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-wand-magic-sparkles text-teal-200"></i>
                <span>تخطيط اليوم بالذكاء الاصطناعي</span>
            </button>

            <button type="button" onclick="openAddItemModal()" class="h-9 flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold transition shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>إضافة فقرة</span>
            </button>
        </div>
    </div>

    <!-- Conflicts Warning Banner (if overlapping slots exist) -->
    @if(!empty($conflicts))
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 flex items-start gap-3 shadow-sm animate-fade-in">
        <div class="w-8 h-8 rounded-xl bg-amber-200 dark:bg-amber-800 text-amber-700 dark:text-amber-200 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold">تنبيه: تم رصد تعارض في أوقات الجدول</h4>
            <p class="text-xs mt-1 text-amber-800 dark:text-amber-300">
                هناك فقرات متداخلة في نفس الوقت:
                @foreach($conflicts as $c)
                    <span class="font-bold underline decoration-amber-400 mx-1">[{{ $c['item_a']['title'] }} مع {{ $c['item_b']['title'] }}]</span>
                @endforeach
                — يرجى تعديل أوقات البدء والانتهاء لتفادي التشتت.
            </p>
        </div>
    </div>
    @endif

    <!-- AI Plan Proposal Banner (if pending review or loaded) -->
    @if($plan && $plan->ai_proposal && !empty($plan->ai_proposal['ai_reasoning']))
    <div class="p-4 rounded-2xl bg-brand-50/50 dark:bg-brand-950/30 border border-brand-200 dark:border-brand-800 text-slate-800 dark:text-slate-200 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center text-xs shrink-0 shadow-sm">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs font-black text-brand-700 dark:text-brand-300 uppercase tracking-wider">تحليل وتوصيات المساعد MO AI لليوم</span>
                    @if(!empty($plan->ai_proposal['energy_level']))
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-900/60 text-brand-800 dark:text-brand-200 font-bold">
                        مستوى الطاقة المطلوب: {{ $plan->ai_proposal['energy_level'] }}
                    </span>
                    @endif
                </div>
                <p class="text-xs mt-1 text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                    {{ $plan->ai_proposal['ai_reasoning'] }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Key Plan Metrics -->
    @php
        $items = $plan ? $plan->items->sortBy('start_time') : collect();
        $totalItemsCount = $items->count();
        $completedItemsCount = $items->where('is_completed', true)->count();
        $totalMinutesScheduled = 0;
        $totalBreakMinutes = 0;
        foreach($items as $it) {
            $dur = (int)$it->duration_minutes;
            if ($dur <= 0 && $it->start_time && $it->end_time) {
                $dur = max(0, \Carbon\Carbon::parse($it->end_time)->diffInMinutes(\Carbon\Carbon::parse($it->start_time)));
            }
            $totalMinutesScheduled += $dur;
            if (in_array($it->type, ['break', 'buffer'])) {
                $totalBreakMinutes += $dur;
            }
        }
        $schedHours = floor($totalMinutesScheduled / 60);
        $schedRemMin = $totalMinutesScheduled % 60;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">ساعات العمل والأنشطة</span>
            <div class="mt-1 flex items-baseline gap-1 text-lg sm:text-xl font-black text-slate-900 dark:text-white font-mono">
                <span>{{ $schedHours }}</span> <span class="text-xs font-semibold text-slate-400">س</span>
                <span>{{ $schedRemMin }}</span> <span class="text-xs font-semibold text-slate-400">د</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">فترات الراحة والطوارئ</span>
            <div class="mt-1 flex items-baseline gap-1 text-lg sm:text-xl font-black text-teal-600 dark:text-teal-400 font-mono">
                <span>{{ $totalBreakMinutes }}</span> <span class="text-xs font-semibold text-slate-400">دقيقة</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">الفقرات المنجزة</span>
            <div class="mt-1 flex items-baseline gap-1 text-lg sm:text-xl font-black text-slate-900 dark:text-white font-mono">
                <span>{{ $completedItemsCount }}</span>
                <span class="text-xs font-semibold text-slate-400">من {{ $totalItemsCount }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">حالة الخطة</span>
            <div class="mt-1 flex items-center gap-1.5 text-sm sm:text-base font-black">
                @if($plan && $plan->is_approved)
                    <span class="text-teal-600 dark:text-teal-400 flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-xs"></i> معتمدة ونشطة
                    </span>
                @else
                    <span class="text-amber-500 flex items-center gap-1">
                        <i class="fa-solid fa-clock text-xs"></i> مسوّدة قيد التعديل
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Section: Daily Timeline & Sidebar Unscheduled Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Timeline Column (8 cols) -->
        <div class="lg:col-span-8 space-y-4">

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-brand-600"></i>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">جدول الساعات والفقرات</h3>
                        <span class="text-xs text-slate-400">({{ $items->count() }} فقرة مجدولة)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Sync Task Dates Button -->
                        <button type="button" onclick="confirmSyncTasks()" class="px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition" title="تحديث مواعيد المهام الفعلية في قاعدة البيانات لتتطابق مع أوقات الخطة">
                            <i class="fa-solid fa-arrows-rotate text-[11px] ml-1"></i>
                            مزامنة المواعيد مع المهام
                        </button>

                        <!-- Save & Approve Button -->
                        @if(!$plan || !$plan->is_approved)
                        <button type="button" onclick="approvePlan()" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition">
                            <i class="fa-solid fa-check ml-1"></i>
                            اعتماد وتفعيل الخطة
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Timeline Items List -->
                <div class="mt-4 space-y-3 relative">
                    @forelse($items as $item)
                    @php
                        $isBreak = in_array($item->type, ['break', 'buffer']);
                        $isFocus = $item->type === 'focus';
                    @endphp
                    <div class="group relative flex items-start gap-3 sm:gap-4 p-3.5 rounded-2xl border transition-all {{ $item->is_completed ? 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 opacity-60' : ($isBreak ? 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700' : ($isFocus ? 'bg-brand-50/30 dark:bg-brand-950/20 border-brand-200/70 dark:border-brand-800/40' : 'bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 hover:border-brand-300')) }}">
                        
                        <!-- Checkbox -->
                        <div class="pt-0.5 shrink-0">
                            <button type="button" onclick="toggleItemComplete({{ $item->id }}, {{ $item->is_completed ? 'false' : 'true' }})" class="w-6 h-6 rounded-lg border flex items-center justify-center text-xs transition {{ $item->is_completed ? 'bg-teal-600 border-teal-600 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent hover:border-teal-500' }}">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </div>

                        <!-- Time pill -->
                        <div class="flex flex-col items-center justify-center min-w-[70px] sm:min-w-[85px] py-1 px-2 rounded-xl bg-slate-100 dark:bg-slate-700/60 font-mono text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                            <span>{{ $item->start_time ? substr($item->start_time, 0, 5) : '--:--' }}</span>
                            <span class="text-[9px] text-slate-400">إلى</span>
                            <span>{{ $item->end_time ? substr($item->end_time, 0, 5) : '--:--' }}</span>
                        </div>

                        <!-- Content Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate {{ $item->is_completed ? 'line-through text-slate-400' : '' }}">
                                    {{ $item->title }}
                                </h4>

                                <!-- Type badge -->
                                @if($item->type === 'break')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                        ☕ استراحة
                                    </span>
                                @elseif($item->type === 'buffer')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                        🛡️ وقت طوارئ
                                    </span>
                                @elseif($item->type === 'focus')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 dark:bg-brand-950/60 text-brand-800 dark:text-brand-300">
                                        ⚡ تركيز عميق
                                    </span>
                                @elseif($item->priority === 'high')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200">
                                        ⚡ أولوية عالية
                                    </span>
                                @endif

                                @if($item->is_locked)
                                    <span class="text-amber-500 text-xs" title="هذا الموعد مقفول — لن يغيّره الذكاء الاصطناعي">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                @endif

                                @if($item->task)
                                    <a href="{{ route('tasks.index') }}" class="text-[10px] text-brand-600 hover:underline flex items-center gap-1 font-semibold">
                                        <i class="fa-solid fa-link text-[8px]"></i>
                                        <span>مهمة مربوطة</span>
                                    </a>
                                @endif
                            </div>

                            @if($item->notes)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">
                                {{ $item->notes }}
                            </p>
                            @endif
                        </div>

                        <!-- Actions (Lock, Edit, Delete) -->
                        <div class="flex items-center gap-1 shrink-0 opacity-80 group-hover:opacity-100 transition">
                            <button type="button" onclick="toggleLockItem({{ $item->id }}, {{ $item->is_locked ? 'false' : 'true' }})" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition {{ $item->is_locked ? 'text-amber-500 bg-amber-50 dark:bg-amber-950/50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700' }}" title="{{ $item->is_locked ? 'إلغاء قفل الموعد' : 'قفل الموعد لمنع الذكاء الاصطناعي من تغييره' }}">
                                <i class="fa-solid {{ $item->is_locked ? 'fa-lock' : 'fa-lock-open' }}"></i>
                            </button>
                            <button type="button" onclick="openEditItemModal({{ json_encode($item) }})" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="تعديل الفقرة">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" onclick="deletePlanItem({{ $item->id }})" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="حذف من الجدول">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>

                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400">
                        <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center text-2xl text-slate-300 dark:text-slate-500 mb-3">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-600 dark:text-slate-300">لا يوجد جدول مسجّل لهذا اليوم حتى الآن</p>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1 mb-4">
                            اضغط على «تخطيط اليوم بالذكاء الاصطناعي» وسيقوم MO AI بجدولة مهامك تلقائياً وبشكل واقعي ومريح.
                        </p>
                        <button type="button" onclick="generateSmartPlan()" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition">
                            <i class="fa-solid fa-wand-magic-sparkles ml-1"></i>
                            أنشئ جدولي اليومي الآن
                        </button>
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- Sidebar (4 cols): Unscheduled Tasks & Buffer Recommendations -->
        <div class="lg:col-span-4 space-y-4">

            <!-- Unscheduled / Deferred Tasks Drawer -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-inbox text-amber-500"></i>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">مهام لم تُجدول اليوم</h4>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                        {{ count($unscheduledTasks) }}
                    </span>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse($unscheduledTasks as $unTask)
                    <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 hover:border-brand-300 transition">
                        <div class="flex items-start justify-between gap-2">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                {{ $unTask['title'] }}
                            </h5>
                            @if(($unTask['priority'] ?? '') === 'high')
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200 shrink-0">
                                عاجلة
                            </span>
                            @endif
                        </div>
                        @if(!empty($unTask['reason']))
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                            💡 {{ $unTask['reason'] }}
                        </p>
                        @endif
                        <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                            <span>المدة المتوقعة: {{ $unTask['estimated_minutes'] ?? 30 }} دقيقة</span>
                            <button type="button" onclick="quickScheduleTask({{ $unTask['id'] }}, '{{ addslashes($unTask['title']) }}', {{ $unTask['estimated_minutes'] ?? 30 }})" class="text-brand-600 hover:underline font-bold">
                                + جدولة الآن
                            </button>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">جميع مهامك الحالية مجدولة بالكامل! 🎯</p>
                    @endforelse
                </div>
            </div>

            <!-- Smart Daily Advice Box -->
            <div class="bg-gradient-to-br from-brand-50 to-teal-50 dark:from-slate-800 dark:to-slate-800 rounded-2xl p-5 border border-brand-200/60 dark:border-slate-700">
                <div class="flex items-center gap-2 mb-2 text-brand-700 dark:text-brand-300">
                    <i class="fa-solid fa-lightbulb"></i>
                    <h5 class="text-xs font-bold uppercase tracking-wider">نصيحة الانضباط اليومي</h5>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    «التخطيط الواقعي ليس حشر 15 مهمة في اليوم، بل تحديد أهم 3 مهام وإنجازها بإتقان، مع إبقاء وقت مرن للطوارئ والراحة لتجنب الاحتراق النفسي.»
                </p>
            </div>

        </div>

    </div>

</div>

<!-- Modal: Add / Edit Plan Item -->
<div id="planItemModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden animate-fade-in">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 id="modalTitle" class="text-base font-bold text-slate-900 dark:text-white">إضافة فقرة للجدول</h3>
            <button type="button" onclick="closePlanItemModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="planItemForm" onsubmit="submitPlanItem(event)" class="p-5 space-y-4">
            <input type="hidden" id="itemPlanId" value="{{ $plan ? $plan->id : '' }}">
            <input type="hidden" id="itemId" value="">
            <input type="hidden" id="itemTaskId" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان الفقرة *</label>
                <input type="text" id="itemTitle" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وقت البدء *</label>
                    <input type="time" id="itemStartTime" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وقت الانتهاء *</label>
                    <input type="time" id="itemEndTime" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نوع الفقرة</label>
                    <select id="itemType" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value="task">مهمة عادية</option>
                        <option value="focus">تركيز عميق (Focus Session)</option>
                        <option value="break">استراحة (Break)</option>
                        <option value="buffer">وقت احتياطي / طوارئ (Buffer)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأولوية</label>
                    <select id="itemPriority" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value="medium">متوسطة</option>
                        <option value="high">عالية ⚡</option>
                        <option value="low">منخفضة</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات إضافية</label>
                <input type="text" id="itemNotes" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500" placeholder="أية ملاحظة أو رابط...">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="itemIsLocked" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                <label for="itemIsLocked" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    قفل هذا الموعد (تثبيته بحيث لا يغيره الذكاء الاصطناعي عند إعادة التخطيط)
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closePlanItemModal()" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition">
                    حفظ الفقرة
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const currentDate = "{{ $date }}";

    function generateSmartPlan() {
        const btn = document.getElementById('btnGeneratePlan');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري التخطيط الذكي عبر Gemini...';

        fetch('{{ route("planner.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                date: currentDate,
                work_start_time: '08:30',
                work_end_time: '21:00',
                target_focus_hours: 6
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            if (data.success) {
                showToast(data.message || 'تم توليد خطة اليوم بنجاح ✓', 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                showToast(data.message || 'تعذر توليد الخطة', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            showToast('حدث خطأ أثناء الاتصال بالخادم', 'error');
        });
    }

    function approvePlan() {
        fetch('{{ route("planner.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                date: currentDate,
                is_approved: true
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('تم اعتماد الخطة اليومية وتفعيلها بنجاح 🎯', 'success');
                setTimeout(() => window.location.reload(), 400);
            }
        });
    }

    function confirmSyncTasks() {
        if (!confirm('هل تريد فعلاً تحديث تواريخ استحقاق المهام في قاعدة البيانات لتتطابق مع أوقات الخطة المعتمدة؟ لن يتم تغيير أي شيء بدون تأكيدك.')) {
            return;
        }

        fetch('{{ route("planner.sync-tasks") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ date: currentDate })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
            }
        });
    }

    function openAddItemModal() {
        document.getElementById('modalTitle').textContent = 'إضافة فقرة جديدة للجدول';
        document.getElementById('itemId').value = '';
        document.getElementById('itemTaskId').value = '';
        document.getElementById('itemTitle').value = '';
        document.getElementById('itemStartTime').value = '09:00';
        document.getElementById('itemEndTime').value = '10:00';
        document.getElementById('itemType').value = 'task';
        document.getElementById('itemPriority').value = 'medium';
        document.getElementById('itemNotes').value = '';
        document.getElementById('itemIsLocked').checked = false;
        document.getElementById('planItemModal').classList.remove('hidden');
    }

    function openEditItemModal(item) {
        document.getElementById('modalTitle').textContent = 'تعديل فقرة الجدول';
        document.getElementById('itemId').value = item.id;
        document.getElementById('itemTaskId').value = item.task_id || '';
        document.getElementById('itemTitle').value = item.title;
        document.getElementById('itemStartTime').value = item.start_time ? item.start_time.substring(0, 5) : '09:00';
        document.getElementById('itemEndTime').value = item.end_time ? item.end_time.substring(0, 5) : '10:00';
        document.getElementById('itemType').value = item.type || 'task';
        document.getElementById('itemPriority').value = item.priority || 'medium';
        document.getElementById('itemNotes').value = item.notes || '';
        document.getElementById('itemIsLocked').checked = !!item.is_locked;
        document.getElementById('planItemModal').classList.remove('hidden');
    }

    function closePlanItemModal() {
        document.getElementById('planItemModal').classList.add('hidden');
    }

    function submitPlanItem(e) {
        e.preventDefault();
        const itemId = document.getElementById('itemId').value;
        const payload = {
            date: currentDate,
            task_id: document.getElementById('itemTaskId').value || null,
            title: document.getElementById('itemTitle').value,
            start_time: document.getElementById('itemStartTime').value,
            end_time: document.getElementById('itemEndTime').value,
            type: document.getElementById('itemType').value,
            priority: document.getElementById('itemPriority').value,
            notes: document.getElementById('itemNotes').value,
            is_locked: document.getElementById('itemIsLocked').checked
        };

        const url = itemId ? `/planner/item/${itemId}` : '{{ route("planner.item.add") }}';
        const method = itemId ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'تم حفظ الفقرة بنجاح', 'success');
                closePlanItemModal();
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function deletePlanItem(id) {
        if (!confirm('هل أنت متأكد من حذف هذه الفقرة من الجدول؟')) return;

        fetch(`/planner/item/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('تم حذف الفقرة من الجدول', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function toggleLockItem(id, lockStatus) {
        fetch(`/planner/item/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_locked: lockStatus })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(lockStatus ? 'تم تثبيت الموعد بنجاح 🔒' : 'تم فك تثبيت الموعد', 'success');
                setTimeout(() => window.location.reload(), 200);
            }
        });
    }

    function toggleItemComplete(id, completed) {
        fetch(`/planner/item/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_completed: completed })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(completed ? 'أحسنت! تم إنجاز الفقرة ✓' : 'تمت إعادة تعيين الفقرة', 'success');
                setTimeout(() => window.location.reload(), 200);
            }
        });
    }

    function quickScheduleTask(taskId, title, durationMinutes) {
        openAddItemModal();
        document.getElementById('itemTaskId').value = taskId;
        document.getElementById('itemTitle').value = title;
        // suggest next available hour or 11:00
        document.getElementById('itemStartTime').value = '11:00';
        document.getElementById('itemEndTime').value = '11:45';
    }
</script>
@endpush
