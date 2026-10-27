<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/helpers.php';
    require_once __DIR__ . '/database/db.php';
    require_once __DIR__ . '/core/Model.php';
    require_once __DIR__ . '/core/Auth.php';
    
    foreach (glob(__DIR__ . '/models/*.php') as $f) require_once $f;
    foreach (glob(__DIR__ . '/controllers/*.php') as $f) require_once $f;
    
    // Simulate GET params
    $_GET['page'] = 'laporan';
    $_GET['type'] = 'bulanan';
    $_GET['bulan'] = date('n');
    $_GET['tahun'] = date('Y');
    
    // Set $_SERVER for require_role check
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Mock auth_role and auth_user
    if (!function_exists('auth_role')) {
        function auth_role() { return 'admin'; }
    }
    if (!function_exists('auth_user')) {
        function auth_user() { return ['id' => 1, 'name' => 'Test']; }
    }
    
    $ctrl = new LaporanController();
    echo "Calling index()...\n";
    $result = $ctrl->index();
    
    echo "Result type: " . gettype($result) . "\n";
    echo "Result keys: " . implode(", ", array_keys($result)) . "\n";
    
    if (isset($result['LAPORAN'])) {
        echo "\nLAPORAN key exists\n";
        $lap = $result['LAPORAN'];
        echo "LAPORAN type: " . gettype($lap) . "\n";
        echo "LAPORAN keys: " . implode(", ", array_keys($lap)) . "\n";
    } else {
        echo "\nERROR: LAPORAN key missing!\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
