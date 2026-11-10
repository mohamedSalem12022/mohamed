<!DOCTYPE html>
<html lang="{{ $currentLocale ?? 'ar' }}" dir="{{ $currentDir ?? 'rtl' }}" class="{{ ($currentTheme ?? 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MO Habit Tracker & Task Manager')</title>

    <!-- Automatic City Timezone Auto-Detection -->
    <script>
        (function() {
            try {
                const detectedTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (detectedTz) {
                    const cookieMatch = document.cookie.split('; ').find(row => row.startsWith('user_timezone='));
                    const currentTz = cookieMatch ? decodeURIComponent(cookieMatch.split('=')[1]) : null;

                    if (currentTz !== detectedTz) {
                        document.cookie = "user_timezone=" + encodeURIComponent(detectedTz) + "; path=/; max-age=31536000; SameSite=Lax";
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (csrfToken) {
                            fetch("{{ route('timezone.sync') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": csrfToken,
                                    "Accept": "application/json"
                                },
                                body: JSON.stringify({ timezone: detectedTz })
                            }).catch(() => {});
                        }
                    }
                }
            } catch (e) {
                console.debug("Timezone sync notice:", e);
            }
        })();
    </script>

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0d9488">

    <!-- Google Fonts: Cairo & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Caveat:wght@600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488', // Original Primary Green
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                            950: '#042f2e',
                        },
                        accent: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#0d9488',
                            600: '#0f766e',
                            700: '#115e59',
                            800: '#115e59',
                            900: '#134e4a',
                            950: '#042f2e',
                        }
                    },
                    boxShadow: {
                        '2xs': '0 1px rgb(0 0 0 / 0.05)',
                        'xs': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                    },
                    fontFamily: {
                        sans: ['Cairo', 'Inter', 'sans-serif'],
                        handwriting: ['Caveat', 'Cairo', 'cursive'],
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        html[dir="ltr"] body, html[lang="en"] body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .dark body {
            background-color: #0b1120;
            color: #f1f5f9;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /* Lined Notebook Paper styling for Reflections */
        .lined-paper {
            background-color: #fff;
            background-image: repeating-linear-gradient(transparent, transparent 31px, #e2e8f0 31px, #e2e8f0 32px);
            line-height: 32px;
            padding: 8px 16px;
        }
        .dark .lined-paper {
            background-color: #1e293b;
            background-image: repeating-linear-gradient(transparent, transparent 31px, #334155 31px, #334155 32px);
            line-height: 32px;
        }

        /* Wheel SVG Interactive Styles */
        .wheel-cell {
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            stroke: #cbd5e1;
            stroke-width: 0.8px;
        }
        .dark .wheel-cell {
            stroke: #334155;
            stroke-width: 0.8px;
        }
        .wheel-cell:hover {
            filter: brightness(1.15);
            stroke: #0d9488;
            stroke-width: 2px;
        }
        .wheel-cell.status-completed {
            fill: #0d9488 !important; /* Turquoise marker */
        }
        .wheel-cell.status-missed {
            fill: #f97316 !important; /* Orange/Coral marker */
        }
        .wheel-cell.status-skipped {
            fill: #94a3b8 !important; /* Neutral Gray */
        }
        .wheel-cell.status-empty {
            fill: #ffffff;
        }
        .dark .wheel-cell.status-empty {
            fill: #1e293b;
        }

        /* Pulse animation for active streak */
        @keyframes fire-glow {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 4px rgba(13, 148, 136, 0.4)); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 8px rgba(13, 148, 136, 0.7)); }
        }
        .fire-active {
            animation: fire-glow 2s infinite ease-in-out;
        }

        /* Modern Slim Horizontal Scrollbar for Nav Tabs */
        #desktopNavTabs {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
            -webkit-overflow-scrolling: touch;
        }
        #desktopNavTabs::-webkit-scrollbar {
            height: 4px;
        }
        #desktopNavTabs::-webkit-scrollbar-track {
            background: transparent;
        }
        #desktopNavTabs::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.35);
            border-radius: 9999px;
        }
        #desktopNavTabs::-webkit-scrollbar-thumb:hover {
            background: rgba(13, 148, 136, 0.6);
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-800 dark:text-slate-100 flex flex-col antialiased selection:bg-brand-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-xs">
        <!-- Top Row: Logo & Brand + User Controls -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16 gap-3">
                <!-- Logo & Brand (Right in RTL) -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 group shrink-0 focus:outline-none">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform shrink-0">
                        <i class="fa-solid fa-bullseye text-base sm:text-lg"></i>
                    </div>
                    <div class="flex flex-col justify-center shrink-0">
                        <span class="text-sm sm:text-lg font-black tracking-tight text-slate-900 dark:text-white leading-none whitespace-nowrap">
                            MO <span class="text-brand-600 dark:text-brand-400">Tracker</span>
                        </span>
                        <span class="hidden sm:block text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider leading-tight whitespace-nowrap mt-1">
                            Discipline & Habits
                        </span>
                    </div>
                </a>

                <!-- Right Side Controls (Left in RTL) -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    <!-- Notifications Bell Dropdown -->
                    @auth
                    @php
                        $unreadCount = Auth::user()->inAppNotifications()->where('is_read', false)->count();
                        $recentNotifications = Auth::user()->inAppNotifications()->latest()->take(6)->get();
                    @endphp
                    <div class="relative shrink-0" id="notificationsDropdownContainer">
                        <button id="notificationsBtn" type="button" class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0" title="الإشعارات والتذكيرات">
                            <i class="fa-solid fa-bell text-xs sm:text-sm"></i>
                            <span id="unreadBadge" class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 px-1 min-w-[16px] sm:min-w-[18px] h-[16px] sm:h-[18px] bg-rose-500 text-white text-[8px] sm:text-[9px] font-black rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900 shadow-sm animate-pulse">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        </button>

                        <div id="notificationsDropdownMenu" class="hidden absolute {{ ($currentLocale ?? 'ar') === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-72 sm:w-80 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-1.5 z-50 overflow-hidden">
                            <div class="px-3.5 py-2 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('notifications_and_reminders') }}</span>
                                <button type="button" onclick="markAllNotificationsAsRead()" class="text-[10px] font-bold text-brand-600 hover:underline">
                                    {{ __('mark_all_read') }}
                                </button>
                            </div>
                            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse($recentNotifications as $rn)
                                <div class="p-3 text-xs flex items-start gap-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition {{ $rn->is_read ? 'opacity-60' : 'bg-teal-50/20 dark:bg-teal-950/20' }}">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 text-[10px] {{ $rn->type === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                                        <i class="fa-solid {{ $rn->type === 'warning' ? 'fa-triangle-exclamation' : 'fa-bell' }}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-slate-900 dark:text-white truncate">{{ $rn->title }}</p>
                                            <span class="text-[9px] text-slate-400">{{ $rn->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $rn->message }}</p>
                                    </div>
                                </div>
                                @empty
                                <div class="py-6 text-center text-slate-400">
                                    <i class="fa-regular fa-bell-slash text-xl mb-1 block"></i>
                                    <span class="text-xs">{{ __('no_notifications') }}</span>
                                </div>
                                @endforelse
                            </div>
                            <div class="px-3 py-1.5 border-t border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-center">
                                <a href="{{ route('reminders.index') }}" class="text-[11px] font-bold text-brand-600 hover:underline">
                                    {{ __('view_all_notifications') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- Streak Indicator -->
                    @auth
                    @php
                        $streakHabits = Auth::user()->habits;
                        $topStreak = 0;
                        foreach($streakHabits as $sh) {
                            $s = $sh->getCurrentStreak();
                            if ($s > $topStreak) $topStreak = $s;
                        }
                    @endphp
                    <div class="flex items-center gap-1 sm:gap-1.5 px-2 py-1 sm:px-3 sm:py-1.5 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800 text-brand-700 dark:text-brand-300 text-xs font-bold shadow-xs shrink-0" title="سلسلة الالتزام الحالية">
                        <i class="fa-solid fa-calendar-check text-brand-600 dark:text-brand-400 text-xs"></i>
                        <span>{{ $topStreak }}</span>
                        <span>{{ __('days') }}</span>
                    </div>

                    <!-- Direct MO AI Quick Button in Header -->
                    <a href="{{ route('ai.index') }}" class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-xl bg-gradient-to-r from-teal-500 to-brand-600 hover:from-teal-600 hover:to-brand-700 text-white font-bold text-xs shadow-sm shadow-brand-500/20 hover:scale-105 active:scale-95 transition-all shrink-0 {{ request()->routeIs('ai.*') ? 'ring-2 ring-brand-400' : '' }}" title="المساعد الذكي MO AI">
                        <i class="fa-solid fa-wand-magic-sparkles text-xs text-teal-100"></i>
                        <span class="hidden sm:inline">MO AI</span>
                    </a>
                    @endauth

                    <!-- Theme Toggle -->
                    <button id="themeToggleBtn" type="button" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0" title="تبديل الوضع الليلي / النهاري">
                        <i class="fa-solid fa-moon dark:hidden text-xs sm:text-sm"></i>
                        <i class="fa-solid fa-sun hidden dark:block text-amber-400 text-xs sm:text-sm"></i>
                    </button>

                    <!-- Language Toggle -->
                    <button id="langToggleBtn" type="button" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0 flex items-center gap-1.5" title="{{ ($currentLocale ?? 'ar') === 'ar' ? 'Switch to English' : 'التحويل إلى العربية' }}">
                        <i class="fa-solid fa-globe text-xs text-brand-600 dark:text-brand-400"></i>
                        <span>{{ ($currentLocale ?? 'ar') === 'ar' ? 'English' : 'عربي' }}</span>
                    </button>

                    <!-- User Menu / Auth -->
                    @auth
                    <div class="relative shrink-0" id="userMenuContainer">
                        <button id="userMenuBtn" type="button" class="flex items-center gap-1.5 sm:gap-2 p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-xs sm:text-sm font-semibold hidden sm:inline-block max-w-[120px] truncate text-slate-800 dark:text-slate-200">
                                {{ Auth::user()->name }}
                            </span>
                            <i class="fa-solid fa-chevron-down text-[9px] sm:text-[10px] text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute {{ ($currentLocale ?? 'ar') === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 py-1.5 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('welcome') }}</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                            </div>
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-gear text-slate-400"></i>
                                {{ __('settings') }}
                            </a>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-teal-700 dark:text-teal-300 font-semibold hover:bg-brand-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-users-gear text-teal-600 dark:text-teal-400"></i>
                                <span>{{ __('user_management') }}</span>
                            </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-start flex items-center gap-2 px-4 py-2 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    {{ __('logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                        <a href="{{ route('login') }}" class="px-2.5 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-brand-600">
                            {{ __('login') }}
                        </a>
                        <a href="{{ \App\Http\Controllers\AuthController::getRegistrationWhatsAppUrl() ?? route('register') }}" target="_blank" rel="noopener noreferrer" class="px-2.5 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-sm flex items-center gap-1.5" title="طلب إنشاء حساب جديد عبر واتساب">
                            <i class="fa-brands fa-whatsapp text-xs sm:text-sm"></i>
                            <span>{{ __('register') }}</span>
                        </a>
                    </div>
                    @endauth

                    @auth
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" type="button" class="lg:hidden w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0" aria-label="Toggle Menu">
                        <i class="fa-solid fa-bars text-base sm:text-lg"></i>
                    </button>
                    @endauth
                </div>
            </div>
        </div>

        @auth
        <!-- Navigation Tabs Bar (Desktop) -->
        <div class="hidden lg:block border-t border-slate-200/70 dark:border-slate-800/70 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav id="desktopNavTabs" class="flex items-center gap-1 py-1.5 overflow-x-auto scroll-smooth select-none">
                    <a href="{{ route('dashboard') }}" {{ request()->routeIs('dashboard') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-house text-xs"></i>
                        <span>{{ __('dashboard') }}</span>
                    </a>
                    <a href="{{ route('habits.index') }}" {{ request()->routeIs('habits.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('habits.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-circle-notch text-xs"></i>
                        <span>{{ __('habits_tracker') }}</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" {{ request()->routeIs('tasks.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('tasks.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-list-check text-xs"></i>
                        <span>{{ __('tasks') }}</span>
                    </a>
                    <a href="{{ route('planner.index') }}" {{ request()->routeIs('planner.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('planner.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-calendar-day text-xs text-teal-600 dark:text-teal-400"></i>
                        <span>{{ __('smart_planner') }}</span>
                    </a>
                    <a href="{{ route('calendar.index') }}" {{ request()->routeIs('calendar.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('calendar.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-calendar-days text-xs"></i>
                        <span>{{ __('calendar') }}</span>
                    </a>
                    <a href="{{ route('weekly-review.index') }}" {{ request()->routeIs('weekly-review.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('weekly-review.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-chart-line text-xs"></i>
                        <span>{{ __('weekly_review') }}</span>
                    </a>
                    <a href="{{ route('reflections.index') }}" {{ request()->routeIs('reflections.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('reflections.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-book-open text-xs"></i>
                        <span>{{ __('notes_reflections') }}</span>
                    </a>
                    <a href="{{ route('excuses.index') }}" {{ request()->routeIs('excuses.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('excuses.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>{{ __('excuses_overcome') }}</span>
                    </a>
                    <a href="{{ route('analytics.index') }}" {{ request()->routeIs('analytics.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('analytics.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-chart-pie text-xs"></i>
                        <span>{{ __('reports_analytics') }}</span>
                    </a>
                    <a href="{{ route('finances.index') }}" {{ request()->routeIs('finances.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('finances.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-wallet text-xs text-teal-600 dark:text-teal-400"></i>
                        <span>{{ __('finances') }}</span>
                    </a>
                    <a href="{{ route('skills.index') }}" {{ request()->routeIs('skills.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('skills.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-graduation-cap text-xs text-teal-600 dark:text-teal-400"></i>
                        <span>{{ __('skills_mastery') }}</span>
                    </a>
                    <a href="{{ route('ai.index') }}" {{ request()->routeIs('ai.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('ai.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-wand-magic-sparkles text-xs text-brand-600 dark:text-brand-400"></i>
                        <span>{{ __('mo_ai') }}</span>
                    </a>
                    @if(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" {{ request()->routeIs('admin.users.*') ? 'data-active=true' : '' }} class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('admin.users.*') ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-bold shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-users-gear text-xs text-teal-600 dark:text-teal-400"></i>
                        <span>{{ __('user_management') }}</span>
                    </a>
                    @endif
                </nav>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobileMenu" class="hidden xl:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-4 py-3 space-y-1 shadow-lg">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-house text-sm w-5 text-center"></i>
                <span>{{ __('dashboard') }}</span>
            </a>
            <a href="{{ route('habits.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('habits.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-circle-notch text-sm w-5 text-center"></i>
                <span>{{ __('habits_tracker') }}</span>
            </a>
            <a href="{{ route('tasks.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('tasks.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-list-check text-sm w-5 text-center"></i>
                <span>{{ __('tasks') }}</span>
            </a>
            <a href="{{ route('planner.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('planner.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-calendar-day text-sm w-5 text-center text-teal-600"></i>
                <span>{{ __('smart_planner') }}</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('calendar.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-calendar-days text-sm w-5 text-center"></i>
                <span>{{ __('calendar') }}</span>
            </a>
            <a href="{{ route('weekly-review.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('weekly-review.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-chart-line text-sm w-5 text-center"></i>
                <span>{{ __('weekly_review') }}</span>
            </a>
            <a href="{{ route('reminders.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('reminders.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-bell text-sm w-5 text-center"></i>
                <span>{{ __('reminders') }}</span>
            </a>
            <a href="{{ route('reflections.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('reflections.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-book-open text-sm w-5 text-center"></i>
                <span>{{ __('notes_reflections') }}</span>
            </a>
            <a href="{{ route('excuses.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('excuses.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-shield-halved text-sm w-5 text-center"></i>
                <span>{{ __('excuses_overcome') }}</span>
            </a>
            <a href="{{ route('analytics.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('analytics.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-chart-pie text-sm w-5 text-center"></i>
                <span>{{ __('reports_analytics') }}</span>
            </a>
            <a href="{{ route('finances.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('finances.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-wallet text-sm w-5 text-center text-teal-600 dark:text-teal-400"></i>
                <span>{{ __('finances') }}</span>
            </a>
            <a href="{{ route('skills.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('skills.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-graduation-cap text-sm w-5 text-center text-teal-600 dark:text-teal-400"></i>
                <span>{{ __('skills_mastery') }}</span>
            </a>
            <a href="{{ route('ai.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('ai.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-wand-magic-sparkles text-sm w-5 text-center text-brand-600 dark:text-brand-400"></i>
                <span>{{ __('mo_ai') }}</span>
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('settings.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-gear text-sm w-5 text-center"></i>
                <span>{{ __('settings') }}</span>
            </a>
            @if(Auth::check() && Auth::user()->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid fa-users-gear text-sm w-5 text-center text-teal-600 dark:text-teal-400"></i>
                <span>إدارة المستخدمين</span>
            </a>
            @endif
        </div>
        @endauth
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash Alert Messages -->
        @if(session('success'))
        <div id="flashAlert" class="mb-5 flex items-center justify-between p-4 rounded-xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 text-teal-800 dark:text-teal-200 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-teal-600 text-lg"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button onclick="document.getElementById('flashAlert').remove()" class="text-teal-500 hover:text-teal-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div id="flashAlertErr" class="mb-5 flex items-center justify-between p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button onclick="document.getElementById('flashAlertErr').remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer Quote (from reference sheet: "Discipline is doing what needs to be done...") -->
    <footer class="mt-auto border-t border-slate-200 dark:border-slate-800 py-6 bg-white dark:bg-slate-900/50 text-center">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-quote-left text-brand-600"></i>
                <span class="italic font-bold text-slate-700 dark:text-slate-300">Discipline is doing what needs to be done, even when you don't feel like it.</span>
                <i class="fa-solid fa-quote-right text-brand-600"></i>
            </div>
            <p class="text-xs text-slate-400 mt-2">
                MO Habit Tracker & Task Manager &copy; {{ date('Y') }} — صُمم لتحقيق أعلى مستويات الانضباط الشخصي
            </p>
        </div>
    </footer>

    <!-- Global Toast Notification Container -->
    <div id="toastContainer" class="fixed bottom-5 {{ ($currentLocale ?? 'ar') === 'ar' ? 'left-5' : 'right-5' }} z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Motivations Sidebar Panel (محفّزاتي) -->
    @auth
        @include('partials.motivations-panel')
    @endauth

    <!-- Scripts -->
    <script>
        // Setup CSRF header for AJAX fetch requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // User Dropdown Toggle
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', () => userDropdown.classList.add('hidden'));
        }

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Desktop Nav Tabs Horizontal Scrolling & Active Tab Auto-Focus
        const desktopNavTabs = document.getElementById('desktopNavTabs');
        if (desktopNavTabs) {
            // 1. Auto-scroll to center the active tab so it is always visible
            const activeNavTab = desktopNavTabs.querySelector('[data-active="true"]');
            if (activeNavTab) {
                setTimeout(() => {
                    activeNavTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 80);
            }

            // 2. Enable horizontal mouse wheel scrolling
            desktopNavTabs.addEventListener('wheel', (e) => {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    const isRtl = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
                    desktopNavTabs.scrollBy({
                        left: isRtl ? -e.deltaY * 1.5 : e.deltaY * 1.5,
                        behavior: 'smooth'
                    });
                }
            }, { passive: false });

            // 3. Enable drag-to-scroll with mouse
            let isDragging = false;
            let startX, scrollLeftVal;
            desktopNavTabs.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.pageX - desktopNavTabs.offsetLeft;
                scrollLeftVal = desktopNavTabs.scrollLeft;
            });
            window.addEventListener('mouseup', () => {
                isDragging = false;
                desktopNavTabs.classList.remove('cursor-grabbing');
            });
            desktopNavTabs.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                const x = e.pageX - desktopNavTabs.offsetLeft;
                const walk = (x - startX);
                if (Math.abs(walk) > 5) {
                    desktopNavTabs.classList.add('cursor-grabbing');
                    desktopNavTabs.scrollLeft = scrollLeftVal - walk;
                }
            });
        }

        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        themeToggleBtn.addEventListener('click', () => {
            fetch('{{ route("settings.toggle-theme") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    if (data.theme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            }).catch(() => {
                document.documentElement.classList.toggle('dark');
            });
        });

        // Language Toggle
        const langToggleBtn = document.getElementById('langToggleBtn');
        langToggleBtn.addEventListener('click', () => {
            fetch('{{ route("settings.toggle-language") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });

        // Global Toast Notification Function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const isSuccess = type === 'success';

            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all transform duration-300 translate-y-4 opacity-0 ${
                isSuccess
                ? 'bg-teal-600 text-white border-teal-500 shadow-teal-500/20'
                : 'bg-rose-600 text-white border-rose-500 shadow-rose-500/20'
            }`;

            toast.innerHTML = `
                <i class="fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'} text-base"></i>
                <span>${message}</span>
            `;

            container.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
            }, 10);

            // Animate out & remove
            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Notifications Dropdown Toggle
        const notificationsBtn = document.getElementById('notificationsBtn');
        const notificationsDropdownMenu = document.getElementById('notificationsDropdownMenu');
        if (notificationsBtn && notificationsDropdownMenu) {
            notificationsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationsDropdownMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', () => {
                if (notificationsDropdownMenu) notificationsDropdownMenu.classList.add('hidden');
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
                    const badge = document.getElementById('unreadBadge');
                    if (badge) badge.classList.add('hidden');
                    showToast('تم تحديد جميع الإشعارات كمقروءة ✓', 'success');
                }
            });
        }

        // Notification & Due Reminders Poller
        @auth
        function pollDueNotifications() {
            fetch('{{ route("reminders.due") }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(notif => {
                            showToast(notif.title + ': ' + notif.message, 'success');
                            if ('Notification' in window && Notification.permission === 'granted') {
                                new Notification(notif.title, {
                                    body: notif.message,
                                    icon: '/favicon.ico'
                                });
                            }
                        });
                        const badge = document.getElementById('unreadBadge');
                        if (badge && data.unread_count > 0) {
                            badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                            badge.classList.remove('hidden');
                        }
                    }
                })
                .catch(() => {});
        }

        // Poll every 45 seconds
        setInterval(pollDueNotifications, 45000);
        @endauth
    </script>
    @stack('scripts')
</body>
</html>
