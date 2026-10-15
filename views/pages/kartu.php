<?php
$ms = $m['membership'];
$tier = strtoupper($ms['tier_snapshot'] ?? 'SILVER');
$isGold = $tier === 'GOLD';
$accent = $isGold ? '#f5c044' : '#cbd5e1';
$accent2 = $isGold ? '#b8860b' : '#64748b';
?>
<style>
.mcard { width: 100%; max-width: 430px; aspect-ratio: 1.586; border-radius: 18px; overflow: hidden; position: relative;
  box-shadow: 0 18px 40px -12px rgba(2, 32, 71, .45); font-family: 'Plus Jakarta Sans', sans-serif; }
.mcard-front { color: #fff; padding: 18px 34px; display: flex; flex-direction: column; justify-content: center;
  background: linear-gradient(130deg, #04263e 0%, #075985 42%, #0284c7 68%, #0d9488 100%); }
.mcard-front::before { content: ''; position: absolute; inset: 0;
  background:
    radial-gradient(circle at 88% 12%, rgba(255,255,255,.22) 0 46px, transparent 47px),
    radial-gradient(circle at 96% 30%, rgba(255,255,255,.12) 0 70px, transparent 71px),
    radial-gradient(circle at -30px 110%, rgba(245,192,68,.35) 0 90px, transparent 91px);
  pointer-events: none; }
.mcard-front::after { content: ''; position: absolute; inset: 0;
  background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.14) 45%, transparent 60%); pointer-events: none; }
