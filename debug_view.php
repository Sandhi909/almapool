<?php
// Test laporan.php view directly with mock LAPORAN
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Create mock LAPORAN data
$LAPORAN = [
    'periode' => 'Oktober 2026',
    'tambahan' => 0,
    'saldo_awal' => ['bon' => 0, 'tunai' => 0],
    'tiket' => [
        ['nama' => 'Umum', 'warna' => 'Putih', 'ta' => 25000, 'tb' => 30000, 'tc' => 35000,
         'qa' => 8, 'qb' => 0, 'qc' => 0, 'pengunjung' => 8, 'subtotal' => 200000,
         'seri_prefix' => 'SC', 'seri_awal' => 114, 'seri_akhir' => 121],
    ],
    'member' => [
        ['jenis' => 'Bulanan', 'qg' => 0, 'qs' => 0, 'tg' => 0, 'ts' => 0, 'jg' => 0, 'js' => 0, 'jml' => 0],
    ],
    'sewa_alat' => 0,
    'les' => [
        ['jenis' => 'Les Private', 'tarif' => 450000, 'qputra' => 0, 'qputri' => 0, 'jpra' => 0, 'jpri' => 0, 'jml' => 0],
    ],
    'biaya' => [
        ['nama' => 'Backwash', 'jumlah' => 20000],
        ['nama' => 'Gaji', 'jumlah' => 50000],
    ],
    'setoran' => [
        ['nama' => 'QRIS', 'jumlah' => 190000],
        ['nama' => 'Transfer', 'jumlah' => 0],
        ['nama' => 'Setoran Uang', 'jumlah' => 10000],
    ],
    'saldo_akhir' => ['bon' => null, 'tunai' => null],
    'keterangan' => ['Tarif A', 'Tarif B', 'Tarif C'],
    'ttd' => [
        ['nama' => 'Admin', 'jabatan' => 'Admin'],
    ],
];

// Now try to process the view code
echo "Testing variable extraction from LAPORAN...\n";
echo "LAPORAN keys: " . implode(", ", array_keys($LAPORAN)) . "\n\n";

$L = $LAPORAN;
$n = fn($v) => number_format((float)$v, 0, ',', '.');

// Hitung total dari tiket rows
$totTiket = 0;
$totLbr = 0;
foreach ($L['tiket'] as $r) {
    $totTiket += (float)$r['subtotal'];
    $totLbr += ((float)$r['pengunjung'] ?? 0);
}

echo "Total Tiket: " . $n($totTiket) . "\n";
echo "Total Lbr: " . $n($totLbr) . "\n";

// Hitung total member
$totMember = 0;
$totQG = 0;
$totQS = 0;
foreach ($L['member'] as $r) {
    $totMember += ((float)$r['jml'] ?? 0);
    $totQG += ((float)$r['qg'] ?? 0);
    $totQS += ((float)$r['qs'] ?? 0);
}

echo "Total Member: " . $n($totMember) . "\n";

// Hitung total les & sewa
$totSewaLes = 0;
$totQPutra = 0;
$totQPutri = 0;
foreach ($L['les'] as $r) {
    $totSewaLes += ((float)$r['jml'] ?? 0);
    $totQPutra += ((float)$r['qputra'] ?? 0);
    $totQPutri += ((float)$r['qputri'] ?? 0);
}
$totSewaLes += (float)$L['sewa_alat'];

echo "Total Sewa Les: " . $n($totSewaLes) . "\n";

// Total penerimaan & biaya
$totPenerimaan = $totTiket + $totMember + $totSewaLes + (float)$L['tambahan'];
$totBiaya = 0;
foreach ($L['biaya'] as $b) {
    $totBiaya += (float)$b['jumlah'];
}

echo "Total Penerimaan: " . $n($totPenerimaan) . "\n";
echo "Total Biaya: " . $n($totBiaya) . "\n";

$bersih = $totPenerimaan - $totBiaya;

// Setoran
$cashbon = ((float)$L['setoran'][0]['jumlah'] ?? 0) + ((float)$L['setoran'][1]['jumlah'] ?? 0);
$keYab = $cashbon + ((float)$L['setoran'][2]['jumlah'] ?? 0);

echo "Cashbon: " . $n($cashbon) . "\n";
echo "Ke YAB: " . $n($keYab) . "\n";

echo "\nView variables extracted successfully!\n";
