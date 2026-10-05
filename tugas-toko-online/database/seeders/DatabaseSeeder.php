<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['U001', 'budi', 'Budi Santoso', 'budi@example.test', '081234560001', 'Jalan Belajar No. 12, Bandung'],
            ['U002', 'siti', 'Siti Aminah', 'siti@example.test', '081234560002', 'Jalan Melati No. 8, Bandung'],
        ] as [$id, $username, $nama, $email, $noHp, $alamat]) {
            User::firstOrCreate(['id_user' => $id], [
                'username' => $username, 'nama_lengkap' => $nama, 'email' => $email,
                'password' => 'rahasia123', 'no_hp' => $noHp, 'alamat' => $alamat,
            ]);
        }

        foreach ([
            ['B001', 'Buku Tulis', 'Buku bergaris 38 lembar untuk ide dan catatan sehari-hari.', 5000, 40, 'buku.svg'],
            ['B002', 'Pulpen', 'Pulpen tinta biru dengan pegangan nyaman dan ujung halus.', 3000, 60, 'pulpen.svg'],
            ['B003', 'Penggaris', 'Penggaris 30 cm untuk membuat garis dan mengukur dengan rapi.', 4000, 25, 'penggaris.svg'],
            ['B004', 'Pensil 2B', 'Pensil grafit 2B untuk menggambar, menulis, dan mengarsir.', 2500, 50, 'pensil.svg'],
            ['B005', 'Penghapus', 'Penghapus lembut yang menjaga halaman tetap bersih.', 1500, 35, 'penghapus.svg'],
            ['B006', 'Stabilo Pastel', 'Penanda teks warna lembut untuk merangkum bagian penting.', 8500, 20, 'stabilo.svg'],
            ['B007', 'Sticky Notes', 'Catatan tempel praktis untuk pengingat dan ide singkat.', 7000, 30, 'sticky-notes.svg'],
            ['B008', 'Gunting Kertas', 'Gunting kecil dengan pegangan nyaman untuk kegiatan kreatif.', 12000, 15, 'gunting.svg'],
            ['B009', 'Tempat Pensil', 'Simpan alat tulis favorit dengan rapi di satu tempat.', 25000, 12, 'tempat-pensil.svg'],
            ['B010', 'Binder A5', 'Binder isi ulang untuk menyusun catatan sesuai kebutuhan.', 35000, 0, 'binder.svg'],
        ] as [$id, $nama, $deskripsi, $harga, $stok, $gambar]) {
            // Re-running the seeder preserves stock after real checkouts.
            Product::firstOrCreate(['id_barang' => $id], [
                'nama_barang' => $nama, 'deskripsi' => $deskripsi,
                'harga' => $harga, 'stok' => $stok, 'gambar' => $gambar,
            ]);
        }
    }
}
