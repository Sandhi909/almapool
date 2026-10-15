<?php $d=$DATA; $type=$TYPE; $period=$PERIOD; ?>
<div class="kasir-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative no-print" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white" style="width:48px;height:48px;color:#0ea5e9;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-people fs-4"></i></span>
    <div>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Laporan Kunjungan Kolam</h5>
      <small class="opacity-90"><?= e($PERIODE_LABEL) ?> &bull; Putra/Putri &bull; External/Internal &bull; Rombongan</small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 align-items-center" style="position:relative;z-index:1">
    <span class="badge bg-white text-primary" style="border-radius:9999px;padding:.4rem .7rem"><?= e(strtoupper($type)) ?></span>
    <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25" style="backdrop-filter:blur(8px);border-radius:9999px;padding:.4rem .7rem">Total: <?= number_format($d['grandTotal'],0,',','.') ?></span>
  </div>
</div>

<div class="d-flex flex-column gap-2 mb-3 no-print">
  <div class="tt-card d-flex align-items-center flex-wrap gap-2">
    <form id="kunForm" method="get" action="<?= url('index.php') ?>" class="d-flex align-items-center flex-wrap gap-2">
      <input type="hidden" name="page" value="kunjungan">
      <input type="hidden" name="type" id="kType" value="<?= e($type) ?>">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-sm <?= $type==='harian'?'btn-tt':'btn-outline-secondary' ?>" data-type="harian"><i class="bi bi-calendar-day me-1"></i>Harian</button>
        <button type="button" class="btn btn-sm <?= $type==='rentang'?'btn-tt':'btn-outline-secondary' ?>" data-type="rentang"><i class="bi bi-calendar-range me-1"></i>Rentang</button>
        <button type="button" class="btn btn-sm <?= $type==='mingguan'?'btn-tt':'btn-outline-secondary' ?>" data-type="mingguan"><i class="bi bi-calendar-week me-1"></i>Mingguan</button>
        <button type="button" class="btn btn-sm <?= $type==='bulanan'?'btn-tt':'btn-outline-secondary' ?>" data-type="bulanan"><i class="bi bi-calendar-month me-1"></i>Bulanan</button>
      </div>
      <span id="kHarian" class="<?= $type==='harian'?'':'d-none' ?>"><input type="date" name="tanggal" class="form-control form-control-sm" style="max-width:180px" value="<?= e($period['date'] ?? $START) ?>"></span>
      <span id="kRentang" class="<?= $type==='rentang'?'d-flex':'d-none' ?> align-items-center gap-1">
        <input type="date" name="tgl_awal" class="form-control form-control-sm" style="max-width:160px" value="<?= e($period['tgl_awal'] ?? $START) ?>">
        <span class="text-muted">s/d</span>
        <input type="date" name="tgl_akhir" class="form-control form-control-sm" style="max-width:160px" value="<?= e($period['tgl_akhir'] ?? $END) ?>">
      </span>
      <span id="kMingguan" class="<?= $type==='mingguan'?'d-flex':'d-none' ?> align-items-center gap-1">
        <input type="number" name="minggu" min="1" max="53" class="form-control form-control-sm" style="max-width:90px" value="<?= e($period['week'] ?? date('W')) ?>">
        <select name="tahun" class="form-select form-select-sm" style="max-width:100px">
          <?php for($t=2024;$t<=2027;$t++): ?><option value="<?= $t ?>" <?= $t==($period['year']??date('Y'))?'selected':'' ?>><?= $t ?></option><?php endfor; ?>
        </select>
      </span>
      <span id="kBulanan" class="<?= $type==='bulanan'?'d-flex':'d-none' ?> align-items-center gap-1">
        <select name="bulan" class="form-select form-select-sm" style="max-width:160px">
          <?php for($b=1;$b<=12;$b++): ?><option value="<?= $b ?>" <?= $b==($period['month']??date('n'))?'selected':'' ?>><?= bulan_indo($b) ?></option><?php endfor; ?>
        </select>
        <select name="tahun" class="form-select form-select-sm" style="max-width:100px">
          <?php for($t=2024;$t<=2027;$t++): ?><option value="<?= $t ?>" <?= $t==($period['year']??date('Y'))?'selected':'' ?>><?= $t ?></option><?php endfor; ?>
        </select>
      </span>
      <button class="btn btn-tt btn-sm" type="submit">Tampilkan</button>
    </form>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-light btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak</button>
    </div>
  </div>
  <small class="text-muted"><i class="bi bi-info-circle me-1"></i><b>Internal/External</b> diambil dari <b>Master > Paket Tiket > Kategori</b> (Internal = Karyawan/Keluarga/Siswa/Santri, Eksternal = Umum/Rombongan). <b>Rombongan</b> = tiket nama mengandung "rombongan" + rincian jenjang SD/SMP/SMA/Mahasiswa MU.</small>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3"><div class="tt-card h-100 d-flex justify-content-between align-items-center" style="border-left:4px solid #0ea5e9">
    <div><span class="metric-label">Kolam Putra</span><div class="metric-value num" style="color:#0284c7"><?= number_format($d['putraTotal'],0,',','.') ?></div><small class="text-muted">Tiket <?= $d['tiketPutra'] ?> + Member <?= $d['memberPutra'] ?></small></div><span class="metric-icon mi-blue"><i class="bi bi-gender-male"></i></span></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card h-100 d-flex justify-content-between align-items-center" style="border-left:4px solid #ec4899">
    <div><span class="metric-label">Kolam Putri</span><div class="metric-value num" style="color:#be185d"><?= number_format($d['putriTotal'],0,',','.') ?></div><small class="text-muted">Tiket <?= $d['tiketPutri'] ?> + Member <?= $d['memberPutri'] ?></small></div><span class="metric-icon" style="background:#fce7f3;color:#be185d"><i class="bi bi-gender-female"></i></span></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card h-100 d-flex justify-content-between align-items-center" style="border-left:4px solid #f59e0b">
    <div><span class="metric-label">External</span><div class="metric-value num"><?= number_format($d['external'],0,',','.') ?></div><small class="text-muted">Umum + Rombongan + Member biasa</small></div><span class="metric-icon mi-amber"><i class="bi bi-box-arrow-up-right"></i></span></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card h-100 d-flex justify-content-between align-items-center" style="border-left:4px solid #10b981">
    <div><span class="metric-label">Internal</span><div class="metric-value num" style="color:#065f46"><?= number_format($d['internal'],0,',','.') ?></div><small class="text-muted">Karyawan/Keluarga + Siswa + Santri</small></div><span class="metric-icon mi-green"><i class="bi bi-building"></i></span></div></div>
