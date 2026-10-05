# Catatan eksperimen Modul 4

Dokumen ini mendeskripsikan implementasi dan prosedur. Kolom **hasil yang diharapkan** adalah prediksi berdasarkan kode; bukti hasil aktual harus berasal dari pelaksanaan dan tangkapan layar, bukan disalin sebagai klaim pengujian.

## Menjalankan laboratorium

Persyaratan: PHP 8.0+ dengan PDO MySQL, mbstring, session, dan MySQL/MariaDB. Dari akar repository, jalankan `php eksperimen/seed_user.php` untuk membuat database `db_belajar`, tabel `users`, dan akun `budi` / `rahasia123`. Seed menggunakan `password_hash(..., PASSWORD_DEFAULT)` dan dapat diulang. Endpoint seed menolak akses HTTP dengan status 404; tidak dapat dipakai pengunjung untuk membuat akun.

Konfigurasi koneksi melalui environment: `EXP_DB_HOST` (default `127.0.0.1`), `EXP_DB_PORT` (`3306`), `EXP_DB_DATABASE` (`db_belajar`), `EXP_DB_USER` (`root`), dan `EXP_DB_PASSWORD` (kosong untuk server lokal demonstrasi). Tidak ada kredensial produksi yang disimpan dalam source. Untuk menjalankan dari akar repository: `php -S 127.0.0.1:8090 -t .`. Buka `http://127.0.0.1:8090/eksperimen/`. Alternatif docroot langsung `eksperimen/` juga didukung karena path cookie dihitung dari direktori URL.

File dan tanggung jawab:

| File | Fungsi |
|---|---|
| `langkah1.php`, `langkah2.php`, `langkah3.php` | Bukti HTTP stateless melalui POST lalu GET |
| `cookie_dasar.php`, `cookie_dasar_atas.php`, `cookie_form.php` | Cookie nama dan perbandingan urutan penghitung |
| `keranjang_cookie.php` | JSON cookie dan manipulasi kuantitas lokal yang sengaja diizinkan |
| `koneksi.php`, `seed_user.php` | PDO MySQL dan seed CLI |
| `session_bootstrap.php`, `login.php`, `dashboard.php`, `logout.php`, `logout_selesai.php` | Autentikasi, proteksi dashboard, dan penghancuran sesi |
| `tema.html`, `assets/tema.js` | Preferensi tema persisten |
| `keranjang_ls.html`, `assets/keranjang-ls.js` | Array JSON keranjang Local Storage |
| `storage_beda.html`, `assets/storage-beda.js` | Pembandingan localStorage dengan sessionStorage |
| `akses_storage.html` | Demonstrasi pembacaan Local Storage oleh skrip origin yang sama |

## Prosedur dan hasil yang diharapkan

Gunakan origin yang konsisten: pergantian port, `localhost` menjadi `127.0.0.1`, atau HTTP menjadi HTTPS menciptakan origin storage yang berbeda. Cookie tidak diisolasi menurut port, sehingga path eksperimen dibatasi untuk mengurangi benturan dengan aplikasi tugas.

