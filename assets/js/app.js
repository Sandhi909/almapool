// TirtaTrack — JS global: sidebar mobile + tombol demo.
document.addEventListener('DOMContentLoaded', function () {
  const sb = document.getElementById('btnSidebar');
  const side = document.getElementById('ttSidebar');
  if (sb && side) {
    sb.addEventListener('click', function () { side.classList.toggle('open'); });
    document.addEventListener('click', function (ev) {
      if (window.innerWidth < 992 && side.classList.contains('open')
          && !side.contains(ev.target) && ev.target !== sb && !sb.contains(ev.target)) {
        side.classList.remove('open');
      }
    });
  }

  // Tombol bertanda data-demo = aksi backend (tahap berikutnya).
  document.querySelectorAll('[data-demo]').forEach(function (el) {
    el.addEventListener('click', function () {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'info', title: 'Segera hadir di tahap backend', text: 'Tombol ini aktif setelah modul backend dibuat.', confirmButtonText: 'OK' });
      }
    });
  });
});

/* ==========================================================================
   Global barcode listener — Cashcow P10 (USB-HID / keyboard wedge).
   Bekerja di halaman mana pun yang diizinkan scan tanpa perlu klik input.
   Scan diarahkan ke route hasil-scan yang sudah ada (auto-checkin tetap
   dijalankan oleh ScanController::result()).
   ========================================================================== */
(function () {
  var cfg = window.ALMAPOOL_SCAN;
  if (!cfg || !cfg.enabled || !cfg.resultUrl) return;
  if (window.almapoolBarcodeReady) return; // cegah listener terpasang ganda
  window.almapoolBarcodeReady = true;

  var MAX_GAP = 60;    // ms — jeda maks antar karakter scanner (USB-HID jauh lebih cepat)
  var IDLE_MS = 140;   // ms — fallback bila scanner tidak mengirim Enter
  var MIN_LEN = 3;     // barcode lebih pendek diabaikan (hindari ketukan kebetulan)
  var DUP_MS = 2500;   // jeda proteksi untuk kode yang sama
  var MAX_LEN = 64;

  var buf = '';
  var lastKeyAt = 0;
  var idleTimer = null;
  var navTimer = null;
  var lastCode = '';
  var lastCodeAt = 0;
  var badge = null;

  // Normalisasi barcode — dipakai juga oleh halaman Scan (goScan).
  function normalize(text) {
    text = String(text == null ? '' : text).trim();
    if (!text) return null;
    var m = text.match(/MEM-\d+/i);
    if (!m && /^\d{1,6}$/.test(text)) m = ['MEM-' + text.padStart(6, '0')];
    return m ? m[0].toUpperCase() : null;
  }
  window.almapoolNormalizeBarcode = normalize;

  function isEditable(el) {
    if (!el || !el.tagName) return false;
    var tag = el.tagName.toLowerCase();
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return true;
    if (el.isContentEditable) return true;
    return !!(el.closest && el.closest('[contenteditable="true"]'));
  }

  function area() {
    var saved = '';
    try { saved = localStorage.getItem('almapool_scan_area') || ''; } catch (e) {}
    var a = String(window.ALMAPOOL_SCAN_AREA || saved || 'PUTRA').toUpperCase();
    return a === 'PUTRI' ? 'PUTRI' : 'PUTRA';
  }

  function showProcessing(code) {
    if (!badge) {
      badge = document.createElement('div');
      badge.setAttribute('role', 'status');
      badge.style.cssText = 'position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:2147483000;' +
        'background:#0f172a;color:#fff;padding:9px 16px;border-radius:9999px;font:600 13px/1.2 system-ui,sans-serif;' +
        'box-shadow:0 8px 24px rgba(0,0,0,.25);display:flex;align-items:center;gap:9px';
      badge.innerHTML = '<span style="width:14px;height:14px;border:2px solid rgba(255,255,255,.35);' +
        'border-top-color:#fff;border-radius:50%;display:inline-block;animation:almapoolSpin .7s linear infinite"></span>' +
        '<span data-code></span>';
      var st = document.createElement('style');
      st.textContent = '@keyframes almapoolSpin{to{transform:rotate(360deg)}}';
      document.head.appendChild(st);
      document.body.appendChild(badge);
    }
    badge.querySelector('[data-code]').textContent = 'Memproses ' + code + '…';
    badge.style.display = 'flex';
  }

  function resetBuffer() {
    buf = '';
    if (idleTimer) { clearTimeout(idleTimer); idleTimer = null; }
  }

  function handle(raw) {
    raw = String(raw || '').trim();
    if (raw.length < MIN_LEN) return;
    var code = normalize(raw);
    var now = Date.now();
    if (!code) {
      // Hanya tampilkan peringatan untuk string yang menyerupai barcode (mengandung angka),
      // agar ketikan manusia cepat tidak memicu notifikasi palsu.
      if (raw.length >= 4 && /\d/.test(raw) && typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Barcode tidak dikenali', text: 'Isi barcode: ' + raw });
      }
      return;
    }
    if (code === lastCode && (now - lastCodeAt) < DUP_MS) return; // scan ganda kode sama
    lastCode = code;
    lastCodeAt = now;

    showProcessing(code);
    if (navTimer) clearTimeout(navTimer); // kode berbeda -> ikuti kode terbaru
    navTimer = setTimeout(function () {
      navTimer = null;
      window.location.assign(cfg.resultUrl + '&id=' + encodeURIComponent(code) + '&area=' + area());
    }, 180);
  }

  function scheduleIdle() {
    if (idleTimer) clearTimeout(idleTimer);
    idleTimer = setTimeout(function () {
      idleTimer = null;
      var raw = buf;
      buf = '';
      handle(raw);
    }, IDLE_MS);
  }

  document.addEventListener('keydown', function (ev) {
    if (ev.defaultPrevented) return;
    if (ev.ctrlKey || ev.altKey || ev.metaKey) return; // jangan ganggu shortcut browser/aplikasi
    if (isEditable(ev.target)) return;                // jangan ganggu input manual/textarea/select
    var key = ev.key || '';
    if (key === 'Enter') {                            // penanda akhir dari scanner
      if (!buf) return;
      var rawEnter = buf;
      resetBuffer();
      ev.preventDefault();
      handle(rawEnter);
      return;
    }
    if (key.length !== 1) return;                     // Shift, Tab, panah, F1, dsb.
    if (!/^[\x20-\x7e]$/.test(key)) return;           // hanya karakter printable
    var now = Date.now();
    if (now - lastKeyAt > MAX_GAP) buf = '';          // terlalu lambat -> bukan scanner
    lastKeyAt = now;
    buf += key;
    if (buf.length > MAX_LEN) buf = buf.slice(-MAX_LEN);
    scheduleIdle();
  }, true);
})();
