@extends('layouts.app')

@section('title', 'إدارة المهام — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Top Header: Stats & Add Task Button -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-brand-600"></i>
                إدارة المهام اليومية والمشاريع
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                نظّم مهامك، حدد أولوياتك، وتابع نسبة إنجازك اليومية والأسبوعية
            </p>
        </div>

        <button onclick="openAddTaskModal()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>إضافة مهمة جديدة</span>
        </button>
    </div>

    <!-- 4 Stats Cards for Tasks -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('tasks.index') }}" class="p-4 rounded-xl bg-white dark:bg-slate-800 border {{ $status === 'all' ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-sm transition hover:border-brand-400">
            <span class="text-xs font-bold text-slate-400">إجمالي المهام</span>
            <p class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-1" id="overallTotalCount">{{ $totalCount }}</p>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-800 border {{ $status === 'pending' ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-sm transition hover:border-brand-400">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">قيد الانتظار</span>
            <p class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-1" id="overallPendingCount">{{ $pendingCount }}</p>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-800 border {{ $status === 'completed' ? 'border-teal-500 ring-2 ring-teal-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-sm transition hover:border-teal-400">
            <span class="text-xs font-bold text-teal-600">المهام المكتملة</span>
            <p class="text-2xl font-black text-teal-600 font-mono mt-1" id="overallCompletedCount">{{ $completedCount }}</p>
        </a>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400">نسبة الإنجاز</span>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-2xl font-black text-brand-600 font-mono" id="overallRateText">{{ $completionRate }}%</p>
                <div class="flex-1 bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                    <div id="overallRateBar" class="bg-brand-500 h-full rounded-full transition-all duration-300" style="width: {{ $completionRate }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
        <form action="{{ route('tasks.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث عن مهمة بالاسم أو الوصف..." class="w-full pl-3 pr-9 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400 text-xs"></i>
            </div>

            <!-- Priority Filter -->
            <div>
                <select name="priority" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">كل الأولويات</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>أولوية عالية 📌</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>أولوية متوسطة ⚡</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>أولوية منخفضة ☕</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">كل التصنيفات</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter -->
            <div>
                <select name="date_filter" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">كل التواريخ</option>
                    <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>مهام اليوم</option>
                    <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>مهام هذا الأسبوع</option>
                    <option value="overdue" {{ request('date_filter') == 'overdue' ? 'selected' : '' }}>مهام متأخرة ⚠️</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Tasks List Container -->
    <div class="space-y-3">
        @forelse($tasks as $task)
        @php
            $isDone = ($task->status === 'completed');
            $totalSub = $task->subtasks->count();
            $completedSub = $task->subtasks->where('is_completed', true)->count();
            $subRate = $totalSub > 0 ? round(($completedSub / $totalSub) * 100) : 0;

            $priorityColors = [
                'high' => 'bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-950/40 dark:border-rose-900',
                'medium' => 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-950/40 dark:border-amber-900',
                'low' => 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-900',
            ];
            $priorityLabels = [
                'high' => 'عالية',
                'medium' => 'متوسطة',
                'low' => 'منخفضة',
            ];
        @endphp
        <div id="taskCard-{{ $task->id }}" class="p-4 rounded-2xl bg-white dark:bg-slate-800 border {{ $isDone ? 'border-teal-200 dark:border-teal-900 bg-teal-50/20 dark:bg-teal-950/10' : 'border-slate-200 dark:border-slate-700' }} shadow-sm hover:shadow transition flex flex-col gap-3 group">
            
            <!-- Main Task Row -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                
                <!-- Left Info & Checkbox -->
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <button type="button" onclick="toggleTaskStatus({{ $task->id }}, this)" id="taskCheck-{{ $task->id }}" class="mt-1 w-6 h-6 rounded-lg border-2 {{ $isDone ? 'bg-teal-600 border-teal-600 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent hover:border-teal-500' }} flex items-center justify-center text-xs transition shrink-0 shadow-sm" title="تبديل حالة الإنجاز">
                        <i class="fa-solid fa-check"></i>
                    </button>

                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 id="taskTitle-{{ $task->id }}" class="text-sm font-bold {{ $isDone ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-900 dark:text-white' }} truncate">
                                {{ $task->title }}
                            </h3>

                            <!-- Priority Badge -->
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $priorityColors[$task->priority] ?? '' }}">
                                {{ $priorityLabels[$task->priority] ?? $task->priority }}
                            </span>

                            <!-- Category Badge -->
                            @if($task->category)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white shadow-xs" style="background-color: {{ $task->category->color }}">
                                {{ $task->category->name }}
                            </span>
                            @endif

                            <!-- Recurrence Badge -->
                            @if($task->recurrence && $task->recurrence !== 'none')
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800 flex items-center gap-1">
                                <i class="fa-solid fa-arrows-rotate text-[8px]"></i>
                                {{ $task->recurrence === 'daily' ? 'يومية' : ($task->recurrence === 'weekly' ? 'أسبوعية' : 'شهرية') }}
                            </span>
                            @endif
                        </div>

                        @if($task->description)
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                            {{ $task->description }}
                        </p>
                        @endif

                        <!-- Date & Completed Timestamp info -->
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400 pt-0.5">
                            @if($task->due_date)
                            <span class="flex items-center gap-1 {{ ($task->due_date < date('Y-m-d') && !$isDone) ? 'text-rose-500 font-bold' : '' }}">
                                <i class="fa-regular fa-calendar text-[10px]"></i>
                                {{ $task->due_date->format('Y-m-d') }}
                                @if($task->due_time)
                                ({{ substr($task->due_time, 0, 5) }})
                                @endif
                            </span>
                            @endif

                            <span id="taskCompletedInfo-{{ $task->id }}" class="{{ $isDone && $task->completed_at ? '' : 'hidden' }} text-teal-600 dark:text-teal-400 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                أُنجزت في: <span id="taskCompletedDate-{{ $task->id }}">{{ $task->completed_at ? $task->completed_at->format('Y-m-d H:i') : '' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action buttons (Edit, Delete) -->
                <div class="flex items-center gap-1.5 self-end sm:self-center shrink-0">
                    <button onclick="openEditTaskModal({{ json_encode($task) }})" class="p-2 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="تعديل">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="حذف">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Subtasks Bar / Controls Footer -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSubtasksPanel({{ $task->id }})" class="flex items-center gap-1.5 font-bold text-slate-600 dark:text-slate-300 hover:text-brand-600 transition" title="إظهار / إخفاء المهام الفرعية">
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" id="subtasksChevron-{{ $task->id }}"></i>
                        <span>المهام الفرعية</span>
                    </button>

                    <span id="subtasksSummaryBadge-{{ $task->id }}" class="{{ $totalSub > 0 ? '' : 'hidden' }} text-[11px] font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 px-2.5 py-0.5 rounded-full border border-teal-200 dark:border-teal-800">
                        {{ $completedSub }} من {{ $totalSub }} مهام مكتملة — {{ $subRate }}%
                    </span>
                </div>

                <button type="button" onclick="focusAddSubtask({{ $task->id }})" class="text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 flex items-center gap-1.5 px-2.5 py-1 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-950/40 transition">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>إضافة مهمة فرعية</span>
                </button>
            </div>

            <!-- Subtasks Collapsible Panel -->
            <div id="subtasksPanel-{{ $task->id }}" class="space-y-2.5 mt-1 {{ $totalSub > 0 ? '' : 'hidden' }}">
                <!-- Mini Progress Bar -->
                <div id="subtasksProgressWrapper-{{ $task->id }}" class="{{ $totalSub > 0 ? '' : 'hidden' }} w-full bg-slate-100 dark:bg-slate-700/60 h-1.5 rounded-full overflow-hidden">
                    <div id="subtasksProgressBar-{{ $task->id }}" class="bg-teal-500 h-full rounded-full transition-all duration-300" style="width: {{ $subRate }}%;"></div>
                </div>

                <!-- Subtasks List -->
                <div id="subtasksList-{{ $task->id }}" class="space-y-1.5">
                    @foreach($task->subtasks as $subtask)
                    <div id="subtaskItem-{{ $subtask->id }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60 hover:border-teal-400 dark:hover:border-teal-500 transition text-xs group/sub">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <button type="button" onclick="toggleSubtaskStatus({{ $subtask->id }})" id="subtaskCheck-{{ $subtask->id }}" class="w-4 h-4 rounded border {{ $subtask->is_completed ? 'bg-teal-600 border-teal-600 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent hover:border-teal-500' }} flex items-center justify-center text-[9px] transition shrink-0" title="تبديل حالة الإنجاز">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <span id="subtaskTitle-{{ $subtask->id }}" class="font-medium {{ $subtask->is_completed ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-800 dark:text-slate-200' }} truncate">
                                {{ $subtask->title }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 opacity-70 group-hover/sub:opacity-100 transition shrink-0">
                            <button type="button" onclick="openEditSubtaskModal({{ $subtask->id }}, '{{ addslashes($subtask->title) }}')" class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-white dark:hover:bg-slate-800 transition" title="تعديل">
                                <i class="fa-solid fa-pen text-[10px]"></i>
                            </button>
                            <button type="button" onclick="deleteSubtask({{ $subtask->id }})" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-white dark:hover:bg-slate-800 transition" title="حذف">
                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Add Subtask Inline Form -->
                <form onsubmit="submitNewSubtask(event, {{ $task->id }})" class="flex items-center gap-2 pt-1">
                    <input type="text" id="newSubtaskInput-{{ $task->id }}" required placeholder="اكتب اسم المهمة الفرعية واضغط إضافة..." class="flex-1 px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1 shrink-0">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>إضافة</span>
                    </button>
                </form>
            </div>

        </div>
        @empty
        <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
            <i class="fa-solid fa-clipboard-check text-4xl text-slate-300 dark:text-slate-600 mb-3"></i>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">لا توجد مهام مطابقة للفلتر المحدد</h3>
            <p class="text-xs text-slate-400 mt-1">ابدأ بإضافة مهامك اليومية وتنظيم يومك بنجاح</p>
            <button onclick="openAddTaskModal()" class="mt-4 px-4 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                + إضافة مهمة جديدة الآن
            </button>
        </div>
        @endforelse

        <!-- Pagination -->
        <div class="pt-4">
            {{ $tasks->links() }}
        </div>
    </div>

</div>

<!-- Modal: Add New Task -->
<div id="addTaskModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-brand-600"></i>
                إضافة مهمة جديدة
            </h3>
            <button onclick="closeAddTaskModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان المهمة *</label>
                <input type="text" name="title" required placeholder="مثال: إنهاء تقرير المشروع، مراجعة الدرس الأول..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوصف أو تفاصيل المهمة</label>
                <textarea name="description" rows="2" placeholder="أي تفاصيل أو روابط مساعدة للمهمة..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ الاستحقاق</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وقت المهمة (اختياري)</label>
                    <input type="time" name="due_time" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأولوية</label>
                    <select name="priority" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="medium" selected>متوسطة ⚡</option>
                        <option value="high">عالية 📌</option>
                        <option value="low">منخفضة ☕</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">التصنيف</label>
                    <select name="category_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">بدون تصنيف</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">التكرار</label>
                    <select name="recurrence" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="none">بدون تكرار</option>
                        <option value="daily">يومياً</option>
                        <option value="weekly">أسبوعياً</option>
                        <option value="monthly">شهرياً</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeAddTaskModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                    حفظ المهمة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Task -->
<div id="editTaskModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-brand-600"></i>
                تعديل المهمة
            </h3>
            <button onclick="closeEditTaskModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editTaskForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان المهمة *</label>
                <input type="text" id="editTaskTitle" name="title" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوصف</label>
                <textarea id="editTaskDesc" name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ الاستحقاق</label>
                    <input type="date" id="editTaskDueDate" name="due_date" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوقت</label>
                    <input type="time" id="editTaskDueTime" name="due_time" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأولوية</label>
                    <select id="editTaskPriority" name="priority" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="low">منخفضة</option>
                        <option value="medium">متوسطة</option>
                        <option value="high">عالية</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">التصنيف</label>
                    <select id="editTaskCat" name="category_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">بدون تصنيف</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">التكرار</label>
                    <select id="editTaskRecurrence" name="recurrence" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="none">بدون تكرار</option>
                        <option value="daily">يومياً</option>
                        <option value="weekly">أسبوعياً</option>
                        <option value="monthly">شهرياً</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditTaskModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                    تحديث المهمة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Subtask -->
<div id="editSubtaskModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen text-brand-600 text-xs"></i>
                تعديل اسم المهمة الفرعية
            </h3>
            <button type="button" onclick="closeEditSubtaskModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form onsubmit="submitEditSubtask(event)" class="space-y-4">
            <input type="hidden" id="editSubtaskId">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المهمة الفرعية *</label>
                <input type="text" id="editSubtaskTitle" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditSubtaskModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                    حفظ التعديل
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirm Complete All Subtasks -->
<div id="confirmCompleteAllModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto text-xl shadow-sm">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                مهام فرعية غير مكتملة
            </h3>
            <p id="confirmCompleteAllMsg" class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                هذه المهمة تحتوي على مهام فرعية غير مكتملة. هل تريد إكمال جميع المهام الفرعية أيضاً؟
            </p>
        </div>
        <div class="flex items-center justify-center gap-2 pt-2">
            <button type="button" onclick="closeConfirmCompleteAllModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                إلغاء
            </button>
            <button type="button" id="confirmCompleteAllBtn" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition">
                نعم، إكمال الكل
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let pendingTaskIdToConfirm = null;

    function toggleTaskStatus(taskId, btn, confirmSubtasks = false) {
        fetch(`/tasks/${taskId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                confirm_complete_subtasks: confirmSubtasks
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.requires_confirmation) {
                pendingTaskIdToConfirm = taskId;
                document.getElementById('confirmCompleteAllMsg').textContent = data.message || 'هذه المهمة تحتوي على مهام فرعية غير مكتملة. هل تريد إكمال جميع المهام الفرعية أيضاً؟';
                document.getElementById('confirmCompleteAllModal').classList.remove('hidden');
            } else if (data.success) {
                showToast(data.status === 'completed' ? 'تم إنجاز المهمة بنجاح ✓' : 'تم تحويل المهمة إلى قيد الانتظار', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    document.getElementById('confirmCompleteAllBtn')?.addEventListener('click', function() {
        if (pendingTaskIdToConfirm) {
            const tid = pendingTaskIdToConfirm;
            closeConfirmCompleteAllModal();
            toggleTaskStatus(tid, null, true);
        }
    });

    function closeConfirmCompleteAllModal() {
        document.getElementById('confirmCompleteAllModal').classList.add('hidden');
        pendingTaskIdToConfirm = null;
    }

    function toggleSubtaskStatus(subtaskId) {
        fetch(`/subtasks/${subtaskId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.is_completed ? 'تم إنجاز المهمة الفرعية ✓' : 'تمت إعادة فتح المهمة الفرعية', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function submitNewSubtask(e, taskId) {
        e.preventDefault();
        const input = document.getElementById(`newSubtaskInput-${taskId}`);
        const title = input.value.trim();
        if (!title) return;

        fetch(`/tasks/${taskId}/subtasks`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ title: title })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'تمت إضافة المهمة الفرعية بنجاح ✓', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function openEditSubtaskModal(subtaskId, currentTitle) {
        document.getElementById('editSubtaskId').value = subtaskId;
        document.getElementById('editSubtaskTitle').value = currentTitle;
        document.getElementById('editSubtaskModal').classList.remove('hidden');
    }

    function closeEditSubtaskModal() {
        document.getElementById('editSubtaskModal').classList.add('hidden');
    }

    function submitEditSubtask(e) {
        e.preventDefault();
        const subtaskId = document.getElementById('editSubtaskId').value;
        const title = document.getElementById('editSubtaskTitle').value.trim();
        if (!title) return;

        fetch(`/subtasks/${subtaskId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ title: title })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeEditSubtaskModal();
                showToast(data.message || 'تم تعديل المهمة الفرعية بنجاح', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function deleteSubtask(subtaskId) {
        if (!confirm('هل أنت متأكد من حذف هذه المهمة الفرعية؟')) {
            return;
        }

        fetch(`/subtasks/${subtaskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'تم حذف المهمة الفرعية بنجاح', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function toggleSubtasksPanel(taskId) {
        const panel = document.getElementById(`subtasksPanel-${taskId}`);
        const chevron = document.getElementById(`subtasksChevron-${taskId}`);
        if (panel) {
            const isHidden = panel.classList.toggle('hidden');
            if (chevron) {
                chevron.classList.toggle('rotate-180', isHidden);
            }
        }
    }

    function focusAddSubtask(taskId) {
        const panel = document.getElementById(`subtasksPanel-${taskId}`);
        const chevron = document.getElementById(`subtasksChevron-${taskId}`);
        if (panel && panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
        const input = document.getElementById(`newSubtaskInput-${taskId}`);
        if (input) {
            input.focus();
        }
    }

    function openAddTaskModal() {
        document.getElementById('addTaskModal').classList.remove('hidden');
    }
    function closeAddTaskModal() {
        document.getElementById('addTaskModal').classList.add('hidden');
    }

    function openEditTaskModal(task) {
        document.getElementById('editTaskTitle').value = task.title;
        document.getElementById('editTaskDesc').value = task.description || '';
        document.getElementById('editTaskDueDate').value = task.due_date ? task.due_date.substring(0, 10) : '';
        document.getElementById('editTaskDueTime').value = task.due_time ? task.due_time.substring(0, 5) : '';
        document.getElementById('editTaskPriority').value = task.priority;
        document.getElementById('editTaskCat').value = task.category_id || '';
        document.getElementById('editTaskRecurrence').value = task.recurrence || 'none';

        const form = document.getElementById('editTaskForm');
        form.action = `/tasks/${task.id}`;

        document.getElementById('editTaskModal').classList.remove('hidden');
    }
    function closeEditTaskModal() {
        document.getElementById('editTaskModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
