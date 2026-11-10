<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\SubSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillMasteryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_skills()
    {
        $response = $this->get('/skills');
        $response->assertRedirect('/login');

        $responsePost = $this->post('/skills', [
            'title' => 'مهارة جديدة',
        ]);
        $responsePost->assertRedirect('/login');
    }

    public function test_user_can_view_skills_index_page()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'صناعة المحتوى',
            'description' => 'إتقان صناعة المحتوى على يوتيوب',
            'goal' => '100 ألف مشترك',
            'icon' => 'fa-solid fa-video',
            'color' => '#0d9488',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/skills');

        $response->assertStatus(200);
        $response->assertSee('صناعة المحتوى');
        $response->assertSee('إتقان المهارات');
    }

    public function test_user_cannot_view_or_modify_another_users_skill()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $skill1 = Skill::create([
            'user_id' => $user1->id,
            'title' => 'مهارة مستخدم 1',
            'icon' => 'fa-solid fa-code',
            'color' => '#0d9488',
        ]);

        // User 2 attempts to view User 1's skill
        $response = $this->actingAs($user2)->get("/skills/{$skill1->id}");
        $response->assertStatus(403);

        // User 2 attempts to add subskill to User 1's skill
        $responsePost = $this->actingAs($user2)->postJson("/skills/{$skill1->id}/sub-skills", [
            'title' => 'اختراق غير مسموح',
        ]);
        $responsePost->assertStatus(403);
    }

    public function test_user_can_create_a_main_skill()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'البرمجة بلغة بايثون',
            'description' => 'تعلم البرمجة وهياكل البيانات',
            'goal' => 'بناء 5 مشاريع عملية',
            'icon' => 'fa-solid fa-code',
            'color' => '#2563eb',
            'start_date' => now()->toDateString(),
            'target_date' => now()->addMonths(3)->toDateString(),
            'status' => 'in_progress',
        ];

        $response = $this->actingAs($user)->post('/skills', $payload);

        $this->assertDatabaseHas('skills', [
            'user_id' => $user->id,
            'title' => 'البرمجة بلغة بايثون',
            'color' => '#2563eb',
        ]);

        $skill = Skill::where('title', 'البرمجة بلغة بايثون')->first();
        $response->assertRedirect(route('skills.show', $skill));
    }

    public function test_user_can_view_skill_details_show_page()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'التصميم الجرافيكي',
            'description' => 'تعلم الفوتوشوب والاليستريتور',
            'icon' => 'fa-solid fa-palette',
            'color' => '#ec4899',
            'status' => 'in_progress',
        ]);

        $sub1 = SubSkill::create([
            'skill_id' => $skill->id,
            'title' => 'أساسيات الألوان',
            'status' => 'mastered',
            'mastery_level' => 100,
            'order' => 1,
        ]);

        $sub2 = SubSkill::create([
            'skill_id' => $skill->id,
            'title' => 'برنامج الفوتوشوب',
            'status' => 'in_progress',
            'mastery_level' => 50,
            'order' => 2,
        ]);

        $response = $this->actingAs($user)->get("/skills/{$skill->id}");

        $response->assertStatus(200);
        $response->assertSee('التصميم الجرافيكي');
        $response->assertSee('أساسيات الألوان');
        $response->assertSee('برنامج الفوتوشوب');
    }

    public function test_user_can_update_main_skill()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'اسم قديم',
            'icon' => 'fa-solid fa-graduation-cap',
            'color' => '#0d9488',
        ]);

        $response = $this->actingAs($user)->put("/skills/{$skill->id}", [
            'title' => 'اسم معدل وجديد',
            'description' => 'وصف معدل',
            'goal' => 'هدف جديد',
            'icon' => 'fa-solid fa-brain',
            'color' => '#10b981',
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'title' => 'اسم معدل وجديد',
            'color' => '#10b981',
        ]);
    }

    public function test_user_can_add_sub_skill()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'صناعة المحتوى',
        ]);

        $response = $this->actingAs($user)->postJson("/skills/{$skill->id}/sub-skills", [
            'title' => 'كتابة السيناريو (Script Writing)',
            'description' => 'بناء نص متماسك ومثير للاهتمام',
            'status' => 'in_progress',
            'mastery_level' => 60,
            'resources' => "https://example.com/course\nكتاب السرد",
            'practical_tasks' => "كتابة سكريبت فيديو دقيقة واحدة\nكتابة هوك تشويقي",
            'notes' => 'التركيز على أول 5 ثوان',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'sub_skill' => [
                    'title' => 'كتابة السيناريو (Script Writing)',
                    'mastery_level' => 60,
                ],
            ]);

        $this->assertDatabaseHas('sub_skills', [
            'skill_id' => $skill->id,
            'title' => 'كتابة السيناريو (Script Writing)',
            'mastery_level' => 60,
        ]);
    }

    public function test_user_can_toggle_sub_skill_status_and_calculate_mastery()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'مهارة التقدم',
        ]);

        $sub = SubSkill::create([
            'skill_id' => $skill->id,
            'title' => 'المهارة 1',
            'status' => 'not_started',
            'mastery_level' => 0,
            'order' => 1,
        ]);

        // Toggle 1: not_started -> in_progress
        $res1 = $this->actingAs($user)->postJson("/skills/{$skill->id}/sub-skills/{$sub->id}/toggle");
        $res1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'in_progress',
                'mastery_level' => 50,
            ]);

        // Toggle 2: in_progress -> mastered
        $res2 = $this->actingAs($user)->postJson("/skills/{$skill->id}/sub-skills/{$sub->id}/toggle");
        $res2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'mastered',
                'mastery_level' => 100,
            ]);

        // Toggle 3: mastered -> not_started
        $res3 = $this->actingAs($user)->postJson("/skills/{$skill->id}/sub-skills/{$sub->id}/toggle");
        $res3->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'not_started',
                'mastery_level' => 0,
            ]);
    }

    public function test_user_can_update_mastery_level_slider()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'مهارة السلايدر',
        ]);

        $sub = SubSkill::create([
            'skill_id' => $skill->id,
            'title' => 'مهارة فرعية',
            'status' => 'not_started',
            'mastery_level' => 0,
            'order' => 1,
        ]);

        // Update to 85%
        $response = $this->actingAs($user)->postJson("/skills/{$skill->id}/sub-skills/{$sub->id}/mastery", [
            'mastery_level' => 85,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'mastery_level' => 85,
                'status' => 'in_progress',
                'overall_progress' => 85,
            ]);

        $this->assertDatabaseHas('sub_skills', [
            'id' => $sub->id,
            'mastery_level' => 85,
            'status' => 'in_progress',
        ]);
    }

    public function test_user_can_reorder_sub_skills()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'مهارة الترتيب',
        ]);

        $sub1 = SubSkill::create(['skill_id' => $skill->id, 'title' => 'أولاً', 'order' => 1]);
        $sub2 = SubSkill::create(['skill_id' => $skill->id, 'title' => 'ثانياً', 'order' => 2]);
        $sub3 = SubSkill::create(['skill_id' => $skill->id, 'title' => 'ثالثاً', 'order' => 3]);

        // Swap order: [sub3, sub1, sub2]
        $response = $this->actingAs($user)->postJson("/skills/{$skill->id}/sub-skills/reorder", [
            'order' => [$sub3->id, $sub1->id, $sub2->id],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals(1, $sub3->fresh()->order);
        $this->assertEquals(2, $sub1->fresh()->order);
        $this->assertEquals(3, $sub2->fresh()->order);
    }

    public function test_ai_breakdown_endpoint_generates_recommendations()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'صناعة المحتوى',
            'goal' => 'إطلاق قناة بودكاست وفيديو',
        ]);

        $response = $this->actingAs($user)->postJson("/skills/{$skill->id}/ai-breakdown", [
            'auto_save' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'sub_skills',
                'provider',
            ]);

        $this->assertNotEmpty($response->json('sub_skills'));
    }

    public function test_ai_breakdown_with_auto_save_persists_sub_skills()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'تعلم اللغة الإنجليزية',
        ]);

        $response = $this->actingAs($user)->postJson("/skills/{$skill->id}/ai-breakdown", [
            'auto_save' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'auto_saved' => true,
            ]);

        $this->assertGreaterThan(0, $skill->subSkills()->count());
    }

    public function test_ai_roadmap_endpoint_returns_roadmap()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'إدارة المشاريع الرشيقة Agile',
        ]);

        $response = $this->actingAs($user)->postJson("/skills/{$skill->id}/ai-roadmap");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'roadmap',
                'provider',
            ]);

        $this->assertNotEmpty($response->json('roadmap'));
    }

    public function test_user_can_delete_sub_skill()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'مهارة رئيسية',
        ]);

        $sub = SubSkill::create([
            'skill_id' => $skill->id,
            'title' => 'مهارة ستحذف',
        ]);

        $response = $this->actingAs($user)->deleteJson("/skills/{$skill->id}/sub-skills/{$sub->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('sub_skills', ['id' => $sub->id]);
    }

    public function test_deleting_main_skill_deletes_its_sub_skills()
    {
        $user = User::factory()->create();

        $skill = Skill::create([
            'user_id' => $user->id,
            'title' => 'مهارة ستحذف مع أطفالها',
        ]);

        $sub = SubSkill::create([
            'skill_id' => $skill->id,
            'title' => 'طفل للمهارة',
        ]);

        $response = $this->actingAs($user)->delete("/skills/{$skill->id}");

        $response->assertRedirect('/skills');
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
        $this->assertDatabaseMissing('sub_skills', ['id' => $sub->id]);
    }
}
