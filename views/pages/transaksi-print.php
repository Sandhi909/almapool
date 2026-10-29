<?php
// Print view for transaksi harian - standalone page via index.php?page=transaksi-print
$filter = ['date' => get_param('tgl', date('Y-m-d')), 'type' => get_param('jenis', ''),
    'method' => get_param('metode', ''), 'q' => get_param('q', '')];

$sum = (new Transaction())->todaySummary($filter['date']);
$biayaHarian = (new OpCost())->totalDaily($filter['date']);
$tunaiBersih = $sum['by']['TUNAI'] - $biayaHarian;
$TRANSAKSI = (new Transaction())->listAll($filter);

$periode = hari_tanggal_indo($filter['date']);
$APP_NAME = defined('APP_NAME') ? APP_NAME : 'Kolam Renang Al Ma\'soem';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Transaksi Harian - <?= e($periode) ?></title>
<style>
  @page { margin: 12mm 10mm; size: A4; }
  * { box-sizing: border-box; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10.5px; color: #111; margin: 0; padding: 0; line-height: 1.4; }
  
  /* Header */
  .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #1a1a2e; padding-bottom: 8px; }
  .header .logo { height: 50px; margin-bottom: 4px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; letter-spacing: 1px; }
  .header .subtitle { font-size: 11px; color: #444; margin-top: 2px; font-weight: 500; }
  .header .periode { font-size: 10px; color: #666; margin-top: 1px; }
  
  /* Info row */
  .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 10px; padding: 4px 0; border-bottom: 1px dashed #ddd; }
  .info-row .label { font-weight: 600; color: #333; }
  .info-row .value { color: #111; }
  
  /* Summary Grid */
  .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 12px; }
  @media print { .summary-grid { grid-template-columns: repeat(4, 1fr); } }
  .sum-card { border: 1px solid #ddd; border-radius: 4px; padding: 8px 6px; text-align: center; background: #fafafa; }
  .sum-card .label { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; font-weight: 600; }
  .sum-card .value { font-size: 13px; font-weight: 700; font-variant-numeric: tabular-nums; font-family: 'Segoe UI', monospace; }
  .sum-card .value.primary { color: #1a1a2e; }
  .sum-card .value.cash { color: #198754; }
  .sum-card .value.digital { color: #0d6efd; }
  .sum-card .value.expense { color: #dc3545; }
  .sum-card .value.net { color: #198754; }
  .sum-card .value.net.neg { color: #dc3545; }
  
  /* Table */
  table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9.5px; }
  th, td { border: 1px solid #bbb; padding: 4px 5px; vertical-align: middle; }
  thead th { background: #1a1a2e; color: #fff; font-weight: 700; text-align: center; font-size: 9px; padding: 5px 3px; }
  tbody tr:nth-child(even) { background: #fafafa; }
  tbody tr:hover { background: #f0f4f8; }
  td.num { text-align: right; font-variant-numeric: tabular-nums; font-family: 'Segoe UI', monospace; }
  td.center { text-align: center; }
  td.bold { font-weight: 600; }
  
  /* Badges */
  .badge { display: inline-block; padding: 1px 6px; border-radius: 999px; font-size: 8px; font-weight: 600; line-height: 1.3; white-space: nowrap; }
  .badge-type { background: #cfe2ff; color: #084298; }
  .badge-area-putra { background: #cfe2ff; color: #084298; }
  .badge-area-putri { background: #ffeeba; color: #856404; }
  .badge-area-campur { background: #d4edda; color: #155724; }
  .badge-cash { background: #e2e3e5; color: #383d41; }
  .badge-digital { background: #cfe2ff; color: #084298; }
  .badge-paid { background: #d4edda; color: #155724; }
  .badge-cancelled { background: #f8d7da; color: #721c24; }
  
  .detail-small { display: block; font-size: 8px; color: #666; margin-top: 1px; line-height: 1.2; }
  
  /* Totals row */
  tfoot td { background: #f0f0f0; font-weight: 700; border-top: 2px solid #1a1a2e; }
  
  /* Footer */
  .footer { margin-top: 15px; text-align: center; font-size: 8.5px; color: #666; border-top: 1px solid #ccc; padding-top: 8px; }
  .footer .sig-line { display: inline-block; width: 180px; border-top: 1px solid #333; margin: 15px 20px 0; padding-top: 3px; font-size: 8px; font-weight: 600; }
  
  @media print { 
    body { margin: 0; padding: 0; }
    .no-print { display: none !important; }
    .summary-grid { grid-template-columns: repeat(4, 1fr) !important; }
  }
</style>
</head>
<body>
<div class="header">
  <?php if (file_exists(__DIR__ . '/../../../' . APP_LOGO)): ?>
    <img src="<?= url(APP_LOGO) ?>" alt="Logo" class="logo">
  <?php endif; ?>
  <h1><?= e($APP_NAME) ?></h1>
  <div class="subtitle">LAPORAN TRANSAKSI HARIAN</div>
  <div class="periode"><?= e($periode) ?></div>
</div>

<div class="info-row">
  <span class="label">Tanggal Cetak:</span>
  <span class="value"><?= hari_tanggal_indo() ?> <?= date('H:i') ?> WIB</span>
</div>
<?php if ($filter['type'] || $filter['method'] || $filter['q']): ?>
<div class="info-row">
  <span class="label">Filter:</span>
  <span class="value">
    <?php if ($filter['type']): ?>Jenis: <?= e($filter['type']) ?> &nbsp;|&nbsp; <?php endif; ?>
    <?php if ($filter['method']): ?>Metode: <?= e($filter['method']) ?> &nbsp;|&nbsp; <?php endif; ?>
    <?php if ($filter['q']): ?>Pencarian: <?= e($filter['q']) ?> &nbsp;|&nbsp; <?php endif; ?>
  </span>
</div>
<?php endif; ?>

<div class="summary-grid">
  <div class="sum-card"><div class="label">Jumlah Transaksi</div><div class="value primary"><?= $sum['count'] ?></div></div>
  <div class="sum-card"><div class="label">Total Penerimaan</div><div class="value primary"><?= rupiah($sum['total']) ?></div></div>
  <div class="sum-card"><div class="label">Tunai</div><div class="value cash"><?= rupiah($sum['by']['TUNAI']) ?></div></div>
  <div class="sum-card"><div class="label">QRIS</div><div class="value digital"><?= rupiah($sum['by']['QRIS']) ?></div></div>
  <div class="sum-card"><div class="label">Transfer</div><div class="value digital"><?= rupiah($sum['by']['TRANSFER']) ?></div></div>
  <div class="sum-card"><div class="label">Total Biaya Operasional</div><div class="value expense"><?= rupiah($biayaHarian) ?></div></div>
  <div class="sum-card"><div class="label">Tunai Bersih (Tunai - Biaya)</div><div class="value net <?= $tunaiBersih < 0 ? 'neg' : '' ?>"><?= rupiah($tunaiBersih) ?></div></div>
</div>

<table>
  <thead>
    <tr>
      <th style="width:30px">No</th>
      <th style="width:110px">Nomor Transaksi</th>
      <th style="width:50px">Waktu</th>
      <th>Jenis / Detail</th>
      <th style="width:65px">Kolam</th>
      <th style="width:80px">Kasir</th>
      <th style="width:85px" class="num">Total (Rp)</th>
      <th style="width:70px">Metode</th>
      <th style="width:70px">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($TRANSAKSI as $tr): ?>
    <tr>
      <td class="center"><?= $no++ ?></td>
      <td class="num bold"><?= e($tr['number']) ?></td>
      <td class="center"><?= e((new DateTime($tr['created_at'], new DateTimeZone('Asia/Jakarta')))->format('H:i')) ?></td>
      <td>
        <span class="badge badge-type"><?= e($tr['type']) ?></span>
        <?php if (!empty($tr['jenis_detail'])): ?>
        <span class="detail-small"><?= e($tr['jenis_detail']) ?></span>
        <?php endif; ?>
      </td>
      <td class="center">
        <?php 
          $area = $tr['area'] ?? 'PUTRA';
          $areaClass = $area === 'PUTRI' ? 'badge-area-putri' : ($area === 'CAMPURAN' ? 'badge-area-campur' : 'badge-area-putra');
          $areaLabel = $area === 'CAMPURAN' ? 'Putra/Putri' : ucfirst(strtolower($area));
        ?>
        <span class="badge <?= $areaClass ?>"><?= e($areaLabel) ?></span>
      </td>
      <td><?= e($tr['kasir'] ?: '-') ?></td>
      <td class="num bold"><?= rupiah($tr['amount']) ?></td>
      <td class="center">
        <?php 
          $pm = $tr['payment_method'] ?? 'TUNAI';
          $pmClass = $pm === 'TUNAI' ? 'badge-cash' : 'badge-digital';
        ?>
        <span class="badge <?= $pmClass ?>"><?= e($pm) ?></span>
      </td>
      <td class="center">
        <?php 
          $ps = $tr['payment_status'] ?? 'PAID';
          $psClass = $ps === 'CANCELLED' ? 'badge-cancelled' : 'badge-paid';
        ?>
        <span class="badge <?= $psClass ?>"><?= e($ps) ?></span>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$TRANSAKSI): ?>
    <tr><td colspan="9" class="center" style="padding: 20px; color: #999;">Tidak ada transaksi pada periode ini.</td></tr>
    <?php endif; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="6" class="bold" style="text-align:right; padding-right:10px;">TOTAL</td>
      <td class="num bold"><?= rupiah($sum['total']) ?></td>
      <td colspan="2"></td>
    </tr>
  </tfoot>
</table>

<div class="footer">
  <div>Dicetak pada: <?= hari_tanggal_indo() ?> <?= date('H:i') ?> WIB</div>
  <div style="margin-top: 5px; font-size: 7.5px; color: #999;">Sistem Kolam Renang Al Ma'soem</div>
</div>

<script>
  window.onload = function() { 
    setTimeout(function() { window.print(); }, 300);
  };
</script>
</body>
</html>