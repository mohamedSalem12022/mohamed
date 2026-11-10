<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReflectionController;
use App\Http\Controllers\ExcuseController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\WeeklyReviewController;
use App\Http\Controllers\MotivationController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\SkillController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Auto Timezone Sync & Theme / Language Toggles (Guest & Auth)
Route::post('/timezone/sync', [SettingsController::class, 'syncTimezone'])->name('timezone.sync');
Route::post('/settings/toggle-theme', [SettingsController::class, 'toggleTheme'])->name('settings.toggle-theme');
Route::post('/settings/toggle-language', [SettingsController::class, 'toggleLanguage'])->name('settings.toggle-language');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Habits & Wheel
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::post('/habits/reorder', [HabitController::class, 'reorder'])->name('habits.reorder');
    Route::put('/habits/{habit}', [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
    Route::post('/habits/log-status', [HabitController::class, 'logStatus'])->name('habits.log-status');
    Route::get('/habits/month-data', [HabitController::class, 'getMonthData'])->name('habits.month-data');

    // 3. Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])->name('tasks.toggle');
    Route::post('/tasks/{task}/subtasks', [TaskController::class, 'storeSubtask'])->name('subtasks.store');
    Route::put('/subtasks/{subtask}', [TaskController::class, 'updateSubtask'])->name('subtasks.update');
    Route::delete('/subtasks/{subtask}', [TaskController::class, 'destroySubtask'])->name('subtasks.destroy');
    Route::post('/subtasks/{subtask}/toggle', [TaskController::class, 'toggleSubtask'])->name('subtasks.toggle');

    // 4. Notes & Reflections
    Route::get('/reflections', [ReflectionController::class, 'index'])->name('reflections.index');
    Route::post('/reflections', [ReflectionController::class, 'storeOrUpdate'])->name('reflections.store');

    // 5. Excuses I Overcome
    Route::get('/excuses', [ExcuseController::class, 'index'])->name('excuses.index');
    Route::post('/excuses', [ExcuseController::class, 'store'])->name('excuses.store');
    Route::post('/excuses/{excuse}/toggle', [ExcuseController::class, 'toggleOvercame'])->name('excuses.toggle');
    Route::delete('/excuses/{excuse}', [ExcuseController::class, 'destroy'])->name('excuses.destroy');

    // 6. Analytics & Reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // 7. Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::post('/settings/toggle-theme', [SettingsController::class, 'toggleTheme'])->name('settings.toggle-theme');
    Route::post('/settings/toggle-language', [SettingsController::class, 'toggleLanguage'])->name('settings.toggle-language');
    Route::post('/settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
    Route::delete('/settings/categories/{category}', [SettingsController::class, 'destroyCategory'])->name('settings.categories.destroy');

    // 8. MO AI Assistant
    Route::get('/ai', [AiAssistantController::class, 'index'])->name('ai.index');
    Route::post('/ai/chat', [AiAssistantController::class, 'chat'])->name('ai.chat');
    Route::get('/ai/search-knowledge', [AiAssistantController::class, 'searchKnowledge'])->name('ai.search-knowledge');
    Route::post('/ai/confirm-transaction', [AiAssistantController::class, 'confirmTransaction'])->name('ai.confirm-transaction');
    Route::post('/ai/apply-action', [AiAssistantController::class, 'applyAction'])->name('ai.apply-action');
    Route::delete('/ai/history', [AiAssistantController::class, 'clearHistory'])->name('ai.clear-history');

    // 9. Smart Daily Planner (المرحلة الثانية)
    Route::get('/planner', [PlannerController::class, 'index'])->name('planner.index');
    Route::post('/planner/generate', [PlannerController::class, 'generate'])->name('planner.generate');
    Route::post('/planner/save', [PlannerController::class, 'save'])->name('planner.save');
    Route::post('/planner/items', [PlannerController::class, 'addItem'])->name('planner.items.store');
    Route::post('/planner/item', [PlannerController::class, 'addItem'])->name('planner.item.add');
    Route::put('/planner/items/{item}', [PlannerController::class, 'updateItem'])->name('planner.items.update');
    Route::put('/planner/item/{item}', [PlannerController::class, 'updateItem'])->name('planner.item.update');
    Route::delete('/planner/items/{item}', [PlannerController::class, 'deleteItem'])->name('planner.items.destroy');
    Route::delete('/planner/item/{item}', [PlannerController::class, 'deleteItem'])->name('planner.item.delete');
    Route::post('/planner/sync-tasks', [PlannerController::class, 'syncTasks'])->name('planner.sync-tasks');

    // 10. Smart Calendar (المرحلة الثانية)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // 11. Smart Reminders & Notifications (المرحلة الثانية)
    Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders', [ReminderController::class, 'store'])->name('reminders.store');
    Route::post('/reminders/{reminder}/cancel', [ReminderController::class, 'cancel'])->name('reminders.cancel');
    Route::get('/notifications/due', [ReminderController::class, 'getDue'])->name('notifications.due');
    Route::get('/reminders/due', [ReminderController::class, 'getDue'])->name('reminders.due');
    Route::post('/notifications/{notification}/read', [ReminderController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/mark-read', [ReminderController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [ReminderController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/mark-all-read', [ReminderController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // 12. Smart Weekly Review (المرحلة الثانية)
    Route::get('/weekly-review', [WeeklyReviewController::class, 'index'])->name('weekly-review.index');
    Route::post('/weekly-review/answers', [WeeklyReviewController::class, 'saveAnswers'])->name('weekly-review.answers');
    Route::post('/weekly-review/save-answers', [WeeklyReviewController::class, 'saveAnswers'])->name('weekly-review.save-answers');
    Route::post('/weekly-review/analyze', [WeeklyReviewController::class, 'analyze'])->name('weekly-review.analyze');
    Route::post('/weekly-review/plan-next-week', [WeeklyReviewController::class, 'planNextWeek'])->name('weekly-review.plan-next-week');

    // 13. My Motivations Panel (شريط محفّزاتي)
    Route::get('/motivations', [MotivationController::class, 'index'])->name('motivations.index');
    Route::post('/motivations', [MotivationController::class, 'store'])->name('motivations.store');
    Route::post('/motivations/generate-ai', [MotivationController::class, 'generateAi'])->name('motivations.generate-ai');
    Route::get('/motivations/random', [MotivationController::class, 'random'])->name('motivations.random');
    Route::post('/motivations/seed-defaults', [MotivationController::class, 'seedDefaults'])->name('motivations.seed-defaults');
    Route::put('/motivations/{motivation}', [MotivationController::class, 'update'])->name('motivations.update');
    Route::delete('/motivations/{motivation}', [MotivationController::class, 'destroy'])->name('motivations.destroy');
    Route::post('/motivations/{motivation}/toggle-pin', [MotivationController::class, 'togglePin'])->name('motivations.toggle-pin');

    // 14. Personal Finance & Expenses (المرحلة الثالثة: المال والمصروفات)
    Route::get('/finances', [FinancialController::class, 'index'])->name('finances.index');
    Route::post('/finances/transactions', [FinancialController::class, 'storeTransaction'])->name('finances.transactions.store');
    Route::put('/finances/transactions/{transaction}', [FinancialController::class, 'updateTransaction'])->name('finances.transactions.update');
    Route::delete('/finances/transactions/{transaction}', [FinancialController::class, 'destroyTransaction'])->name('finances.transactions.destroy');
    Route::post('/finances/budgets', [FinancialController::class, 'setBudget'])->name('finances.budgets.store');
    Route::post('/finances/categories', [FinancialController::class, 'storeCategory'])->name('finances.categories.store');
    Route::get('/finances/export-csv', [FinancialController::class, 'exportCsv'])->name('finances.export-csv');
    Route::get('/finances/export-pdf', [FinancialController::class, 'exportPdf'])->name('finances.export-pdf');
    Route::get('/finances/ai-insights', [FinancialController::class, 'getAiInsights'])->name('finances.ai-insights');

    // 16. Skill Mastery (إتقان مهارة)
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::get('/skills/{skill}', [SkillController::class, 'show'])->name('skills.show');
    Route::put('/skills/{skill}', [SkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');
    Route::post('/skills/{skill}/sub-skills', [SkillController::class, 'storeSubSkill'])->name('skills.sub-skills.store');
    Route::put('/skills/{skill}/sub-skills/{subSkill}', [SkillController::class, 'updateSubSkill'])->name('skills.sub-skills.update');
    Route::post('/skills/{skill}/sub-skills/{subSkill}/toggle', [SkillController::class, 'toggleSubSkillStatus'])->name('skills.sub-skills.toggle');
    Route::post('/skills/{skill}/sub-skills/{subSkill}/mastery', [SkillController::class, 'updateMasteryLevel'])->name('skills.sub-skills.mastery');
    Route::post('/skills/{skill}/sub-skills/reorder', [SkillController::class, 'reorderSubSkills'])->name('skills.sub-skills.reorder');
    Route::delete('/skills/{skill}/sub-skills/{subSkill}', [SkillController::class, 'destroySubSkill'])->name('skills.sub-skills.destroy');
    Route::post('/skills/{skill}/ai-breakdown', [SkillController::class, 'generateAiBreakdown'])->name('skills.ai-breakdown');
    Route::post('/skills/{skill}/ai-roadmap', [SkillController::class, 'generateAiRoadmap'])->name('skills.ai-roadmap');

    // 15. Admin User Management (لوحة تحكم المشرف — إدارة المستخدمين)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-role', [\App\Http\Controllers\Admin\UserController::class, 'toggleRole'])->name('users.toggle-role');
        Route::post('/whatsapp-settings', [\App\Http\Controllers\Admin\UserController::class, 'updateWhatsAppSettings'])->name('whatsapp.update');
    });
});
