<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-wallet2 fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">KEUANGAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Biaya Operasional</h5>
      <small class="opacity-90">Daftar pengeluaran operasional &bull; hanya lihat</small>
    </div>
  </div>
</div>

<div class="tt-card mb-3">
  <form method="get" action="<?= url('index.php') ?>" class="row g-2 align-items-end">
    <input type="hidden" name="page" value="biaya-operasional">
    <div class="col-md-3">
      <label class="form-label small fw-semibold">Bulan</label>
      <select class="form-select" name="bulan">
        <?php for ($b = 1; $b <= 12; $b++): ?>
        <option value="<?= $b ?>" <?= $b === $MONTH ? 'selected' : '' ?>><?= e(bulan_indo($b)) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small fw-semibold">Tahun</label>
      <select class="form-select" name="tahun">
        <?php for ($t = 2024; $t <= 2027; $t++): ?>
        <option value="<?= $t ?>" <?= $t === $YEAR ? 'selected' : '' ?>><?= $t ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-tt w-100" type="submit"><i class="bi bi-search me-1"></i>Tampilkan</button>
    </div>
  </form>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="tt-card h-100"><span class="metric-label">Total Biaya Operasional</span>
    <div class="metric-value num" style="color:#dc3545"><?= rupiah($TOTAL) ?></div>
    <small class="text-muted"><?= e(bulan_indo($MONTH)) ?> <?= (int) $YEAR ?></small></div></div>
  <div class="col-md-4"><div class="tt-card h-100"><span class="metric-label">Jumlah Catatan</span>
    <div class="metric-value num"><?= count($ROWS) ?></div>
    <small class="text-muted">Transaksi biaya pada periode ini</small></div></div>
  <div class="col-md-4"><div class="tt-card h-100"><span class="metric-label">Jenis Biaya</span>
    <div class="metric-value num"><?= count(array_unique(array_map(fn ($r) => (string) ($r['expense_type_name'] ?? '-'), $ROWS))) ?></div>
    <small class="text-muted">Kategori pengeluaran</small></div></div>
</div>

<div class="tt-card">
  <div class="d-flex align-items-center gap-2 mb-2">
    <b><i class="bi bi-wallet2 me-1"></i>Daftar Biaya Operasional</b>
    <small class="ms-auto text-muted"><?= e(bulan_indo($MONTH)) ?> <?= (int) $YEAR ?></small>
  </div>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Jenis Biaya</th>
          <th>Keterangan</th>
          <th class="text-end">Jumlah (Rp)</th>
          <th>Dicatat Oleh</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ROWS as $r): ?>
        <tr>
          <td class="num text-nowrap"><?= e(date('d/m/Y', strtotime($r['cost_date']))) ?></td>
          <td><b><?= e($r['expense_type_name'] ?: '-') ?></b></td>
          <td class="small text-muted"><?= e($r['notes'] ?: '-') ?></td>
          <td class="text-end num fw-bold"><?= number_format($r['amount'], 0, ',', '.') ?></td>
          <td><?= e($r['created_by_name'] ?: '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$ROWS): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada biaya operasional pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
      <?php if ($ROWS): ?>
      <tfoot>
        <tr class="fw-bold"><td colspan="3">Total Biaya Operasional</td><td class="text-end num"><?= number_format($TOTAL, 0, ',', '.') ?></td><td></td></tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
  <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Halaman ini hanya untuk dilihat. Penambahan, edit, dan hapus biaya dilakukan oleh admin/kasir di halaman Transaksi.</small>
</div>
