@extends('layouts.app')

@section('title', 'المالية والمصروفات — MO Life OS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header & Action Controls -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    المالية والمصروفات
                    <span class="text-xs px-2.5 py-1 rounded-full bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border border-teal-200/50 dark:border-teal-800/40 font-medium">المرحلة الثالثة</span>
                </h1>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    إدارة الدخل والمصروفات، مراقبة الميزانية الشهرية، والتحليل المالي الذكي عبر MO AI.
                </p>
            </div>
        </div>

        <!-- Filter Controls & Actions: All on the EXACT SAME LEVEL -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3 overflow-x-auto pb-1 flex-nowrap">
            <!-- Month, Year & Currency Filter Form -->
            <form method="GET" action="{{ route('finances.index') }}" class="flex items-center gap-2 shrink-0 flex-nowrap">
                <select name="currency" onchange="this.form.submit()" class="h-9 px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 max-w-[160px] shrink-0">
                    @foreach($currencies as $code => $cName)
                        <option value="{{ $code }}" @selected($currency === $code)>{{ $code }} — {{ $cName }}</option>
                    @endforeach
                </select>

                <select name="month" onchange="this.form.submit()" class="h-9 px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 shrink-0">
                    <option value="all" @selected($month === 'all')>كامل السنة</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month !== 'all' && (int)$month === $m)>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->locale('ar')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" onchange="this.form.submit()" class="h-9 px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 shrink-0">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </form>

            <div class="flex items-center gap-2 shrink-0 flex-nowrap">
                <!-- Export PDF Button -->
                <a href="{{ route('finances.export-pdf', ['month' => $month, 'year' => $year, 'currency' => $currency]) }}" target="_blank" class="h-9 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/40 transition-colors flex items-center gap-1.5 shadow-2xs shrink-0 whitespace-nowrap" title="تصدير تقرير مالي رسمي بصيغة PDF">
                    <i class="fa-solid fa-file-pdf text-rose-600 dark:text-rose-400 text-sm"></i>
                    <span>تصدير PDF</span>
                </a>

                <!-- AI Advisor Button -->
                <button type="button" onclick="openAiInsightsModal()" class="h-9 px-3 py-1.5 rounded-xl text-xs font-semibold bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 text-teal-700 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/40 transition-colors flex items-center gap-1.5 shadow-2xs shrink-0 whitespace-nowrap">
                    <i class="fa-solid fa-wand-magic-sparkles text-teal-600 dark:text-teal-400"></i>
                    <span>تحليل MO AI</span>
                </button>

                <!-- Set Budget Button -->
                <button type="button" onclick="openBudgetModal()" class="h-9 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                    <i class="fa-solid fa-bullseye text-teal-600 dark:text-teal-400"></i>
                    <span>ضبط الميزانية</span>
                </button>

                <!-- Add Transaction Button -->
                <button type="button" onclick="openTransactionModal()" class="h-9 px-4 py-1.5 rounded-xl text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i>
                    <span>معاملة جديدة</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-teal-800 dark:text-teal-200 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-teal-600 dark:text-teal-400 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-base"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Key Performance Indicators (KPI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Income -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $isAnnual ? 'إجمالي الدخل للسنة' : 'إجمالي الدخل للشهر' }}</span>
                <div class="w-9 h-9 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-800 dark:text-slate-100 flex items-baseline gap-1.5">
                    <span>{{ number_format($incomeTotal, 2) }}</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $currency }}</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-xs">
                    @if($incomeChangePercent > 0)
                        <span class="text-teal-600 dark:text-teal-400 font-bold flex items-center gap-0.5">
                            <i class="fa-solid fa-arrow-up"></i> +{{ $incomeChangePercent }}%
                        </span>
                        <span class="text-slate-400">{{ $isAnnual ? 'عن السنة الماضية' : 'عن الشهر الماضي' }}</span>
                    @elseif($incomeChangePercent < 0)
                        <span class="text-rose-600 dark:text-rose-400 font-bold flex items-center gap-0.5">
                            <i class="fa-solid fa-arrow-down"></i> {{ $incomeChangePercent }}%
                        </span>
                        <span class="text-slate-400">{{ $isAnnual ? 'عن السنة الماضية' : 'عن الشهر الماضي' }}</span>
                    @else
                        <span class="text-slate-400">{{ $isAnnual ? 'نفس معدل السنة الماضية' : 'نفس معدل الشهر الماضي' }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Total Expenses -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $isAnnual ? 'إجمالي المصروفات للسنة' : 'إجمالي المصروفات للشهر' }}</span>
                <div class="w-9 h-9 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-800 dark:text-slate-100 flex items-baseline gap-1.5">
                    <span>{{ number_format($expenseTotal, 2) }}</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $currency }}</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-xs">
                    @if($expenseChangePercent > 0)
                        <span class="text-rose-600 dark:text-rose-400 font-bold flex items-center gap-0.5">
                            <i class="fa-solid fa-arrow-up"></i> +{{ $expenseChangePercent }}%
                        </span>
                        <span class="text-slate-400">زيادة إنفاق</span>
                    @elseif($expenseChangePercent < 0)
                        <span class="text-teal-600 dark:text-teal-400 font-bold flex items-center gap-0.5">
                            <i class="fa-solid fa-arrow-down"></i> {{ $expenseChangePercent }}%
                        </span>
                        <span class="text-slate-400">وفورات محققة</span>
                    @else
                        <span class="text-slate-400">مستقر مقارنة بالفترة السابقة</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Net Balance -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">الصافي (الفائض / العجز)</span>
                <div class="w-9 h-9 rounded-lg {{ $netBalance >= 0 ? 'bg-teal-500/10 text-teal-600 dark:text-teal-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }} flex items-center justify-center text-sm">
                    <i class="fa-solid {{ $netBalance >= 0 ? 'fa-scale-balanced' : 'fa-triangle-exclamation' }}"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black {{ $netBalance >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400' }} flex items-baseline gap-1.5">
                    <span>{{ ($netBalance >= 0 ? '+' : '') . number_format($netBalance, 2) }}</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $currency }}</span>
                </div>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    نسبة الادخار من الدخل: <strong class="text-slate-800 dark:text-slate-200">{{ $savingsRate }}%</strong>
                </div>
            </div>
        </div>

        <!-- 4. Budget Status -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $isAnnual ? 'إجمالي الميزانية السنوية' : 'الميزانية الشهرية العامة' }}</span>
                <button type="button" onclick="openBudgetModal()" class="text-xs text-teal-600 dark:text-teal-400 hover:underline">
                    {{ $budgetAmount > 0 ? 'تعديل' : 'تحديد' }}
                </button>
            </div>
            <div class="mt-3">
                @if($budgetAmount > 0)
                    <div class="text-2xl font-black text-slate-800 dark:text-slate-100 flex items-baseline gap-1.5">
                        <span>{{ number_format($budgetRemaining, 2) }}</span>
                        <span class="text-xs font-semibold text-slate-500">{{ $currency }} متبقٍ</span>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $budgetUsedPercent >= 100 ? 'bg-rose-500' : ($budgetUsedPercent >= 80 ? 'bg-amber-500' : 'bg-teal-600') }}" style="width: {{ min(100, $budgetUsedPercent) }}%"></div>
                        </div>
                        <div class="flex justify-between items-center text-[10px] text-slate-400 mt-1">
                            <span>استُهلك: {{ $budgetUsedPercent }}%</span>
                            <span>الميزانية: {{ number_format($budgetAmount, 0) }} {{ $currency }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-sm font-semibold text-slate-500 mt-1">لم تُحدد ميزانية بعد</div>
                    <button type="button" onclick="openBudgetModal()" class="mt-3 text-xs text-teal-600 dark:text-teal-400 font-bold flex items-center gap-1 hover:underline">
                        <i class="fa-solid fa-plus text-[10px]"></i> اضغط لتحديد سقف الميزانية
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Multi-Currency Tab / Notification if multiple currencies present -->
    @if(count($multiCurrencyBalances) > 1)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-coins text-teal-600 dark:text-teal-400"></i>
                <span>الأرصدة بالعملات الأخرى خلال هذه الفترة (عدم الخلط بين العملات):</span>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($multiCurrencyBalances as $cName => $cBal)
                    <a href="{{ route('finances.index', ['month' => $month, 'year' => $year, 'currency' => $cName]) }}" class="px-3 py-1.5 rounded-xl border text-xs transition-all flex items-center gap-2 {{ $currency === $cName ? 'bg-teal-50 dark:bg-teal-950/60 border-teal-500 text-teal-700 dark:text-teal-300 font-bold shadow-2xs' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-slate-300' }}">
                        <span>{{ $cName }} ({{ $cBal['name'] }}):</span>
                        <span class="font-semibold {{ $cBal['net'] >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ ($cBal['net'] >= 0 ? '+' : '') . number_format($cBal['net'], 2) }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Visual Analytics: Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Category Breakdown Doughnut Chart -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-teal-600 dark:text-teal-400"></i>
                <span>توزيع المصروفات حسب التصنيف</span>
            </h3>
            @if(empty($categoriesBreakdown))
                <div class="h-56 flex flex-col items-center justify-center text-slate-400 text-xs text-center p-4">
                    <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 dark:text-slate-700"></i>
                    <span>لا توجد مصروفات مسجلة {{ $isAnnual ? 'لهذه السنة' : 'لهذا الشهر' }} بعملة {{ $currency }}</span>
                </div>
            @else
                <div class="relative h-56 flex items-center justify-center">
                    <canvas id="categoryExpensesChart"></canvas>
                </div>
            @endif
        </div>

        <!-- 2. Categories Breakdown List & Budgets -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-teal-600 dark:text-teal-400"></i>
                    <span>تفاصيل الإنفاق والميزانيات المخصصة</span>
                </h3>
                <button type="button" onclick="openCategoryModal()" class="text-xs text-teal-600 dark:text-teal-400 hover:underline flex items-center gap-1 font-semibold">
                    <i class="fa-solid fa-plus text-[10px]"></i> إضافة تصنيف
                </button>
            </div>

            @if(empty($categoriesBreakdown))
                <div class="py-10 text-center text-slate-400 text-xs">
                    لم يتم تسجيل أي نفقات بعد. يمكنك البدء بإضافة معاملة مالية جديدة.
                </div>
            @else
                <div class="space-y-3.5 max-h-72 overflow-y-auto pr-1">
                    @foreach($categoriesBreakdown as $catItem)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200">
                                    <div class="w-6 h-6 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-[11px]">
                                        <i class="fa-solid {{ $catItem['icon'] }}"></i>
                                    </div>
                                    <span>{{ $catItem['name'] }}</span>
                                    @if($catItem['is_over_budget'])
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-sm bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 font-bold">تجاوز الميزانية!</span>
                                    @endif
                                </div>
                                <div class="text-left font-bold text-slate-800 dark:text-slate-100">
                                    <span>{{ number_format($catItem['spent'], 2) }} {{ $currency }}</span>
                                    <span class="text-slate-400 font-normal text-[11px]">({{ $catItem['percent_of_total'] }}%)</span>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                @php
                                    $progressPercent = $catItem['budget'] > 0 ? min(100, round(($catItem['spent'] / $catItem['budget']) * 100)) : $catItem['percent_of_total'];
                                @endphp
                                <div class="h-full rounded-full {{ $catItem['is_over_budget'] ? 'bg-rose-500' : 'bg-teal-600' }}" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            @if($catItem['budget'] > 0)
                                <div class="flex justify-between items-center text-[10px] text-slate-400 mt-1">
                                    <span>الميزانية المخصصة: {{ number_format($catItem['budget'], 2) }} {{ $currency }}</span>
                                    <span>المتبقي: {{ number_format($catItem['budget'] - $catItem['spent'], 2) }} {{ $currency }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Transactions List & Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <!-- Table Top Controls -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-list-ol text-teal-600 dark:text-teal-400"></i>
                    <span>سجل المعاملات المالية</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    جميع حركات الدخل والمصروفات المسجلة {{ $isAnnual ? 'لهذه السنة' : 'لهذا الشهر' }}.
                </p>
            </div>

            <!-- Inline Filters -->
            <form method="GET" action="{{ route('finances.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="currency" value="{{ $currency }}">

                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث في الوصف أو المشروع..." class="w-44 sm:w-56 px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 pr-8">
                    <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>

                <select name="type" onchange="this.form.submit()" class="px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                    <option value="">جميع الأنواع</option>
                    <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>مصروفات فقط</option>
                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>دخل فقط</option>
                </select>

                <select name="category_id" onchange="this.form.submit()" class="px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                    <option value="">جميع التصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">التاريخ</th>
                        <th class="py-3 px-4">التصنيف</th>
                        <th class="py-3 px-4">الوصف</th>
                        <th class="py-3 px-4">طريقة الدفع / المشروع</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $t->date ? $t->date->format('Y-m-d') : '' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                    <i class="fa-solid {{ $t->category?->icon ?? 'fa-asterisk' }} text-teal-600 dark:text-teal-400"></i>
                                    <span>{{ $t->category?->name ?? 'بدون تصنيف' }}</span>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $t->description }}</div>
                                @if(!empty($t->notes))
                                    <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $t->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-[11px]">
                                    @if($t->payment_method)
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $t->payment_method }}</span>
                                    @endif
                                    @if($t->project_name)
                                        <span class="text-teal-600 dark:text-teal-400 font-medium">#{{ $t->project_name }}</span>
                                    @endif
                                    @if(!$t->payment_method && !$t->project_name)
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 font-bold whitespace-nowrap {{ $t->type === 'income' ? 'text-teal-600 dark:text-teal-400' : 'text-slate-800 dark:text-slate-100' }}">
                                <span>{{ $t->type === 'income' ? '+' : '-' }}{{ number_format($t->amount, 2) }}</span>
                                <span class="text-[10px] text-slate-400 font-normal mr-1">{{ $t->currency }}</span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('finances.transactions.destroy', $t) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة المالية؟')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="حذف المعاملة">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                                <span>لا توجد معاملات تطابق عوامل التصفية المحددة.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL 1: Add New Transaction -->
