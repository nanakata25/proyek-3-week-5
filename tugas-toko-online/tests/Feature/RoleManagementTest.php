<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RoleManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        foreach (['RADMIN' => 'admin', 'RADMIN2' => 'admin', 'RCUSTOMER' => 'customer', 'RSTAFF' => 'staff'] as $id => $role) {
            $user = User::create([
                'id_user' => $id,
                'nama_lengkap' => 'Demo '.$id,
                'username' => strtolower($id),
                'email' => strtolower($id).'@example.test',
                'password' => 'password-test-role',
                'no_hp' => '0800000000',
                'alamat' => 'Alamat dummy pengujian Modul 5',
            ]);
            // Fixtures use trusted model assignment, never public request mass assignment.
            $user->role = $role;
            $user->save();
        }

        Log::spy();
    }

    private function user(string $id): User
    {
        return User::findOrFail($id);
    }

    private function roleUrl(string $id = 'RCUSTOMER'): string
    {
        return '/admin/users/'.$id.'/role';
    }

    public function test_new_users_default_to_customer_and_role_is_not_mass_assignable(): void
    {
        $user = User::create([
            'id_user' => 'RNEW',
            'nama_lengkap' => 'User Baru',
            'username' => 'rnew',
            'email' => 'rnew@example.test',
            'password' => 'password-test-role',
            'no_hp' => '0800000001',
            'alamat' => 'Alamat dummy untuk user baru',
        ]);

        $this->assertSame('customer', $user->fresh()->role);
        $this->assertFalse($user->isFillable('role'));
        Log::shouldNotHaveReceived('info');
    }

    public function test_gate_abilities_match_the_access_matrix(): void
    {
        foreach ([
            'RCUSTOMER' => ['manage-roles' => false, 'manage-products' => false, 'adjust-stock' => false, 'use-cart' => true],
            'RSTAFF' => ['manage-roles' => false, 'manage-products' => false, 'adjust-stock' => true, 'use-cart' => false],
            'RADMIN' => ['manage-roles' => true, 'manage-products' => true, 'adjust-stock' => true, 'use-cart' => false],
        ] as $id => $abilities) {
            foreach ($abilities as $ability => $allowed) {
                $this->assertSame($allowed, Gate::forUser($this->user($id))->allows($ability), $id.' '.$ability);
            }
        }
    }

    public function test_admin_changes_customer_to_staff_and_observer_records_one_safe_event(): void
    {
        $this->actingAs($this->user('RADMIN'))
            ->from('/admin/users')
            ->patch($this->roleUrl(), ['role' => 'staff'])
            ->assertRedirect('/admin/users')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id_user' => 'RCUSTOMER', 'role' => 'staff']);
        Log::shouldHaveReceived('info')->once()->with('user.role.changed', [
            'target_user_id' => 'RCUSTOMER',
            'old_role' => 'customer',
            'new_role' => 'staff',
            'actor_id' => 'RADMIN',
        ]);
    }

    public function test_admin_can_change_staff_back_to_customer(): void
    {
        $this->actingAs($this->user('RADMIN'))
            ->patch($this->roleUrl('RSTAFF'), ['role' => 'customer'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id_user' => 'RSTAFF', 'role' => 'customer']);
        Log::shouldHaveReceived('info')->once()->with('user.role.changed', [
            'target_user_id' => 'RSTAFF', 'old_role' => 'staff', 'new_role' => 'customer', 'actor_id' => 'RADMIN',
        ]);
    }

    public function test_customer_cannot_patch_the_admin_route_even_with_a_valid_payload(): void
    {
        $before = $this->user('RSTAFF')->getAttributes();

        $this->actingAs($this->user('RCUSTOMER'))
            ->patchJson($this->roleUrl('RSTAFF'), ['role' => 'customer'])
            ->assertForbidden();

        $this->assertSame($before, $this->user('RSTAFF')->getAttributes());
        Log::shouldNotHaveReceived('info');
    }

    public function test_staff_cannot_patch_the_admin_route(): void
    {
        $this->actingAs($this->user('RSTAFF'))
            ->patchJson($this->roleUrl(), ['role' => 'staff'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id_user' => 'RCUSTOMER', 'role' => 'customer']);
        Log::shouldNotHaveReceived('info');
    }

    public function test_admin_cannot_change_own_role_or_another_admin_role(): void
    {
        $this->actingAs($this->user('RADMIN'));

        foreach (['RADMIN', 'RADMIN2'] as $id) {
            $this->patchJson($this->roleUrl($id), ['role' => 'staff'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('role');
            $this->assertDatabaseHas('users', ['id_user' => $id, 'role' => 'admin']);
        }

        Log::shouldNotHaveReceived('info');
    }

    public function test_invalid_or_missing_role_is_rejected_before_the_service_saves(): void
    {
        $this->actingAs($this->user('RADMIN'));

        foreach ([['role' => 'admin'], ['role' => 'owner'], ['role' => ['staff']], [], ['role' => null]] as $payload) {
            $this->patchJson($this->roleUrl(), $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('role');
        }

        $this->assertDatabaseHas('users', ['id_user' => 'RCUSTOMER', 'role' => 'customer']);
        Log::shouldNotHaveReceived('info');
    }

    public function test_same_role_does_not_save_the_target_or_emit_an_observer_log(): void
    {
        $saving = 0;
        User::saving(function (User $user) use (&$saving): void {
            if ($user->getKey() === 'RSTAFF') {
                $saving++;
            }
        });
        $before = $this->user('RSTAFF')->getAttributes();

        $this->actingAs($this->user('RADMIN'))
            ->patch($this->roleUrl('RSTAFF'), ['role' => 'staff'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $saving);
        $this->assertSame($before, $this->user('RSTAFF')->getAttributes());
        Log::shouldNotHaveReceived('info');
    }

    public function test_service_returns_whether_a_role_actually_changed(): void
    {
        $service = $this->app->make(RoleManagementService::class);
        $this->actingAs($this->user('RADMIN'));

        $this->assertTrue($service->changeRole($this->user('RADMIN'), $this->user('RCUSTOMER'), 'staff'));
        $this->assertFalse($service->changeRole($this->user('RADMIN'), $this->user('RCUSTOMER'), 'staff'));
        Log::shouldHaveReceived('info')->once();
    }

    public function test_bulk_update_bypasses_model_observer_but_instance_save_emits_it(): void
    {
        User::whereKey('RCUSTOMER')->update(['role' => 'staff']);
        $this->assertDatabaseHas('users', ['id_user' => 'RCUSTOMER', 'role' => 'staff']);
        Log::shouldNotHaveReceived('info');

        $target = $this->user('RCUSTOMER');
        $target->role = 'customer';
        $target->save();

        Log::shouldHaveReceived('info')->once()->with('user.role.changed', [
            'target_user_id' => 'RCUSTOMER', 'old_role' => 'staff', 'new_role' => 'customer', 'actor_id' => null,
        ]);
    }

    public function test_editing_a_non_role_field_does_not_emit_role_change_log(): void
    {
        $target = $this->user('RCUSTOMER');
        $target->nama_lengkap = 'Nama dummy diperbarui';
        $target->save();

        $this->assertDatabaseHas('users', ['id_user' => 'RCUSTOMER', 'nama_lengkap' => 'Nama dummy diperbarui']);
        Log::shouldNotHaveReceived('info');
    }

    public function test_guests_are_redirected_for_html_and_unauthorized_for_json(): void
    {
        $this->patch($this->roleUrl(), ['role' => 'staff'])->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
        $this->postJson('/cart/add', ['cart' => [], 'product_id' => 1])->assertUnauthorized();

        $this->assertDatabaseHas('users', ['id_user' => 'RCUSTOMER', 'role' => 'customer']);
        Log::shouldNotHaveReceived('info');
    }

    public function test_role_management_page_is_admin_only(): void
    {
        $this->actingAs($this->user('RCUSTOMER'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->user('RSTAFF'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->user('RADMIN'))->get('/admin/users')->assertOk();
    }

    public function test_staff_and_admin_cannot_use_either_stateless_cart_operation(): void
    {
        foreach (['RSTAFF', 'RADMIN'] as $id) {
            $this->actingAs($this->user($id));
            $this->postJson('/cart/add', ['cart' => [], 'product_id' => 1])->assertForbidden();
            $this->postJson('/cart/update', ['cart' => ['1' => 2]])->assertForbidden();
        }

        $this->assertDatabaseCount('cart_items', 0);
        Log::shouldNotHaveReceived('info');
    }

    public function test_customer_cart_uses_request_payload_and_does_not_store_cart_in_session(): void
    {
        $this->actingAs($this->user('RCUSTOMER'));

        $this->postJson('/cart/add', ['cart' => ['1' => 2], 'product_id' => 2])
            ->assertOk()->assertJsonPath('total', 13000)
            ->assertJsonPath('items.0.quantity', 2)->assertJsonPath('items.1.quantity', 1);
        $this->postJson('/cart/update', ['cart' => ['1' => 2, '2' => 1]])
            ->assertOk()->assertJsonPath('total', 13000);
        $this->get('/stateless')->assertOk()->assertSee('Keranjang kosong.')->assertSee('Total: Rp0');
        $this->assertFalse(session()->has('cart'));
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_latest_role_is_used_for_a_subsequent_authenticated_request(): void
    {
        $this->actingAs($this->user('RCUSTOMER'))
            ->postJson('/cart/add', ['cart' => [], 'product_id' => 1])->assertOk();

        $this->actingAs($this->user('RADMIN'))
            ->patch($this->roleUrl(), ['role' => 'staff'])->assertRedirect();

        // Resolve the account again, as the session user provider does on a new HTTP request.
        $this->actingAs($this->user('RCUSTOMER'))
            ->postJson('/cart/add', ['cart' => [], 'product_id' => 1])->assertForbidden();
        $this->assertSame('staff', $this->user('RCUSTOMER')->role);
    }
}
