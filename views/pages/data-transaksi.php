<?php
$qs = array_filter([
    'page' => 'data-transaksi',
    'dari' => $FILTER['from'],
    'sampai' => $FILTER['to'],
    'jenis' => $FILTER['type'],
    'metode' => $FILTER['method'],
    'status' => $FILTER['status'],
    'q' => $FILTER['q'],
], fn ($v) => $v !== '' && $v !== null);
$pageUrl = function (int $p) use ($qs) {
    return url('index.php?' . http_build_query(array_merge($qs, ['hal' => $p])));
};
$periodeLabel = ($FILTER['from'] || $FILTER['to'])
    ? (($FILTER['from'] ? tanggal_indo($FILTER['from']) : 'Awal') . ' &ndash; ' . ($FILTER['to'] ? tanggal_indo($FILTER['to']) : 'Sekarang'))
    : 'Semua Periode';
?>
<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-journal-text fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">KEUANGAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Data Transaksi</h5>
      <small class="opacity-90">Seluruh transaksi kasir &bull; hanya lihat</small>
    </div>
  </div>
  <div class="ms-auto" style="position:relative;z-index:1">
    <a class="btn bg-white text-primary fw-bold btn-sm" href="<?= url('index.php?page=tagihan') ?>" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-receipt me-1"></i>Transaksi Tagihan</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-2"><div class="tt-card h-100"><span class="metric-label">Jumlah Transaksi</span>
    <div class="metric-value num"><?= (int) $SUM['count'] ?></div>
    <small class="text-muted"><?= (int) $SUM['paid'] ?> lunas &bull; <?= (int) $SUM['cancelled'] ?> batal</small></div></div>
  <div class="col-6 col-xl-2"><div class="tt-card h-100"><span class="metric-label">Total Transaksi</span>
    <div class="metric-value num" style="color:var(--tt-primary)"><?= rupiah($SUM['total']) ?></div></div></div>
  <div class="col-6 col-xl-2"><div class="tt-card h-100"><span class="metric-label">Tunai</span>
    <div class="metric-value num"><?= rupiah($SUM['tunai']) ?></div></div></div>
  <div class="col-6 col-xl-2"><div class="tt-card h-100"><span class="metric-label">QRIS</span>
    <div class="metric-value num"><?= rupiah($SUM['qris']) ?></div></div></div>
  <div class="col-6 col-xl-2"><div class="tt-card h-100"><span class="metric-label">Transfer</span>
    <div class="metric-value num"><?= rupiah($SUM['transfer']) ?></div></div></div>
  <div class="col-6 col-xl-2"><div class="tt-card h-100"><span class="metric-label">Ditampilkan</span>
    <div class="metric-value num"><?= count($ROWS) ?></div>
    <small class="text-muted">dari <?= (int) $SUM['count'] ?> transaksi</small></div></div>
</div>

<div class="tt-card mb-3">
  <form method="get" action="<?= url('index.php') ?>" class="row g-2 align-items-end">
    <input type="hidden" name="page" value="data-transaksi">
    <div class="col-6 col-md-2">
      <label class="form-label small fw-semibold">Dari Tanggal</label>
      <input type="date" class="form-control" name="dari" value="<?= e($FILTER['from']) ?>">
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-semibold">Sampai Tanggal</label>
      <input type="date" class="form-control" name="sampai" value="<?= e($FILTER['to']) ?>">
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-semibold">Jenis</label>
      <select class="form-select" name="jenis">
        <option value="">Semua Jenis</option>
        <?php foreach (['TIKET' => 'Tiket', 'MEMBER' => 'Member', 'LES' => 'Les', 'SEWA' => 'Sewa', 'TAMBAHAN' => 'Biaya Tambahan'] as $k => $j): ?>
        <option value="<?= $k ?>" <?= $FILTER['type'] === $k ? 'selected' : '' ?>><?= e($j) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-semibold">Metode</label>
      <select class="form-select" name="metode">
        <option value="">Semua Metode</option>
        <?php foreach (['TUNAI' => 'Tunai', 'TAGIHAN' => 'Tagihan', 'QRIS' => 'QRIS', 'TRANSFER' => 'Transfer', 'GABUNGAN' => 'Gabungan'] as $k => $j): ?>
        <option value="<?= $k ?>" <?= $FILTER['method'] === $k ? 'selected' : '' ?>><?= e($j) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-semibold">Status</label>
      <select class="form-select" name="status">
        <option value="">Semua Status</option>
        <option value="PAID" <?= $FILTER['status'] === 'PAID' ? 'selected' : '' ?>>Lunas</option>
        <option value="CANCELLED" <?= $FILTER['status'] === 'CANCELLED' ? 'selected' : '' ?>>Batal</option>
      </select>
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label small fw-semibold">Cari</label>
      <input class="form-control" name="q" value="<?= e($FILTER['q']) ?>" placeholder="No. / kasir / catatan">
    </div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-tt" type="submit"><i class="bi bi-search me-1"></i>Tampilkan</button>
      <a class="btn btn-light" href="<?= url('index.php?page=data-transaksi') ?>"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    </div>
  </form>
