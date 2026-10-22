# Modul 5 Role Management

Lanjutan toko online W05–W06 pada `tugas-toko-online`, Laravel 13. Nama: Dyas Nakata Hilwan; NIM: 251511009; kelas: D3 A. Baseline sebelum implementasi: `a0fd091`.

## Rencana dan prediksi sebelum pengujian

| Aksi | Guest | Customer | Staff | Admin |
|---|---|---|---|---|
| Katalog | Ya | Ya | Ya | Ya |
| Keranjang | Tidak | Ya | Tidak | Tidak |
| Angka stok | Tidak | Tidak | Ya | Ya |
| Kelola produk | Tidak | Tidak | Tidak | Ya |
| Ubah role | Tidak | Tidak | Tidak | Ya |

BR-01: role default customer. BR-02: endpoint perubahan role hanya admin. BR-03: perubahan hanya customer/staff. BR-04: akun sendiri dan target admin ditolak. BR-05: role sama tidak memanggil save. BR-06: Observer hanya log perubahan role dengan ID target, role lama/baru, dan ID aktor. BR-07: cart dari payload hanya untuk customer yang login.

Prediksi HTTP JSON: admin customer→staff 302 dengan satu perubahan DB dan satu log; customer PATCH 403 tanpa perubahan; self-edit 422; input admin 422; no-op 302 tanpa save/log; staff POST cart/add 403. Guest HTML ke route terlindungi 302 ke login, guest JSON 401. Validasi HTML diprediksi 422 sebagai hipotesis awal yang akan dibandingkan dengan perilaku redirect form Laravel.

## Pembagian tanggung jawab

Route dan Gate menjaga akses; ChangeRoleRequest memvalidasi role; controller menghubungkan request dengan service; RoleManagementService menjaga business rule sebelum save; UserObserver merespons perubahan sukses. Custom primary key pengguna adalah id_user sehingga log memakai getKey(). Role tidak boleh masuk fillable atau formulir publik.

Pengujian lokal memakai database dummy terpisah dari MySQL tugas sebelumnya. Jangan commit database, .env, cookie, CSRF token, password, atau hash. Screenshot dan ringkasan HTTP/DB/log yang sudah disaring disimpan dalam evidence/modul-5.

## Menjalankan hasil praktikum

Dari root repository, dengan runtime PHP lokal yang sudah disiapkan pada tugas sebelumnya:

```powershell
node scripts/modul5-runtime.cjs
```

Buka `http://127.0.0.1:8015/login`. Akun demonstrasi: `admin_modul5`, `admin_lain5`, `customer_modul5`, dan `staff_modul5`; nilai password demo tersedia pada RoleDemoSeeder, bukan kredensial database. Seeder hanya berjalan pada local/testing dan tidak mereset akun yang sudah ada. Database lokal `.runtime/modul5-demo.sqlite` diabaikan Git; konfigurasi `.env` dan database MySQL sebelumnya tetap utuh.

Menu `/dashboard` memperlihatkan role, `/admin/users` hanya admin, `/stok` untuk staff/admin, dan `/stateless` untuk customer. Admin dapat mengedit data produk melalui formulir stok; endpoint update juga dilindungi Gate. Katalog tetap publik, tetapi angka stok hanya ditampilkan kepada staff/admin dan tombol keranjang hanya kepada customer.

## Hasil aktual dan cara mengulang

Seluruh suite lulus: 44 tests dan 369 assertions, termasuk 17 tests/100 assertions untuk role. Sembilan skenario HTTP dijalankan dengan CSRF dan session asli. Admin mengubah customer menjadi staff dengan 302 dan satu log; akses customer/staff yang tidak berhak mendapat 403; self-edit/admin lain dan role admin JSON mendapat 422; no-op tidak save/log. Form HTML invalid menghasilkan 302 dengan error session, berbeda dari prediksi awal 422. Sesi customer yang masih terbuka mengikuti role baru pada request berikutnya.

Instance save memicu Observer, sedangkan bulk update dummy tidak. Role dummy dikembalikan menjadi staff melalui instance save; log CLI memiliki actor_id null. Observer menggunakan getKey() agar ID string id_user tercatat.

```powershell
cd tugas-toko-online
..\.runtime\php\php.exe artisan test
```

Untuk mengulangi bukti browser, siapkan dahulu M5CUSTOMER sebagai customer melalui akun admin (hasil akhir pengujian meninggalkannya sebagai staff), lalu jalankan `node scripts/browser-modul5.cjs`. `--panels-only` merender kembali panel dari hasil tersimpan tanpa mengirim ulang perubahan. Panel E/F adalah screenshot ringkasan respons HTTP/CLI, SELECT database, dan log yang benar-benar diamati, bukan tampilan DevTools palsu. Screenshot UI asli tersedia bersama panel.

## Checkpoint dan worksheet

Branch: `modul-5-role-management`. Daftar checkpoint kode tersedia pada `commits.json`; seluruh commit menggunakan isi dan waktu pengerjaan sebenarnya. Worksheet terisi disimpan dalam folder ini setelah pemeriksaan tata letak. Kolom kelompok dan peer assessment sengaja dibiarkan kosong sesuai permintaan mahasiswa.

Referensi teknis: Modul 5 halaman 2–8; dokumentasi resmi https://laravel.com/docs/13.x/authorization dan https://laravel.com/docs/13.x/eloquent#observers. Lokasi trigger event di framework lokal: Model::performUpdate() memanggil fireModelEvent('updated', false); User::observe didaftarkan sekali di AppServiceProvider.
