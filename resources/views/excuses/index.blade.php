@extends('layouts.app')

@section('title', 'تحليل أسباب عدم الالتزام (Excuses I Overcome) — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2 font-mono">
                <i class="fa-solid fa-shield-halved text-brand-600"></i>
                Excuses I Overcome
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                لا نكتفي بتسجيل الأعذار؛ بل نحللها ونضع خططاً عملية للتغلب عليها وبناء الانضباط الصارم
            </p>
        </div>

        <button onclick="openAddExcuseModal()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>تسجيل عذر وخطة تجاوزه</span>
        </button>
    </div>

    <!-- 3 KPI Cards for Excuses -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400">إجمالي العقبات المسجلة</span>
                <p class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-1">{{ $totalExcuses }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-base">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400">عقبات تم التغلب عليها</span>
                <p class="text-2xl font-black text-teal-600 font-mono mt-1">{{ $overcameCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="flex-1 mr-3">
                <span class="text-xs font-bold text-brand-600 dark:text-brand-400">نسبة الانتصار على الأعذار</span>
                <p class="text-2xl font-black text-brand-600 dark:text-brand-400 font-mono mt-1">{{ $overcameRate }}%</p>
                <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-brand-500 h-full rounded-full" style="width: {{ $overcameRate }}%;"></div>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-base">
                <i class="fa-solid fa-medal"></i>
            </div>
        </div>
    </div>

    <!-- Excuses List -->
    <div class="space-y-3">
        @php
            $typeLabels = [
                'time_shortage' => ['label' => 'ضيق الوقت', 'icon' => 'fa-clock', 'color' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 border-slate-200 dark:border-slate-600'],
                'fatigue' => ['label' => 'التعب والإرهاق', 'icon' => 'fa-bed', 'color' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 border-slate-200 dark:border-slate-600'],
                'distraction' => ['label' => 'التشتت والسوشيال ميديا', 'icon' => 'fa-mobile-screen', 'color' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 border-slate-200 dark:border-slate-600'],
                'no_motivation' => ['label' => 'عدم الشعور بالرغبة', 'icon' => 'fa-face-meh', 'color' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 border-slate-200 dark:border-slate-600'],
                'other' => ['label' => 'سبب آخر', 'icon' => 'fa-circle-question', 'color' => 'text-slate-500 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700'],
            ];
        @endphp

        @forelse($excuses as $item)
        @php
            $typeConfig = $typeLabels[$item->excuse_type] ?? $typeLabels['other'];
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-800 border {{ $item->overcame ? 'border-teal-200 dark:border-teal-900 bg-teal-50/15 dark:bg-teal-950/10' : 'border-slate-200 dark:border-slate-700' }} shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
            
            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                <!-- Toggle Button Overcame -->
                <button type="button" onclick="toggleExcuseStatus({{ $item->id }}, this)" class="mt-1 w-7 h-7 rounded-xl border-2 {{ $item->overcame ? 'bg-teal-600 border-teal-600 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent hover:border-teal-500' }} flex items-center justify-center text-xs transition shrink-0 shadow-sm" title="تبديل حالة التغلب">
                    <i class="fa-solid fa-check"></i>
                </button>

                <div class="min-w-0 space-y-1 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Excuse Reason Title -->
                        <h3 class="text-sm font-bold {{ $item->overcame ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-900 dark:text-white' }}">
                            {{ $item->excuse_details }}
                        </h3>

                        <!-- Type Badge -->
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border flex items-center gap-1 {{ $typeConfig['color'] }}">
                            <i class="fa-solid {{ $typeConfig['icon'] }} text-[9px]"></i>
                            {{ $typeConfig['label'] }}
                        </span>

                        <!-- Linked Habit or Task -->
                        @if($item->habit)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white" style="background-color: {{ $item->habit->color }}">
                            {{ $item->habit->name }}
                        </span>
                        @endif

                        @if($item->overcame)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300">
                            ✓ تم التغلب عليه
                        </span>
                        @endif
                    </div>

                    <!-- How to Overcome Strategy -->
                    @if($item->how_to_overcome)
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300 mt-1.5 flex items-start gap-2">
                        <span class="text-brand-600 font-bold shrink-0">💡 خطة التغلب:</span>
                        <p class="leading-relaxed">{{ $item->how_to_overcome }}</p>
                    </div>
                    @endif

                    <span class="text-[11px] text-slate-400 block pt-0.5 font-mono">
                        تاريخ التسجيل: {{ $item->date->format('Y-m-d') }}
                    </span>
                </div>
            </div>

            <!-- Delete Action -->
            <div class="self-end sm:self-center shrink-0">
                <form action="{{ route('excuses.destroy', $item->id) }}" method="POST" onsubmit="return confirm('حذف هذا العذر؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="حذف">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </form>
            </div>

        </div>
        @empty
        <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
            <i class="fa-solid fa-shield-halved text-4xl text-slate-300 dark:text-slate-600 mb-3"></i>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">لا توجد أعذار مسجلة</h3>
            <p class="text-xs text-slate-400 mt-1">عندما تعترضك عقبة في الالتزام، سجلها هنا لتحليلها ووضع خطة لتجاوزها</p>
            <button onclick="openAddExcuseModal()" class="mt-4 px-4 py-2 text-xs font-bold bg-accent-500 hover:bg-accent-600 text-white rounded-xl shadow-md transition">
                + تسجيل عذر وخطة تجاوزه
            </button>
        </div>
        @endforelse

        <div class="pt-4">
            {{ $excuses->links() }}
        </div>
    </div>

</div>

<!-- Modal: Add Excuse & Overcome Strategy -->
<div id="addExcuseModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-shield text-accent-500"></i>
                تسجيل عذر وخطة التغلب عليه
            </h3>
            <button onclick="closeAddExcuseModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('excuses.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ما هو العذر أو سبب عدم الإنجاز؟ *</label>
                <input type="text" name="excuse_details" required placeholder="مثال: I need time / شعرت بالتعب وأجلت التمرين..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نوع العذر</label>
                    <select name="excuse_type" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent-500">
                        <option value="fatigue">التعب والإرهاق 😴</option>
                        <option value="time_shortage">ضيق الوقت ⏳</option>
                        <option value="distraction">التشتت بالسوشيال ميديا 📱</option>
                        <option value="no_motivation">عدم الشعور بالرغبة 😐</option>
                        <option value="other">سبب آخر ❓</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ الحدوث</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ربط بعادة معينة (اختياري)</label>
                <select name="habit_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent-500">
                    <option value="">غير مرتبط بعادة محددة</option>
                    @foreach($habits as $h)
                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    💡 كيف ستتغلب على هذا العذر مستقبلاً؟ (How to Overcome)
                </label>
                <textarea name="how_to_overcome" rows="3" placeholder="مثال: تطبيق قاعدة الـ 5 دقائق، تجهيز الملابس من المساء، إبعاد الهاتف خارج غرفة النوم..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent-500"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="overcameCheck" name="overcame" value="1" class="rounded text-teal-600 focus:ring-teal-500">
                <label for="overcameCheck" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    تم التغلب على هذا العذر بالفعل بنجاح 💪
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeAddExcuseModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-accent-500 hover:bg-accent-600 text-white rounded-xl shadow-md transition">
                    حفظ العذر وخطة تجاوزه
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddExcuseModal() {
        document.getElementById('addExcuseModal').classList.remove('hidden');
    }
    function closeAddExcuseModal() {
        document.getElementById('addExcuseModal').classList.add('hidden');
    }

    function toggleExcuseStatus(excuseId, btn) {
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
                showToast(data.overcame ? 'أحسنت! تم وضع علامة التغلب على العذر ✓' : 'تم تغيير الحالة', 'success');
                setTimeout(() => window.location.reload(), 350);
            }
        });
    }
</script>
@endpush
@endsection