</div>

<div class="row g-3 mb-3">
  <div class="col-xl-6"><div class="tt-card h-100">
    <b><i class="bi bi-diagram-3 me-1"></i>Rincian per Sumber — per Tiket</b>
    <div class="table-responsive mt-2"><table class="table tt-table align-middle mb-0">
      <thead><tr><th>Jenis Tiket</th><th class="text-end">Putra</th><th class="text-end">Putri</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        <?php $tr = $d['tiketRincian'] ?? []; if($tr): foreach($tr as $r): ?>
        <tr><td><span class="pill pill-blue" style="font-size:.65rem"><?= e($r['color'] ?? '-') ?></span> <?= e($r['name']) ?></td><td class="text-end num"><?= (int)$r['putra'] ?></td><td class="text-end num"><?= (int)$r['putri'] ?></td><td class="text-end num fw-bold"><?= (int)$r['total'] ?></td></tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4" class="text-center text-muted">Belum ada penjualan tiket di periode ini.</td></tr>
        <?php endif; ?>
        <tr class="fw-bold" style="background:#f0f9ff"><td>Subtotal Tiket</td><td class="text-end num"><?= $d['tiketPutra'] ?></td><td class="text-end num"><?= $d['tiketPutri'] ?></td><td class="text-end num" style="color:#0284c7"><?= $d['tiketTotal'] ?></td></tr>
        <tr><td><span class="pill pill-green">Member</span> Kunjungan scan</td><td class="text-end num"><?= $d['memberPutra'] ?></td><td class="text-end num"><?= $d['memberPutri'] ?></td><td class="text-end num fw-bold"><?= $d['memberTotal'] ?></td></tr>
        <tr class="fw-bold" style="background:#f0f9ff"><td>Total Kunjungan Kolam</td><td class="text-end num"><?= $d['putraTotal'] ?></td><td class="text-end num"><?= $d['putriTotal'] ?></td><td class="text-end num" style="color:#0284c7"><?= $d['grandTotal'] ?></td></tr>
      </tbody>
    </table></div>
    <small class="text-muted d-block mt-2" style="font-size:.68rem"><i class="bi bi-info-circle me-1"></i>Tiket Umum, Santri, Karyawan dst — pecah Putra/Putri sesuai detail transaksi.</small>
  </div></div>
  <div class="col-xl-6"><div class="tt-card h-100">
    <b><i class="bi bi-bar-chart me-1"></i>External vs Internal</b>
    <div class="row g-3 mt-1">
      <div class="col-6"><div class="rounded-4 p-3 text-center" style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #fcd34d">
        <small class="text-muted d-block" style="font-size:.70rem;letter-spacing:.05em">EXTERNAL</small><b class="num fs-2"><?= number_format($d['external'],0,',','.') ?></b><br><small class="text-muted">Umum + Rombongan + Member biasa</small>
        <small class="text-muted d-block mt-1" style="font-size:.68rem">Tiket ext <?= $d['externalTiketTotal'] ?? '-' ?> + Member ext <?= $d['externalMemberTotal'] ?? '-' ?></small>
      </div></div>
      <div class="col-6"><div class="rounded-4 p-3 text-center" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border:1px solid #6ee7b7">
        <small class="text-muted d-block" style="font-size:.70rem;letter-spacing:.05em">INTERNAL</small><b class="num fs-2" style="color:#065f46"><?= number_format($d['internal'],0,',','.') ?></b><br><small class="text-muted">Karyawan/Kelg + Siswa + Santri</small>
        <small class="text-muted d-block mt-1" style="font-size:.68rem">Tiket int <?= $d['internalTiketTotal'] ?? '-' ?> + Member int <?= $d['internalMemberTotal'] ?? '-' ?></small>
      </div></div>
    </div>
    <div class="rounded-3 p-2 mt-3 d-flex align-items-center gap-2" style="background:#f0f9ff;border:1px solid #e0f2fe">
      <i class="bi bi-people fs-4" style="color:#0284c7"></i><div><b>Rombongan: <?= $d['rombonganTotal'] ?> orang</b><br><small class="text-muted">Putra <?= $d['rombonganPutra'] ?> • Putri <?= $d['rombonganPutri'] ?> — dari penjualan tiket rombongan</small></div>
    </div>
    <?php $jt = $d['jenjangTotal'] ?? []; $jp=$d['jenjangPutra'] ?? []; $jw=$d['jenjangPutri'] ?? []; $jList2=$d['jenjangList'] ?? ['SD','SMP','SMA','Mahasiswa MU']; if(!empty($jt)): ?>
    <div class="mt-3">
      <b style="font-size:.85rem"><i class="bi bi-mortarboard me-1"></i>Rincian Jenjang</b>
      <div class="row g-2 mt-2">
        <?php foreach($jList2 as $jen): $tot=$jt[$jen]??0; $put=$jp[$jen]??0; $putri=$jw[$jen]??0; ?>
        <div class="col-6">
          <div class="rounded-3 p-2 text-center h-100" style="background:#fff;border:1.5px solid #e0f2fe">
            <small class="text-muted d-block" style="font-size:.70rem;letter-spacing:.04em"><?= e($jen) ?></small>
            <b class="num fs-5" style="color:#0284c7"><?= number_format($tot,0,',','.') ?></b>
            <small class="text-muted d-block" style="font-size:.68rem">Putra <?= $put ?> • Putri <?= $putri ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php $ut = $d['usiaTotal'] ?? []; $up=$d['usiaPutra'] ?? []; $uw=$d['usiaPutri'] ?? []; $uList2=$d['usiaList'] ?? ['Anak-anak','Remaja','Dewasa']; if(!empty($ut) && array_sum($ut)>0): ?>
    <div class="mt-3">
      <b style="font-size:.85rem"><i class="bi bi-people me-1"></i>Rincian Kelompok Usia</b>
      <div class="row g-2 mt-2">
        <?php foreach($uList2 as $usia): $tot=$ut[$usia]??0; $put=$up[$usia]??0; $putri=$uw[$usia]??0; ?>
        <div class="col-4">
          <div class="rounded-3 p-2 text-center h-100" style="background:#fff;border:1.5px solid #a7f3d0">
            <small class="text-muted d-block" style="font-size:.70rem;letter-spacing:.04em"><?= e($usia) ?></small>
            <b class="num fs-5" style="color:#065f46"><?= number_format($tot,0,',','.') ?></b>
            <small class="text-muted d-block" style="font-size:.68rem">Putra <?= $put ?> • Putri <?= $putri ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <small class="text-muted d-block mt-2" style="font-size:.68rem"><i class="bi bi-info-circle me-1"></i>Diambil dari suffix item_name " - Anak-anak/Remaja/Dewasa" (tiket dengan flag Kelompok Usia).</small>
    </div>
    <?php endif; ?>
    <small class="text-muted d-block mt-2"><i class="bi bi-lightbulb me-1"></i>Periode: <?= e($START) ?> s/d <?= e($END) ?>. Internal = tiket/membership mengandung Karyawan/Keluarga/Siswa Al/Santri.</small>
  </div></div>
