# Tugas 2 — Keranjang Belanja Tanpa Login

**Ruang Tulis** adalah katalog alat tulis dengan keranjang tanpa akun. Aplikasi menggunakan Laravel, tabel `barang` pada MySQL, dan JavaScript Local Storage untuk menyimpan pilihan pengguna. Tidak ada proses checkout pada tugas ini; checkout terintegrasi tersedia pada `tugas-toko-online`.

## Menjalankan aplikasi

Prasyarat: PHP 8.4.1+ sesuai paket pada `composer.lock`, ekstensi yang dibutuhkan Laravel 13, Composer, serta MySQL/MariaDB. Tidak membutuhkan build frontend/NPM karena CSS dan modul JavaScript langsung dilayani dari `public`.

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Siapkan database MySQL `db_keranjang` sesuai `.env.example`, lalu setel `.env`:

```dotenv
APP_NAME="Ruang Tulis"
APP_URL=http://127.0.0.1:8002
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_keranjang
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
```

Sesuaikan kredensial dengan lingkungan lokal (jangan commit `.env`). Pada PowerShell, perintah salin alternatif adalah `Copy-Item .env.example .env`.

```sh
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8002
```

Buka `/` untuk daftar barang dan `/keranjang` untuk keranjang.

## Data dan fitur

| ID | Nama | Harga | Stok awal |
|---|---|---:|---:|
| 1 | Buku Tulis | Rp 5.000 | 40 |
| 2 | Pulpen | Rp 3.000 | 60 |
| 3 | Penggaris | Rp 4.000 | 25 |
| 4 | Pensil 2B | Rp 2.500 | 50 |
| 5 | Penghapus | Rp 1.500 | 35 |

Keranjang mendukung tambah barang (produk sama dijumlahkan), tombol tambah/kurang, hapus otomatis saat nol, hapus satu barang, kosongkan seluruh keranjang, penghitung total unit, subtotal per barang, dan total dalam rupiah. Tampilannya responsif. Ilustrasi produk adalah SVG asli yang digambar dalam source code.

Key Local Storage: **`modul4.tugas2.keranjang`**. Nilainya hanya memuat ID dan jumlah:

```json
[{"id":1,"jumlah":2},{"id":2,"jumlah":1}]
```

Nama, harga, dan stok dibaca dari tabel `barang` oleh Laravel dan disisipkan sebagai JSON pada halaman. Tidak ada harga atau nama yang disimpan dalam keranjang. DOM menampilkan teks produk melalui `textContent`; JSON dari server menggunakan `JSON_HEX_*` agar nama produk tidak dapat menutup elemen script.

Data browser dinormalisasi: ID harus dikenal; jumlah harus bilangan bulat positif; ID duplikat digabung; stok kosong/ID tak dikenal dihapus; jumlah berlebih dibatasi stok. JSON rusak diatur ulang dengan pemberitahuan. Perubahan dari tab lain tersinkron lewat event `storage`; kembali fokus ke halaman membaca ulang data. Jika penyimpanan browser tidak tersedia, ada pemberitahuan bahwa keranjang hanya sementara.

## Alasan teknis dan jawaban pertanyaan