<div id="transactionModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl p-6 overflow-hidden">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-teal-600 dark:text-teal-400"></i>
                <span>تسجيل معاملة مالية جديدة</span>
            </h3>
            <button type="button" onclick="closeTransactionModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finances.transactions.store') }}" class="mt-4 space-y-4">
            @csrf

            <!-- Type Selector: Expense or Income -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">نوع المعاملة *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer border rounded-xl p-2.5 flex items-center justify-center gap-2 text-xs font-bold transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-950/40">
                        <input type="radio" name="type" value="expense" checked class="accent-teal-600" onchange="filterCategoryType('expense')">
                        <span class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-arrow-trend-down"></i> مصروف</span>
                    </label>
                    <label class="cursor-pointer border rounded-xl p-2.5 flex items-center justify-center gap-2 text-xs font-bold transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-950/40">
                        <input type="radio" name="type" value="income" class="accent-teal-600" onchange="filterCategoryType('income')">
                        <span class="text-teal-600 dark:text-teal-400"><i class="fa-solid fa-arrow-trend-up"></i> دخل</span>
                    </label>
                </div>
            </div>

            <!-- Amount & Currency -->
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">المبلغ *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">العملة *</label>
                    <select name="currency" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 font-bold">
                        @foreach($currencies as $code => $cName)
                            <option value="{{ $code }}" @selected($code === $currency)>{{ $code }} — {{ $cName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Category & Date -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">التصنيف</label>
                    <select name="category_id" id="modalCategorySelect" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
                        <option value="">بدون تصنيف</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">التاريخ *</label>
                    <input type="date" name="date" value="{{ \Carbon\Carbon::today()->toDateString() }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">الوصف *</label>
                <input type="text" name="description" required placeholder="مثال: غداء عمل، بنزين للسيارة، فاتورة إنترنت..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
            </div>

            <!-- Optional: Payment Method & Project -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">طريقة الدفع (اختياري)</label>
                    <input type="text" name="payment_method" placeholder="كاش، بطاقة، تحويل..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">المشروع المرتبط (اختياري)</label>
                    <input type="text" name="project_name" placeholder="اسم المشروع..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">ملاحظات إضافية (اختياري)</label>
                <textarea name="notes" rows="2" placeholder="أي تفاصيل أخرى..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white shadow-sm transition-all">
                    تأكيد وحفظ المعاملة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Set Budget -->
<div id="budgetModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-2xl p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-teal-600 dark:text-teal-400"></i>
                <span>تحديد سقف الميزانية الشهرية</span>
            </h3>
            <button type="button" onclick="closeBudgetModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finances.budgets.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="month" value="{{ $month === 'all' ? \Carbon\Carbon::now()->month : $month }}">
            <input type="hidden" name="year" value="{{ $year }}">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">العملة</label>
                <select name="currency" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 font-bold">
                    @foreach($currencies as $code => $cName)
                        <option value="{{ $code }}" @selected($code === $currency)>{{ $code }} — {{ $cName }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">النطاق</label>
                <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
                    <option value="">الميزانية العامة لكامل الشهر</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">ميزانية خاصة بتصنيف: {{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">مبلغ الميزانية المستهدف *</label>
                <input type="number" step="0.01" min="0" name="budget_amount" value="{{ $budgetAmount > 0 ? $budgetAmount : '' }}" required placeholder="مثال: 1500" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500 font-bold">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeBudgetModal()" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white shadow-sm transition-all">
                    حفظ الميزانية
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Add Category -->
<div id="categoryModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-2xl p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-tags text-teal-600 dark:text-teal-400"></i>
                <span>إضافة تصنيف مالي مخصص</span>
            </h3>
            <button type="button" onclick="closeCategoryModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finances.categories.store') }}" class="mt-4 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">اسم التصنيف *</label>
                <input type="text" name="name" required placeholder="مثال: اشتراكات رقمية، تأمين صحي..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">نوع التصنيف *</label>
                <select name="type" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
                    <option value="expense">مصروفات فقط</option>
                    <option value="income">دخل فقط</option>
                    <option value="both">كلاهما (دخل ومصروف)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">الأيقونة (FontAwesome class)</label>
                <input type="text" name="icon" value="fa-tag" placeholder="fa-tag" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-hidden focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white shadow-sm transition-all">
                    حفظ التصنيف
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: MO AI Financial Advisor -->
<div id="aiInsightsModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-2xl p-6 overflow-hidden">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">تحليل المستشار المالي MO AI</h3>
                    <p class="text-[11px] text-slate-500">تحليل مبني على الأرقام الحقيقية المسجلة لـ {{ $isAnnual ? 'عام ' . $year : 'شهر ' . $month . '/' . $year }}</p>
                </div>
            </div>
            <button type="button" onclick="closeAiInsightsModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="aiInsightsBody" class="mt-4 py-2 min-h-[180px] max-h-96 overflow-y-auto text-xs text-slate-700 dark:text-slate-300 leading-relaxed pr-1">
            <!-- Spinner -->
            <div id="aiInsightsSpinner" class="flex flex-col items-center justify-center py-10 space-y-3">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-teal-600 dark:text-teal-400"></i>
                <span class="text-slate-500 font-semibold">جارٍ معالجة الأرقام وتحليل سلوك الإنفاق بالذكاء الاصطناعي...</span>
            </div>
            <!-- Dynamic Content -->
            <div id="aiInsightsContent" class="hidden whitespace-pre-line"></div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
            <span class="text-[11px] text-slate-400">حسابات مالية مؤكدة بنسبة 100% من Laravel</span>
            <button type="button" onclick="closeAiInsightsModal()" class="px-4 py-1.5 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition-all">
                إغلاق
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 1. Render Category Expenses Doughnut Chart
    @if(!empty($categoriesBreakdown))
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('categoryExpensesChart');
        if (ctx) {
            const labels = {!! json_encode(array_column($categoriesBreakdown, 'name')) !!};
            const data = {!! json_encode(array_column($categoriesBreakdown, 'spent')) !!};
            const colors = [
                '#0d9488', '#14b8a6', '#2dd4bf', '#0f766e',
                '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899',
                '#f59e0b', '#10b981', '#64748b'
            ];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: 'Cairo', size: 10 },
                                boxWidth: 10,
                                padding: 10,
                                color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#475569'
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
    @endif

    // Modal Helpers
    function openTransactionModal() {
        document.getElementById('transactionModal').classList.remove('hidden');
    }
    function closeTransactionModal() {
        document.getElementById('transactionModal').classList.add('hidden');
    }
    function openBudgetModal() {
        document.getElementById('budgetModal').classList.remove('hidden');
    }
    function closeBudgetModal() {
        document.getElementById('budgetModal').classList.add('hidden');
    }
    function openCategoryModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
    }
    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }

    // AI Financial Advisor Loader
    function openAiInsightsModal() {
        const modal = document.getElementById('aiInsightsModal');
        const spinner = document.getElementById('aiInsightsSpinner');
        const content = document.getElementById('aiInsightsContent');

        modal.classList.remove('hidden');
        spinner.classList.remove('hidden');
        content.classList.add('hidden');
        content.innerHTML = '';

        fetch(`{{ route('finances.ai-insights') }}?month={{ $month }}&year={{ $year }}&currency={{ $currency }}`)
            .then(res => res.json())
            .then(data => {
                spinner.classList.add('hidden');
                content.classList.remove('hidden');
                if (data.success && data.insights) {
                    content.innerHTML = data.insights;
                } else {
                    content.innerHTML = 'تعذر توليد التحليل في الوقت الحالي، يرجى المحاولة لاحقاً.';
                }
            })
            .catch(err => {
                spinner.classList.add('hidden');
                content.classList.remove('hidden');
                content.innerHTML = 'حدث خطأ في الاتصال بالخدمة.';
            });
    }
    function closeAiInsightsModal() {
        document.getElementById('aiInsightsModal').classList.add('hidden');
    }

    function filterCategoryType(type) {
        const select = document.getElementById('modalCategorySelect');
        const options = select.querySelectorAll('option');
        options.forEach(opt => {
            const catType = opt.getAttribute('data-type');
            if (!catType || catType === 'both' || catType === type) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
