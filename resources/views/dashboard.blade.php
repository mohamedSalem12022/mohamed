@extends('layouts.app')

@section('title', 'لوحة التحكم — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Welcome Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 text-white p-6 sm:p-8 shadow-xl border border-slate-700/60">
        <!-- Background subtle glow & design elements -->
        <div class="absolute -top-12 -left-12 w-56 h-56 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-64 h-64 bg-brand-400/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-teal-300 text-xs font-semibold">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>{{ \Carbon\Carbon::now()->locale($currentLocale ?? 'ar')->isoFormat(($currentLocale ?? 'ar') === 'ar' ? 'dddd، D MMMM YYYY' : 'dddd, MMMM D, YYYY') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    {{ __('welcome_back') }} {{ $user->name }} 👋
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                    {{ __('discipline_quote') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('habits.index') }}" class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-brand-600 to-teal-500 hover:from-brand-500 hover:to-teal-400 text-white font-bold text-xs sm:text-sm shadow-lg shadow-brand-500/30 transition-all hover:scale-105">
                    <i class="fa-solid fa-circle-notch"></i>
                    <span>{{ __('open_full_wheel') }}</span>
                </a>
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/15 text-white font-bold text-xs sm:text-sm backdrop-blur-md transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>{{ __('new_task') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Performance Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- 1. Today's Progress -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden group hover:border-brand-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('today_rate') }}
                </span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">
                    {{ $todayRate }}%
                </span>
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400">
                    ({{ $completedTodayCount }}/{{ $totalHabitsCount }})
                </span>
            </div>
            <div class="mt-3 w-full bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                <div class="bg-teal-500 h-full rounded-full transition-all duration-700" style="width: {{ $todayRate }}%;"></div>
            </div>
        </div>

        <!-- 2. Month's Progress -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden group hover:border-brand-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('month_rate') }}
                </span>
                <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">
                    {{ $monthRate }}%
                </span>
                <span class="text-xs font-medium text-slate-400">
                    {{ __('until_today') }}
                </span>
            </div>
            <div class="mt-3 w-full bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                <div class="bg-brand-500 h-full rounded-full transition-all duration-700" style="width: {{ $monthRate }}%;"></div>
            </div>
        </div>

        <!-- 3. Current Streak -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden group hover:border-brand-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('streak') }}
                </span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">
                    {{ $maxStreak }}
                </span>
                <span class="text-xs font-bold text-brand-600 dark:text-brand-400 flex items-center gap-1">
                    <span>{{ __('consecutive_days_label') }}</span>
                    <i class="fa-solid fa-calendar-check text-[11px]"></i>
                </span>
            </div>
            <p class="mt-3 text-[11px] text-slate-400 truncate">
                {{ __('discipline_daily_quote') }}
            </p>
        </div>

        <!-- 4. Remaining Tasks -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden group hover:border-brand-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('today_tasks') }}
                </span>
                <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">
                    {{ $pendingTasksCount }}
                </span>
                <span class="text-xs font-medium text-slate-400">
                    {{ __('remaining_tasks_sub') }}
                </span>
            </div>
            <p class="mt-3 text-[11px] text-slate-400 truncate">
                {{ __('tasks_completed_prefix') }} {{ $completedTasksCount }} {{ __('tasks_completed_total') }}
            </p>
        </div>
    </div>

    <!-- Smart Daily Planner Widget (Phase 2) -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('today_smart_plan') }}</h3>
                        @if($todayPlan && $todayPlan->is_approved)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-950/70 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                {{ __('plan_approved') }}
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] text-slate-400">{{ __('plan_subtitle') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('planner.index') }}" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
                    <span>{{ __('open_full_planner') }}</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>
        </div>

        @if($todayPlan && $todayPlan->items->count() > 0)
            @php
                $dashboardPlanItems = $todayPlan->items->sortBy('start_time');
                $dCompletedCount = $dashboardPlanItems->where('is_completed', true)->count();
                $dTotalCount = $dashboardPlanItems->count();
                $dPercent = $dTotalCount > 0 ? round(($dCompletedCount / $dTotalCount) * 100) : 0;
            @endphp
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                    <span>{{ __('plan_progress') }}: {{ $dCompletedCount }} {{ __('of') }} {{ $dTotalCount }} {{ __('slots') }}</span>
                    <span class="font-mono font-bold text-teal-600 dark:text-teal-400">{{ $dPercent }}%</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                    <div class="bg-teal-500 h-full rounded-full transition-all duration-500" style="width: {{ $dPercent }}%;"></div>
                </div>

                <!-- Horizontal scrollable timeline strip -->
                <div class="flex items-center gap-2.5 overflow-x-auto py-2 scrollbar-thin">
                    @foreach($dashboardPlanItems as $pItem)
                        @php
                            $isBreak = in_array($pItem->type, ['break', 'buffer']);
                        @endphp
                        <div class="shrink-0 flex items-center gap-2 px-3 py-2 rounded-xl border text-xs {{ $pItem->is_completed ? 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 opacity-60' : ($isBreak ? 'bg-slate-100 dark:bg-slate-700/60 border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300' : 'bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700') }}">
                            <span class="font-mono text-[10px] font-bold text-slate-400">
                                {{ $pItem->start_time ? substr($pItem->start_time, 0, 5) : '' }}
                            </span>
                            <span class="font-bold max-w-[130px] truncate {{ $pItem->is_completed ? 'line-through text-slate-400' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $pItem->title }}
                            </span>
                            @if($isBreak)
                                <span class="text-[9px]">☕</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950/70 text-brand-600 dark:text-brand-400 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-wand-magic-sparkles text-brand-600 dark:text-brand-400"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ ($currentLocale ?? 'ar') === 'en' ? 'No Smart Plan Created for Today Yet' : 'لم تُنشئ جدولاً ذكياً لليوم بعد' }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ ($currentLocale ?? 'ar') === 'en' ? 'Let AI arrange your daily schedule to eliminate distractions and maximize focus.' : 'دع الذكاء الاصطناعي يرتب مهامك اليومية تلقائياً لمنع التشتت وتحقيق أقصى تركيز.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('planner.index') }}" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition">
                        <i class="fa-solid fa-wand-magic-sparkles ltr:mr-1 rtl:ml-1"></i>
                        {{ ($currentLocale ?? 'ar') === 'en' ? 'AI Daily Planning' : 'تخطيط اليوم بالذكاء الاصطناعي' }}
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Section: Habit Wheel & Today's Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Center Interactive Habit Wheel -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('month_summary') }}</h2>
                        <span class="text-xs text-slate-400">{{ __('interactive_instruction') }}</span>
                    </div>
                </div>
                <a href="{{ route('habits.index') }}" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
                    <span>{{ __('full_wheel_view') }}</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>

            <!-- SVG Habit Wheel -->
            <div class="flex justify-center items-center py-2">
                @include('habits.wheel', ['monthNameAr' => \Carbon\Carbon::createFromDate($year, $month, 1)->locale($currentLocale ?? 'ar')->isoFormat('MMMM')])
            </div>
        </div>

        <!-- Right Side Widgets (Today's Checklist, Weekly Progress, Recent Tasks) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Today's Quick Habit Checklist -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-teal-600"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('today_habits_quick') }}</h3>
                    </div>
                    <span class="text-xs font-bold text-teal-600 bg-teal-50 dark:bg-teal-950/60 px-2 py-0.5 rounded-full">
                        {{ $completedTodayCount }}/{{ $totalHabitsCount }}
                    </span>
                </div>

                <div class="space-y-2">
                    @forelse($habits as $habit)
                    @php
                        $todayLog = $todayLogs[$habit->id] ?? null;
                        $isCompleted = ($todayLog && $todayLog->status === 'completed');
                    @endphp
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 {{ $isCompleted ? 'bg-teal-50/50 dark:bg-teal-950/30 border-teal-200 dark:border-teal-800/60' : 'bg-slate-50 dark:bg-slate-800/50' }} hover:border-brand-300 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $habit->color }}"></span>
                            <span class="text-xs font-bold truncate {{ $isCompleted ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $habit->name }}
                            </span>
                        </div>

                        <!-- Fast checkmark button -->
                        <button type="button" onclick="quickToggleHabitToday({{ $habit->id }}, '{{ $todayDate }}', {{ $isCompleted ? 'true' : 'false' }}, this)" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold transition shadow-sm {{ $isCompleted ? 'bg-teal-600 text-white hover:bg-teal-700' : 'border border-slate-300 dark:border-slate-600 text-slate-400 hover:border-teal-500 hover:text-teal-600' }}">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">{{ ($currentLocale ?? 'ar') === 'en' ? 'No habits added yet.' : 'لم تقم بإضافة عادات بعد.' }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Weekly 7-Day Performance Overview -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-simple text-brand-600"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('weekly_performance') }}</h3>
                    </div>
                    <span class="text-[11px] text-slate-400 font-semibold">{{ ($currentLocale ?? 'ar') === 'en' ? 'Discipline Rate' : 'نسبة الالتزام' }}</span>
                </div>

                <!-- 7-day Bar Chart -->
                <div class="grid grid-cols-7 gap-2 items-end h-32 pt-4">
                    @foreach($weeklyDays as $wDay)
                    <div class="flex flex-col items-center gap-1.5 h-full justify-end group">
                        <span class="text-[10px] font-bold text-slate-400 group-hover:text-brand-600 transition font-mono opacity-0 group-hover:opacity-100">
                            {{ $wDay['rate'] }}%
                        </span>
                        <div class="w-full bg-slate-100 dark:bg-slate-700/60 rounded-lg overflow-hidden h-20 flex items-end">
                            <div class="w-full rounded-lg transition-all duration-500 {{ $wDay['is_today'] ? 'bg-gradient-to-t from-teal-600 to-teal-400' : 'bg-teal-500/70 group-hover:bg-teal-500' }}" style="height: {{ max(10, $wDay['rate']) }}%;"></div>
                        </div>
                        <span class="text-[10px] font-bold {{ $wDay['is_today'] ? 'text-teal-600 dark:text-teal-400 font-black' : 'text-slate-500' }}">
                            {{ $wDay['day_name'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Today's Pending Tasks -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-brand-600"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('today_tasks') }}</h3>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-bold text-brand-600 hover:underline">
                        {{ ($currentLocale ?? 'ar') === 'en' ? 'Manage Tasks' : 'إدارة المهام' }}
                    </a>
                </div>

                <div class="space-y-2">
                    @forelse($todayTasks as $task)
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50/60 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-700/40 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <button onclick="quickToggleTask({{ $task->id }}, this)" class="w-5 h-5 rounded border {{ $task->status === 'completed' ? 'bg-teal-600 border-teal-600 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent hover:border-teal-500' }} flex items-center justify-center text-[10px] transition shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <span class="text-xs font-bold truncate {{ $task->status === 'completed' ? 'line-through text-slate-400' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $task->title }}
                            </span>
                        </div>
                        @if($task->priority === 'high')
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200 shrink-0">
                            {{ __('priority_high') }}
                        </span>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">{{ ($currentLocale ?? 'ar') === 'en' ? 'No tasks scheduled for today.' : 'لا توجد مهام محددة لليوم.' }}</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function quickToggleHabitToday(habitId, date, isCurrentlyCompleted, btn) {
        const nextStatus = isCurrentlyCompleted ? 'clear' : 'completed';

        fetch('{{ route("habits.log-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                habit_id: habitId,
                date: date,
                status: nextStatus
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(nextStatus === 'completed' ? 'أحسنت! تم إنجاز العادة اليوم ✓' : 'تم إلغاء الإنجاز', 'success');
                setTimeout(() => window.location.reload(), 400);
            }
        });
    }

    function quickToggleTask(taskId, btn) {
        fetch(`/tasks/${taskId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.status === 'completed' ? 'تم إنجاز المهمة بنجاح ✓' : 'تمت إعادة المهمة كقيد التنفيذ', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }
</script>
@endpush
@endsection