</div>

<div class="tt-card mb-3">
  <b><i class="bi bi-people-fill me-1"></i>Rombongan Eksternal — Rincian Nama Rombongan</b>
  <small class="text-muted d-block">Tampilkan per tanggal & nama rombongan (kolom Putra/Putri dari tiket dengan flag Nama)</small>
  <div class="table-responsive mt-2"><table class="table tt-table align-middle mb-0">
    <thead><tr><th>Tanggal</th><th>Nama Rombongan</th><th class="text-end">Putra</th><th class="text-end">Putri</th><th class="text-end">Total</th></tr></thead>
    <tbody>
      <?php $re = $d['rombonganEksternal'] ?? []; if(empty($re)): ?>
      <tr><td colspan="5" class="text-center text-muted py-3">Belum ada rombongan eksternal di periode ini.</td></tr>
      <?php else: foreach($re as $row): ?>
      <tr><td class="num"><?= tanggal_indo($row['tgl']) ?><br><small class="text-muted"><?= e($row['tgl']) ?></small></td><td><b><?= e($row['nama']) ?></b></td><td class="text-end num"><?= $row['putra'] ?></td><td class="text-end num"><?= $row['putri'] ?></td><td class="text-end num fw-bold"><?= $row['total'] ?></td></tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table></div>
