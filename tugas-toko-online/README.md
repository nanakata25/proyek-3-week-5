# Tugas Integrasi / PR — Rapi Stationery

Toko alat tulis Laravel 13 + MySQL dengan login, 10 barang bergambar, keranjang per akun, checkout atomik, dan riwayat pesanan. Tampilan Blade/CSS dan gambar SVG tersedia lokal; tidak memerlukan npm atau CDN.

## Menjalankan

1. Siapkan PHP 8.3+, Composer, serta MySQL/MariaDB, lalu `composer install` di folder ini.
2. Salin `.env.example` menjadi `.env`. Atur `DB_CONNECTION=mysql`, `DB_DATABASE=db_toko_online`, dan kredensial database. Gunakan `SESSION_DRIVER=file` serta `SESSION_COOKIE=toko_online_session` agar tidak bentrok dengan tugas lain.
3. Buat database kosong: `CREATE DATABASE db_toko_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`.
4. Jalankan `php artisan key:generate`, kemudian `php artisan migrate --seed`.
5. Jalankan `php artisan serve --port=8003` dan buka <http://127.0.0.1:8003>.

| ID | Username | Password demo | Nama |
|---|---|---|---|
| U001 | budi | rahasia123 | Budi Santoso |
| U002 | siti | rahasia123 | Siti Aminah |

Akun dan alamat adalah data contoh praktikum. Seeder memakai `firstOrCreate`, sehingga menjalankan ulang seeder tidak mengembalikan stok yang telah berkurang karena checkout.

## Fitur dan keputusan teknis

| Kebutuhan | Implementasi |
|---|---|
| Katalog dapat dilihat tanpa login | `GET /` menampilkan 10 gambar SVG, nama, harga, dan stok. B010 Binder A5 berstok 0; tombol pembelian dinonaktifkan dan server menolak pembeliannya. |
| Pembelian wajib login | Middleware `auth` melindungi seluruh mutasi keranjang, checkout, dan riwayat. Middleware `guest` melindungi halaman login. |
| Password aman | Model User melakukan cast `hashed`; login menggunakan `Auth::attempt()` dengan error generik dan regenerasi ID sesi. Login dibatasi 10 permintaan per menit. |
| Keranjang | Tabel `cart_items` mempunyai primary key gabungan (`id_user`, `id_barang`); data tetap ada setelah logout dan hanya dapat diakses pemiliknya. Harga diambil dari katalog server. |
| Perubahan kuantitas | Tambah 1, ubah jumlah integer, hapus barang, subtotal, total, validasi stok di server. Jumlah 0 menghapus baris. |
| Checkout atomik | Satu `DB::transaction()` mencatat order dan details, mengurangi stok, lalu menghapus keranjang pemilik. Kegagalan menggagalkan seluruh perubahan. |
| Stok berubah / permintaan bersamaan | Kunci baris pengguna lalu produk dengan `lockForUpdate()`; stok diperiksa kembali di dalam transaksi. Semua mutasi keranjang memakai urutan kunci yang sama. Produk diproses berurutan menurut ID. |
| Klik checkout berulang | UUID `checkout_token` terikat pada sesi dan disimpan unik di order. Pengiriman ulang token yang sama oleh pemilik mengembalikan pesanan semula tanpa mengurangi stok lagi. |
| Harga historis | `order_details.harga_satuan` menyimpan harga saat checkout; detail pesanan memakai harga arsip tersebut walaupun katalog berubah. Harga dan total yang dikirim klien diabaikan. |
| Akses riwayat | Query selalu difilter dengan ID pengguna aktif. Detail pesanan milik pengguna lain memberikan 404. |
| Logout dan CSRF | Logout POST, `@csrf`, `Auth::logout()`, invalidasi sesi, serta regenerasi token. Seluruh form mutasi menggunakan token CSRF; input dinamis di-escape oleh Blade. |

Total bayar sama dengan total harga barang; ongkos kirim Rp0 sesuai soal. Tidak ada integrasi pembayaran eksternal atau klaim bahwa pembayaran telah diproses. Alamat tujuan disimpan pada pesanan, bukan hanya dibaca dari profil terkini.

