@extends('layouts.app')

@section('title', 'إنشاء حساب جديد — MO Habit Tracker')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-200 dark:border-slate-700 space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center mx-auto shadow-lg shadow-brand-500/20 text-2xl">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                إنشاء حساب جديد
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                ابدأ رحلتك لبناء الانضباط الصارم وإدارة عاداتك ومهامك اليومية
            </p>
        </div>

        <!-- Register Form -->
        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">الاسم الكامل *</label>
                <div class="relative">
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="محمد سالم" class="w-full pl-3 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <i class="fa-regular fa-user absolute right-3 top-3.5 text-slate-400 text-xs"></i>
                </div>
                @error('name')
                <p class="text-xs font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

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
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">كلمة المرور *</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="8 أحرف على الأقل" class="w-full pl-3 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    <i class="fa-solid fa-lock absolute right-3 top-3.5 text-slate-400 text-xs"></i>
                </div>
                @error('password')
                <p class="text-xs font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تأكيد كلمة المرور *</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" required placeholder="أعد كتابة كلمة المرور" class="w-full pl-3 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    <i class="fa-solid fa-shield absolute right-3 top-3.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.01]">
                إنشاء الحساب وبدء التتبع
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:underline">
                سجل الدخول هنا
            </a>
        </div>

    </div>
</div>
@endsection
