<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_passwords_are_hashes_and_can_be_verified(): void
    {
        $this->seed();
        $this->assertDatabaseCount('users', 2);
        foreach (User::all() as $user) {
            $this->assertNotSame('rahasia123', $user->getRawOriginal('password'));
            $this->assertTrue(Hash::check('rahasia123', $user->password));
        }
        $this->assertNotSame(User::first()->password, User::latest('id')->first()->password);
    }

    public function test_guest_dashboard_access_redirects_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_valid_login_authenticates_and_rotates_session_id(): void
    {
        $user = User::factory()->create(['username' => 'budi', 'nama_lengkap' => 'Budi Santoso']);
        $this->get('/login')->assertOk();
        $oldSessionId = session()->getId();

        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->get('/dashboard')->assertOk()->assertSee('Budi Santoso')->assertSee('budi');
    }

    public function test_incorrect_password_and_unknown_user_share_one_generic_error(): void
    {
        User::factory()->create(['username' => 'budi']);

        foreach (['budi', 'tidak-ada'] as $username) {
            $this->from('/login')->post('/login', ['username' => $username, 'password' => 'salah'])
                ->assertRedirect('/login')
                ->assertSessionHasErrors(['username' => 'Username atau password salah.'])
                ->assertSessionHas('_old_input.username', $username)
                ->assertSessionMissing('_old_input.password');
            $this->assertGuest();
        }
    }

    public function test_input_validation_requires_both_credentials(): void
    {
        $this->from('/login')->post('/login', [])
            ->assertRedirect('/login')->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_open_login_again(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard');
    }

    public function test_logout_invalidates_auth_and_session_and_rotates_csrf_token(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['private_marker' => 'old session'])->get('/dashboard');
        $oldSessionId = session()->getId();
        $oldToken = session()->token();

        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('private_marker');

        $this->assertGuest();
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_logout_is_not_a_get_endpoint(): void
    {
        $this->actingAs(User::factory()->create())->get('/logout')->assertStatus(405);
        $this->assertAuthenticated();
    }

    public function test_forms_include_csrf_fields(): void
    {
        $this->get('/login')->assertSee('name="_token"', false);
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertSee('name="_token"', false);
    }
}
