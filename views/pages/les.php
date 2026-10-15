<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-tsunami fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">LES RENANG & JADWAL</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Kelola Peserta & Jadwal Les</h5>
      <small class="opacity-90">1x bayar = 3 bulan / 10x pertemuan, mana dulu &bull; Putra/Putri terpisah</small>
    </div>
  </div>
  <div class="ms-auto px-3 py-2 rounded-4 bg-white text-dark" style="position:relative;z-index:1;min-width:140px;box-shadow:0 6px 16px rgba(0,0,0,.12)">
    <small class="text-muted d-block" style="font-size:.68rem">PESERTA AKTIF</small>
    <b class="num fs-5" style="color:#0284c7"><?= $STAT['les_aktif'] ?></b>
  </div>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Peserta Les Aktif</span><div class="metric-value num"><?= $STAT['les_aktif'] ?></div></div>
    <span class="metric-icon mi-blue"><i class="bi bi-people"></i></span></div></div>
  <div class="col-md-4"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Jadwal Terdaftar</span><div class="metric-value num"><?= count($JADWAL_LES) ?> <small class="text-muted" style="font-size:.9rem">kelas</small></div></div>
    <span class="metric-icon mi-teal"><i class="bi bi-calendar-week"></i></span></div></div>
  <div class="col-md-4"><div class="tt-card d-flex justify-content-between align-items-center">
    <div><span class="metric-label">Instruktur Aktif</span><div class="metric-value num"><?= count($INSTRUKTUR) ?></div></div>
    <span class="metric-icon mi-green"><i class="bi bi-person-video3"></i></span></div></div>
</div>

