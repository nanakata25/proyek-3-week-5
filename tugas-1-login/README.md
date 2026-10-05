# Tugas 1 — Login dengan Password Ter-hash

Aplikasi Laravel 13 tanpa starter kit. Autentikasi menggunakan **username**, `Auth::attempt()`, sesi server, dan Blade. Tampilan memakai CSS lokal sehingga tidak memerlukan build npm.

## Menjalankan

1. Pasang PHP 8.3+ dengan ekstensi Laravel, Composer, dan MySQL/MariaDB.
2. Jalankan `composer install` di folder ini.
3. Salin `.env.example` menjadi `.env`, lalu sesuaikan akses MySQL. Database: `db_tugas`. Driver sesi: `file`, cookie sesi: `tugas1_session`.
4. Buat database kosong: `CREATE DATABASE db_tugas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`.
5. Jalankan `php artisan key:generate` lalu `php artisan migrate --seed`.
6. Jalankan `php artisan serve --port=8001` dan buka <http://127.0.0.1:8001/login>.

| Username | Password demo | Nama lengkap |
|---|---|---|
| budi | rahasia123 | Budi Santoso |
| siti | rahasia123 | Siti Aminah |

Kredensial ini khusus data praktikum lokal. Sistem tidak menyediakan fitur remember-me, sehingga tabel `users` hanya berisi kolom yang diminta modul.

## Alur dan keputusan teknis

- `GET /login` dan `POST /login`: middleware `guest`; validasi username/password; kesalahan kredensial memakai pesan umum persis **Username atau password salah.** Password tidak di-flash kembali ke sesi.
- Login sukses memanggil `$request->session()->regenerate()` sebelum redirect `/dashboard`.
- `GET /dashboard` dan `POST /logout` diproteksi `auth`.
- Logout memanggil `Auth::logout()`, `invalidate()`, lalu `regenerateToken()`. Seluruh formulir POST memakai `@csrf`.
- `$fillable` menyesuaikan `username`, `password`, `nama_lengkap`; cast `'password' => 'hashed'` menjaga penyimpanan password melalui model selalu berbentuk hash.
- Session disimpan sebagai file di server; browser menyimpan cookie pengenal sesi. Nama cookie khusus menghindari benturan dengan proyek tugas lain pada localhost.
- Teks dinamis dirender melalui `{{ ... }}` agar di-escape oleh Blade.

## Jawaban pertanyaan (a–d; ringkas)

**a. Mengapa password di-hash dan apa isi kolom password?** Hash adalah hasil fungsi satu arah; sistem memverifikasi kecocokan tanpa perlu menyimpan password asli. Dengan bcrypt, nilai kolom berupa string 60 karakter berawalan `$2y$`, yang memuat informasi algoritma, cost, salt, dan hasil hash. Dua pengguna dengan password sama tetap memperoleh hash berbeda karena salt acak. Hash bukan enkripsi yang dapat didekripsi. Cast `hashed` membentuk hash saat penyimpanan; `Auth::attempt()` memeriksa password masukan terhadap hash.

**b. Mengapa logout POST dengan `@csrf`?** Logout mengubah keadaan sesi sehingga memakai POST. `@csrf` menambahkan token yang diverifikasi Laravel untuk mengurangi risiko permintaan lintas situs yang memaksa logout. Link GET tidak sesuai untuk perubahan keadaan dan bisa terpanggil oleh navigasi/prefetch. POST sendiri belum cukup tanpa pemeriksaan token.

**c. Fungsi middleware `auth` dan `guest`?** `auth` hanya mengizinkan pengguna terautentikasi mengakses dashboard/logout. Membuka `/dashboard` tanpa login mengarahkan browser ke `/login`. `guest` hanya menerima pengunjung yang belum login; pengguna aktif yang membuka `/login` dialihkan ke `/dashboard`.

**d. Fungsi `session()->regenerate()`?** Mengganti ID sesi setelah login sehingga ID sesi sebelum autentikasi tidak menjadi ID sesi terautentikasi. Ini mencegah session fixation, yaitu pemakaian ulang ID sesi yang sebelumnya diketahui penyerang. Data sesi yang diperlukan tetap tersedia.

## Pengujian

Jalankan `php artisan test --filter=AuthenticationTest`. Pengujian mencakup seed ter-hash, redirect tamu, login benar/salah, regenerasi ID sesi, validasi, middleware guest, logout yang menghapus data sesi dan mengganti CSRF token, penolakan GET logout, serta keberadaan field CSRF. Tes memakai database terisolasi sesuai `phpunit.xml`; jangan arahkan konfigurasi tes ke database aplikasi. Middleware CSRF dilewati oleh Laravel saat HTTP feature test, sehingga penolakan POST tanpa token perlu diperiksa dengan HTTP/browser sungguhan.

## Bukti layar yang diperlukan

1. Isi `users` melalui klien database (`SELECT id, username, password, nama_lengkap FROM users;`).
2. Login gagal dengan password salah.
3. Login berhasil, banner berhasil, dan dashboard Budi Santoso.
4. `/dashboard` pada browser incognito diarahkan ke `/login`.
5. Setelah logout, banner logout dan dashboard tidak lagi dapat diakses.

Referensi: [Laravel 13 Authentication](https://laravel.com/docs/13.x/authentication), [Laravel 13 HTTP Tests](https://laravel.com/docs/13.x/http-tests). Dokumen tugas lokal: `Tugas-1-Login-Laravel.pdf`.
