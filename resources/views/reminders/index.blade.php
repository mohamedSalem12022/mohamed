@extends('layouts.app')

@section('title', 'التذكيرات والإشعارات — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xl shadow-md shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        التذكيرات والإشعارات
                    </h1>
                    <span class="text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/70 px-2 py-0.5 rounded-full border border-brand-200 dark:border-brand-800">
                        Smart Reminders
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    إدارة مواعيد التنبيه، إشعارات المتصفح، والتنبيهات التلقائية للمهام والأحداث.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Browser Permission Button -->
            <button type="button" id="btnRequestBrowserPerm" onclick="requestBrowserNotifications()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                <i class="fa-solid fa-desktop text-teal-600"></i>
                <span id="permBtnText">إشعارات المتصفح</span>
            </button>

            <!-- Add Reminder Button -->
            <button type="button" onclick="openCreateReminderModal()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-105">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>تذكير جديد</span>
            </button>
        </div>
    </div>

    <!-- Main Grid: Active Reminders & Notification Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Active Reminders (6 cols) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock text-brand-600"></i>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">التذكيرات المجدولة القادمة</h3>
                </div>
                <span class="text-xs font-bold text-brand-600 bg-brand-50 dark:bg-brand-950/70 px-2.5 py-0.5 rounded-full">
                    {{ $activeReminders->count() }} تذكير نشط
                </span>
            </div>

            <div class="space-y-3">
                @forelse($activeReminders as $reminder)
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/30 hover:border-brand-300 transition flex items-start justify-between gap-3 group">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm shrink-0">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                {{ $reminder->title }}
                            </h4>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400 flex-wrap">
                                <span class="font-mono text-teal-700 dark:text-teal-300 font-bold">
                                    <i class="fa-regular fa-clock ml-1"></i>
                                    {{ $reminder->remind_at ? $reminder->remind_at->format('Y-m-d H:i') : 'تلقائي' }}
                                </span>
                                @if($reminder->remind_at)
                                    <span>({{ $reminder->remind_at->diffForHumans() }})</span>
                                @endif
                                @if($reminder->task)
                                    <span class="text-brand-600 font-medium truncate max-w-[150px]">
                                        📌 {{ $reminder->task->title }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="cancelReminder({{ $reminder->id }})" class="text-xs text-slate-400 hover:text-rose-600 px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition shrink-0" title="إلغاء التذكير">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                @empty
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-regular fa-bell-slash text-3xl mb-2 block"></i>
                    <p class="text-xs font-semibold">لا توجد تذكيرات مجدولة قادمة.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- In-App Notifications History (6 cols) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-inbox text-brand-600"></i>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">سجل الإشعارات والتنبيهات</h3>
                </div>
                <button type="button" onclick="markAllNotificationsAsRead()" class="text-xs font-bold text-brand-600 hover:underline">
                    تحديد الكل كمقروء
                </button>
            </div>

            <div class="space-y-2.5 max-h-[500px] overflow-y-auto scrollbar-thin">
                @forelse($notifications as $notif)
                <div class="p-3.5 rounded-xl border transition flex items-start gap-3 {{ $notif->is_read ? 'bg-slate-50/50 dark:bg-slate-900/20 border-slate-100 dark:border-slate-700/50 opacity-70' : 'bg-brand-50/30 dark:bg-brand-950/20 border-brand-200 dark:border-brand-800' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-xs {{ $notif->type === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-brand-100 text-brand-700' }}">
                        <i class="fa-solid {{ $notif->type === 'warning' ? 'fa-triangle-exclamation' : 'fa-bell' }}"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $notif->title }}</h5>
                            <span class="text-[10px] text-slate-400 shrink-0 font-mono">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">{{ $notif->message }}</p>
                    </div>

                    @if(!$notif->is_read)
                    <button type="button" onclick="markNotificationRead({{ $notif->id }}, this)" class="text-[10px] text-brand-600 hover:underline font-bold shrink-0 self-center">
                        تم القراءة
                    </button>
                    @endif
                </div>
                @empty
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-regular fa-envelope-open text-3xl mb-2 block"></i>
                    <p class="text-xs">سجل الإشعارات فارغ حالياً.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- Modal: Create Reminder -->
<div id="reminderModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden animate-fade-in">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">إضافة تذكير مخصص جديد</h3>
            <button type="button" onclick="closeCreateReminderModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form onsubmit="submitNewReminder(event)" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">عنوان التذكير *</label>
                <input type="text" id="remTitle" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500" placeholder="مثال: شرب الماء أو مراجعة مهام الغد...">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تاريخ ووقت التنبيه *</label>
                <input type="datetime-local" id="remDate" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-brand-500 font-mono">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeCreateReminderModal()" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition">
                    تأكيد التذكير
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        checkBrowserPermStatus();
    });

    function checkBrowserPermStatus() {
        const textSpan = document.getElementById('permBtnText');
        if (!('Notification' in window)) {
            textSpan.textContent = 'المتصفح لا يدعم التنبيهات';
            return;
        }
        if (Notification.permission === 'granted') {
            textSpan.textContent = 'إشعارات المتصفح مفعلة ✓';
        } else if (Notification.permission === 'denied') {
            textSpan.textContent = 'الإشعارات محظورة في المتصفح';
        } else {
            textSpan.textContent = 'تفعيل إشعارات المتصفح';
        }
    }

    function requestBrowserNotifications() {
        if (!('Notification' in window)) {
            alert('متصفحك الحالي لا يدعم ميزة إشعارات سطح المكتب');
            return;
        }
        Notification.requestPermission().then(permission => {
            checkBrowserPermStatus();
            if (permission === 'granted') {
                showToast('تم تفعيل إشعارات المتصفح بنجاح! ستصلك التنبيهات حتى خارج الصفحة ✓', 'success');
                new Notification('MO Habit Tracker & Task Manager', {
                    body: 'تم تفعيل التنبيهات بنجاح 🎯',
                    icon: '/favicon.ico'
                });
            }
        });
    }

    function openCreateReminderModal() {
        const now = new Date();
        now.setMinutes(now.getMinutes() + 15);
        document.getElementById('remDate').value = now.toISOString().substring(0, 16);
        document.getElementById('remTitle').value = '';
        document.getElementById('reminderModal').classList.remove('hidden');
    }

    function closeCreateReminderModal() {
        document.getElementById('reminderModal').classList.add('hidden');
    }

    function submitNewReminder(e) {
        e.preventDefault();
        const payload = {
            title: document.getElementById('remTitle').value,
            remind_type: 'custom',
            remind_at: document.getElementById('remDate').value
        };

        fetch('{{ route("reminders.store") }}', {
            method: 'POST',
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
                showToast(data.message || 'تمت إضافة التذكير بنجاح ✓', 'success');
                closeCreateReminderModal();
                setTimeout(() => window.location.reload(), 400);
            }
        });
    }

    function cancelReminder(id) {
        if (!confirm('هل تريد فعلاً إلغاء هذا التذكير؟')) return;

        fetch(`/reminders/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('تم إلغاء التذكير بنجاح', 'success');
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }

    function markNotificationRead(id, btn) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.closest('.border').classList.add('opacity-70');
                btn.remove();
                showToast('تم تحديد الإشعار كمقروء', 'success');
            }
        });
    }

    function markAllNotificationsAsRead() {
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('تم تحديد جميع الإشعارات كمقروءة ✓', 'success');
                setTimeout(() => window.location.reload(), 400);
            }
        });
    }
</script>
@endpush
