<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-clipboard-check fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">STOK OPNAME</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Verifikasi Kas & Stok Harian</h5>
      <small class="opacity-90">Internal Control &bull; Rekonsiliasi uang masuk vs real</small>
    </div>
  </div>
</div>

<div class="tt-card mb-3">
  <form method="get" action="<?= url('index.php') ?>" class="row g-2">
    <input type="hidden" name="page" value="stok-opname">
    <div class="col-md-3"><label class="form-label small fw-semibold">Tanggal</label><input type="date" class="form-control" name="tgl" value="<?= e($date) ?>"></div>
    <div class="col-md-3"><button class="btn btn-tt mt-4" type="submit"><i class="bi bi-search me-1"></i>Tampilkan</button></div>
  </form>
</div>

<div class="row g-3">
  <div class="col-xl-6">
    <div class="tt-card h-100">
      <div class="d-flex align-items-center gap-2 mb-3">
        <b><i class="bi bi-cash-coin me-1"></i>Uang Masuk Hari Ini (dari Transaksi)</b>
      </div>
      <table class="table tt-table align-middle mb-0">
        <tbody>
          <tr><td>Tunai (Cash)</td><td class="text-end num fw-bold"><?= rupiah($tunaiMasuk) ?></td></tr>
          <tr><td>QRIS</td><td class="text-end num"><?= rupiah($qrisMasuk) ?></td></tr>
          <tr><td>Transfer</td><td class="text-end num"><?= rupiah($transferMasuk) ?></td></tr>
          <tr class="fw-bold"><td>Total Penerimaan</td><td class="text-end num" style="color:var(--tt-primary)"><?= rupiah($sum['total']) ?></td></tr>
        </tbody>
      </table>
      <hr class="my-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-tools me-1"></i>Biaya Operasional Hari Ini</b>
      </div>
      <table class="table tt-table align-middle mb-0">
        <tbody>
          <tr><td>Total Biaya Operasional</td><td class="text-end num fw-bold" style="color:#dc3545"><?= rupiah($biayaHarian) ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="tt-card h-100">
      <div class="d-flex align-items-center gap-2 mb-3">
        <b><i class="bi bi-pencil-square me-1"></i>Input Real (Internal Control)</b>
      </div>
      <form method="post" action="<?= url('index.php?page=stok-opname&tgl=' . urlencode($date)) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="tunai_masuk" value="<?= $tunaiMasuk ?>">
        <div class="mb-3">
          <label class="form-label fw-semibold">Tunai Masuk (sistem)</label>
          <input class="form-control num" value="<?= number_format($tunaiMasuk, 0, ',', '.') ?>" readonly style="background:#f8f9fa">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Tunai Real (hitung fisik)</label>
          <input class="form-control num" name="tunai_real" inputmode="numeric" value="<?= $existing ? number_format($existing['tunai_real'], 0, ',', '.') : '' ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">QRIS (real)</label>
          <input class="form-control num" name="qris" inputmode="numeric" value="<?= $existing ? number_format($existing['qris'], 0, ',', '.') : number_format($qrisMasuk, 0, ',', '.') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Transfer (real)</label>
          <input class="form-control num" name="transfer" inputmode="numeric" value="<?= $existing ? number_format($existing['transfer'], 0, ',', '.') : number_format($transferMasuk, 0, ',', '.') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Tagihan (real)</label>
          <input class="form-control num" name="tagihan" inputmode="numeric" value="<?= $existing ? number_format($existing['tagihan'], 0, ',', '.') : '0' ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Biaya Operasional Keluar (real)</label>
          <input class="form-control num" name="biaya_keluar" inputmode="numeric" value="<?= $existing ? number_format($existing['biaya_keluar'], 0, ',', '.') : number_format($biayaHarian, 0, ',', '.') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Keterangan</label>
          <textarea class="form-control" name="notes" rows="2"><?= e($existing['notes'] ?? '') ?></textarea>
        </div>
        <?php if ($existing): ?>
        <div class="alert alert-info small py-2"><i class="bi bi-info-circle me-1"></i>Sudah ada data untuk tanggal ini. Simpan akan memperbarui.</div>
        <?php endif; ?>
        <button class="btn btn-tt w-100" type="submit"><i class="bi bi-save me-1"></i>Simpan Stok Opname</button>
      </form>
    </div>
  </div>
