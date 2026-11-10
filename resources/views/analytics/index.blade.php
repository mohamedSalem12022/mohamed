@extends('layouts.app')

@section('title', 'التقارير والإحصائيات — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-brand-600"></i>
                لوحة التقارير والتحليلات البيانية
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                رؤية شاملة لأدائك، نسب الالتزام بالعادات، ومعدلات إنجاز المهام
            </p>
        </div>

        <!-- Month Filter -->
        <form action="{{ route('analytics.index') }}" method="GET" class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-semibold">الشهر:</span>
            <select name="month" class="px-3 py-1.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->locale('ar')->isoFormat('MMMM') }}
                </option>
                @endfor
            </select>
            <select name="year" class="px-3 py-1.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-brand-600 text-white rounded-xl shadow-sm">
                تطبيق
            </button>
        </form>
    </div>

    <!-- 4 High-level Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400">إجمالي المهام المنجزة</span>
            <p class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-1">{{ $tasksCompleted }} / {{ $tasksTotal }}</p>
            <span class="text-xs font-bold text-teal-600 mt-1 block">نسبة إنجاز {{ $tasksRate }}%</span>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400">العادات النشطة</span>
            <p class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-1">{{ count($habitsStats) }}</p>
            <span class="text-xs font-semibold text-slate-400 mt-1 block">في هذا الشهر</span>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400">التغلب على الأعذار</span>
            <p class="text-2xl font-black text-brand-600 dark:text-brand-400 font-mono mt-1">{{ $overcameRate }}%</p>
            <span class="text-xs font-semibold text-slate-400 mt-1 block">{{ $overcameExcuses }} تم التغلب عليها</span>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400">أفضل عادة التزاماً</span>
            <p class="text-sm font-bold text-teal-600 truncate mt-1">
                {{ $bestHabits[0]['habit']->name ?? 'لا توجد بيانات' }}
            </p>
            <span class="text-xs font-mono font-bold text-slate-500 mt-1 block">
                {{ $bestHabits[0]['rate'] ?? 0 }}% إنجاز
            </span>
        </div>
    </div>

    <!-- Charts Row: Monthly Trend Line Chart & Habits Bar Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Line Chart: 6-Month Commitment Trend -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-arrow-trend-up text-brand-600"></i>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">تطور نسبة الالتزام على مدار الأشهر</h2>
                </div>
                <span class="text-xs text-slate-400 font-semibold">آخر 6 أشهر</span>
            </div>
            <div class="h-64">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Task Categories Distribution -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-brand-600"></i>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">توزيع المهام حسب التصنيفات</h2>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="tasksCategoryChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Habits Performance Breakdown Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-ranking-star text-brand-600"></i>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">جدول أداء العادات لشهر {{ \Carbon\Carbon::createFromDate(null, $selectedMonth, 1)->locale('ar')->isoFormat('MMMM') }}</h2>
            </div>
            <span class="text-xs text-slate-400">مرتبة حسب أعلى نسبة إنجاز</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-700 text-slate-400 uppercase tracking-wider font-bold">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">العادة</th>
                        <th class="py-3 px-3">الأيام المكتملة</th>
                        <th class="py-3 px-3">السلسلة الحالية</th>
                        <th class="py-3 px-3">نسبة الالتزام</th>
                        <th class="py-3 px-3">مستوى الأداء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($habitsStats as $idx => $st)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                        <td class="py-3 px-3 font-mono font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $st['habit']->color }}"></span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $st['habit']->name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ $st['completed_days'] }} يوم
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-teal-600 dark:text-teal-400">
                            <i class="fa-solid fa-calendar-check text-xs ml-1"></i> {{ $st['streak'] }} يوم
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $st['rate'] }}%</span>
                                <div class="w-20 bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ $st['rate'] }}%; background-color: {{ $st['habit']->color }}"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            @if($st['rate'] >= 80)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-950 text-teal-600 border border-teal-200 dark:border-teal-800">
                                ممتاز 🌟
                            </span>
                            @elseif($st['rate'] >= 50)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950 text-blue-600 border border-blue-200 dark:border-blue-800">
                                جيد 👍
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950 text-amber-600 border border-amber-200 dark:border-amber-800">
                                يحتاج تركيز ⚡
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-slate-400">لا توجد عادات مسجلة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // 1. Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    const monthlyLabels = {!! json_encode(array_column($monthlyTrend, 'label')) !!};
    const monthlyRates = {!! json_encode(array_column($monthlyTrend, 'rate')) !!};

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'نسبة الالتزام %',
                data: monthlyRates,
                borderColor: '#0d9488',
                backgroundColor: 'rgba(13, 148, 136, 0.1)',
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#0d9488',
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        callback: val => val + '%'
                    }
                }
            }
        }
    });

    // 2. Task Category Doughnut Chart
    const catCtx = document.getElementById('tasksCategoryChart').getContext('2d');
    const catData = {!! json_encode($categoriesStats) !!};
    const catLabels = catData.map(c => c.name);
    const catCounts = catData.map(c => c.total);
    const catColors = catData.map(c => c.color);

    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels.length ? catLabels : ['عام'],
            datasets: [{
                data: catCounts.length ? catCounts : [1],
                backgroundColor: catColors.length ? catColors : ['#0d9488'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: 'Cairo' } }
                }
            }
        }
    });
</script>
@endpush
@endsection
