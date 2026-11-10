@extends('layouts.app')

@section('title', 'طلب إنشاء حساب — MO Life OS')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-200 dark:border-slate-700 space-y-6 text-center">
        
        <!-- Header & Logo -->
        <div class="space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 text-white flex items-center justify-center mx-auto shadow-lg shadow-brand-500/20 text-3xl">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                طلب إنشاء حساب جديد
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                منصة تتبع العادات والانضباط وإدارة المهام — MO Life OS
            </p>
        </div>

        <!-- Explanatory Box -->
        <div class="p-4 rounded-2xl bg-teal-50/70 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-xs text-slate-700 dark:text-slate-300 leading-relaxed space-y-2 text-right">
            <div class="flex items-center gap-2 font-bold text-brand-700 dark:text-brand-300 text-sm">
                <i class="fa-solid fa-shield-halved text-base"></i>
                <span>التسجيل متاح عبر المشرف حصراً</span>
            </div>
            <p>
                لضمان أعلى معايير الخصوصية والأمان وعزل البيانات، يتم إنشاء وتفعيل الحسابات الجديدة عن طريق التواصل المباشر مع إدارة المنصة عبر تطبيق واتساب.
            </p>
        </div>

        @if(!empty($whatsappUrl))
        <!-- WhatsApp CTA Button -->
        <div class="space-y-3 pt-2">
            <a 
                href="{{ $whatsappUrl }}" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/25 transition-all hover:scale-[1.01] flex items-center justify-center gap-2.5"
            >
                <i class="fa-brands fa-whatsapp text-lg"></i>
                <span>متابعة إلى محادثة واتساب لطلب الحساب</span>
            </a>
            <p class="text-[11px] text-slate-400">
                سيتم فتح محادثة مباشرة مع المشرف لطلب اسم المستخدم وتفعيل حسابك
            </p>
        </div>
        @else
        <!-- Notice if WhatsApp number is not yet in .env -->
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-right space-y-2">
            <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300 text-xs">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>تنبيه لمدير النظام:</span>
            </div>
            <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
                لم يتم تعيين رقم واتساب للتسجيل بعد في ملف الإعدادات. يرجى إضافة رقم الهاتف في ملف <code class="font-mono bg-white dark:bg-slate-900 px-1 py-0.5 rounded border border-amber-300 dark:border-amber-700">.env</code> عبر المتغير:
            </p>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-800 font-mono text-[10px] text-slate-700 dark:text-slate-300 text-center select-all">
                WHATSAPP_REGISTRATION_NUMBER=2010xxxxxxxx
            </div>
        </div>
        @endif

        <!-- Return to Login -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:underline">
                تسجيل الدخول هنا
            </a>
        </div>

    </div>
</div>
@endsection
