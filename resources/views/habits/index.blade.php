@extends('layouts.app')

@section('title', 'دائرة العادات الشهرية — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Top Bar: Navigation between months & Quick Summary -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <!-- Month Navigator -->
        <div class="flex items-center gap-3">
            @php
                $prevMonth = $month - 1;
                $prevYear = $year;
                if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

                $nextMonth = $month + 1;
                $nextYear = $year;
                if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
            @endphp
            <a href="{{ route('habits.index', ['year' => $prevYear, 'month' => $prevMonth]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="الشهر السابق">
                <i class="fa-solid fa-chevron-right text-xs rtl:rotate-0 ltr:rotate-180"></i>
            </a>

            <div class="text-center min-w-[150px]">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $monthNameAr }} {{ $year }}
                </h1>
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                    {{ $monthNameEn }} — {{ $daysInMonth }} يوماً
                </span>
            </div>

            <a href="{{ route('habits.index', ['year' => $nextYear, 'month' => $nextMonth]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="الشهر القادم">
                <i class="fa-solid fa-chevron-left text-xs rtl:rotate-0 ltr:rotate-180"></i>
            </a>

            <!-- Return to Current Month -->
            @if($year != date('Y') || $month != date('n'))
            <a href="{{ route('habits.index') }}" class="px-3 py-1.5 rounded-lg bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800 text-brand-600 dark:text-brand-400 text-xs font-bold hover:bg-brand-100 transition">
                الشهر الحالي
            </a>
            @endif
        </div>

        <!-- Action: Add New Habit Button & MO AI Consultation -->
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('ai.index') }}" class="flex items-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 text-teal-700 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/60 font-bold text-xs sm:text-sm shadow-2xs transition-all hover:scale-[1.02]" title="استشارة المساعد الذكي حول عاداتك والتزامك">
                <i class="fa-solid fa-wand-magic-sparkles text-teal-600 dark:text-teal-400 text-xs sm:text-sm"></i>
                <span>استشارة MO AI</span>
            </a>
            <button onclick="openAddHabitModal()" class="flex items-center gap-2 px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.02]">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>إضافة عادة جديدة</span>
            </button>
        </div>
    </div>

    <!-- The 3-Column Layout Matching the Printed Paper Tracker -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Column 1 (Left): HABITS List (Rows 1 to 7) -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-list-ol text-brand-600 text-lg"></i>
                        <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider font-mono">
                            HABITS :
                        </h2>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-800">
                        {{ count($habits) }} عادات
                    </span>
                </div>

                <!-- Habits List Rows (Aligned from Outer Ring 1 to Inner Ring) -->
                <div class="space-y-2.5" id="habitsListContainer">
                    @forelse($habits as $index => $habit)
                    @php
                        $habitRate = $habit->getMonthlyCompletionRate($year, $month);
                        $habitStreak = $habit->getCurrentStreak();
                    @endphp
                    <div class="habit-item group relative p-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-700/50 hover:shadow-sm hover:border-brand-300 dark:hover:border-brand-600 transition-all cursor-grab active:cursor-grabbing"
                         data-habit-id="{{ $habit->id }}"
                         draggable="true">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <!-- Compact Move Up / Down Buttons -->
                                <div class="flex flex-col -space-y-0.5 shrink-0" title="تبديل ترتيب العادة">
                                    <button type="button" onclick="moveHabitUp(this, {{ $habit->id }})" class="btn-move-up w-3.5 h-3 flex items-center justify-center text-slate-300 hover:text-brand-600 dark:text-slate-600 dark:hover:text-brand-400 transition disabled:opacity-0 disabled:pointer-events-none" title="تقديم العادة (تحريك لأعلى)" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-caret-up text-[10px] leading-none"></i>
                                    </button>
                                    <button type="button" onclick="moveHabitDown(this, {{ $habit->id }})" class="btn-move-down w-3.5 h-3 flex items-center justify-center text-slate-300 hover:text-brand-600 dark:text-slate-600 dark:hover:text-brand-400 transition disabled:opacity-0 disabled:pointer-events-none" title="تأخير العادة (تحريك لأسفل)" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-caret-down text-[10px] leading-none"></i>
                                    </button>
                                </div>

                                <!-- Ring Index Badge (Draggable handle) -->
                                <span class="habit-badge w-6 h-6 rounded-lg text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 shadow-xs select-none" style="background-color: {{ $habit->color }}" title="اسحب لإعادة الترتيب">
                                    {{ $index + 1 }}
                                </span>

                                <!-- Habit Name & English Subtitle -->
                                <div class="min-w-0 flex-1 pr-1">
                                    <h3 class="text-xs font-bold text-slate-900 dark:text-white truncate" title="{{ $habit->name }}">
                                        {{ $habit->name }}
                                    </h3>
                                    @if($habit->name_en)
                                    <span class="text-[10px] text-slate-400 font-mono block truncate">
                                        {{ $habit->name_en }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions & Stats (Preserved original compact width) -->
                            <div class="flex items-center gap-1 shrink-0">
                                <span id="rate-habit-{{ $habit->id }}" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-brand-50 dark:bg-brand-950/80 text-brand-600 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/60">
                                    {{ $habitRate }}%
                                </span>

                                <button onclick="openEditHabitModal({{ json_encode($habit) }})" class="w-6 h-6 rounded text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-600 flex items-center justify-center transition" title="تعديل">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                </button>
                                <form action="{{ route('habits.destroy', $habit->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه العادة وسجلاتها؟');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-6 h-6 rounded text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-600 flex items-center justify-center transition" title="حذف">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Progress Bar & Streak -->
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex-1 h-1.5 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $habitRate }}%; background-color: {{ $habit->color }};"></div>
                            </div>
                            <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 flex items-center gap-0.5" id="streak-habit-{{ $habit->id }}" title="سلسلة الالتزام">
                                <i class="fa-solid fa-calendar-check text-[10px]"></i> {{ $habitStreak }}ي
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-slate-400">
                        <i class="fa-solid fa-circle-plus text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                        <p class="text-xs">لم تقم بإضافة أي عادات بعد.</p>
                        <button onclick="openAddHabitModal()" class="mt-2 text-xs font-bold text-brand-600 hover:underline">
                            + أضف عادتك الأولى
                        </button>
                    </div>
                    @endforelse
                </div>

                <!-- Legend of status colors -->
                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-1.5 text-[11px]">
                    <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px] mb-2">دليل الألوان في الدائرة:</p>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-teal-600 shadow-sm shrink-0"></span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">تم الإنجاز (تركوازي / أخضر)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-accent-500 shadow-sm shrink-0"></span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">لم يتم الإنجاز (برتقالي / عذر)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-slate-400 shadow-sm shrink-0"></span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">تخطي اليوم المسموح</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 shrink-0"></span>
                        <span class="text-slate-500 dark:text-slate-400 font-medium">خلية محايدة / لم تسجل بعد</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2 (Center): The Star Circular Tracker Wheel -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center min-h-[550px] relative">
            <!-- Top Instruction Badge -->
            <div class="w-full flex items-center justify-between mb-2">
                <span class="text-xs text-slate-400 font-medium">
                    <i class="fa-regular fa-hand-pointer text-brand-600 ml-1"></i> اضغط على أي قطاع بالدائرة لتسجيل حالتك
                </span>
                <span class="text-xs font-mono font-bold text-brand-600 bg-brand-50 dark:bg-brand-950 px-2 py-0.5 rounded-md border border-brand-200 dark:border-brand-800">
                    {{ count($habits) }} Rings × {{ $daysInMonth }} Days
                </span>
            </div>

            <!-- SVG Circular Wheel Inclusion -->
            @include('habits.wheel')
        </div>

        <!-- Column 3 (Right): Notes & Reflections & Excuses Overcome (Matching reference sheet) -->
        <div class="lg:col-span-3 space-y-5">
            <!-- Discipline Header with Muscle Graphic -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-sm border border-slate-700 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black tracking-tight flex items-center gap-2 font-mono">
                            Discipline
                        </h2>
                        <span class="text-[11px] text-teal-400 font-bold uppercase tracking-widest block">
                            TRACKER & JOURNAL
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-teal-400 text-xl">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-300 italic leading-relaxed">
                    "Discipline is doing what needs to be done, even when you don't feel like it."
                </p>
            </div>

            <!-- NOTE & REFLECTIONS Box (Lined paper design matching the sheet) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest font-mono flex items-center gap-1.5">
                        <i class="fa-solid fa-feather text-brand-600"></i>
                        NOTE & REFLECTIONS
                    </h3>
                    <a href="{{ route('reflections.index') }}" class="text-[11px] font-bold text-brand-600 hover:underline">
                        عرض الكل
                    </a>
                </div>

                <div class="lined-paper rounded-xl border border-slate-200 dark:border-slate-700/80 min-h-[140px] text-xs font-handwriting text-slate-800 dark:text-slate-100 whitespace-pre-line leading-relaxed">
