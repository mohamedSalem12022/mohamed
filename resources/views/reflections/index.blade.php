@extends('layouts.app')

@section('title', 'الملاحظات والتأملات اليومية — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2 font-mono">
                <i class="fa-solid fa-feather-pointed text-brand-600"></i>
                NOTE & REFLECTIONS
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                سجل تأملاتك، إنجازاتك اليومية، وما تعلمته لبناء الانضباط الذاتي المستمر
            </p>
        </div>

        <!-- Date Picker Form -->
        <form action="{{ route('reflections.index') }}" method="GET" class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-semibold">تاريخ اليوم:</span>
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="px-3 py-1.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
            @if($selectedDate !== date('Y-m-d'))
            <a href="{{ route('reflections.index') }}" class="px-2.5 py-1.5 text-xs font-bold rounded-xl bg-brand-50 dark:bg-brand-950 text-brand-600 border border-brand-200 dark:border-brand-800">
                اليوم
            </a>
            @endif
        </form>
    </div>

    <!-- 2 Column Layout: Reflection Journal Editor (Lined Paper) & History Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Column 1 (Left 7 cols): Today's Notebook Page -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 dark:border-slate-700 space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-brand-500"></span>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider font-mono">
                        JOURNAL ENTRY — {{ $selectedDate }}
                    </h2>
                </div>
                <span class="text-xs text-slate-400 font-medium">
                    {{ \Carbon\Carbon::parse($selectedDate)->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}
                </span>
            </div>

            <form action="{{ route('reflections.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <!-- 1. Notes & Daily Musings (Lined paper design) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                        <span>ملاحظات وتأملات اليوم (Notes & Reflections) *</span>
                        <span class="text-[10px] text-slate-400 font-normal">كيف كان يومك وما الذي لفت انتباهك؟</span>
                    </label>
                    <textarea name="notes" rows="5" placeholder="اكتب بحرية عن يومك، التزامك بالعادات، التحديات التي واجهتك، ومشاعرك..." class="lined-paper w-full rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 leading-8">{{ $reflection->notes ?? '' }}</textarea>
                </div>

                <!-- 2. Best Win of the Day -->
                <div class="p-4 rounded-2xl bg-brand-50/50 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-900/60 space-y-1.5">
                    <label class="block text-xs font-black text-brand-800 dark:text-brand-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-trophy text-brand-600"></i>
                        أفضل إنجاز حققته اليوم (Best Win)
                    </label>
                    <input type="text" name="best_win" value="{{ $reflection->best_win ?? '' }}" placeholder="مثال: الاستيقاظ 6 صباحاً بدون منبه، أو إنهاء مهمة صعبة كنت أؤجلها..." class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-brand-200 dark:border-brand-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- 3. Lessons Learned -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 space-y-1.5">
                    <label class="block text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-lightbulb text-brand-600"></i>
                        ما تعلمته اليوم (Lessons Learned)
                    </label>
                    <input type="text" name="lessons_learned" value="{{ $reflection->lessons_learned ?? '' }}" placeholder="مثال: النوم المبكر يرفع تركيزي بنسبة 80% في الصباح..." class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- 4. Discipline Rating (1 to 5 Stars) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        تقييم مستوى انضباطك اليوم (Discipline Score)
                    </label>
                    <div class="flex items-center gap-3">
                        @for($r = 1; $r <= 5; $r++)
                        <label class="cursor-pointer flex items-center gap-1 p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-400 transition">
                            <input type="radio" name="rating" value="{{ $r }}" {{ ($reflection->rating ?? 5) == $r ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-bold font-mono text-slate-700 dark:text-slate-200">{{ $r }} ★</span>
                        </label>
                        @endfor
                    </div>
                </div>

                <!-- 5. Tomorrow's Improvements -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                        <span>أمور أريد تحسينها غداً (Tomorrow's Improvements)</span>
                        <span class="text-[10px] text-slate-400">كن محدداً وقابلاً للتطبيق</span>
                    </label>
                    <textarea name="tomorrow_improvements" rows="2" placeholder="ما الشيء الوحيد الذي إذا فعلته غداً سيجعل يومك أفضل؟" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">{{ $reflection->tomorrow_improvements ?? '' }}</textarea>
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button type="submit" class="px-6 py-2.5 text-xs sm:text-sm font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md shadow-brand-500/20 transition-all hover:scale-[1.02]">
                        <i class="fa-solid fa-floppy-disk ml-1"></i>
                        حفظ تأملات هذا اليوم
                    </button>
                </div>
            </form>
        </div>

        <!-- Column 2 (Right 5 cols): Reflection History Timeline -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">سجل التأملات السابقة</h3>
                </div>
                <span class="text-xs text-slate-400 font-semibold">
                    {{ $history->total() }} تأملات
                </span>
            </div>

            <div class="space-y-3">
                @forelse($history as $item)
                @php
                    $isCurrentSelected = ($item->date->format('Y-m-d') === $selectedDate);
                @endphp
                <a href="{{ route('reflections.index', ['date' => $item->date->format('Y-m-d')]) }}" class="block p-4 rounded-2xl border {{ $isCurrentSelected ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/30 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40' }} hover:border-brand-400 transition">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">
                            {{ $item->date->format('Y-m-d') }}
                        </span>
                        <div class="flex items-center text-amber-500 text-[10px] font-mono font-bold">
                            @for($i = 0; $i < ($item->rating ?? 5); $i++)
                                ★
                            @endfor
                        </div>
                    </div>

                    @if($item->best_win)
                    <p class="text-xs font-bold text-teal-700 dark:text-teal-300 truncate">
                        🏆 {{ $item->best_win }}
                    </p>
                    @endif

                    @if($item->notes)
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-1">
                        {{ $item->notes }}
                    </p>
                    @endif
                </a>
                @empty
                <div class="text-center py-8 text-slate-400">
                    <i class="fa-solid fa-book-open text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                    <p class="text-xs">لم تسجل أي تأملات سابقة بعد.</p>
                </div>
                @endforelse

                <div class="pt-2">
                    {{ $history->links() }}
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
