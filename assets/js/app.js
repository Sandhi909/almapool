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
