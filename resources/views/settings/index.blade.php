@extends('layouts.app')

@section('title', __('settings_preferences') . ' — MO Life OS')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header Section with User Summary -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="flex items-center gap-4 z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-brand-500/20 text-2xl shrink-0">
                <i class="fa-solid fa-gear"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                        {{ __('settings_preferences') }}
                    </h1>
                    @if($user->isAdmin())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        <i class="fa-solid fa-shield-halved text-[10px]"></i>
                        {{ __('admin_role') }}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                        <i class="fa-solid fa-user-check text-[10px]"></i>
                        {{ __('active_account') }}
                    </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                    {{ __('settings_description') }}
                </p>
            </div>
        </div>

        <!-- Quick Profile Pill -->
        <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-2xl border border-slate-100 dark:border-slate-700/60 z-10 w-full md:w-auto">
            <div class="w-10 h-10 rounded-xl bg-brand-100 dark:bg-brand-950/70 text-brand-700 dark:text-brand-300 flex items-center justify-center font-black text-sm shrink-0">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div class="min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $user->name }}</h4>
                <p class="text-[11px] text-slate-400 truncate font-mono">{{ $user->email }}</p>
            </div>
        </div>

        <div class="absolute -left-10 -bottom-10 w-44 h-44 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-teal-800 dark:text-teal-200 text-sm flex items-center gap-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-check text-teal-600 dark:text-teal-400 text-base shrink-0"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 text-sm flex items-start gap-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-base shrink-0 mt-0.5"></i>
            <div>
                <p class="font-bold">{{ ($currentLocale ?? 'ar') === 'en' ? 'Please correct the following errors:' : 'يرجى تصحيح الأخطاء التالية:' }}</p>
                <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Section 1: Profile & Password (الأول والأهم: الملف الشخصي والأمان) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200 dark:border-slate-700 space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('profile_security') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('profile_security_desc') }}</p>
            </div>
        </div>

        <form action="{{ route('settings.profile') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Name & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('full_name') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('email') }} <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
            </div>

            <!-- Password Change Accordion / Box -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-key text-brand-600 text-xs"></i>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ __('change_password') }}</span>
                    <span class="text-[11px] text-slate-400 font-normal">{{ __('leave_empty_to_keep') }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('current_password') }}</label>
                        <input type="password" name="current_password" placeholder="••••••••" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('new_password') }}</label>
                        <input type="password" name="new_password" placeholder="••••••••" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('confirm_password') }}</label>
                        <input type="password" name="new_password_confirmation" placeholder="••••••••" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md shadow-brand-500/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>{{ __('save_profile_security') }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Appearance & Preferences (المظهر والتفضيلات) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200 dark:border-slate-700 space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-palette"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('appearance_preferences') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('appearance_preferences_desc') }}</p>
            </div>
        </div>

        <form action="{{ route('settings.preferences') }}" method="POST" class="space-y-4">
            @csrf

            @php
                $activeTheme = $currentTheme ?? ($settings->theme ?? 'light');
                $activeLang = $currentLocale ?? ($settings->language ?? 'ar');
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Theme Card -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">{{ __('display_theme') }}</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label onclick="setThemePreference('light')" data-value="light" class="pref-card-theme cursor-pointer relative flex items-center justify-between p-3 rounded-2xl border transition-all duration-200 {{ $activeTheme === 'light' ? 'border-brand-500 dark:border-brand-400 bg-brand-50/80 dark:bg-brand-950/60 ring-2 ring-brand-500/50 dark:ring-brand-400/50 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800' }}">
                            <input type="radio" name="theme" value="light" {{ $activeTheme === 'light' ? 'checked' : '' }} class="sr-only">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-sun text-amber-500 text-sm"></i>
                                <span class="text-xs pref-card-text {{ $activeTheme === 'light' ? 'font-black text-brand-700 dark:text-brand-300' : 'font-bold text-slate-700 dark:text-slate-300' }}">{{ __('light_mode') }}</span>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-brand-600 dark:bg-brand-500 text-white flex items-center justify-center text-[10px] shadow-xs check-badge {{ $activeTheme === 'light' ? '' : 'hidden' }}">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </label>
                        <label onclick="setThemePreference('dark')" data-value="dark" class="pref-card-theme cursor-pointer relative flex items-center justify-between p-3 rounded-2xl border transition-all duration-200 {{ $activeTheme === 'dark' ? 'border-brand-500 dark:border-brand-400 bg-brand-50/80 dark:bg-brand-950/60 ring-2 ring-brand-500/50 dark:ring-brand-400/50 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800' }}">
                            <input type="radio" name="theme" value="dark" {{ $activeTheme === 'dark' ? 'checked' : '' }} class="sr-only">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-moon text-indigo-400 text-sm"></i>
                                <span class="text-xs pref-card-text {{ $activeTheme === 'dark' ? 'font-black text-brand-700 dark:text-brand-300' : 'font-bold text-slate-700 dark:text-slate-300' }}">{{ __('dark_mode') }}</span>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-brand-600 dark:bg-brand-500 text-white flex items-center justify-center text-[10px] shadow-xs check-badge {{ $activeTheme === 'dark' ? '' : 'hidden' }}">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Language Card -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">{{ __('language') }}</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label onclick="setLanguagePreference('ar')" data-value="ar" class="pref-card-lang cursor-pointer relative flex items-center justify-between p-3 rounded-2xl border transition-all duration-200 {{ $activeLang === 'ar' ? 'border-brand-500 dark:border-brand-400 bg-brand-50/80 dark:bg-brand-950/60 ring-2 ring-brand-500/50 dark:ring-brand-400/50 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800' }}">
                            <input type="radio" name="language" value="ar" {{ $activeLang === 'ar' ? 'checked' : '' }} class="sr-only">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-language text-brand-600 dark:text-brand-400 text-sm"></i>
                                <span class="text-xs pref-card-text {{ $activeLang === 'ar' ? 'font-black text-brand-700 dark:text-brand-300' : 'font-bold text-slate-700 dark:text-slate-300' }}">العربية (RTL)</span>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-brand-600 dark:bg-brand-500 text-white flex items-center justify-center text-[10px] shadow-xs check-badge {{ $activeLang === 'ar' ? '' : 'hidden' }}">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </label>
                        <label onclick="setLanguagePreference('en')" data-value="en" class="pref-card-lang cursor-pointer relative flex items-center justify-between p-3 rounded-2xl border transition-all duration-200 {{ $activeLang === 'en' ? 'border-brand-500 dark:border-brand-400 bg-brand-50/80 dark:bg-brand-950/60 ring-2 ring-brand-500/50 dark:ring-brand-400/50 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800' }}">
                            <input type="radio" name="language" value="en" {{ $activeLang === 'en' ? 'checked' : '' }} class="sr-only">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-globe text-slate-400 text-sm"></i>
                                <span class="text-xs pref-card-text {{ $activeLang === 'en' ? 'font-black text-brand-700 dark:text-brand-300' : 'font-bold text-slate-700 dark:text-slate-300' }}">English (LTR)</span>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-brand-600 dark:bg-brand-500 text-white flex items-center justify-center text-[10px] shadow-xs check-badge {{ $activeLang === 'en' ? '' : 'hidden' }}">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Timezone & Current Time -->
            @php
                $activeTimezone = $settings->timezone ?? session('timezone', config('app.timezone', 'Asia/Gaza'));
            @endphp
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clock text-base"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('timezone_local_time') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-950/70 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                {{ __('auto_synced_browser') }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                            {{ $activeTimezone }} — {{ __('current_time_now') }}: {{ now()->translatedFormat('h:i A') }}
                        </p>
                    </div>
                </div>
                <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-1.5 bg-white dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-location-dot text-brand-600"></i>
                    <span>{{ $activeTimezone }}</span>
                </div>
            </div>

            <!-- Brand Color (Hidden/Preset to match official MO Life OS brand) -->
            <input type="hidden" name="primary_color" value="{{ $settings->primary_color ?? '#0d9488' }}">

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-md shadow-brand-500/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>{{ __('save_appearance_preferences') }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Section 3: Task Categories Management (تصنيفات المهام) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200 dark:border-slate-700 space-y-4">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('task_categories') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('task_categories_desc') }}</p>
                </div>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                {{ count($categories) }} {{ ($currentLocale ?? 'ar') === 'en' ? 'Categories' : 'تصنيفات' }}
            </span>
        </div>

        <!-- Add Category Form -->
        <form action="{{ route('settings.categories.store') }}" method="POST" class="flex flex-wrap items-center gap-2.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
            @csrf
            <input type="text" name="name" required placeholder="{{ ($currentLocale ?? 'ar') === 'en' ? 'Category name (e.g. Study, Work, Reading)...' : 'اسم التصنيف الجديد (مثال: دراسة، رياضة، قراءة)...' }}" class="flex-1 min-w-[200px] px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            <div class="flex items-center gap-2">
                <input type="color" name="color" value="#0d9488" class="w-9 h-9 p-0.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer" title="{{ ($currentLocale ?? 'ar') === 'en' ? 'Pick color' : 'اختر لون التصنيف' }}">
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-sm transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>{{ __('add_category_btn') }}</span>
                </button>
            </div>
        </form>

        <!-- Existing Categories Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 pt-1">
            @forelse($categories as $cat)
            <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 hover:border-slate-300 transition">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $cat->color }}"></span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $cat->name }}</span>
                </div>
                <form action="{{ route('settings.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('{{ ($currentLocale ?? 'ar') === 'en' ? 'Are you sure you want to delete this category?' : 'هل أنت متأكد من حذف هذا التصنيف؟' }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center transition" title="{{ ($currentLocale ?? 'ar') === 'en' ? 'Delete category' : 'حذف التصنيف' }}">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-400 col-span-full py-4 text-center italic">{{ __('no_categories_yet') }}</p>
            @endforelse
        </div>
    </div>

    <!-- Section 4: System Information (معلومات النظام والذكاء الاصطناعي) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 shadow-xs border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ __('system_info_title') }}</span>
                <p class="text-[11px] text-slate-400">{{ __('ai_connected_active') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 font-mono text-[11px]">
            <span>PHP 8.3</span>
            <span>•</span>
            <span>Laravel 13</span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function setThemePreference(theme) {
        const radio = document.querySelector(`input[name="theme"][value="${theme}"]`);
        if (radio) radio.checked = true;

        document.querySelectorAll('.pref-card-theme').forEach(card => {
            const val = card.getAttribute('data-value');
            const badge = card.querySelector('.check-badge');
            const text = card.querySelector('.pref-card-text');

            if (val === theme) {
                card.classList.add('border-brand-500', 'dark:border-brand-400', 'bg-brand-50/80', 'dark:bg-brand-950/60', 'ring-2', 'ring-brand-500/50', 'dark:ring-brand-400/50', 'shadow-xs');
                card.classList.remove('border-slate-200', 'dark:border-slate-700', 'hover:border-slate-300', 'dark:hover:border-slate-600', 'bg-white', 'dark:bg-slate-800');
                if (badge) badge.classList.remove('hidden');
                if (text) {
                    text.classList.add('font-black', 'text-brand-700', 'dark:text-brand-300');
                    text.classList.remove('font-bold', 'text-slate-700', 'dark:text-slate-300');
                }
            } else {
                card.classList.remove('border-brand-500', 'dark:border-brand-400', 'bg-brand-50/80', 'dark:bg-brand-950/60', 'ring-2', 'ring-brand-500/50', 'dark:ring-brand-400/50', 'shadow-xs');
                card.classList.add('border-slate-200', 'dark:border-slate-700', 'hover:border-slate-300', 'dark:hover:border-slate-600', 'bg-white', 'dark:bg-slate-800');
                if (badge) badge.classList.add('hidden');
                if (text) {
                    text.classList.remove('font-black', 'text-brand-700', 'dark:text-brand-300');
                    text.classList.add('font-bold', 'text-slate-700', 'dark:text-slate-300');
                }
            }
        });

        // Immediate visual theme toggle across the app
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Auto-save theme via AJAX in the background
        if (csrfToken) {
            fetch('{{ route("settings.toggle-theme") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ theme: theme })
            }).catch(() => {});
        }
    }

    function setLanguagePreference(lang) {
        const radio = document.querySelector(`input[name="language"][value="${lang}"]`);
        if (radio) radio.checked = true;

        document.querySelectorAll('.pref-card-lang').forEach(card => {
            const val = card.getAttribute('data-value');
            const badge = card.querySelector('.check-badge');
            const text = card.querySelector('.pref-card-text');

            if (val === lang) {
                card.classList.add('border-brand-500', 'dark:border-brand-400', 'bg-brand-50/80', 'dark:bg-brand-950/60', 'ring-2', 'ring-brand-500/50', 'dark:ring-brand-400/50', 'shadow-xs');
                card.classList.remove('border-slate-200', 'dark:border-slate-700', 'hover:border-slate-300', 'dark:hover:border-slate-600', 'bg-white', 'dark:bg-slate-800');
                if (badge) badge.classList.remove('hidden');
                if (text) {
                    text.classList.add('font-black', 'text-brand-700', 'dark:text-brand-300');
                    text.classList.remove('font-bold', 'text-slate-700', 'dark:text-slate-300');
                }
            } else {
                card.classList.remove('border-brand-500', 'dark:border-brand-400', 'bg-brand-50/80', 'dark:bg-brand-950/60', 'ring-2', 'ring-brand-500/50', 'dark:ring-brand-400/50', 'shadow-xs');
                card.classList.add('border-slate-200', 'dark:border-slate-700', 'hover:border-slate-300', 'dark:hover:border-slate-600', 'bg-white', 'dark:bg-slate-800');
                if (badge) badge.classList.add('hidden');
                if (text) {
                    text.classList.remove('font-black', 'text-brand-700', 'dark:text-brand-300');
                    text.classList.add('font-bold', 'text-slate-700', 'dark:text-slate-300');
                }
            }
        });
    }
</script>
@endpush
