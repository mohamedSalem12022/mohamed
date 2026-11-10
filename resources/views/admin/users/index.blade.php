@extends('layouts.app')

@section('title', 'إدارة المستخدمين — MO Habit Tracker')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-brand-500/20 text-xl shrink-0">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span>لوحة تحكم المشرف — إدارة المستخدمين</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-brand-100 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 font-bold border border-brand-200 dark:border-brand-800">
                        Admin Only
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    إدارة شاملة لجميع الحسابات المسجلة في MO Life OS، التحكم بالصلاحيات، وحذف الحسابات مع حماية صارمة للبيانات.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
            <button 
                type="button" 
                onclick="openCreateModal()" 
                class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-brand-500/20"
            >
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة حساب جديد</span>
            </button>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للرئيسية</span>
            </a>
        </div>
    </div>

    <!-- WhatsApp Registration Info Banner -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl {{ !empty($whatsappNumber) ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' }} flex items-center justify-center text-base shrink-0">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span>قناة طلب الحسابات للزوار:</span>
                    @if(!empty($whatsappNumber))
                    <span class="font-mono text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold">
                        +{{ $whatsappNumber }}
                    </span>
                    @else
                    <span class="text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800 text-[11px] font-semibold">
                        لم يتم تعيين الرقم بعد في .env (WHATSAPP_REGISTRATION_NUMBER)
                    </span>
                    @endif
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    الزائر الذي يضغط على «إنشاء حساب جديد» يتم تحويله لمحادثة واتساب مباشرةً، بينما يتم فتح الحسابات فعلياً هنا من داخل لوحة التحكم.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
            <button type="button" onclick="openEditWhatsAppModal()" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs flex items-center gap-1.5 transition">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>تعديل رقم الواتساب</span>
            </button>
            @if(!empty($whatsappNumber))
            <a href="{{ \App\Http\Controllers\AuthController::getRegistrationWhatsAppUrl() }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold flex items-center gap-1 transition" title="تجربة الرابط">
                <span>تجربة الرابط</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
            @endif
        </div>
    </div>

    <!-- Statistics KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Users -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">إجمالي الحسابات</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['total']) }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">كل الحسابات المسجلة</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-brand-600 dark:text-brand-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Admins Count -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">المشرفون (Admins)</p>
                <h3 class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">{{ number_format($stats['admins']) }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">صلاحيات كاملة بالنظام</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

        <!-- Regular Users -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">مستخدمون عاديون</p>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['regular']) }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">حسابات شخصية معزولة</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <!-- New This Week -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">انضموا هذا الأسبوع</p>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['new_this_week']) }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">خلال آخر 7 أيام</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-plus"></i>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass absolute right-3.5 top-3.5 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="ابحث بالاسم الكامل أو عنوان البريد الإلكتروني..." 
                    class="w-full pr-10 pl-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-search"></i>
                    <span>بحث</span>
                </button>
                @if($search !== '')
                <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold transition" title="إلغاء البحث">
                    إعادة ضبط
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table (Desktop) / Cards (Mobile) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        
        <!-- Table for screens md and up -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-bold">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">المستخدم</th>
                        <th class="py-3.5 px-4">البريد الإلكتروني</th>
                        <th class="py-3.5 px-4">الصلاحية والرتبة</th>
                        <th class="py-3.5 px-4">البيانات النشطة</th>
                        <th class="py-3.5 px-4">تاريخ التسجيل</th>
                        <th class="py-3.5 px-4 text-center">الإجراءات والتحكم</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($users as $user)
                    @php
                        $isSelf = (Auth::id() === $user->id);
                        $isLastAdmin = ($user->isAdmin() && $stats['admins'] <= 1);
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition {{ $isSelf ? 'bg-teal-50/20 dark:bg-teal-950/10' : '' }}">
                        <!-- ID -->
                        <td class="py-3.5 px-4 font-mono text-slate-400 font-semibold">
                            #{{ $user->id }}
                        </td>

                        <!-- Name & Avatar -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-tr {{ $user->isAdmin() ? 'from-amber-500 to-amber-300' : 'from-brand-600 to-teal-400' }} text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span class="truncate">{{ $user->name }}</span>
                                        @if($isSelf)
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-brand-100 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 font-bold">
                                            أنت
                                        </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400">انضم {{ $user->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300 text-xs">
                            {{ $user->email }}
                        </td>

                        <!-- Role Badge -->
                        <td class="py-3.5 px-4">
                            @if($user->isAdmin())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                <span>مشرف (Admin)</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                <i class="fa-regular fa-user text-[10px]"></i>
                                <span>مستخدم عادي</span>
                            </span>
                            @endif
                        </td>

                        <!-- Data counts -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                <span title="عدد العادات"><i class="fa-solid fa-circle-notch text-brand-600 ml-1"></i>{{ $user->habits_count }}</span>
                                <span title="عدد المهام"><i class="fa-solid fa-list-check text-blue-500 ml-1"></i>{{ $user->tasks_count }}</span>
                                <span title="المعاملات المالية"><i class="fa-solid fa-wallet text-emerald-500 ml-1"></i>{{ $user->financial_transactions_count }}</span>
                            </div>
                        </td>

                        <!-- Date -->
                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                            {{ $user->created_at->format('Y-m-d') }}
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                
                                <!-- Role Toggle Button -->
                                @if(! $isSelf && ! $isLastAdmin)
                                <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST" onsubmit="return confirmRoleToggle(event, '{{ $user->name }}', {{ $user->isAdmin() ? 'true' : 'false' }});">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="p-2 rounded-lg text-xs font-semibold transition {{ $user->isAdmin() ? 'bg-amber-100 hover:bg-amber-200 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200' }}"
                                        title="{{ $user->isAdmin() ? 'تخفيض الرتبة إلى مستخدم عادي' : 'ترقية المستخدم إلى مشرف' }}"
                                    >
                                        <i class="fa-solid {{ $user->isAdmin() ? 'fa-user-minus' : 'fa-user-shield' }}"></i>
                                    </button>
                                </form>
                                @else
                                <span class="p-2 text-slate-300 dark:text-slate-600 cursor-not-allowed" title="{{ $isSelf ? 'لا يمكنك تغيير رتبة حسابك' : 'لا يمكن خفض رتبة المشرف الأخير' }}">
                                    <i class="fa-solid {{ $user->isAdmin() ? 'fa-shield' : 'fa-user' }}"></i>
                                </span>
                                @endif

                                <!-- Delete Button -->
                                @if(! $isSelf && ! $isLastAdmin)
                                <button 
                                    type="button" 
                                    onclick="openDeleteModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->email }}')" 
                                    class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition"
                                    title="حذف حساب المستخدم نهائياً"
                                >
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                                @else
                                <span class="p-2 text-slate-300 dark:text-slate-600 cursor-not-allowed" title="{{ $isSelf ? 'لا يمكنك حذف حسابك الشخصي الحالي' : 'لا يمكن حذف المشرف الأخير' }}">
                                    <i class="fa-solid fa-ban"></i>
                                </span>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-regular fa-user-slash text-3xl mb-2 block"></i>
                            <span class="text-sm font-semibold">لم يتم العثور على أي مستخدمين يطابقون معايير البحث.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Cards View for Mobile -->
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($users as $user)
            @php
                $isSelf = (Auth::id() === $user->id);
                $isLastAdmin = ($user->isAdmin() && $stats['admins'] <= 1);
            @endphp
            <div class="p-4 space-y-3 {{ $isSelf ? 'bg-teal-50/20 dark:bg-teal-950/10' : '' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr {{ $user->isAdmin() ? 'from-amber-500 to-amber-300' : 'from-brand-600 to-teal-400' }} text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $user->name }}</span>
                                @if($isSelf)
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-brand-100 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 font-bold">
                                    أنت
                                </span>
                                @endif
                            </div>
                            <p class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div>
                        @if($user->isAdmin())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            مشرف
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            مستخدم
                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-700/50">
                    <div class="flex items-center gap-2 text-[10px]">
                        <span><i class="fa-solid fa-circle-notch text-brand-600 ml-1"></i>{{ $user->habits_count }} عادات</span>
                        <span><i class="fa-solid fa-list-check text-blue-500 ml-1"></i>{{ $user->tasks_count }} مهام</span>
                        <span><i class="fa-solid fa-wallet text-emerald-500 ml-1"></i>{{ $user->financial_transactions_count }} معاملات</span>
                    </div>
                    <span class="text-[10px] font-mono">{{ $user->created_at->format('Y-m-d') }}</span>
                </div>

                <!-- Mobile Actions -->
                <div class="flex items-center gap-2 pt-1">
                    @if(! $isSelf && ! $isLastAdmin)
                    <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST" class="flex-1" onsubmit="return confirmRoleToggle(event, '{{ $user->name }}', {{ $user->isAdmin() ? 'true' : 'false' }});">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200' }}">
                            <i class="fa-solid {{ $user->isAdmin() ? 'fa-user-minus' : 'fa-user-shield' }}"></i>
                            <span>{{ $user->isAdmin() ? 'تحويل لمستخدم' : 'ترقية لمشرف' }}</span>
                        </button>
                    </form>
                    <button 
                        type="button" 
                        onclick="openDeleteModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->email }}')" 
                        class="py-2 px-4 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-xs font-bold transition flex items-center justify-center gap-1.5"
                    >
                        <i class="fa-regular fa-trash-can"></i>
                        <span>حذف</span>
                    </button>
                    @else
                    <div class="w-full text-center py-1.5 text-xs text-slate-400 bg-slate-50 dark:bg-slate-900 rounded-lg">
                        {{ $isSelf ? 'حسابك الحالي (محمي)' : 'المشرف الوحيد (محمي)' }}
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400">
                <i class="fa-regular fa-user-slash text-3xl mb-2 block"></i>
                <span class="text-sm font-semibold">لم يتم العثور على أي مستخدمين.</span>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
            {{ $users->links() }}
        </div>
        @endif

    </div>

</div>

<!-- Create User Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 animate-scale-in">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">إضافة مستخدم جديد</h3>
            </div>
            <button type="button" onclick="closeCreateModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3.5 text-right">
            @csrf
            
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الاسم الكامل *</label>
                <input 
                    type="text" 
                    name="name" 
                    required 
                    placeholder="محمد سالم" 
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">البريد الإلكتروني *</label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    placeholder="user@example.com" 
                    class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">كلمة المرور *</label>
                    <button type="button" onclick="generateRandomPassword()" class="text-[10px] font-bold text-brand-600 hover:underline">
                        توليد كلمة مرور عشوائية
                    </button>
                </div>
                <input 
                    type="text" 
                    id="createUserPassword" 
                    name="password" 
                    required 
                    minlength="8" 
                    placeholder="8 أحرف على الأقل" 
                    class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <input type="checkbox" name="is_admin" value="1" class="rounded text-brand-600 focus:ring-brand-500">
                    <div class="text-xs">
                        <span class="font-bold text-slate-800 dark:text-slate-200">منح صلاحية مشرف (Admin)</span>
                        <p class="text-[10px] text-slate-400">يمكّن هذا الحساب من إدارة المستخدمين والإعدادات</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center gap-2 pt-3">
                <button type="button" onclick="closeCreateModal()" class="flex-1 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold text-xs transition">
                    إلغاء
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>إنشاء وتفعيل الحساب</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 animate-scale-in">
        
        <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <div class="text-center space-y-1">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">تأكيد حذف حساب المستخدم</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                أنت على وشك حذف الحساب التالي بشكل نهائي:
            </p>
        </div>

        <div class="bg-slate-50 dark:bg-slate-900/70 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 text-center space-y-1">
            <p id="modalUserName" class="text-sm font-bold text-slate-900 dark:text-white"></p>
            <p id="modalUserEmail" class="text-xs font-mono text-slate-500 dark:text-slate-400"></p>
        </div>

        <div class="p-3 rounded-xl bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-900/40 text-[11px] text-rose-700 dark:text-rose-300 space-y-1 leading-relaxed">
            <p class="font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>تحذير أمني وبيانات هام:</span>
            </p>
            <p>
                سيتم حذف كافة المهام، العادات، التذكيرات، الملاحظات، والمعاملات المالية التابعة لهذا الحساب نهائياً، ولن يمكن استرجاعها مطلقاً.
            </p>
        </div>

        <form id="deleteForm" method="POST" action="" class="flex items-center gap-2 pt-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="flex-1 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold text-xs transition">
                إلغاء التراجع
            </button>
            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/20 transition flex items-center justify-center gap-1.5">
                <i class="fa-regular fa-trash-can"></i>
                <span>نعم، حذف الحساب</span>
            </button>
        </form>

    </div>
</div>

<!-- Edit WhatsApp Registration Number Modal -->
<div id="editWhatsAppModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 animate-scale-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">تعديل رقم واتساب لإنشاء الحسابات</h3>
                    <p class="text-[11px] text-slate-400">تحديث الرقم الذي يتواصل معه الزوار لطلب حساب جديد</p>
                </div>
            </div>
            <button type="button" onclick="closeEditWhatsAppModal()" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.whatsapp.update') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    <span>رقم هاتف الواتساب</span>
                    <span class="text-rose-500">*</span>
                    <span class="text-[10px] text-slate-400 font-normal">(مع رمز الدولة، بدون + أو أصفار إضافية)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-phone text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        name="whatsapp_number" 
                        id="whatsappNumberInput"
                        value="{{ $whatsappNumber }}" 
                        required 
                        placeholder="972568080358" 
                        dir="ltr"
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs font-mono font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>
                <p class="text-[10px] text-slate-400 mt-1">مثال: لفلسطين 97259xxxxxxx أو للأردن 9627xxxxxxxx أو لمصر 2010xxxxxxxx</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    <span>نص الرسالة التلقائية للزائر</span>
                    <span class="text-[10px] text-slate-400 font-normal">(اختياري)</span>
                </label>
                <textarea 
                    name="whatsapp_message" 
                    rows="2" 
                    placeholder="مرحبًا، أريد إنشاء حساب جديد في MO Life OS"
                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                >{{ $whatsappMessage ?? 'مرحبًا، أريد إنشاء حساب جديد في MO Life OS' }}</textarea>
                <p class="text-[10px] text-slate-400 mt-1">هذه الرسالة تظهر مكتوبة تلقائياً للزائر في محادثة الواتساب عند الضغط على زر إنشاء الحساب.</p>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeEditWhatsAppModal()" class="flex-1 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold text-xs transition">
                    إلغاء
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ وتحديث الرقم</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditWhatsAppModal() {
        document.getElementById('editWhatsAppModal').classList.remove('hidden');
    }

    function closeEditWhatsAppModal() {
        document.getElementById('editWhatsAppModal').classList.add('hidden');
    }

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function generateRandomPassword() {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let pass = '';
        for (let i = 0; i < 12; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('createUserPassword').value = pass;
    }

    function openDeleteModal(userId, userName, userEmail) {
        const form = document.getElementById('deleteForm');
        form.action = `{{ url('/admin/users') }}/${userId}`;
        document.getElementById('modalUserName').innerText = userName;
        document.getElementById('modalUserEmail').innerText = userEmail;
        
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
    }

    function confirmRoleToggle(event, userName, isCurrentlyAdmin) {
        const action = isCurrentlyAdmin ? 'خفض صلاحية' : 'ترقية';
        const target = isCurrentlyAdmin ? 'إلى مستخدم عادي' : 'إلى مشرف (Admin) مع صلاحيات كاملة';
        return confirm(`هل أنت متأكد من ${action} «${userName}» ${target}؟`);
    }

    // Close modal on Escape key or backdrop click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
            closeCreateModal();
            closeEditWhatsAppModal();
        }
    });
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
    document.getElementById('createModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });
    document.getElementById('editWhatsAppModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditWhatsAppModal();
    });
</script>
@endsection
