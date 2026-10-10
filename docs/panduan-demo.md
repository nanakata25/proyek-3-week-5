# Panduan demo Modul 4

Panduan ini menjelaskan langkah demonstrasi manual dan hasil yang diharapkan. Ini bukan catatan pengujian baru. Hasil eksekusi sebelumnya tetap tersedia di folder [`evidence`](../evidence).

## Persiapan

Ikuti README masing-masing aplikasi untuk memasang dependensi, mengatur `.env`, dan menjalankan server. Gunakan host yang konsisten, misalnya `127.0.0.1`; berganti ke `localhost` mengubah origin Local Storage.

| Aplikasi | Alamat awal | Database contoh |
|---|---|---|
| [Tugas 1](../tugas-1-login/README.md) | http://127.0.0.1:8001/login | `db_tugas` |
| [Tugas 2](../tugas-2-keranjang/README.md) | http://127.0.0.1:8002 | `db_keranjang` |
| [Toko online](../tugas-toko-online/README.md) | http://127.0.0.1:8003 | `db_toko_online` |

Akun demo Tugas 1 dan toko: `budi` atau `siti`, dengan password `rahasia123`. Gunakan profil browser khusus demonstrasi agar kondisi login dan keranjang mudah diketahui.

Catat stok serta jumlah pesanan sebelum memulai demo toko. Checkout mengubah database secara nyata. Seeder toko memakai `firstOrCreate`, sehingga menjalankan ulang seeder tidak mengembalikan stok atau menghapus pesanan. Jika perlu kondisi awal persis seperti seeder, gunakan database demo baru sesuai petunjuk instalasi; tidak perlu menghapus database hasil pengerjaan.

## Tugas 1 — Login dan session

1. Buka `/dashboard` sebelum login. Browser seharusnya beralih ke `/login`.
2. Masukkan username `budi` dengan password salah. Tampil pesan “Username atau password salah.”
3. Login menggunakan password demo. Dashboard menampilkan Budi Santoso.
4. Saat masih login, buka `/login`. Middleware `guest` mengarahkan kembali ke dashboard.
5. Buka `/dashboard` pada jendela privat yang belum login. Browser seharusnya kembali ke login karena tidak membawa session pengguna sebelumnya.
6. Klik **Logout** pada jendela yang sudah login, kemudian akses `/dashboard` lagi. Akses kembali memerlukan login.

Untuk menjelaskan password, tunjukkan kolom `users.password` pada database demonstrasi: nilainya berupa hash. Untuk menjelaskan session, tunjukkan cookie `tugas1_session`: cookie membawa pengenal sesi, sedangkan identitas pengguna tersimpan di server.

## Tugas 2 — Keranjang Local Storage

1. Buka katalog dan halaman `/keranjang`. Jika ada isi dari demo sebelumnya, klik **Kosongkan keranjang** untuk memulai kondisi kosong.
2. Tambahkan Buku Tulis sebanyak 2 dan Pulpen sebanyak 1. Keranjang berisi dua jenis barang, tiga unit, dengan total **Rp13.000** pada harga seeder.
3. Refresh halaman. Jumlah dan total tetap sama. Buka tab kedua pada origin yang sama untuk menunjukkan bahwa kedua tab membaca keranjang yang sama.
4. Buka DevTools → **Application** → **Local Storage** → `http://127.0.0.1:8002`. Periksa key `modul4.tugas2.keranjang` dengan nilai berikut:

   ```json
   [{"id":1,"jumlah":2},{"id":2,"jumlah":1}]
   ```

5. Untuk percobaan manipulasi, ubah nilai key menjadi `[{"id":1,"jumlah":999}]`, lalu refresh. Pada stok seeder Buku Tulis 40, aplikasi menampilkan peringatan, menyesuaikan jumlah menjadi 40, dan menghasilkan total **Rp200.000**. Jika stok katalog telah diubah, batas mengikuti stok terbaru.
6. Coba kurangi jumlah hingga nol, hapus satu barang, dan kosongkan keranjang. Tunjukkan bahwa kontrol tersebut memperbarui tampilan dan Local Storage.
7. Isi kembali keranjang, tutup browser normal, lalu buka pada profil dan origin yang sama. Data tetap tersedia selama data situs tidak dihapus. Profil baru atau jendela privat terpisah memulai keranjang sendiri.

Jelaskan bahwa koreksi jumlah di JavaScript membantu antarmuka. Pengguna tetap dapat mengubah data browser; keputusan harga dan stok saat checkout harus dilakukan server. Tugas 2 tidak memiliki checkout, sedangkan integrasinya ada pada toko online.

## Toko online — Checkout dan riwayat

1. Buka katalog tanpa login. Sepuluh barang dapat dilihat; Binder A5 berstok nol pada seeder tidak dapat dibeli.
2. Buka `/keranjang` sebagai tamu. Browser mengarahkan ke login. Masuk sebagai `budi`.
3. Siapkan keranjang berisi **B001 Buku Tulis ×2** dan **B002 Pulpen ×3**, tanpa barang lain. Jika ada isi sebelumnya, gunakan **Hapus** atau ubah jumlah lalu klik **Ubah**. Pastikan stok cukup.
4. Pada harga seeder, subtotal buku Rp10.000 dan pulpen Rp9.000. Total bayar **Rp19.000**, dengan ongkos kirim Rp0. Catat stok katalog dan jumlah pesanan Budi sebelum checkout.
5. Isi alamat demonstrasi minimal 10 karakter, lalu klik **Checkout sekarang** satu kali.
6. Halaman detail menampilkan ID pesanan, dua jenis barang, jumlah pembelian, harga saat beli, alamat, dan total. Verifikasi perubahan berikut:

   | Pemeriksaan | Perubahan yang diharapkan |
   |---|---|
   | Pesanan Budi | Bertambah satu |
   | Detail pesanan baru | Dua baris: B001 ×2 dan B002 ×3 |
   | Stok B001 | Stok sebelum checkout dikurangi 2 |
   | Stok B002 | Stok sebelum checkout dikurangi 3 |
   | Keranjang Budi | Kosong |
   | Riwayat `/pesanan` | Memuat pesanan baru dengan total Rp19.000 |

7. Simpan URL detail pesanan Budi, logout, lalu login sebagai `siti`. Riwayat Siti hanya memuat pesanannya sendiri. Membuka URL pesanan Budi memberikan **404**.

Pada database baru, stok berubah **40 → 38** untuk buku dan **60 → 57** untuk pulpen. Jika database sudah pernah dipakai checkout, bandingkan dengan stok yang dicatat sebelum demo, bukan selalu dengan angka awal seeder. Periksa tabel `orders`, `order_details`, `products`, dan `cart_items` untuk menghubungkan tampilan dengan perubahan data.

## Poin penjelasan saat presentasi

- Password di-hash untuk verifikasi; hash tidak didekripsi saat login.
- Session menyimpan status login di server; Local Storage menyimpan keranjang tamu pada origin dan profil browser.
- Keranjang toko berada di database per akun sehingga terpisah antara Budi dan Siti.
- Checkout membaca harga dari database dan memeriksa stok dalam transaksi. Pembuatan pesanan, detail, pengurangan stok, dan pengosongan keranjang harus berhasil bersama.
- Harga satuan pada detail pesanan menyimpan harga saat beli. Riwayat tidak menghitung ulang total menggunakan harga katalog yang berubah kemudian.

Untuk percobaan HTTP stateless, cookie, session PHP, tema, dan umur penyimpanan tab, lanjutkan ke [catatan eksperimen](eksperimen-catatan.md).
