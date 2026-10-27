<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/helpers.php';
    require_once __DIR__ . '/database/db.php';
    require_once __DIR__ . '/core/Model.php';
    require_once __DIR__ . '/core/Auth.php';
    
    echo "Loading models...\n";
    foreach (glob(__DIR__ . '/models/*.php') as $f) {
        echo "  Loading: " . basename($f) . "\n";
        require_once $f;
    }
    
    echo "Loading controllers...\n";
    foreach (glob(__DIR__ . '/controllers/*.php') as $f) {
        echo "  Loading: " . basename($f) . "\n";
        require_once $f;
    }
    
    echo "\nAll files loaded!\n";
    
    // Check if LaporanController exists
    if (class_exists('LaporanController')) {
        echo "LaporanController class exists\n";
        
        // Try to instantiate
        $ctrl = new LaporanController();
        echo "LaporanController instantiated\n";
        
        // Check if index method exists
        if (method_exists($ctrl, 'index')) {
            echo "index() method exists\n";
        } else {
            echo "ERROR: index() method not found\n";
        }
    } else {
        echo "ERROR: LaporanController class not found\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
