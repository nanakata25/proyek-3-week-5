<?php

namespace Tests\Feature;

use App\Models\Barang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeranjangTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_reads_five_products_and_prices_from_database(): void
    {
        $this->seed();
        $response = $this->get('/');
        $response->assertOk()->assertSee('Buku Tulis')->assertSee('Rp 5.000')->assertSee('Masukkan ke keranjang');
        $this->assertDatabaseCount('barang', 5);
        preg_match('/<script id="catalog-data" type="application\/json">(.*?)<\/script>/s', $response->getContent(), $matches);
        $catalog = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(5, $catalog);
        $this->assertSame(['id', 'nama', 'harga', 'stok'], array_keys($catalog[0]));
        $this->assertSame(5000, $catalog[0]['harga']);
        $this->assertSame(40, $catalog[0]['stok']);
    }

    public function test_cart_is_accessible_without_login_and_contains_required_controls(): void
    {
        $this->seed();
        $this->get('/keranjang')->assertOk()
            ->assertSee('Keranjang belanja')->assertSee('Tanpa login')
            ->assertSee('Kosongkan keranjang')->assertSee('Kurangi jumlah')->assertSee('Tambah jumlah');
    }

    public function test_database_name_cannot_break_out_of_json_script(): void
    {
        Barang::create(['nama' => '</script><script>alert(1)</script>', 'harga' => 5000, 'stok' => 10]);
        $response = $this->get('/');
        $response->assertOk()->assertDontSee('</script><script>alert(1)</script>', false);
        $response->assertSee('\\u003C', false);
    }
}
