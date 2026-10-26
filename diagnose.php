<?php
// Diagnostic script untuk cek masalah almapool
echo "=== ALMAPOOL DIAGNOSTIC ===\n\n";

// 1. Cek file utama
$files = [
    'config.php',
    'helpers.php',
    'database/db.php',
    'core/Model.php',
    'core/Auth.php',
    'models/',
    'controllers/',
    'views/pages/',
    'views/layouts/',
];

echo "1. CHECKING FILES:\n";
foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    $exists = file_exists($path) ? 'OK' : 'MISSING';
    echo "  $f ... $exists\n";
}

// 2. Try load config
echo "\n2. LOADING CONFIG:\n";
try {
    require_once __DIR__ . '/config.php';
    echo "  config.php loaded OK\n";
    echo "  BASE_URL: " . BASE_URL . "\n";
    echo "  DB_HOST: " . DB_HOST . "\n";
    echo "  DB_NAME: " . DB_NAME . "\n";
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Try connect to DB
echo "\n3. DATABASE CONNECTION:\n";
try {
    require_once __DIR__ . '/database/db.php';
    $pdo = db();
    echo "  Connected OK\n";
    
    // Check tables
    $tables = ['users', 'members', 'transactions', 'operational_costs', 'expense_types'];
    echo "\n  TABLE STATUS:\n";
    foreach ($tables as $t) {
        $r = $pdo->query("SELECT COUNT(*) n FROM $t")->fetch();
        $cnt = $r['n'] ?? '?';
        echo "    $t: $cnt rows\n";
    }
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Check models
echo "\n4. LOADING MODELS:\n";
try {
    require_once __DIR__ . '/core/Model.php';
    echo "  core/Model.php loaded OK\n";
    
    foreach (glob(__DIR__ . '/models/*.php') as $f) {
        require_once $f;
        $name = basename($f);
        echo "  models/$name loaded OK\n";
    }
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Check controllers
echo "\n5. LOADING CONTROLLERS:\n";
try {
    foreach (glob(__DIR__ . '/controllers/*.php') as $f) {
        require_once $f;
        $name = basename($f);
        echo "  controllers/$name loaded OK\n";
    }
} catch (Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== ALL CHECKS PASSED ===\n";
echo "\nIf site still won't load, check:\n";
echo "1. Apache error log: C:\\xampp\\apache\\logs\\error.log\n";
echo "2. Browser console (F12)\n";
echo "3. Permissions on almapool folder\n";