| ID | Tindakan yang dapat diulang | Hasil yang diharapkan / bukti yang perlu dicatat |
|---|---|---|
| E01 | Buka `langkah1.php`, isi Budi, kirim form. | Request POST ke Langkah 2 menampilkan “Halo, Budi!”. Tangkap form dan hasil POST. |
| E02 | Klik tautan lanjut ke `langkah3.php`. | GET tidak membawa nama; hasil “Halo, tidak diketahui”. Tangkap Langkah 3. |
| E03 | Hapus cookie `nama` dan `kunjungan`, buka `cookie_dasar.php`. | Tamu dan angka 1. Simpan Budi: halaman akhir menunjukkan angka 2. Refresh: 3. Lupakan saya: 4 dan form tamu. |
| E04 | Amati DevTools → Application → Cookies. | `nama=Budi` dan `kunjungan` berada di browser, masa berlaku 7 hari, path direktori eksperimen. Tangkap sebelum menghapus nama. |
| E05 | Network → Preserve log; pada angka 1, simpan nama. | POST berstatus 302, diikuti GET berstatus 200. Penghitung normal naik 1. Tangkap Network dengan kedua request. |
| E06 | Hapus `nama_atas` / `kunjungan_atas`; buka `cookie_dasar_atas.php`. | Halaman awal 1; Simpan menghasilkan 3; refresh 4; Lupakan saya 6. Berarti simpan/hapus masing-masing +2. Nama cookie varian dipisahkan agar pengukuran independen. |
| E07 | Kosongkan `keranjang_cookie.php`, tambah Buku Tulis dua kali dan Pulpen sekali. | JSON `{"1":2,"2":1}`; subtotal 10.000 + 3.000 = **Rp 13.000**. Tangkap UI dan cookie. |
| E08 | Edit cookie `keranjang` menjadi `{"1":999,"2":1}` melalui DevTools, refresh. | Total **Rp 4.998.000** = 999 × 5.000 + 1 × 3.000. Tangkap cookie yang diedit dan UI sesudah refresh. |
| E09 | Buka `login.php`; kirim budi dengan password salah, lalu benar. | Salah menampilkan pesan generik; benar menampilkan Budi Santoso di dashboard. Tangkap kedua keadaan. |
| E10 | Bandingkan ID cookie PHPSESSID sebelum dan setelah login. | ID berubah setelah `session_regenerate_id(true)`. Cookie memuat ID, bukan nama/password. Tabel dashboard menampilkan data server hanya untuk demonstrasi. |
| E11 | Buka dashboard di konteks browser baru tanpa cookie; atau hapus PHPSESSID lalu refresh. | Dialihkan ke `login.php`; identitas tidak dapat dipulihkan tanpa session ID valid. Tangkap tujuan redirect. |
| E12 | Pada dashboard klik Logout. | POST valid menghapus data session dan cookie lalu menuju `logout_selesai.php`. Halaman ini tidak membuat sesi baru; PHPSESSID path eksperimen hilang. Akses dashboard berikutnya ditolak. |
| E13 | Buka `tema.html`, hapus preferensi, klik Ganti Tema, refresh. | Awalnya terang/null; setelah klik gelap/`"gelap"`; refresh tetap gelap. Tangkap terang, gelap, hasil refresh, serta Application → Local Storage. |
| E14 | Buka `keranjang_ls.html`, kosongkan, tambah Buku Tulis dua kali dan Pulpen sekali. | Total **Rp 13.000**; kunci `keranjang` berisi `[{"id":1,"jumlah":2},{"id":2,"jumlah":1}]`. Refresh mempertahankan isi. Tidak ada request server karena tombol tambah. |
| E15 | Edit Local Storage `keranjang` menjadi `[{"id":1,"jumlah":999},{"id":2,"jumlah":1}]`, refresh. | Total menjadi **Rp 4.998.000**. Local Storage tidak lebih tepercaya daripada cookie. Hapus sesudah percobaan. |
| E16 | Buka `storage_beda.html`, hapus data, tekan Inisialisasi, refresh. | Kedua nilai masih ada. Tutup tab, buka tab baru biasa dengan URL sama tanpa menekan Inisialisasi: `dari_local` tetap ada, `dari_session` null. Tangkap kedua kondisi dan Application → kedua storage. |
| E17 | Isi tema/keranjang, buka `akses_storage.html`, tekan Baca Local Storage. | Data origin yang sama dapat dibaca JavaScript. Tangkap hasil JSON. Ini demonstrasi akses lokal tanpa mengirim data ke pihak lain, bukan klaim telah mengeksploitasi XSS. |

**Catatan eksperimen E16:** Jangan gunakan Duplicate Tab, pemulihan tab tertutup, atau pemulihan sesi browser sebagai “tab baru”; mekanisme tersebut dapat menyalin/memulihkan sessionStorage. Contoh asli modul menulis ulang kedua storage setiap load, sehingga pembukaan ulang tidak dapat menunjukkan hilangnya sessionStorage. Implementasi memisahkan tombol penulisan dari pembacaan untuk memperbaiki validitas percobaan.

**Catatan logout:** Contoh modul langsung redirect ke halaman login yang memanggil `session_start()`. Halaman tujuan dapat segera membuat PHPSESSID anonim baru, sehingga tabel cookie belum tentu kosong walaupun logout benar. Halaman selesai terpisah membuat penghapusan teramati. Saat form login dibuka berikutnya, session anonim baru diperlukan untuk token CSRF; cookie baru itu bukan bukti pengguna masih login.

