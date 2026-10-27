<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Test if we can run the LaporanController
$page = 'laporan';

// Include the necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Auth.php';

foreach (glob(__DIR__ . '/models/*.php') as $f) require_once $f;
foreach (glob(__DIR__ . '/controllers/*.php') as $f) require_once $f;

// Set test parameters
$_GET['page'] = 'laporan';
$_GET['type'] = 'bulanan';

// Run the LaporanController
$controller = new LaporanController();

try {
    $result = $controller->index();
    echo "LaporanController returned successfully!\n";
    echo "Keys in result: " . implode(", ", array_keys($result)) . "\n";
    
    // Check if LAPORAN key exists
    if (isset($result['LAPORAN'])) {
        echo "LAPORAN data exists.\n";
        $laporan = $result['LAPORAN'];
        echo "Keys in LAPORAN: " . implode(", ", array_keys($laporan)) . "\n";
        
        // Check if required keys exist
        $requiredKeys = ['tiket', 'member', 'les', 'biaya', 'setoran', 'saldo_awal'];
        foreach ($requiredKeys as $key) {
            if (isset($laporan[$key])) {
                echo "Key '$key' exists.\n";
            } else {
                echo "ERROR: Key '$key' is missing!\n";
            }
        }
    } else {
        echo "ERROR: LAPORAN key is missing!\n";
    }
} catch (Exception $e) {
    echo "ERROR: Exception occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}