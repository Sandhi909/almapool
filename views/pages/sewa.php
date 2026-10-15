<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-tools fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">SEWA ALAT & FASILITAS</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Sewa Peralatan Kolam</h5>
      <small class="opacity-90">Catat transaksi sewa alat renang & fasilitas • Nominal bebas</small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2" style="position:relative;z-index:1">
    <button class="btn bg-white text-primary fw-bold btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#sewaModal" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-plus-lg me-1"></i>Transaksi Baru</button>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Transaksi Hari Ini</span><div class="metric-value num"><?= number_format(count($TRANSAKSI), 0, ',', '.') ?></div></div>
    <span class="metric-icon mi-blue"><i class="bi bi-receipt"></i></span></div></div>
  <div class="col-md-4"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Total Pendapatan Sewa</span><div class="metric-value"><?= rupiah_short($shiftStat['total']) ?></div></div>
    <span class="metric-icon mi-green"><i class="bi bi-cash-stack"></i></span></div></div>
  <div class="col-md-4"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Rata-rata per Transaksi</span><div class="metric-value"><?= count($TRANSAKSI) > 0 ? rupiah_short($shiftStat['total'] / count($TRANSAKSI)) : 'Rp0' ?></div></div>
    <span class="metric-icon mi-teal"><i class="bi bi-graph-up"></i></span></div></div>
</div>

<div class="tt-card">
  <b class="d-block mb-2"><i class="bi bi-clock-history me-1"></i>Riwayat Transaksi Hari Ini</b>
  <?php if (!$TRANSAKSI): ?>
    <div class="text-center text-muted py-4">Belum ada transaksi sewa hari ini.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead><tr><th>No. Transaksi</th><th>Waktu</th><th>Keterangan</th><th>Jumlah</th><th>Pembayaran</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($TRANSAKSI as $t): ?>
        <tr>
          <td><span class="pill pill-blue num"><?= e($t['number']) ?></span></td>
          <td><span class="num"><?= date('H:i', strtotime($t['created_at'])) ?></span><br><small class="text-muted"><?= tanggal_indo($t['created_at']) ?></small></td>
          <td><?= e($t['notes'] ?: 'Sewa alat') ?></td>
          <td><b><?= rupiah($t['amount']) ?></b></td>
          <td><span class="pill <?= $t['payment_method'] === 'TUNAI' ? 'pill-green' : 'pill-blue' ?>"><?= e($t['payment_method']) ?></span></td>
          <td><a class="btn btn-light btn-sm" href="<?= url('index.php?page=struk&id=' . (int) $t['id']) ?>" target="_blank"><i class="bi bi-receipt"></i> Struk</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="modal fade" id="sewaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Transaksi Sewa Alat Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="sewaForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Alat / Keterangan</label>
            <input type="text" class="form-control" name="keterangan" placeholder="Contoh: Pelampung, Ban Renang, Kacamata" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah (Rp)</label>
            <input type="number" class="form-control" name="jumlah" placeholder="0" min="0" step="1000" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Metode Pembayaran</label>
            <select class="form-select" name="payment" required>
              <option value="TUNAI">Tunai</option>
              <option value="QRIS">QRIS</option>
              <option value="TRANSFER">Transfer</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-tt">Simpan Transaksi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php ob_start(); ?>
<script>
document.getElementById('sewaForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const form = e.target;
  const keterangan = form.keterangan.value.trim();
  const jumlah = parseFloat(form.jumlah.value) || 0;
  const payment = form.payment.value;
  
  if (!keterangan || jumlah <= 0) {
    alert('Keterangan dan jumlah wajib diisi.');
    return;
  }
  
  fetch('<?= url('index.php?page=kasir&ajax=checkout') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      tarif: 'A', kelas: 'Gold', area: 'PUTRA', payment: payment,
      items: [{kind: 'sewa', amount: jumlah, nama: keterangan}]
    })
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      alert('Transaksi sewa berhasil. No: ' + d.number);
      location.reload();
    } else {
      alert('Error: ' + d.message);
    }
  })
  .catch(() => alert('Gagal menghubungi server.'));
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
