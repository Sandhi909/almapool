<?php $m = $SCAN_MEMBER; ?>
<div class="page-hero p-3 p-md-3 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon" style="width:40px;height:40px"><i class="bi bi-qr-code-scan"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">HASIL SCAN</small>
      <b style="font-family:'Plus Jakarta Sans'">Kolam Renang — Scan QR</b>
    </div>
  </div>
  <span class="badge bg-white text-success ms-auto" style="position:relative;z-index:1;border-radius:9999px"><span class="dot d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:var(--tt-green)"></span>Online</span>
</div>

<?php $auto = $auto_checkin ?? null; ?>
<?php if ($auto && $auto['ok']): ?>
<div class="alert d-flex align-items-center gap-2 py-2 mb-2" style="background:linear-gradient(135deg,#ecfdf5,#f0fdfa);border:1px solid #a7f3d0;border-radius:12px">
  <i class="bi bi-check-circle-fill fs-5" style="color:#10b981"></i>
  <div><b>Check-in otomatis berhasil!</b><br><small class="text-muted"><?= e($auto['message']) ?> Data kunjungan sudah masuk ke <b>member_visits</b> (<?= e($area ?? 'PUTRA') ?>) pada <?= date('d/m/Y H:i') ?>.</small></div>
  <span class="badge bg-success ms-auto">Tercatat</span>
</div>
<?php elseif ($auto && !$auto['ok']): ?>
<div class="alert alert-warning py-2 small mb-2"><i class="bi bi-exclamation-triangle me-1"></i><?= e($auto['message']) ?></div>
<?php endif; ?>
<?php if (!$m): ?>
<div class="tt-card text-center py-5">
  <i class="bi bi-qr-code fs-1 d-block mb-2 text-muted"></i>
  <h5 class="fw-bold">QR Code tidak valid atau member tidak ditemukan</h5>
  <p class="text-muted">Periksa kembali kartu member atau masukkan ID manual.</p>
  <a class="btn btn-tt" href="<?= url('index.php?page=scan') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali ke Scan</a>
</div>
<?php else:
  $isExpired = $m['status'] === 'Expired';
  $sudah = ($m['kunjungan'] === 'Sudah Check-in');
?>
<div class="result-banner <?= $isExpired ? 'result-exp' : 'result-ok' ?> mb-2">
  <span class="metric-icon" style="background:<?= $isExpired ? '#991b1b' : '#065f46' ?>;color:#fff">
    <i class="bi <?= $isExpired ? 'bi-x-lg' : 'bi-check-lg' ?>"></i></span>
  <div><b class="fs-5"><?= $isExpired ? 'MEMBERSHIP EXPIRED' : 'MEMBER AKTIF' ?></b><br>
    <small><?= $isExpired ? 'Check-in ditolak — arahkan ke kasir' : 'Akses Gerbang Turnstile Terbuka' ?></small></div>
  <span class="pill ms-auto" style="background:#fff">Valid s/d <?= e($m['berlaku']) ?></span>
</div>
<div class="d-flex align-items-center gap-2 mb-3">
  <small><span class="dot d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:var(--tt-green)"></span>Masa Berlaku Kuota</small>
  <b class="ms-auto" style="color:var(--tt-teal)"><?= e($m['sisa']) ?></b>
</div>

<div class="tt-card text-center mb-3">
  <div class="tt-avatar mx-auto mb-2" style="width:84px;height:84px;font-size:1.8rem"><?= e(initials($m['nama'])) ?></div>
  <h3 class="fw-bold mb-0"><?= e($m['nama']) ?></h3>
  <div class="text-muted"><i class="bi bi-person-badge me-1"></i><span class="num"><?= e($m['nomor']) ?></span><br>
  <i class="bi bi-telephone me-1"></i><span class="num"><?= e($m['hp']) ?></span></div>
</div>

<div class="tt-card mb-3">
  <b><?= e($m['paket']) ?></b>
  <div class="row g-2 mt-2">
    <div class="col-6"><div class="rounded-3 p-2" style="background:var(--tt-canvas)">
      <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>Kunjungan Hari Ini</small>
      <b class="d-block"><?= e($m['kunjungan']) ?></b></div></div>
    <div class="col-6"><div class="rounded-3 p-2" style="background:var(--tt-canvas)">
      <small class="text-muted"><i class="bi bi-clock-history me-1"></i>Terakhir</small>
      <b class="d-block num"><?= e($m['terakhir'] !== '-' ? date('d/m H:i', strtotime($m['terakhir'])) : '-') ?></b></div></div>
  </div>
</div>

<?php if (!$isExpired && !$sudah): ?>
<?php if (($m['tier'] ?? 'SILVER') === 'GOLD'): ?>
<div class="alert alert-success py-2 small"><i class="bi bi-patch-check me-1"></i>Member <b>Gold</b> — bebas berkunjung kapan saja, tanpa biaya tambahan.</div>
<?php elseif (!empty($m['libur'])): ?>
<div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>Hari libur — member <b>Silver</b> tetap bisa masuk dengan <b>biaya tambahan <?= rupiah($m['fee']) ?></b> (tunai di gate).</div>
<?php endif; ?>
<form method="post" action="<?= url('index.php?page=scan') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="member_id" value="<?= (int) $m['id'] ?>">
  <div class="tt-card mb-3 d-flex align-items-center gap-2">
    <b class="small">Kolam:</b>
    <div class="btn-group btn-group-sm" role="group">
      <input type="radio" class="btn-check" name="area" id="arPutra" value="PUTRA" <?= ($area ?? 'PUTRA') === 'PUTRA' ? 'checked' : '' ?>>
      <label class="btn btn-outline-primary" for="arPutra">Putra</label>
      <input type="radio" class="btn-check" name="area" id="arPutri" value="PUTRI" <?= ($area ?? '') === 'PUTRI' ? 'checked' : '' ?>>
      <label class="btn btn-outline-primary" for="arPutri">Putri</label>
    </div>
  </div>
  <button class="btn btn-tt btn-lg w-100 mb-2" type="submit"><i class="bi bi-box-arrow-in-right me-2"></i><?= !empty($m['libur']) && ($m['tier'] ?? '') !== 'GOLD' ? 'CHECK-IN + BIAYA ' . rupiah($m['fee']) : 'CHECK-IN SEKARANG' ?></button>
</form>
<?php elseif ($sudah && !($auto && $auto['ok'])): ?>
<div class="alert alert-warning">Member sudah melakukan kunjungan hari ini.</div>
<?php else: ?>
<div class="alert alert-danger">Member tidak dapat check-in. Silakan perpanjangan di kasir.</div>
<?php endif; ?>
<a class="btn btn-light btn-lg w-100" href="<?= url('index.php?page=scan') ?>"><i class="bi bi-qr-code-scan me-2"></i>Batal / Scan Kartu Lain</a>
<?php endif; ?>
