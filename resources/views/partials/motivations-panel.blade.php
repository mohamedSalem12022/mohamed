<!-- Motivations Floating Trigger Button -->
<button id="motivationsToggleBtn" type="button" onclick="toggleMotivationsPanel()" class="fixed bottom-5 {{ ($currentLocale ?? 'ar') === 'ar' ? 'right-5' : 'left-5' }} z-40 flex items-center gap-2 px-3.5 py-2.5 rounded-full bg-gradient-to-r from-brand-600 to-teal-500 text-white font-bold text-xs shadow-lg shadow-brand-500/25 hover:shadow-xl hover:scale-105 active:scale-95 transition-all group focus:outline-none" title="شريط محفّزاتي">
    <i class="fa-solid fa-lightbulb text-teal-200 group-hover:scale-110 transition-transform text-sm"></i>
    <span class="font-bold tracking-wide">محفّزاتي</span>
    <span id="motivationsBadgeCount" class="px-1.5 py-0.5 rounded-full bg-white/25 text-[10px] font-black leading-none min-w-[18px] text-center">0</span>
</button>

<!-- Slide-Over Backdrop Overlay -->
<div id="motivationsBackdrop" onclick="closeMotivationsPanel()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden opacity-0 transition-opacity duration-300"></div>

<!-- Slide-Over Panel Container -->
<aside id="motivationsPanel" class="fixed top-0 bottom-0 {{ ($currentLocale ?? 'ar') === 'ar' ? 'right-0' : 'left-0' }} z-50 w-full sm:w-[430px] max-w-[94vw] bg-white dark:bg-slate-900 shadow-2xl border-{{ ($currentLocale ?? 'ar') === 'ar' ? 'l' : 'r' }} border-slate-200 dark:border-slate-800 flex flex-col transform transition-transform duration-300 ease-in-out {{ ($currentLocale ?? 'ar') === 'ar' ? 'translate-x-full' : '-translate-x-full' }}" aria-label="شريط محفّزاتي">
    
    <!-- Panel Header -->
    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center shadow-md shadow-brand-500/20 text-base shrink-0">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>محفّزاتي</span>
                    <span id="motivationsHeaderCount" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-800">
                        0 محفّز
                    </span>
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">حافظ على انضباطك واستمر في التقدم نحو أهدافك</p>
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            <button type="button" onclick="openAiMotivationModal()" class="px-2.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all hover:scale-105 active:scale-95" title="توليد محفّز بذكاء MO AI">
                <i class="fa-solid fa-wand-magic-sparkles text-[10px] text-teal-200"></i>
                <span class="hidden sm:inline">توليد ذكي ✨</span>
            </button>
            <button type="button" onclick="openAddMotivationModal()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 shadow-xs transition-all hover:scale-105 active:scale-95" title="إضافة محفّز جديد">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span class="hidden sm:inline">إضافة</span>
            </button>
            <button type="button" onclick="closeMotivationsPanel()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition" title="إغلاق الشريط">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Random Inspiration Card (Featured Quote) -->
    <div id="randomMotivationContainer" class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 shrink-0">
        <div class="relative p-3.5 rounded-2xl bg-brand-50/40 dark:bg-brand-950/20 border border-brand-200/80 dark:border-brand-800/60 shadow-xs">
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-brand-200/50 dark:border-brand-800/40 text-xs">
                <span class="font-bold text-brand-800 dark:text-brand-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-brand-600"></i>
                    <span>إلهام اليوم / عشوائي</span>
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openAiMotivationModal()" class="text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 flex items-center gap-1 hover:scale-105 transition" title="توليد محفّز بذكاء MO AI">
                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                        <span>توليد ذكي ✨</span>
                    </button>
                    <button type="button" onclick="fetchRandomMotivation()" class="text-[11px] font-bold text-slate-600 dark:text-slate-400 hover:text-brand-600 flex items-center gap-1 hover:scale-105 transition" title="محفّز عشوائي آخر">
                        <i class="fa-solid fa-shuffle text-[10px]"></i>
                        <span>تبديل</span>
                    </button>
                </div>
            </div>
            
            <div id="randomMotivationBody">
                <p id="randomMotivationQuote" class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed italic">
                    «ابدأ الآن، حتى لو بخطوة صغيرة.. فالاستمرار يصنع المعجزات.»
                </p>
                <div class="flex items-center justify-between mt-2 pt-1 text-[11px]">
                    <span id="randomMotivationTitle" class="font-bold text-brand-700 dark:text-brand-400">الخطوة الأولى</span>
                    <span id="randomMotivationCategory" class="px-2 py-0.5 rounded-md bg-white/80 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold">انضباط</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter / Category Tabs -->
    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-1.5 overflow-x-auto scrollbar-none text-xs shrink-0">
        <button type="button" onclick="filterMotivations('all', this)" class="motivation-filter-btn px-2.5 py-1 rounded-lg font-bold transition whitespace-nowrap bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400">
            الكل (<span id="filterAllCount">0</span>)
        </button>
        <button type="button" onclick="filterMotivations('pinned', this)" class="motivation-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition whitespace-nowrap">
            📌 المثبتة (<span id="filterPinnedCount">0</span>)
        </button>
    </div>

    <!-- Scrollable Motivations List -->
    <div id="motivationsListContainer" class="flex-1 overflow-y-auto p-4 space-y-3">
        <!-- Loading State -->
        <div id="motivationsLoading" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-spinner fa-spin text-2xl text-brand-600 mb-2"></i>
            <p class="text-xs font-semibold">جاري تحميل المحفزات...</p>
        </div>

        <!-- Empty State -->
        <div id="motivationsEmptyState" class="hidden py-10 px-4 text-center space-y-3">
            <div class="w-16 h-16 rounded-3xl bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <h4 class="text-sm font-black text-slate-900 dark:text-white">لم تقم بإضافة أي محفّز بعد</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs mx-auto">
                اكتب عباراتك المفضلة وأهدافك لتذكر نفسك بها دائماً عند الحاجة وتظل ملتزماً ومنجزاً.
            </p>
            <div class="pt-2 flex flex-col gap-2 max-w-xs mx-auto">
                <button type="button" onclick="openAddMotivationModal()" class="w-full px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition">
                    + أضف أول محفّز
                </button>
                <button type="button" onclick="seedDefaultMotivations()" class="w-full px-4 py-2 rounded-xl bg-brand-100 dark:bg-brand-950/60 text-brand-800 dark:text-brand-300 hover:bg-brand-200 dark:hover:bg-brand-900 text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i>
                    <span>تحميل 5 محفزات مقترحة للتأسيس</span>
                </button>
            </div>
        </div>

        <!-- Motivations Dynamic Cards Container -->
        <div id="motivationsList" class="space-y-3"></div>
    </div>

    <!-- Panel Footer -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-center text-[11px] text-slate-400 shrink-0">
        <p>MO Life OS &bull; خطوة صغيرة كل يوم تصنع فارقاً عظيماً</p>
    </div>

