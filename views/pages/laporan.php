<?php
require_once __DIR__ . '/../layouts/header.php';

$L = $LAPORAN ?? [];
$n = fn($v) => number_format((float)$v, 0, ',', '.');

// Hitung total dari tiket rows
$totTiket = 0;
$totLbr = 0;
foreach ($L['tiket'] as $r) {
    $totTiket += (float)$r['subtotal'];
    $totLbr += ((float)$r['pengunjung'] ?? 0);
}

// Hitung total member
$totMember = 0;
$totQG = 0;
$totQS = 0;
foreach ($L['member'] as $r) {
    $totMember += ((float)$r['jml'] ?? 0);
    $totQG += ((float)$r['qg'] ?? 0);
    $totQS += ((float)$r['qs'] ?? 0);
}

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

// Total penerimaan & biaya
$totPenerimaan = $totTiket + $totMember + $totSewaLes + (float)$L['tambahan'];
$totBiaya = 0;
foreach ($L['biaya'] as $b) {
    $totBiaya += (float)$b['jumlah'];
}

$bersih = $totPenerimaan - $totBiaya;

// Setoran
$cashbon = ((float)$L['setoran'][0]['jumlah'] ?? 0) + ((float)$L['setoran'][1]['jumlah'] ?? 0);
$keYab = $cashbon + ((float)$L['setoran'][2]['jumlah'] ?? 0);