<div class="row g-3">
  <div class="col-xl-7">
    <div class="tt-card mb-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-people me-1"></i>Peserta Les <small class="text-muted fw-normal">(1x bayar = 3 bulan / 10x pertemuan, mana dulu)</small></b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#siswaModal"><i class="bi bi-plus-lg me-1"></i>Tambah Peserta</button>
      </div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama</th><th>Paket</th><th>Instruktur</th><th>Progress</th><th>Masa</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($SISWA as $s):
            $quota = max(1, (int) ($s['quota_sessions'] ?? 10));
            $hadir = (int) $s['hadir'];
            $sisaHari = $s['end_date'] ? (int) floor((strtotime($s['end_date']) - strtotime(date('Y-m-d'))) / 86400) : null;
            if ($s['status'] === 'CANCELLED') { $stx = '<span class="pill pill-grey">Batal</span>'; }
            elseif ($hadir >= $quota) { $stx = '<span class="pill pill-green"><span class="dot"></span>Selesai (kuota)</span>'; }
            elseif ($sisaHari !== null && $sisaHari < 0) { $stx = '<span class="pill pill-amber">Selesai (masa habis)</span>'; }
            else { $stx = '<span class="pill pill-blue"><span class="dot"></span>Aktif</span>'; }
          ?>
          <tr>
            <td><b><?= e($s['name']) ?></b><br><small class="text-muted"><?= $s['gender'] === 'P' ? 'Putri' : 'Putra' ?> &bull; <span class="num"><?= e($s['phone'] ?: '-') ?></span></small></td>
            <td><?= e($s['paket'] ?: '-') ?></td>
            <td><?= e($s['instruktur'] ?: '-') ?></td>
            <td><b class="num"><?= $hadir ?>/<?= $quota ?></b>
              <div class="progress" style="height:6px"><div class="progress-bar" style="width:<?= min(100, round($hadir / $quota * 100)) ?>%;background:var(--tt-primary)"></div></div></td>
            <td><span class="num"><?= e($s['end_date'] ? tanggal_indo($s['end_date']) : '-') ?></span><br>
              <small class="<?= $sisaHari !== null && $sisaHari < 0 ? 'text-danger' : 'text-muted' ?>"><?= $sisaHari === null ? '' : ($sisaHari < 0 ? 'Habis' : $sisaHari . ' hari lagi') ?></small></td>
            <td><?= $stx ?></td>
            <td class="text-nowrap">
              <button class="btn btn-tt-soft btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#absenModal"
              data-siswa="<?= (int) $s['id'] ?>" data-nama="<?= e($s['name']) ?>"><i class="bi bi-check2-square me-1"></i>Absen</button>
              <form method="post" action="<?= url('index.php?page=les') ?>" class="d-inline" onsubmit="return confirm('Hapus peserta <?= e(addslashes($s['name'])) ?> beserta absensinya?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="hapus-siswa"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$SISWA): ?><tr><td colspan="7" class="text-center text-muted">Belum ada peserta.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>

    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-calendar3 me-1"></i>Jadwal Les (kolam putra/putri terpisah)</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#jadwalModal"><i class="bi bi-plus-lg me-1"></i>Tambah Jadwal</button>
      </div>
      <div class="table-responsive">
        <table class="table tt-table align-middle mb-0">
          <thead><tr><th>Hari</th><th>Jam</th><th>Kelas</th><th>Kolam</th><th>Instruktur</th><th>Kapasitas</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($JADWAL_LES as $j): ?>
            <tr>
              <td><b><?= e($j['day_of_week']) ?></b></td>
              <td class="num"><?= e(substr($j['start_time'], 0, 5)) ?> – <?= e(substr($j['end_time'], 0, 5)) ?></td>
              <td><?= e($j['class_name']) ?></td>
              <td><span class="pill <?= $j['area'] === 'PUTRI' ? 'pill-amber' : ($j['area'] === 'PUTRA' ? 'pill-blue' : 'pill-grey') ?>"><?= e(ucfirst(strtolower($j['area']))) ?></span></td>
              <td><?= e($j['instruktur'] ?: '-') ?></td>
              <td class="num"><?= (int) $j['capacity'] ?></td>
              <td>
                <form method="post" action="<?= url('index.php?page=les') ?>" class="d-inline" onsubmit="return confirm('Hapus jadwal <?= e(addslashes($j['class_name'])) ?>?')">
                  <?= csrf_field() ?><input type="hidden" name="action" value="hapus-jadwal"><input type="hidden" name="id" value="<?= (int) $j['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="tt-card mb-3">
      <b><i class="bi bi-tsunami me-1"></i>Paket Les <small class="text-muted fw-normal">(sekali bayar: 3 bulan / 10x, mana dulu)</small></b>
      <?php foreach ($LES_PAKET as $p): ?>
      <div class="d-flex align-items-center gap-2 py-2 border-bottom">
        <div class="flex-fill"><b>Les <?= e($p['name']) ?></b><br><small class="text-muted"><?= e($p['description'] ?: '') ?></small></div>
        <b class="num"><?= rupiah($p['price']) ?></b>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="tt-card">
      <b><i class="bi bi-person-video3 me-1"></i>Instruktur</b>
      <?php foreach ($INSTRUKTUR as $i): ?>
      <div class="d-flex gap-2 align-items-center py-2 border-bottom">
        <span class="tt-avatar"><?= e(initials($i['name'])) ?></span>
        <div class="flex-fill"><b><?= e($i['name']) ?></b><br><small class="text-muted"><?= e($i['specialty'] ?: '-') ?> &bull; <span class="num"><?= e($i['phone'] ?: '-') ?></span></small></div>
        <span class="pill pill-green"><span class="dot"></span>Aktif</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="modal fade" id="siswaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=les') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="siswa">
      <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Peserta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-12"><label class="form-label fw-semibold">Nama *</label><input class="form-control" name="nama" required></div>
        <div class="col-6"><label class="form-label fw-semibold">HP</label><input class="form-control" name="hp"></div>
        <div class="col-6"><label class="form-label fw-semibold">Putra/Putri</label><select class="form-select" name="gender"><option value="L">Putra</option><option value="P">Putri</option></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Paket</label><select class="form-select" name="package_id">
          <?php foreach ($LES_PAKET as $p): ?><option value="<?= $p['id'] ?>">Les <?= e($p['name']) ?> — <?= rupiah($p['price']) ?> (<?= (int) $p['duration_days'] ?> hari / <?= (int) $p['session_count'] ?>x)</option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Mulai Tanggal</label><input type="date" class="form-control" name="tgl_mulai" value="<?= date('Y-m-d') ?>"></div>
        <div class="col-6"><label class="form-label fw-semibold">Instruktur</label><select class="form-select" name="instructor_id">
          <?php foreach ($INSTRUKTUR as $i): ?><option value="<?= $i['id'] ?>"><?= e($i['name']) ?></option><?php endforeach; ?></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="jadwalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=les') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="jadwal">
      <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Jadwal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-6"><label class="form-label fw-semibold">Hari</label><select class="form-select" name="hari">
          <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h): ?><option><?= $h ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Kolam</label><select class="form-select" name="area"><option value="PUTRA">Putra</option><option value="PUTRI">Putri</option></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Mulai</label><input type="time" class="form-control" name="mulai" value="08:00"></div>
        <div class="col-6"><label class="form-label fw-semibold">Selesai</label><input type="time" class="form-control" name="selesai" value="09:00"></div>
        <div class="col-12"><label class="form-label fw-semibold">Nama Kelas</label><input class="form-control" name="kelas" required></div>
        <div class="col-6"><label class="form-label fw-semibold">Instruktur</label><select class="form-select" name="instructor_id">
          <?php foreach ($INSTRUKTUR as $i): ?><option value="<?= $i['id'] ?>"><?= e($i['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label fw-semibold">Kapasitas</label><input type="number" class="form-control" name="kapasitas" value="10" min="1"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="absenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=les') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="absen">
      <input type="hidden" name="student_id" id="absenSiswa">
      <div class="modal-header"><h5 class="modal-title fw-bold">Catat Pertemuan — <span id="absenNama"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body row g-3">
        <div class="col-6"><label class="form-label fw-semibold">Tanggal</label><input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>"></div>
        <div class="col-6"><label class="form-label fw-semibold">Kehadiran</label><select class="form-select" name="hadir"><option value="HADIR">Hadir</option><option value="TIDAK">Tidak hadir</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<?php ob_start(); ?>
<script>
document.getElementById('absenModal').addEventListener('show.bs.modal', function (ev) {
  const b = ev.relatedTarget;
  document.getElementById('absenSiswa').value = b.dataset.siswa;
  document.getElementById('absenNama').textContent = b.dataset.nama;
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
