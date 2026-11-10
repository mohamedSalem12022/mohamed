<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSettings;
use App\Models\Category;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Mail\PasswordRecoveryMail;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "تم تجاوز عدد محاولات الدخول المسموح بها. يرجى المحاولة بعد {$seconds} ثانية.",
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'مرحباً بك مجدداً!');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ])->onlyInput('email');
    }

    public static function getRegistrationWhatsAppNumber(): ?string
    {
        $dbNumber = \App\Models\SystemSetting::get('whatsapp_registration_number');
        $number = !empty($dbNumber) ? $dbNumber : config('services.whatsapp.registration_number');
        if (!empty($number)) {
            $clean = preg_replace('/[^0-9]/', '', (string)$number);
            return !empty($clean) ? $clean : null;
        }
        return null;
    }

    public static function getRegistrationWhatsAppMessage(): string
    {
        $dbMessage = \App\Models\SystemSetting::get('whatsapp_registration_message');
        return !empty($dbMessage) ? $dbMessage : config('services.whatsapp.registration_message', 'مرحبًا، أريد إنشاء حساب جديد في MO Life OS');
    }

    public static function getRegistrationWhatsAppUrl(): ?string
    {
        $number = self::getRegistrationWhatsAppNumber();
        if (empty($number)) {
            return null;
        }
        $message = self::getRegistrationWhatsAppMessage();
        return 'https://wa.me/' . $number . '?text=' . urlencode($message);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $whatsappUrl = self::getRegistrationWhatsAppUrl();
        if ($whatsappUrl) {
            return redirect()->away($whatsappUrl);
        }

        return view('auth.register-whatsapp', [
            'whatsappNumber' => self::getRegistrationWhatsAppNumber(),
            'whatsappUrl' => $whatsappUrl,
        ]);
    }

    public function register(Request $request)
    {
        $whatsappUrl = self::getRegistrationWhatsAppUrl();
        if ($whatsappUrl) {
            return redirect()->away($whatsappUrl);
        }

        abort(403, 'إنشاء الحسابات المباشر غير متاح. يرجى التواصل مع المشرف عبر واتساب لفتح حساب جديد.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'تم تسجيل الخروج بنجاح.');
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'البريد الإلكتروني المدخل غير مسجل لدينا في النظام.',
            ])->withInput();
        }

        $newPassword = 'pass' . rand(100000, 999999);

        $user->password = Hash::make($newPassword);
        $user->save();

        try {
            Mail::to($user->email)->send(new PasswordRecoveryMail($user, $newPassword));
        } catch (\Throwable $e) {
            Log::error('Mail sending error: ' . $e->getMessage());
        }

        return back()->with([
            'email_sent' => true,
            'sent_to' => $user->email,
        ]);
    }
}
