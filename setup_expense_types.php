<?php
// Create expense_types table & seed default data
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/db.php';

$pdo = db();

try {
    // Create table
    $pdo->exec("CREATE TABLE IF NOT EXISTS expense_types (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      is_active TINYINT(1) NOT NULL DEFAULT 1,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✓ expense_types table created/verified\n";
    
    // Check if already seeded
    $cnt = $pdo->query("SELECT COUNT(*) n FROM expense_types")->fetch()['n'];
    echo "  Current rows: $cnt\n";
    
    if ($cnt == 0) {
        // Seed data
        $pdo->exec("INSERT INTO expense_types (name, is_active, created_at) VALUES 
            ('Backwash', 1, NOW()),
            ('Listrik', 1, NOW()),
            ('Gaji', 1, NOW()),
            ('Penggantian Listrik', 1, NOW()),
            ('Makan PJ Rombongan', 1, NOW())");
        
        echo "✓ Seeded 5 default expense types\n";
    } else {
        echo "✓ Already seeded (skip)\n";
    }
    
    // Verify
    $rows = $pdo->query("SELECT id, name FROM expense_types ORDER BY id")->fetchAll();
    echo "\n  Expense types in DB:\n";
    foreach ($rows as $r) {
        echo "    [{$r['id']}] {$r['name']}\n";
    }
    
    echo "\n✓ SETUP COMPLETE\n";
    echo "\nNow try: http://localhost/almapool\n";
    
} catch (Throwable $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
