<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function userWithRole(string $role): User
    {
        $user = User::findOrFail('U001');
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function productPayload(): array
    {
        return [
            'nama_barang' => 'Buku Tulis Revisi',
            'deskripsi' => 'Deskripsi barang yang diperbarui admin.',
            'harga' => 6500,
            'stok' => 22,
        ];
    }

    public function test_guest_and_customer_cannot_read_stock_page(): void
    {
        $this->get('/stok')->assertRedirect(route('login'));
        $this->actingAs($this->userWithRole('customer'))->get('/stok')->assertForbidden();
    }

    public function test_staff_can_read_stock_without_product_edit_form(): void
    {
        $this->actingAs($this->userWithRole('staff'))->get('/stok')
            ->assertOk()
            ->assertSee('Stok: 40')
            ->assertSee('Buku Tulis')
            ->assertDontSee('name="nama_barang"', false)
            ->assertDontSee('Simpan produk B001');
    }

    public function test_staff_cannot_update_product_even_with_direct_url(): void
    {
        $this->actingAs($this->userWithRole('staff'))
            ->patch('/admin/products/B001', $this->productPayload())->assertForbidden();

        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'nama_barang' => 'Buku Tulis', 'stok' => 40]);
    }

    public function test_admin_can_update_allowlisted_product_fields(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->patch('/admin/products/B001', $this->productPayload() + ['gambar' => '../../secret'])
            ->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'nama_barang' => 'Buku Tulis Revisi', 'stok' => 22, 'gambar' => 'buku.svg']);
        $this->assertSame('6500.00', Product::findOrFail('B001')->harga);
    }

    public function test_admin_product_validation_rejects_negative_stock_and_price(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->patchJson('/admin/products/B001', array_replace($this->productPayload(), ['harga' => -1, 'stok' => -1]))
            ->assertUnprocessable()->assertJsonValidationErrors(['harga', 'stok']);

        $this->assertDatabaseHas('products', ['id_barang' => 'B001', 'stok' => 40]);
    }
}
