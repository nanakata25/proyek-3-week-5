<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeSecond();
        User::factory()->create(['username' => 'budi']);
    }

    public function test_sixth_attempt_is_blocked_with_retry_information_even_with_a_valid_password(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->attemptLogin()
                ->assertRedirect('/login')
                ->assertSessionHasErrors(['username' => 'Username atau password salah.'])
                ->assertHeader('X-RateLimit-Limit', '5')
                ->assertHeader('X-RateLimit-Remaining', (string) (5 - $attempt));
        }

        $this->attemptLogin()->assertStatus(429)->assertHeader('Retry-After', '60');
        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia123'])
            ->assertStatus(429)
            ->assertHeader('X-RateLimit-Remaining', '0');

        $this->assertGuest();
        $this->get('/login')->assertOk();
    }

    public function test_username_case_and_surrounding_spaces_share_one_limit(): void
    {
        foreach (['budi', 'BUDI', ' Budi ', 'bUdI', '  budi  '] as $username) {
            $this->attemptLogin($username)->assertRedirect('/login');
        }

        $this->attemptLogin(' BUDI ')->assertStatus(429)->assertHeader('Retry-After', '60');
        $this->assertGuest();
    }

    public function test_another_username_on_the_same_ip_has_its_own_limit(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->attemptLogin()->assertRedirect('/login');
        }

        $this->attemptLogin()->assertStatus(429);
        $this->attemptLogin('siti')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username')
            ->assertHeader('X-RateLimit-Remaining', '4');
    }

    public function test_the_same_username_on_another_ip_has_its_own_limit(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->attemptLogin()->assertRedirect('/login');
        }

        $this->attemptLogin()->assertStatus(429);
        $this->attemptLogin('budi', '192.0.2.2')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username')
            ->assertHeader('X-RateLimit-Remaining', '4');
    }

    public function test_login_can_succeed_after_the_one_minute_limit_expires(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->attemptLogin()->assertRedirect('/login');
        }

        $this->travel(59)->seconds();
        $this->attemptLogin()->assertStatus(429)->assertHeader('Retry-After', '1');
        $this->assertGuest();

        $this->travel(1)->seconds();
        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard')
            ->assertHeader('X-RateLimit-Remaining', '4');
        $this->assertAuthenticatedAs(User::where('username', 'budi')->firstOrFail());
    }

    public function test_a_non_string_username_still_returns_a_validation_error(): void
    {
        $this->from('/login')->post('/login', ['username' => ['budi'], 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    private function attemptLogin(string $username = 'budi', string $ip = '192.0.2.1'): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->from('/login')
            ->post('/login', ['username' => $username, 'password' => 'salah']);
    }
}
