<?php
// Script untuk mengosongkan semua isi database untuk testing
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/db.php';

try {
    $pdo = db();
    
    echo "=== Membersihkan Database untuk Testing ===\n\n";
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "✓ Foreign key checks dimatikan\n";
    
    // Truncate tabel data (bukan master)
    $tables = [
        'member_visits',
        'transaction_items',
        'transactions',
        'memberships',
        'members',
        'lesson_sessions',
        'lesson_students',
        'lesson_schedules',
        'operational_costs',
        'deposits',
        'instructors',
        'medicine_stock',
        'medicine_usage',
        'medicines',
        'medicine_categories',
        'medicine_units'
    ];
    
    foreach ($tables as $table) {
        try {
            $pdo->exec("TRUNCATE TABLE $table");
            echo "✓ Tabel $table dikosongkan\n";
        } catch (PDOException $e) {
            echo "⚠ Tabel $table dilewati: " . $e->getMessage() . "\n";
        }
    }
    
    // Hapus user demo, sisakan admin (id=1)
    $pdo->exec("DELETE FROM users WHERE id != 1");
    echo "✓ User demo dihapus, admin tetap tersimpan\n";
    
    // Enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "✓ Foreign key checks diaktifkan kembali\n";
    
    // Reset admin password
    $pdo->exec("UPDATE users SET password = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE id = 1");
    echo "✓ Password admin di-reset ke: admin123\n";
    
    echo "\n=== Database Berhasil Dikosongkan ===\n";
    echo "Login: admin / admin123\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
