<?php require __DIR__ . '/common.php'; page_start('Langkah 1: Perkenalkan diri', 'HTTP stateless · Request 1'); ?>
<section class="card"><p>Nama dikirim hanya melalui form ini. Belum ada cookie, session, atau storage yang menyimpan nama.</p><form method="post" action="langkah2.php"><label for="nama">Nama Anda</label><input id="nama" name="nama" placeholder="Budi" maxlength="100" required><div class="actions"><button type="submit">Kirim ke server</button></div></form></section>
<?php page_end(); ?>
