<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\CalendarEvent;
use App\Models\Reminder;
use App\Models\InAppNotification;
use App\Models\DailyPlan;
use App\Models\DailyPlanItem;
use App\Models\WeeklyReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class Phase2Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an authenticated test user
        $this->user = User::factory()->create([
            'email' => 'phase2_test_' . uniqid() . '@example.com',
        ]);
    }

    // ==========================================
    // 1. Smart Daily Planner Tests
    // ==========================================

    public function test_planner_index_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('planner.index'));
        $response->assertStatus(200);
        $response->assertSee('خطتي الذكية لليوم');
    }

    public function test_planner_generate_creates_daily_plan_with_items()
    {
        // Create 2 test tasks for today
        $task1 = Task::create([
            'user_id' => $this->user->id,
            'title' => 'مهمة برمجية هامة',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => Carbon::today(),
        ]);

        $task2 = Task::create([
            'user_id' => $this->user->id,
            'title' => 'مهمة قراءة وتلخيص',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('planner.generate'), [
            'date' => Carbon::today()->toDateString(),
            'work_start_time' => '09:00',
            'work_end_time' => '17:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $plan = DailyPlan::where('user_id', $this->user->id)
            ->whereDate('plan_date', Carbon::today())
            ->first();

        $this->assertNotNull($plan);
        $this->assertGreaterThan(0, $plan->items()->count());
    }

    public function test_planner_item_crud_operations()
    {
        $plan = DailyPlan::create([
            'user_id' => $this->user->id,
            'plan_date' => Carbon::today()->toDateString(),
            'is_approved' => false,
        ]);

        // Add item
        $addRes = $this->actingAs($this->user)->postJson(route('planner.item.add'), [
            'date' => Carbon::today()->toDateString(),
            'title' => 'جلسة برمجة مركزة',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'type' => 'focus',
            'priority' => 'high',
            'is_locked' => true,
        ]);

        $addRes->assertStatus(200);
        $this->assertDatabaseHas('daily_plan_items', [
            'daily_plan_id' => $plan->id,
            'title' => 'جلسة برمجة مركزة',
            'is_locked' => 1,
        ]);

        $item = DailyPlanItem::where('daily_plan_id', $plan->id)->first();

        // Update item
        $updRes = $this->actingAs($this->user)->putJson(route('planner.item.update', $item->id), [
            'title' => 'جلسة برمجة محدثة',
            'is_completed' => true,
        ]);
        $updRes->assertStatus(200);

        $item->refresh();
        $this->assertEquals('جلسة برمجة محدثة', $item->title);
        $this->assertTrue((bool)$item->is_completed);

        // Delete item
        $delRes = $this->actingAs($this->user)->deleteJson(route('planner.item.delete', $item->id));
        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('daily_plan_items', ['id' => $item->id]);
    }

    public function test_planner_save_and_sync_tasks()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'مهمة مزامنة المواعيد',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $plan = DailyPlan::create([
            'user_id' => $this->user->id,
            'plan_date' => Carbon::today()->toDateString(),
            'is_approved' => false,
        ]);

        $item = DailyPlanItem::create([
            'daily_plan_id' => $plan->id,
            'task_id' => $task->id,
            'title' => $task->title,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'duration_minutes' => 60,
            'type' => 'task',
        ]);

        // Approve plan
        $saveRes = $this->actingAs($this->user)->postJson(route('planner.save'), [
            'date' => Carbon::today()->toDateString(),
            'is_approved' => true,
        ]);
        $saveRes->assertStatus(200);

        $plan->refresh();
        $this->assertTrue((bool)$plan->is_approved);

        // Sync task dates
        $syncRes = $this->actingAs($this->user)->postJson(route('planner.sync-tasks'), [
            'date' => Carbon::today()->toDateString(),
        ]);
        $syncRes->assertStatus(200);

        $task->refresh();
        $this->assertNotNull($task->due_date);
        $this->assertEquals(Carbon::today()->toDateString(), $task->due_date->toDateString());
    }

    // ==========================================
    // 2. Smart Calendar Tests
    // ==========================================

    public function test_calendar_pages_load_successfully()
    {
        $responseMonth = $this->actingAs($this->user)->get(route('calendar.index', ['view' => 'month']));
        $responseMonth->assertStatus(200);

        $responseWeek = $this->actingAs($this->user)->get(route('calendar.index', ['view' => 'week']));
        $responseWeek->assertStatus(200);

        $responseDay = $this->actingAs($this->user)->get(route('calendar.index', ['view' => 'day']));
        $responseDay->assertStatus(200);
    }

    public function test_calendar_event_crud_and_json_feed()
    {
        $now = Carbon::now();

        // 1. Create Event with Reminder
        $storeRes = $this->actingAs($this->user)->postJson(route('calendar.store'), [
            'title' => 'اجتماع مناقشة المشروع',
            'event_type' => 'meeting',
            'start_time' => $now->toDateTimeString(),
            'end_time' => $now->copy()->addHour()->toDateTimeString(),
            'reminder_minutes_before' => 30,
        ]);

        $storeRes->assertStatus(200);
        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $this->user->id,
            'title' => 'اجتماع مناقشة المشروع',
            'event_type' => 'meeting',
        ]);

        // Check auto reminder creation
        $this->assertDatabaseHas('reminders', [
            'user_id' => $this->user->id,
            'title' => 'تذكير بـ: اجتماع مناقشة المشروع',
        ]);

        $event = CalendarEvent::where('user_id', $this->user->id)->first();

        // 2. Events JSON feed
        $eventsFeed = $this->actingAs($this->user)->getJson(route('calendar.events', [
            'start' => $now->copy()->subDay()->toISOString(),
            'end' => $now->copy()->addDays(2)->toISOString(),
        ]));
        $eventsFeed->assertStatus(200);
        $eventsData = $eventsFeed->json();
        $this->assertIsArray($eventsData);
        $this->assertTrue(collect($eventsData)->contains('id', $event->id));

        // 3. Update Event
        $updRes = $this->actingAs($this->user)->putJson(route('calendar.update', $event->id), [
            'title' => 'اجتماع محدث',
            'event_type' => 'focus',
            'start_time' => $event->start_time->toDateTimeString(),
            'end_time' => $event->end_time->toDateTimeString(),
        ]);
        $updRes->assertStatus(200);
        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'title' => 'اجتماع محدث',
            'event_type' => 'focus',
        ]);

        // 4. Delete Event
        $delRes = $this->actingAs($this->user)->deleteJson(route('calendar.destroy', $event->id));
        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    // ==========================================
    // 3. Reminders & In-App Notifications Tests
    // ==========================================

    public function test_reminders_index_and_creation()
    {
        $indexRes = $this->actingAs($this->user)->get(route('reminders.index'));
        $indexRes->assertStatus(200);

        // Store custom reminder
        $remindAt = Carbon::now()->addHours(2);
        $storeRes = $this->actingAs($this->user)->postJson(route('reminders.store'), [
            'title' => 'شرب الماء والراحة',
            'remind_type' => 'custom',
            'remind_at' => $remindAt->toDateTimeString(),
        ]);
        $storeRes->assertStatus(200);

        $this->assertDatabaseHas('reminders', [
            'user_id' => $this->user->id,
            'title' => 'شرب الماء والراحة',
            'status' => 'pending',
        ]);

        $reminder = Reminder::where('user_id', $this->user->id)->first();

        // Cancel reminder
        $cancelRes = $this->actingAs($this->user)->postJson(route('reminders.cancel', $reminder->id));
        $cancelRes->assertStatus(200);
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_artisan_reminders_process_command()
    {
        // Create a reminder due in the past
        $pastReminder = Reminder::create([
            'user_id' => $this->user->id,
            'title' => 'تنبيه موعد مستحق الآن',
            'remind_type' => 'custom',
            'remind_at' => Carbon::now()->subMinutes(5),
            'status' => 'pending',
        ]);

        // Run the artisan process command
        \Illuminate\Support\Facades\Artisan::call('reminders:process');

        // Reminder should be processed
        $pastReminder->refresh();
        $this->assertEquals('sent', $pastReminder->status);

        // An in-app notification must have been created
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $this->user->id,
            'reminder_id' => $pastReminder->id,
        ]);
    }

    public function test_notification_read_and_mark_all_read()
    {
        $notif1 = InAppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'إشعار 1',
            'message' => 'رسالة إشعار 1',
            'is_read' => false,
        ]);

        $notif2 = InAppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'إشعار 2',
            'message' => 'رسالة إشعار 2',
            'is_read' => false,
        ]);

        // Mark 1 as read
        $readRes = $this->actingAs($this->user)->postJson(route('notifications.mark-read', $notif1->id));
        $readRes->assertStatus(200);
        $this->assertDatabaseHas('in_app_notifications', ['id' => $notif1->id, 'is_read' => 1]);

        // Mark all as read
        $allRes = $this->actingAs($this->user)->postJson(route('notifications.mark-all-read'));
        $allRes->assertStatus(200);
        $this->assertDatabaseHas('in_app_notifications', ['id' => $notif2->id, 'is_read' => 1]);
    }

    // ==========================================
    // 4. Weekly Review Tests
    // ==========================================

    public function test_weekly_review_index_and_saving_answers()
    {
        $startDate = Carbon::now()->startOfWeek();

        $indexRes = $this->actingAs($this->user)->get(route('weekly-review.index', [
            'start' => $startDate->toDateString(),
        ]));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('المراجعة الأسبوعية الذكية');

        // Save answers
        $saveRes = $this->actingAs($this->user)->postJson(route('weekly-review.save-answers'), [
            'start_date' => $startDate->toDateString(),
            'q_best_accomplishment' => 'إتمام المرحلة الأولى بنجاح',
            'q_what_went_wrong' => 'تأجيل بعض المهام بسبب السهر',
            'q_habits_discipline' => 'التزام بنسبة 85%',
            'q_lessons_learned' => 'النوم المبكر مفتاح الإنتاجية',
            'q_next_week_focus' => 'إطلاق المرحلة الثانية ومراجعة الأهداف',
            'rating' => 4,
        ]);

        $saveRes->assertStatus(200);
        $review = WeeklyReview::where('user_id', $this->user->id)->first();
        $this->assertNotNull($review);
        $this->assertEquals(4, $review->rating);
    }

    public function test_weekly_review_ai_analysis_endpoint()
    {
        $startDate = Carbon::now()->startOfWeek();

        $response = $this->actingAs($this->user)->postJson(route('weekly-review.analyze'), [
            'start_date' => $startDate->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $review = WeeklyReview::where('user_id', $this->user->id)->first();
        $this->assertNotNull($review);
        $this->assertNotNull($review->ai_analysis);
    }
}
