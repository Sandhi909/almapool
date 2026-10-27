<?php
$L = $LAPORAN ?? [];
$n = fn($v) => number_format((float)$v, 0, ',', '.');

// Hitung total
$totTiket = array_sum(array_map(fn($r) => (float)($r['subtotal'] ?? 0), $L['tiket'] ?? []));
$totLbr = array_sum(array_map(fn($r) => (float)($r['qa'] ?? 0) + (float)($r['qb'] ?? 0) + (float)($r['qc'] ?? 0), $L['tiket'] ?? []));

$totMember = array_sum(array_map(fn($r) => (float)($r['jml'] ?? 0), $L['member'] ?? []));
$totQG = array_sum(array_map(fn($r) => (float)($r['qg'] ?? 0), $L['member'] ?? []));
$totQS = array_sum(array_map(fn($r) => (float)($r['qs'] ?? 0), $L['member'] ?? []));

$totSewaLes = array_sum(array_map(fn($r) => (float)($r['jml'] ?? 0), $L['les'] ?? [])) + (float)($L['sewa_alat'] ?? 0);
$totQPutra = array_sum(array_map(fn($r) => (float)($r['qputra'] ?? 0), $L['les'] ?? []));
$totQPutri = array_sum(array_map(fn($r) => (float)($r['qputri'] ?? 0), $L['les'] ?? []));

$totPenerimaan = $totTiket + $totMember + $totSewaLes + (float)($L['tambahan'] ?? 0);
$totBiaya = array_sum(array_map(fn($b) => (float)($b['jumlah'] ?? 0), $L['biaya'] ?? []));
$bersih = $totPenerimaan - $totBiaya;

$cashbon = ((float)($L['setoran'][0]['jumlah'] ?? 0) + (float)($L['setoran'][1]['jumlah'] ?? 0));
$keYab = $cashbon + ((float)($L['setoran'][2]['jumlah'] ?? 0));

$tiketRows = $L['tiket'] ?? [];
$memberRows = $L['member'] ?? [];
$lesRows = $L['les'] ?? [];
$title = 'Laporan ' . ucfirst($L['periode'] ?? 'Bulanan');
?>

<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-file-earmark-text fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">LAPORAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'"><?= e($title) ?></h5>
      <small class="opacity-90"><?= e($L['periode'] ?? '') ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2" style="position:relative;z-index:1">
    <button onclick="window.print()" class="btn bg-white text-primary fw-bold btn-sm" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-printer me-1"></i>Print / PDF</button>
  </div>
</div>

