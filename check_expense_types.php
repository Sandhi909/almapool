<?php
require_once __DIR__ . '/database/db.php';

try {
    $stmt = db()->query("SELECT * FROM expense_types ORDER BY id");
    $types = $stmt->fetchAll();
    
    echo "<h3>Expense Types:</h3>";
    if (empty($types)) {
        echo "<p style='color:red'>KOSONG! Tabel expense_types tidak ada data.</p>";
        echo "<p>Jalankan migration: database/migrations/add_operational_costs_expense_types.sql</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Active</th><th>Created</th></tr>";
        foreach ($types as $t) {
            echo "<tr>";
            echo "<td>{$t['id']}</td>";
            echo "<td>{$t['name']}</td>";
            echo "<td>{$t['is_active']}</td>";
            echo "<td>{$t['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr><h3>Operational Costs:</h3>";
    $stmt2 = db()->query("SELECT oc.*, et.name as type_name FROM operational_costs oc LEFT JOIN expense_types et ON oc.expense_type_id = et.id ORDER BY oc.id DESC LIMIT 10");
    $costs = $stmt2->fetchAll();
    
    if (empty($costs)) {
        echo "<p>Belum ada data operational costs.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Date</th><th>Type</th><th>Amount</th><th>Notes</th><th>Created By</th></tr>";
        foreach ($costs as $c) {
            echo "<tr>";
            echo "<td>{$c['id']}</td>";
            echo "<td>{$c['cost_date']}</td>";
            echo "<td>{$c['type_name']}</td>";
            echo "<td>{$c['amount']}</td>";
            echo "<td>{$c['notes']}</td>";
            echo "<td>{$c['created_by']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<p>Table belum ada. Jalankan migration dulu.</p>";
}