</aside>

<!-- Modal: Add / Edit Motivation -->
<div id="motivationModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 animate-scale-in" dir="rtl">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 id="motivationModalTitle" class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-brand-600"></i>
                <span>إضافة محفّز جديد</span>
            </h3>
            <button type="button" onclick="closeMotivationModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="motivationForm" onsubmit="saveMotivation(event)" class="space-y-3.5">
            <input type="hidden" id="motivationId" value="">

            <!-- Quick AI Suggestion Banner -->
            <div class="p-2.5 rounded-2xl bg-brand-50/60 dark:bg-brand-950/40 border border-brand-200 dark:border-brand-800/60 flex items-center justify-between gap-2 text-xs">
                <span class="font-bold text-brand-900 dark:text-brand-200 flex items-center gap-1.5 text-[11px]">
                    <i class="fa-solid fa-wand-magic-sparkles text-brand-600 text-xs"></i>
                    <span>هل تريد اقتراحاً ملهماً من الذكاء الاصطناعي؟</span>
                </span>
                <button type="button" onclick="generateAiForForm()" id="formAiGenBtn" class="px-3 py-1 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] shadow-sm transition-all hover:scale-105 flex items-center gap-1 shrink-0">
                    <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                    <span>توليد فوري ✨</span>
                </button>
            </div>

            <!-- Title -->
            <div>
                <label for="motivationTitle" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                    عنوان المحفّز <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="motivationTitle" required maxlength="150" placeholder="مثال: الخطوة الأولى، سر الانضباط..." class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Quote / Text -->
            <div>
                <label for="motivationQuote" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                    نص المحفّز أو العبارة <span class="text-rose-500">*</span>
                </label>
                <textarea id="motivationQuote" required rows="3" maxlength="1000" placeholder="اكتب العبارة التحفيزية الملهمة التي تبقيك على المسار الصحيح..." class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Category & Icon Selection -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="motivationCategory" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                        التصنيف (اختياري)
                    </label>
                    <input type="text" id="motivationCategory" maxlength="60" list="categoriesList" placeholder="انضباط، تركيز..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <datalist id="categoriesList">
                        <option value="انضباط">
                        <option value="تركيز">
                        <option value="استمرار">
                        <option value="إلهام">
                        <option value="صحة">
                        <option value="عمل">
                        <option value="عام">
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                        الأيقونة (اختياري)
                    </label>
                    <select id="motivationIcon" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="fa-lightbulb">💡 فكرة / إلهام</option>
                        <option value="fa-bolt">⚡ طاقة وانضباط</option>
                        <option value="fa-bullseye">🎯 هدف وتركيز</option>
                        <option value="fa-play">▶️ بداية فورية</option>
                        <option value="fa-arrow-trend-up">📈 استمرار وتقدم</option>
                        <option value="fa-compass">🧭 وضوح الاتجاه</option>
                        <option value="fa-rocket">🚀 طاقة وإنجاز</option>
                        <option value="fa-crown">👑 تفوق وتميز</option>
                        <option value="fa-star">⭐ تميز</option>
                        <option value="fa-sun">☀️ أمل وتجدد</option>
                        <option value="fa-quote-right">💬 حكمة واقتباس</option>
                    </select>
                </div>
            </div>

            <!-- Pin Checkbox -->
            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" id="motivationIsPinned" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    <span>تثبيت هذا المحفّز في الأعلى دائماً 📌</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeMotivationModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                    إلغاء
                </button>
                <button type="submit" id="saveMotivationBtn" class="px-5 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md transition hover:scale-[1.02] active:scale-95">
                    حفظ المحفّز
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: AI Motivation Generator -->
<div id="aiMotivationModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 animate-scale-in" dir="rtl">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xs shadow-md shadow-brand-500/20">
                    <i class="fa-solid fa-wand-magic-sparkles text-teal-200"></i>
                </div>
                <span>توليد محفّز بذكاء MO AI</span>
            </h3>
            <button type="button" onclick="closeAiMotivationModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            يحلل الذكاء الاصطناعي مهامك وسلسلة التزامك ليولد لك محفّزاً شخصياً يمنحك طاقة فورية ويواكب واقعك اليومي.
        </p>

        <!-- Topic Chips -->
        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">اختر تركيز المحفّز أو اكتب موضوعاً:</label>
            <div class="flex flex-wrap gap-1.5">
                <button type="button" onclick="selectAiTopic('انضباط وقوة إرادة')" class="ai-topic-chip text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-950/60 dark:hover:text-brand-300 transition">
                    🎯 انضباط وإرادة
                </button>
                <button type="button" onclick="selectAiTopic('قهر التسويف والبدء فوراً')" class="ai-topic-chip text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-950/60 dark:hover:text-brand-300 transition">
                    ⚡ قهر التسويف
                </button>
                <button type="button" onclick="selectAiTopic('تركيز وصفاء ذهني')" class="ai-topic-chip text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-950/60 dark:hover:text-brand-300 transition">
                    🎯 تركيز وصفاء
                </button>
                <button type="button" onclick="selectAiTopic('الاستمرار والثبات')" class="ai-topic-chip text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-950/60 dark:hover:text-brand-300 transition">
                    📈 استمرار وثبات
                </button>
                <button type="button" onclick="selectAiTopic('مواكبة مهامي الحالية')" class="ai-topic-chip text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-950/60 dark:hover:text-brand-300 transition">
                    📋 مواكبة مهامي
                </button>
            </div>
            <input type="text" id="aiTopicInput" placeholder="أو اكتب موضوعاً مخصصاً (مثال: قبل موعد تسليم، مذاكرة...)" class="mt-2 w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>

        <!-- Generate Button -->
        <div class="pt-1">
            <button type="button" id="triggerAiGenBtn" onclick="executeAiMotivationGeneration()" class="w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-teal-500 hover:from-brand-700 hover:to-teal-600 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition-all hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-teal-200 text-xs"></i>
                <span id="triggerAiGenBtnText">توليد المحفّز الذكي الآن ✨</span>
            </button>
        </div>

        <!-- Generated Preview Container -->
        <div id="aiGeneratedPreview" class="hidden p-3.5 rounded-2xl bg-brand-50/40 dark:bg-brand-950/30 border-2 border-brand-300 dark:border-brand-700 space-y-2.5 animate-scale-in">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span id="aiPreviewIcon" class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-200 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-lightbulb"></i>
                    </span>
                    <strong id="aiPreviewTitle" class="text-slate-900 dark:text-white text-xs sm:text-sm"></strong>
                </div>
                <span id="aiPreviewCategory" class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-brand-700 dark:text-brand-300 text-[10px] font-bold"></span>
            </div>
            <p id="aiPreviewQuote" class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed italic whitespace-pre-line"></p>
            
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-brand-200/60 dark:border-brand-800/50">
                <button type="button" onclick="executeAiMotivationGeneration()" class="px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">
                    🔄 توليد آخر
                </button>
                <button type="button" id="adoptAiMotivationBtn" onclick="adoptAiGeneratedMotivation()" class="px-4 py-1.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-lg shadow-sm transition flex items-center gap-1.5 hover:scale-[1.02]">
                    <i class="fa-solid fa-check text-[10px]"></i>
                    <span>حفظ إلى محفّزاتي مباشرة ✓</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Confirm Delete Motivation -->
