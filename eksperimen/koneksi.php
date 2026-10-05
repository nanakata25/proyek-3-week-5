<?php
declare(strict_types=1);

function demo_database(bool $selectDatabase = true): PDO {
    $host = getenv('EXP_DB_HOST') ?: '127.0.0.1';
    $port = getenv('EXP_DB_PORT') ?: '3306';
    $database = getenv('EXP_DB_DATABASE') ?: 'db_belajar';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) throw new RuntimeException('Nama database tidak valid.');
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4" . ($selectDatabase ? ";dbname={$database}" : '');
    return new PDO($dsn, getenv('EXP_DB_USER') ?: 'root', getenv('EXP_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
}
