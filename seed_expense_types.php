<?php
// Seed script for expense_types table
// Default expense types: Backwash, Listrik, Gaji, Penggantian Listrik, Makan PJ Rombongan

require_once 'vendor/autoload.php';

// Load the Model class to get database connection
$autoload = true;

// Database connection
$host = 'localhost';
$dbname = 'db_kolam_renang';
$username = 'root';
$password = ''; // adjust as needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Seed expense_types table with default data
    $sql = "INSERT INTO expense_types (name, is_active, created_at) VALUES 
            ('Backwash', 1, NOW()),
            ('Listrik', 1, NOW()),
            ('Gaji', 1, NOW()),
            ('Penggantian Listrik', 1, NOW()),
            ('Makan PJ Rombongan', 1, NOW());";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $inserted = $stmt->rowCount();
    echo "Seeded $inserted expense types successfully.\n";

    // Display inserted data
    $stmt = $pdo->query("SELECT id, name, is_active, created_at FROM expense_types ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nInserted expense types:\n";
    foreach ($rows as $row) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Active: {$row['is_active']}, Created: {$row['created_at']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
"