<div class="tt-card p-4" style="overflow-x:auto">
  <style>
    .laporan-table { width:100%; border-collapse:collapse; margin-bottom:12px; background:#fff; font-size:12px; }
    .laporan-table th, .laporan-table td { border:1px solid #aaa; padding:4px 8px; }
    .laporan-table th { background:#e9ecef; font-weight:700; text-align:center; vertical-align:middle; }
    .laporan-table td.r { text-align:right; }
    .laporan-table td.c { text-align:center; }
    .laporan-table td.b { font-weight:700; }
    .laporan-table tr.sec td { background:#d4edda; }
    .laporan-table tr.sec-setoran td { background:#d1ecf1; }
    .laporan-table tr.sec-bop td { background:#fff3cd; }
    @media print {
      .page-hero { display:none; }
      .tt-card { box-shadow:none; border:none; padding:0; }
      .laporan-table { page-break-inside:avoid; }
    }
  </style>

  <div class="text-center mb-4">
    <h5 style="margin-bottom:4px;font-weight:700"><?= e($APP['nama_kolam'] ?? 'KOLAM RENANG AL MA\'SOEM') ?></h5>
    <h6 style="margin-top:0;font-weight:700">LAPORAN <?= strtoupper($TYPE ?? 'BULANAN') ?></h6>
    <p style="margin:0;font-size:13px"><?= e($L['periode'] ?? '') ?></p>
  </div>

  <table class="laporan-table">
    <tbody>
      <tr class="sec"><td style="width:26px">I.</td><td>Rekapitulasi Tiket Harian</td>
        <td class="c" colspan="3">Tiket Masuk</td><td class="c" colspan="3">Pengunjung</td><td class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</td></tr>
      <tr><th style="width:26px">A</th><th>Jenis Tiket</th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th></th></tr>
    </tbody>
    <tbody>
      <?php foreach ($tiketRows as $r): $lbr=(float)($r['qa']??0)+(float)($r['qb']??0)+(float)($r['qc']??0); ?>
      <tr>
        <td></td><td><?= e($r['nama']) ?></td>
        <td class="r"><?= e($n($r['ta'] ?? 0)) ?></td><td class="r"><?= e($n($r['qa'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['ta'] ?? 0) * ($r['qa'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['tb'] ?? 0)) ?></td><td class="r"><?= e($n($r['qb'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['tb'] ?? 0) * ($r['qb'] ?? 0)) ?></td>
        <td class="r b"><?= e($n($lbr)) ?></td>
        <td class="r b"><?= e($n($r['subtotal'] ?? 0)) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr class="sec-bop"><td colspan="8">Total Penerimaan dari Tiket</td><td class="r b"><?= e($n($totTiket)) ?></td></tr>
    </tbody>
  </table>

  <table class="laporan-table">
    <thead>
      <tr><th style="width:26px">B</th><th>Jenis Member</th><th class="c" colspan="3">Gold</th><th class="c" colspan="3">Silver</th><th class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</th></tr>
      <tr><th></th><th></th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th class="c">Tarif (Rp)</th><th class="c">Qty</th><th class="c">Jumlah</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($memberRows as $r): ?>
      <tr>
        <td></td><td><?= e($r['jenis']) ?></td>
        <td class="r"><?= e($n($r['tg'] ?? 0)) ?></td><td class="r"><?= e($n($r['qg'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['jg'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['ts'] ?? 0)) ?></td><td class="r"><?= e($n($r['qs'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['js'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['jml'] ?? 0)) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr class="sec-bop"><td></td><td>Total Penerimaan dari Member</td>
        <td></td><td class="r b"><?= e($n($totQG)) ?></td><td></td>
        <td></td><td class="r b"><?= e($n($totQS)) ?></td><td></td>
        <td class="r b"><?= e($n($totMember)) ?></td></tr>
    </tbody>
  </table>

  <table class="laporan-table">
    <thead>
      <tr><th style="width:26px">C</th><th>Sewa Alat (Rp)</th><th>Jenis Les</th><th class="c">Tarif (Rp)</th><th class="c" colspan="2">Putra</th><th class="c" colspan="2">Putri</th><th class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</th></tr>
      <tr><th></th><th></th><th></th><th></th><th class="c">Qty</th><th class="c">Jumlah</th><th class="c">Qty</th><th class="c">Jumlah</th><th></th></tr>
    </thead>
    <tbody>
      <?php $nLes = max(1, count($lesRows)); $iLes = 0; foreach ($lesRows as $r): $jenisLes = preg_replace('/^les\s+/i', '', (string)($r['jenis'] ?? '')); ?>
      <tr>
        <td></td>
        <?php if ($iLes === 0): ?><td class="r" rowspan="<?= $nLes ?>"><?= e($n($L['sewa_alat'] ?? 0)) ?></td><?php endif; ?>
        <td><?= e($jenisLes) ?></td><td class="r"><?= e($n($r['tarif'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['qputra'] ?? 0)) ?></td><td class="r"><?= e($n($r['jpra'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['qputri'] ?? 0)) ?></td><td class="r"><?= e($n($r['jpri'] ?? 0)) ?></td>
        <td class="r"><?= e($n($r['jml'] ?? 0)) ?></td>
      </tr>
      <?php $iLes++; endforeach; ?>
      <tr class="sec-bop"><td></td><td colspan="3">Total Penerimaan dari Alat Sewa dan Les</td>
        <td class="r b"><?= e($n($totQPutra)) ?></td><td></td>
        <td class="r b"><?= e($n($totQPutri)) ?></td><td></td>
        <td class="r b"><?= e($n($totSewaLes)) ?></td></tr>
      <?php if ((float)($L['tambahan'] ?? 0) > 0): ?>
      <tr><td></td><td colspan="7">Biaya Tambahan</td><td class="r"><?= e($n($L['tambahan'])) ?></td></tr>
      <?php endif; ?>
      <tr class="sec"><td></td><td colspan="7">Total Penerimaan</td><td class="r b"><?= e($n($totPenerimaan)) ?></td></tr>
    </tbody>
  </table>

  <table class="laporan-table">
    <tbody>
      <tr class="sec"><td style="width:26px">III.</td><td>Biaya Operasional Kolam</td><td class="c" style="width:140px">Jumlah (Rp)</td></tr>
      <?php foreach ($L['biaya'] ?? [] as $b): ?>
      <tr><td></td><td><?= e($b['nama']) ?></td><td class="r"><?= e($n($b['jumlah'] ?? 0)) ?></td></tr>
      <?php endforeach; ?>
      <tr class="sec"><td></td><td>Total Biaya Operasional</td><td class="r b"><?= e($n($totBiaya)) ?></td></tr>
      <tr class="sec"><td>IV.</td><td>Penerimaan di Kurang BOP Kolam</td><td class="r b"><?= e($n($bersih)) ?></td></tr>
    </tbody>
  </table>

  <table class="laporan-table">
    <tbody>
      <tr class="sec-setoran"><td style="width:26px">V.</td><td>Setoran</td><td class="c">Keterangan</td><td class="c" style="width:140px">Jumlah (Rp)</td></tr>
      <tr><td></td><td rowspan="3">Cashbon disetor</td><td><?= e($L['setoran'][0]['nama'] ?? '-') ?></td><td class="r"><?= e($n($L['setoran'][0]['jumlah'] ?? 0)) ?></td></tr>
      <tr><td></td><td><?= e($L['setoran'][1]['nama'] ?? '-') ?></td><td class="r"><?= e($n($L['setoran'][1]['jumlah'] ?? 0)) ?></td></tr>
      <tr class="sec"><td></td><td>Total Cashbon</td><td class="r b"><?= e($n($cashbon)) ?></td></tr>
      <tr><td></td><td>Setoran Uang Tunai</td><td></td><td class="r"><?= e($n(((float)($L['setoran'][2]['jumlah'] ?? 0)) - $totBiaya)) ?></td></tr>
      <tr class="sec"><td></td><td>Jumlah Setoran ke YAB</td><td></td><td class="r b"><?= e($n($keYab)) ?></td></tr>
    </tbody>
  </table>

  <table class="laporan-table">
    <tbody>
      <tr class="sec"><td style="width:26px">VI.</td><td>Saldo Akhir Kas</td>
        <td class="c">Bon blm disetor (Rp)</td><td class="r" style="width:110px"><?= e($n($L['saldo_akhir']['bon'] ?? 0)) ?></td>
        <td class="c">Uang Tunai (Rp)</td><td class="r" style="width:110px"><?= e($n($L['saldo_akhir']['tunai'] ?? 0)) ?></td>
        <td class="c" style="width:70px">Total</td><td class="r" style="width:110px">-</td></tr>
    </tbody>
  </table>

  <div style="margin-top:40px">
    <b>Keterangan :</b>
    <?php foreach ($L['keterangan'] ?? [] as $k): ?>
    <div style="margin-left:20px;font-size:12px">- <?= e($k) ?></div>
    <?php endforeach; ?>
  </div>

  <table style="margin-top:48px;border:0;width:100%">
    <tbody>
      <tr>
        <?php $wTtd = count($L['ttd'] ?? []) ? (100 / count($L['ttd'])) : 33; foreach ($L['ttd'] ?? [] as $t): ?>
        <td class="c" style="border:0;width:<?= $wTtd ?>%;padding:0;vertical-align:top">
          <div style="height:72px"></div>
          <div style="border-top:1px solid #333;padding-top:4px;font-size:12px;font-weight:700"><?= e($t['nama'] ?? '') ?></div>
          <div style="font-size:11px"><?= e($t['jabatan'] ?? '') ?></div>
        </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>
</div>