$tiketRows = $L['tiket'];
$memberRows = $L['member'];
$lesRows = $L['les'];
$title = 'Laporan ' . ucfirst($L['periode'] ?? 'Bulanan');
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> - <?= e(env_val('APP_NAME')) ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
  <style>
    body { background:#f8f9fa; color:#212529; }
    .container { max-width:1250px; margin:0 auto; }
    table { width:100%; border-collapse:collapse; margin-bottom:12px; background:#fff; font-size:12px; }
    th, td { border:1px solid #aaa; padding:4px 8px; }
    th { background:#e9ecef; font-weight:700; text-align:center; vertical-align:middle; }
    td.r { text-align:right; }
    td.c { text-align:center; }
    td.b { font-weight:700; }
    tr.sec td { background:#d4edda; }
    tr.sec-setoran td { background:#d1ecf1; }
    tr.sec-bop td { background:#fff3cd; }
    .mt-3 { margin-top:16px; }
    .print-btn { margin-bottom:16px; }
    @media print {
      .print-btn, .navbar { display:none; }
      body { background:#fff; }
      table { break-inside:avoid; page-break-inside:avoid; }
      tr.sec td, tr.sec-setoran td, tr.sec-bop td { background:#eee !important; }
    }
  </style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 print-btn">
    <a href="<?= e(url('?page=laporan')) ?>" class="btn btn-outline-secondary">← Kembali</a>
    <button onclick="window.print()" class="btn btn-primary">Print / Simpan PDF</button>
  </div>

  <div class="text-center mb-4">
    <h4 style="margin-bottom:4px;font-weight:700"><?= e($APP['nama_kolam']) ?></h4>
    <h6 style="margin-top:0;font-weight:700">LAPORAN <?= strtoupper($TYPE) ?></h6>
    <p style="margin:0;font-size:13px"><?= e($L['periode']) ?></p>
  </div>

  <table>
    <tbody>
      <tr class="sec"><td style="width:26px">I.</td><td>Rekapitulasi Tiket Harian</td>
        <td class="c" colspan="3">Tiket Tiket Masuk</td><td class="c" colspan="3">Pengunjung</td><td class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</th></tr>
      <tr><th style="width:26px">A</th><th>Jenis Tiket</th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th></th></tr>
    </tbody>
    <tbody>
      <?php foreach ($tiketRows as $r): $lbr=$r['qa']+$r['qb']+$r['qc']; ?>
      <tr>
        <td></td><td><?= e($r['nama']) ?></td>
        <td class="r"><?= e($n($r['ta'])) ?></td><td class="r"><?= e($n($r['qa'])) ?></td>
        <td class="r"><?= e($n($r['tb'])) ?></td><td class="r"><?= e($n($r['qb'])) ?></td>
        <td class="r"><?= e($n($r['tc'])) ?></td><td class="r"><?= e($n($r['qc'])) ?></td>
        <td class="r b"><?= e($n($lbr)) ?></td>
        <td class="r b"><?= e($n($r['jml'])) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr class="sec-bop"><td colspan="8">Total Penerimaan dari Tiket</td><td></td><td class="r b"><?= e($n($totTiket)) ?></td></tr>
      <tr class="sec-bop"><td colspan="8">Jumlah Tiket Keluar</td><td class="r b"><?= e($n($totLbr)) ?></td><td></td></tr>
    </tbody>
  </table>
  <table>
    <thead>
      <tr><th style="width:26px">B</th><th>Jenis Member</th><th class="c" colspan="3">Gold</th><th class="c" colspan="3">Silver</th><th class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</th></tr>
      <tr><th></th><th></th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($memberRows as $r): ?>
      <tr>
        <td></td><td><?= e($r['jenis']) ?></td>
        <td class="r"><?= e($n($r['tg'])) ?></td><td class="r"><?= e($n($r['qg'])) ?></td>
        <td class="r"><?= e($n($r['jg'])) ?></td>
        <td class="r"><?= e($n($r['ts'])) ?></td><td class="r"><?= e($n($r['qs'])) ?></td>
        <td class="r"><?= e($n($r['js'])) ?></td>
        <td class="r"><?= e($n($r['jml'])) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr class="sec-bop"><td></td><td>Total Penerimaan dari Member</td>
        <td></td><td class="r b"><?= e($n($totQG)) ?></td><td></td>
        <td></td><td class="r b"><?= e($n($totQS)) ?></td><td></td>
        <td class="r b"><?= e($n($totMember)) ?></td></tr>
    </tbody>
  </table>
  <table>
    <thead>
      <tr><th style="width:26px">C</th><th>Sewa Alat (Rp)</th><th>Jenis Les</th><th class="c">Tarif (Rp)</th><th class="c" colspan="2">Putra</th><th class="c" colspan="2">Putri</th><th class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</th></tr>
      <tr><th></th><th></th><th></th><th></th><th class="c">Qty</th><th class="c">Jumlah</th><th class="c">Qty</th><th class="c">Jumlah</th><th></th></tr>
    </thead>
    <tbody>
      <?php $nLes = max(1, count($lesRows)); $iLes = 0; foreach ($lesRows as $r): $jenisLes = preg_replace('/^les\s+/i', '', (string)($r['jenis'] ?? '')); ?>
      <tr>
        <td></td>
        <?php if ($iLes === 0): ?><td class="r" rowspan="<?= $nLes ?>"><?= e($n($L['sewa_alat'])) ?></td><?php endif; ?>
        <td><?= e($jenisLes) ?></td><td class="r"><?= e($n($r['tarif'])) ?></td>
        <td class="r"><?= e($n($r['qputra'])) ?></td><td class="r"><?= e($n($r['jpra'])) ?></td>
        <td class="r"><?= e($n($r['qputri'])) ?></td><td class="r"><?= e($n($r['jpri'])) ?></td>
        <td class="r"><?= e($n($r['jml'])) ?></td>
      </tr>
      <?php $iLes++; endforeach; ?>
      <tr class="sec-bop"><td></td><td colspan="3">Total Penerimaan dari Alat Sewa dan Les</td>
        <td class="r b"><?= e($n($totQPutra)) ?></td><td></td>
        <td class="r b"><?= e($n($totQPutri)) ?></td><td></td>
        <td class="r b"><?= e($n($totSewaLes)) ?></td></tr>
      <?php if ($tambahan > 0): ?>
      <tr><td></td><td colspan="7">Biaya Tambahan Silver Hari Libur</td><td class="r"><?= e($n($tambahan)) ?></td></tr>
      <?php endif; ?>
      <tr class="sec"><td></td><td colspan="7">Total Penerimaan</td><td class="r b"><?= e($n($totPenerimaan)) ?></td></tr>
    </tbody>
  </table>
  <table>
    <tbody>
      <tr class="sec"><td style="width:26px">III.</td><td>Biaya Operasional Kolam</td><td class="c" style="width:140px">Jumlah (Rp)</td></tr>
      <?php foreach ($L['biaya'] as $b): ?>
      <tr><td></td><td><?= e($b['nama']) ?></td><td class="r"><?= e($n($b['jumlah'])) ?></td></tr>
      <?php endforeach; ?>
      <tr class="sec"><td></td><td>Total Biaya Operasional</td><td class="r b"><?= e($n($totBiaya)) ?></td></tr>
      <tr class="sec"><td>IV.</td><td>Penerimaan di Kurang BOP Kolam</td><td class="r b"><?= e($n($bersih)) ?></td></tr>
    </tbody>
  </table>
  <table>
    <tbody>
      <tr class="sec-setoran"><td style="width:26px">V.</td><td>Setoran</td><td class="c">Keterangan</td><td class="c" style="width:140px">Jumlah (Rp)</td></tr>
      <tr><td></td><td rowspan="3">Cashbon disetor</td><td><?= e($L['setoran'][0]['nama']) ?></td><td class="r"><?= e($n($L['setoran'][0]['jumlah'])) ?></td></tr>
      <tr><td></td><td><?= e($L['setoran'][1]['nama']) ?></td><td class="r"><?= e($n($L['setoran'][1]['jumlah'])) ?></td></tr>
      <tr class="sec"><td></td><td>Total Cashbon</td><td class="r b"><?= e($n($cashbon)) ?></td></tr>
      <tr><td></td><td>Setoran Uang Tunai</td><td></td><td class="r"><?= e($n($L['setoran'][2]['jumlah'] - $totBiaya)) ?></td></tr>
      <tr class="sec"><td></td><td>Jumlah Setoran ke YAB</td><td></td><td class="r b"><?= e($n($keYab)) ?></td></tr>
    </tbody>
  </table>
  <table>
    <tbody>
      <tr class="sec"><td style="width:26px">VI.</td><td>Saldo Akhir Kas</td>
        <td class="c">Bon blm disetor (Rp)</td><td class="r" style="width:110px"><?= e($n($L['saldo_akhir']['bon'])) ?></td>
        <td class="c">Uang Tunai (Rp)</td><td class="r" style="width:110px"><?= e($n($L['saldo_akhir']['tunai'])) ?></td>
        <td class="c" style="width:70px">Total</td><td class="r" style="width:110px">-</td></tr>
    </tbody>
  </table>

  <div class="mt-3" style="margin-top:40px">
    <b>Keterangan :</b>
    <?php foreach ($L['keterangan'] as $k): ?><div style="margin-left:20px;font-size:10px">- <?= e($k) ?></div><?php endforeach; ?>
  </div>

  <table style="margin-top:48px;border:0;width:100%">
    <tbody>
      <tr>
        <?php $wTtd = $L['ttd'] ? (100 / count($L['ttd'])) : 33; foreach ($L['ttd'] as $t): ?>
        <td class="c" style="border:0;width:<?= $wTtd ?>%;padding:0;vertical-align:top">
          <div style="height:72px"></div>
          <div style="border-top:1px solid #333;padding-top:4px;font-size:10px;font-weight:700"><?= e($t['nama']) ?></div>
          <div style="font-size:9px"><?= e($t['jabatan']) ?></div>
        </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>