</div>

<?php if ($existing): ?>
<div class="tt-card mt-3">
  <div class="d-flex align-items-center gap-2 mb-2">
    <b><i class="bi bi-calculator me-1"></i>Hasil Stok Opname — <?= e(tanggal_indo($date)) ?></b>
  </div>
  <table class="table tt-table align-middle mb-0">
    <tbody>
      <tr><td>Tunai Masuk (sistem)</td><td class="text-end num"><?= rupiah($existing['tunai_masuk']) ?></td></tr>
      <tr><td>Tunai Real (fisik)</td><td class="text-end num"><?= rupiah($existing['tunai_real']) ?></td></tr>
      <tr><td>Selisih Tunai</td><td class="text-end num fw-bold" style="color:<?= ($existing['tunai_real'] - $existing['tunai_masuk']) >= 0 ? '#198754' : '#dc3545' ?>"><?= rupiah($existing['tunai_real'] - $existing['tunai_masuk']) ?></td></tr>
      <tr><td>QRIS</td><td class="text-end num"><?= rupiah($existing['qris']) ?></td></tr>
      <tr><td>Transfer</td><td class="text-end num"><?= rupiah($existing['transfer']) ?></td></tr>
      <tr><td>Tagihan</td><td class="text-end num"><?= rupiah($existing['tagihan']) ?></td></tr>
      <tr><td>Biaya Operasional Keluar</td><td class="text-end num" style="color:#dc3545"><?= rupiah($existing['biaya_keluar']) ?></td></tr>
      <tr class="fw-bold"><td>TOTAL (Tunai Real + QRIS + Transfer + Tagihan - Biaya)</td><td class="text-end num" style="color:var(--tt-primary)"><?= rupiah($existing['total']) ?></td></tr>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="tt-card mt-3">
  <div class="d-flex align-items-center gap-2 mb-2">
    <b><i class="bi bi-clock-history me-1"></i>Riwayat Stok Opname</b>
  </div>
  <div class="table-responsive"><table class="table tt-table align-middle mb-0">
    <thead><tr><th>Tanggal</th><th>Tunai Masuk</th><th>Tunai Real</th><th>Selisih</th><th>QRIS</th><th>Transfer</th><th>Tagihan</th><th>Biaya</th><th>Total</th><th>Oleh</th></tr></thead>
    <tbody>
      <?php foreach ($recent as $r): ?>
      <tr>
        <td class="num"><?= e(tanggal_indo($r['opname_date'])) ?></td>
        <td class="text-end num"><?= number_format($r['tunai_masuk'], 0, ',', '.') ?></td>
        <td class="text-end num"><?= number_format($r['tunai_real'], 0, ',', '.') ?></td>
        <td class="text-end num" style="color:<?= ($r['tunai_real'] - $r['tunai_masuk']) >= 0 ? '#198754' : '#dc3545' ?>"><?= number_format($r['tunai_real'] - $r['tunai_masuk'], 0, ',', '.') ?></td>
        <td class="text-end num"><?= number_format($r['qris'], 0, ',', '.') ?></td>
        <td class="text-end num"><?= number_format($r['transfer'], 0, ',', '.') ?></td>
        <td class="text-end num"><?= number_format($r['tagihan'], 0, ',', '.') ?></td>
        <td class="text-end num"><?= number_format($r['biaya_keluar'], 0, ',', '.') ?></td>
        <td class="text-end num fw-bold"><?= number_format($r['total'], 0, ',', '.') ?></td>
        <td><?= e($r['created_by_name'] ?: '-') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="10" class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>