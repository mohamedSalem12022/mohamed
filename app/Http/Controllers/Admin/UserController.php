<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSettings;
use App\Models\Category;
use App\Models\Habit;
use App\Models\Subtask;
use App\Models\HabitLog;
use App\Models\FinancialCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of registered users with search and metrics.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'regular' => User::where('is_admin', false)->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $query = User::query()
            ->withCount(['habits', 'tasks', 'financialTransactions']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $whatsappNumber = \App\Http\Controllers\AuthController::getRegistrationWhatsAppNumber();
        $whatsappMessage = \App\Http\Controllers\AuthController::getRegistrationWhatsAppMessage();

        return view('admin.users.index', compact('users', 'stats', 'search', 'whatsappNumber', 'whatsappMessage'));
    }

    /**
     * Store a new user created exclusively by the Administrator.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['nullable'],
        ], [
            'name.required' => 'يرجى إدخال اسم المستخدم.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
            'email.unique' => 'هذا البريد الإلكتروني مسجل بالفعل لمستخدم آخر.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->boolean('is_admin')) {
            $user->is_admin = true;
            $user->save();
        }

        // 1. Seed Default Settings
        UserSettings::create([
            'user_id' => $user->id,
            'theme' => 'light',
            'language' => 'ar',
            'primary_color' => '#0d9488',
            'accent_color' => '#f97316',
            'currency' => 'USD',
            'week_starts_on' => 6,
        ]);

        // 2. Seed Default Categories
        $categories = [
            ['name' => 'عادات وروتين', 'color' => '#0d9488', 'icon' => 'fa-clock'],
            ['name' => 'صحة ولياقة', 'color' => '#10b981', 'icon' => 'fa-heartbeat'],
            ['name' => 'عمل ومشاريع', 'color' => '#3b82f6', 'icon' => 'fa-briefcase'],
            ['name' => 'تطوير شخصي', 'color' => '#8b5cf6', 'icon' => 'fa-brain'],
        ];
        foreach ($categories as $cat) {
            Category::create(array_merge(['user_id' => $user->id], $cat));
        }

        // 3. Seed Default 7 Habits
        $habits = [
            ['name' => 'الاستيقاظ 6:00 الصبح', 'name_en' => 'Wake up 6:00', 'color' => '#0d9488', 'icon' => 'fa-sun', 'order' => 1],
            ['name' => 'صلاة الفجر', 'name_en' => 'Pray Fajr', 'color' => '#06b6d4', 'icon' => 'fa-mosque', 'order' => 2],
            ['name' => 'الأكل الصحي', 'name_en' => 'Eat healthy', 'color' => '#10b981', 'icon' => 'fa-apple-whole', 'order' => 3],
            ['name' => 'شرب 2 لتر ماء', 'name_en' => 'Drink 2L water', 'color' => '#3b82f6', 'icon' => 'fa-glass-water', 'order' => 4],
            ['name' => 'ساعة رياضة', 'name_en' => '1hr Training', 'color' => '#f59e0b', 'icon' => 'fa-dumbbell', 'order' => 5],
            ['name' => 'ساعة بدون سوشيال ميديا', 'name_en' => '1hr No Scroll', 'color' => '#8b5cf6', 'icon' => 'fa-ban', 'order' => 6],
            ['name' => 'النوم قبل 11:00', 'name_en' => 'Sleep before 11:00', 'color' => '#ec4899', 'icon' => 'fa-moon', 'order' => 7],
        ];
        foreach ($habits as $h) {
            Habit::create(array_merge(['user_id' => $user->id, 'target_days_per_week' => 7], $h));
        }

        // 4. Seed Default Financial Categories
        FinancialCategory::seedDefaultsForUser($user->id);

        $roleText = $user->isAdmin() ? 'مشرف (Admin)' : 'مستخدم عادي';
        $message = "تم إنشاء وتفعيل حساب «{$user->name}» بنجاح برتبة {$roleText}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->isAdmin(),
                ],
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Delete the specified user account and cleanly cascade all their data.
     */
    public function destroy(Request $request, User $user)
    {
        // 1. Guard against self-deletion
        if ($user->id === Auth::id()) {
            $error = 'لا يمكنك حذف حسابك الشخصي الذي تستخدمه حالياً لتفادي قفل لوحة التحكم.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 403);
            }
            return back()->with('error', $error);
        }

        // 2. Guard against deleting the last admin
        if ($user->isAdmin() && User::where('is_admin', true)->count() <= 1) {
            $error = 'لا يمكن حذف المشرف الوحيد المتبقي في النظام.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 400);
            }
            return back()->with('error', $error);
        }

        $userName = $user->name;

        // 3. Safe cascading delete wrapped in a transaction
        DB::transaction(function () use ($user) {
            // Delete 1-to-1 settings
            $user->settings()?->delete();

            // Delete notifications, reminders, events
            $user->inAppNotifications()->delete();
            $user->reminders()->delete();
            $user->calendarEvents()->delete();

            // Delete reflections, excuses, planner, AI chats, weekly reviews, motivations
            $user->aiMessages()->delete();
            $user->dailyPlans()->delete();
            $user->dailyReflections()->delete();
            $user->excuseLogs()->delete();
            $user->weeklyReviews()->delete();
            $user->motivations()->delete();

            // Delete finances (transactions, budgets, categories)
            $user->financialTransactions()->delete();
            $user->financialBudgets()->delete();
            $user->financialCategories()->delete();

            // Delete tasks & subtasks
            $taskIds = $user->tasks()->pluck('id');
            if ($taskIds->isNotEmpty()) {
                Subtask::whereIn('task_id', $taskIds)->delete();
            }
            $user->tasks()->delete();

            // Delete habits & habit logs
            $habitIds = $user->habits()->pluck('id');
            if ($habitIds->isNotEmpty()) {
                HabitLog::whereIn('habit_id', $habitIds)->delete();
            }
            $user->habitLogs()->delete();
            $user->habits()->delete();

            // Delete categories
            $user->categories()->delete();

            // Finally delete the user account
            $user->delete();
        });

        $message = "تم حذف حساب المستخدم «{$userName}» وجميع بياناته المرتبطة بنجاح.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'stats' => [
                    'total' => User::count(),
                    'admins' => User::where('is_admin', true)->count(),
                    'regular' => User::where('is_admin', false)->count(),
                ],
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Toggle a user's role between Admin and Regular User.
     */
    public function toggleRole(Request $request, User $user)
    {
        // 1. Guard against changing self role
        if ($user->id === Auth::id()) {
            $error = 'لا يمكنك تعديل صلاحيات حسابك الشخصي.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 403);
            }
            return back()->with('error', $error);
        }

        // 2. Guard against demoting the last admin
        if ($user->isAdmin() && User::where('is_admin', true)->count() <= 1) {
            $error = 'لا يمكن خفض صلاحية المشرف الوحيد في النظام.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 400);
            }
            return back()->with('error', $error);
        }

        $user->is_admin = ! (bool) $user->is_admin;
        $user->save();

        $roleText = $user->isAdmin() ? 'مشرف (Admin)' : 'مستخدم عادي';
        $message = "تم تحديث رتبة «{$user->name}» بنجاح إلى: {$roleText}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_admin' => $user->isAdmin(),
                'role_text' => $roleText,
                'message' => $message,
                'stats' => [
                    'total' => User::count(),
                    'admins' => User::where('is_admin', true)->count(),
                    'regular' => User::where('is_admin', false)->count(),
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update WhatsApp Registration number and introductory message.
     */
    public function updateWhatsAppSettings(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_number' => ['required', 'string', 'max:35'],
            'whatsapp_message' => ['nullable', 'string', 'max:500'],
        ], [
            'whatsapp_number.required' => 'يرجى إدخال رقم الواتساب المخصص لاستقبال طلبات التسجيل.',
        ]);

        $cleanNumber = preg_replace('/[^0-9]/', '', (string)$validated['whatsapp_number']);
        if (empty($cleanNumber) || strlen($cleanNumber) < 6) {
            return back()->withErrors(['whatsapp_number' => 'يرجى إدخال رقم هاتف صحيح مع رمز الدولة بدون مسافات أو علامة + (مثال: 972568080358).']);
        }

        $message = !empty($validated['whatsapp_message']) ? trim($validated['whatsapp_message']) : 'مرحبًا، أريد إنشاء حساب جديد في MO Life OS';

        \App\Models\SystemSetting::set('whatsapp_registration_number', $cleanNumber);
        \App\Models\SystemSetting::set('whatsapp_registration_message', $message);

        // Update or append to .env
        $this->updateEnvFile([
            'WHATSAPP_REGISTRATION_NUMBER' => $cleanNumber,
            'WHATSAPP_REGISTRATION_MESSAGE' => '"' . str_replace('"', '\"', $message) . '"',
        ]);

        return back()->with('success', 'تم تحديث رقم واتساب للتسجيل بنجاح!');
    }

    /**
     * Update or append environment variables in .env file safely.
     */
    protected function updateEnvFile(array $values): void
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath) || !is_writable($envPath)) {
                return;
            }

            $content = file_get_contents($envPath);
            foreach ($values as $key => $value) {
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                } else {
                    $content = rtrim($content) . "\n{$key}={$value}\n";
                }
            }

            file_put_contents($envPath, $content);
        } catch (\Throwable) {
            // Silently continue if .env write fails
        }
    }
}
