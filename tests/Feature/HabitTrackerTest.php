<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Habit;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\AiMessage;
use App\Models\Category;
use App\Models\DailyReflection;
use App\Models\ExcuseLog;
use App\Models\HabitLog;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HabitTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Mohamed Salem',
            'email' => 'mo@tracker.com',
            'password' => Hash::make('password'),
        ]);

        $this->user->settings()->create([
            'theme' => 'light',
            'language' => 'ar',
            'primary_color' => '#0d9488',
        ]);

        $this->user->habits()->create([
            'name' => 'الاستيقاظ 6:00 الصبح',
            'name_en' => 'Wake up 6:00',
            'color' => '#0d9488',
            'target_days_per_week' => 7,
            'order' => 1,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول');
    }

    public function test_user_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'mo@tracker.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('إنجاز اليوم');
        $response->assertSee('إنجاز الشهر');
        $response->assertSee('دائرة تتبع العادات');
    }

    public function test_authenticated_user_can_access_habits_page(): void
    {
        $response = $this->actingAs($this->user)->get('/habits');
        $response->assertStatus(200);
        $response->assertSee('HABITS');
        $response->assertSee('REFLECTIONS');
        $response->assertSee('Excuses I overcome');
    }

    public function test_user_can_log_habit_status_via_ajax(): void
    {
        $habit = $this->user->habits()->first();
        $this->assertNotNull($habit);

        $today = Carbon::today()->toDateString();

        $response = $this->actingAs($this->user)->postJson('/habits/log-status', [
            'habit_id' => $habit->id,
            'date' => $today,
            'status' => 'completed',
            'note' => 'تم الإنجاز في الموعد',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
            'habit_id' => $habit->id,
            'date' => $today,
        ]);

        $log = HabitLog::where('habit_id', $habit->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('completed', $log->status);
    }

    public function test_user_can_create_and_toggle_task(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'مهمة اختبار جديدة',
            'priority' => 'high',
            'due_date' => Carbon::today()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->postJson("/tasks/{$task->id}/toggle");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    public function test_user_can_save_daily_reflection(): void
    {
        $today = Carbon::today()->toDateString();

        $response = $this->actingAs($this->user)->post('/reflections', [
            'date' => $today,
            'notes' => 'يوم ممتاز ومليء بالتركيز',
            'best_win' => 'إنهاء مهام العمل مبكراً',
            'lessons_learned' => 'البدء بالمهمة الأصعب صباحاً',
            'rating' => 5,
            'tomorrow_improvements' => 'النوم مبكراً',
        ]);

        $response->assertRedirect("/reflections?date={$today}");

        $reflection = DailyReflection::where('user_id', $this->user->id)->first();
        $this->assertNotNull($reflection);
        $this->assertEquals('إنهاء مهام العمل مبكراً', $reflection->best_win);
    }

    public function test_user_can_access_analytics_page(): void
    {
        $response = $this->actingAs($this->user)->get('/analytics');
        $response->assertStatus(200);
        $response->assertSee('لوحة التقارير والتحليلات البيانية');
    }

    public function test_user_can_toggle_theme_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->postJson('/settings/toggle-theme');
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'theme' => 'dark']);

        $this->assertEquals('dark', session('theme'));
    }

    public function test_user_can_toggle_language_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->postJson('/settings/toggle-language');
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'language' => 'en']);

        $this->assertEquals('en', session('locale'));
    }

    public function test_user_can_save_preferences(): void
    {
        $response = $this->actingAs($this->user)->post('/settings/preferences', [
            'theme' => 'dark',
            'language' => 'en',
            'primary_color' => '#0d9488',
        ]);

        $response->assertSessionHas('theme', 'dark');
        $response->assertSessionHas('locale', 'en');
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $this->user->id,
            'theme' => 'dark',
            'language' => 'en',
        ]);
    }

    public function test_user_can_view_forgot_password_page(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('استعادة كلمة المرور');
    }

    public function test_user_can_send_password_to_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'mo@tracker.com',
        ]);

        $response->assertSessionHas('email_sent', true);
        $response->assertSessionHas('sent_to', 'mo@tracker.com');

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PasswordRecoveryMail::class, function ($mail) {
            return $mail->hasTo('mo@tracker.com') && !empty($mail->newPassword);
        });
    }

    public function test_forgot_password_fails_for_unknown_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_create_subtask(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'المهمة الرئيسية للبرمجة',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->postJson("/tasks/{$task->id}/subtasks", [
            'title' => 'تجهيز قاعدة البيانات',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('subtasks', [
            'task_id' => $task->id,
            'title' => 'تجهيز قاعدة البيانات',
            'is_completed' => false,
        ]);
    }

    public function test_user_can_update_subtask_title(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'مهمة رئيسية',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $subtask = $task->subtasks()->create([
            'title' => 'اسم قديم',
            'is_completed' => false,
        ]);

        $response = $this->actingAs($this->user)->putJson("/subtasks/{$subtask->id}", [
            'title' => 'اسم جديد معدل',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('subtasks', [
            'id' => $subtask->id,
            'title' => 'اسم جديد معدل',
        ]);
    }

    public function test_user_can_toggle_subtask_and_auto_complete_parent_task(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'إنشاء موقعي الإلكتروني',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $sub1 = $task->subtasks()->create(['title' => 'تجهيز قاعدة البيانات', 'is_completed' => false]);
        $sub2 = $task->subtasks()->create(['title' => 'إنشاء صفحة تسجيل الدخول', 'is_completed' => false]);

        // Complete subtask 1 -> main task still pending
        $res1 = $this->actingAs($this->user)->postJson("/subtasks/{$sub1->id}/toggle");
        $res1->assertStatus(200);
        $res1->assertJson(['success' => true, 'is_completed' => true]);
        $this->assertEquals('pending', $task->fresh()->status);

        // Complete subtask 2 -> main task becomes completed automatically!
        $res2 = $this->actingAs($this->user)->postJson("/subtasks/{$sub2->id}/toggle");
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'is_completed' => true, 'task_status' => 'completed']);
        $this->assertEquals('completed', $task->fresh()->status);

        // Reopen subtask 1 -> main task automatically reverts to pending!
        $res3 = $this->actingAs($this->user)->postJson("/subtasks/{$sub1->id}/toggle");
        $res3->assertStatus(200);
        $res3->assertJson(['success' => true, 'is_completed' => false, 'task_status' => 'pending']);
        $this->assertEquals('pending', $task->fresh()->status);
    }

    public function test_completing_task_with_incomplete_subtasks_requires_confirmation(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'مهمة مع مهام فرعية غير منجزة',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $sub = $task->subtasks()->create(['title' => 'مهمة فرعية لم تكتمل', 'is_completed' => false]);

        // Trying to complete parent without confirmation
        $res = $this->actingAs($this->user)->postJson("/tasks/{$task->id}/toggle");
        $res->assertStatus(200);
        $res->assertJson([
            'requires_confirmation' => true,
        ]);
        $this->assertEquals('pending', $task->fresh()->status);

        // Confirming completing all subtasks as well
        $resConfirm = $this->actingAs($this->user)->postJson("/tasks/{$task->id}/toggle", [
            'confirm_complete_subtasks' => true,
        ]);
        $resConfirm->assertStatus(200);
        $resConfirm->assertJson(['success' => true, 'status' => 'completed']);

        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertTrue($sub->fresh()->is_completed);
    }

    public function test_user_can_delete_subtask(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'مهمة للحذف الفرعي',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $sub = $task->subtasks()->create(['title' => 'مهمة ستُحذف', 'is_completed' => false]);

        $response = $this->actingAs($this->user)->deleteJson("/subtasks/{$sub->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('subtasks', ['id' => $sub->id]);
    }

    public function test_unauthorized_user_cannot_modify_subtask_of_another_user(): void
    {
        $otherUser = User::create([
            'name' => 'مستخدم آخر',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);

        $task = $this->user->tasks()->create([
            'title' => 'مهمة المستخدم الأول',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $sub = $task->subtasks()->create(['title' => 'مهمة فرعية خاصة', 'is_completed' => false]);

        // Attempting to toggle or delete by other user must return 403 Forbidden
        $response = $this->actingAs($otherUser)->postJson("/subtasks/{$sub->id}/toggle");
        $response->assertStatus(403);

        $responseDel = $this->actingAs($otherUser)->deleteJson("/subtasks/{$sub->id}");
        $responseDel->assertStatus(403);
    }

    public function test_user_can_view_ai_assistant_page(): void
    {
        $response = $this->actingAs($this->user)->get('/ai');
        $response->assertStatus(200);
        $response->assertSee('المساعد الذكي');
    }

    public function test_user_can_chat_with_ai_assistant(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*generativelanguage.googleapis.com*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'أهلاً بك! أنا جاهز لمساعدتك اليوم.']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/chat', [
            'message' => 'مرحباً، كيف يمكنك مساعدتي اليوم؟',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('ai_messages', [
            'user_id' => $this->user->id,
            'role' => 'user',
            'content' => 'مرحباً، كيف يمكنك مساعدتي اليوم؟',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'user_id' => $this->user->id,
            'role' => 'assistant',
        ]);
    }

    public function test_user_can_trigger_distracted_mode(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'مهمة مستعجلة جداً',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            '*generativelanguage.googleapis.com*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "لا تقلق! ابدأ بهذه المهمة الآن.\n```json\n{\"action\": \"distracted_task\", \"task_id\": {$task->id}, \"first_step\": \"افتح المهمة وركز عليها\", \"duration_minutes\": 20}\n```"]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/chat', [
            'mode' => 'distracted',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $assistantMsg = AiMessage::where('user_id', $this->user->id)->where('role', 'assistant')->latest()->first();
        $this->assertNotNull($assistantMsg);
        $this->assertEquals('distracted_task', $assistantMsg->action_type);
        $this->assertEquals($task->id, $assistantMsg->action_data['task_id']);
    }

    public function test_user_can_trigger_plan_day_mode(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*generativelanguage.googleapis.com*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'إليك خطة يومية مقترحة: صباحاً ومساءً.']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/chat', [
            'mode' => 'plan_day',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_user_can_apply_ai_suggested_subtasks_with_approval(): void
    {
        $task = $this->user->tasks()->create([
            'title' => 'بناء تطبيق جوال',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $msg = $this->user->aiMessages()->create([
            'role' => 'assistant',
            'content' => 'إليك خطوات مقترحة',
            'action_type' => 'subtask_breakdown',
            'action_data' => [
                'task_id' => $task->id,
                'subtasks' => ['تصميم الشاشات', 'ربط الـ API', 'رفع التطبيق'],
            ],
            'is_action_executed' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/apply-action', [
            'message_id' => $msg->id,
            'action' => 'add_subtasks',
            'task_id' => $task->id,
            'subtasks' => ['تصميم الشاشات', 'ربط الـ API', 'رفع التطبيق'],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('subtasks', [
            'task_id' => $task->id,
            'title' => 'تصميم الشاشات',
        ]);
        $this->assertDatabaseHas('subtasks', [
            'task_id' => $task->id,
            'title' => 'رفع التطبيق',
        ]);

        $this->assertTrue($msg->fresh()->is_action_executed);
    }

    public function test_user_cannot_apply_action_to_other_users_task(): void
    {
        $otherUser = User::create([
            'name' => 'شخص آخر',
            'email' => 'person@example.com',
            'password' => Hash::make('password'),
        ]);

        $otherTask = $otherUser->tasks()->create([
            'title' => 'مهمة سرية للغير',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $msg = $this->user->aiMessages()->create([
            'role' => 'assistant',
            'content' => 'اقتراح',
            'action_type' => 'subtask_breakdown',
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/apply-action', [
            'message_id' => $msg->id,
            'action' => 'add_subtasks',
            'task_id' => $otherTask->id,
            'subtasks' => ['اختراق غير مسموح'],
        ]);

        $response->assertStatus(404);
    }

    public function test_user_can_clear_ai_chat_history(): void
    {
        $this->user->aiMessages()->create(['role' => 'user', 'content' => 'سؤال قديم']);
        $this->user->aiMessages()->create(['role' => 'assistant', 'content' => 'جواب قديم']);

        $response = $this->actingAs($this->user)->deleteJson('/ai/history');
        $response->assertStatus(200);

        $this->assertEquals(0, $this->user->aiMessages()->count());
    }

    public function test_user_can_reorder_habits(): void
    {
        // Setup multiple habits
        $habit1 = $this->user->habits()->first(); // order 1
        $habit2 = $this->user->habits()->create([
            'name' => 'شرب ماء',
            'color' => '#3b82f6',
            'target_days_per_week' => 7,
            'order' => 2,
        ]);
        $habit3 = $this->user->habits()->create([
            'name' => 'رياضة',
            'color' => '#10b981',
            'target_days_per_week' => 5,
            'order' => 3,
        ]);

        // Reorder: put first habit down at the bottom [habit2, habit3, habit1]
        $res = $this->actingAs($this->user)->postJson('/habits/reorder', [
            'habit_ids' => [$habit2->id, $habit3->id, $habit1->id],
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        // Verify new orders in DB
        $this->assertEquals(1, $habit2->fresh()->order);
        $this->assertEquals(2, $habit3->fresh()->order);
        $this->assertEquals(3, $habit1->fresh()->order);

        // Verify habits page loads with the new order
        $page = $this->actingAs($this->user)->get('/habits');
        $page->assertStatus(200);
        $habitsInView = $page->viewData('habits');
        $this->assertEquals($habit2->id, $habitsInView[0]->id);
        $this->assertEquals($habit3->id, $habitsInView[1]->id);
        $this->assertEquals($habit1->id, $habitsInView[2]->id);
    }

    public function test_user_cannot_reorder_other_users_habits(): void
    {
        $otherUser = User::create([
            'name' => 'شخص آخر',
            'email' => 'other_user_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
        ]);

        $otherHabit = $otherUser->habits()->create([
            'name' => 'عادة خاصة بالغير',
            'color' => '#ef4444',
            'target_days_per_week' => 7,
            'order' => 1,
        ]);

        $myHabit = $this->user->habits()->first();

        // Attempting to reorder including other user's habit should ignore other user's habit
        $res = $this->actingAs($this->user)->postJson('/habits/reorder', [
            'habit_ids' => [$otherHabit->id, $myHabit->id],
        ]);

        $res->assertStatus(200);
        // The other habit's order must NOT change
        $this->assertEquals(1, $otherHabit->fresh()->order);
    }
}
