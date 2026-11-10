<?php

namespace App\Http\Controllers;

use App\Models\UserSettings;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $settings = $user->settings ?? UserSettings::create([
            'user_id' => $user->id,
            'theme' => 'light',
            'language' => 'ar',
            'primary_color' => '#0d9488',
            'accent_color' => '#f97316',
        ]);

        $categories = $user->categories()->get();

        return view('settings.index', compact('user', 'settings', 'categories'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', Password::defaults(), 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث الملف الشخصي بنجاح!']);
        }

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح!');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark'],
            'language' => ['required', 'in:ar,en'],
            'primary_color' => ['required', 'string', 'max:20'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $settings = $user->settings()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        session(['theme' => $settings->theme, 'locale' => $settings->language]);

        return back()->with('success', 'تم حفظ التفضيلات بنجاح!');
    }

    public function toggleTheme(Request $request)
    {
        $user = Auth::user();
        $targetTheme = $request->input('theme');
        if ($targetTheme && in_array($targetTheme, ['light', 'dark'], true)) {
            $newTheme = $targetTheme;
        } else {
            $current = $user ? ($user->settings?->theme ?? 'light') : session('theme', 'light');
            $newTheme = ($current === 'dark') ? 'light' : 'dark';
        }

        if ($user) {
            $user->settings()->updateOrCreate(
                ['user_id' => $user->id],
                ['theme' => $newTheme]
            );
        }

        session(['theme' => $newTheme]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'theme' => $newTheme]);
        }

        return back();
    }

    public function toggleLanguage(Request $request)
    {
        $user = Auth::user();
        $targetLang = $request->input('language');
        if ($targetLang && in_array($targetLang, ['ar', 'en'], true)) {
            $newLang = $targetLang;
        } else {
            $current = $user ? ($user->settings?->language ?? 'ar') : session('locale', 'ar');
            $newLang = ($current === 'ar') ? 'en' : 'ar';
        }

        if ($user) {
            $user->settings()->updateOrCreate(
                ['user_id' => $user->id],
                ['language' => $newLang]
            );
        }

        session(['locale' => $newLang]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'language' => $newLang]);
        }

        return back();
    }

    // Category CRUD
    public function storeCategory(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $category = $user->categories()->create($validated);

        return back()->with('success', 'تمت إضافة التصنيف بنجاح!');
    }

    public function destroyCategory(Category $category)
    {
        $user = Auth::user();
        if ($category->user_id !== $user->id) {
            abort(403);
        }

        $category->delete();

        return back()->with('success', 'تم حذف التصنيف بنجاح.');
    }

    public function syncTimezone(Request $request)
    {
        $tz = $request->input('timezone');
        if ($tz && in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
            if (Auth::check()) {
                Auth::user()->settings()->updateOrCreate(
                    ['user_id' => Auth::id()],
                    ['timezone' => $tz]
                );
            }
            session(['timezone' => $tz]);
            return response()->json(['success' => true, 'timezone' => $tz]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid timezone'], 422);
    }
}
