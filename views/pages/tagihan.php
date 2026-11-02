<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-receipt fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">KEUANGAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Transaksi Tagihan</h5>
      <small class="opacity-90">Daftar tiket tagihan & transaksi dengan nominal tagihan</small>
    </div>
  </div>
</div>

<div class="tt-card mb-3">
  <form method="get" action="<?= url('index.php') ?>" class="row g-2 align-items-end">
    <input type="hidden" name="page" value="tagihan">
    <div class="col-md-3">
      <label class="form-label small fw-semibold">Dari Tanggal</label>
      <input type="date" class="form-control" name="dari" value="<?= e($FILTER['from']) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small fw-semibold">Sampai Tanggal</label>
      <input type="date" class="form-control" name="sampai" value="<?= e($FILTER['to']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label small fw-semibold">Cari</label>
      <input class="form-control" name="q" value="<?= e($FILTER['q']) ?>" placeholder="Nomor transaksi / kasir / tiket...">
    </div>
    <div class="col-md-2">
      <button class="btn btn-tt w-100" type="submit"><i class="bi bi-search me-1"></i>Tampilkan</button>
    </div>
  </form>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3"><div class="tt-card h-100"><span class="metric-label">Jumlah Transaksi</span>
    <div class="metric-value num"><?= (int) $SUM['count'] ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card h-100"><span class="metric-label">Nilai Tiket Tagihan</span>
    <div class="metric-value num" style="color:#0284c7"><?= rupiah($SUM['nilai_tiket']) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card h-100"><span class="metric-label">Nominal Tagihan</span>
    <div class="metric-value num" style="color:#dc3545"><?= rupiah($SUM['tagihan']) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card h-100"><span class="metric-label">Total Transaksi</span>
    <div class="metric-value num" style="color:var(--tt-primary)"><?= rupiah($SUM['total']) ?></div></div></div>
</div>

<div class="tt-card">
  <div class="d-flex align-items-center gap-2 mb-2">
    <b><i class="bi bi-receipt me-1"></i>Daftar Transaksi Tagihan</b>
    <small class="ms-auto text-muted"><?= e(tanggal_indo($FILTER['from'])) ?> &ndash; <?= e(tanggal_indo($FILTER['to'])) ?></small>
  </div>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Nomor</th>
          <th>Kasir</th>
          <th>Tiket / Detail</th>
          <th>Kolam</th>
          <th class="text-end">Total</th>
          <th>Metode</th>
          <th class="text-end">Nilai Tiket</th>
          <th class="text-end">Tagihan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ROWS as $r): ?>
        <tr>
          <td class="num text-nowrap"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
          <td class="num fw-bold" style="color:var(--tt-primary)"><?= e($r['number']) ?></td>
          <td><?= e($r['kasir'] ?: '-') ?></td>
          <td style="max-width:320px">
            <?php if (!empty($r['is_tagihan_ticket'])): ?><span class="pill pill-blue" style="font-size:.62rem">Tiket Tagihan</span><br><?php endif; ?>
            <small title="<?= e($r['items']) ?>" style="display:inline-block;max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($r['items'] ?: '-') ?></small>
          </td>
          <td><span class="pill <?= $r['area']==='PUTRI'?'pill-amber':($r['area']==='CAMPURAN'?'pill-green':'pill-blue') ?>"><?= e($r['area']==='CAMPURAN'?'Putra/Putri':ucfirst(strtolower($r['area']))) ?></span></td>
          <td class="text-end num fw-bold"><?= rupiah($r['amount']) ?></td>
          <td>
            <span class="pill <?= $r['payment_method'] === 'TUNAI' || $r['payment_method'] === 'TAGIHAN' ? 'pill-grey' : 'pill-green' ?>"><?= e($r['payment_method'] === 'TAGIHAN' ? 'TAGIHAN' : $r['payment_method']) ?></span>
            <?php if (($r['payment_method'] ?? '') === 'GABUNGAN'): ?><br><small class="text-muted" style="font-size:.65rem"><?= e(Transaction::payLabel($r)) ?></small><?php endif; ?>
          </td>
          <td class="text-end num"><?= $r['nilai_tiket'] > 0 ? rupiah($r['nilai_tiket']) : '<span class="text-muted">-</span>' ?></td>
          <td class="text-end num fw-bold" style="color:<?= $r['pay_tagihan'] > 0 ? '#dc3545' : '#6c757d' ?>"><?= rupiah($r['pay_tagihan']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$ROWS): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada transaksi tagihan pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Menampilkan transaksi yang mengandung tiket tagihan (Cabor, Eskul, Sport Day, Eskul PSAM, Santri, Al Ma'soem Swimming Day) dan transaksi dengan nominal tagihan. Halaman ini hanya untuk dilihat.</small>
</div>
