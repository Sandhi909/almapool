<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/core/Model.php';

foreach (glob(__DIR__ . '/models/*.php') as $f) require_once $f;

// Test Report::monthly()
$rep = new Report();
$year = (int)date('Y');
$month = (int)date('m');

echo "Testing Report::monthly($year, $month)\n";
echo "================================================\n";

$repData = $rep->monthly($year, $month);

echo "Keys in repData: " . implode(", ", array_keys($repData)) . "\n\n";

echo "Tickets count: " . count($repData['tickets'] ?? []) . "\n";
if (!empty($repData['tickets'])) {
    echo "First ticket: " . json_encode($repData['tickets'][0], JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nMembers count: " . count($repData['members'] ?? []) . "\n";
if (!empty($repData['members'])) {
    echo "First member: " . json_encode($repData['members'][0], JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nLes count: " . count($repData['les'] ?? []) . "\n";
if (!empty($repData['les'])) {
    echo "First les: " . json_encode($repData['les'][0], JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nBiaya count: " . count($repData['biaya'] ?? []) . "\n";
if (!empty($repData['biaya'])) {
    foreach ($repData['biaya'] as $b) {
        echo "  - " . json_encode($b, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

echo "\nSewa: " . $repData['sewa'] . "\n";
echo "Tambahan: " . $repData['tambahan'] . "\n";
echo "TrxPay: " . json_encode($repData['trxPay'], JSON_UNESCAPED_UNICODE) . "\n";
