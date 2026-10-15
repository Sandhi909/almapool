<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-qr-code-scan fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">SCAN & CHECK-IN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Kolam Renang — Scan QR</h5>
      <small class="opacity-90">Pintu Masuk Utama &bull; Petugas: <?= e(auth_user()['name']) ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex align-items-center gap-2" style="position:relative;z-index:1">
    <span class="badge bg-white text-success" style="border-radius:9999px;padding:.4rem .7rem"><span class="dot d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:var(--tt-green)"></span>Online</span>
  </div>
</div>

<div class="tt-card mb-3 d-flex align-items-center gap-3 flex-wrap">
  <b class="small">Gate / Kolam check-in:</b>
  <div class="btn-group btn-group-sm" role="group" id="gateGroup">
    <button type="button" class="btn btn-tt" data-gate="PUTRA">Putra <small>(<?= e($settings['sesi_putra'] ?? '') ?>)</small></button>
    <button type="button" class="btn btn-outline-secondary" data-gate="PUTRI">Putri <small>(<?= e($settings['sesi_putri'] ?? '') ?>)</small></button>
  </div>
  <small class="text-muted">Kolam terpisah putra &amp; putri — pastikan gate sesuai kolam.</small>
</div>

<div class="tt-card mb-1 p-2">
  <div id="reader" style="border-radius:14px;overflow:hidden"></div>
  <div id="scanIdle" class="scan-frame" style="min-height:260px">
    <span class="scan-corner sc-tl"></span><span class="scan-corner sc-tr"></span>
    <span class="scan-corner sc-bl"></span><span class="scan-corner sc-br"></span>
    <div class="text-center text-white px-4">
      <i class="bi bi-camera-video fs-1 d-block mb-2"></i>
      <b>Kamera belum aktif</b>
      <div class="small opacity-75">Ketuk tombol di bawah untuk menyalakan kamera &amp; memindai QR kartu member.</div>
    </div>
  </div>
  <div id="scanError" class="alert alert-warning py-2 small mb-0 mt-2" style="display:none"></div>
  <div class="d-grid gap-2 mt-2">
    <button class="btn btn-tt btn-lg" type="button" id="btnCam"><i class="bi bi-camera-video me-2"></i>MULAI PINDARI QR</button>
    <label class="btn btn-light" for="qrFile"><i class="bi bi-image me-2"></i>Ambil Foto QR (bila kamera tidak didukung)</label>
    <input type="file" id="qrFile" accept="image/*" capture="environment" class="d-none">
  </div>
  <small class="text-muted d-block mt-2"><i class="bi bi-lightbulb me-1"></i>Posisikan QR di dalam bingkai, cukup cahaya, tahan HP diam sampai fokus.</small>
</div>

<div class="tt-card mt-3">
  <div class="d-flex align-items-center gap-2 mb-1">
    <b><i class="bi bi-grid-3x3-gap me-1"></i>Input Manual ID Member</b>
    <button class="btn btn-link btn-sm ms-auto text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#keypadWrap">Tutup Keypad</button>
  </div>
  <small class="text-muted d-block mb-2">Untuk kartu fisik pudar atau layar redup.</small>
  <div class="collapse show" id="keypadWrap">
    <form method="get" action="<?= url('index.php') ?>">
      <input type="hidden" name="page" value="hasil-scan">
      <input type="hidden" name="area" id="gateArea" value="PUTRA">
      <div class="input-group mb-2">
        <span class="input-group-text fw-bold" style="color:var(--tt-primary)">MEM-</span>
        <input class="form-control form-control-lg num" id="manualId" name="id" inputmode="numeric" placeholder="000123" autocomplete="off">
      </div>
      <div class="keypad mb-2">
        <?php foreach (['1','2','3','4','5','6','7','8','9','C','0'] as $k): ?>
        <button type="button" data-key="<?= $k ?>"><?= $k ?></button>
        <?php endforeach; ?>
        <button type="submit" class="go" title="Cari"><i class="bi bi-arrow-right"></i></button>
      </div>
    </form>
  </div>
</div>

