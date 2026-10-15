<?php
// Konfigurasi dasar aplikasi Kolam Renang Al Ma'soem.
// Support .env / environment variable untuk deploy ke VM (tanpa ubah kode).

// Load .env sederhana jika ada (tanpa composer)
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k); $v = trim(trim($v), '"\'');
        if (getenv($k) === false) putenv("$k=$v");
        if (!isset($_ENV[$k])) $_ENV[$k] = $v;
        if (!isset($_SERVER[$k])) $_SERVER[$k] = $v;
    }
}
function env_val(string $key, $default = null) {
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($v !== false && $v !== '' && $v !== null) ? $v : $default;
}

// APP
define('APP_NAME', env_val('APP_NAME', "Kolam Renang Al Ma'soem"));
define('APP_SHORT', env_val('APP_SHORT', 'Kolam Renang'));
define('APP_LOGO', env_val('APP_LOGO', 'assets/images/logo.png'));
// BASE_URL: kosongkan jika DocumentRoot = /var/www/almapool (https://domain langsung jadi app)
// isi /almapool hanya jika diakses via https://example.com/almapool
// AUTO-DETECT: jika .env tidak set BASE_URL, tebak dari SCRIPT_NAME biar lokal (XAMPP htdocs/almapool) & VM dua-duanya jalan
$__base = env_val('BASE_URL', null);
if ($__base === null) {
    // deteksi subfolder dari SCRIPT_NAME, mis: /almapool/index.php -> /almapool, /index.php -> ""
    $__script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (str_starts_with($__script, '/almapool/')) $__base = '/almapool';
    else $__base = '';
}
if ($__base === '' || $__base === '/') $__base = '';
define('BASE_URL', rtrim($__base, '/'));

// Database (override via .env di VM)
define('DB_HOST', env_val('DB_HOST', '127.0.0.1'));
define('DB_PORT', env_val('DB_PORT', '3306'));
define('DB_NAME', env_val('DB_NAME', 'db_kolam_renang'));
define('DB_USER', env_val('DB_USER', 'root'));
define('DB_PASS', env_val('DB_PASS', ''));
define('DB_CHARSET', env_val('DB_CHARSET', 'utf8mb4'));

// Environment: production / development
define('APP_ENV', env_val('APP_ENV', 'production'));

date_default_timezone_set(env_val('APP_TIMEZONE', 'Asia/Jakarta'));

// Error handling: production jangan tampilkan error ke user
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
// Session: perangkat loket tetap login (tidak logout sendiri).
// Lifetime default 24 jam (86400 detik), bisa dioverride via .env SESSION_LIFETIME (detik).
// Sebelumnya default PHP: cookie hilang saat browser ditutup + session dihapus
// server setelah 24 menit idle (gc_maxlifetime=1440) -> keluhan "sering logout".
$__sessLife = (int) env_val('SESSION_LIFETIME', 86400);
if ($__sessLife < 3600) $__sessLife = 3600;
ini_set('session.gc_maxlifetime', (string) $__sessLife);
session_set_cookie_params([
    'lifetime' => $__sessLife,
    'path' => '/',
    'secure' => (APP_ENV === 'production' && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