@if($reflection && $reflection->notes)
{{ $reflection->notes }}
@if($reflection->best_win)
★ إنجاز اليوم: {{ $reflection->best_win }}
@endif
@else
- سجل هنا تأملاتك وملاحظاتك لهذا اليوم...
- ما هو أفضل إنجاز حققته اليوم؟
- ما الذي تريد تحسينه غداً؟
@endif
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">تاريخ اليوم: {{ $todayDate }}</span>
                    <a href="{{ route('reflections.index') }}" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-brand-50 hover:text-brand-600 transition">
                        تعديل الملاحظات ✏️
                    </a>
                </div>
            </div>

            <!-- Excuses I Overcome Box (Matching reference sheet) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest font-mono flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-accent-500"></i>
                        Excuses I overcome
                    </h3>
                    <a href="{{ route('excuses.index') }}" class="text-[11px] font-bold text-accent-600 hover:underline">
                        إدارة الأعذار
                    </a>
                </div>

                <div class="space-y-2">
                    @forelse($excuses as $excuse)
                    <div class="p-2.5 rounded-xl border {{ $excuse->overcame ? 'border-teal-200 dark:border-teal-900 bg-teal-50/50 dark:bg-teal-950/30' : 'border-accent-200 dark:border-accent-900 bg-accent-50/40 dark:bg-accent-950/30' }} flex items-start gap-2">
                        <button onclick="toggleExcuse({{ $excuse->id }}, this)" class="mt-0.5 w-4 h-4 rounded text-xs flex items-center justify-center shrink-0 {{ $excuse->overcame ? 'bg-teal-600 text-white' : 'border border-accent-400 text-accent-500' }}">
                            <i class="fa-solid {{ $excuse->overcame ? 'fa-check' : '' }} text-[9px]"></i>
                        </button>
                        <div class="min-w-0">
                            <p class="text-xs font-bold {{ $excuse->overcame ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $excuse->excuse_details }}
                            </p>
                            @if($excuse->how_to_overcome)
                            <p class="text-[10px] text-teal-600 dark:text-teal-400 mt-0.5">
                                💡 {{ $excuse->how_to_overcome }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">
                        لا توجد أعذار مسجلة. استمر في الانضباط!
                    </p>
                    @endforelse
                </div>

                <a href="{{ route('excuses.index') }}" class="mt-3 block text-center py-1.5 text-xs font-bold text-accent-600 dark:text-accent-400 hover:bg-accent-50 dark:hover:bg-accent-950/40 rounded-lg border border-accent-200 dark:border-accent-800 transition">
                    + تسجيل عذر وخطة تجاوزه
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Modal: Add New Habit -->
<div id="addHabitModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-brand-600"></i>
                إضافة عادة يومية جديدة
            </h3>
            <button onclick="closeAddHabitModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('habits.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العادة باللغة العربية *</label>
                <input type="text" name="name" required placeholder="مثال: قراءة 20 صفحة، شرب 2 لتر ماء" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العادة بالإنجليزية (اختياري)</label>
                <input type="text" name="name_en" placeholder="e.g. Read 20 pages" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">لون العادة في الدائرة</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="habitColorPicker" name="color" value="#0d9488" class="w-10 h-10 rounded-lg cursor-pointer border border-slate-300 dark:border-slate-600 bg-transparent">
                    <span class="text-xs text-slate-500">اختر لوناً مميزاً لحلقة هذه العادة في الدائرة</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأيام المستهدفة أسبوعياً (1-7)</label>
                <input type="number" name="target_days_per_week" value="7" min="1" max="7" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وصف أو هدف العادة (اختياري)</label>
                <textarea name="description" rows="2" placeholder="ما الهدف من هذه العادة؟ وكيف ستساعدك؟" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeAddHabitModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                    حفظ العادة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Habit -->
<div id="editHabitModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-brand-600"></i>
                تعديل العادة
            </h3>
            <button onclick="closeEditHabitModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editHabitForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العادة باللغة العربية *</label>
                <input type="text" id="editHabitName" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم العادة بالإنجليزية</label>
                <input type="text" id="editHabitNameEn" name="name_en" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">لون العادة في الدائرة</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="editHabitColor" name="color" class="w-10 h-10 rounded-lg cursor-pointer border border-slate-300 dark:border-slate-600 bg-transparent">
                    <span class="text-xs text-slate-500">تغيير لون الحلقة في الدائرة</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأيام المستهدفة أسبوعياً (1-7)</label>
                <input type="number" id="editHabitTarget" name="target_days_per_week" min="1" max="7" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وصف العادة</label>
                <textarea id="editHabitDesc" name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditHabitModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                    تحديث العادة
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddHabitModal() {
        document.getElementById('addHabitModal').classList.remove('hidden');
    }
    function closeAddHabitModal() {
        document.getElementById('addHabitModal').classList.add('hidden');
    }

    function openEditHabitModal(habit) {
        document.getElementById('editHabitName').value = habit.name;
        document.getElementById('editHabitNameEn').value = habit.name_en || '';
        document.getElementById('editHabitColor').value = habit.color;
        document.getElementById('editHabitTarget').value = habit.target_days_per_week;
        document.getElementById('editHabitDesc').value = habit.description || '';

        const form = document.getElementById('editHabitForm');
        form.action = `/habits/${habit.id}`;

        document.getElementById('editHabitModal').classList.remove('hidden');
    }
    function closeEditHabitModal() {
        document.getElementById('editHabitModal').classList.add('hidden');
    }

    function toggleExcuse(excuseId, btn) {
        fetch(`/excuses/${excuseId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.overcame ? 'رائع! تم التغلب على هذا العذر 💪' : 'تم تغيير الحالة', 'success');
                setTimeout(() => window.location.reload(), 400);
            }
        });
    }

    // Habit Reordering (Drag & Drop + Up/Down buttons)
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('habitsListContainer');
        if (!container) return;

        let draggedItem = null;

        container.addEventListener('dragstart', function(e) {
            const item = e.target.closest('.habit-item');
            if (!item) return;
            draggedItem = item;
            item.classList.add('opacity-40', 'border-brand-500', 'scale-[0.98]');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.dataset.habitId);
        });

        container.addEventListener('dragend', function(e) {
            const item = e.target.closest('.habit-item');
            if (item) {
                item.classList.remove('opacity-40', 'border-brand-500', 'scale-[0.98]');
            }
            draggedItem = null;
            saveCurrentHabitsOrder();
        });

        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const target = e.target.closest('.habit-item');
            if (!target || target === draggedItem) return;

            const rect = target.getBoundingClientRect();
            const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            container.insertBefore(draggedItem, next ? target.nextSibling : target);
            updateHabitsUI();
        });
    });

    function moveHabitUp(btn, habitId) {
        const item = btn.closest('.habit-item');
        if (!item) return;
        const prev = item.previousElementSibling;
        if (prev && prev.classList.contains('habit-item')) {
            item.parentNode.insertBefore(item, prev);
            updateHabitsUI();
            saveCurrentHabitsOrder();
        }
    }

    function moveHabitDown(btn, habitId) {
        const item = btn.closest('.habit-item');
        if (!item) return;
        const next = item.nextElementSibling;
        if (next && next.classList.contains('habit-item')) {
            item.parentNode.insertBefore(next, item);
            updateHabitsUI();
            saveCurrentHabitsOrder();
        }
    }

    function updateHabitsUI() {
        const container = document.getElementById('habitsListContainer');
        if (!container) return;
        const items = container.querySelectorAll('.habit-item');
        items.forEach((item, index) => {
            const badge = item.querySelector('.habit-badge');
            if (badge) badge.textContent = index + 1;

            const upBtn = item.querySelector('.btn-move-up');
            const downBtn = item.querySelector('.btn-move-down');
            if (upBtn) upBtn.disabled = (index === 0);
            if (downBtn) downBtn.disabled = (index === items.length - 1);
        });
    }

    let isSavingOrder = false;
    function saveCurrentHabitsOrder() {
        if (isSavingOrder) return;
        const container = document.getElementById('habitsListContainer');
        if (!container) return;

        const items = container.querySelectorAll('.habit-item');
        const habitIds = Array.from(items).map(item => parseInt(item.dataset.habitId)).filter(id => !isNaN(id));

        if (habitIds.length === 0) return;

        isSavingOrder = true;
        fetch('{{ route("habits.reorder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ habit_ids: habitIds })
        })
        .then(r => r.json())
        .then(data => {
            isSavingOrder = false;
            if (data.success) {
                showToast(data.message || 'تم تحديث ترتيب العادات في الدائرة بنجاح! ✓', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 400);
            }
        })
        .catch(err => {
            isSavingOrder = false;
            console.error('Reorder error:', err);
            showToast('حدث خطأ أثناء حفظ الترتيب', 'error');
        });
    }
</script>
@endpush
@endsection
