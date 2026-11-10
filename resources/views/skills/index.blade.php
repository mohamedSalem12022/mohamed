@extends('layouts.app')

@section('title', 'إتقان مهارة — Skill Mastery | MO Life OS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="flex items-center gap-4 z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-brand-500/20 text-2xl shrink-0">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        إتقان المهارات <span class="text-brand-600 dark:text-brand-400">Skill Mastery</span>
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                        <i class="fa-solid fa-layer-group text-[10px]"></i>
                        تفكيك وتعلّم تدريجي
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                    اختر المهارة التي تريد إتقانها، قسّمها إلى مهارات فرعية متسلسلة، وتتبّع مستوى إتقانك وتطبيقاتك العملية خطوة بخطوة حتى تصل إلى الاحتراف.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 z-10 w-full md:w-auto">
            <button type="button" onclick="openCreateSkillModal()" class="w-full md:w-auto px-5 py-3 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>إضافة مهارة رئيسية جديدة</span>
            </button>
        </div>

        <!-- Decorative background glow -->
        <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Stats Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Skills -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">المهارات الرئيسية</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $totalSkills }}</h3>
            </div>
        </div>

        <!-- Card 2: Average Progress -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">متوسط الإتقان العام</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $averageProgress }}%</h3>
                </div>
            </div>
        </div>

        <!-- Card 3: Mastered Subskills -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-award"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">مهارات فرعية متقنة</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    {{ $masteredSubSkills }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalSubSkills }}</span>
                </h3>
            </div>
        </div>

        <!-- Card 4: Fully Mastered Skills -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">مهارات أُتقنت بالكامل</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $completedSkills }}</h3>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search Input -->
        <form method="GET" action="{{ route('skills.index') }}" class="relative w-full sm:w-80">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="ابحث عن مهارة أو هدف..." class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="absolute left-3 top-2.5 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </button>
        </form>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto w-full sm:w-auto py-1">
            <a href="{{ route('skills.index', ['status' => 'all', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                الكل ({{ $totalSkills }})
            </a>
            <a href="{{ route('skills.index', ['status' => 'in_progress', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'in_progress' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                قيد التعلم ({{ $inProgressSkills }})
            </a>
            <a href="{{ route('skills.index', ['status' => 'completed', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'completed' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                المكتملة ({{ $completedSkills }})
            </a>
        </div>
    </div>

    <!-- Skills Cards Grid -->
    @if($skills->isEmpty())
        <!-- Empty State -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-10 text-center border border-slate-200 dark:border-slate-700 space-y-4 max-w-xl mx-auto my-6 shadow-xs">
            <div class="w-16 h-16 rounded-3xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-base font-black text-slate-900 dark:text-white">
                    @if(!empty($search))
                        لم يتم العثور على مهارات تطابق بحثك «{{ $search }}»
                    @else
                        ابدأ رحلتك في إتقان أول مهارة الآن!
                    @endif
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                    أي مهارة كبيرة في العالم تصبح بسيطة عند تقسيمها إلى مهارات فرعية واضحة. اختر مهارة ترغب بتعلمها (مثل: صناعة المحتوى، البرمجة، التسويق، أو لغة جديدة).
                </p>
            </div>
            <div class="pt-2">
                <button type="button" onclick="openCreateSkillModal()" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة أول مهارة الآن</span>
                </button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($skills as $skill)
                @php
                    $progress = $skill->progressPercentage();
                    $totalSub = $skill->totalSubSkillsCount();
                    $mastered = $skill->masteredCount();
                    $inProgress = $skill->inProgressCount();
                    $notStarted = $skill->notStartedCount();
                    $nextSubSkill = $skill->nextRecommendedSubSkill();
                    $timeline = $skill->timelineStatus();
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col justify-between hover:border-brand-500/50 hover:shadow-md transition-all duration-200 group">
                    <!-- Top Card Content -->
                    <div class="space-y-4">
                        <!-- Header: Icon, Title, Dropdown -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-xl shadow-md shrink-0 transition-transform group-hover:scale-105" style="background-color: {{ $skill->color ?? '#0d9488' }};">
                                    <i class="{{ $skill->icon ?? 'fa-solid fa-graduation-cap' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('skills.show', $skill) }}" class="text-base font-black text-slate-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition truncate block">
                                        {{ $skill->title }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $timeline['class'] }}">
                                            {{ $timeline['text'] }}
                                        </span>
                                        @if($progress === 100)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-300">
                                            متقنة ✓
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Menu -->
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" onclick="editSkill({{ $skill->toJson() }})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition" title="تعديل المهارة">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button type="button" onclick="confirmDeleteSkill({{ $skill->id }}, '{{ addslashes($skill->title) }}')" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center transition" title="حذف المهارة">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Description & Goal -->
                        @if($skill->description || $skill->goal)
                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                            @if($skill->goal)
                            <div class="flex items-start gap-1.5 text-slate-700 dark:text-slate-200 font-semibold bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
                                <i class="fa-solid fa-bullseye text-brand-600 mt-0.5 text-[11px] shrink-0"></i>
                                <span class="line-clamp-2"><strong>الهدف:</strong> {{ $skill->goal }}</span>
                            </div>
                            @endif
                            @if($skill->description)
                            <p class="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                                {{ $skill->description }}
                            </p>
                            @endif
                        </div>
                        @endif

                        <!-- Progress Bar Section -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-200">مستوى الإتقان</span>
                                <span class="font-black text-brand-600 dark:text-brand-400">{{ $progress }}%</span>
                            </div>
                            <!-- Bar -->
                            <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-brand-600 to-teal-400 rounded-full transition-all duration-500" style="width: {{ $progress }}%;"></div>
                            </div>
                            <!-- Subskills counts -->
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-0.5">
                                <span>{{ $mastered }} من {{ $totalSub }} مهارات متقنة</span>
                                <span>{{ $inProgress }} قيد التعلم</span>
                            </div>
                        </div>

                        <!-- Next Recommended Step Spotlight -->
                        @if($nextSubSkill)
                        <div class="p-2.5 rounded-2xl bg-teal-50/70 dark:bg-teal-950/40 border border-teal-100 dark:border-teal-900/60 flex items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <i class="fa-solid fa-bolt text-teal-600 text-[11px] shrink-0 animate-pulse"></i>
                                <div class="min-w-0">
                                    <span class="text-[10px] text-teal-700 dark:text-teal-400 font-bold block">الخطوة القادمة للعمل:</span>
                                    <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $nextSubSkill->title }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-white dark:bg-slate-800 text-teal-700 dark:text-teal-300 shadow-2xs shrink-0">
                                {{ $nextSubSkill->statusLabel() }}
                            </span>
                        </div>
                        @elseif($totalSub === 0)
                        <div class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-center text-xs text-slate-400">
                            <i class="fa-solid fa-list-check text-xs ml-1"></i> لم يتم إضافة مهارات فرعية بعد
                        </div>
                        @else
                        <div class="p-2.5 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-center text-xs text-teal-700 dark:text-teal-300 font-bold">
                            <i class="fa-solid fa-circle-check text-xs ml-1"></i> تم إتقان جميع المتطلبات الفرعية بنجاح!
                        </div>
                        @endif
                    </div>

                    <!-- Card Footer: Go to show page -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/80 mt-4">
                        <a href="{{ route('skills.show', $skill) }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-50 hover:bg-brand-50 dark:bg-slate-900/60 dark:hover:bg-brand-950/40 text-slate-700 hover:text-brand-600 dark:text-slate-200 dark:hover:text-brand-400 text-xs font-bold transition flex items-center justify-center gap-2 group-hover:bg-brand-600 group-hover:text-white shadow-2xs">
                            <span>متابعة تفاصيل المهارة والتعلم</span>
                            <i class="fa-solid fa-arrow-left text-[11px] group-hover:-translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<!-- Modal: Create / Edit Skill -->
