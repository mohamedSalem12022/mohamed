<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSettings;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('test_key');

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@molife.test',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
        UserSettings::create(['user_id' => $this->admin->id]);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@molife.test',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
        UserSettings::create(['user_id' => $this->regularUser->id]);
    }

    /**
     * Test 1: Visitor clicking register is redirected to WhatsApp when number is configured.
     */
    public function test_visitor_clicking_register_is_redirected_to_whatsapp_when_number_is_set(): void
    {
        config(['services.whatsapp.registration_number' => '972568080358']);
        config(['services.whatsapp.registration_message' => 'مرحبًا، أريد إنشاء حساب جديد في MO Life OS']);

        // GET /register redirects to WhatsApp chat with the exact configured number and message
        $response = $this->get(route('register'));
        $response->assertRedirect();
        $this->assertStringContainsString('https://wa.me/972568080358', $response->headers->get('Location'));

        // Public POST /register also redirects to WhatsApp chat
        $postResponse = $this->post(route('register.post'), [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'secret1234',
        ]);
        $postResponse->assertRedirect();
        $this->assertStringContainsString('https://wa.me/972568080358', $postResponse->headers->get('Location'));

        // The Login page displays the direct WhatsApp button with the correct number
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('https://wa.me/972568080358', false);
    }

    /**
     * Test 2: Visitor sees WhatsApp contact page when number is not yet set in .env.
     */
    public function test_visitor_sees_whatsapp_notice_when_number_is_not_set(): void
    {
        config(['services.whatsapp.registration_number' => '']);

        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('طلب إنشاء حساب جديد');
        $response->assertSee('واتساب');

        // Public direct POST is blocked
        $postResponse = $this->post(route('register.post'), [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'secret1234',
        ]);
        $postResponse->assertStatus(403);
    }

    /**
     * Test 3: Admin can create new user from inside Admin Panel with defaults seeded.
     */
    public function test_admin_can_create_new_user_from_admin_panel(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Created By Admin',
            'email' => 'createdbyadmin@example.com',
            'password' => 'securePass123!',
            'is_admin' => 0,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $newUser = User::where('email', 'createdbyadmin@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Created By Admin', $newUser->name);
        $this->assertTrue(Hash::check('securePass123!', $newUser->password));
        $this->assertFalse($newUser->isAdmin());

        // Assert defaults were seeded for the newly created user
        $this->assertNotNull($newUser->settings);
        $this->assertGreaterThan(0, $newUser->categories()->count());
        $this->assertGreaterThan(0, $newUser->habits()->count());
        $this->assertGreaterThan(0, $newUser->financialCategories()->count());
    }

    /**
     * Test 4: Regular user cannot create users via Admin store route.
     */
    public function test_non_admin_cannot_create_user_via_admin_store(): void
    {
        $response = $this->actingAs($this->regularUser)->post(route('admin.users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauth@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'unauth@example.com']);
    }

    /**
     * Test 5: Admin user creation validation fails for duplicate email and short password.
     */
    public function test_admin_user_creation_validation_fails_for_duplicate_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Duplicate Test',
            'email' => 'user@molife.test', // already exists
            'password' => '123', // less than 8 chars
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Test 6: Login rate limiting blocks brute force after 5 failed attempts.
     */
    public function test_login_rate_limiting_blocks_after_5_failed_attempts(): void
    {
        $email = 'victim@example.com';

        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post(route('login.post'), [
                'email' => $email,
                'password' => 'wrong-pass',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be blocked by RateLimiter
        $response6 = $this->post(route('login.post'), [
            'email' => $email,
            'password' => 'wrong-pass',
        ]);

        $response6->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertStringContainsString('تم تجاوز عدد محاولات الدخول', $errorMessage);
    }

    /**
     * Test 7: Guest and regular users cannot access Admin User Management.
     */
    public function test_non_admin_cannot_access_admin_user_management(): void
    {
        // Guest redirected to login
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));

        // Regular user gets 403 Forbidden
        $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    /**
     * Test 8: Admin can access User Management and search by name or email.
     */
    public function test_admin_can_access_user_management_and_search(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('إدارة المستخدمين');
        $response->assertSee('Admin User');
        $response->assertSee('Regular User');

        // Search for specific user
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'user@molife.test']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('user@molife.test');
    }

    /**
     * Test 9: Admin cannot delete self.
     */
    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Test 10: Admin cannot delete the last remaining admin.
     */
    public function test_admin_cannot_delete_the_last_admin(): void
    {
        // Admin is the only admin
        $this->assertEquals(1, User::where('is_admin', true)->count());

        // Create temporary second admin who is deleted first
        $secondAdmin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $secondAdmin));
        $this->assertEquals(1, User::where('is_admin', true)->count());

        // Attempting to delete the last remaining admin fails
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Test 11: Admin can delete regular user and their data cascades safely.
     */
    public function test_admin_can_delete_regular_user_with_cascading_data(): void
    {
        // Create user data
        $habit = Habit::create([
            'user_id' => $this->regularUser->id,
            'name' => 'Morning Run',
            'target_days_per_week' => 5,
        ]);
        HabitLog::create([
            'user_id' => $this->regularUser->id,
            'habit_id' => $habit->id,
            'log_date' => now()->toDateString(),
            'status' => 'completed',
        ]);
        $task = Task::create([
            'user_id' => $this->regularUser->id,
            'title' => 'Read Clean Code',
            'status' => 'pending',
        ]);
        Subtask::create([
            'task_id' => $task->id,
            'title' => 'Chapter 1',
            'is_completed' => false,
        ]);

        $userId = $this->regularUser->id;

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->regularUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify user and data are deleted
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('habits', ['user_id' => $userId]);
        $this->assertDatabaseMissing('habit_logs', ['user_id' => $userId]);
        $this->assertDatabaseMissing('tasks', ['user_id' => $userId]);
        $this->assertDatabaseMissing('subtasks', ['title' => 'Chapter 1']);
        $this->assertDatabaseMissing('user_settings', ['user_id' => $userId]);
    }

    /**
     * Test 12: Admin can toggle user roles (promote / demote).
     */
    public function test_admin_can_toggle_user_role(): void
    {
        $this->assertFalse($this->regularUser->isAdmin());

        // Promote to admin
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-role', $this->regularUser));
        $response->assertSessionHas('success');
        $this->assertTrue($this->regularUser->fresh()->isAdmin());

        // Demote back to regular user
        $response2 = $this->actingAs($this->admin)->post(route('admin.users.toggle-role', $this->regularUser));
        $response2->assertSessionHas('success');
        $this->assertFalse($this->regularUser->fresh()->isAdmin());
    }

    /**
     * Test 13: Strict data isolation - User cannot access another user's resources.
     */
    public function test_data_isolation_between_users(): void
    {
        $userA = User::factory()->create();
        UserSettings::create(['user_id' => $userA->id]);
        $taskA = Task::create(['user_id' => $userA->id, 'title' => 'Private Task A', 'status' => 'pending']);

        $userB = User::factory()->create();
        UserSettings::create(['user_id' => $userB->id]);

        // User B tries to toggle Task A
        $response = $this->actingAs($userB)->post(route('tasks.toggle', $taskA));
        $response->assertStatus(403);

        // User B tries to delete Task A
        $deleteResponse = $this->actingAs($userB)->delete(route('tasks.destroy', $taskA));
        $deleteResponse->assertStatus(403);
    }
}
