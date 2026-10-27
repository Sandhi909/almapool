<?php
// Laporan Bulanan — replika format dokumen Kolam Renang Al Ma'soem.
// Seluruh angka dihitung dari $LAPORAN (dummy) + tarif master.
$L = $LAPORAN;
$n = function ($v): string {
    if ($v === null || $v === '' || (float) $v == 0) return '-';
    return number_format((float) $v, 0, ',', '.');
};
// peta tarif tiket per nama
$tmap = [];
foreach ($TIKET as $t) $tmap[mb_strtolower($t['name'])] = ['a' => $t['price_a'], 'b' => $t['price_b'], 'c' => $t['price_c']];
// peta tarif member per jenis (normalisasi)
$mmap = [];
foreach ($MEMBER_PAKET as $jenis => $tiers) {
    $mmap[mb_strtolower($jenis)] = ['gold' => $tiers['GOLD']['price'] ?? 0, 'silver' => $tiers['SILVER']['price'] ?? 0];
}
$norm = function ($s): string { return mb_strtolower(trim(preg_replace('/^-\s*/', '', $s))); };
// peta tarif les
$lmap = [];
foreach ($LES_PAKET as $p) {
    $lmap[mb_strtolower($p['name'])] = $p['price'];
    $lmap['les ' . mb_strtolower($p['name'])] = $p['price'];
}