## Struktur data

- `users`: `id_user VARCHAR(15)` primary key string, nama_lengkap, email unik, username unik, password, no_hp, alamat; tambahan timestamps untuk pencatatan akun.
- `products`: `id_barang VARCHAR(10)` primary key string, nama_barang, deskripsi, harga `DECIMAL(12,2)`, stok integer tak negatif, gambar.
- `orders`: `id_order VARCHAR(15)` primary key string, foreign key id_user, tanggal_order, total_harga `DECIMAL(12,2)`, alamat_pengiriman; tambahan token UUID unik untuk idempotensi.
- `order_details`: primary key gabungan id_order/id_barang, harga_satuan `DECIMAL(12,2)`, jumlah_beli. Nama `jumlah_beli` ditulis huruf kecil konsisten dengan konvensi kode dan memenuhi makna `Jumlah_beli` di soal.
- `cart_items`: tabel tambahan untuk keranjang per pengguna; primary key gabungan dan foreign key mencegah duplikasi/relasi yatim.

ID order terdiri dari `ORD` + 12 karakter acak. Relasi ke produk membatasi penghapusan produk yang masih dirujuk pesanan. Harga diarsipkan, sementara nama dan gambar produk pada detail mengikuti katalog terkini.

## Pengujian

`php artisan test --filter=ShopTest`

18 feature tests mencakup katalog, akses tamu, hash/login/session, kredensial salah, tambah/ubah/hapus keranjang, batas stok, input negatif/pecahan, checkout dan manipulasi harga/ID, keranjang kosong, validasi alamat/token, pengiriman checkout berulang, arsip harga, stok berubah, simulasi kegagalan database di tengah transaksi, pemisahan akun/riwayat, logout, form CSRF, serta seeder berulang.

Tes `test_database_failure_mid_checkout_rolls_back_order_details_stock_and_cart` menyuntikkan kegagalan setelah insert detail kedua. Hasil yang diharapkan adalah nol order/details baru, stok awal kembali, dan keranjang tetap lengkap. Tes stok berubah menurunkan stok sebelum checkout untuk memastikan transaksi ditolak tanpa perubahan parsial.

Konfigurasi `phpunit.xml` harus memakai database tes terisolasi. Tes SQLite memverifikasi perilaku fungsional dan rollback, tetapi tidak membuktikan perilaku lock MySQL dalam permintaan paralel nyata. Uji browser/HTTP pada MySQL diperlukan untuk bukti lingkungan target. Laravel melewati pemeriksaan CSRF saat feature test; keberadaan field diuji di sini, sementara penolakan token hilang diperiksa pada HTTP nyata.

## Skenario demonstrasi yang dapat diulang

Pada database baru: login sebagai Budi, tambahkan **Buku Tulis B001 sebanyak 2** dan **Pulpen B002 sebanyak 3**, lalu checkout dengan alamat contoh. Total seharusnya **Rp19.000**, order memiliki dua detail, stok buku berubah **40 → 38**, stok pulpen **60 → 57**, dan keranjang Budi menjadi kosong. Riwayat Budi menampilkan pesanan tersebut; Siti tidak dapat melihatnya. Mengulangi POST dengan token yang sama tidak menambah pesanan baru.

## Refleksi singkat

Keranjang dan checkout membutuhkan validasi server walaupun kontrol HTML sudah membatasi input. Pemeriksaan stok paling penting dilakukan di dalam transaksi saat checkout karena keadaan katalog dapat berubah setelah keranjang ditampilkan. Menyimpan harga saat beli menjaga riwayat tetap benar, sementara penguncian serta token idempotensi menangani dua penyebab pesanan ganda yang berbeda: request bersamaan dan pengiriman ulang request yang sama.

Sumber: dokumen `Tugas_Toko_Online (PR).pdf`; [Laravel Authentication](https://laravel.com/docs/13.x/authentication), [Database Transactions](https://laravel.com/docs/13.x/database#database-transactions), [Pessimistic Locking](https://laravel.com/docs/13.x/queries#pessimistic-locking).
