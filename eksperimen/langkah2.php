<?php require __DIR__ . '/common.php'; $nama = is_string($_POST['nama'] ?? null) ? $_POST['nama'] : null; page_start('Langkah 2: Server menerima data', 'HTTP stateless · Request 2'); ?>
<section class="card"><h2>Halo, <?= e($nama ?? 'tidak diketahui') ?>!</h2><p>Nama pada halaman ini dibaca dari <code>$_POST['nama']</code> di request saat ini.</p><pre>Metode request: <?= e($_SERVER['REQUEST_METHOD']) ?>
Nama yang diterima: <?= e($nama ?? '(tidak ada)') ?></pre><a class="button" href="langkah3.php">Lanjut ke Langkah 3 →</a></section>
<?php page_end(); ?>