/* --- hitung tiket --- */
$tiketRows = [];
$totQa = 0; $totQb = 0; $totQc = 0; $totLbr = 0; $totTiket = 0;
foreach ($L['tiket'] as $r) {
    $t = $tmap[mb_strtolower($r['nama'])] ?? ['a' => 0, 'b' => 0, 'c' => 0];
    $ta = $t['a']; $tb = $t['b']; $tc = $t['c'];
    $qa = (float) ($r['qa'] ?? 0);
    $qb = (float) ($r['qb'] ?? 0);
    $qc = (float) ($r['qc'] ?? 0);
    $ja = $qa * $ta;
    $jb = $qb * $tb;
    $jc = $qc * $tc;
    $jml = $ja + $jb + $jc;
    $tiketRows[] = array_merge($r, ['ta' => $ta, 'tb' => $tb, 'tc' => $tc,
        'qa' => $qa, 'qb' => $qb, 'qc' => $qc,
        'ja' => $ja, 'jb' => $jb, 'jc' => $jc, 'jml' => $jml]);
    $totQa += $qa; $totQb += $qb; $totQc += $qc;
    $totLbr += $qa + $qb + $qc;
    $totTiket += $jml;
}
/* --- hitung member --- */
$memberRows = [];
$totQG = 0; $totQS = 0; $totMember = 0;
foreach ($L['member'] as $r) {
    $p = $mmap[$norm($r['jenis'])] ?? ['gold' => 0, 'silver' => 0];
    $jg = $r['qg'] * $p['gold']; $js = $r['qs'] * $p['silver'];
    $memberRows[] = array_merge($r, ['tg' => $p['gold'], 'ts' => $p['silver'], 'jg' => $jg, 'js' => $js, 'jml' => $jg + $js]);
    $totQG += $r['qg']; $totQS += $r['qs']; $totMember += $jg + $js;
}
/* --- hitung les --- */
$lesRows = [];
$totQPutra = 0; $totQPutri = 0; $totLes = 0;
foreach ($L['les'] as $r) {
    $tarif = $lmap[mb_strtolower($r['jenis'])] ?? 0;
    $jpra = $r['qputra'] * $tarif; $jpri = $r['qputri'] * $tarif;
    $lesRows[] = array_merge($r, ['tarif' => $tarif, 'jpra' => $jpra, 'jpri' => $jpri, 'jml' => $jpra + $jpri]);
    $totQPutra += $r['qputra']; $totQPutri += $r['qputri']; $totLes += $jpra + $jpri;
}
$totSewaLes = $L['sewa_alat'] + $totLes;
$tambahan = (float) ($L['tambahan'] ?? 0);
$totPenerimaan = $totTiket + $totMember + $totSewaLes + $tambahan;
$totBiaya = array_sum(array_column($L['biaya'], 'jumlah'));
$bersih = $totPenerimaan - $totBiaya;
$cashbon = $L['setoran'][0]['jumlah'] + $L['setoran'][1]['jumlah'];
$keYab = $cashbon + $L['setoran'][2]['jumlah'];
?>
<style>
.lap-wrap{font-family:Arial,Helvetica,sans-serif;font-size:11.5px;color:#111;background:#fff}
.lap-wrap table{border-collapse:collapse;width:100%;margin-bottom:2px}
.lap-wrap th,.lap-wrap td{border:1px solid #333;padding:3px 6px;vertical-align:top}
.lap-wrap thead th{background:#d7e7f7;text-align:center}
.lap-wrap tr.sec td,.lap-wrap tr.sec th{background:#fbe3ae;font-weight:700}
.lap-wrap tr.sec-bop td,.lap-wrap tr.sec-bop th{background:#d7e7f7;font-weight:700}
.lap-wrap tr.sec-bersih td,.lap-wrap tr.sec-bersih th{background:#bae6fd;color:#111;font-weight:700;border:1px solid #333}
.lap-wrap tr.sec-setoran td,.lap-wrap tr.sec-setoran th{background:#d7e7f7;font-weight:700}
.lap-wrap .c{text-align:center}
.lap-wrap .r{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
.lap-wrap .b{font-weight:700}
.lap-title{text-align:center;font-weight:700}
</style>

<?php
/* --- selector tipe laporan (harian / mingguan / bulanan) --- */
$type = $TYPE ?? 'bulanan';
$P = $PERIOD ?? [];
$judulLap = ['harian' => 'HARIAN', 'mingguan' => 'MINGGUAN', 'bulanan' => 'BULANAN'][$type] ?? 'BULANAN';
// Periode mingguan selalu 4 minggu per bulan: 1-7, 8-14, 15-21, 22-akhir bulan.
$lapMonth = (int) ($P['month'] ?? date('n'));
$lapYear = (int) ($P['year'] ?? date('Y'));
$lapWeek = (int) ($P['week'] ?? current_week_of_month());
$lapLastDay = (int) date('t', mktime(0, 0, 0, $lapMonth, 1, $lapYear));
?>
<div class="kasir-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative no-print" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white" style="width:48px;height:48px;color:#0ea5e9;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-file-earmark-bar-graph fs-4"></i></span>
    <div>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Laporan <?= e($judulLap) ?> Kolam Renang</h5>
      <small class="opacity-90"><?= e($L['periode']) ?> &bull; Penerimaan &rarr; BOP &rarr; Setoran YAB &bull; Total: <?= e($n($totPenerimaan)) ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2" style="position:relative;z-index:1">
    <span class="badge bg-white text-primary" style="font-size:.70rem;border-radius:9999px;padding:.4rem .7rem"><?= e($judulLap) ?></span>
    <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25" style="backdrop-filter:blur(8px);border-radius:9999px;padding:.4rem .7rem">Bersih: <?= e($n($bersih)) ?></span>
  </div>
</div>
<div class="d-flex flex-column gap-2 mb-3 no-print">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <form id="lapForm" method="get" action="<?= url('index.php') ?>" class="d-flex align-items-center flex-wrap gap-2" style="max-width: 760px">
      <input type="hidden" name="page" value="laporan">
      <input type="hidden" name="type" id="fType" value="<?= e($type) ?>">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary btn-sm <?= $type === 'harian' ? 'active' : '' ?>" data-type="harian"><i class="bi bi-calendar-day me-1"></i>Harian</button>
        <button type="button" class="btn btn-outline-secondary btn-sm <?= $type === 'mingguan' ? 'active' : '' ?>" data-type="mingguan"><i class="bi bi-calendar-week me-1"></i>Mingguan</button>
        <button type="button" class="btn btn-outline-secondary btn-sm <?= $type === 'bulanan' ? 'active' : '' ?>" data-type="bulanan"><i class="bi bi-calendar-month me-1"></i>Bulanan</button>
      </div>

      <span id="grpHarian" class="d-none"><input type="date" name="tanggal" class="form-control form-control-sm" style="max-width:200px" value="<?= e($P['date'] ?? date('Y-m-d')) ?>"></span>

      <span id="grpMingguan" class="d-none d-flex align-items-center gap-1">
        <select name="bulan" class="form-select form-select-sm" style="max-width:130px">
          <?php for ($b = 1; $b <= 12; $b++): ?>
          <option value="<?= $b ?>" <?= $b == $lapMonth ? 'selected' : '' ?>><?= bulan_indo($b) ?></option>
          <?php endfor; ?>
        </select>
        <select name="minggu" class="form-select form-select-sm" style="max-width:170px">
          <?php for ($w = 1; $w <= 4; $w++):
            $awal = ($w - 1) * 7 + 1;
            $akhir = $w === 4 ? $lapLastDay : $awal + 6;
          ?>
          <option value="<?= $w ?>" <?= $w === $lapWeek ? 'selected' : '' ?>>Minggu <?= $w ?> (<?= $awal ?>-<?= $akhir ?>)</option>
          <?php endfor; ?>
        </select>
        <select name="tahun" class="form-select form-select-sm" style="max-width:100px">
          <?php for ($t = 2024; $t <= 2027; $t++): ?>
          <option value="<?= $t ?>" <?= $t == $lapYear ? 'selected' : '' ?>><?= $t ?></option>
          <?php endfor; ?>
        </select>
      </span>

      <span id="grpBulanan" class="d-none d-flex align-items-center gap-1">
        <select name="bulan" class="form-select form-select-sm" style="max-width:160px">
          <?php for ($b = 1; $b <= 12; $b++): ?>
          <option value="<?= $b ?>" <?= $b == ($P['month'] ?? date('n')) ? 'selected' : '' ?>><?= bulan_indo($b) ?></option>
          <?php endfor; ?>
        </select>
        <select name="tahun" class="form-select form-select-sm" style="max-width:100px">
          <?php for ($t = 2024; $t <= 2027; $t++): ?>
          <option value="<?= $t ?>" <?= $t == ($P['year'] ?? date('Y')) ? 'selected' : '' ?>><?= $t ?></option>
          <?php endfor; ?>
        </select>
      </span>

      <button class="btn btn-tt btn-sm" type="submit">Tampilkan</button>
    </form>
    <div class="d-flex gap-2 no-print">
      <button class="btn btn-light btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak</button>
      <a class="btn btn-light btn-sm" href="<?= url('index.php?page=transaksi') ?>">Kembali</a>
    </div>
  </div>
  <script>
  (function(){
    var grp = {harian:'grpHarian',mingguan:'grpMingguan',bulanan:'grpBulanan'};
    function show(t){
      document.getElementById('fType').value = t;
      document.querySelectorAll('[id^=grp]').forEach(function(el){el.classList.add('d-none');});
      document.getElementById(grp[t]).classList.remove('d-none');
    }
    document.querySelectorAll('[data-type]').forEach(function(b){
      b.addEventListener('click', function(){ show(b.getAttribute('data-type')); });
    });
    show('<?= e($type) ?>');
  })();
  </script>
</div>
<small class="text-muted d-block mb-2 no-print"><i class="bi bi-info-circle me-1"></i>Seluruh total dihitung otomatis dari transaksi pada periode di atas. Setoran QRIS, Transfer, dan Uang Tunai otomatis dari metode bayar transaksi kasir.</small>

<div class="tt-card lap-wrap" id="lapPrint">
  <div class="text-center mb-1"><img src="<?= url(APP_LOGO) ?>" alt="Logo" style="height:64px"></div>
  <div class="lap-title">LAPORAN <?= e($judulLap) ?> <?= e($APP['nama_kolam']) ?></div>
  <div class="lap-title mb-1"><?= e($L['periode']) ?></div>
  <div class="text-center fst-italic mb-2">Bissmillahirrahmanirrahim</div>

  <table>
    <tbody>
      <tr class="sec"><td style="width:26px">I.</td><td>Saldo Awal Kas</td>
        <td class="c">Bon blm disetor (Rp)</td><td class="r"><?= e($n($L['saldo_awal']['bon'])) ?></td>
        <td class="c">Uang Tunai (Rp)</td><td class="r"><?= e($n($L['saldo_awal']['tunai'])) ?></td></tr>
    </tbody>
  </table>
  <table>
    <thead>
      <tr><th colspan="10" style="text-align:left">II.&nbsp;&nbsp;Penerimaan</th></tr>
      <tr><th style="width:26px">A</th><th>Tiket</th><th class="c" colspan="2">Tarif A</th><th class="c" colspan="2">Tarif B</th><th class="c" colspan="2">Tarif C</th><th class="c">Jumlah</th><th class="c" style="white-space:nowrap;width:140px">Jumlah (Rp)</th></tr>
      <tr><th></th><th></th><th class="c">(Rp)</th><th class="c">Pengunjung</th><th class="c">(Rp)</th><th class="c">Pengunjung</th><th class="c">(Rp)</th><th class="c">Pengunjung</th><th></th><th></th></tr>
    </thead>
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
        <td class="r"><?= e($n($r['tg'])) ?></td><td class="r"><?= e($n($r['qg'])) ?></td><td class="r"><?= e($n($r['jg'])) ?></td>
        <td class="r"><?= e($n($r['ts'])) ?></td><td class="r"><?= e($n($r['qs'])) ?></td><td class="r"><?= e($n($r['js'])) ?></td>
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
