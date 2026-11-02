<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative no-print" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-receipt-cutoff fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">TRANSAKSI & KEUANGAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Laporan Bulanan Kolam Renang Al Ma'soem</h5>
      <small class="opacity-90">Penerimaan &rarr; Biaya Operasional &rarr; Setoran ke YAB &rarr; TTD</small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2" style="position:relative;z-index:1">
    <a class="btn bg-white text-primary fw-bold btn-sm" href="<?= url('index.php?page=laporan') ?>" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-eye me-1"></i>Lihat & Cetak Laporan</a>
  </div>
</div>

<style>
/* Print-only elements: hidden on screen, shown on print */
.print-only { display: none; }
@media print {
  .no-print, .page-hero, .btn, .btn-group, .input-group, form, .modal, .sidebar, .topbar, .flash, footer, header, nav, .tt-topbar, .tt-sidebar, .tt-breadcrumb, .print-footer { display: none !important; }
  .print-only { display: block !important; }
  body { margin: 0; padding: 0; background: #fff; }
  .tt-main { padding: 0 !important; }
  .tt-card { border: none !important; box-shadow: none !important; margin: 0 !important; padding: 0 !important; }

  /* ===== Laporan harian style (mengikuti laporan.php) ===== */
  .lap-wrap { font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; color: #111; background: #fff; }
  .lap-wrap table { border-collapse: collapse; width: 100%; margin-bottom: 2px; page-break-inside: auto; }
  .lap-wrap th, .lap-wrap td { border: 1px solid #000; padding: 2px 4px; vertical-align: middle; }
  .lap-wrap thead th { background: #d7e7f7 !important; text-align: center; font-weight: 700; }
  .lap-wrap tr.sec td, .lap-wrap tr.sec th { background: #fbe3ae !important; font-weight: 700; }
  .lap-wrap tr.sec-bop td, .lap-wrap tr.sec-bop th { background: #d7e7f7 !important; font-weight: 700; }
  .lap-wrap tr.total-row td { background: #bae6fd !important; font-weight: 700; }
  .lap-wrap .c { text-align: center; }
  .lap-wrap .r { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
  .lap-wrap .b { font-weight: 700; }
  .lap-title { text-align: center; font-weight: 700; }
  .lap-wrap tr { page-break-inside: avoid; page-break-after: auto; }
  .lap-wrap img { max-height: 50px; }

  @page { margin: 10mm 8mm; size: A4; }
  * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<!-- Print-only document (mengikuti gaya laporan harian) -->
<div class="print-only lap-wrap" id="printDoc">
  <div class="text-center mb-1"><img src="<?= url(APP_LOGO) ?>" alt="Logo" style="height:56px"></div>
  <div class="lap-title" style="font-size:14px">LAPORAN TRANSAKSI HARIAN</div>
  <div class="lap-title mb-1" style="font-size:12px;font-weight:400"><?= e(defined('APP_NAME') ? APP_NAME : 'Kolam Renang Al Ma\'soem') ?></div>
  <div class="text-center fst-italic mb-2" style="font-size:11px"><?= e(hari_tanggal_indo($filter['date'] ?: date('Y-m-d'))) ?></div>

  <table style="table-layout:fixed">
    <colgroup>
      <col style="width:28px">
      <col style="width:30%">
      <col style="width:18%">
      <col style="width:30%">
      <col style="width:18%">
    </colgroup>
    <tbody>
      <tr class="sec"><td>I.</td><td colspan="4">Ringkasan Penerimaan</td></tr>
      <tr><td></td><td>Tunai (termasuk Tagihan)</td><td class="r"><?= number_format($sumHari['by']['TUNAI'], 0, ',', '.') ?></td>
        <td>QRIS</td><td class="r"><?= number_format($sumHari['by']['QRIS'], 0, ',', '.') ?></td></tr>
      <tr><td></td><td>Transfer</td><td class="r"><?= number_format($sumHari['by']['TRANSFER'], 0, ',', '.') ?></td>
        <td>Jumlah Transaksi</td><td class="r"><?= (int) $sumHari['count'] ?></td></tr>
      <tr><td></td><td>Total Penerimaan</td><td class="r b"><?= number_format($sumHari['total'], 0, ',', '.') ?></td>
        <td>Total Biaya Operasional</td><td class="r"><?= number_format($biayaHarian, 0, ',', '.') ?></td></tr>
      <tr class="total-row"><td></td><td colspan="2">Tunai Bersih (Tunai &minus; Biaya)</td><td class="r b" colspan="2"><?= number_format($tunaiBersih, 0, ',', '.') ?></td></tr>
    </tbody>
  </table>

  <table>
    <thead>
      <tr class="sec"><th colspan="9" style="text-align:left">II.&nbsp;&nbsp;Daftar Transaksi</th></tr>
      <tr>
        <th style="width:26px">No</th>
        <th style="width:120px">Nomor</th>
        <th style="width:50px">Waktu</th>
        <th>Jenis / Detail</th>
        <th style="width:60px">Kolam</th>
        <th style="width:80px">Kasir</th>
        <th style="width:90px" class="c">Total (Rp)</th>
        <th style="width:80px">Metode</th>
        <th style="width:70px">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach ($TRANSAKSI as $tr): ?>
      <tr>
        <td class="c"><?= $no++ ?></td>
        <td class="b" style="font-size:10px"><?= e($tr['number']) ?></td>
        <td class="c"><?= e((new DateTime($tr['created_at'], new DateTimeZone('Asia/Jakarta')))->format('H:i')) ?></td>
        <td style="font-size:10px"><?= e($tr['type']) ?><?php if (!empty($tr['jenis_detail'])): ?> &middot; <?= e($tr['jenis_detail']) ?><?php endif; ?></td>
        <td class="c"><?= e($tr['area']==='CAMPURAN'?'Putra/Putri':ucfirst(strtolower($tr['area']))) ?></td>
        <td style="font-size:10px"><?= e($tr['kasir'] ?: '-') ?></td>
        <td class="r b"><?= number_format($tr['amount'], 0, ',', '.') ?></td>
        <td class="c"><?= e($tr['payment_method'] === 'TAGIHAN' ? 'TAGIHAN' : $tr['payment_method']) ?><?php if (($tr['payment_method'] ?? '') === 'GABUNGAN'): ?><br><span style="font-size:8px;color:#555"><?= e(Transaction::payLabel($tr)) ?></span><?php endif; ?></td>
        <td class="c"><?= e($tr['payment_status']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$TRANSAKSI): ?>
      <tr><td colspan="9" class="c" style="padding:14px;color:#777">Tidak ada transaksi pada periode ini.</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="sec-bop"><td colspan="6" class="r">TOTAL</td><td class="r b"><?= number_format($sumHari['total'], 0, ',', '.') ?></td><td colspan="2"></td></tr>
    </tfoot>
  </table>

  <div style="margin-top:18px;font-size:9px;color:#555;border-top:1px solid #999;padding-top:6px;text-align:center">
    Dicetak pada: <?= hari_tanggal_indo() ?> <?= date('H:i') ?> WIB &bull; Sistem Kolam Renang Al Ma'soem
  </div>
</div>

<div class="row g-3 mb-3 no-print">
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">Transaksi (<?= e($filter['date'] ?: 'semua') ?>)</span>
    <div class="metric-value num"><?= $sumHari['count'] ?></div></div></div>
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">Total</span>
    <div class="metric-value num" style="color:var(--tt-primary)"><?= rupiah($sumHari['total']) ?></div></div></div>
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">Tunai</span>
    <div class="metric-value num"><?= rupiah($sumHari['by']['TUNAI']) ?></div></div></div>
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">QRIS</span>
    <div class="metric-value num"><?= rupiah($sumHari['by']['QRIS']) ?></div></div></div>
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">Transfer</span>
    <div class="metric-value num"><?= rupiah($sumHari['by']['TRANSFER']) ?></div></div></div>
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">Total Biaya (<?= e(date('d/m', strtotime($filter['date'] ?: date('Y-m-d')))) ?>)</span>
    <div class="metric-value num" style="color:#dc3545"><?= rupiah($biayaHarian) ?></div></div></div>
  <div class="col-6 col-xl"><div class="tt-card"><span class="metric-label">Tunai Bersih (<?= e(date('d/m', strtotime($filter['date'] ?: date('Y-m-d')))) ?>)</span>
    <div class="metric-value num" style="color:<?= $tunaiBersih >= 0 ? '#198754' : '#dc3545' ?>"><?= rupiah($tunaiBersih) ?></div></div></div>
</div>

<div class="tt-card mb-3 no-print">
  <form method="get" action="<?= url('index.php') ?>" class="row g-2" id="filterForm">
    <input type="hidden" name="page" value="transaksi">
    <div class="col-md-3"><label class="form-label small fw-semibold">Tanggal</label><input type="date" class="form-control" name="tgl" value="<?= e($filter['date']) ?>"></div>
    <div class="col-md-3"><label class="form-label small fw-semibold">Jenis Transaksi</label><select class="form-select" name="jenis">
      <option value="">Semua Jenis</option>
      <?php foreach (['TIKET' => 'Tiket', 'MEMBER' => 'Member', 'LES' => 'Les', 'SEWA' => 'Sewa', 'TAMBAHAN' => 'Biaya Tambahan'] as $k => $j): ?>
      <option value="<?= $k ?>" <?= $filter['type'] === $k ? 'selected' : '' ?>><?= e($j) ?></option>
      <?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small fw-semibold">Metode</label><select class="form-select" name="metode">
      <option value="">Semua</option>
      <?php foreach (['TUNAI','TAGIHAN','QRIS','TRANSFER','GABUNGAN'] as $j): ?>
      <option value="<?= $j ?>" <?= $filter['method'] === $j ? 'selected' : '' ?>><?= e(ucfirst(strtolower($j))) ?></option>
      <?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small fw-semibold">Cari</label><div class="input-group">
      <input class="form-control" name="q" value="<?= e($filter['q']) ?>" placeholder="No. transaksi...">
      <button class="btn btn-tt" type="submit"><i class="bi bi-search"></i></button></div></div>
  </form>
  <div class="d-flex gap-2 mt-2 no-print">
    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak Transaksi Harian</button>
    <a class="btn btn-light btn-sm" href="<?= url('index.php?page=laporan') ?>"><i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan Lengkap</a>
  </div>
</div>

<div class="tt-card mb-3 no-print">
  <div class="d-flex align-items-center gap-2 mb-2">
    <b><i class="bi bi-receipt-cutoff me-1"></i>Semua Transaksi</b>
    <small class="ms-auto text-muted">Nomor otomatis: TRX-YYYYMMDD-0001</small>
  </div>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead><tr><th>Nomor</th><th>Waktu</th><th>Jenis</th><th>Kolam</th><th>Kasir</th><th class="text-end">Total</th><th>Metode</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($TRANSAKSI as $tr):
          $canEdit = $tr['payment_status']==='PAID';
          $canRefund = $canEdit && $tr['type']==='TIKET' && (time()-strtotime($tr['created_at']) < 24*3600);
          $isCancelled = $tr['payment_status']==='CANCELLED';
        ?>
        <tr>
          <td class="num fw-bold" style="color:var(--tt-primary)"><?= e($tr['number']) ?></td>
          <td class="num" title="<?= e(date('d/m/Y H:i:s', strtotime($tr['created_at']))) ?> WIB"><?= e((new DateTime($tr['created_at'], new DateTimeZone('Asia/Jakarta')))->format('d/m H:i')) ?> <small class="text-muted">WIB</small></td>
          <td><?php $jd = $tr['jenis_detail'] ?? $tr['type']; $isLong = mb_strlen($jd)>40; ?><span class="pill pill-blue" style="font-size:.62rem"><?= e($tr['type']) ?></span><br><small class="<?= $isLong?'text-muted':'' ?>" title="<?= e($jd) ?>" style="font-size:.68rem;display:inline-block;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($jd) ?></small></td>
          <td><span class="pill <?= $tr['area']==='PUTRI'?'pill-amber':($tr['area']==='CAMPURAN'?'pill-green':'pill-blue') ?>"><?= e($tr['area']==='CAMPURAN'?'Putra dan Putri':ucfirst(strtolower($tr['area']))) ?></span></td>
          <td><?= e($tr['kasir'] ?: '-') ?></td>
          <td class="text-end num fw-bold"><?= rupiah($tr['amount']) ?></td>
          <td><span class="pill <?= $tr['payment_method'] === 'TUNAI' || $tr['payment_method'] === 'TAGIHAN' ? 'pill-grey' : 'pill-green' ?>" title="<?= e(Transaction::payLabel($tr)) ?>"><?= e($tr['payment_method'] === 'TAGIHAN' ? 'TAGIHAN' : $tr['payment_method']) ?></span><?php if (($tr['payment_method'] ?? '') === 'GABUNGAN'): ?><br><small class="text-muted" style="font-size:.65rem"><?= e(Transaction::payLabel($tr)) ?></small><?php endif; ?></td>
          <td><span class="pill <?= $isCancelled?'pill-red':'pill-green' ?>"><span class="dot"></span><?= e($tr['payment_status']) ?></span><?php if($isCancelled && str_contains($tr['notes']??'','REFUND')): ?><?php
          // Extract refund reason from notes
          $refundReason = '';
          if (preg_match('/\[REFUND[^:]*:\s*(.*?)\]/', $tr['notes'] ?? '', $matches)) {
            $refundReason = trim($matches[1]);
          }
          ?><br><small class="text-danger" style="font-size:.65rem"><i class="bi bi-arrow-counterclockwise me-1"></i><?= e($refundReason ?: 'Refund') ?></small><?php endif; ?></td>
          <td class="text-nowrap">
            <div class="btn-group btn-group-sm" role="group">
              <a class="btn btn-light border" href="<?= url('index.php?page=struk&id='.(int)$tr['id']) ?>" target="_blank" title="Cetak Struk"><i class="bi bi-receipt"></i></a>
              <?php if (!empty($tr['notes'])): ?>
              <button class="btn btn-light border" type="button" data-bs-toggle="modal" data-bs-target="#notesModal<?= (int)$tr['id'] ?>" title="Lihat Catatan"><i class="bi bi-file-text"></i></button>
              <?php endif; ?>
              <?php if ($isCancelled): ?>
                <span class="btn btn-light border disabled" title="Sudah di-refund"><i class="bi bi-x-circle text-danger"></i></span>
              <?php elseif ($canEdit): ?>
                <a class="btn btn-outline-primary" href="<?= url('index.php?page=kasir&edit='.(int)$tr['id']) ?>" title="Edit transaksi — kasir terisi otomatis"><i class="bi bi-pencil-square"></i></a>
                <?php if ($canRefund): ?>
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#refundModal<?= (int)$tr['id'] ?>" title="Refund — isi keterangan"><i class="bi bi-arrow-counterclockwise"></i></button>
                <?php endif; ?>
              <?php else: ?><button class="btn btn-outline-secondary" type="button" disabled title="Transaksi tidak bisa diedit"><i class="bi bi-pencil-square"></i></button><?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$TRANSAKSI): ?><tr><td colspan="9" class="text-center text-muted">Tidak ada transaksi.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($TRANSAKSI as $tr): if($tr['payment_status']!=='PAID' || $tr['type']!=='TIKET' || (time()-strtotime($tr['created_at'])>=24*3600)) continue; ?>
<div class="modal fade" id="refundModal<?= (int)$tr['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=transaksi') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="refund"><input type="hidden" name="id" value="<?= (int)$tr['id'] ?>">
      <div class="modal-header"><h5 class="modal-title fw-bold">Refund Tiket — <?= e($tr['number']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="alert alert-warning small py-2"><i class="bi bi-exclamation-triangle me-1"></i>Refund hanya dalam <b>24 jam</b>. Status akan jadi <b>CANCELLED</b> dan tercatat keterangan refund.</div>
        <div class="d-flex gap-2 small mb-2"><span class="pill pill-blue"><?= e($tr['type']) ?></span><span class="pill <?= $tr['area']==='PUTRI'?'pill-amber':($tr['area']==='CAMPURAN'?'pill-green':'pill-blue') ?>"><?= e($tr['area']==='CAMPURAN'?'Putra dan Putri':ucfirst(strtolower($tr['area']))) ?></span><span class="ms-auto fw-bold num"><?= rupiah($tr['amount']) ?></span></div>
        <label class="form-label fw-semibold">Keterangan refund *</label>
        <textarea class="form-control" name="keterangan" rows="3" required placeholder="Contoh: Salah input qty, pelanggan batal, tiket rusak..."></textarea>
        <small class="text-muted">Wajib diisi, akan tersimpan di kolom notes sebagai [REFUND ...: keterangan].</small>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" type="submit"><i class="bi bi-arrow-counterclockwise me-1"></i>Refund Sekarang</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php foreach ($TRANSAKSI as $tr): if (empty($tr['notes'])) continue; ?>
<div class="modal fade" id="notesModal<?= (int)$tr['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px">
      <div class="modal-header"><h5 class="modal-title fw-bold">Catatan — <?= e($tr['number']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="alert alert-info small py-2"><i class="bi bi-info-circle me-1"></i>Catatan lengkap transaksi</div>
        <div class="bg-light p-3 rounded-3" style="font-family:monospace;font-size:13px;white-space:pre-wrap"><?= e($tr['notes']) ?></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div class="row g-3 no-print">
  <div class="col-xl-6">
    <div class="tt-card h-100">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-tools me-1"></i>Biaya Operasional (Bulan Ini)</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#biayaModal"><i class="bi bi-plus-lg me-1"></i>Tambah Biaya</button>
      </div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Tanggal</th><th>Jenis Biaya</th><th>Keterangan</th><th class="text-end">Jumlah (Rp)</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($BIAYA as $b): ?>
          <tr><td class="num"><?= e(date('d/m', strtotime($b['cost_date']))) ?></td><td><b><?= e($b['expense_type_name'] ?: $b['name']) ?></b></td>
            <td class="small text-muted"><?= e($b['notes'] ?: '-') ?></td>
            <td class="text-end num"><?= number_format($b['amount'], 0, ',', '.') ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#biayaModal"
                data-row='<?= e(json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' title="Edit"><i class="bi bi-pencil-square"></i></button>
              <?php if (is_admin()): ?>
              <form method="post" action="<?= url('index.php?page=transaksi') ?>" class="d-inline" onsubmit="return confirm('Hapus biaya ini?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="hapus-biaya"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
              </form><?php endif; ?>
            </td></tr>
          <?php endforeach; ?>
          <tr class="fw-bold"><td colspan="3">Total Biaya Operasional</td><td class="text-end num"><?= number_format($totBiaya, 0, ',', '.') ?></td><td></td></tr>
        </tbody></table></div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="tt-card h-100">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-bank me-1"></i>Setoran Otomatis ke YAB (<?= e($filter['date'] ?: date('Y-m-d')) ?>)</b>
      </div>
      <div><small class="text-muted d-block mb-2">Otomatis dari transaksi kasir per metode bayar. Input manual dinonaktifkan.</small></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <tbody>
          <tr><td>Cashbon — QRIS</td><td class="text-end num"><?= number_format($SETORAN['QRIS'], 0, ',', '.') ?></td></tr>
          <tr><td>Cashbon — Transfer Rek. Mandiri Yayasan</td><td class="text-end num"><?= number_format($SETORAN['TRANSFER'], 0, ',', '.') ?></td></tr>
          <tr><td>Setoran Uang</td><td class="text-end num"><?= number_format($SETORAN['UANG'], 0, ',', '.') ?></td></tr>
          <tr class="fw-bold"><td>Jumlah Setoran ke YAB</td><td class="text-end num" style="color:var(--tt-primary)"><?= number_format($SETORAN['QRIS'] + $SETORAN['TRANSFER'] + $SETORAN['UANG'], 0, ',', '.') ?></td></tr>
        </tbody></table></div>
    </div>
  </div>
</div>

<div class="modal fade" id="biayaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=transaksi') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="biaya"><input type="hidden" name="id" class="f-id" value="">
      <div class="modal-header"><h5 class="modal-title fw-bold">Biaya Operasional</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-6"><label class="form-label fw-semibold">Tanggal</label><input type="date" class="form-control f-tanggal" name="tanggal" value="<?= date('Y-m-d') ?>"></div>
        <div class="col-6"><label class="form-label fw-semibold">Jumlah (Rp)</label><input class="form-control num f-jumlah" name="jumlah" inputmode="numeric" required></div>
        <div class="col-12"><label class="form-label fw-semibold">Jenis Biaya</label>
          <select class="form-select f-type" name="expense_type_id" required>
            <option value="">-- Pilih Jenis Biaya --</option>
            <?php foreach ($EXPENSE_TYPES as $et): ?>
            <option value="<?= $et['id'] ?>"><?= e($et['name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="col-12"><label class="form-label fw-semibold">Keterangan</label><textarea class="form-control f-keterangan" name="keterangan" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<?php ob_start(); ?>
<script>
document.getElementById('biayaModal')?.addEventListener('show.bs.modal', function (ev) {
  const b = ev.relatedTarget;
  const row = b && b.dataset.row ? JSON.parse(b.dataset.row) : null;
  const modal = this;
  const idField = modal.querySelector('.f-id');
  const isEdit = row && row.id;
  idField.value = isEdit ? row.id : '';
  modal.querySelector('input[name="action"]').value = isEdit ? 'edit-biaya' : 'biaya';
  modal.querySelector('.f-tanggal').value = row && row.cost_date ? row.cost_date : '<?= date('Y-m-d') ?>';
  modal.querySelector('.f-jumlah').value = row && row.amount ? row.amount : '';
  modal.querySelector('.f-type').value = row && row.expense_type_id ? row.expense_type_id : '';
  modal.querySelector('.f-keterangan').value = row && row.notes ? row.notes : '';
  modal.querySelector('.modal-title').textContent = isEdit ? 'Edit Biaya Operasional' : 'Tambah Biaya Operasional';
});
</script>
<?php $pageScripts = ob_get_clean(); ?>


