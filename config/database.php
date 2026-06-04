<?php
define('DB_HOST', '10.10.9.212');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'finance_db');
define('WEB_NAME', 'Woycx');
define('WEB_DOMAIN', $_SERVER["HTTP_HOST"]);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}