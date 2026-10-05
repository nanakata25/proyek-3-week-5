# Eksperimen penyimpanan web

Jalankan PHP dengan docroot akar repository dan buka `/eksperimen/`. Contoh: `php -S 127.0.0.1:8090 -t .`.

Eksperimen session membutuhkan MySQL dan extension PDO MySQL. Jalankan `php eksperimen/seed_user.php` melalui terminal; seed ditolak bila diakses lewat HTTP. Akun demonstrasi: `budi` / `rahasia123`. Environment database: `EXP_DB_HOST`, `EXP_DB_PORT`, `EXP_DB_DATABASE`, `EXP_DB_USER`, `EXP_DB_PASSWORD` (default 127.0.0.1:3306, db_belajar, root, password kosong).

Prosedur, hasil yang diharapkan, jawaban aktivitas, keputusan teknis, dan jawaban kuis 1–26 tersedia pada [catatan eksperimen](../docs/eksperimen-catatan.md). Hasil pengujian aktual dicatat terpisah pada laporan utama.

`keranjang_cookie.php` sengaja menerima jumlah dari cookie tanpa pemeriksaan stok sebagai demonstrasi manipulasi data browser. Gunakan hanya untuk pembelajaran lokal, bukan checkout produksi. Contoh login memakai hash password, prepared statement, cookie HttpOnly, ID session baru, dan CSRF. Demo stateless, cookie, tema, dan Web Storage tidak membutuhkan database.
