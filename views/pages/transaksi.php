<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
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

<div class="row g-3 mb-3">
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

<div class="tt-card mb-3">
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
      <?php foreach (['TUNAI','QRIS','TRANSFER','GABUNGAN'] as $j): ?>
      <option value="<?= $j ?>" <?= $filter['method'] === $j ? 'selected' : '' ?>><?= e(ucfirst(strtolower($j))) ?></option>
      <?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small fw-semibold">Cari</label><div class="input-group">
      <input class="form-control" name="q" value="<?= e($filter['q']) ?>" placeholder="No. transaksi...">
      <button class="btn btn-tt" type="submit"><i class="bi bi-search"></i></button></div></div>
  </form>
  <div class="d-flex gap-2 mt-2 no-print">
    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="printTransaksi()"><i class="bi bi-printer me-1"></i>Cetak Transaksi Harian</button>
    <a class="btn btn-light btn-sm" href="<?= url('index.php?page=laporan') ?>"><i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan Lengkap</a>
  </div>
</div>

<div class="tt-card mb-3">
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
          <td><span class="pill <?= $tr['payment_method'] === 'TUNAI' ? 'pill-grey' : 'pill-green' ?>" title="<?= e(Transaction::payLabel($tr)) ?>"><?= e($tr['payment_method']) ?></span><?php if (($tr['payment_method'] ?? '') === 'GABUNGAN'): ?><br><small class="text-muted" style="font-size:.65rem"><?= e(Transaction::payLabel($tr)) ?></small><?php endif; ?></td>
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

<div class="row g-3">
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

function printTransaksi() {
  const form = document.getElementById('filterForm');
  const params = new URLSearchParams(new FormData(form));
  params.set('page', 'transaksi-print');
  window.open('<?= url('index.php') ?>?' + params.toString(), '_blank', 'width=900,height=700');
}
</script>
<?php $pageScripts = ob_get_clean(); ?>


