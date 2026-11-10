@extends('layouts.app')

@section('title', 'استعادة كلمة المرور — MO Habit Tracker')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-200 dark:border-slate-700 space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center mx-auto shadow-lg shadow-brand-500/20 text-2xl">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                استعادة كلمة المرور
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                أدخل بريدك الإلكتروني المسجل وسنرسل لك كلمة المرور الجديدة مباشرة إلى بريدك
            </p>
        </div>

        @if(session('email_sent'))
            <!-- Recovery Success Result Card (Email Sent) -->
            <div class="p-6 rounded-2xl bg-teal-50 dark:bg-teal-950/50 border border-teal-500/30 text-center space-y-4">
                <div class="w-14 h-14 rounded-full bg-teal-100 dark:bg-teal-900/60 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto text-2xl shadow-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 dark:text-white text-base">
                        تم إرسال كلمة المرور إلى بريدك!
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                        أرسلنا رسالة تحتوي على كلمة المرور الجديدة إلى:
                        <br>
                        <strong class="font-mono text-brand-600 dark:text-brand-400 text-sm">{{ session('sent_to') }}</strong>
                    </p>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed text-right space-y-1">
                    <p class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-brand-600"></i> ملاحظة:
                    </p>
                    <p>يرجى فحص صندوق الوارد (Inbox) أو مجلد الرسائل غير المرغوب فيها (Spam / Junk) للحصول على كلمة المرور، ثم قم بالدخول بها.</p>
                </div>

                <a href="{{ route('login') }}" class="block w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.01]">
                    <i class="fa-solid fa-arrow-right-to-bracket ml-1.5"></i>
                    الانتقال لتسجيل الدخول
                </a>
            </div>
        @else
            <!-- Password Recovery Form -->
            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">البريد الإلكتروني المسجل *</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com" class="w-full pl-3 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                        <i class="fa-regular fa-envelope absolute right-3 top-3.5 text-slate-400 text-xs"></i>
                    </div>
                    @error('email')
                    <p class="text-xs font-bold text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-500/20 transition-all hover:scale-[1.01] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>إرسال كلمة المرور إلى البريد الإلكتروني</span>
                </button>
            </form>
        @endif

        <div class="text-center pt-2 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500">
            تذكرت كلمة المرور؟
            <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:underline">
                العودة لتسجيل الدخول
            </a>
        </div>

    </div>
</div>
@endsection
