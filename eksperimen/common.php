<?php
declare(strict_types=1);

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function rupiah(int $value): string { return 'Rp ' . number_format($value, 0, ',', '.'); }
function is_https(): bool { return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'; }
function experiment_path(): string {
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/eksperimen/index.php'));
    return rtrim($directory, '/') . '/';
}
function demo_cookie(string $name, string $value, int $expires, bool $httpOnly = true): void {
    setcookie($name, $value, ['expires' => $expires, 'path' => experiment_path(), 'secure' => is_https(), 'httponly' => $httpOnly, 'samesite' => 'Lax']);
}
function page_start(string $title, string $step): void {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' · Lab Modul 4</title><link rel="stylesheet" href="assets/style.css"></head><body><header><a href="index.php">Lab Penyimpanan Web</a><span class="pill">MODUL 04</span></header><main><div class="eyebrow">' . e($step) . '</div><h1>' . e($title) . '</h1>';
}
function page_end(): void { echo '</main><footer>Praktikum Modul 4 · Cookies, Session, dan Local Storage · Laboratorium lokal</footer></body></html>'; }
function redirect(string $location): void { header('Location: ' . $location, true, 302); exit; }