</div>

<?php if (!empty($d['daily'])): ?>
<div class="tt-card">
  <b><i class="bi bi-calendar3 me-1"></i>Rincian Harian Periode Ini</b>
  <div class="table-responsive mt-2"><table class="table tt-table align-middle mb-0">
    <thead><tr><th>Tanggal</th><th class="text-end">Tiket</th><th class="text-end">Member</th><th class="text-end">Total</th></tr></thead>
    <tbody>
      <?php foreach ($d['daily'] as $row): ?>
      <tr><td class="num"><?= tanggal_indo($row['tgl']) ?><br><small class="text-muted"><?= e($row['tgl']) ?></small></td><td class="text-end num"><?= $row['tiket'] ?></td><td class="text-end num"><?= $row['member'] ?></td><td class="text-end num fw-bold"><?= $row['tiket']+$row['member'] ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>

<script>
(function(){
  const map={harian:'kHarian',rentang:'kRentang',mingguan:'kMingguan',bulanan:'kBulanan'};
  function show(t){
    document.getElementById('kType').value=t;
    Object.values(map).forEach(id=>{
      const el=document.getElementById(id);
      if(!el) return;
      el.classList.add('d-none');
      el.classList.remove('d-flex');
    });
    const el=document.getElementById(map[t]);
    if(el){ el.classList.remove('d-none'); el.classList.add('d-flex'); if(t==='harian'){ el.classList.remove('d-flex'); el.style.display=''; } }
    document.querySelectorAll('[data-type]').forEach(b=>{ b.classList.remove('btn-tt'); b.classList.add('btn-outline-secondary'); if(b.getAttribute('data-type')===t){ b.classList.remove('btn-outline-secondary'); b.classList.add('btn-tt'); }});
  }
  document.querySelectorAll('[data-type]').forEach(b=>b.addEventListener('click',()=>show(b.getAttribute('data-type'))));
})();
</script>
