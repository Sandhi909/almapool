<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    require_once __DIR__ . '/config.php';
    echo "config.php loaded\n";
    
    require_once __DIR__ . '/helpers.php';
    echo "helpers.php loaded\n";
    
    require_once __DIR__ . '/database/db.php';
    echo "db.php loaded\n";
    
    require_once __DIR__ . '/core/Model.php';
    echo "Model.php loaded\n";
    
    require_once __DIR__ . '/core/Auth.php';
    echo "Auth.php loaded\n";
    
    echo "All files loaded successfully\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