## Jawaban aktivitas dan diskusi

1. **Mengapa langkah 3 kehilangan nama?** Setiap HTTP request independen. Langkah 2 menerima nama melalui body POST; tautan Langkah 3 hanya membuat GET tanpa body nama. PHP tidak menghubungkan variabel lokal request sebelumnya secara otomatis. Agar tetap menyapa Budi, jalankan `session_start()` di kedua halaman, set `$_SESSION['nama'] = $nama` pada Langkah 2, lalu baca pada Langkah 3. Cookie dapat menjadi alternatif untuk data tidak sensitif; Local Storage perlu dikirim secara eksplisit lewat JavaScript jika PHP memerlukannya.
2. **Mengapa angka kunjungan berbeda +1 dan +2?** POST Simpan atau GET Hapus menjalankan skrip lalu menghasilkan 302; browser mengirim GET kedua untuk menampilkan halaman. Blok penghitung di atas `exit` dijalankan pada kedua request. Blok di bawah seluruh cabang redirect hanya berjalan pada request yang benar-benar merender halaman. Refresh sendiri hanya satu request sehingga kedua varian +1.
3. **Mengapa cookie yang baru disetel belum ada di `$_COOKIE` saat itu?** `$_COOKIE` berasal dari header request yang sudah diterima. `setcookie()` menulis header respons `Set-Cookie`; browser baru mengembalikan cookie melalui request berikutnya. Variabel lokal dapat digunakan untuk tampilan langsung.
4. **Apa risiko perubahan kuantitas 999?** Pengguna menguasai data browser. Server yang mempercayai kuantitas tanpa memeriksa integer positif, batas jumlah, keberadaan produk, stok, dan harga database dapat menghasilkan transaksi salah. Demo hanya menghitung UI; tidak mempunyai checkout. Meng-escape output mencegah injeksi HTML, tetapi tidak menjamin kebenaran bisnis.
5. **Mengapa session lebih baik daripada cookie untuk keranjang satu kunjungan?** Keranjang di server tidak dapat diedit langsung melalui DevTools; browser hanya membawa ID sesi yang sulit ditebak. Payload besar tidak dikirim berulang pada setiap request dan tidak dibatasi ukuran cookie sekitar 4 KiB. Tetap validasi input tambah/ubah jumlah, gunakan CSRF, dan periksa ulang stok/harga saat checkout. Session satu server tidak otomatis memberi sinkronisasi lintas perangkat; kebutuhan itu memerlukan database per akun.
6. **Apa yang ada di PHPSESSID?** ID sesi acak. Identitas seperti `user_id` dan nama berada dalam penyimpanan session server. Cookie tidak boleh sekadar menyatakan `login=true` atau memuat username yang dipercaya sebagai autentikasi karena dapat diedit pengguna.
7. **Apa akibat dashboard dibuka tanpa login atau cookie dihapus?** Server tidak menemukan session berisi `user_id`, sehingga mengalihkan request ke login. Menghapus cookie saja tidak menjamin data sesi lama di server langsung terhapus; logout server harus menghapus data dan `session_destroy()`.
8. **Mengapa mengganti session ID saat login?** Mencegah sesi sebelum autentikasi yang diketahui penyerang menjadi sesi autentikasi pengguna (session fixation). Parameter `true` menghapus penyimpanan ID lama.
9. **Mengapa tema tetap gelap?** Nilai `tema=gelap` dipertahankan pada Local Storage dan dibaca kembali saat page load. Persisten bukan berarti abadi: pengguna, kebijakan browser, mode privat, atau penghapusan data situs dapat menghapusnya.
10. **Apakah Local Storage lebih aman daripada cookie untuk keranjang?** Tidak sebagai sumber kebenaran. Pengguna dan skrip origin yang sama dapat mengubah keduanya. Perbedaan transport: cookie yang cocok domain/path/flags dikirim otomatis, sedangkan Local Storage tidak. Harga final dan stok harus ditentukan server.
11. **Mengapa array memakai JSON?** Cookie dan Web Storage menyimpan string. `JSON.stringify`/`json_encode` mengubah struktur menjadi teks, sedangkan `JSON.parse`/`json_decode` memulihkannya. Parsing perlu penanganan gagal dan validasi bentuk data; JSON bukan enkripsi.
12. **Apa risiko XSS?** Skrip yang berjalan dalam origin aplikasi dapat membaca Local Storage, mengubah UI, dan bertindak dengan sesi pengguna. Gunakan escaping sesuai konteks, `textContent` untuk data dinamis, validasi, serta cookie session HttpOnly. HttpOnly mengurangi pencurian nilai cookie via JavaScript, bukan menghentikan semua tindakan XSS. Token CSRF dan SameSite saling melengkapi.

