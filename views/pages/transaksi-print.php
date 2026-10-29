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
  @page { margin: 15mm; size: A4; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #111; margin: 0; padding: 0; }
  .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
  .header h2 { margin: 0; font-size: 18px; font-weight: 700; }
  .header .sub { font-size: 12px; color: #555; margin-top: 4px; }
  .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 11px; }
  .info-row .label { font-weight: 600; }
  .summary-cards { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
  .card { flex: 1; min-width: 120px; border: 1px solid #ddd; border-radius: 4px; padding: 8px; text-align: center; background: #fafafa; }
  .card .label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
  .card .value { font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; }
  .card .value.pos { color: #198754; }
  .card .value.neg { color: #dc3545; }
  .card .value.primary { color: #0d6efd; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
  th, td { border: 1px solid #ccc; padding: 4px 6px; vertical-align: top; }
  th { background: #f0f0f0; font-weight: 700; text-align: center; }
  td.num { text-align: right; font-variant-numeric: tabular-nums; font-family: 'Courier New', monospace; }
  td.center { text-align: center; }
  .pill { display: inline-block; padding: 1px 6px; border-radius: 999px; font-size: 9px; font-weight: 600; }
  .pill-blue { background: #cfe2ff; color: #084298; }
  .pill-amber { background: #ffeeba; color: #856404; }
  .pill-green { background: #d4edda; color: #155724; }
  .pill-grey { background: #e2e3e5; color: #383d41; }
  .pill-red { background: #f8d7da; color: #721c24; }
  .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
  @media print { .no-print { display: none !important; } body { margin: 0; } }
</style>
</head>
<body>
<div class="header">
  <h2><?= e($APP_NAME) ?></h2>
  <div class="sub">Laporan Transaksi Harian</div>
</div>
<div class="info-row">
  <span class="label">Periode:</span>
  <span><?= e($periode) ?></span>
</div>
<?php if ($filter['type'] || $filter['method'] || $filter['q']): ?>
<div class="info-row">
  <span class="label">Filter:</span>
  <span>
    <?php if ($filter['type']): ?>Jenis: <?= e($filter['type']) ?> | <?php endif; ?>
    <?php if ($filter['method']): ?>Metode: <?= e($filter['method']) ?> | <?php endif; ?>
    <?php if ($filter['q']): ?>Cari: <?= e($filter['q']) ?> | <?php endif; ?>
  </span>
</div>
<?php endif; ?>
<div class="summary-cards">
  <div class="card"><div class="label">Transaksi</div><div class="value"><?= $sum['count'] ?></div></div>
  <div class="card"><div class="label">Total</div><div class="value primary"><?= rupiah($sum['total']) ?></div></div>
  <div class="card"><div class="label">Tunai</div><div class="value"><?= rupiah($sum['by']['TUNAI']) ?></div></div>
  <div class="card"><div class="label">QRIS</div><div class="value"><?= rupiah($sum['by']['QRIS']) ?></div></div>
  <div class="card"><div class="label">Transfer</div><div class="value"><?= rupiah($sum['by']['TRANSFER']) ?></div></div>
  <div class="card"><div class="label">Total Biaya</div><div class="value neg"><?= rupiah($biayaHarian) ?></div></div>
  <div class="card"><div class="label">Tunai Bersih</div><div class="value <?= $tunaiBersih >= 0 ? 'pos' : 'neg' ?>"><?= rupiah($tunaiBersih) ?></div></div>
</div>
<table>
  <thead>
    <tr>
      <th style="width:40px">No</th>
      <th style="width:100px">Nomor</th>
      <th style="width:60px">Waktu</th>
      <th>Jenis</th>
      <th style="width:70px">Kolam</th>
      <th style="width:80px">Kasir</th>
      <th style="width:90px" class="num">Total</th>
      <th style="width:70px">Metode</th>
      <th style="width:70px">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($TRANSAKSI as $tr): ?>
    <tr>
      <td class="center"><?= $no++ ?></td>
      <td class="num fw-bold"><?= e($tr['number']) ?></td>
      <td class="center"><?= e((new DateTime($tr['created_at'], new DateTimeZone('Asia/Jakarta')))->format('H:i')) ?></td>
      <td>
        <span class="pill pill-blue"><?= e($tr['type']) ?></span><br>
        <small><?= e($tr['jenis_detail'] ?? '-') ?></small>
      </td>
      <td class="center">
        <span class="pill <?= $tr['area']==='PUTRI'?'pill-amber':($tr['area']==='CAMPURAN'?'pill-green':'pill-blue') ?>">
          <?= e($tr['area']==='CAMPURAN'?'Putra/Putri':ucfirst(strtolower($tr['area']))) ?>
        </span>
      </td>
      <td><?= e($tr['kasir'] ?: '-') ?></td>
      <td class="num fw-bold"><?= rupiah($tr['amount']) ?></td>
      <td class="center">
        <span class="pill <?= $tr['payment_method'] === 'TUNAI' ? 'pill-grey' : 'pill-green' ?>">
          <?= e($tr['payment_method']) ?>
        </span>
      </td>
      <td class="center">
        <span class="pill <?= $tr['payment_status']==='CANCELLED'?'pill-red':'pill-green' ?>">
          <?= e($tr['payment_status']) ?>
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$TRANSAKSI): ?>
    <tr><td colspan="9" class="center text-muted">Tidak ada transaksi.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
<div class="footer">
  Dicetak pada: <?= hari_tanggal_indo() ?> <?= date('H:i') ?> WIB | Halaman ini dicetak dari sistem Kolam Renang Al Ma'soem
</div>
<script>
  window.onload = function() { window.print(); }
</script>
</body>
</html>