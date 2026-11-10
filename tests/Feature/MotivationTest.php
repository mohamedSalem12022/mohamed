<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Motivation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_motivations()
    {
        $response = $this->getJson('/motivations');
        $response->assertStatus(401);

        $responsePost = $this->postJson('/motivations', [
            'title' => 'عنوان',
            'quote' => 'نص تحفيزي',
        ]);
        $responsePost->assertStatus(401);
    }

    public function test_user_can_list_their_motivations_with_pinned_first()
    {
        $user = User::factory()->create();

        $m1 = Motivation::create([
            'user_id' => $user->id,
            'title' => 'عادي',
            'quote' => 'عبارة عادية',
            'is_pinned' => false,
        ]);

        $m2 = Motivation::create([
            'user_id' => $user->id,
            'title' => 'مثبت',
            'quote' => 'عبارة مثبتة',
            'is_pinned' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/motivations');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 2,
            ]);

        // Pinned must be first
        $this->assertEquals($m2->id, $response->json('motivations.0.id'));
        $this->assertEquals($m1->id, $response->json('motivations.1.id'));
    }

    public function test_user_can_create_a_motivation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/motivations', [
            'title' => 'الخطوة الأولى',
            'quote' => 'ابدأ الآن، حتى لو بخطوة صغيرة.',
            'category' => 'انضباط',
            'icon' => 'fa-play',
            'is_pinned' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'تمت إضافة المحفّز بنجاح ✓',
                'motivation' => [
                    'title' => 'الخطوة الأولى',
                    'quote' => 'ابدأ الآن، حتى لو بخطوة صغيرة.',
                    'category' => 'انضباط',
                    'icon' => 'fa-play',
                    'is_pinned' => true,
                ]
            ]);

        $this->assertDatabaseHas('motivations', [
            'user_id' => $user->id,
            'title' => 'الخطوة الأولى',
            'is_pinned' => true,
        ]);
    }

    public function test_validation_fails_when_fields_are_missing()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/motivations', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'quote']);
    }

    public function test_user_can_update_their_motivation()
    {
        $user = User::factory()->create();

        $m = Motivation::create([
            'user_id' => $user->id,
            'title' => 'قديم',
            'quote' => 'نص قديم',
            'category' => 'عام',
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($user)->putJson("/motivations/{$m->id}", [
            'title' => 'جديد ومحدث',
            'quote' => 'نص محدث ومطور',
            'category' => 'تطوير',
            'is_pinned' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم تعديل المحفّز بنجاح ✓',
            ]);

        $m->refresh();
        $this->assertEquals('جديد ومحدث', $m->title);
        $this->assertEquals('نص محدث ومطور', $m->quote);
        $this->assertTrue($m->is_pinned);
    }

    public function test_user_cannot_update_another_users_motivation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $m = Motivation::create([
            'user_id' => $user1->id,
            'title' => 'محفز يوزر 1',
            'quote' => 'خاص بيوزر 1',
        ]);

        $response = $this->actingAs($user2)->putJson("/motivations/{$m->id}", [
            'title' => 'محاولة اختراق',
            'quote' => 'تعديل غير مصرح',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_motivation()
    {
        $user = User::factory()->create();

        $m = Motivation::create([
            'user_id' => $user->id,
            'title' => 'للحذف',
            'quote' => 'سيتم حذفه',
        ]);

        $response = $this->actingAs($user)->deleteJson("/motivations/{$m->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم حذف المحفّز بنجاح ✓',
            ]);

        $this->assertDatabaseMissing('motivations', ['id' => $m->id]);
    }

    public function test_user_cannot_delete_another_users_motivation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $m = Motivation::create([
            'user_id' => $user1->id,
            'title' => 'محفز يوزر 1',
            'quote' => 'لا تحذفني',
        ]);

        $response = $this->actingAs($user2)->deleteJson("/motivations/{$m->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('motivations', ['id' => $m->id]);
    }

    public function test_user_can_toggle_pin()
    {
        $user = User::factory()->create();

        $m = Motivation::create([
            'user_id' => $user->id,
            'title' => 'تثبيت تجريبي',
            'quote' => 'تجربة التثبيت',
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($user)->postJson("/motivations/{$m->id}/toggle-pin");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_pinned' => true,
            ]);

        $m->refresh();
        $this->assertTrue($m->is_pinned);

        // Toggle back
        $response2 = $this->actingAs($user)->postJson("/motivations/{$m->id}/toggle-pin");
        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_pinned' => false,
            ]);

        $m->refresh();
        $this->assertFalse($m->is_pinned);
    }

    public function test_user_can_get_random_motivation()
    {
        $user = User::factory()->create();

        Motivation::create([
            'user_id' => $user->id,
            'title' => 'محفز 1',
            'quote' => 'عبارة 1',
        ]);

        $response = $this->actingAs($user)->getJson('/motivations/random');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
        $this->assertNotNull($response->json('motivation.title'));
    }

    public function test_user_can_seed_default_motivations()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/motivations/seed-defaults');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 5,
            ]);

        $this->assertEquals(5, $user->motivations()->count());
    }

    public function test_user_can_generate_motivation_via_ai()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/motivations/generate-ai', [
            'topic' => 'انضباط وتركيز',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotEmpty($response->json('motivation.title'));
        $this->assertNotEmpty($response->json('motivation.quote'));
    }
}
