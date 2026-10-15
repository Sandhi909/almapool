<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-capsule fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">MANAJEMEN OBAT</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Stok Obat Kolam Renang</h5>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2" style="position:relative;z-index:1">
    <button class="btn bg-white text-primary fw-bold btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#tambahModal" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-plus-lg me-1"></i>Tambah Obat</button>
  </div>
</div>

<?php if (!empty($LOW_STOCK)): ?>
<div class="alert alert-warning d-flex align-items-center gap-3 mb-3" style="border-radius:14px">
  <i class="bi bi-exclamation-triangle fs-4"></i>
  <div>
    <b>Stok Menipis!</b><br>
    <small><?= count($LOW_STOCK) ?> obat di bawah batas minimum. Silakan restock.</small>
  </div>
</div>
<?php endif; ?>

<div class="tt-card mb-3">
  <div class="d-flex align-items-center gap-2 mb-2">
    <b><i class="bi bi-capsule me-1"></i>Daftar Obat</b>
    <small class="ms-auto text-muted"><?= count($MEDICINES) ?> item</small>
  </div>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead><tr><th>Nama</th><th>Kategori</th><th>Stok</th><th>Min. Stok</th><th>Satuan</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($MEDICINES as $m): ?>
        <tr>
          <td><b><?= e($m['name']) ?></b></td>
          <td><span class="pill pill-blue"><?= e($m['category_name'] ?: '-') ?></span></td>
          <td class="num fw-bold <?= $m['current_stock'] <= $m['min_stock'] ? 'text-danger' : '' ?>"><?= number_format($m['current_stock'], 2, ',', '.') ?></td>
          <td class="num"><?= number_format($m['min_stock'], 2, ',', '.') ?></td>
          <td><?= e($m['unit_name'] ?: '-') ?></td>
          <td class="text-nowrap">
            <div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-light border" type="button" data-bs-toggle="modal" data-bs-target="#restockModal<?= (int)$m['id'] ?>" title="Tambah Stok"><i class="bi bi-plus-circle"></i></button>
              <button class="btn btn-light border" type="button" data-bs-toggle="modal" data-bs-target="#pakaiModal<?= (int)$m['id'] ?>" title="Pakai Obat"><i class="bi bi-dash-circle"></i></button>
              <button class="btn btn-light border" type="button" data-bs-toggle="modal" data-bs-target="#editModal<?= (int)$m['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-light border" type="button" data-bs-toggle="modal" data-bs-target="#historyModal<?= (int)$m['id'] ?>" title="Riwayat"><i class="bi bi-clock-history"></i></button>
              <form method="post" action="<?= url('index.php?page=obat') ?>" class="d-inline" onsubmit="return confirm('Hapus obat ini?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= $m['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$MEDICINES): ?><tr><td colspan="6" class="text-center text-muted">Belum ada obat.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Obat -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=obat') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="tambah">
      <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Obat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-12"><label class="form-label fw-semibold">Nama Obat *</label><input class="form-control" name="nama" required placeholder="Nama obat..."></div>
        <div class="col-6"><label class="form-label fw-semibold">Kategori</label><select class="form-select" name="kategori_id"><option value="">-- Pilih --</option><?php foreach ($CATEGORIES as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Satuan</label><select class="form-select" name="satuan_id"><option value="">-- Pilih --</option><?php foreach ($UNITS as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Stok Awal</label><input class="form-control num" name="stok_awal" inputmode="numeric" value="0"></div>
        <div class="col-6"><label class="form-label fw-semibold">Stok Minimum</label><input class="form-control num" name="stok_minimum" inputmode="numeric" value="0"></div>
        <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($MEDICINES as $m): ?>
<!-- Modal Restock -->
<div class="modal fade" id="restockModal<?= (int)$m['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=obat') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="restock"><input type="hidden" name="id" value="<?= $m['id'] ?>">
      <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Stok — <?= e($m['name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-6"><label class="form-label fw-semibold">Jumlah</label><input class="form-control num" name="jumlah" inputmode="numeric" required placeholder="0"></div>
        <div class="col-6"><label class="form-label fw-semibold">Satuan</label><input class="form-control" value="<?= e($m['unit_name']) ?>" readonly></div>
        <div class="col-12"><label class="form-label fw-semibold">Supplier</label><input class="form-control" name="supplier" placeholder="Nama supplier..."></div>
        <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Tambah Stok</button></div>
    </form>
  </div>
</div>

<!-- Modal Pakai Obat -->
<div class="modal fade" id="pakaiModal<?= (int)$m['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=obat') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="pakai"><input type="hidden" name="id" value="<?= $m['id'] ?>">
      <div class="modal-header"><h5 class="modal-title fw-bold">Pakai Obat — <?= e($m['name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-12"><div class="alert alert-info small py-2">Stok saat ini: <b><?= number_format($m['current_stock'], 2, ',', '.') ?> <?= e($m['unit_name']) ?></b></div></div>
        <div class="col-6"><label class="form-label fw-semibold">Jumlah</label><input class="form-control num" name="jumlah" inputmode="numeric" required placeholder="0"></div>
        <div class="col-6"><label class="form-label fw-semibold">Satuan</label><input class="form-control" value="<?= e($m['unit_name']) ?>" readonly></div>
        <div class="col-12"><label class="form-label fw-semibold">Tujuan *</label><input class="form-control" name="tujuan" required placeholder="Contoh: Backwash, Treatment kolam putra..."></div>
        <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Catat Pemakaian</button></div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal<?= (int)$m['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=obat') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= $m['id'] ?>">
      <div class="modal-header"><h5 class="modal-title fw-bold">Edit Obat — <?= e($m['name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-12"><label class="form-label fw-semibold">Nama Obat</label><input class="form-control" name="nama" value="<?= e($m['name']) ?>" required></div>
        <div class="col-6"><label class="form-label fw-semibold">Kategori</label><select class="form-select" name="kategori_id"><option value="">-- Pilih --</option><?php foreach ($CATEGORIES as $c): ?><option value="<?= $c['id'] ?>" <?= $m['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Satuan</label><select class="form-select" name="satuan_id"><option value="">-- Pilih --</option><?php foreach ($UNITS as $u): ?><option value="<?= $u['id'] ?>" <?= $m['unit_id'] == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Stok Minimum</label><input class="form-control num" name="stok_minimum" inputmode="numeric" value="<?= $m['min_stock'] ?>"></div>
        <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan" rows="2"><?= e($m['notes'] ?: '') ?></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<!-- Modal Riwayat -->
<div class="modal fade" id="historyModal<?= (int)$m['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:20px">
      <div class="modal-header"><h5 class="modal-title fw-bold">Riwayat — <?= e($m['name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <ul class="nav nav-pills mb-3">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#hist-stock<?= (int)$m['id'] ?>">Stok Masuk</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#hist-usage<?= (int)$m['id'] ?>">Pemakaian</button></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="hist-stock<?= (int)$m['id'] ?>">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Supplier</th><th>Oleh</th></tr></thead>
                <tbody>
                  <?php
                  $stockHist = (new Medicine())->getStockHistory($m['id']);
                  foreach ($stockHist as $sh):
                  ?>
                  <tr>
                    <td><?= e(date('d/m/Y', strtotime($sh['stock_date']))) ?></td>
                    <td class="num text-success fw-bold">+<?= number_format($sh['quantity'], 2, ',', '.') ?> <?= e($m['unit_name']) ?></td>
                    <td><?= e($sh['supplier'] ?: '-') ?></td>
                    <td><?= e($sh['created_by_name'] ?: '-') ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (!$stockHist): ?><tr><td colspan="4" class="text-center text-muted">Belum ada riwayat stok.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="hist-usage<?= (int)$m['id'] ?>">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Tujuan</th><th>Oleh</th></tr></thead>
                <tbody>
                  <?php
                  $usageHist = (new Medicine())->getUsageHistory($m['id']);
                  foreach ($usageHist as $uh):
                  ?>
                  <tr>
                    <td><?= e(date('d/m/Y', strtotime($uh['usage_date']))) ?></td>
                    <td class="num text-danger fw-bold">-<?= number_format($uh['quantity'], 2, ',', '.') ?> <?= e($m['unit_name']) ?></td>
                    <td><?= e($uh['purpose']) ?></td>
                    <td><?= e($uh['used_by_name'] ?: '-') ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (!$usageHist): ?><tr><td colspan="4" class="text-center text-muted">Belum ada riwayat pemakaian.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
    </div>
  </div>
</div>
<?php endforeach; ?>
