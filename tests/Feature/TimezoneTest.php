<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_application_timezone_is_asia_gaza(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $this->assertEquals('Asia/Gaza', date_default_timezone_get());
        $this->assertEquals('Asia/Gaza', config('app.timezone'));
    }

    public function test_timezone_cookie_dynamically_adjusts_timezone(): void
    {
        $response = $this->withUnencryptedCookie('user_timezone', 'Europe/London')
            ->get('/login');

        $response->assertStatus(200);
        $this->assertEquals('Europe/London', date_default_timezone_get());
        $this->assertEquals('Europe/London', config('app.timezone'));
    }

    public function test_timezone_sync_endpoint_saves_user_timezone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/timezone/sync', [
                'timezone' => 'Asia/Gaza',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'timezone' => 'Asia/Gaza',
            ]);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'timezone' => 'Asia/Gaza',
        ]);
    }

    public function test_invalid_timezone_config_or_cookie_safely_falls_back(): void
    {
        config(['app.timezone' => 'Asia/Gaza:8000']);

        $response = $this->withUnencryptedCookie('user_timezone', 'Invalid/Timezone:8000')
            ->get('/login');

        $response->assertStatus(200);
        $this->assertEquals('Asia/Gaza', date_default_timezone_get());
    }
}
