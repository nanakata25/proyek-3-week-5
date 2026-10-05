<?php
require __DIR__ . '/common.php';
page_start('Bagaimana web mengingat data?', 'Eksperimen · Tahap 1—5');
?>
<p class="lead">Jalankan setiap percobaan, amati request HTTP dan penyimpanan browser, lalu bandingkan hasilnya dengan penjelasan pada modul.</p>
<div class="grid">
<section class="card"><span class="pill">01 · HTTP</span><h2>Server tidak otomatis mengingat</h2><p>Tiga request membuktikan sifat stateless.</p><a href="langkah1.php">Mulai eksperimen →</a></section>
<section class="card"><span class="pill">02 · COOKIES</span><h2>Nama dan kunjungan</h2><p>Bandingkan penghitung sebelum dan setelah blok redirect.</p><a href="cookie_dasar.php">Penghitung +1 →</a><br><a href="cookie_dasar_atas.php">Varian penghitung +2 →</a></section>
<section class="card"><span class="pill">02 · COOKIES</span><h2>Keranjang dan manipulasi</h2><p>Ubah JSON di cookie dan amati pengaruhnya pada total.</p><a href="keranjang_cookie.php">Buka demo →</a></section>
<section class="card"><span class="pill">03 · SESSION</span><h2>Login yang dilindungi session</h2><p>Password hash, ID sesi baru, halaman terproteksi, dan logout.</p><a href="login.php">Buka form login →</a></section>
<section class="card"><span class="pill">04 · LOCAL STORAGE</span><h2>Preferensi dan keranjang</h2><p>Data bertahan sesudah refresh tanpa terkirim otomatis ke server.</p><a href="tema.html">Preferensi tema →</a><br><a href="keranjang_ls.html">Keranjang Local Storage →</a></section>
<section class="card"><span class="pill">04—05 · BANDINGKAN</span><h2>Umur data dan akses skrip</h2><p>Bandingkan tab baru dan baca data melalui JavaScript.</p><a href="storage_beda.html">Local vs Session Storage →</a><br><a href="akses_storage.html">Akses skrip pada storage →</a></section>
</div>
<div class="notice">Semua tampilan di sini adalah demo lokal. Keranjang cookie sengaja menerima kuantitas dari browser untuk eksperimen manipulasi; jangan gunakan pola ini sebagai validasi checkout.</div>
<?php page_end(); ?>
