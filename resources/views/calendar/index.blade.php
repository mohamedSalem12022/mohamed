@extends('layouts.app')

@section('title', 'التقويم الذكي — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Header & Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xl shadow-md shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        التقويم
                    </h1>
                    <span class="text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/70 px-2 py-0.5 rounded-full border border-brand-200 dark:border-brand-800">
                        Smart Calendar
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    استعراض المواعيد، المهام المستحقة، وجلسات التركيز في واجهة واحدة متكاملة.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <!-- View Selector (Month, Week, Day) -->
            <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600">
                <a href="{{ route('calendar.index', ['view' => 'month', 'date' => $currentDate->toDateString()]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $view === 'month' ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                    شهري
                </a>
                <a href="{{ route('calendar.index', ['view' => 'week', 'date' => $currentDate->toDateString()]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $view === 'week' ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                    أسبوعي
                </a>
                <a href="{{ route('calendar.index', ['view' => 'day', 'date' => $currentDate->toDateString()]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $view === 'day' ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                    يومي
                </a>
            </div>

            <!-- Date Navigation Controls -->
            <div class="flex items-center gap-1.5">
                @php
                    $prevDate = $view === 'month' ? $currentDate->copy()->subMonth()->toDateString() : ($view === 'week' ? $currentDate->copy()->subWeek()->toDateString() : $currentDate->copy()->subDay()->toDateString());
                    $nextDate = $view === 'month' ? $currentDate->copy()->addMonth()->toDateString() : ($view === 'week' ? $currentDate->copy()->addWeek()->toDateString() : $currentDate->copy()->addDay()->toDateString());
                @endphp
                <a href="{{ route('calendar.index', ['view' => $view, 'date' => $prevDate]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="السابق">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
                <a href="{{ route('calendar.index', ['view' => $view, 'date' => \Carbon\Carbon::today()->toDateString()]) }}" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    اليوم
                </a>
                <a href="{{ route('calendar.index', ['view' => $view, 'date' => $nextDate]) }}" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="التالي">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
            </div>

            <!-- Add Event Button -->
            <button type="button" onclick="openCreateEventModal()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-105">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>حدث أو موعد جديد</span>
            </button>
        </div>
    </div>

    <!-- Active Month/Week Title Banner & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-regular fa-calendar text-brand-600"></i>
            @if($view === 'month')
                <span>{{ $currentDate->locale('ar')->isoFormat('MMMM YYYY') }}</span>
            @elseif($view === 'week')
                <span>أسبوع: {{ $currentDate->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->locale('ar')->isoFormat('D MMMM') }} — {{ $currentDate->copy()->endOfWeek(\Carbon\Carbon::FRIDAY)->locale('ar')->isoFormat('D MMMM YYYY') }}</span>
            @else
                <span>{{ $currentDate->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}</span>
            @endif
        </h2>

        <!-- Event Type Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-1">
            <button type="button" onclick="setCalendarFilter('all', this)" class="filter-pill px-3 py-1 rounded-xl text-xs font-bold transition bg-brand-600 text-white shadow-sm">
                الكل
            </button>
            <button type="button" onclick="setCalendarFilter('task', this)" class="filter-pill px-3 py-1 rounded-xl text-xs font-bold transition text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                📌 مهام
            </button>
            <button type="button" onclick="setCalendarFilter('meeting', this)" class="filter-pill px-3 py-1 rounded-xl text-xs font-bold transition text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                💼 اجتماعات
            </button>
            <button type="button" onclick="setCalendarFilter('focus', this)" class="filter-pill px-3 py-1 rounded-xl text-xs font-bold transition text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                ⚡ جلسات تركيز
            </button>
            <button type="button" onclick="setCalendarFilter('personal', this)" class="filter-pill px-3 py-1 rounded-xl text-xs font-bold transition text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                🌟 شخصية
            </button>
        </div>
    </div>

    <!-- Calendar View Container -->
    <div id="calendarLoading" class="py-16 text-center text-slate-400">
        <i class="fa-solid fa-spinner fa-spin text-2xl text-brand-600 mb-2"></i>
        <p class="text-xs font-semibold">جاري تحميل التقويم والأحداث...</p>
    </div>

    <div id="calendarContainer" class="hidden">
        <!-- Rendered dynamically by JavaScript based on $view and fetched events -->
    </div>

</div>

<!-- Modal: Create / Edit Event -->
<div id="eventModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden animate-fade-in">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 id="eventModalTitle" class="text-base font-bold text-slate-900 dark:text-white">إضافة حدث جديد للتقويم</h3>
            <button type="button" onclick="closeEventModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="eventForm" onsubmit="submitCalendarEvent(event)" class="p-5 space-y-4">
            <input type="hidden" id="eventModalId" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان الحدث أو الموعد *</label>
                <input type="text" id="eventTitle" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500" placeholder="مثال: اجتماع فريق العمل أو جلسة قراءة...">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نوع الحدث</label>
                    <select id="eventType" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value="personal">شخصي (Personal)</option>
                        <option value="meeting">اجتماع / موعد (Meeting)</option>
                        <option value="focus">جلسة تركيز (Focus Session)</option>
                        <option value="task">مهمة (Task)</option>
                        <option value="reminder">تذكير (Reminder)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اللون المميز</label>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="color" id="eventColor" value="#0d9488" class="w-10 h-8 rounded-lg cursor-pointer border border-slate-300 dark:border-slate-600 bg-transparent">
                        <span class="text-xs text-slate-400">اختر لوناً للحدث</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="eventAllDay" onchange="toggleAllDayInputs()" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                <label for="eventAllDay" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    طوال اليوم (All Day)
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ ووقت البدء *</label>
                    <input type="datetime-local" id="eventStartTime" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ ووقت الانتهاء *</label>
                    <input type="datetime-local" id="eventEndTime" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
            </div>

            <!-- Link to existing Task (Optional) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ربط بمهمة حالية (اختياري)</label>
                <select id="eventTaskId" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500">
                    <option value="">بدون ربط بمهمة</option>
                    @foreach($pendingTasks as $pTask)
                        <option value="{{ $pTask->id }}">{{ $pTask->title }} ({{ $pTask->priority }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Reminder Setting -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    <i class="fa-solid fa-bell text-amber-500 ml-1"></i> ضبط تذكير مسبق
                </label>
                <select id="eventReminderMinutes" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500">
                    <option value="">بدون تذكير</option>
                    <option value="10">قبل 10 دقائق</option>
                    <option value="30">قبل 30 دقيقة</option>
                    <option value="60">قبل ساعة واحدة</option>
                    <option value="1440">قبل يوم كامل (24 ساعة)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوصف والمكان</label>
                <input type="text" id="eventLocation" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500" placeholder="المكان أو رابط الاجتماع أو ملاحظة...">
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" id="btnDeleteEvent" onclick="deleteCurrentEvent()" class="hidden px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    <i class="fa-solid fa-trash-can ml-1"></i> حذف الحدث
                </button>
                <div class="flex items-center gap-2 mr-auto">
                    <button type="button" onclick="closeEventModal()" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition">
                        حفظ الموعد
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const currentView = "{{ $view }}";
    const selectedDateStr = "{{ $currentDate->toDateString() }}";
    let activeFilter = 'all';
    let loadedEvents = [];

    document.addEventListener('DOMContentLoaded', () => {
        fetchCalendarEvents();
    });

    function setCalendarFilter(type, btn) {
        activeFilter = type;
        document.querySelectorAll('.filter-pill').forEach(b => {
            b.classList.remove('bg-brand-600', 'text-white', 'shadow-sm');
            b.classList.add('text-slate-600', 'dark:text-slate-300');
        });
        btn.classList.add('bg-brand-600', 'text-white', 'shadow-sm');
        btn.classList.remove('text-slate-600', 'dark:text-slate-300');
        renderCalendar();
    }

    function fetchCalendarEvents() {
        document.getElementById('calendarLoading').classList.remove('hidden');
        document.getElementById('calendarContainer').classList.add('hidden');

        // calculate start and end based on currentView and selectedDateStr
        const dt = new Date(selectedDateStr);
        let startStr = new Date(dt.getFullYear(), dt.getMonth() - 1, 1).toISOString();
        let endStr = new Date(dt.getFullYear(), dt.getMonth() + 2, 0).toISOString();

        fetch(`{{ route('calendar.events') }}?start=${startStr}&end=${endStr}`)
            .then(r => r.json())
            .then(data => {
                loadedEvents = data;
                document.getElementById('calendarLoading').classList.add('hidden');
                document.getElementById('calendarContainer').classList.remove('hidden');
                renderCalendar();
            })
            .catch(() => {
                document.getElementById('calendarLoading').innerHTML = '<p class="text-rose-500 text-xs">تعذر تحميل أحداث التقويم</p>';
            });
    }

    function renderCalendar() {
        const container = document.getElementById('calendarContainer');
        const filtered = activeFilter === 'all' 
            ? loadedEvents 
            : loadedEvents.filter(e => e.type === activeFilter);

        if (currentView === 'month') {
            container.innerHTML = buildMonthView(filtered);
        } else if (currentView === 'week') {
            container.innerHTML = buildWeekView(filtered);
        } else {
            container.innerHTML = buildDayView(filtered);
        }
    }

    function buildMonthView(events) {
        const dt = new Date(selectedDateStr);
        const year = dt.getFullYear();
        const month = dt.getMonth(); // 0-indexed

        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);
        const daysInMonth = lastDayOfMonth.getDate();

        // Arabic week starts on Saturday (6)
        // JS getDay(): 0 is Sunday, 1 is Monday... 6 is Saturday
        let startDayIndex = (firstDayOfMonth.getDay() + 1) % 7; // Saturday = 0, Sunday = 1, ... Friday = 6

        const dayNames = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

        let html = `
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-center">
                    ${dayNames.map(d => `<div class="py-3 text-xs font-bold text-slate-500 dark:text-slate-400">${d}</div>`).join('')}
                </div>
                <div class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-700/60 divide-x-reverse">
        `;

        // Empty cells before start of month
        for (let i = 0; i < startDayIndex; i++) {
            html += `<div class="min-h-[110px] p-2 bg-slate-50/40 dark:bg-slate-900/20 text-slate-300 dark:text-slate-600"></div>`;
        }

        const todayDateStr = new Date().toISOString().substring(0, 10);

        for (let day = 1; day <= daysInMonth; day++) {
            const currentCellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = currentCellDateStr === todayDateStr;
            const isSelected = currentCellDateStr === selectedDateStr;

            // Find events on this day
            const dayEvents = events.filter(e => {
                const eStart = e.start ? e.start.substring(0, 10) : '';
                return eStart === currentCellDateStr;
            });

            html += `
                <div onclick="clickCalendarDay('${currentCellDateStr}')" class="min-h-[110px] p-2 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition cursor-pointer flex flex-col justify-between group ${isToday ? 'bg-brand-50/30 dark:bg-brand-950/20' : ''}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${isToday ? 'bg-brand-600 text-white' : 'text-slate-700 dark:text-slate-300 group-hover:text-brand-600'}">
                            ${day}
                        </span>
                        <button type="button" onclick="event.stopPropagation(); quickAddEventOnDay('${currentCellDateStr}')" class="opacity-0 group-hover:opacity-100 w-5 h-5 rounded hover:bg-brand-100 dark:hover:bg-slate-700 text-brand-600 text-[10px] flex items-center justify-center transition" title="إضافة موعد في هذا اليوم">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <div class="space-y-1 flex-1 overflow-y-auto max-h-20 scrollbar-none">
                        ${dayEvents.slice(0, 3).map(e => `
                            <div onclick="event.stopPropagation(); openEditEventModal(${JSON.stringify(e).replace(/"/g, '&quot;')})" class="px-2 py-0.5 rounded-md text-[10px] font-bold truncate transition hover:opacity-80" style="background-color: ${e.color || '#0d9488'}20; color: ${e.color || '#0d9488'}; border-right: 3px solid ${e.color || '#0d9488'};">
                                ${e.is_task ? '📌 ' : ''}${e.title}
                            </div>
                        `).join('')}
                        ${dayEvents.length > 3 ? `<span class="text-[9px] font-bold text-slate-400 block text-center">+${dayEvents.length - 3} المزيد</span>` : ''}
                    </div>
                </div>
            `;
        }

        // Fill remaining row cells to balance grid
        const totalRendered = startDayIndex + daysInMonth;
        const remainder = totalRendered % 7;
        if (remainder > 0) {
            for (let i = remainder; i < 7; i++) {
                html += `<div class="min-h-[110px] p-2 bg-slate-50/40 dark:bg-slate-900/20"></div>`;
            }
        }

        html += `
                </div>
            </div>
        `;
        return html;
    }

    function buildWeekView(events) {
        const dt = new Date(selectedDateStr);
        // Start of week (Saturday)
        const dayOfWeek = (dt.getDay() + 1) % 7;
        const startOfWeek = new Date(dt);
        startOfWeek.setDate(dt.getDate() - dayOfWeek);

        const dayNames = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];
        const weekDays = [];

        for (let i = 0; i < 7; i++) {
            const wDate = new Date(startOfWeek);
            wDate.setDate(startOfWeek.getDate() + i);
            const dateStr = wDate.toISOString().substring(0, 10);
            weekDays.push({
                name: dayNames[i],
                dateStr: dateStr,
                dayNum: wDate.getDate(),
                isToday: dateStr === new Date().toISOString().substring(0, 10)
            });
        }

        let html = `
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-center">
                    ${weekDays.map(d => `
                        <div class="py-3 px-2 border-r border-slate-100 dark:border-slate-700/50">
                            <span class="block text-xs font-bold text-slate-500 dark:text-slate-400">${d.name}</span>
                            <span class="inline-block mt-1 w-7 h-7 leading-7 rounded-full text-xs font-black ${d.isToday ? 'bg-brand-600 text-white' : 'text-slate-800 dark:text-slate-200'}">${d.dayNum}</span>
                        </div>
                    `).join('')}
                </div>
                <div class="grid grid-cols-7 min-h-[450px] divide-x divide-slate-100 dark:divide-slate-700/60 divide-x-reverse">
                    ${weekDays.map(d => {
                        const dayEvents = events.filter(e => (e.start || '').substring(0, 10) === d.dateStr);
                        return `
                            <div class="p-2 space-y-2 hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition group">
                                <button type="button" onclick="quickAddEventOnDay('${d.dateStr}')" class="w-full py-1 text-center rounded-lg border border-dashed border-slate-200 dark:border-slate-700 text-slate-400 hover:text-brand-600 hover:border-brand-400 text-[10px] transition font-bold opacity-0 group-hover:opacity-100">
                                    + إضافة
                                </button>
                                ${dayEvents.map(e => `
                                    <div onclick="openEditEventModal(${JSON.stringify(e).replace(/"/g, '&quot;')})" class="p-2 rounded-xl text-xs font-bold shadow-sm transition hover:scale-[1.02] cursor-pointer" style="background-color: ${e.color || '#0d9488'}18; border-right: 4px solid ${e.color || '#0d9488'}; color: ${e.color || '#0d9488'};">
                                        <div class="flex items-center justify-between text-[10px] opacity-80 mb-0.5">
                                            <span>${e.start ? e.start.substring(11, 16) : ''}</span>
                                            <span>${e.type}</span>
                                        </div>
                                        <p class="truncate text-slate-900 dark:text-white font-black">${e.title}</p>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
        return html;
    }

    function buildDayView(events) {
        const dayEvents = events.filter(e => (e.start || '').substring(0, 10) === selectedDateStr);
        
        let html = `
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">أحداث ومواعيد هذا اليوم (${dayEvents.length})</h3>
                    <button type="button" onclick="quickAddEventOnDay('${selectedDateStr}')" class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition">
                        + إضافة موعد لليوم
                    </button>
                </div>

                <div class="space-y-3">
                    ${dayEvents.length === 0 ? `
                        <div class="py-12 text-center text-slate-400">
                            <i class="fa-regular fa-calendar-check text-3xl mb-2 block"></i>
                            <p class="text-xs">لا توجد مواعيد مسجلة في هذا اليوم.</p>
                        </div>
                    ` : dayEvents.map(e => `
                        <div onclick="openEditEventModal(${JSON.stringify(e).replace(/"/g, '&quot;')})" class="flex items-start gap-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/60 hover:border-brand-400 transition cursor-pointer shadow-sm group">
                            <div class="w-14 text-center shrink-0 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700/60 font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                <span>${e.start ? e.start.substring(11, 16) : '--:--'}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: ${e.color || '#0d9488'};"></span>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">${e.title}</h4>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">${e.type}</span>
                                </div>
                                ${e.description ? `<p class="text-xs text-slate-500 dark:text-slate-400 mt-1">${e.description}</p>` : ''}
                                ${e.location ? `<p class="text-xs text-brand-600 mt-1 flex items-center gap-1"><i class="fa-solid fa-location-dot text-[10px]"></i> ${e.location}</p>` : ''}
                            </div>
                            <div class="text-slate-400 group-hover:text-brand-600 transition">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        return html;
    }

    function clickCalendarDay(dateStr) {
        window.location.href = `{{ route('calendar.index') }}?view=day&date=${dateStr}`;
    }

    function quickAddEventOnDay(dateStr) {
        openCreateEventModal();
        document.getElementById('eventStartTime').value = `${dateStr}T09:00`;
        document.getElementById('eventEndTime').value = `${dateStr}T10:00`;
    }

    function openCreateEventModal() {
        document.getElementById('eventModalTitle').textContent = 'إضافة حدث أو موعد جديد';
        document.getElementById('eventModalId').value = '';
        document.getElementById('eventTitle').value = '';
        document.getElementById('eventType').value = 'personal';
        document.getElementById('eventColor').value = '#0d9488';
        document.getElementById('eventAllDay').checked = false;
        document.getElementById('eventTaskId').value = '';
        document.getElementById('eventReminderMinutes').value = '30';
        document.getElementById('eventLocation').value = '';
        document.getElementById('btnDeleteEvent').classList.add('hidden');

        const now = new Date();
        const startIso = now.toISOString().substring(0, 16);
        now.setHours(now.getHours() + 1);
        const endIso = now.toISOString().substring(0, 16);
        document.getElementById('eventStartTime').value = startIso;
        document.getElementById('eventEndTime').value = endIso;

        document.getElementById('eventModal').classList.remove('hidden');
    }

    function openEditEventModal(e) {
        if (e.is_task) {
            window.location.href = '{{ route("tasks.index") }}';
            return;
        }

        document.getElementById('eventModalTitle').textContent = 'تعديل موعد التقويم';
        document.getElementById('eventModalId').value = e.id;
        document.getElementById('eventTitle').value = e.title;
        document.getElementById('eventType').value = e.type || 'personal';
        document.getElementById('eventColor').value = e.color || '#0d9488';
        document.getElementById('eventAllDay').checked = !!e.allDay;
        document.getElementById('eventTaskId').value = e.task_id || '';
        document.getElementById('eventLocation').value = e.location || e.description || '';
        document.getElementById('eventReminderMinutes').value = '';

        if (e.start) {
            document.getElementById('eventStartTime').value = e.start.substring(0, 16);
        }
        if (e.end) {
            document.getElementById('eventEndTime').value = e.end.substring(0, 16);
        }

        document.getElementById('btnDeleteEvent').classList.remove('hidden');
        document.getElementById('eventModal').classList.remove('hidden');
    }

    function closeEventModal() {
        document.getElementById('eventModal').classList.add('hidden');
    }

    function toggleAllDayInputs() {
        // all day toggle
    }

    function submitCalendarEvent(e) {
        e.preventDefault();
        const eventId = document.getElementById('eventModalId').value;
        const payload = {
            title: document.getElementById('eventTitle').value,
            event_type: document.getElementById('eventType').value,
            color: document.getElementById('eventColor').value,
            is_all_day: document.getElementById('eventAllDay').checked,
            start_time: document.getElementById('eventStartTime').value,
            end_time: document.getElementById('eventEndTime').value,
            task_id: document.getElementById('eventTaskId').value || null,
            description: document.getElementById('eventLocation').value,
            location: document.getElementById('eventLocation').value,
            reminder_minutes_before: document.getElementById('eventReminderMinutes').value || null
        };

        const url = eventId ? `/calendar/events/${eventId}` : '{{ route("calendar.store") }}';
        const method = eventId ? 'PUT' : 'POST';

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
                showToast(data.message || 'تم حفظ الحدث بنجاح ✓', 'success');
                closeEventModal();
                fetchCalendarEvents();
            }
        });
    }

    function deleteCurrentEvent() {
        const eventId = document.getElementById('eventModalId').value;
        if (!eventId || !confirm('هل أنت متأكد من حذف هذا الحدث من التقويم؟')) return;

        fetch(`/calendar/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'تم حذف الحدث من التقويم', 'success');
                closeEventModal();
                fetchCalendarEvents();
            }
        });
    }
</script>
@endpush
