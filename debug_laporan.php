<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/core/Model.php';

// Check DB connection
try {
    $dbConn = db();
    echo "DB connected OK\n";
    
    // Check if tables exist
    $tables = ['tickets', 'transaction_items', 'transactions', 'operational_costs', 'expense_types'];
    foreach ($tables as $t) {
        $exists = $dbConn->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'db_kolam_renang' AND TABLE_NAME = '$t'")->fetch();
        echo "Table '$t': " . ($exists ? "EXISTS" : "MISSING") . "\n";
    }
    
    // Check ticket data
    $ticketCount = $dbConn->query("SELECT COUNT(*) c FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE ti.item_type = 'TIKET' AND DATE_FORMAT(t.created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') AND t.payment_status = 'PAID'")->fetch();
    echo "Tickets this month: " . ($ticketCount['c'] ?? 0) . "\n";
    
    // Check member data
    $memberCount = $dbConn->query("SELECT COUNT(*) c FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE ti.item_type = 'MEMBER' AND DATE_FORMAT(t.created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') AND t.payment_status = 'PAID'")->fetch();
    echo "Members this month: " . ($memberCount['c'] ?? 0) . "\n";
    
    // Check operational costs data
    $costCount = $dbConn->query("SELECT COUNT(*) c FROM operational_costs WHERE DATE_FORMAT(cost_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")->fetch();
    echo "Operational costs this month: " . ($costCount['c'] ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