## Keputusan implementasi dan batas keamanan

| Keputusan | Alasan dan batasan |
|---|---|
| Demo cookie memakai nama sesuai modul, varian memakai akhiran `_atas` | Memudahkan pemeriksaan DevTools dan perbandingan counter yang independen. |
| Cookie path dibatasi ke direktori eksperimen | Mengurangi benturan cookie dengan tugas lain. Path bukan mekanisme otorisasi. Cookie sama host dapat dibagi lintas port. |
| Cookie nama/penghitung HttpOnly=true; cookie keranjang demo false | Cookie yang tidak perlu dibaca JS dibuat HttpOnly. Cookie keranjang sengaja dapat diamati/diubah untuk pembelajaran; HttpOnly tetap tidak melarang pemilik browser mengedit cookie melalui DevTools. |
| SameSite=Lax pada cookie | Mengurangi pengiriman cookie pada sebagian request lintas situs. Demo GET yang mengubah cookie tetap bukan pola produksi; operasi autentikasi/logout memakai POST + token CSRF. |
| Secure mengikuti HTTPS | HTTP localhost membutuhkan cookie untuk eksperimen; pada HTTPS flag Secure otomatis aktif. Deploy nyata harus menggunakan HTTPS dan konfigurasi proxy tepercaya. |
| Hash password dengan `PASSWORD_DEFAULT` dan verifikasi `password_verify` | Hash satu arah disimpan, plaintext tidak dimasukkan ke database; berbeda dari enkripsi yang dapat didekripsi. |
| PDO prepared statement | Data username tidak dirangkai menjadi SQL. Error koneksi yang tampil tidak membuka kredensial. |
| Session strict mode, only cookies, regenerasi ID | Mengurangi penerimaan ID yang belum diterbitkan server dan risiko session fixation. |
| POST + CSRF untuk login/logout | Mencegah request perubahan autentikasi sembarangan dan logout melalui tautan biasa. |
| Local Storage UI memakai `textContent` | Isi storage diperlakukan sebagai teks, bukan HTML yang dijalankan. Penanganan error storage dan JSON tidak valid menjaga UI tetap terbaca. |
| sessionStorage ditulis melalui tombol | Pemisahan penulisan/pembacaan membuat uji penutupan tab dapat menunjukkan hilangnya data. |
| Demo cookie tidak menjalankan pembayaran atau mengubah stok | Kerentanan kuantitas disengaja hanya di laboratorium. Tugas toko online harus memeriksa jumlah, harga, dan stok di server dalam transaksi database. |

Cookie “per request” berarti hanya request yang cocok domain, path, masa berlaku, Secure dan kebijakan SameSite/browser. Batas sekitar 4 KiB per cookie dan sekitar 5 MiB per Web Storage adalah pedoman pembelajaran; implementasi browser dan kebijakannya menentukan kuota nyata. PHP session lifetime juga bergantung pada cookie, konfigurasi garbage collection server, dan timeout aplikasi; menutup browser bukan mekanisme pencabutan sesi yang dapat diandalkan.

## Jawaban kuis 1–26

