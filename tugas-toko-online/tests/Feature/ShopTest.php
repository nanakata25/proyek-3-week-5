<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function budi(): User
    {
        return User::findOrFail('U001');
    }

    private function checkoutPayload(): array
    {
        $this->get('/keranjang')->assertOk();

        return [
            'alamat_pengiriman' => 'Jalan Belajar No. 12, Bandung',
            'checkout_token' => session('checkout_token'),
        ];
    }

    private function cartRow(string $id, int $jumlah, string $user = 'U001'): void
    {
        DB::table('cart_items')->insert(['id_user' => $user, 'id_barang' => $id, 'jumlah' => $jumlah]);
    }

    public function test_guest_can_see_ten_products_images_prices_and_stock(): void
    {
        $response = $this->get('/')->assertOk()->assertSee('Buku Tulis')->assertSee('Rp5.000')->assertSee('Stok habis');
        $this->assertDatabaseCount('products', 10);
        foreach (Product::all() as $product) {
            $response->assertSee($product->nama_barang)->assertSee('images/'.$product->gambar);
            $this->assertFileExists(public_path('images/'.$product->gambar));
        }
    }

    public function test_guest_cannot_change_cart_checkout_or_read_orders(): void
    {
        $this->post('/keranjang/B001')->assertRedirect('/login');
        $this->patch('/keranjang/B001', ['jumlah' => 2])->assertRedirect('/login');
        $this->delete('/keranjang/B001')->assertRedirect('/login');
        $this->post('/checkout', [])->assertRedirect('/login');
        $this->get('/keranjang')->assertRedirect('/login');
        $this->get('/pesanan')->assertRedirect('/login');
        $this->get('/pesanan/ORDTEST')->assertRedirect('/login');
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_login_uses_hashed_password_and_rotates_session_id(): void
    {
        $user = $this->budi();
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->get('/login')->assertOk();
        $sessionId = session()->getId();
        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->get('/login')->assertRedirect('/');
    }

    public function test_bad_credentials_have_generic_error_and_password_is_not_flashed(): void
    {
        foreach (['budi', 'tidak-terdaftar'] as $username) {
            $this->from('/login')->post('/login', ['username' => $username, 'password' => 'salah'])
                ->assertRedirect('/login')->assertSessionHasErrors(['username' => 'Username atau password salah.'])
                ->assertSessionMissing('_old_input.password');
            $this->assertGuest();
        }
    }

    public function test_cart_add_update_zero_and_remove_keep_stock_unchanged(): void
    {
        $this->actingAs($this->budi());
        $this->post('/keranjang/B001')->assertRedirect();
        $this->post('/keranjang/B001')->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U001', 'id_barang' => 'B001', 'jumlah' => 2]);
        $this->patch('/keranjang/B001', ['jumlah' => 4])->assertRedirect();
        $this->get('/keranjang')->assertOk()->assertSee('Rp20.000');
        $this->patch('/keranjang/B001', ['jumlah' => 0])->assertRedirect();
        $this->assertDatabaseCount('cart_items', 0);
        $this->post('/keranjang/B002')->assertRedirect();
        $this->delete('/keranjang/B002')->assertRedirect();
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 40]);
        $this->assertDatabaseHas('products', ['id_barang' => 'B002', 'stok' => 60]);
    }

    public function test_sold_out_overstock_negative_fractional_and_missing_products_are_rejected(): void
    {
        $this->actingAs($this->budi());
        $this->from('/')->post('/keranjang/B010')->assertSessionHasErrors('jumlah');
        foreach ([41, -1, 1.5, 'tidak-valid'] as $jumlah) {
            $this->from('/keranjang')->patch('/keranjang/B001', ['jumlah' => $jumlah])->assertSessionHasErrors('jumlah');
        }
        $this->post('/keranjang/TIDAKADA')->assertNotFound();
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_repeated_add_cannot_exceed_current_stock(): void
    {
        $this->cartRow('B001', 40);
        $this->actingAs($this->budi())->from('/')->post('/keranjang/B001')->assertSessionHasErrors('jumlah');
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U001', 'id_barang' => 'B001', 'jumlah' => 40]);
    }

    public function test_checkout_uses_server_prices_creates_details_reduces_stock_and_clears_only_owner_cart(): void
    {
        $this->cartRow('B001', 2);
        $this->cartRow('B002', 3);
        $this->cartRow('B001', 4, 'U002');
        $this->actingAs($this->budi());
        $payload = $this->checkoutPayload();
        $response = $this->post('/checkout', $payload + ['total_harga' => 1, 'harga' => 1, 'jumlah' => 1000, 'id_user' => 'U002']);
        $order = DB::table('orders')->sole();

        $response->assertRedirect('/pesanan/'.$order->id_order);
        $this->assertSame(15, strlen($order->id_order));
        $this->assertSame('U001', $order->id_user);
        $this->assertEquals(19000, $order->total_harga);
        $this->assertSame($payload['alamat_pengiriman'], $order->alamat_pengiriman);
        $this->assertDatabaseCount('order_details', 2);
        $this->assertDatabaseHas('order_details', ['id_order' => $order->id_order, 'id_barang' => 'B001', 'jumlah_beli' => 2, 'harga_satuan' => 5000]);
        $this->assertDatabaseHas('order_details', ['id_order' => $order->id_order, 'id_barang' => 'B002', 'jumlah_beli' => 3, 'harga_satuan' => 3000]);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 38]);
        $this->assertDatabaseHas('products', ['id_barang' => 'B002', 'stok' => 57]);
        $this->assertDatabaseMissing('cart_items', ['id_user' => 'U001']);
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U002', 'id_barang' => 'B001', 'jumlah' => 4]);
        $this->get('/keranjang')->assertOk()->assertSee('Keranjangmu masih kosong');
        $this->get('/pesanan')->assertOk()->assertSee($order->id_order)->assertSee('Rp19.000');
        $this->get('/pesanan/'.$order->id_order)->assertOk()->assertSee('Buku Tulis')->assertSee('Rp19.000');
    }

    public function test_empty_cart_cannot_create_order(): void
    {
        $this->actingAs($this->budi());
        $payload = $this->checkoutPayload();
        $this->from('/keranjang')->post('/checkout', $payload)->assertRedirect('/keranjang')->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_details', 0);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 40]);
    }

    public function test_checkout_requires_its_session_token_and_valid_address(): void
    {
        $this->cartRow('B001', 1);
        $this->actingAs($this->budi());
        $payload = $this->checkoutPayload();
        $this->post('/checkout', array_replace($payload, ['checkout_token' => (string) Str::uuid()]))->assertStatus(419);
        $this->from('/keranjang')->post('/checkout', array_replace($payload, ['alamat_pengiriman' => 'x']))
            ->assertSessionHasErrors('alamat_pengiriman');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U001', 'id_barang' => 'B001', 'jumlah' => 1]);
    }

    public function test_repeated_checkout_is_idempotent_even_if_a_new_cart_has_been_started(): void
    {
        $this->cartRow('B001', 2);
        $this->actingAs($this->budi());
        $payload = $this->checkoutPayload();
        $this->post('/checkout', $payload)->assertRedirect();
        $order = DB::table('orders')->sole();
        $this->post('/keranjang/B002')->assertRedirect();
        $this->post('/checkout', $payload)->assertRedirect('/pesanan/'.$order->id_order);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_details', 1);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 38]);
        $this->assertDatabaseHas('products', ['id_barang' => 'B002', 'stok' => 60]);
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U001', 'id_barang' => 'B002', 'jumlah' => 1]);
    }

    public function test_price_snapshot_remains_after_catalog_price_changes(): void
    {
        $this->cartRow('B001', 2);
        $this->actingAs($this->budi());
        $this->post('/checkout', $this->checkoutPayload())->assertRedirect();
        $order = DB::table('orders')->sole();
        Product::whereKey('B001')->update(['harga' => 999999]);
        $this->get('/pesanan/'.$order->id_order)->assertOk()->assertSee('Rp5.000')->assertSee('Rp10.000')->assertDontSee('Rp999.999');
        $this->assertDatabaseHas('order_details', ['id_order' => $order->id_order, 'harga_satuan' => 5000]);
        $this->assertEquals(10000, DB::table('orders')->value('total_harga'));
    }

    public function test_stale_stock_rolls_back_the_whole_checkout_and_preserves_cart(): void
    {
        $this->cartRow('B001', 2);
        $this->cartRow('B002', 3);
        $this->actingAs($this->budi());
        $payload = $this->checkoutPayload();
        Product::whereKey('B002')->update(['stok' => 1]);
        $this->from('/keranjang')->post('/checkout', $payload)->assertRedirect('/keranjang')->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_details', 0);
        $this->assertDatabaseCount('cart_items', 2);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 40]);
        $this->assertDatabaseHas('products', ['id_barang' => 'B002', 'stok' => 1]);
    }

    public function test_database_failure_mid_checkout_rolls_back_order_details_stock_and_cart(): void
    {
        $this->cartRow('B001', 2);
        $this->cartRow('B002', 3);
        $detailsInserted = 0;
        DB::listen(function (QueryExecuted $query) use (&$detailsInserted): void {
            if (str_starts_with(strtolower($query->sql), 'insert into') && str_contains($query->sql, 'order_details')) {
                $detailsInserted++;
                if ($detailsInserted === 2) {
                    throw new RuntimeException('Simulasi kegagalan penyimpanan detail kedua');
                }
            }
        });
        try {
            app(CheckoutService::class)->checkout($this->budi(), 'Jalan Belajar No. 12, Bandung', (string) Str::uuid());
            $this->fail('Checkout harus gagal pada detail kedua.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulasi kegagalan penyimpanan detail kedua', $exception->getMessage());
        }
        $this->assertSame(2, $detailsInserted);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_details', 0);
        $this->assertDatabaseCount('cart_items', 2);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 40]);
        $this->assertDatabaseHas('products', ['id_barang' => 'B002', 'stok' => 60]);
    }

    public function test_cart_operations_and_order_pages_are_scoped_to_logged_in_owner(): void
    {
        $this->cartRow('B001', 2);
        $this->cartRow('B002', 3, 'U002');
        $this->actingAs($this->budi());
        $this->get('/keranjang')->assertSee('Buku Tulis')->assertDontSee('Pulpen');
        $this->delete('/keranjang/B002')->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U002', 'id_barang' => 'B002', 'jumlah' => 3]);
        $this->post('/checkout', $this->checkoutPayload())->assertRedirect();
        $order = DB::table('orders')->sole();

        $this->actingAs(User::findOrFail('U002'));
        $this->get('/keranjang')->assertSee('Pulpen')->assertDontSee('Buku Tulis');
        $this->get('/pesanan')->assertOk()->assertDontSee($order->id_order)->assertSee('Belum ada pesanan');
        $this->get('/pesanan/'.$order->id_order)->assertNotFound();
        $payload = $this->checkoutPayload();
        $this->post('/checkout', array_replace($payload, ['checkout_token' => $order->checkout_token]))->assertStatus(419);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_logout_invalidates_session_but_preserves_only_the_accounts_database_cart(): void
    {
        $this->actingAs($this->budi())->withSession(['private_marker' => 'old session']);
        $this->post('/keranjang/B001')->assertRedirect();
        $this->get('/keranjang')->assertOk();
        $oldId = session()->getId();
        $oldToken = session()->token();
        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('private_marker')->assertSessionMissing('checkout_token');
        $this->assertGuest();
        $this->assertNotSame($oldId, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->get('/keranjang')->assertRedirect('/login');
        $this->assertDatabaseHas('cart_items', ['id_user' => 'U001', 'id_barang' => 'B001', 'jumlah' => 1]);
        $this->post('/login', ['username' => 'siti', 'password' => 'rahasia123'])->assertRedirect();
        $this->get('/keranjang')->assertSee('Keranjangmu masih kosong')->assertDontSee('Buku Tulis');
    }

    public function test_forms_have_csrf_fields_and_logout_has_no_get_route(): void
    {
        $this->get('/login')->assertSee('name="_token"', false);
        $this->actingAs($this->budi())->get('/')->assertSee('name="_token"', false);
        $this->get('/logout')->assertStatus(405);
        $this->assertAuthenticated();
    }

    public function test_reseeding_does_not_reset_stock_after_checkout(): void
    {
        $this->cartRow('B001', 2);
        $this->actingAs($this->budi());
        $this->post('/checkout', $this->checkoutPayload())->assertRedirect();
        $this->seed();
        $this->assertDatabaseCount('products', 10);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 38]);
    }
}
