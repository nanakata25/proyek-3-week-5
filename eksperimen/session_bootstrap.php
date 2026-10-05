<?php
require_once __DIR__ . '/common.php';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('PHPSESSID');
session_set_cookie_params(['lifetime' => 0, 'path' => experiment_path(), 'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax']);
session_start();
if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function csrf_valid(): bool {
    return is_string($_POST['csrf'] ?? null) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
