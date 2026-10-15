<?php
// Koneksi PDO database kolam renang.
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // Paksa MySQL session ke WIB (+07:00) agar NOW()/CURRENT_TIMESTAMP konsisten dengan PHP Asia/Jakarta
        try { $pdo->exec("SET time_zone = '+07:00'"); } catch (Throwable $e) {}
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        if (defined('APP_ENV') && APP_ENV === 'production') {
            die('Koneksi database gagal. Hubungi administrator.');
        }
        die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
    return $pdo;
}
