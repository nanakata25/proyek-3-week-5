<?php
declare(strict_types=1);
// This file is never executable through HTTP. Run from a local terminal only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/koneksi.php';
$pdo = demo_database(false);
$database = getenv('EXP_DB_DATABASE') ?: 'db_belajar';
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$database}`");
$pdo->exec('CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, nama_lengkap VARCHAR(100) NOT NULL) ENGINE=InnoDB');
$statement = $pdo->prepare('INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password), nama_lengkap = VALUES(nama_lengkap)');
$statement->execute(['budi', password_hash('rahasia123', PASSWORD_DEFAULT), 'Budi Santoso']);
echo "Akun demonstrasi budi dibuat/diperbarui pada {$database}. Password demo: rahasia123.\n";
