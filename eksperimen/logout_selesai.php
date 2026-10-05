<?php
// Do not start a fresh session here, so cookie deletion is directly observable.
require __DIR__ . '/common.php';
page_start('Logout selesai', 'Session · Sesi diakhiri');
?>
<section class="card"><h2>Data session dan cookie ID sesi sudah dihapus</h2><p>Halaman ini tidak memanggil <code>session_start()</code>, sehingga tidak langsung membuat cookie sesi anonim yang baru.</p><p>Periksa DevTools: PHPSESSID untuk path eksperimen telah dihapus. Membuka dashboard kembali akan mengalihkan Anda ke form login dan memulai sesi anonim baru untuk token CSRF.</p><div class="actions"><a class="button" href="dashboard.php">Uji proteksi dashboard</a><a href="login.php">Buka form login</a></div></section>
<?php page_end(); ?>