<div class="tt-card mt-3 d-flex align-items-center gap-3">
  <span class="metric-icon mi-blue"><i class="bi bi-people"></i></span>
  <div><small class="text-muted">Pintu Masuk</small><b class="d-block">Total Check-in Hari Ini</b></div>
  <b class="ms-auto num fs-2" style="color:var(--tt-primary)"><?= (int) ($checkinHari ?? 0) ?> <small class="text-muted fs-6">Member</small></b>
</div>

<?php ob_start(); ?>
<script src="<?= url('assets/js/vendor/html5-qrcode.min.js') ?>"></script>
<script src="<?= url('assets/js/vendor/jsQR.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('manualId');
  document.querySelectorAll('.keypad [data-key]').forEach(b => b.addEventListener('click', function () {
    if (this.dataset.key === 'C') input.value = input.value.slice(0, -1);
    else if (input.value.replace(/\D/g, '').length < 6) input.value += this.dataset.key;
  }));
  document.querySelectorAll('#gateGroup [data-gate]').forEach(b => b.addEventListener('click', function () {
    document.querySelectorAll('#gateGroup .btn').forEach(x => { x.classList.remove('btn-tt'); x.classList.add('btn-outline-secondary'); });
    this.classList.remove('btn-outline-secondary'); this.classList.add('btn-tt');
    document.getElementById('gateArea').value = this.dataset.gate;
  }));

  function area() { return document.getElementById('gateArea').value || 'PUTRA'; }
  function goScan(text) {
    text = (text || '').trim();
    let m = text.match(/MEM-\d+/i);
    if (!m && /^\d{1,6}$/.test(text)) m = ['MEM-' + text.padStart(6, '0')];
    if (!m) { Swal.fire({ icon: 'warning', title: 'QR tidak dikenali', text: 'Isi QR: ' + text }); return; }
    stopCam();
    location.href = '<?= url('index.php?page=hasil-scan') ?>&id=' + encodeURIComponent(m[0].toUpperCase()) + '&area=' + area();
  }

  let scanner = null, scanning = false;
  const btnCam = document.getElementById('btnCam');
  const errBox = document.getElementById('scanError');
  function showErr(msg) { errBox.style.display = ''; errBox.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + msg; }
  function camErrorMsg(e) {
    const name = (e && e.name ? e.name : '') + ' ' + (e && e.message ? e.message : e);
    if (/NotAllowed/i.test(name)) return 'Izin kamera <b>ditolak</b>. Ketuk ikon gembok/kamera di address bar browser → izinkan kamera → coba lagi.';
    if (/NotFound|Overconstrained/i.test(name)) return 'Tidak ada kamera yang cocok di perangkat ini. Gunakan Ambil Foto QR.';
    if (/NotReadable|Abort/i.test(name)) return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera lalu coba lagi.';
    if (/Secure|Security/i.test(name) || !window.isSecureContext) return 'Browser memblokir kamera pada alamat HTTP. Gunakan tombol <b>Ambil Foto QR</b>, atau buka via HTTPS.';
    return 'Kamera tidak bisa dibuka (' + (e && e.message ? e.message : e) + '). Gunakan Ambil Foto QR.';
  }

  function stopCam() {
    if (scanner && scanning) {
      scanner.stop().then(function () { scanner.clear(); }.bind(this)).catch(function () {});
      scanning = false;
      btnCam.innerHTML = '<i class="bi bi-camera-video me-2"></i>MULAI PINDARI QR';
      document.getElementById('scanIdle').style.display = '';
    }
  }

  btnCam.addEventListener('click', function () {
    if (scanning) { stopCam(); return; }
    errBox.style.display = 'none';
    const insecureHost = !window.isSecureContext && !/^(localhost|127\.)/.test(location.hostname);
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      showErr(insecureHost
        ? 'Browser <b>tidak menyediakan kamera</b> pada alamat HTTP ini (tidak ada prompt izin sama sekali). Ini batasan browser, bukan error aplikasi.<br>Gunakan tombol <b>Ambil Foto QR</b> di bawah — hasilnya sama.'
        : 'Perangkat/browser ini tidak mendukung akses kamera. Gunakan <b>Ambil Foto QR</b>.');
      return;
    }
    try {
      if (typeof Html5Qrcode === 'undefined') { showErr('Pustaka kamera gagal dimuat. Gunakan Ambil Foto QR.'); return; }
      scanner = new Html5Qrcode('reader');
    } catch (e) { showErr(camErrorMsg(e)); return; }
    let cams;
    try {
      cams = Html5Qrcode.getCameras();
    } catch (e) { showErr(camErrorMsg(e)); return; }
    Promise.resolve(cams).then(function (list) {
      if (!list || !list.length) { showErr('Tidak ada kamera ditemukan di perangkat ini. Gunakan Ambil Foto QR.'); return; }
      document.getElementById('scanIdle').style.display = 'none';
      return scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 250 } }, goScan, function () {})
        .then(function () {
          scanning = true;
          btnCam.innerHTML = '<i class="bi bi-stop-circle me-2"></i>BERHENTI';
        });
    }).catch(function (e) {
      document.getElementById('scanIdle').style.display = '';
      showErr(camErrorMsg(e));
    });
  });

  // Fallback: foto dari kamera bawaan HP -> decode jsQR (jalan di HTTP).
  // Dibaca berkali-kali: 4 arah putar + normal/invers + area tengah, resolusi penuh.
  function decodeCanvas(cv) {
    try {
      const ctx = cv.getContext('2d');
      return jsQR(ctx.getImageData(0, 0, cv.width, cv.height).data, cv.width, cv.height, { inversionAttempts: 'attemptBoth' });
    } catch (e) { return null; }
  }
  function drawRotated(cv, img, deg) {
    const rad = deg * Math.PI / 180;
    const swap = deg === 90 || deg === 270;
    const maxDim = 1600;
    let w = img.width, h = img.height;
    const s = Math.min(1, maxDim / Math.max(w, h));
    w = Math.round(w * s); h = Math.round(h * s);
    cv.width = swap ? h : w; cv.height = swap ? w : h;
    const ctx = cv.getContext('2d');
    ctx.save();
    ctx.translate(cv.width / 2, cv.height / 2);
    ctx.rotate(rad);
    ctx.drawImage(img, -w / 2, -h / 2, w, h);
    ctx.restore();
  }
  document.getElementById('qrFile').addEventListener('change', function () {
    const f = this.files && this.files[0];
    if (!f) return;
    Swal.fire({ title: 'Membaca foto...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
    const img = new Image();
    img.onload = function () {
      const cv = document.createElement('canvas');
      let found = null;
      const angles = [0, 90, 180, 270];
      for (let i = 0; i < angles.length && !found; i++) {
        drawRotated(cv, img, angles[i]);
        found = decodeCanvas(cv);
      }
      if (!found) {
        // coba potongan tengah 65% (QR kecil di foto lebar)
        const cw = Math.round(img.width * 0.65), ch = Math.round(img.height * 0.65);
        const s = Math.min(1, 1600 / Math.max(cw, ch));
        cv.width = Math.round(cw * s); cv.height = Math.round(ch * s);
        const ctx = cv.getContext('2d');
        ctx.drawImage(img, (img.width - cw) / 2, (img.height - ch) / 2, cw, ch, 0, 0, cv.width, cv.height);
        found = decodeCanvas(cv);
      }
      Swal.close();
      URL.revokeObjectURL(img.src);
      document.getElementById('qrFile').value = '';
      if (found && found.data) goScan(found.data);
      else Swal.fire({ icon: 'warning', title: 'QR tidak terbaca', html: 'Tips agar terbaca:<br>1. Dekatkan hingga QR memenuhi layar<br>2. Cukup cahaya, hindari silau<br>3. Tahan HP diam &amp; fokus<br>4. Coba lagi sekali' });
    };
    img.onerror = function () { Swal.close(); Swal.fire({ icon: 'error', title: 'Gagal membuka foto' }); };
    img.src = URL.createObjectURL(f);
  });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
