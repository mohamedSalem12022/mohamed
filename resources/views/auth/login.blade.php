@extends('layouts.app')

@section('title', 'تسجيل الدخول — MO Habit Tracker')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-200 dark:border-slate-700 space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center mx-auto shadow-lg shadow-brand-500/20 text-2xl">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                تسجيل الدخول
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                أهلاً بك مجدداً في نظام تتبع العادات والانضباط MO
            </p>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">البريد الإلكتروني *</label>
                <div class="relative">
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com" class="w-full pl-3 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    <i class="fa-regular fa-envelope absolute right-3 top-3.5 text-slate-400 text-xs"></i>
                </div>
                @error('email')
                <p class="text-xs font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">كلمة المرور *</label>
                    <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 hover:underline">
                        نسيت كلمة المرور؟
                    </a>
                </div>
                <div class="relative">
                    <input type="password" name="password" id="passwordInput" required placeholder="••••••••" class="w-full pl-10 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    <i class="fa-solid fa-lock absolute right-3 top-3.5 text-slate-400 text-xs pointer-events-none"></i>
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 flex items-center justify-center focus:outline-none" title="إظهار / إخفاء كلمة المرور" aria-label="إظهار / إخفاء كلمة المرور">
                        <i id="passwordToggleIcon" class="fa-regular fa-eye text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-brand-600 focus:ring-brand-500">
                    <span class="text-slate-600 dark:text-slate-300 font-semibold">تذكرني على هذا الجهاز</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.01]">
                تسجيل الدخول
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500">
            ليس لديك حساب بعد؟
            <a href="{{ \App\Http\Controllers\AuthController::getRegistrationWhatsAppUrl() ?? route('register') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-brand-600 hover:underline inline-flex items-center gap-1">
                <i class="fa-brands fa-whatsapp text-xs"></i>
                <span>طلب إنشاء حساب عبر واتساب</span>
            </a>
        </div>

    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('passwordToggleIcon');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
