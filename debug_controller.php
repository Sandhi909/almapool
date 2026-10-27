<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Auth.php';

foreach (glob(__DIR__ . '/models/*.php') as $f) require_once $f;
foreach (glob(__DIR__ . '/controllers/*.php') as $f) require_once $f;

// Simulate laporan page request
$_GET['page'] = 'laporan';
$_GET['type'] = 'bulanan';

// Create controller and get data
$controller = new LaporanController();
$data = $controller->index();

echo "Keys in data array:\n";
print_r(array_keys($data));

echo "\n\nChecking LAPORAN key:\n";
if (isset($data['LAPORAN'])) {
    echo "LAPORAN exists!\n";
    $laporan = $data['LAPORAN'];
    echo "Type: " . gettype($laporan) . "\n";
    if (is_array($laporan)) {
        echo "Keys in LAPORAN: " . implode(", ", array_keys($laporan)) . "\n";
        echo "Tiket array count: " . count($laporan['tiket'] ?? []) . "\n";
        echo "Member array count: " . count($laporan['member'] ?? []) . "\n";
        echo "Les array count: " . count($laporan['les'] ?? []) . "\n";
    }
} else {
    echo "ERROR: LAPORAN key NOT in data array!\n";
}

// Now extract as the real page does
echo "\n\nSimulating extract(\$data, EXTR_SKIP):\n";
extract($data, EXTR_SKIP);

if (isset($LAPORAN)) {
    echo "After extract: \$LAPORAN is defined\n";
    echo "Type: " . gettype($LAPORAN) . "\n";
    if (is_array($LAPORAN)) {
        echo "First few keys: " . implode(", ", array_slice(array_keys($LAPORAN), 0, 5)) . "\n";
    }
} else {
    echo "After extract: \$LAPORAN is NOT defined\n";
}