.mcard-waves { position: absolute; bottom: -6px; left: 0; right: 0; opacity: .35; pointer-events: none; }
.mcard-top { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
.mcard-logo { height: 44px; width: 44px; border-radius: 50%; background: #fff; padding: 2px; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
.mcard-brand .b1 { display: block; font-size: 17px; letter-spacing: .04em; line-height: 1.05; font-weight: 800; }
.mcard-brand .b2 { display: block; font-size: 17px; letter-spacing: .04em; line-height: 1.05; font-weight: 800; }
.mcard-brand .pp { display: block; font-size: 10px; letter-spacing: .18em; line-height: 1.2; opacity: .9; margin-top: 1px; }
.mcard-foot { text-align: center; font-size: 14px; letter-spacing: .3em; font-weight: 700; opacity: .85; margin-top: 14px; position: relative; z-index: 1; }
.mcard-tier { margin-left: auto; text-align: right; }
.mcard-tier .t { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: .18em; padding: 4px 12px; border-radius: 999px;
  color: #04263e; background: linear-gradient(180deg, <?= $accent ?>, <?= $accent2 ?>); box-shadow: 0 2px 6px rgba(0,0,0,.35); }
.mcard-name { font-weight: 700; line-height: 1.15; white-space: nowrap; max-width: 100%; overflow: hidden; text-shadow: 0 2px 6px rgba(0,0,0,.35); }
.mcard-bot2 { display: flex; align-items: center; gap: 10px; margin-top: 6px; position: relative; z-index: 1; }
.mcard-idwrap { flex: 1; min-width: 0; }
.mcard-idwrap small { display: block; font-size: 9px; letter-spacing: .22em; line-height: 1; opacity: .8; margin-top: 3px; }
.mcard-num { font-family: 'Courier New', monospace; font-size: 16px; font-weight: 700; letter-spacing: .14em; opacity: .95; }
.mcard-qr { margin-left: auto; background: #fff; border-radius: 12px; padding: 7px; line-height: 0; border: 2px solid <?= $accent ?>; }
.mcard-back { background: #fff; color: #0f172a; padding: 28px 34px; display: flex; flex-direction: column; border: 1px solid #e2e8f0; }
.mcard-back .rule { font-size: 10.5px; color: #475569; }
.mcard-strip { height: 34px; margin: 0 -34px 12px; background: linear-gradient(90deg, #04263e, #0284c7 60%, #0d9488); }
.mcard-sign { border-top: 1px dashed #94a3b8; margin-top: auto; padding-top: 6px; display: flex; justify-content: space-between; font-size: 10px; color: #475569; }
@media print {
  @page { size: auto; margin: 15mm; }
  .mcard { box-shadow: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .kasir-hero, .no-print { display: none !important; }
  .print-area { padding: 5mm; }
  .print-area > div { margin-bottom: 8mm; }
}
</style>

<div class="kasir-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative no-print" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white" style="width:48px;height:48px;color:#0ea5e9;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-person-vcard fs-4"></i></span>
    <div>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Kartu Member — <?= e(strtoupper($m['name'])) ?></h5>
      <small class="opacity-90"><?= e($m['member_number']) ?> &bull; <?= e($ms ? $ms['package_name_snapshot'] : '-') ?> &bull; <?= e($tier) ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 no-print" style="position:relative;z-index:1">
    <a class="btn bg-white text-primary fw-bold btn-sm" href="<?= url('index.php?page=member') ?>" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <div class="dropdown">
      <button class="btn btn-warning fw-bold btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-download me-1"></i>Download</button>
      <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:14px;overflow:hidden">
        <li><h6 class="dropdown-header">Tampak Depan</h6></li>
        <li><a class="dropdown-item" href="#" onclick="return downloadKartu('cardFront','png')"><i class="bi bi-file-image me-2 text-primary"></i>Depan — PNG</a></li>
        <li><a class="dropdown-item" href="#" onclick="return downloadKartu('cardFront','jpg')"><i class="bi bi-file-image me-2 text-success"></i>Depan — JPG</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><h6 class="dropdown-header">Tampak Belakang</h6></li>
        <li><a class="dropdown-item" href="#" onclick="return downloadKartu('cardBack','png')"><i class="bi bi-file-image me-2 text-primary"></i>Belakang — PNG</a></li>
        <li><a class="dropdown-item" href="#" onclick="return downloadKartu('cardBack','jpg')"><i class="bi bi-file-image me-2 text-success"></i>Belakang — JPG</a></li>
      </ul>
    </div>
    <button class="btn bg-white bg-opacity-10 border border-white border-opacity-25 text-white btn-sm" type="button" onclick="window.print()" style="border-radius:12px;backdrop-filter:blur(8px)"><i class="bi bi-printer me-1"></i>Cetak Kartu</button>
  </div>
</div>

<div class="row g-4 justify-content-center print-area">
  <div class="col-md-6 d-flex flex-column align-items-center">
    <div class="mcard mcard-front" id="cardFront">
      <svg class="mcard-waves" viewBox="0 0 430 70" preserveAspectRatio="none" style="height:64px;width:100%">
        <path d="M0,40 Q40,20 80,40 T160,40 T240,40 T320,40 T400,40 T480,40" fill="none" stroke="#ffffff" stroke-width="2.5"/>
        <path d="M0,52 Q40,34 80,52 T160,52 T240,52 T320,52 T400,52 T480,52" fill="none" stroke="<?= $accent ?>" stroke-width="2.5"/>
      </svg>
      <div class="mcard-top">
        <img src="<?= url(APP_LOGO) ?>" alt="Logo" class="mcard-logo">
        <div class="mcard-brand"><span class="b1">KOLAM RENANG</span><span class="b2">AL MA'SOEM</span><span class="pp">TERPISAH PUTRA PUTRI</span></div>
        <div class="mcard-tier"><span class="t"><?= e(ucfirst(strtolower($tier))) ?></span></div>
      </div>
      <div class="mcard-foot">KARTU MEMBER</div>
      <?php $nmLen = mb_strlen($m['name']); $nmSize = max(11, min(26, (int) floor(238 / (0.68 * max(1, $nmLen))))); ?>
      <div class="mcard-bot2">
        <div class="mcard-idwrap">
          <div class="mcard-name" id="mcardName" data-size="<?= $nmSize ?>" style="font-size:<?= $nmSize ?>px"><?= e(strtoupper($m['name'])) ?></div>
          <small>ID MEMBER</small><div class="mcard-num"><?= e($m['member_number']) ?></div>
        </div>
        <div class="mcard-qr"><div id="qrBox"></div></div>
      </div>
    </div>
    <small class="text-muted mt-1 no-print">Tampak depan</small>
    <div class="btn-group btn-group-sm mt-2 no-print" role="group">
      <button type="button" class="btn btn-outline-primary" onclick="downloadKartu('cardFront','png')"><i class="bi bi-download me-1"></i>PNG</button>
      <button type="button" class="btn btn-outline-success" onclick="downloadKartu('cardFront','jpg')"><i class="bi bi-download me-1"></i>JPG</button>
    </div>
  </div>

  <div class="col-md-6 d-flex flex-column align-items-center">
    <div class="mcard mcard-back" id="cardBack">
      <div class="mcard-strip"></div>
      <b style="font-size:13px;letter-spacing:.06em">KETENTUAN PENGGUNAAN KARTU</b>
      <ol class="rule ps-3 mt-2 mb-2">
        <li>Kartu ini adalah identitas member &amp; wajib dibawa setiap berkunjung.</li>
        <li>Berlaku <b>satu check-in per hari</b>; kolam putra & putri terpisah.</li>
        <li>Hilang/rusak dapat <b>cetak ulang tanpa member baru</b> (QR tetap sama).</li>
        <li>Dilarang dipinjamkan ke orang lain.</li>
      </ol>
      <div class="mcard-sign"><span><?= e($m['member_number']) ?></span><span><?= e($settings['nama_kolam'] ?? '') ?></span></div>
    </div>
    <small class="text-muted mt-1 no-print">Tampak belakang</small>
    <div class="btn-group btn-group-sm mt-2 no-print" role="group">
      <button type="button" class="btn btn-outline-primary" onclick="downloadKartu('cardBack','png')"><i class="bi bi-download me-1"></i>PNG</button>
      <button type="button" class="btn btn-outline-success" onclick="downloadKartu('cardBack','jpg')"><i class="bi bi-download me-1"></i>JPG</button>
    </div>
  </div>
</div>

<?php ob_start(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new QRCode(document.getElementById('qrBox'), { text: '<?= e($m['qr_identifier']) ?>', width: 92, height: 92, correctLevel: QRCode.CorrectLevel.M });
  // Pas nama satu baris: kecilkan font sampai muat (pengaman presisi selain hitungan PHP).
  var nm = document.getElementById('mcardName');
  if (nm) {
    var s = parseInt(nm.dataset.size || '26', 10);
    var guard = 0;
    while (nm.scrollWidth > nm.clientWidth && s > 10 && guard < 30) { s--; nm.style.fontSize = s + 'px'; guard++; }
  }
});

// Download kartu member sebagai PNG / JPG (resolusi tinggi 3x).
function downloadKartu(targetId, format) {
  format = (format || 'png').toLowerCase() === 'jpg' ? 'jpg' : 'png';
  var el = document.getElementById(targetId);
  if (!el) return false;
  if (typeof html2canvas === 'undefined') { alert('Pustaka html2canvas belum termuat. Cek koneksi internet.'); return false; }
  var sisi = targetId === 'cardBack' ? 'belakang' : 'depan';
  var fname = 'Kartu-Member-<?= e(preg_replace("/[^A-Za-z0-9-_]+/", "-", $m["member_number"])) ?>-' + sisi + '.' + format;
  var mime = format === 'jpg' ? 'image/jpeg' : 'image/png';
  html2canvas(el, {
    scale: 3,
    useCORS: true,
    backgroundColor: format === 'jpg' ? '#ffffff' : null,
    logging: false
  }).then(function (canvas) {
    var link = document.createElement('a');
    link.download = fname;
    link.href = canvas.toDataURL(mime, format === 'jpg' ? 0.95 : 1.0);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }).catch(function (err) {
    console.error(err);
    alert('Gagal mengunduh kartu: ' + err);
  });
  return false;
}
</script>
<?php $pageScripts = ob_get_clean(); ?>