1. **Mekanisme:** Local Storage. Keranjang tamu sederhana tidak memerlukan identitas maupun sesi server, tetap bertahan saat refresh serta setelah tab/browser normal ditutup, dan tidak dikirim pada setiap HTTP request. Pilihan hanya berisi ID dan jumlah, sehingga ukuran kecil dan harga dapat diambil ulang dari katalog.
2. **Kelebihan dan kekurangan:** dibanding cookie, kapasitas Local Storage umumnya lebih besar dan datanya tidak ikut setiap request. Dibanding session, keranjang ini tidak membutuhkan penyimpanan sesi di server. Kekurangannya, Local Storage dapat dibaca/diubah JavaScript, terikat origin dan profil browser, serta tidak memiliki kedaluwarsa otomatis. Cookie dapat diberi masa berlaku dan `HttpOnly`; session menyimpan isi di server, tetapi memerlukan pengelolaan sesi dan biasanya cookie pengenal. Ketiga pilihan tetap memerlukan validasi server pada transaksi.
3. **Manipulasi jumlah 999:** setelah nilai `[{"id":1,"jumlah":999}]` dimasukkan lewat DevTools lalu halaman dimuat ulang, aplikasi menyesuaikan jumlah Buku Tulis menjadi **40** sesuai stok, menampilkan peringatan, serta menyimpan kembali `[{"id":1,"jumlah":40}]`. Total menjadi **Rp 200.000**. Ini pertahanan antarmuka untuk demonstrasi; pengguna tetap mengendalikan browser dan dapat menonaktifkan JavaScript, sehingga bukan pengamanan transaksi server.
4. **Harga tidak boleh dipercaya dari browser:** pengguna dapat memodifikasi Local Storage, JavaScript, DOM, dan request. Saat checkout server wajib membaca harga terbaru dari database berdasarkan ID, memvalidasi jumlah dan stok, menghitung ulang seluruh total, mengunci stok/menjalankan transaksi database, membuat pesanan beserta detail, mengurangi stok, dan baru mengosongkan keranjang setelah transaksi berhasil. Harga katalog Tugas 2 diperbarui saat halaman dimuat ulang; keranjang tidak memesan/mengunci stok.
5. **Browser lain:** keranjang tidak muncul karena Local Storage terpisah per origin dan profil browser. Port berbeda juga berarti origin berbeda. Mode privat, penghapusan data situs, pengaturan browser, atau profil lain dapat menghilangkan/memisahkan data. Sinkronisasi tab hanya berlaku dalam origin dan profil yang sama.

## Pengujian dan bukti

Urutan presentasi ketiga aplikasi tersedia dalam [panduan demo Modul 4](../docs/panduan-demo.md).

Pengujian fungsi data dapat dijalankan tanpa browser:

```sh
node --test tests/cart-store.test.mjs
php artisan test --compact
```

Tes Node memeriksa total katalog Rp 13.000, penolakan harga/nama browser, manipulasi 999, JSON rusak, data tidak valid, penggabungan ID duplikat, dan round-trip penyimpanan. Tes Laravel memeriksa lima produk database, halaman tanpa login, kontrol keranjang, dan escaping JSON. Database pengujian harus terpisah dari database penggunaan; konfigurasi PHPUnit memakai database pengujian.

Skenario browser untuk laporan (tangkapan layar harus berasal dari eksekusi nyata):

1. Buka `/`: ambil tangkapan layar lima produk.
2. Buka `/keranjang` setelah mengosongkan keranjang: ambil tampilan kosong.
3. Tambahkan Buku Tulis dua kali dan Pulpen satu kali: badge 3, dua jenis barang, total Rp 13.000.
4. Refresh halaman: isi dan total harus tetap sama. Ambil tangkapan layar.
5. Buka DevTools → Application → Local Storage → origin aplikasi; tampilkan key dan JSON keranjang. Ambil tangkapan layar.
6. Ganti nilai key menjadi `[{"id":1,"jumlah":999}]`, refresh, lalu ambil hasil koreksi/peringatan. Periksa key berubah menjadi jumlah 40.
7. Coba tombol kurang sampai nol, tambah sampai stok, hapus satu, dan kosongkan. Buka tab kedua pada origin sama untuk memeriksa sinkronisasi.
8. Tutup tab/browser normal, buka lagi origin yang sama: isi seharusnya bertahan selama data situs tidak dihapus. Buka profil/browser berbeda: keranjang harus kosong.

**Refleksi:** mekanisme penyimpanan menentukan persistensi dan cakupan data, tetapi tidak menggantikan validasi transaksi. Menyimpan ID/jumlah saja membuat keranjang sederhana dan mencegah ketergantungan pada harga lama. Validasi klien meningkatkan pengalaman pengguna; integritas stok dan pembayaran harus dijamin server.