</div>

<div class="tt-card">
  <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
    <b><i class="bi bi-journal-text me-1"></i>Daftar Seluruh Transaksi</b>
    <small class="ms-auto text-muted"><?= $periodeLabel ?></small>
  </div>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead>
        <tr>
          <th style="width:42px">No</th>
          <th>Waktu</th>
          <th>Nomor</th>
          <th>Kasir</th>
          <th>Jenis / Detail</th>
          <th>Kolam</th>
          <th class="text-end">Total</th>
          <th>Metode</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = (($PAGE - 1) * $PER_PAGE) + 1; foreach ($ROWS as $r):
          $jd = $r['jenis_detail'] ?? $r['type'];
          $isCancelled = ($r['payment_status'] ?? '') !== 'PAID';
        ?>
        <tr>
          <td class="num text-muted"><?= $no++ ?></td>
          <td class="num text-nowrap" title="<?= e(date('d/m/Y H:i:s', strtotime($r['created_at']))) ?> WIB"><?= e(date('d/m H:i', strtotime($r['created_at']))) ?> <small class="text-muted">WIB</small></td>
          <td class="num fw-bold" style="color:var(--tt-primary)"><?= e($r['number']) ?></td>
          <td><?= e($r['kasir'] ?: '-') ?></td>
          <td style="max-width:300px">
            <span class="pill pill-blue" style="font-size:.62rem"><?= e($r['type']) ?></span>
            <br><small class="text-muted" title="<?= e($jd) ?>" style="font-size:.68rem;display:inline-block;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($jd ?: '-') ?></small>
          </td>
          <td><span class="pill <?= $r['area'] === 'PUTRI' ? 'pill-amber' : ($r['area'] === 'CAMPURAN' ? 'pill-green' : 'pill-blue') ?>"><?= e($r['area'] === 'CAMPURAN' ? 'Putra/Putri' : ucfirst(strtolower($r['area']))) ?></span></td>
          <td class="text-end num fw-bold"><?= rupiah($r['amount']) ?></td>
          <td>
            <span class="pill <?= in_array($r['payment_method'], ['TUNAI', 'TAGIHAN'], true) ? 'pill-grey' : 'pill-green' ?>" title="<?= e(Transaction::payLabel($r)) ?>"><?= e($r['payment_method']) ?></span>
            <?php if (($r['payment_method'] ?? '') === 'GABUNGAN'): ?><br><small class="text-muted" style="font-size:.65rem"><?= e(Transaction::payLabel($r)) ?></small><?php endif; ?>
          </td>
          <td><span class="pill <?= $isCancelled ? 'pill-red' : 'pill-green' ?>"><span class="dot"></span><?= $isCancelled ? 'BATAL' : 'LUNAS' ?></span></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="<?= url('index.php?page=struk&id=' . (int) $r['id'] . '&from=data-transaksi') ?>" target="_blank" rel="noopener" title="Lihat struk"><i class="bi bi-receipt"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$ROWS): ?>
        <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada transaksi pada filter ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
    <small class="text-muted">Halaman <?= (int) $PAGE ?> dari <?= (int) $PAGES ?> &bull; <?= (int) $SUM['count'] ?> transaksi</small>
    <?php if ($PAGES > 1): ?>
    <nav class="ms-auto">
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $PAGE <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $PAGE <= 1 ? '#' : $pageUrl($PAGE - 1) ?>">&laquo;</a>
        </li>
        <?php
        $start = max(1, $PAGE - 2);
        $end = min($PAGES, $PAGE + 2);
        if ($start > 1): ?>
          <li class="page-item"><a class="page-link" href="<?= $pageUrl(1) ?>">1</a></li>
          <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
        <?php endif; ?>
        <?php for ($p = $start; $p <= $end; $p++): ?>
        <li class="page-item <?= $p === $PAGE ? 'active' : '' ?>"><a class="page-link" href="<?= $pageUrl($p) ?>"><?= $p ?></a></li>
        <?php endfor; ?>
        <?php if ($end < $PAGES): ?>
          <?php if ($end < $PAGES - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
          <li class="page-item"><a class="page-link" href="<?= $pageUrl($PAGES) ?>"><?= $PAGES ?></a></li>
        <?php endif; ?>
        <li class="page-item <?= $PAGE >= $PAGES ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $PAGE >= $PAGES ? '#' : $pageUrl($PAGE + 1) ?>">&raquo;</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
  <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Halaman ini hanya untuk dilihat. Perubahan atau pembatalan transaksi dilakukan oleh admin/kasir.</small>
</div>
