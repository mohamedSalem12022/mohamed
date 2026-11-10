@extends('layouts.app')

@section('title', $skill->title . ' — إتقان مهارة | MO Life OS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition flex items-center gap-1.5">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>لوحة التحكم</span>
        </a>
        <i class="fa-solid fa-chevron-left text-[9px] text-slate-400"></i>
        <a href="{{ route('skills.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
            إتقان المهارات
        </a>
        <i class="fa-solid fa-chevron-left text-[9px] text-slate-400"></i>
        <span class="text-slate-800 dark:text-slate-200 font-bold truncate max-w-xs">{{ $skill->title }}</span>
    </nav>

    <!-- Main Skill Hero Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200 dark:border-slate-700 relative overflow-hidden">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
            <!-- Left: Info & Meta -->
            <div class="flex items-start sm:items-center gap-4 sm:gap-5 min-w-0">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl flex items-center justify-center text-white text-2xl sm:text-3xl shadow-lg shrink-0 transition-transform hover:scale-105" style="background-color: {{ $skill->color ?? '#0d9488' }};">
                    <i class="{{ $skill->icon ?? 'fa-solid fa-graduation-cap' }}"></i>
                </div>
                <div class="min-w-0 space-y-2">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white truncate">
                            {{ $skill->title }}
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $timeline['class'] }}">
                            {{ $timeline['text'] }}
                        </span>
                        @if($progress === 100)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-300 border border-teal-200 dark:border-teal-700">
                            <i class="fa-solid fa-award"></i>
                            تم الإتقان بالكامل
                        </span>
                        @endif
                    </div>

                    @if($skill->goal)
                    <div class="flex items-start gap-2 text-xs sm:text-sm text-slate-700 dark:text-slate-200 font-semibold bg-slate-50 dark:bg-slate-900/60 px-3 py-2 rounded-xl border border-slate-100 dark:border-slate-800">
                        <i class="fa-solid fa-bullseye text-brand-600 dark:text-brand-400 mt-0.5 text-xs shrink-0"></i>
                        <span><strong>الهدف:</strong> {{ $skill->goal }}</span>
                    </div>
                    @endif

                    @if($skill->description)
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">
                        {{ $skill->description }}
                    </p>
                    @endif

                    <!-- Timeline Details -->
                    <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 pt-1 flex-wrap">
                        @if($skill->start_date)
                        <span class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-[11px] text-slate-400"></i>
                            بدأت: {{ $skill->start_date->format('Y-m-d') }}
                        </span>
                        @endif
                        @if($skill->target_date)
                        <span class="flex items-center gap-1.5">
                            <i class="fa-regular fa-flag text-[11px] text-slate-400"></i>
                            الموعد المستهدف: {{ $skill->target_date->format('Y-m-d') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center flex-wrap gap-2.5 w-full lg:w-auto shrink-0 pt-2 lg:pt-0">
                <button type="button" onclick="openAddSubSkillModal()" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة مهارة فرعية</span>
                </button>

                <button type="button" onclick="openAiBreakdownModal()" class="px-3.5 py-2.5 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-500/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2" title="تفكيك المهارة عبر المساعد الذكي">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                    <span>تفكيك ذكي (AI)</span>
                </button>

                <button type="button" onclick="openAiRoadmapModal()" class="px-3.5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm transition flex items-center gap-2" title="اقتراح خطة تعلم ومصادر">
                    <i class="fa-solid fa-route text-xs text-brand-600 dark:text-brand-400"></i>
                    <span>خطة التعلم</span>
                </button>

                <button type="button" onclick="openEditMainSkillModal()" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 flex items-center justify-center transition" title="تعديل المهارة">
                    <i class="fa-solid fa-pen text-xs"></i>
                </button>

                <button type="button" onclick="confirmDeleteMainSkill()" class="w-10 h-10 rounded-2xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center transition" title="حذف المهارة">
                    <i class="fa-regular fa-trash-can text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Decorative background glow -->
        <div class="absolute -left-12 -bottom-12 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-20" style="background-color: {{ $skill->color ?? '#0d9488' }};"></div>
    </div>

    <!-- Progress Dashboard (KPI Cards & Progress Bar) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Progress Bar & Gauge (5 cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-base">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">إجمالي نسبة الإتقان</h3>
                        <p class="text-[11px] text-slate-400">تُحسب تلقائياً من المهارات الفرعية</p>
                    </div>
                </div>
                <div class="text-right">
                    <span id="overallProgressDisplay" class="text-3xl font-black text-brand-600 dark:text-brand-400">
                        {{ $progress }}%
                    </span>
                </div>
            </div>

            <!-- Interactive Bar -->
            <div class="space-y-2">
                <div class="w-full h-3.5 bg-slate-100 dark:bg-slate-700/80 rounded-full overflow-hidden p-0.5 border border-slate-200 dark:border-slate-700">
                    <div id="overallProgressBar" class="h-full bg-gradient-to-r from-brand-600 via-teal-500 to-emerald-400 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $progress }}%;"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium">
                    <span>البداية (0%)</span>
                    <span>المستوى المتوسط (50%)</span>
                    <span>الإتقان التام (100%)</span>
                </div>
            </div>

            <!-- Status message -->
            <div class="text-xs bg-slate-50 dark:bg-slate-900/60 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center gap-2">
                @if($progress === 100)
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm shrink-0"></i>
                    <span class="text-emerald-700 dark:text-emerald-300 font-bold">رائع جداً! لقد أتقنت جميع متطلبات هذه المهارة بنجاح! 🎉</span>
                @elseif($progress >= 70)
                    <i class="fa-solid fa-fire text-amber-500 text-sm shrink-0"></i>
                    <span class="text-slate-700 dark:text-slate-200 font-medium">أنت على وشك الوصول إلى الاحتراف! تابع المهارات المتبقية.</span>
                @elseif($progress > 0)
                    <i class="fa-solid fa-bolt text-brand-500 text-sm shrink-0"></i>
                    <span class="text-slate-700 dark:text-slate-200 font-medium">تقدّم ممتاز! استمر في التعلم والتطبيق العملي خطوة بخطوة.</span>
                @else
                    <i class="fa-regular fa-lightbulb text-slate-400 text-sm shrink-0"></i>
                    <span class="text-slate-500 dark:text-slate-400">ابدأ بأول مهارة فرعية وسجّل تقدمك وتطبيقاتك أولاً بأول.</span>
                @endif
            </div>
        </div>

        <!-- 4 Metrics & Spotlight (7 cols) -->
        <div class="lg:col-span-7 flex flex-col justify-between gap-4">
            <!-- 3 Mini Metric Cards -->
            <div class="grid grid-cols-3 gap-3">
                <!-- Mastered -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-xs border border-slate-200 dark:border-slate-700 text-center">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto text-sm mb-1.5">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <span id="masteredCountDisplay" class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white block">
                        {{ $masteredCount }}
                    </span>
                    <p class="text-[11px] font-bold text-teal-700 dark:text-teal-300">تم إتقانها</p>
                </div>

                <!-- In Progress -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-xs border border-slate-200 dark:border-slate-700 text-center">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto text-sm mb-1.5">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                    <span id="inProgressCountDisplay" class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white block">
                        {{ $inProgressCount }}
                    </span>
                    <p class="text-[11px] font-bold text-amber-700 dark:text-amber-300">قيد التعلم</p>
                </div>

                <!-- Not Started -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-xs border border-slate-200 dark:border-slate-700 text-center">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center mx-auto text-sm mb-1.5">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <span id="notStartedCountDisplay" class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white block">
                        {{ $notStartedCount }}
                    </span>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لم تبدأ</p>
                </div>
            </div>

            <!-- Spotlight: Recommended Next Sub-Skill -->
            <div id="nextSkillBanner" class="bg-gradient-to-r from-brand-50 to-teal-50 dark:from-slate-800 dark:to-teal-950/40 rounded-2xl p-4 sm:p-5 border border-brand-200 dark:border-teal-900/60 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md shadow-brand-500/20">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-black text-brand-700 dark:text-brand-300 uppercase tracking-wider">
                                المهارة التالية المقترحة للعمل عليها
                            </span>
                        </div>
                        <h4 id="nextSkillTitle" class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate mt-0.5">
                            @if($nextSkill)
                                {{ $nextSkill->title }}
                            @elseif($totalSubSkills === 0)
                                لم يتم تقسيم المهارة بعد — أضف مهارات فرعية أو قسّمها بالذكاء الاصطناعي
                            @else
                                أحسنت! تم إتقان جميع المهارات الفرعية 🎉
                            @endif
                        </h4>
                    </div>
                </div>

                @if($nextSkill)
                <button type="button" onclick="scrollToSubSkill({{ $nextSkill->id }})" class="shrink-0 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-700 hover:bg-brand-600 hover:text-white text-brand-600 dark:text-brand-300 font-bold text-xs shadow-xs border border-brand-200 dark:border-slate-600 transition flex items-center gap-1.5">
                    <span>الانتقال إليها</span>
                    <i class="fa-solid fa-arrow-down text-[10px]"></i>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Sub-Skills Section as 2-Column Table (Matching Reference Concept) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200 dark:border-slate-700 space-y-6">

        <!-- Title Above Table - Identical to Reference Concept -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="text-center sm:text-right space-y-1">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    المهارات التي يجب إتقانها أولاً
                </h2>
                <p class="text-sm sm:text-base font-bold text-brand-600 dark:text-brand-400 flex items-center justify-center sm:justify-start gap-1.5">
                    <i class="fa-solid fa-graduation-cap text-xs"></i>
                    <span>في {{ $skill->title }}</span>
                    <span id="totalSubSkillsBadge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 mr-2">
                        ({{ $totalSubSkills }} مهارات)
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-center sm:justify-end flex-wrap">
                <button type="button" onclick="openAddSubSkillModal()" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة مهارة</span>
                </button>
                <button type="button" onclick="openAiBreakdownModal()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm transition flex items-center gap-2" title="تفكيك ذكي بالذكاء الاصطناعي">
                    <i class="fa-solid fa-wand-magic-sparkles text-brand-600 text-xs"></i>
                    <span>اقتراح ذكي (AI)</span>
                </button>
            </div>
        </div>

        <!-- Sub-Skills Content -->
        @if($skill->subSkills->isEmpty())
            <!-- Empty State -->
            <div class="py-12 text-center space-y-4 max-w-md mx-auto">
                <div class="w-16 h-16 rounded-3xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                    <i class="fa-solid fa-table-list"></i>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        لم تقم بإضافة مهارات في الجدول بعد!
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        ابدأ بإضافة أول مهارة فرعية أو دع المساعد الذكي يفكك مهارة «{{ $skill->title }}» ويقترح عليك المهارات المطلوبة بنقرة واحدة.
                    </p>
                </div>
                <div class="pt-2 flex items-center justify-center gap-3 flex-wrap">
                    <button type="button" onclick="openAiBreakdownModal()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                        <span>تفكيك تلقائي بالذكاء الاصطناعي 🤖</span>
                    </button>
                    <button type="button" onclick="openAddSubSkillModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>إضافة مهارة يدوياً</span>
                    </button>
                </div>
            </div>
        @else
            <!-- 2-Column Table Matching Reference Image Concept -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-slate-100/90 dark:bg-slate-700/70 text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700">
                            <!-- Right Column: العدد -->
                            <th scope="col" class="py-3.5 px-4 sm:px-6 text-center text-xs sm:text-sm font-black w-36 sm:w-52 border-l border-slate-200 dark:border-slate-700 bg-slate-200/50 dark:bg-slate-800/80">
                                العدد
                            </th>
                            <!-- Left Column: المهارة -->
                            <th scope="col" class="py-3.5 px-4 sm:px-6 text-right text-xs sm:text-sm font-black">
                                المهارة
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800" id="subSkillsTableBody">
                        @foreach($skill->subSkills as $index => $subSkill)
                            @php
                                $resourcesList = $subSkill->parsedResources();
                                $tasksList = $subSkill->parsedPracticalTasks();
                            @endphp
                            <!-- Main Sub-Skill Row -->
                            <tr id="subSkillCard-{{ $subSkill->id }}" data-id="{{ $subSkill->id }}" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
                                
                                <!-- 1. العدد / الترتيب -->
                                <td class="py-4 px-3 sm:px-6 text-center border-l border-slate-200 dark:border-slate-700 align-middle bg-slate-50/40 dark:bg-slate-900/30 w-36 sm:w-52">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Reorder Arrows -->
                                        <div class="flex flex-col items-center gap-0.5 opacity-40 group-hover:opacity-100 transition-opacity">
                                            @if(!$loop->first)
                                            <button type="button" onclick="moveSubSkill({{ $subSkill->id }}, 'up')" class="text-slate-400 hover:text-brand-600 text-[10px]" title="تحريك لأعلى">
                                                <i class="fa-solid fa-chevron-up"></i>
                                            </button>
                                            @endif
                                            @if(!$loop->last)
                                            <button type="button" onclick="moveSubSkill({{ $subSkill->id }}, 'down')" class="text-slate-400 hover:text-brand-600 text-[10px]" title="تحريك لأسفل">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                            @endif
                                        </div>
                                        
                                        <!-- Ordinal text (المهارة الأولى، المهارة الثانية...) -->
                                        <span class="text-xs sm:text-sm lg:text-base font-black text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                            {{ \App\Models\SubSkill::ordinalLabel($index + 1) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- 2. المهارة -->
                                <td class="py-4 px-4 sm:px-6 align-middle">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                        
                                        <!-- Title, Status & Description -->
                                        <div class="min-w-0 space-y-1 flex-1">
                                            <div class="flex items-center gap-2.5 flex-wrap">
                                                <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
                                                    {{ $subSkill->title }}
                                                </span>

                                                <!-- Interactive Status Badge Button -->
                                                <button type="button" onclick="cycleSubSkillStatus({{ $subSkill->id }})" id="statusBadge-{{ $subSkill->id }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition hover:scale-105 active:scale-95 {{ $subSkill->statusBadgeClass() }}" title="انقر لتغيير الحالة مباشرة">
                                                    <i class="fa-solid fa-rotate text-[9px]"></i>
                                                    <span id="statusLabel-{{ $subSkill->id }}">{{ $subSkill->statusLabel() }}</span>
                                                </button>
                                            </div>

                                            @if($subSkill->description)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 leading-relaxed">
                                                {{ $subSkill->description }}
                                            </p>
                                            @endif
                                        </div>

                                        <!-- Right side: Mastery Slider & Controls -->
                                        <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                                            
                                            <!-- Live Mastery Level Slider -->
                                            <div class="flex items-center gap-2 w-32 sm:w-44">
                                                <input type="range" min="0" max="100" step="5" value="{{ $subSkill->mastery_level }}" id="masteryRange-{{ $subSkill->id }}" oninput="updateMasterySliderText({{ $subSkill->id }}, this.value)" onchange="saveMasteryLevel({{ $subSkill->id }}, this.value)" class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-600">
                                                <span id="masteryVal-{{ $subSkill->id }}" class="text-xs font-black text-brand-600 dark:text-brand-400 w-9 text-left shrink-0">
                                                    {{ $subSkill->mastery_level }}%
                                                </span>
                                            </div>

                                            <!-- Action Buttons: Details, Edit, Delete -->
                                            <div class="flex items-center gap-1">
                                                <button type="button" onclick="toggleSubSkillDetails({{ $subSkill->id }})" id="toggleDetailsBtn-{{ $subSkill->id }}" class="w-8 h-8 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition" title="عرض/إخفاء التفاصيل والمصادر">
                                                    <i class="fa-solid fa-layer-group text-xs" id="detailsChevron-{{ $subSkill->id }}"></i>
                                                </button>
                                                <button type="button" onclick="editSubSkill({{ $subSkill->toJson() }})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition" title="تعديل المهارة الفرعية">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </button>
                                                <button type="button" onclick="deleteSubSkill({{ $subSkill->id }}, '{{ addslashes($subSkill->title) }}')" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center transition" title="حذف المهارة الفرعية">
                                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                                </button>
                                            </div>

                                        </div>

                                    </div>
                                </td>
                            </tr>

                            <!-- Expandable Details Row (Notes, Resources, Practical Tasks) -->
                            <tr id="subSkillDetails-{{ $subSkill->id }}" class="hidden bg-slate-50/50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
                                <td colspan="2" class="p-4 sm:p-5">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        
                                        <!-- 1. Practical Tasks -->
                                        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 shadow-2xs">
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                                <i class="fa-solid fa-list-check text-brand-600 text-[11px]"></i>
                                                <span>التطبيقات والمهام العملية</span>
                                            </div>
                                            @if(!empty($tasksList))
                                                <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                                                    @foreach($tasksList as $task)
                                                        <li class="flex items-start gap-1.5">
                                                            <i class="fa-regular fa-circle-check text-brand-600 mt-1 text-[10px] shrink-0"></i>
                                                            <span>{{ $task }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-[11px] text-slate-400 italic">لا توجد تطبيقات مضافة بعد. انقر على تعديل لإضافة مهام عملية.</p>
                                            @endif
                                        </div>

                                        <!-- 2. Learning Resources & Links -->
                                        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 shadow-2xs">
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                                <i class="fa-solid fa-link text-teal-600 text-[11px]"></i>
                                                <span>مصادر التعلم والدورات</span>
                                            </div>
                                            @if(!empty($resourcesList))
                                                <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                                                    @foreach($resourcesList as $resource)
                                                        @php
                                                            $isUrl = filter_var($resource, FILTER_VALIDATE_URL) || str_starts_with($resource, 'http://') || str_starts_with($resource, 'https://');
                                                        @endphp
                                                        <li class="flex items-start gap-1.5 truncate">
                                                            <i class="fa-solid fa-book-bookmark text-teal-600 mt-1 text-[10px] shrink-0"></i>
                                                            @if($isUrl)
                                                                <a href="{{ $resource }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-1 truncate">
                                                                    <span>{{ $resource }}</span>
                                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                                </a>
                                                            @else
                                                                <span>{{ $resource }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-[11px] text-slate-400 italic">لا توجد روابط أو مصادر مضافة بعد.</p>
                                            @endif
                                        </div>

                                        <!-- 3. Personal Notes -->
                                        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 shadow-2xs">
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                                <i class="fa-regular fa-note-sticky text-amber-500 text-[11px]"></i>
                                                <span>ملاحظات وتأملات شخصية</span>
                                            </div>
                                            @if($subSkill->notes)
                                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                                    {{ $subSkill->notes }}
                                                </p>
                                            @else
                                                <p class="text-[11px] text-slate-400 italic">لا توجد ملاحظات مسجلة بعد.</p>
                                            @endif
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

</div>

<!-- ========================================== -->
<!-- MODAL 1: Add Sub-Skill Modal               -->
<!-- ========================================== -->
<div id="addSubSkillModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-brand-600"></i>
                <span>إضافة مهارة فرعية جديدة</span>
            </h3>
            <button type="button" onclick="closeAddSubSkillModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('skills.sub-skills.store', $skill) }}" method="POST" class="space-y-4">
            @csrf

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المهارة الفرعية <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="مثال: كتابة السيناريو، المونتاج ببرنامج Premiere، أو السرد القصصي" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوصف المختصر</label>
                <textarea name="description" rows="2" placeholder="ماذا تعني هذه المهارة وما الذي يجب إتقانه فيها؟" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Status & Mastery Level -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">حالة التعلم الحالية</label>
                    <select name="status" id="newSubSkillStatus" onchange="syncNewSubSkillStatus(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="not_started">لم تبدأ بعد (0%)</option>
                        <option value="in_progress">قيد التعلم (50%)</option>
                        <option value="mastered">تم الإتقان (100%)</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">مستوى الإتقان (0 - 100%)</label>
                        <span id="newMasteryDisplay" class="text-xs font-black text-brand-600 dark:text-brand-400">0%</span>
                    </div>
                    <input type="range" name="mastery_level" id="newMasterySlider" min="0" max="100" step="5" value="0" oninput="document.getElementById('newMasteryDisplay').textContent = this.value + '%'" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-600">
                </div>
            </div>

            <!-- Practical Tasks -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تطبيقات ومهام عملية (سطر لكل تطبيق)</label>
                <textarea name="practical_tasks" rows="2" placeholder="- تصوير فيديو تجريبي مدته 60 ثانية&#10;- كتابة سيناريو لحلقة كاملة" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Learning Resources -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">روابط مصادر التعلم والدورات (سطر لكل رابط أو دورة)</label>
                <textarea name="resources" rows="2" placeholder="https://youtube.com/watch?...&#10;كتاب فن السرد القصصي" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Personal Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات وتأملات شخصية</label>
                <textarea name="notes" rows="2" placeholder="نصائح تود تذكرها، نقاط قوة أو ضعف للتركيز عليها..." class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeAddSubSkillModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>إضافة المهارة الفرعية</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: Edit Sub-Skill Modal              -->
<!-- ========================================== -->
<div id="editSubSkillModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen text-brand-600"></i>
                <span>تعديل المهارة الفرعية</span>
            </h3>
            <button type="button" onclick="closeEditSubSkillModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="editSubSkillForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المهارة الفرعية <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="editSubSkillTitle" required class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوصف المختصر</label>
                <textarea name="description" id="editSubSkillDescription" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Status & Mastery Level -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">حالة التعلم</label>
                    <select name="status" id="editSubSkillStatus" onchange="syncEditSubSkillStatus(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="not_started">لم تبدأ بعد</option>
                        <option value="in_progress">قيد التعلم</option>
                        <option value="mastered">تم الإتقان</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">مستوى الإتقان (0 - 100%)</label>
                        <span id="editMasteryDisplay" class="text-xs font-black text-brand-600 dark:text-brand-400">0%</span>
                    </div>
                    <input type="range" name="mastery_level" id="editMasterySlider" min="0" max="100" step="5" value="0" oninput="document.getElementById('editMasteryDisplay').textContent = this.value + '%'" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-600">
                </div>
            </div>

            <!-- Practical Tasks -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تطبيقات ومهام عملية (سطر لكل تطبيق)</label>
                <textarea name="practical_tasks" id="editSubSkillTasks" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <!-- Learning Resources -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">روابط مصادر التعلم والدورات (سطر لكل رابط)</label>
                <textarea name="resources" id="editSubSkillResources" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <!-- Personal Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ملاحظات شخصية</label>
                <textarea name="notes" id="editSubSkillNotes" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditSubSkillModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>حفظ التعديلات</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: Edit Main Skill Modal             -->
<!-- ========================================== -->
<div id="editMainSkillModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen text-brand-600"></i>
                <span>تعديل المهارة الرئيسية</span>
            </h3>
            <button type="button" onclick="closeEditMainSkillModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('skills.update', $skill) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المهارة الرئيسية <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ $skill->title }}" required class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الوصف المختصر</label>
                <textarea name="description" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">{{ $skill->description }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الهدف والنتيجة المستهدفة</label>
                <textarea name="goal" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">{{ $skill->goal }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ البدء</label>
                    <input type="date" name="start_date" value="{{ optional($skill->start_date)->format('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الموعد المستهدف للإتقان</label>
                    <input type="date" name="target_date" value="{{ optional($skill->target_date)->format('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأيقونة</label>
                    <select name="icon" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="fa-solid fa-graduation-cap" {{ $skill->icon === 'fa-solid fa-graduation-cap' ? 'selected' : '' }}>🎓 تعلّم عام</option>
                        <option value="fa-solid fa-video" {{ $skill->icon === 'fa-solid fa-video' ? 'selected' : '' }}>🎬 صناعة المحتوى</option>
                        <option value="fa-solid fa-code" {{ $skill->icon === 'fa-solid fa-code' ? 'selected' : '' }}>💻 البرمجة والتقنية</option>
                        <option value="fa-solid fa-camera" {{ $skill->icon === 'fa-solid fa-camera' ? 'selected' : '' }}>📸 التصوير</option>
                        <option value="fa-solid fa-pen-nib" {{ $skill->icon === 'fa-solid fa-pen-nib' ? 'selected' : '' }}>✍️ الكتابة</option>
                        <option value="fa-solid fa-bullhorn" {{ $skill->icon === 'fa-solid fa-bullhorn' ? 'selected' : '' }}>📢 التسويق</option>
                        <option value="fa-solid fa-brain" {{ $skill->icon === 'fa-solid fa-brain' ? 'selected' : '' }}>🧠 تطوير ذاتي</option>
                        <option value="fa-solid fa-palette" {{ $skill->icon === 'fa-solid fa-palette' ? 'selected' : '' }}>🎨 التصميم</option>
                        <option value="fa-solid fa-language" {{ $skill->icon === 'fa-solid fa-language' ? 'selected' : '' }}>🌐 اللغات</option>
                        <option value="fa-solid fa-briefcase" {{ $skill->icon === 'fa-solid fa-briefcase' ? 'selected' : '' }}>💼 الأعمال</option>
                        <option value="fa-solid fa-dumbbell" {{ $skill->icon === 'fa-solid fa-dumbbell' ? 'selected' : '' }}>💪 الرياضة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اللون المميز</label>
                    <input type="color" name="color" value="{{ $skill->color ?? '#0d9488' }}" class="w-full h-9 p-0.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">حالة المهارة</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                    <option value="in_progress" {{ $skill->status === 'in_progress' ? 'selected' : '' }}>قيد التعلم (نشطة)</option>
                    <option value="completed" {{ $skill->status === 'completed' ? 'selected' : '' }}>تم الإتقان بالكامل ✓</option>
                    <option value="paused" {{ $skill->status === 'paused' ? 'selected' : '' }}>معلقة مؤقتاً</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditMainSkillModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>تحديث المهارة</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: AI Breakdown Modal                -->
<!-- ========================================== -->
<div id="aiBreakdownModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-brand-600"></i>
                <span>تفكيك المهارة وتقسيمها بالذكاء الاصطناعي</span>
            </h3>
            <button type="button" onclick="closeAiBreakdownModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-3">
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                يقوم المساعد الذكي بتحليل مهارة <strong>«{{ $skill->title }}»</strong> وتقسيمها إلى المهارات الفرعية الأساسية اللازمة للوصول إلى الاحتراف، مع اقتراح تطبيقات عملية ومصادر لكل مهارة.
            </p>

            <!-- Loading State -->
            <div id="aiBreakdownLoading" class="hidden py-8 text-center space-y-3">
                <div class="w-12 h-12 border-4 border-brand-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">
                    جاري التفكير وتفكيك المهارة بواسطة الذكاء الاصطناعي...
                </p>
            </div>

            <!-- Initial Action / Trigger -->
            <div id="aiBreakdownInitial" class="text-center py-6 space-y-3">
                <div class="w-16 h-16 rounded-3xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <button type="button" onclick="requestAiBreakdown(false)" class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                    <span>توليد واقتراح المهارات الفرعية الآن</span>
                </button>
            </div>

            <!-- Results Preview Container -->
            <div id="aiBreakdownResults" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300" id="aiGeneratedCount">المهارات المقترحة:</span>
                    <button type="button" onclick="addAllAiSuggestions()" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check-double text-xs"></i>
                        <span>إضافة الكل مباشرة للمهارة</span>
                    </button>
                </div>
                <div id="aiSuggestionsList" class="space-y-2 max-h-72 overflow-y-auto pr-1"></div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-700">
            <button type="button" onclick="closeAiBreakdownModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                إغلاق
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 5: AI Roadmap & Advice Modal         -->
<!-- ========================================== -->
<div id="aiRoadmapModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-route text-brand-600"></i>
                <span>خطة التعلم الذكية والتوجيه الاستراتيجي</span>
            </h3>
            <button type="button" onclick="closeAiRoadmapModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-3">
            <!-- Loading State -->
            <div id="aiRoadmapLoading" class="hidden py-8 text-center space-y-3">
                <div class="w-12 h-12 border-4 border-brand-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">
                    جاري إعداد خطة التعلم المخصصة والخطوة التالية...
                </p>
            </div>

            <!-- Content Area -->
            <div id="aiRoadmapContent" class="space-y-4 text-xs sm:text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-line bg-slate-50 dark:bg-slate-900/60 p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800">
                <!-- Dynamically populated -->
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-700">
            <button type="button" onclick="closeAiRoadmapModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                إغلاق
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const appCsrfToken = (typeof csrfToken !== 'undefined' && csrfToken) 
        ? csrfToken 
        : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const skillId = {{ $skill->id }};
    let currentSuggestions = [];

    // ==========================================
    // UI Expand / Collapse Sub-skill Details
    // ==========================================
    function toggleSubSkillDetails(id) {
        const detailsEl = document.getElementById(`subSkillDetails-${id}`);
        const chevronEl = document.getElementById(`detailsChevron-${id}`);
        if (!detailsEl) return;

        detailsEl.classList.toggle('hidden');
        if (chevronEl) {
            chevronEl.classList.toggle('rotate-180');
        }
    }

    function scrollToSubSkill(id) {
        const el = document.getElementById(`subSkillCard-${id}`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('ring-2', 'ring-brand-500');
            setTimeout(() => el.classList.remove('ring-2', 'ring-brand-500'), 2500);
        }
    }

    // ==========================================
    // Live Mastery Slider & Fast AJAX Update
    // ==========================================
    function updateMasterySliderText(id, val) {
        const label = document.getElementById(`masteryVal-${id}`);
        if (label) label.textContent = `${val}%`;
    }

    let masteryDebounceTimer = null;
    function saveMasteryLevel(subSkillId, level) {
        clearTimeout(masteryDebounceTimer);
        masteryDebounceTimer = setTimeout(() => {
            fetch(`/skills/${skillId}/sub-skills/${subSkillId}/mastery`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': appCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mastery_level: parseInt(level) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update badge
                    const statusBadge = document.getElementById(`statusBadge-${subSkillId}`);
                    const statusLabel = document.getElementById(`statusLabel-${subSkillId}`);
                    if (statusBadge && data.status_badge_class) {
                        statusBadge.className = `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition hover:scale-105 active:scale-95 ${data.status_badge_class}`;
                    }
                    if (statusLabel && data.status_label) {
                        statusLabel.textContent = data.status_label;
                    }

                    // Update parent progress counters
                    updateProgressDashboard(data);
                    showToast('تم تحديث مستوى الإتقان ✓', 'success');
                }
            })
            .catch(() => showToast('تعذر حفظ مستوى الإتقان', 'error'));
        }, 150);
    }

    // ==========================================
    // Fast Toggle Status (Cycle)
    // ==========================================
    function cycleSubSkillStatus(subSkillId) {
        fetch(`/skills/${skillId}/sub-skills/${subSkillId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': appCsrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update Badge
                const statusBadge = document.getElementById(`statusBadge-${subSkillId}`);
                const statusLabel = document.getElementById(`statusLabel-${subSkillId}`);
                if (statusBadge && data.status_badge_class) {
                    statusBadge.className = `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition hover:scale-105 active:scale-95 ${data.status_badge_class}`;
                }
                if (statusLabel && data.status_label) {
                    statusLabel.textContent = data.status_label;
                }

                // Update Slider
                const slider = document.getElementById(`masteryRange-${subSkillId}`);
                const sliderVal = document.getElementById(`masteryVal-${subSkillId}`);
                if (slider) slider.value = data.mastery_level;
                if (sliderVal) sliderVal.textContent = `${data.mastery_level}%`;

                // Update parent progress counters
                updateProgressDashboard(data);
                showToast(`تم تغيير الحالة إلى: ${data.status_label} ✓`, 'success');
            }
        })
        .catch(() => showToast('تعذر تغيير حالة المهارة', 'error'));
    }

    // Helper: Refresh dashboard KPIs
    function updateProgressDashboard(data) {
        if (data.overall_progress !== undefined) {
            const bar = document.getElementById('overallProgressBar');
            const display = document.getElementById('overallProgressDisplay');
            if (bar) bar.style.width = `${data.overall_progress}%`;
            if (display) display.textContent = `${data.overall_progress}%`;
        }
        if (data.mastered_count !== undefined) {
            const el = document.getElementById('masteredCountDisplay');
            if (el) el.textContent = data.mastered_count;
        }
        if (data.in_progress_count !== undefined) {
            const el = document.getElementById('inProgressCountDisplay');
            if (el) el.textContent = data.in_progress_count;
        }
        if (data.not_started_count !== undefined) {
            const el = document.getElementById('notStartedCountDisplay');
            if (el) el.textContent = data.not_started_count;
        }
        if (data.next_skill) {
            const titleEl = document.getElementById('nextSkillTitle');
            if (titleEl) titleEl.textContent = data.next_skill;
        }
    }

    // ==========================================
    // Move / Reorder Sub-Skills Up/Down
    // ==========================================
    function moveSubSkill(subSkillId, direction) {
        const cards = Array.from(document.querySelectorAll('#subSkillsTableBody > tr[data-id], #subSkillsContainer > [data-id]'));
        const ids = cards.map(c => parseInt(c.getAttribute('data-id')));
        const currentIndex = ids.indexOf(subSkillId);
        if (currentIndex === -1) return;

        if (direction === 'up' && currentIndex > 0) {
            const temp = ids[currentIndex - 1];
            ids[currentIndex - 1] = ids[currentIndex];
            ids[currentIndex] = temp;
        } else if (direction === 'down' && currentIndex < ids.length - 1) {
            const temp = ids[currentIndex + 1];
            ids[currentIndex + 1] = ids[currentIndex];
            ids[currentIndex] = temp;
        } else {
            return;
        }

        // Send to backend
        fetch(`/skills/${skillId}/sub-skills/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': appCsrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // ==========================================
    // Sub-Skill Modals (Add / Edit / Delete)
    // ==========================================
    function openAddSubSkillModal() {
        const modal = document.getElementById('addSubSkillModal');
        if (modal) {
            modal.classList.remove('hidden');
            const input = modal.querySelector('input[name="title"]');
            if (input) setTimeout(() => input.focus(), 60);
        }
    }
    function closeAddSubSkillModal() {
        const modal = document.getElementById('addSubSkillModal');
        if (modal) modal.classList.add('hidden');
    }

    function syncNewSubSkillStatus(status) {
        const slider = document.getElementById('newMasterySlider');
        const display = document.getElementById('newMasteryDisplay');
        if (status === 'mastered') {
            slider.value = 100;
            display.textContent = '100%';
        } else if (status === 'not_started') {
            slider.value = 0;
            display.textContent = '0%';
        } else if (status === 'in_progress' && parseInt(slider.value) === 0) {
            slider.value = 50;
            display.textContent = '50%';
        }
    }

    function editSubSkill(subSkill) {
        const form = document.getElementById('editSubSkillForm');
        form.action = `/skills/${skillId}/sub-skills/${subSkill.id}`;

        document.getElementById('editSubSkillTitle').value = subSkill.title || '';
        document.getElementById('editSubSkillDescription').value = subSkill.description || '';
        document.getElementById('editSubSkillStatus').value = subSkill.status || 'not_started';
        document.getElementById('editMasterySlider').value = subSkill.mastery_level || 0;
        document.getElementById('editMasteryDisplay').textContent = (subSkill.mastery_level || 0) + '%';
        document.getElementById('editSubSkillTasks').value = subSkill.practical_tasks || '';
        document.getElementById('editSubSkillResources').value = subSkill.resources || '';
        document.getElementById('editSubSkillNotes').value = subSkill.notes || '';

        document.getElementById('editSubSkillModal').classList.remove('hidden');
    }

    function closeEditSubSkillModal() {
        document.getElementById('editSubSkillModal').classList.add('hidden');
    }

    function syncEditSubSkillStatus(status) {
        const slider = document.getElementById('editMasterySlider');
        const display = document.getElementById('editMasteryDisplay');
        if (status === 'mastered') {
            slider.value = 100;
            display.textContent = '100%';
        } else if (status === 'not_started') {
            slider.value = 0;
            display.textContent = '0%';
        } else if (status === 'in_progress' && parseInt(slider.value) === 0) {
            slider.value = 50;
            display.textContent = '50%';
        }
    }

    function deleteSubSkill(subSkillId, title) {
        if (!confirm(`هل أنت متأكد من حذف المهارة الفرعية «${title}»؟`)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/skills/${skillId}/sub-skills/${subSkillId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${appCsrfToken}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // ==========================================
    // Main Skill Modals (Edit / Delete)
    // ==========================================
    function openEditMainSkillModal() {
        document.getElementById('editMainSkillModal').classList.remove('hidden');
    }
    function closeEditMainSkillModal() {
        document.getElementById('editMainSkillModal').classList.add('hidden');
    }

    function confirmDeleteMainSkill() {
        if (!confirm('هل أنت متأكد تماماً من حذف هذه المهارة وكافة المهارات الفرعية التابعة لها؟')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/skills/${skillId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${appCsrfToken}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // ==========================================
    // AI Integration: Breakdown
    // ==========================================
    function openAiBreakdownModal() {
        const modal = document.getElementById('aiBreakdownModal');
        if (modal) {
            modal.classList.remove('hidden');
            // Auto generate if not generated yet for an instant smooth experience
            if (!currentSuggestions || currentSuggestions.length === 0) {
                requestAiBreakdown(false);
            }
        }
    }
    function closeAiBreakdownModal() {
        const modal = document.getElementById('aiBreakdownModal');
        if (modal) modal.classList.add('hidden');
    }

    function requestAiBreakdown(autoSave = false) {
        const loading = document.getElementById('aiBreakdownLoading');
        const initial = document.getElementById('aiBreakdownInitial');
        const results = document.getElementById('aiBreakdownResults');

        initial.classList.add('hidden');
        results.classList.add('hidden');
        loading.classList.remove('hidden');

        fetch(`/skills/${skillId}/ai-breakdown`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': appCsrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ auto_save: autoSave })
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.success && data.sub_skills) {
                currentSuggestions = data.sub_skills;
                renderAiSuggestions(data.sub_skills);
                results.classList.remove('hidden');
            } else {
                showToast('تعذر توليد المهارات الفرعية حالياً', 'error');
                initial.classList.remove('hidden');
            }
        })
        .catch(() => {
            loading.classList.add('hidden');
            initial.classList.remove('hidden');
            showToast('حدث خطأ أثناء الاتصال بالذكاء الاصطناعي', 'error');
        });
    }

    function renderAiSuggestions(list) {
        const container = document.getElementById('aiSuggestionsList');
        const countLabel = document.getElementById('aiGeneratedCount');
        countLabel.textContent = `تم اقتراح ${list.length} مهارات فرعية متسلسلة:`;
        container.innerHTML = '';

        list.forEach((item, idx) => {
            const card = document.createElement('div');
            card.className = 'bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700 flex items-start justify-between gap-3';
            card.innerHTML = `
                <div class="min-w-0 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 text-xs font-bold flex items-center justify-center shrink-0">${idx + 1}</span>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">${item.title}</h4>
                    </div>
                    ${item.description ? `<p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">${item.description}</p>` : ''}
                </div>
                <button type="button" onclick="addSingleAiSuggestion(${idx})" class="shrink-0 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-700 hover:bg-brand-600 hover:text-white text-brand-600 dark:text-brand-300 font-bold text-xs border border-brand-200 dark:border-slate-600 transition flex items-center gap-1">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>إضافة</span>
                </button>
            `;
            container.appendChild(card);
        });
    }

    function addSingleAiSuggestion(index) {
        const item = currentSuggestions[index];
        if (!item) return;

        fetch(`/skills/${skillId}/sub-skills`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': appCsrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: item.title,
                description: item.description || '',
                practical_tasks: item.practical_tasks || '',
                resources: item.resources || '',
                status: 'not_started',
                mastery_level: 0
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(`تمت إضافة «${item.title}» بنجاح ✓`, 'success');
                setTimeout(() => location.reload(), 600);
            }
        });
    }

    function addAllAiSuggestions() {
        if (!confirm('هل تريد إضافة جميع المهارات الفرعية المقترحة دفعة واحدة؟')) return;

        fetch(`/skills/${skillId}/ai-breakdown`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': appCsrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ auto_save: true })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 600);
            }
        });
    }

    // ==========================================
    // AI Integration: Roadmap
    // ==========================================
    function openAiRoadmapModal() {
        const modal = document.getElementById('aiRoadmapModal');
        if (modal) {
            modal.classList.remove('hidden');
            requestAiRoadmap();
        }
    }
    function closeAiRoadmapModal() {
        const modal = document.getElementById('aiRoadmapModal');
        if (modal) modal.classList.add('hidden');
    }

    function requestAiRoadmap() {
        const loading = document.getElementById('aiRoadmapLoading');
        const content = document.getElementById('aiRoadmapContent');

        loading.classList.remove('hidden');
        content.classList.add('hidden');

        fetch(`/skills/${skillId}/ai-roadmap`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': appCsrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.success && data.roadmap) {
                content.innerHTML = formatMarkdownText(data.roadmap);
                content.classList.remove('hidden');
            } else {
                showToast('تعذر جلب خطة التعلم حالياً', 'error');
            }
        })
        .catch(() => {
            loading.classList.add('hidden');
            showToast('حدث خطأ في تحميل الخطة', 'error');
        });
    }

    // Simple markdown renderer for AI responses
    function formatMarkdownText(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Bold
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Headers
        escaped = escaped.replace(/^### (.*$)/gim, '<h4 class="text-sm font-black text-brand-600 dark:text-brand-400 mt-3 mb-1">$1</h4>');
        escaped = escaped.replace(/^## (.*$)/gim, '<h3 class="text-base font-black text-slate-900 dark:text-white mt-4 mb-2 pb-1 border-b border-slate-200 dark:border-slate-700">$1</h3>');
        // Lists
        escaped = escaped.replace(/^\* (.*$)/gim, '<div class="flex items-start gap-1.5 my-1"><span class="text-brand-500 font-bold">•</span><span>$1</span></div>');
        escaped = escaped.replace(/^- (.*$)/gim, '<div class="flex items-start gap-1.5 my-1"><span class="text-teal-500 font-bold">•</span><span>$1</span></div>');

        return escaped;
    }

    // Explicitly bind all modal and action functions to window for 100% reliable inline event handling
    window.openAddSubSkillModal = openAddSubSkillModal;
    window.closeAddSubSkillModal = closeAddSubSkillModal;
    window.syncNewSubSkillStatus = syncNewSubSkillStatus;
    window.editSubSkill = editSubSkill;
    window.closeEditSubSkillModal = closeEditSubSkillModal;
    window.syncEditSubSkillStatus = syncEditSubSkillStatus;
    window.deleteSubSkill = deleteSubSkill;
    window.openEditMainSkillModal = openEditMainSkillModal;
    window.closeEditMainSkillModal = closeEditMainSkillModal;
    window.confirmDeleteMainSkill = confirmDeleteMainSkill;
    window.openAiBreakdownModal = openAiBreakdownModal;
    window.closeAiBreakdownModal = closeAiBreakdownModal;
    window.requestAiBreakdown = requestAiBreakdown;
    window.addAllAiSuggestions = addAllAiSuggestions;
    window.addSingleAiSuggestion = addSingleAiSuggestion;
    window.openAiRoadmapModal = openAiRoadmapModal;
    window.closeAiRoadmapModal = closeAiRoadmapModal;
    window.toggleSubSkillDetails = toggleSubSkillDetails;
    window.scrollToSubSkill = scrollToSubSkill;
    window.updateMasterySliderText = updateMasterySliderText;
    window.saveMasteryLevel = saveMasteryLevel;
    window.cycleSubSkillStatus = cycleSubSkillStatus;
    window.moveSubSkill = moveSubSkill;

    // Close modals when clicking on background overlay
    document.querySelectorAll('#addSubSkillModal, #editSubSkillModal, #editMainSkillModal, #aiBreakdownModal, #aiRoadmapModal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>
@endpush
@endsection
