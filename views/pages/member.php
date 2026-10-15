<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-person-badge fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">DIREKTORI KEANGGOTAAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Data Member Kolam Renang</h5>
      <small class="opacity-90">Gold bebas datang &bull; Silver hari libur +Rp15.000 &bull; <?= number_format($STAT['total_member']??0,0,',','.') ?> member</small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 flex-wrap align-items-center" style="position:relative;z-index:1">
    <button class="btn bg-white text-primary fw-bold btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#memberModal" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-person-plus me-1"></i>Tambah Member Baru</button>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Total Member Terdaftar</span><div class="metric-value num"><?= number_format($STAT['total_member'], 0, ',', '.') ?></div></div><span class="metric-icon mi-blue"><i class="bi bi-people"></i></span></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Member Aktif</span><div class="metric-value num" style="color:var(--tt-green)"><?= number_format($STAT['member_aktif'], 0, ',', '.') ?></div></div><span class="metric-icon mi-green"><i class="bi bi-patch-check"></i></span></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Akan Expired (&le; 7 Hari)</span><div class="metric-value num"><?= $STAT['akan_expired'] ?></div></div><span class="metric-icon mi-amber"><i class="bi bi-hourglass-split"></i></span></div></div>
  <div class="col-6 col-xl-3"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Expired / Tidak Aktif</span><div class="metric-value num" style="color:var(--tt-red)"><?= $STAT['expired'] ?></div></div><span class="metric-icon mi-red"><i class="bi bi-person-x"></i></span></div></div>
</div>

<div class="tt-card mb-3">
  <form method="get" action="<?= url('index.php') ?>" class="row g-2">
    <input type="hidden" name="page" value="member">
    <div class="col-md-6"><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input class="form-control" id="cariMember" placeholder="Cari Nama, Nomor Member (MEM-xxx), HP..."></div></div>
    <div class="col-6 col-md-3"><select class="form-select" id="filterStatus"><option value="">Status: Semua</option>
      <option>Aktif</option><option>Akan Expired</option><option>Expired</option></select></div>
  </form>
</div>

<div class="tt-card">
  <?php if (!$MEMBERS): ?>
    <div class="text-center text-muted py-4">Belum ada member. Tambahkan member baru.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0" id="tabelMember">
      <thead><tr><th>Profil &amp; Nama</th><th>ID Member</th><th>Kontak</th><th>Paket</th><th>Masa Berlaku</th><th>Status</th><th>Kunjungan</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($MEMBERS as $m): ?>
        <tr data-cari="<?= e(strtolower($m['member_number'] . ' ' . $m['name'] . ' ' . ($m['phone'] ?? ''))) ?>" data-status="<?= e($m['status']) ?>">
          <td><div class="d-flex gap-2 align-items-center"><span class="tt-avatar"><?= e(initials($m['name'])) ?></span>
            <div><b><?= e($m['name']) ?></b><br><small class="text-muted"><?= $m['gender'] === 'P' ? 'Putri' : ($m['gender'] === 'L' ? 'Putra' : '-') ?></small></div></div></td>
          <td><span class="pill pill-blue num"><?= e($m['member_number']) ?></span></td>
          <td><span class="num"><?= e($m['phone'] ?: '-') ?></span></td>
          <td><?= e(trim(($m['paket'] ?? '-') . ' ' . ucfirst(strtolower($m['tier'] ?? '')))) ?></td>
          <td><span class="num"><?= e($m['start_date'] ? tanggal_pendek($m['start_date']) . ' → ' . tanggal_pendek($m['end_date']) : '-') ?></span><br><small class="text-muted"><?= e($m['sisa']) ?></small></td>
          <td><?= pill_status($m['status']) ?></td>
          <td>
            <b class="num"><?= (int)$m['total_kunjungan'] ?>x</b>
            <?php if ($m['kunjungan_today']): ?><span class="pill pill-green ms-1" style="font-size:.62rem">Hari ini</span><?php endif; ?>
            <br><small class="text-muted num" style="font-size:.72rem"><?= $m['kunjungan_terakhir'] ? date('d/m/Y H:i', strtotime($m['kunjungan_terakhir'])) : 'Belum pernah' ?></small>
          </td>
          <td class="text-nowrap">
            <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
              <button class="btn btn-outline-info" type="button" data-bs-toggle="modal" data-bs-target="#visitModal<?= (int)$m['id'] ?>" title="Riwayat Kunjungan"><i class="bi bi-clock-history"></i></button>
              <a class="btn btn-outline-secondary" href="<?= url('index.php?page=kartu&id=' . (int) $m['id']) ?>" title="Lihat Kartu" data-bs-toggle="tooltip" data-bs-title="Kartu"><i class="bi bi-person-vcard"></i></a>
              <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#editMemberModal" title="Edit" data-bs-title="Edit" data-id="<?= (int) $m['id'] ?>" data-nama="<?= e($m['name']) ?>" data-hp="<?= e($m['phone'] ?? '') ?>" data-nik="<?= e($m['identity_number'] ?? '') ?>" data-gender="<?= e($m['gender']) ?>" data-tgl_lahir="<?= e($m['birth_date'] ?? '') ?>" data-alamat="<?= e($m['address'] ?? '') ?>" data-package_id="<?= (int) ($m['package_id'] ?? 0) ?>" data-catatan="<?= e($m['notes'] ?? '') ?>"><i class="bi bi-pencil-square"></i></button>
              <?php if ($m['status'] === 'Expired'): ?>
                <a class="btn btn-outline-danger" href="<?= url('index.php?page=kasir') ?>" title="Aktivasi Ulang" data-bs-toggle="tooltip" data-bs-title="Aktivasi Ulang"><i class="bi bi-arrow-clockwise"></i></a>
              <?php else: ?>
                <a class="btn btn-outline-success" href="<?= url('index.php?page=kasir') ?>" title="Perpanjang" data-bs-toggle="tooltip" data-bs-title="Perpanjang"><i class="bi bi-calendar-plus"></i></a>
              <?php endif; ?>
              <button class="btn btn-outline-danger" type="button" title="Hapus" data-bs-toggle="tooltip" data-bs-title="Hapus" onclick="if(confirm('Hapus member <?= e(addslashes($m['name'])) ?> (<?= e($m['member_number']) ?>)? Data kunjungan & membership ikut terhapus.')) location.href='<?= url('index.php?page=member&action=delete&id=' . (int) $m['id']) ?>'"><i class="bi bi-trash3"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if (!empty($RECENT_VISITS)): ?>
