<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_limited_to_five_attempts_per_minute(): void
    {
        $payload = ['email' => 'nobody@example.com', 'password' => 'wrong-password'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', $payload)->assertUnprocessable();
        }

        $this->postJson('/api/auth/login', $payload)
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    public function test_authenticated_routes_are_limited_to_sixty_requests_per_minute(): void
    {
        $token = User::factory()->create()->createToken('auth-token', ['auth:user'])->plainTextToken;

        for ($i = 0; $i < 60; $i++) {
            $this->withToken($token)->getJson('/api/projects')->assertOk();
        }

        $this->withToken($token)
            ->getJson('/api/projects')
            ->assertTooManyRequests()
            ->assertJsonPath('success', false);
    }

    public function test_api_limit_is_tracked_per_user(): void
    {
        $firstToken = User::factory()->create()->createToken('auth-token', ['auth:user'])->plainTextToken;
        $secondToken = User::factory()->create()->createToken('auth-token', ['auth:user'])->plainTextToken;

        for ($i = 0; $i < 60; $i++) {
            $this->withToken($firstToken)->getJson('/api/projects');
        }

        $this->app['auth']->forgetGuards();

        $this->withToken($secondToken)
            ->getJson('/api/projects')
            ->assertOk();
    }
}
