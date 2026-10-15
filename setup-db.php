<?php
// Setup database: create DB, import schema, seed data
// AMAN: diblok .htaccess di production. Hapus file ini setelah install di VM.
if (defined('APP_ENV') && APP_ENV === 'production' && php_sapi_name() !== 'cli' && empty($_GET['allow'])) {
    http_response_code(403);
    die('Setup diblok di production. Hapus setup-db.php setelah install atau jalankan via CLI.');
}
if (session_status() !== PHP_SESSION_NONE) session_destroy();

try {
    // Koneksi ke MySQL server (tanpa DB dulu)
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Baca schema.sql
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Jalankan query dalam schema
    $statements = array_filter(array_map('trim', explode(';', $schema)), fn($s) => !empty($s) && !str_starts_with($s, '--'));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) $pdo->exec($stmt . ';');
    }
    
    echo "✓ Database & schema berhasil dibuat.\n";
    echo "✓ Jalankan seed: curl http://localhost:8000/database/seed.php\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
