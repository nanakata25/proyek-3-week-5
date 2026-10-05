<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['id' => 1, 'nama' => 'Buku Tulis', 'harga' => 5000, 'stok' => 40],
            ['id' => 2, 'nama' => 'Pulpen', 'harga' => 3000, 'stok' => 60],
            ['id' => 3, 'nama' => 'Penggaris', 'harga' => 4000, 'stok' => 25],
            ['id' => 4, 'nama' => 'Pensil 2B', 'harga' => 2500, 'stok' => 50],
            ['id' => 5, 'nama' => 'Penghapus', 'harga' => 1500, 'stok' => 35],
        ] as $item) {
            Barang::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