<div id="deleteMotivationModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-sm w-full p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-700 text-center space-y-3" dir="rtl">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center mx-auto text-xl">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">تأكيد حذف المحفّز</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            هل أنت متأكد من حذف محفّز <strong id="deleteTargetTitle" class="text-slate-800 dark:text-slate-200"></strong>؟ لا يمكن التراجع عن هذه الخطوة.
        </p>
        <div class="flex items-center justify-center gap-2 pt-2">
            <button type="button" onclick="closeDeleteMotivationModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                إلغاء
            </button>
            <button type="button" id="confirmDeleteBtn" onclick="executeDeleteMotivation()" class="px-5 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-md transition">
                نعم، احذف
            </button>
        </div>
    </div>
</div>

<!-- Motivations JavaScript Logic -->
<script>
    let userMotivations = [];
    let currentMotivationFilter = 'all';
    let targetDeleteMotivationId = null;
    let lastAiGeneratedMotivation = null;

    // AI Motivation Generator Controls
    function openAiMotivationModal() {
        document.getElementById('aiTopicInput').value = '';
        document.getElementById('aiGeneratedPreview').classList.add('hidden');
        document.getElementById('aiMotivationModal').classList.remove('hidden');
    }

    function closeAiMotivationModal() {
        document.getElementById('aiMotivationModal').classList.add('hidden');
    }

    function selectAiTopic(topic) {
        document.getElementById('aiTopicInput').value = topic;
        executeAiMotivationGeneration();
    }

    function executeAiMotivationGeneration() {
        const topic = document.getElementById('aiTopicInput').value.trim();
        const btn = document.getElementById('triggerAiGenBtn');
        const btnText = document.getElementById('triggerAiGenBtnText');
        const preview = document.getElementById('aiGeneratedPreview');

        btn.disabled = true;
        btnText.textContent = 'جاري الابتكار بذكاء MO AI...';

        fetch('{{ route("motivations.generate-ai") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ topic: topic })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btnText.textContent = 'توليد المحفّز الذكي الآن ✨';

            if (data.success && data.motivation) {
                lastAiGeneratedMotivation = data.motivation;
                document.getElementById('aiPreviewTitle').textContent = data.motivation.title;
                document.getElementById('aiPreviewQuote').textContent = `«${data.motivation.quote}»`;
                document.getElementById('aiPreviewCategory').textContent = data.motivation.category || 'انضباط';
                const aiGenIcon = (data.motivation.icon && data.motivation.icon !== 'fa-calendar-check' && data.motivation.icon !== 'fa-fire') ? data.motivation.icon : 'fa-lightbulb';
                document.getElementById('aiPreviewIcon').innerHTML = `<i class="fa-solid ${aiGenIcon}"></i>`;
                preview.classList.remove('hidden');
                showToast(data.message || 'تم التوليد بنجاح ✨', 'success');
            } else {
                showToast(data.message || 'تعذر التوليد، يرجى المحاولة ثانية', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'توليد المحفّز الذكي الآن ✨';
            showToast('حدث خطأ أثناء الاتصال بالذكاء الاصطناعي', 'error');
            console.error(err);
        });
    }

    function adoptAiGeneratedMotivation() {
        if (!lastAiGeneratedMotivation) return;

        const adoptBtn = document.getElementById('adoptAiMotivationBtn');
        adoptBtn.disabled = true;
        adoptBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i><span>جاري الحفظ...</span>';

        fetch('{{ route("motivations.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                title: lastAiGeneratedMotivation.title,
                quote: lastAiGeneratedMotivation.quote,
                category: lastAiGeneratedMotivation.category,
                icon: lastAiGeneratedMotivation.icon,
                is_pinned: false
            })
        })
        .then(r => r.json())
        .then(data => {
            adoptBtn.disabled = false;
            adoptBtn.innerHTML = '<i class="fa-solid fa-check text-[10px]"></i><span>حفظ إلى محفّزاتي مباشرة ✓</span>';

            if (data.success && data.motivation) {
                closeAiMotivationModal();
                userMotivations.unshift(data.motivation);
                userMotivations.sort((a, b) => (b.is_pinned - a.is_pinned));
                updateCounts();
                renderMotivationsList();
                fetchRandomMotivation();
                showToast('تم حفظ المحفّز الذكي إلى قائمتك بنجاح ✨', 'success');
            } else {
                showToast(data.message || 'تعذر حفظ المحفّز', 'error');
            }
        })
        .catch(() => {
            adoptBtn.disabled = false;
            adoptBtn.innerHTML = '<i class="fa-solid fa-check text-[10px]"></i><span>حفظ إلى محفّزاتي مباشرة ✓</span>';
            showToast('حدث خطأ أثناء الحفظ', 'error');
        });
    }

    // Autofill Add Form via AI
    function generateAiForForm() {
        const btn = document.getElementById('formAiGenBtn');
        const existingCat = document.getElementById('motivationCategory').value.trim();
        const existingTitle = document.getElementById('motivationTitle').value.trim();
        const topic = existingCat || existingTitle || null;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i><span>جاري التوليد...</span>';

        fetch('{{ route("motivations.generate-ai") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ topic: topic })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i><span>توليد فوري ✨</span>';

            if (data.success && data.motivation) {
                document.getElementById('motivationTitle').value = data.motivation.title;
                document.getElementById('motivationQuote').value = data.motivation.quote;
                document.getElementById('motivationCategory').value = data.motivation.category || 'انضباط';
                
                const selectIcon = document.getElementById('motivationIcon');
                if (data.motivation.icon) {
                    for (let i = 0; i < selectIcon.options.length; i++) {
                        if (selectIcon.options[i].value === data.motivation.icon) {
                            selectIcon.selectedIndex = i;
                            break;
                        }
                    }
                }
                showToast('تم توليد واقتراح المحفّز بالذكاء الاصطناعي ✨', 'success');
            } else {
                showToast('تعذر التوليد، يرجى المحاولة ثانية', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i><span>توليد فوري ✨</span>';
            showToast('حدث خطأ أثناء التوليد', 'error');
        });
    }

    // Toggle Slide-Over Panel
    function toggleMotivationsPanel() {
        const panel = document.getElementById('motivationsPanel');
        const isClosed = panel.classList.contains('{{ ($currentLocale ?? "ar") === "ar" ? "translate-x-full" : "-translate-x-full" }}');
        if (isClosed) {
            openMotivationsPanel();
        } else {
            closeMotivationsPanel();
        }
    }

    function openMotivationsPanel() {
        const panel = document.getElementById('motivationsPanel');
        const backdrop = document.getElementById('motivationsBackdrop');
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
        panel.classList.remove('{{ ($currentLocale ?? "ar") === "ar" ? "translate-x-full" : "-translate-x-full" }}');
        loadMotivations();
    }

    function closeMotivationsPanel() {
        const panel = document.getElementById('motivationsPanel');
        const backdrop = document.getElementById('motivationsBackdrop');
        panel.classList.add('{{ ($currentLocale ?? "ar") === "ar" ? "translate-x-full" : "-translate-x-full" }}');
        backdrop.classList.add('opacity-0');
        setTimeout(() => backdrop.classList.add('hidden'), 300);
    }

    // Load Motivations via AJAX
    function loadMotivations() {
        const loader = document.getElementById('motivationsLoading');
        const emptyState = document.getElementById('motivationsEmptyState');
        const listContainer = document.getElementById('motivationsList');

        fetch('{{ route("motivations.index") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            loader.classList.add('hidden');
            if (data.success && data.motivations) {
                userMotivations = data.motivations;
                updateCounts();
                renderMotivationsList();
                if (userMotivations.length > 0) {
                    emptyState.classList.add('hidden');
                } else {
                    emptyState.classList.remove('hidden');
                }
            }
        })
        .catch(err => {
            loader.classList.add('hidden');
            console.error(err);
        });
    }

    function updateCounts() {
        const total = userMotivations.length;
        const pinned = userMotivations.filter(m => m.is_pinned).length;

        document.getElementById('motivationsBadgeCount').textContent = total;
        document.getElementById('motivationsHeaderCount').textContent = `${total} محفّز`;
        document.getElementById('filterAllCount').textContent = total;
        document.getElementById('filterPinnedCount').textContent = pinned;
    }

    // Render Motivations Cards
    function renderMotivationsList() {
        const container = document.getElementById('motivationsList');
        const emptyState = document.getElementById('motivationsEmptyState');
        container.innerHTML = '';

        let filtered = userMotivations;
        if (currentMotivationFilter === 'pinned') {
            filtered = userMotivations.filter(m => m.is_pinned);
        }

        if (filtered.length === 0) {
            if (userMotivations.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                container.innerHTML = `
                    <div class="py-8 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-filter-circle-xmark text-lg mb-1 block"></i>
                        لا توجد محفزات في هذا الفلتر
                    </div>
                `;
            }
            return;
        }

        emptyState.classList.add('hidden');

        filtered.forEach(m => {
            const card = document.createElement('div');
            card.className = `p-3.5 rounded-2xl border transition-all ${
                m.is_pinned
                ? 'bg-brand-50/50 dark:bg-slate-800/90 border-brand-300 dark:border-brand-700 shadow-xs'
                : 'bg-white dark:bg-slate-800/70 border-slate-200 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600'
            }`;

            let iconClass = m.icon || 'fa-lightbulb';
            if (iconClass === 'fa-calendar-check' || iconClass === 'fa-fire') {
                iconClass = 'fa-lightbulb';
            }
            const cat = m.category || 'عام';

            card.innerHTML = `
                <div class="flex items-start justify-between gap-2.5">
                    <div class="flex items-start gap-2.5 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-xl ${m.is_pinned ? 'bg-brand-100 dark:bg-brand-950/80 text-brand-600 dark:text-brand-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'} flex items-center justify-center text-xs shrink-0 mt-0.5">
                            <i class="fa-solid ${iconClass}"></i>
                        </div>
                        <div class="flex-1 min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white truncate">
                                    ${escapeHtml(m.title)}
                                </h4>
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full ${m.is_pinned ? 'bg-brand-100 dark:bg-brand-900/60 text-brand-800 dark:text-brand-200' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'}">
                                    ${escapeHtml(cat)}
                                </span>
                                ${m.is_pinned ? '<span class="text-[9px] font-bold text-brand-600 dark:text-brand-400">📌 مثبت</span>' : ''}
                            </div>
                            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed italic whitespace-pre-line">
                                «${escapeHtml(m.quote)}»
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" onclick="togglePinMotivation(${m.id})" class="w-7 h-7 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition" title="${m.is_pinned ? 'إلغاء التثبيت' : 'تثبيت في الأعلى'}">
                            <i class="fa-solid fa-thumbtack text-xs ${m.is_pinned ? 'text-brand-600' : ''}"></i>
                        </button>
                        <button type="button" onclick="openEditMotivationModal(${m.id})" class="w-7 h-7 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition" title="تعديل">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button type="button" onclick="confirmDeleteMotivation(${m.id}, '${escapeJs(m.title)}')" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition" title="حذف">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    // Filter Switcher
    function filterMotivations(filter, btn) {
        currentMotivationFilter = filter;
        document.querySelectorAll('.motivation-filter-btn').forEach(b => {
            b.classList.remove('bg-brand-50', 'dark:bg-brand-950/60', 'text-brand-600', 'dark:text-brand-400', 'font-bold');
            b.classList.add('text-slate-600', 'dark:text-slate-400');
        });
        btn.classList.add('bg-brand-50', 'dark:bg-brand-950/60', 'text-brand-600', 'dark:text-brand-400', 'font-bold');
        btn.classList.remove('text-slate-600', 'dark:text-slate-400');
        renderMotivationsList();
    }

    // Random Motivation
    function fetchRandomMotivation() {
        fetch('{{ route("motivations.random") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.motivation) {
                const m = data.motivation;
                document.getElementById('randomMotivationQuote').textContent = `«${m.quote}»`;
                document.getElementById('randomMotivationTitle').textContent = m.title;
                document.getElementById('randomMotivationCategory').textContent = m.category || 'عام';
            } else if (userMotivations.length > 0) {
                const rand = userMotivations[Math.floor(Math.random() * userMotivations.length)];
                document.getElementById('randomMotivationQuote').textContent = `«${rand.quote}»`;
                document.getElementById('randomMotivationTitle').textContent = rand.title;
                document.getElementById('randomMotivationCategory').textContent = rand.category || 'عام';
            }
        });
    }

    // Add / Edit Modal Controls
    function openAddMotivationModal() {
        document.getElementById('motivationModalTitle').innerHTML = '<i class="fa-solid fa-lightbulb text-brand-600"></i><span>إضافة محفّز جديد</span>';
        document.getElementById('motivationId').value = '';
        document.getElementById('motivationTitle').value = '';
        document.getElementById('motivationQuote').value = '';
        document.getElementById('motivationCategory').value = '';
        document.getElementById('motivationIcon').value = 'fa-lightbulb';
        document.getElementById('motivationIsPinned').checked = false;
        document.getElementById('motivationModal').classList.remove('hidden');
    }

    function openEditMotivationModal(id) {
        const m = userMotivations.find(item => item.id == id);
        if (!m) return;

        document.getElementById('motivationModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-brand-500"></i><span>تعديل المحفّز</span>';
        document.getElementById('motivationId').value = m.id;
        document.getElementById('motivationTitle').value = m.title;
        document.getElementById('motivationQuote').value = m.quote;
        document.getElementById('motivationCategory').value = m.category || '';
        document.getElementById('motivationIcon').value = m.icon || 'fa-lightbulb';
        document.getElementById('motivationIsPinned').checked = !!m.is_pinned;
        document.getElementById('motivationModal').classList.remove('hidden');
    }

    function closeMotivationModal() {
        document.getElementById('motivationModal').classList.add('hidden');
    }

    // Save Motivation (Create or Update)
    function saveMotivation(e) {
        e.preventDefault();
        const id = document.getElementById('motivationId').value;
        const title = document.getElementById('motivationTitle').value.trim();
        const quote = document.getElementById('motivationQuote').value.trim();
        const category = document.getElementById('motivationCategory').value.trim();
        const icon = document.getElementById('motivationIcon').value;
        const isPinned = document.getElementById('motivationIsPinned').checked;

        if (!title || !quote) {
            showToast('يرجى ملء عنوان المحفّز ونصه', 'error');
            return;
        }

        const isUpdate = !!id;
        const url = isUpdate ? `/motivations/${id}` : '{{ route("motivations.store") }}';
        const method = isUpdate ? 'PUT' : 'POST';

        const saveBtn = document.getElementById('saveMotivationBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'جاري الحفظ...';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                title: title,
                quote: quote,
                category: category,
                icon: icon,
                is_pinned: isPinned
            })
        })
        .then(r => r.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'حفظ المحفّز';

            if (data.success && data.motivation) {
                closeMotivationModal();
                showToast(data.message || 'تم حفظ المحفّز بنجاح ✓', 'success');
                if (isUpdate) {
                    const idx = userMotivations.findIndex(item => item.id == id);
                    if (idx !== -1) userMotivations[idx] = data.motivation;
                } else {
                    userMotivations.unshift(data.motivation);
                }
                userMotivations.sort((a, b) => (b.is_pinned - a.is_pinned));
                updateCounts();
                renderMotivationsList();
                fetchRandomMotivation();
            } else {
                showToast(data.message || 'تعذر حفظ المحفّز، يرجى التحقق من البيانات', 'error');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'حفظ المحفّز';
            showToast('حدث خطأ أثناء حفظ المحفّز', 'error');
            console.error(err);
        });
    }

    // Toggle Pin
    function togglePinMotivation(id) {
        fetch(`/motivations/${id}/toggle-pin`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.motivation) {
                const m = userMotivations.find(item => item.id == id);
                if (m) m.is_pinned = data.is_pinned;
                userMotivations.sort((a, b) => (b.is_pinned - a.is_pinned));
                updateCounts();
                renderMotivationsList();
                showToast(data.message, 'success');
            }
        })
        .catch(() => showToast('تعذر تغيير حالة التثبيت', 'error'));
    }

    // Delete Modal
    function confirmDeleteMotivation(id, title) {
        targetDeleteMotivationId = id;
        document.getElementById('deleteTargetTitle').textContent = `«${title}»`;
        document.getElementById('deleteMotivationModal').classList.remove('hidden');
    }

    function closeDeleteMotivationModal() {
        targetDeleteMotivationId = null;
        document.getElementById('deleteMotivationModal').classList.add('hidden');
    }

    function executeDeleteMotivation() {
        if (!targetDeleteMotivationId) return;

        const btn = document.getElementById('confirmDeleteBtn');
        btn.disabled = true;
        btn.textContent = 'جاري الحذف...';

        fetch(`/motivations/${targetDeleteMotivationId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'نعم، احذف';
            closeDeleteMotivationModal();

            if (data.success) {
                userMotivations = userMotivations.filter(m => m.id != targetDeleteMotivationId);
                updateCounts();
                renderMotivationsList();
                showToast(data.message || 'تم حذف المحفّز بنجاح ✓', 'success');
                fetchRandomMotivation();
            } else {
                showToast(data.message || 'تعذر حذف المحفّز', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'نعم، احذف';
            showToast('حدث خطأ أثناء الحذف', 'error');
        });
    }

    // Seed Default Motivations
    function seedDefaultMotivations() {
        fetch('{{ route("motivations.seed-defaults") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                loadMotivations();
                fetchRandomMotivation();
            } else {
                showToast(data.message, 'error');
            }
        });
    }

    // Helpers
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJs(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // Escape Key Listener to close modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMotivationModal();
            closeDeleteMotivationModal();
            closeAiMotivationModal();
            closeMotivationsPanel();
        }
    });

    // Auto load count on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        fetch('{{ route("motivations.index") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.motivations) {
                userMotivations = data.motivations;
                updateCounts();
                if (userMotivations.length > 0) {
                    fetchRandomMotivation();
                }
            }
        })
        .catch(() => {});
    });
</script>
