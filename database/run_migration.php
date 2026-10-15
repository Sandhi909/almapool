<?php
// Script untuk menjalankan migration obat
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/db.php';

try {
    $pdo = db();

    // Run medicine tables migration
    $sql1 = file_get_contents(__DIR__ . '/migrations/add_medicine_tables.sql');
    $pdo->exec($sql1);
    echo "✓ Tabel medicine (medicines, medicine_usage, medicine_stock) berhasil dibuat.\n";

    // Run categories/units migration
    $sql2 = file_get_contents(__DIR__ . '/migrations/add_medicine_categories_units.sql');
    $pdo->exec($sql2);
    echo "✓ Tabel kategori dan satuan obat berhasil dibuat.\n";

    // Run update migration (remove price, add foreign keys)
    try {
        $sql3 = file_get_contents(__DIR__ . '/migrations/update_medicine_remove_price.sql');
        $pdo->exec($sql3);
        echo "✓ Update tabel medicine (hapus harga, tambah foreign key) berhasil.\n";
    } catch (PDOException $e) {
        // Might fail if already run or columns don't exist yet
        echo "⚠ Update migration dilewati (mungkin sudah dijalankan): " . $e->getMessage() . "\n";
    }

    echo "\nMigration selesai!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