<div id="skillModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 id="skillModalTitle" class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-brand-600"></i>
                <span>إضافة مهارة رئيسية جديدة</span>
            </h3>
            <button type="button" onclick="closeSkillModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="skillForm" method="POST" action="{{ route('skills.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="skillFormMethod" value="POST">

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    اسم المهارة الرئيسية <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="skillTitle" required placeholder="مثال: صناعة المحتوى، البرمجة بلغة Python، التصوير الفوتوغرافي" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وصف مختصر</label>
                <textarea name="description" id="skillDescription" rows="2" placeholder="اكتب نبذة مختصرة عن نطاق هذه المهارة وما تحتويه..." class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Goal -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الهدف من التعلم والنتيجة المستهدفة</label>
                <textarea name="goal" id="skillGoal" rows="2" placeholder="مثال: إطلاق قناة يوتيوب والحصول على 10,000 مشترك، أو العمل كمطور برمجيات مستقل" class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Dates Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ البدء</label>
                    <input type="date" name="start_date" id="skillStartDate" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الموعد المستهدف للإتقان</label>
                    <input type="date" name="target_date" id="skillTargetDate" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <!-- Icon & Color Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الأيقونة</label>
                    <select name="icon" id="skillIcon" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="fa-solid fa-graduation-cap">🎓 تعلّم عام (قبعة التخرج)</option>
                        <option value="fa-solid fa-video">🎬 صناعة المحتوى والفيديو</option>
                        <option value="fa-solid fa-code">💻 البرمجة والتقنية</option>
                        <option value="fa-solid fa-camera">📸 التصوير والفوتوغراف</option>
                        <option value="fa-solid fa-pen-nib">✍️ الكتابة والتأليف</option>
                        <option value="fa-solid fa-bullhorn">📢 التسويق والتواصل</option>
                        <option value="fa-solid fa-brain">🧠 عقل وتطوير ذاتي</option>
                        <option value="fa-solid fa-palette">🎨 التصميم والفنون</option>
                        <option value="fa-solid fa-language">🌐 اللغات والترجمة</option>
                        <option value="fa-solid fa-briefcase">💼 الأعمال والريادة</option>
                        <option value="fa-solid fa-music">🎵 الموسيقى والصوت</option>
                        <option value="fa-solid fa-dumbbell">💪 اللياقة والرياضة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">اللون المميز</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color" id="skillColor" value="#0d9488" class="w-10 h-9 p-0.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 cursor-pointer">
                        <span class="text-xs text-slate-500 dark:text-slate-400">اختر لوناً مميزاً للمهارة</span>
                    </div>
                </div>
            </div>

            <!-- Status (For Edit) -->
            <div id="skillStatusContainer" class="hidden">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">حالة المهارة</label>
                <select name="status" id="skillStatus" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                    <option value="in_progress">قيد التعلم (نشطة)</option>
                    <option value="completed">تم الإتقان بالكامل ✓</option>
                    <option value="paused">معلقة مؤقتاً</option>
                </select>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeSkillModal()" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" id="skillModalSubmitBtn" class="px-5 py-2.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>حفظ المهارة والبدء</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const skillModal = document.getElementById('skillModal');
    const skillForm = document.getElementById('skillForm');
    const skillModalTitle = document.getElementById('skillModalTitle');
    const skillFormMethod = document.getElementById('skillFormMethod');
    const skillStatusContainer = document.getElementById('skillStatusContainer');

    function openCreateSkillModal() {
        skillForm.reset();
        skillForm.action = "{{ route('skills.store') }}";
        skillFormMethod.value = "POST";
        skillModalTitle.innerHTML = '<i class="fa-solid fa-graduation-cap text-brand-600"></i><span>إضافة مهارة رئيسية جديدة</span>';
        skillStatusContainer.classList.add('hidden');
        document.getElementById('skillStartDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('skillColor').value = "#0d9488";
        skillModal.classList.remove('hidden');
    }

    function editSkill(skill) {
        skillForm.reset();
        skillForm.action = `/skills/${skill.id}`;
        skillFormMethod.value = "PUT";
        skillModalTitle.innerHTML = '<i class="fa-solid fa-pen text-brand-600"></i><span>تعديل المهارة الرئيسية</span>';
        
        document.getElementById('skillTitle').value = skill.title || '';
        document.getElementById('skillDescription').value = skill.description || '';
        document.getElementById('skillGoal').value = skill.goal || '';
        document.getElementById('skillIcon').value = skill.icon || 'fa-solid fa-graduation-cap';
        document.getElementById('skillColor').value = skill.color || '#0d9488';
        
        if (skill.start_date) {
            document.getElementById('skillStartDate').value = skill.start_date.substring(0, 10);
        }
        if (skill.target_date) {
            document.getElementById('skillTargetDate').value = skill.target_date.substring(0, 10);
        }

        document.getElementById('skillStatus').value = skill.status || 'in_progress';
        skillStatusContainer.classList.remove('hidden');

        skillModal.classList.remove('hidden');
    }

    function closeSkillModal() {
        skillModal.classList.add('hidden');
    }

    function confirmDeleteSkill(id, title) {
        if (!confirm(`هل أنت متأكد من حذف مهارة «${title}» وكافة المهارات الفرعية التابعة لها؟`)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/skills/${id}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush
@endsection
