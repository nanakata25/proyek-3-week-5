<?php
require __DIR__ . '/session_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) { http_response_code(405); header('Allow: POST'); page_start('Logout melalui dashboard', 'Session'); echo '<p>Gunakan tombol Logout pada dashboard untuk mengakhiri session.</p><a href="dashboard.php">Kembali ke dashboard</a>'; page_end(); exit; }
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'domain' => $params['domain'], 'secure' => $params['secure'], 'httponly' => $params['httponly'], 'samesite' => $params['samesite']]);
session_destroy();
redirect('logout_selesai.php');
