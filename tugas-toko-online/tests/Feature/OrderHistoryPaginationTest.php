<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderHistoryPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::findOrFail('U001'));
    }

    private function createOrder(string $id, string $user = 'U001', string $date = '2026-10-10 09:00:00'): void
    {
        DB::table('orders')->insert([
            'id_order' => $id,
            'id_user' => $user,
            'tanggal_order' => $date,
            'total_harga' => 5000,
            'alamat_pengiriman' => 'Jalan Belajar No. 12, Bandung',
            'checkout_token' => (string) Str::uuid(),
        ]);
    }

    public function test_history_paginates_only_the_owner_orders_and_links_to_the_next_page(): void
    {
        for ($number = 1; $number <= 12; $number++) {
            $this->createOrder(sprintf('ORD%03d', $number));
        }
        $this->createOrder('OTHER-ORDER', 'U002', '2026-10-11 09:00:00');

        $first = $this->get('/pesanan')->assertOk()
            ->assertSee('Halaman 1 dari 2')
            ->assertSee('href="'.url('/pesanan?page=2').'"', false)
            ->assertSee('rel="next"', false)
            ->assertDontSee('rel="prev"', false)
            ->assertDontSee('OTHER-ORDER')
            ->assertDontSee('ORD002')
            ->assertDontSee('ORD001');
        $orders = $first->viewData('orders');
        $this->assertInstanceOf(LengthAwarePaginator::class, $orders);
        $this->assertSame(12, $orders->total());
        $this->assertSame(10, $orders->perPage());
        $this->assertSame(
            array_map(fn ($number) => sprintf('ORD%03d', $number), range(12, 3)),
            $orders->pluck('id_order')->all(),
        );

        $second = $this->get('/pesanan?page=2')->assertOk()
            ->assertSee('Halaman 2 dari 2')
            ->assertSee('href="'.url('/pesanan?page=1').'"', false)
            ->assertSee('rel="prev"', false)
            ->assertDontSee('rel="next"', false)
            ->assertSee('ORD002')
            ->assertSee('ORD001')
            ->assertDontSee('ORD003')
            ->assertDontSee('OTHER-ORDER');
        $this->assertSame(['ORD002', 'ORD001'], $second->viewData('orders')->pluck('id_order')->all());
    }

    public function test_newer_order_dates_sort_before_order_ids(): void
    {
        $this->createOrder('ORD999', date: '2026-10-09 09:00:00');
        $this->createOrder('ORD001', date: '2026-10-10 09:00:00');

        $response = $this->get('/pesanan')->assertOk();
        $this->assertSame(['ORD001', 'ORD999'], $response->viewData('orders')->pluck('id_order')->all());
    }

    public function test_out_of_range_page_has_a_recovery_link_without_claiming_no_orders_exist(): void
    {
        $this->createOrder('ORD001');

        $this->get('/pesanan?page=99')->assertOk()
            ->assertSee('Halaman ini tidak memuat pesanan.')
            ->assertSee('Ke halaman pertama')
            ->assertSee('href="'.route('orders').'"', false)
            ->assertDontSee('Belum ada pesanan')
            ->assertDontSee('Pesanan pertama akan muncul')
            ->assertDontSee('ORD001');
    }

    public function test_owner_with_no_orders_keeps_the_empty_history_message(): void
    {
        $this->createOrder('OTHER-ORDER', 'U002');

        $this->get('/pesanan')->assertOk()
            ->assertSee('Belum ada pesanan')
            ->assertSee('Pesanan pertama akan muncul di sini setelah kamu checkout.')
            ->assertDontSee('OTHER-ORDER')
            ->assertDontSee('Navigasi riwayat pesanan');
    }
}
