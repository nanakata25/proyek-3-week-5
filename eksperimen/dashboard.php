<?php
require __DIR__ . '/session_bootstrap.php';
if (!isset($_SESSION['user_id'])) redirect('login.php');
page_start('Selamat datang, ' . $_SESSION['nama'] . '!', 'Session · Halaman terproteksi');
?>
<section class="card"><span class="pill">LOGIN BERHASIL</span><h2>Identitas tersedia dari session server</h2><p>Halaman ini hanya dapat dibuka setelah login. Nama di judul diambil dari <code>$_SESSION['nama']</code>; nilai tersebut tidak disimpan dalam cookie login.</p><table><thead><tr><th>Data</th><th>Lokasi penyimpanan</th></tr></thead><tbody><tr><td>user_id = <?= (int)$_SESSION['user_id'] ?></td><td>Session server</td></tr><tr><td>nama = <?= e($_SESSION['nama']) ?></td><td>Session server</td></tr><tr><td>PHPSESSID</td><td>Cookie browser berisi ID sesi acak (HttpOnly, SameSite=Lax)</td></tr><tr><td>Password</td><td>Hash di database; tidak disalin ke session atau storage browser</td></tr></tbody></table><form method="post" action="logout.php" class="actions"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><button type="submit">Logout</button></form></section>
<div class="notice">Percobaan: hapus cookie PHPSESSID di DevTools, lalu refresh halaman. Tanpa ID sesi yang valid, server mengalihkan Anda ke login.</div>
<?php page_end(); ?>
