# Praktikum Modul 4 - Penyimpanan Data pada Web

Repository: https://github.com/nanakata25/proyek-3-week-5

Tiga aplikasi Laravel 13 dan laboratorium PHP/JavaScript untuk Cookies, Session, dan Local Storage. Seluruh contoh memakai data demonstrasi. Laporan PDF dikumpulkan terpisah di Moodle.

## Struktur

| Folder | Isi |
|---|---|
| `tugas-1-login` | Login username dengan hash password, dashboard, middleware, logout |
| `tugas-2-keranjang` | Keranjang tamu di Local Storage, katalog MySQL berisi 5 produk |
| `tugas-toko-online` | 10 produk, login, keranjang database per akun, checkout transaksional, riwayat |
| `eksperimen` | HTTP stateless, cookie, session PHP, tema, perbandingan Web Storage |
| `docs` | Prosedur eksperimen dan jawaban materi |
| `evidence` | Hasil pengujian aktual dan screenshot aplikasi demonstrasi |
| `scripts` | Pengujian browser, ekspor bukti database, dan pembuat laporan |

## Menjalankan aplikasi

Gunakan **PHP 8.4+**, Composer 2, MySQL 8 atau MariaDB yang didukung Laravel; aktifkan PDO MySQL, mbstring, openssl, fileinfo, curl, zip, XML dan DOM. Laravel 13 membutuhkan minimal PHP 8.3, tetapi paket yang terkunci pada repository ini diuji dengan PHP 8.4.26. Pengujian lokal menggunakan **MariaDB 10.4.32 bawaan XAMPP melalui driver `mysql`**. Tidak diperlukan npm, starter kit, CDN, atau koneksi internet saat aplikasi sudah terpasang.

1. Buat database kosong `db_tugas`, `db_keranjang`, `db_toko_online`.
2. Di masing-masing folder aplikasi, jalankan `composer install`.
3. Salin `.env.example` menjadi `.env`; sesuaikan host, port, user, dan password database. Contoh memakai port standar 3306; lingkungan pengujian lokal terisolasi memakai 3307.
4. Jalankan `php artisan key:generate`, kemudian `php artisan migrate --seed`.
5. Jalankan masing-masing pada terminal berbeda:

```shell
# dari tugas-1-login
php artisan serve --host=127.0.0.1 --port=8001
# dari tugas-2-keranjang
php artisan serve --host=127.0.0.1 --port=8002
# dari tugas-toko-online
php artisan serve --host=127.0.0.1 --port=8003
```

Akun contoh pada Tugas 1 dan toko: **budi / rahasia123**, **siti / rahasia123**. Semua alamat dan pengguna seeder adalah data demonstrasi.

Eksperimen: lihat `eksperimen/README.md`. Seeder hanya berjalan dari CLI. Pengujian di komputer pengerjaan dapat menjalankan `scripts/start-local.ps1` setelah database lokal aktif. Jangan jalankan server dua kali pada port yang sama.

## Pilihan penyimpanan

- Tugas 1: session server dengan cookie identitas sesi yang HttpOnly; password berupa hash.
- Tugas 2: Local Storage untuk `{id,jumlah}`. Persisten pada profil/origin yang sama, tidak terkirim otomatis. Nama, harga, dan stok diambil dari katalog server.
- Toko: keranjang tersimpan pada database per akun agar terpisah antar pengguna dan dapat dimuat lintas sesi/perangkat.

Checkout mengunci pemilik keranjang dan produk dalam urutan tetap di satu transaksi: memvalidasi stok ulang, menghitung harga dari database, membuat order/detail, menurunkan stok, dan menghapus cart. `checkout_token` unik mencegah pesanan ganda saat pengiriman ulang. Semua pembacaan riwayat dibatasi ke akun pemilik. Harga satuan di detail adalah snapshot saat pembelian.

## Pengujian yang telah dijalankan

| Kelompok | Hasil |
|---|---:|
| PHPUnit Tugas 1 | 9 tes / 53 assertions |
| PHPUnit Tugas 2 | 3 tes / 18 assertions |
| PHPUnit toko | 18 tes / 214 assertions |
| Unit JavaScript cart | 6 tes |
| Browser Tugas 1 dan 2 | 20 langkah |
| Browser eksperimen | 9 kelompok (E01-E17) |
| Browser toko | 6 kelompok |

Semua lulus. Bukti tersedia di `evidence`. PHPUnit dijalankan dengan database pengujian terpisah melalui driver MySQL. Jangan mengarahkan `RefreshDatabase` ke database yang berisi data penting. Secara default konfigurasi PHPUnit menggunakan SQLite `:memory:`; untuk mengulang pengujian MySQL, set environment `DB_CONNECTION=mysql`, `DB_DATABASE` ke database `_test`, dan parameter koneksinya sebelum menjalankan `php artisan test`.

```shell
node --test tugas-2-keranjang/tests/cart-store.test.mjs
# dari setiap folder Laravel
php artisan test
```

Skrip browser memakai Playwright dan Chrome. Jalankan `npm install` di akar repository untuk memasang alat pengujian; aplikasi Laravel sendiri tidak memerlukan npm. Sesuaikan `CHROME_PATH` (skrip Tugas 1/2) atau executablePath pada dua skrip lainnya jika lokasi Chrome berbeda. Skrip toko memerlukan database demo baru dengan stok seeder asli dan keranjang kosong; tes melakukan checkout nyata terhadap database lokal. Skrip tidak memakai profil browser pribadi.

## Batas demonstrasi

Belum ada pembayaran eksternal, ongkir, registrasi, admin katalog, pengiriman email, atau deployment produksi karena di luar lingkup tugas. Uji konkurensi multi-proses belum dilakukan; logika penguncian dan rollback diuji, termasuk simulasi kegagalan di tengah checkout. Eksperimen cookie sengaja mempercayai jumlah untuk menunjukkan manipulasi; aplikasi toko memvalidasi seluruh keputusan transaksi di server.

`.env`, APP_KEY, dependensi vendor, database runtime, profil browser, PDF materi dosen, dan laporan PDF/DOCX tidak dipublikasikan. File `.env.example` hanya memuat konfigurasi contoh lokal.

Laporan PDF dibuat dengan `scripts/build_report.py`, sedangkan versi Word yang dapat diedit dibuat dengan `scripts/build_report_docx.py`. Keduanya memakai konten yang sama serta identitas dari argumen `--nim` dan `--name`, dan menyimpan hasil ke folder `output` yang diabaikan Git. Pembuat PDF memerlukan reportlab dan Pillow; pembuat Word memerlukan python-docx, beautifulsoup4, dan Pillow. Screenshot serta hasil pengujian tersedia di `evidence`.