| No. | Jawaban | Penjelasan |
|---|---|---|
| 1 | **B** | HTTP tidak otomatis mengingat atau mengaitkan request sebelumnya. Aplikasi menambahkan mekanisme state. |
| 2 | **C — `setcookie()`** | Fungsi menulis header Set-Cookie. `$_COOKIE` digunakan untuk membaca cookie dari request. |
| 3 | **B** | Setel masa berlaku ke masa lalu dengan path/domain yang sama. `unset($_COOKIE['nama'])` hanya mengubah array pada request PHP itu. |
| 4 | **B — `session_start()`** | Memulai/memulihkan session sebelum membaca atau menulis `$_SESSION` (kecuali konfigurasi auto-start yang tidak digunakan di sini). |
| 5 | **C — ID sesi (PHPSESSID)** | Isi data sesi sebenarnya berada di server; browser hanya mengirim identifikator. |
| 6 | **B — sekitar 5 MB** | Angka pedoman per origin untuk Local Storage; kuota ditentukan browser, tidak tak terbatas. |
| 7 | **C — string** | `setItem` mengonversi 20 menjadi `"20"`; `getItem` mengembalikan string atau null jika tidak ada. |
| 8 | **B — `localStorage.setItem('a', JSON.stringify(array))`** | Serialisasi JSON mempertahankan struktur; gunakan `JSON.parse` ketika membaca kembali. |
| 9 | **C** | sessionStorage terkait sesi satu tab, localStorage persisten per origin. Session restore/duplikasi tab perlu diperhitungkan dalam percobaan. |
| 10 | **B — HttpOnly** | Melarang pembacaan cookie oleh JavaScript; Secure hanya membatasi transport HTTPS. |
| 11 | **C** | `session_regenerate_id(true)` mengganti ID dan menghapus sesi ID lama untuk melawan fixation. |
| 12 | **C — token akses atau password** | Local Storage dapat dibaca skrip origin yang sama; XSS dapat mengekspos kredensial tersebut. |
| 13 | **Benar, dengan cakupan cookie yang cocok** | Browser otomatis mengirim cookie yang cocok domain/path, belum kedaluwarsa, dan memenuhi flags/kebijakan. Tidak berarti semua cookie ke semua request domain. |
| 14 | **Salah** | Local Storage tidak dikirim otomatis melalui HTTP; perlu kode yang mengirimkannya secara eksplisit. |
| 15 | **Salah sebagai aturan praktik** | Header harus disetel sebelum output dikirim. Output buffering dapat menunda pengiriman, tetapi tidak dijadikan ketergantungan pola kode ini. |
| 16 | **Salah** | Cookie baru kembali dalam request selanjutnya setelah browser menerima Set-Cookie. |
| 17 | **Benar** | Data PHP session berada pada storage server, misalnya file/Redis/database sesuai handler. |
| 18 | **Salah** | Pemilik browser dapat membaca dan mengedit cookie lewat DevTools; data klien tidak dapat dipercaya begitu saja. |
| 19 | `localStorage.removeItem('tema');` | Menghapus hanya kunci tema. `clear()` akan menghapus semua kunci pada Local Storage origin itu. |
| 20 | `$_SESSION['user_id'] = $id;` | Jalankan setelah `session_start()` dan hanya setelah identitas berhasil diverifikasi. |
| 21 | `session_destroy();` | Untuk logout lengkap, kosongkan `$_SESSION`, hapus cookie dengan atribut cocok, lalu hancurkan data sesi di server. |
| 22 | **Session PHP** | Status autentikasi dipercayai dari server; cookie berisi ID sesi, bukan flag login yang bebas diedit. |
| 23 | **Cookie** | Bahasa adalah preferensi kecil yang perlu tersedia ketika server menyusun halaman. |
| 24 | **Local Storage** | Draft teks panjang dapat disimpan otomatis pada perangkat tanpa request tambahan; pertimbangkan sensitivitas dan batas kuota. |
| 25 | **sessionStorage** | Posisi langkah sementara terisolasi per sesi tab. Jika nilai menentukan validasi/proses server, session PHP juga diperlukan. |
| 26 | **Database terkait akun** | Keranjang dapat dimuat dari HP dan laptop; penyimpanan lokal browser atau session tunggal tidak otomatis menyinkronkan antar perangkat. |

## Refleksi teknis untuk dilengkapi hasil aktual

Eksperimen dirancang untuk memisahkan lokasi data, cara pengiriman, dan masa hidupnya. “Disimpan di browser” tidak berarti aman dari perubahan pemilik browser, sedangkan “disimpan di server” tetap membutuhkan validasi input dan perlindungan session. Perbandingan +1/+2 menunjukkan pentingnya menelusuri seluruh rangkaian request, bukan hanya satu klik. Perbaikan uji sessionStorage dan halaman logout menunjukkan bahwa rancangan percobaan juga menentukan apakah kesimpulan dapat dipercaya. Isi paragraf refleksi akhir laporan dengan pengalaman dan hasil pengujian aktual yang tercatat.