<div class="tt-card mt-3">
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#fff"><i class="bi bi-qr-code-scan"></i></span>
    <b>Riwayat Kunjungan Terbaru (Scan QR)</b>
    <span class="pill pill-blue ms-auto"><?= count($RECENT_VISITS) ?> kunjungan</span>
  </div>
  <small class="text-muted d-block mb-2">Data otomatis masuk ke <b>member_visits</b> saat scan QR member aktif — tampil di sini.</small>
  <div class="table-responsive">
    <table class="table tt-table align-middle mb-0">
      <thead><tr><th>Waktu</th><th>Member</th><th>ID</th><th>Kolam</th><th>Petugas</th></tr></thead>
      <tbody>
        <?php foreach ($RECENT_VISITS as $v): ?>
        <tr>
          <td class="num"><?= date('d/m/Y H:i', strtotime($v['check_in_at'])) ?><br><small class="text-muted"><?= e($v['visit_date']) ?></small></td>
          <td><b><?= e($v['name']) ?></b></td>
          <td><span class="pill pill-blue num"><?= e($v['member_number']) ?></span></td>
          <td><span class="pill <?= $v['area']==='PUTRI'?'pill-amber':'pill-blue' ?>"><?= e($v['area']) ?></span></td>
          <td class="num"><?= e($v['officer_id'] ?: '-') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php foreach ($MEMBERS as $m): 
  $visits = db()->query('SELECT * FROM member_visits WHERE member_id='.(int)$m['id'].' ORDER BY check_in_at DESC LIMIT 15')->fetchAll();
