<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/koneksi.php';
if (isset($_SESSION['user_id'])) redirect('dashboard.php');
$error = '';
$username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { http_response_code(419); $error = 'Form kedaluwarsa. Muat ulang halaman lalu coba lagi.'; }
    else {
        try {
            $pdo = demo_database();
            $stmt = $pdo->prepare('SELECT id, password, nama_lengkap FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['nama'] = $user['nama_lengkap'];
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                redirect('dashboard.php');
            }
            $error = 'Username atau password salah.';
        } catch (PDOException $errorDb) {
            http_response_code(503);
            $error = 'Database eksperimen belum tersedia. Jalankan seed_user.php melalui terminal dan periksa konfigurasi MySQL.';
        }
    }
}
page_start('Login dengan session PHP', 'Session · Autentikasi');
?>
<p class="lead">Password diverifikasi terhadap hash di MySQL. Browser hanya membawa ID sesi setelah autentikasi.</p>
<div class="grid"><section class="card"><h2>Masuk ke laboratorium</h2><?php if ($error): ?><p role="alert" class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><label for="username">Username</label><input id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required maxlength="50"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required><div class="actions"><button type="submit">Masuk</button></div></form></section><section class="card"><h2>Akun demonstrasi</h2><p>Username <code>budi</code><br>Password <code>rahasia123</code></p><p class="muted">Akun lokal ini dibuat oleh seed CLI. Coba password salah terlebih dahulu, kemudian login dengan password yang benar.</p><a href="dashboard.php">Coba membuka halaman terproteksi →</a></section></div>
<?php page_end(); ?>
