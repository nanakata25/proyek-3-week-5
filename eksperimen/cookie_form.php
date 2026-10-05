<?php
// Shared implementation: direct access is not a separate experiment.
if (!isset($counterAtTop)) { http_response_code(404); exit; }
$nameKey = $counterAtTop ? 'nama_atas' : 'nama';
$counterKey = $counterAtTop ? 'kunjungan_atas' : 'kunjungan';
$target = $counterAtTop ? 'cookie_dasar_atas.php' : 'cookie_dasar.php';
$kunjungan = (int)($_COOKIE[$counterKey] ?? 0);
// Variant: both POST and redirected GET execute this block.
if ($counterAtTop) { $kunjungan++; demo_cookie($counterKey, (string)$kunjungan, time() + 86400 * 7); }
if (isset($_POST['nama']) && is_string($_POST['nama']) && trim($_POST['nama']) !== '') {
    demo_cookie($nameKey, mb_substr(trim($_POST['nama']), 0, 100), time() + 86400 * 7);
    redirect($target);
}
if (isset($_GET['hapus'])) { demo_cookie($nameKey, '', time() - 3600); redirect($target); }
// Normal version: only a request rendering HTML increments the counter.
if (!$counterAtTop) { $kunjungan++; demo_cookie($counterKey, (string)$kunjungan, time() + 86400 * 7); }
$nama = is_string($_COOKIE[$nameKey] ?? null) ? $_COOKIE[$nameKey] : null;
page_start($counterAtTop ? 'Cookie: penghitung di atas exit' : 'Cookie: penghitung setelah exit', 'Cookies · Urutan kode dan redirect');
?>
<p class="lead">Satu klik Simpan menghasilkan POST → 302 → GET → 200. Posisi blok penghitung menentukan request mana yang ikut dihitung.</p>
<div class="grid"><section class="card"><h2>Halo, <?= e($nama ?: 'tamu') ?>!</h2><form method="post"><label for="nama">Nama Anda</label><input id="nama" name="nama" value="<?= e($nama ?? '') ?>" placeholder="Budi" required maxlength="100"><div class="actions"><button type="submit">Simpan</button><?php if ($nama): ?><a href="?hapus=1">Lupakan saya</a><?php endif; ?></div></form></section><section class="card"><p class="muted">Kunjungan terhitung</p><div class="number" id="kunjungan"><?= $kunjungan ?></div><p><?= $counterAtTop ? 'POST/GET redirect ikut dihitung: Simpan atau Lupakan saya menambah 2.' : 'Request redirect berhenti sebelum penghitung: Simpan atau Lupakan saya menambah 1.' ?></p><span class="pill"><?= $counterAtTop ? 'VARIAN +2' : 'NORMAL +1' ?></span></section></div>
<section class="card"><h2>Data masuk pada request ini</h2><p class="small muted">Nilai cookie yang baru disetel baru kembali melalui header Cookie pada request berikutnya. Penghitung tampilan berasal dari variabel yang sudah dinaikkan.</p><pre><?= e(json_encode([$nameKey => $_COOKIE[$nameKey] ?? null, $counterKey => $_COOKIE[$counterKey] ?? null], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre><p>Path: <code><?= e(experiment_path()) ?></code> · Masa berlaku: 7 hari · HttpOnly: ya · SameSite: Lax · Secure: <?= is_https() ? 'ya (HTTPS)' : 'tidak (HTTP lokal)' ?></p><a href="<?= $counterAtTop ? 'cookie_dasar.php' : 'cookie_dasar_atas.php' ?>">Buka <?= $counterAtTop ? 'versi normal (+1)' : 'varian penghitung di atas (+2)' ?> →</a></section>
<?php page_end(); ?>