?>
<div class="modal fade" id="visitModal<?= (int)$m['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px">
      <div class="modal-header"><h5 class="modal-title fw-bold">Kunjungan — <?= e($m['name']) ?> <small class="text-muted">(<?= e($m['member_number']) ?>)</small></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?php if (!$visits): ?>
          <div class="text-center text-muted py-3">Belum ada kunjungan. Scan QR saat member aktif untuk mencatat otomatis.</div>
        <?php else: ?>
          <div class="table-responsive"><table class="table tt-table align-middle mb-0">
            <thead><tr><th>Tanggal</th><th>Jam</th><th>Kolam</th></tr></thead>
            <tbody>
              <?php foreach ($visits as $vv): ?>
              <tr><td class="num"><?= tanggal_indo($vv['visit_date']) ?></td><td class="num"><?= date('H:i:s', strtotime($vv['check_in_at'])) ?></td><td><span class="pill <?= $vv['area']==='PUTRI'?'pill-amber':'pill-blue' ?>"><?= e($vv['area']) ?></span></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
          <small class="text-muted d-block mt-2">Total: <b><?= count($visits) ?><?= count($visits)>=15?' (maks 15 terbaru)':'' ?></b> kunjungan tercatat di <b>member_visits</b>.</small>
        <?php endif; ?>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=member') ?>">
      <?= csrf_field() ?>
      <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Member Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="alert alert-info small py-2">Nomor member (MEM-xxxxxx) &amp; QR dibuat otomatis. Membership aktif setelah pembayaran di kasir.</div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold">Nama Lengkap *</label><input class="form-control" name="nama" required maxlength="100"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Nomor HP</label><input class="form-control" name="hp" maxlength="20"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">NIK / Identitas</label><input class="form-control" name="nik" maxlength="30"></div>
          <div class="col-md-3"><label class="form-label fw-semibold">Putra/Putri</label><select class="form-select" name="gender"><option value="L">Putra</option><option value="P">Putri</option></select></div>
          <div class="col-md-3"><label class="form-label fw-semibold">Tgl Lahir</label><input type="date" class="form-control" name="tgl_lahir"></div>
          <div class="col-12"><label class="form-label fw-semibold">Alamat</label><input class="form-control" name="alamat" maxlength="255"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Paket Membership *</label><select class="form-select" name="package_id" required>
            <?php foreach ($PAKET_MEMBER as $jenis => $tiers): foreach (['GOLD', 'SILVER'] as $tr): ?>
            <option value="<?= $tiers[$tr]['id'] ?>"><?= e($jenis . ' ' . ucfirst(strtolower($tr))) ?> — <?= rupiah($tiers[$tr]['price']) ?></option>
            <?php endforeach; endforeach; ?></select></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Mulai</label><input type="date" class="form-control" name="tgl_mulai" value="<?= date('Y-m-d') ?>"></div>
          <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-tt">Simpan Member</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=member') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="member_id">
      <div class="modal-header"><h5 class="modal-title fw-bold">Edit Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold">Nama Lengkap *</label><input class="form-control" name="nama" required maxlength="100"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Nomor HP</label><input class="form-control" name="hp" maxlength="20"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">NIK / Identitas</label><input class="form-control" name="nik" maxlength="30"></div>
          <div class="col-md-3"><label class="form-label fw-semibold">Putra/Putri</label><select class="form-select" name="gender"><option value="L">Putra</option><option value="P">Putri</option></select></div>
          <div class="col-md-3"><label class="form-label fw-semibold">Tgl Lahir</label><input type="date" class="form-control" name="tgl_lahir"></div>
          <div class="col-12"><label class="form-label fw-semibold">Alamat</label><input class="form-control" name="alamat" maxlength="255"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Paket Membership *</label><select class="form-select" name="package_id" required>
            <?php foreach ($PAKET_MEMBER as $jenis => $tiers): foreach (['GOLD', 'SILVER'] as $tr): ?>
            <option value="<?= $tiers[$tr]['id'] ?>"><?= e($jenis . ' ' . ucfirst(strtolower($tr))) ?> — <?= rupiah($tiers[$tr]['price']) ?></option>
            <?php endforeach; endforeach; ?></select></div>
          <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-tt">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // tooltip rapi untuk aksi
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

  const q = document.getElementById('cariMember');
  const fs = document.getElementById('filterStatus');
  function apply() {
    const keyword = q.value.toLowerCase();
    document.querySelectorAll('#tabelMember tbody tr').forEach(tr => {
      const okQ = tr.dataset.cari.includes(keyword);
      const okS = !fs.value || tr.dataset.status === fs.value;
      tr.style.display = (okQ && okS) ? '' : 'none';
    });
  }
  [q, fs].forEach(el => el.addEventListener('input', apply));
  
  const editModal = document.getElementById('editMemberModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget;
      editModal.querySelector('[name="member_id"]').value = btn.dataset.id;
      editModal.querySelector('[name="nama"]').value = btn.dataset.nama;
      editModal.querySelector('[name="hp"]').value = btn.dataset.hp;
      editModal.querySelector('[name="nik"]').value = btn.dataset.nik;
      editModal.querySelector('[name="gender"]').value = btn.dataset.gender;
      editModal.querySelector('[name="tgl_lahir"]').value = btn.dataset.tgl_lahir;
      editModal.querySelector('[name="alamat"]').value = btn.dataset.alamat;
      editModal.querySelector('[name="package_id"]').value = btn.dataset.package_id;
      editModal.querySelector('[name="catatan"]').value = btn.dataset.catatan;
    });
  }
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
