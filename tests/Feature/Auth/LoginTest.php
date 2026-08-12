<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_validation_requires_username_and_password(): void
    {
        $response = $this->post('/login', [
            'username' => '',
            'password' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['username', 'password']);
    }

    public function test_users_are_rate_limited_after_5_failed_attempts(): void
    {
        User::factory()->create([
            'username' => 'targetuser',
            'password' => bcrypt('password123'),
        ]);

        // Fire 5 bad requests to hit the limit
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'targetuser',
                'password' => 'wrong-password',
            ]);
        }

        // The 6th request should trigger the lockout
        $response = $this->post('/login', [
            'username' => 'targetuser',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('seconds', session('errors')->first('username'));
    }

    public function test_rate_limiter_clears_upon_successful_authentication(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        // 4 failed attempts (one away from lockout)
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', [
                'username' => 'testuser',
                'password' => 'wrong-password',
            ]);
        }

        // 5th attempt is successful
        $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        // Verify the rate limiter is wiped clean by checking the underlying throttle key
        $request = request();
        $request->merge(['username' => 'testuser']);
        $throttleKey = Str::transliterate(Str::lower('testuser').'|'.$request->ip());
        
        $this->assertEquals(0, RateLimiter::attempts($throttleKey));
    }

    public function test_authenticated_users_can_logout_securely(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}