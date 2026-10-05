<?php require __DIR__ . '/common.php'; $nama = is_string($_POST['nama'] ?? null) ? $_POST['nama'] : null; page_start('Langkah 3: Apakah server masih ingat?', 'HTTP stateless · Request 3'); ?>
<section class="card"><h2>Halo, <?= e($nama ?? 'tidak diketahui') ?>.</h2><p>Tautan dari Langkah 2 menghasilkan request GET tanpa data nama. Data POST dari request sebelumnya tidak terbawa.</p><pre>Metode request: <?= e($_SERVER['REQUEST_METHOD']) ?>
Nama yang diterima: <?= e($nama ?? '(tidak ada)') ?></pre><div class="notice">Agar nama tetap tersedia, simpan di session pada Langkah 2 dan baca session itu di Langkah 3; kedua halaman harus memanggil <code>session_start()</code>.</div><div class="actions"><a href="langkah1.php">Ulangi eksperimen</a></div></section>
<?php page_end(); ?>
