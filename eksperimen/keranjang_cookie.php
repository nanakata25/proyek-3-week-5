<?php
require __DIR__ . '/common.php';
$produk = [1 => ['nama' => 'Buku Tulis', 'harga' => 5000], 2 => ['nama' => 'Pulpen', 'harga' => 3000], 3 => ['nama' => 'Penggaris', 'harga' => 4000]];
$raw = is_string($_COOKIE['keranjang'] ?? null) ? $_COOKIE['keranjang'] : '{}';
$keranjang = json_decode($raw, true);
if (!is_array($keranjang)) $keranjang = [];
if (isset($_GET['tambah'])) {
    $id = (int)$_GET['tambah'];
    if (isset($produk[$id])) { $keranjang[$id] = (int)($keranjang[$id] ?? 0) + 1; demo_cookie('keranjang', json_encode($keranjang), time() + 3600, false); }
    redirect('keranjang_cookie.php');
}
if (isset($_GET['kosongkan'])) { demo_cookie('keranjang', '', time() - 3600, false); redirect('keranjang_cookie.php'); }
page_start('Keranjang berbasis cookie', 'Cookies · Demo manipulasi data klien');
?>
<div class="warning"><strong>Demo sengaja tidak memvalidasi kuantitas.</strong> Keranjang ini tidak melakukan checkout, pembayaran, atau perubahan stok. Ubah kuantitas hanya pada aplikasi lokal ini untuk mempelajari batas kepercayaan data browser.</div>
<div class="grid"><section class="card"><h2>Daftar produk</h2><ul class="products"><?php foreach ($produk as $id => $p): ?><li><span><strong><?= e($p['nama']) ?></strong><?= rupiah($p['harga']) ?></span><a class="button" href="?tambah=<?= $id ?>">Tambah</a></li><?php endforeach; ?></ul></section><section class="card"><h2>Keranjang Anda</h2><?php $total = 0; if (!$keranjang): ?><p>Keranjang kosong.</p><?php else: ?><ul><?php foreach ($keranjang as $id => $jumlah): if (!isset($produk[$id]) || !is_scalar($jumlah)) continue; $sub = $produk[$id]['harga'] * (int)$jumlah; $total += $sub; ?><li><?= e($produk[$id]['nama']) ?> × <?= (int)$jumlah ?> = <?= rupiah($sub) ?></li><?php endforeach; ?></ul><?php endif; ?><p class="summary">Total: <?= rupiah($total) ?></p><a href="?kosongkan=1">Kosongkan keranjang</a></section></div>
<section class="card"><h2>Cookie yang diterima server</h2><pre id="cookie-json"><?= e($raw) ?></pre><p>Tambahkan Buku Tulis 2 kali dan Pulpen 1 kali: total yang diharapkan <strong>Rp 13.000</strong>. Melalui DevTools, ubah JSON menjadi <code>{"1":999,"2":1}</code> lalu refresh: total yang diharapkan <strong>Rp 4.998.000</strong>.</p><p>Nama cookie <code>keranjang</code>, Path <code><?= e(experiment_path()) ?></code>, berlaku 1 jam, HttpOnly=false untuk pengamatan skrip. Nilai mentah di DevTools dapat tampil ter-URL-encode; gunakan panel edit decoded bila tersedia.</p></section>
<?php page_end(); ?>
