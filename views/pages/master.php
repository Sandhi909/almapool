<?php
$delForm = function (string $tabel, int $id): string {
    return '<form method="post" action="' . url('index.php?page=master') . '" class="d-inline" onsubmit="return confirm(\'Hapus data ini?\')">'
        . csrf_field() . '<input type="hidden" name="action" value="hapus">'
        . '<input type="hidden" name="tabel" value="' . $tabel . '"><input type="hidden" name="id" value="' . $id . '">'
        . '<button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button></form>';
};
?>
<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-gear fs-4"></i></span>
    <div>
      <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">MASTER DATA & PENGATURAN</small>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Kelola Tiket, Paket & Sistem</h5>
      <small class="opacity-90">Sinkron dengan tarif Kasir & Kolam Putra/Putri</small>
    </div>
  </div>
</div>
<ul class="nav nav-pills gap-2 mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-tiket" type="button"><i class="bi bi-ticket-perforated me-1"></i>Paket Tiket</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-paket" type="button"><i class="bi bi-person-badge me-1"></i>Membership</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-les" type="button"><i class="bi bi-water me-1"></i>Paket Les</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-instruktur" type="button"><i class="bi bi-person-video3 me-1"></i>Instruktur</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-jenjang" type="button"><i class="bi bi-mortarboard me-1"></i>Jenjang</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-usia" type="button"><i class="bi bi-people me-1"></i>Kelompok Usia</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-rombongan" type="button"><i class="bi bi-flag me-1"></i>Rombongan</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-obat" type="button"><i class="bi bi-capsule me-1"></i>Kategori & Satuan Obat</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-biaya" type="button"><i class="bi bi-cash-coin me-1"></i>Jenis Biaya</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-user" type="button"><i class="bi bi-people me-1"></i>User & Role</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pengaturan" type="button"><i class="bi bi-sliders me-1"></i>Pengaturan</button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tab-tiket">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Paket Tiket (7 jenis + warna)</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#tiketModal"><i class="bi bi-plus-lg me-1"></i>Tambah Tiket</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Jenis Tiket</th><th>Warna</th><th class="text-end">Tarif A</th><th class="text-end">Tarif B</th><th class="text-end">Tarif C</th><th>Kategori</th><th>Opsi</th><th>Nomor Seri</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($TIKET as $t): ?>
          <tr><td><b><?= e($t['name']) ?></b></td><td><span class="pill pill-blue"><?= e($t['color']) ?></span></td>
            <td class="text-end num"><?= $t['price_a'] ? number_format($t['price_a'], 0, ',', '.') : '-' ?></td>
            <td class="text-end num"><?= $t['price_b'] ? number_format($t['price_b'], 0, ',', '.') : '-' ?></td>
            <td class="text-end num"><?= $t['price_c'] ? number_format($t['price_c'], 0, ',', '.') : '-' ?></td>
            <td><?php if(!empty($t['is_internal'])) echo '<span class="pill pill-green" style="font-size:.65rem">Internal</span>'; else echo '<span class="pill pill-amber" style="font-size:.65rem">Eksternal</span>'; ?></td>
            <td class="small"><?php if(!empty($t['require_jenjang'])) echo '<span class="pill pill-amber" style="font-size:.65rem">Jenjang</span> '; if(!empty($t['require_usia']??0)) echo '<span class="pill pill-green" style="font-size:.65rem">Usia</span> '; if(!empty($t['require_nama'])) echo '<span class="pill pill-blue" style="font-size:.65rem">Rombongan</span>'; if(empty($t['require_jenjang'])&&empty($t['require_nama'])&&empty($t['require_usia']??0)) echo '<span class="text-muted">-</span>'; ?></td>
            <td class="small num"><?= e($t['serial_prefix'] ?? '') ?><?= str_pad((string)($t['serial_next'] ?? 1),4,'0',STR_PAD_LEFT) ?> <small class="text-muted">next</small></td>
            <td><?= (int) $t['is_active'] ? '<span class="pill pill-green"><span class="dot"></span>Aktif</span>' : '<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#tiketModal"
                data-row='<?= e(json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Edit</button>
              <?= $delForm('tiket', (int) $t['id']) ?>
            </td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
      <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>A: Senin–Sabtu &bull; B: Minggu &amp; Libur Nasional &bull; C: Idul Fitri. Harga transaksi menyimpan snapshot tarif saat dibayar.</small>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-paket">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Paket Membership (Gold / Silver)</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#paketModal"><i class="bi bi-plus-lg me-1"></i>Tambah Paket</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Jenis</th><th>Kelas</th><th>Durasi (hari)</th><th class="text-end">Harga (Rp)</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($MEMBER_PAKET as $p): ?>
          <tr><td><b><?= e($p['name']) ?></b></td>
            <td><span class="pill <?= $p['tier'] === 'GOLD' ? 'pill-amber' : 'pill-blue' ?>"><?= e(ucfirst(strtolower($p['tier']))) ?></span></td>
            <td class="num"><?= (int) $p['duration_days'] ?></td>
            <td class="text-end num fw-bold"><?= number_format($p['price'], 0, ',', '.') ?></td>
            <td><?= (int) $p['is_active'] ? '<span class="pill pill-green"><span class="dot"></span>Aktif</span>' : '<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#paketModal"
                data-row='<?= e(json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Edit</button>
              <?= $delForm('paket', (int) $p['id']) ?>
            </td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-les">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Paket Les (Putra / Putri)</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#lesModal"><i class="bi bi-plus-lg me-1"></i>Tambah Paket Les</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Jenis Les</th><th class="text-end">Tarif (Rp)</th><th>Masa / Kuota</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($LES_PAKET as $p): ?>
          <tr><td><b>Les <?= e($p['name']) ?></b></td>
            <td class="text-end num fw-bold"><?= number_format($p['price'], 0, ',', '.') ?></td>
            <td class="num"><?= (int) $p['duration_days'] ?> hari / <?= (int) $p['session_count'] ?>x</td>
            <td><?= (int) $p['is_active'] ? '<span class="pill pill-green"><span class="dot"></span>Aktif</span>' : '<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#lesModal"
                data-row='<?= e(json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Edit</button>
              <?= $delForm('lespaket', (int) $p['id']) ?>
            </td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-instruktur">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Instruktur</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#insModal"><i class="bi bi-plus-lg me-1"></i>Tambah Instruktur</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama</th><th>HP</th><th>Spesialis</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($INSTRUKTUR as $i): ?>
          <tr><td><b><?= e($i['name']) ?></b></td><td class="num"><?= e($i['phone'] ?: '-') ?></td><td><?= e($i['specialty'] ?: '-') ?></td>
            <td><?= (int) $i['is_active'] ? '<span class="pill pill-green"><span class="dot"></span>Aktif</span>' : '<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#insModal"
                data-row='<?= e(json_encode($i, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Edit</button>
              <?= $delForm('instruktur', (int) $i['id']) ?>
            </td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-jenjang">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Master Jenjang (SD/SMP/SMA...)</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#jenjangModal"><i class="bi bi-plus-lg me-1"></i>Tambah Jenjang</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama</th><th>Urut</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach (($JENJANG ?? []) as $j): ?>
          <tr><td><b><?= e($j['name']) ?></b></td><td class="num"><?= (int)$j['sort_order'] ?></td>
            <td><?= (int)$j['is_active']?'<span class="pill pill-green">Aktif</span>':'<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap"><button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#jenjangModal" data-row='<?= e(json_encode($j,JSON_HEX_APOS|JSON_HEX_QUOT)) ?>'>Edit</button> <?= $delForm('jenjang',(int)$j['id']) ?></td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
      <small class="text-muted">Dipakai di Kasir saat pilih jenjang (dropdown dinamis).</small>
    </div>
  </div>
  <div class="tab-pane fade" id="tab-usia">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Master Kelompok Usia</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#usiaModal"><i class="bi bi-plus-lg me-1"></i>Tambah Kelompok</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama</th><th>Rentang Usia</th><th>Urut</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach (($KELOMPOK_USIA ?? []) as $u): ?>
          <tr><td><b><?= e($u['name']) ?></b></td><td class="num"><?= $u['min_age']!==null? (int)$u['min_age'].' - '.(int)$u['max_age'].' th' : '-' ?></td><td class="num"><?= (int)$u['sort_order'] ?></td>
            <td><?= (int)$u['is_active']?'<span class="pill pill-green">Aktif</span>':'<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap"><button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#usiaModal" data-row='<?= e(json_encode($u,JSON_HEX_APOS|JSON_HEX_QUOT)) ?>'>Edit</button> <?= $delForm('kelompok_usia',(int)$u['id']) ?></td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
      <small class="text-muted">Misal: Anak-anak, Remaja, Dewasa — wajib pilih kalau tiket aktifkan opsi Usia.</small>
    </div>
  </div>
  <div class="tab-pane fade" id="tab-rombongan">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Master Rombongan Eksternal</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#rombonganModal"><i class="bi bi-plus-lg me-1"></i>Tambah Rombongan</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama Rombongan</th><th>Deskripsi</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach (($ROMBONGAN ?? []) as $r): ?>
          <tr><td><b><?= e($r['name']) ?></b></td><td><?= e($r['description']??'-') ?></td>
            <td><?= (int)$r['is_active']?'<span class="pill pill-green">Aktif</span>':'<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap"><button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#rombonganModal" data-row='<?= e(json_encode($r,JSON_HEX_APOS|JSON_HEX_QUOT)) ?>'>Edit</button> <?= $delForm('rombongan',(int)$r['id']) ?></td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
      <small class="text-muted">Dropdown rombongan eksternal di Kasir — pilih, tidak perlu ketik manual.</small>
    </div>
  </div>
  <div class="tab-pane fade" id="tab-obat">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="tt-card h-100">
          <div class="d-flex align-items-center gap-2 mb-2"><b>Kategori Obat</b>
            <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#kategoriModal"><i class="bi bi-plus-lg me-1"></i>Tambah</button></div>
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><th>Nama</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($CATEGORIES as $c): ?>
              <tr><td><?= e($c['name']) ?></td>
                <td>
                  <form method="post" action="<?= url('index.php?page=master') ?>" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                    <?= csrf_field() ?><input type="hidden" name="action" value="hapus_kategori"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="tt-card h-100">
          <div class="d-flex align-items-center gap-2 mb-2"><b>Satuan Obat</b>
            <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#satuanModal"><i class="bi bi-plus-lg me-1"></i>Tambah</button></div>
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><th>Nama</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($UNITS as $u): ?>
              <tr><td><?= e($u['name']) ?></td>
                <td>
                  <form method="post" action="<?= url('index.php?page=master') ?>" class="d-inline" onsubmit="return confirm('Hapus satuan ini?')">
                    <?= csrf_field() ?><input type="hidden" name="action" value="hapus_satuan"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-biaya">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>Jenis Biaya Operasional</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#biayaTypeModal"><i class="bi bi-plus-lg me-1"></i>Tambah Jenis Biaya</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama Jenis Biaya</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($JENIS_BIAYA as $jb): ?>
          <tr><td><b><?= e($jb['name']) ?></b></td>
            <td><?= (int) $jb['is_active'] ? '<span class="pill pill-green"><span class="dot"></span>Aktif</span>' : '<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#biayaTypeModal"
                data-row='<?= e(json_encode($jb, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Edit</button>
              <?= $delForm('jenis_biaya', (int) $jb['id']) ?>
            </td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
      <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Jenis biaya digunakan saat input biaya operasional di halaman transaksi.</small>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-user">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2"><b>User &amp; Role</b>
        <button class="btn btn-tt btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#userModal"><i class="bi bi-plus-lg me-1"></i>Tambah User</button></div>
      <div class="table-responsive"><table class="table tt-table align-middle mb-0">
        <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($USERS as $u): ?>
          <tr><td><b><?= e($u['name']) ?></b></td><td class="num"><?= e($u['username']) ?></td>
            <td><span class="pill pill-blue"><?= e($u['role']) ?></span></td>
            <td><?= (int) $u['is_active'] ? '<span class="pill pill-green"><span class="dot"></span>Aktif</span>' : '<span class="pill pill-grey">Nonaktif</span>' ?></td>
            <td class="text-nowrap">
              <button class="btn btn-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#userModal"
                data-row='<?= e(json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Edit</button>
              <?= $delForm('user', (int) $u['id']) ?>
            </td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
      <small class="text-muted d-block mt-2"><i class="bi bi-shield-check me-1"></i>Admin (penuh) &bull; Kasir (transaksi) &bull; Petugas Kolam (scan saja, tanpa data keuangan) &bull; Manajemen (monitoring).</small>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-pengaturan">
    <form method="post" action="<?= url('index.php?page=master') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="setting">
      <div class="row g-3">
        <div class="col-md-6"><div class="tt-card h-100">
          <b>Kolam (putra/putri terpisah)</b>
          <div class="row g-2 mt-1">
            <div class="col-12"><label class="form-label small fw-semibold mb-1">Nama Kolam</label><input class="form-control" name="nama_kolam" value="<?= e($settings['nama_kolam'] ?? '') ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Kapasitas Maks (pax)</label><input type="number" class="form-control" name="kapasitas" value="<?= e($settings['kapasitas'] ?? 400) ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Kolam Putra</label><input class="form-control" name="sesi_putra" value="<?= e($settings['sesi_putra'] ?? '') ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Kolam Putri</label><input class="form-control" name="sesi_putri" value="<?= e($settings['sesi_putri'] ?? '') ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Batas Expiring (hari)</label><input type="number" class="form-control" name="expiring_days" value="<?= e($settings['expiring_days'] ?? 7) ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Biaya Silver Hari Libur (Rp)</label><input class="form-control num" name="silver_holiday_fee" value="<?= e($settings['silver_holiday_fee'] ?? 15000) ?>"></div>
            <div class="col-12"><label class="form-label small fw-semibold mb-1">Tanggal Libur Nasional (satu per baris, YYYY-MM-DD)</label><textarea class="form-control" name="holidays" rows="3" placeholder="2026-12-25"><?= e($settings['holidays'] ?? '') ?></textarea>
              <small class="text-muted">Sabtu/Minggu otomatis libur. Gold bebas kapan saja; Silver di hari libur kena biaya di atas.</small></div>
          </div>
        </div></div>
        <div class="col-md-6"><div class="tt-card h-100">
          <b>Saldo Awal &amp; TTD Laporan</b>
          <div class="row g-2 mt-1">
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Saldo Awal — Bon (Rp)</label><input class="form-control num" name="saldo_awal_bon" value="<?= e($settings['saldo_awal_bon'] ?? 0) ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Saldo Awal — Tunai (Rp)</label><input class="form-control num" name="saldo_awal_tunai" value="<?= e($settings['saldo_awal_tunai'] ?? 0) ?>"></div>
            <?php foreach ([['ttd_admin', 'ttd_admin_jabatan'], ['ttd_manajer', 'ttd_manajer_jabatan'], ['ttd_kepala', 'ttd_kepala_jabatan']] as $pair): ?>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Nama</label><input class="form-control" name="<?= $pair[0] ?>" value="<?= e($settings[$pair[0]] ?? '') ?>"></div>
            <div class="col-6"><label class="form-label small fw-semibold mb-1">Jabatan</label><input class="form-control" name="<?= $pair[1] ?>" value="<?= e($settings[$pair[1]] ?? '') ?>"></div>
            <?php endforeach; ?>
          </div>
        </div></div>
        <div class="col-12"><button class="btn btn-tt" type="submit"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button></div>
      </div>
    </form>
  </div>
</div>

<?php
$modal = function (string $id, string $title, string $action, string $body): void {
    echo '<div class="modal fade" id="' . $id . '" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered">'
        . '<form class="modal-content" style="border-radius:20px" method="post" action="' . url('index.php?page=master') . '">'
        . csrf_field() . '<input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="id" class="f-id">'
        . '<div class="modal-header"><h5 class="modal-title fw-bold">' . $title . '</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body row g-3">' . $body . '</div>'
        . '<div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-tt" type="submit">Simpan</button></div>'
        . '</form></div></div>';
};
$modal('tiketModal', 'Paket Tiket',
    'tiket',
    '<div class="col-12"><label class="form-label fw-semibold">Jenis Tiket</label><input class="form-control f-nama" name="nama" required></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Warna</label><input class="form-control f-warna" name="warna"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Status</label><select class="form-select f-aktif" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Kategori Laporan</label><select class="form-select f-kategori" name="is_internal"><option value="0">Eksternal</option><option value="1">Internal</option></select><small class="text-muted" style="font-size:.68rem">Internal = Karyawan/Keluarga/Siswa/Santri, Eksternal = Umum/Rombongan</small></div>'
    . '<div class="col-6 d-flex align-items-end"><small class="text-muted" style="font-size:.70rem">Kategori menentukan hitungan Internal vs Eksternal di Laporan Kunjungan.</small></div>'
    . '<div class="col-4"><label class="form-label fw-semibold">Tarif A</label><input class="form-control num f-a" name="a" inputmode="numeric"></div>'
    . '<div class="col-4"><label class="form-label fw-semibold">Tarif B</label><input class="form-control num f-b" name="b" inputmode="numeric"></div>'
    . '<div class="col-4"><label class="form-label fw-semibold">Tarif C</label><input class="form-control num f-c" name="c" inputmode="numeric"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Prefix Seri</label><input class="form-control f-prefix" name="serial_prefix" placeholder="UM / kosong" maxlength="10"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Nomor Seri Awal</label><input type="number" class="form-control f-seri" name="serial_next" min="1" placeholder="1"></div>'
    . '<div class="col-4"><div class="form-check mt-2"><input class="form-check-input f-jenjang" type="checkbox" name="require_jenjang" value="1" id="tJenjang"><label class="form-check-label small fw-semibold" for="tJenjang">Butuh Jenjang</label></div></div>'
    . '<div class="col-4"><div class="form-check mt-2"><input class="form-check-input f-usia" type="checkbox" name="require_usia" value="1" id="tUsia"><label class="form-check-label small fw-semibold" for="tUsia">Butuh Kelompok Usia</label></div></div>'
    . '<div class="col-4"><div class="form-check mt-2"><input class="form-check-input f-nama-req" type="checkbox" name="require_nama" value="1" id="tNama"><label class="form-check-label small fw-semibold" for="tNama">Butuh Rombongan</label></div></div>');
$modal('paketModal', 'Paket Membership',
    'paket',
    '<div class="col-8"><label class="form-label fw-semibold">Jenis</label><input class="form-control f-nama" name="nama" required></div>'
    . '<div class="col-4"><label class="form-label fw-semibold">Kelas</label><select class="form-select f-tier" name="tier"><option value="GOLD">Gold</option><option value="SILVER">Silver</option></select></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Durasi (hari)</label><input type="number" class="form-control f-durasi" name="durasi" value="30"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Harga (Rp)</label><input class="form-control num f-harga" name="harga" inputmode="numeric"></div>');
$modal('lesModal', 'Paket Les',
    'lespaket',
    '<div class="col-12"><label class="form-label fw-semibold">Jenis Les</label><input class="form-control f-nama" name="nama" required></div>'
    . '<div class="col-12"><label class="form-label fw-semibold">Harga (Rp)</label><input class="form-control num f-harga" name="harga" inputmode="numeric"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Masa (hari)</label><input type="number" class="form-control f-durasi" name="durasi" value="90"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Kuota (pertemuan)</label><input type="number" class="form-control f-kuota" name="kuota" value="10"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Status</label><select class="form-select f-aktif" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>');
$modal('insModal', 'Instruktur',
    'instruktur',
    '<div class="col-12"><label class="form-label fw-semibold">Nama</label><input class="form-control f-nama" name="nama" required></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">HP</label><input class="form-control f-hp" name="hp"></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Spesialis</label><input class="form-control f-spesialis" name="spesialis"></div>');
$modal('userModal', 'User',
    'user',
    '<div class="col-6"><label class="form-label fw-semibold">Nama</label><input class="form-control f-nama" name="nama" required></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Username</label><input class="form-control f-username" name="username" required></div>'
     . '<div class="col-6"><label class="form-label fw-semibold">Role</label><select class="form-select f-role" name="role"><option value="admin">Admin</option><option value="kasir">Kasir</option><option value="petugas">Petugas Kolam</option><option value="instruktur">Instruktur</option><option value="manajemen">Manajemen</option></select></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Kaitkan Instruktur (khusus role instruktur)</label><select class="form-select f-inslink" name="instructor_link"><option value="0">-- Tidak dikaitkan --</option>'
    . implode('', array_map(fn ($i) => '<option value="' . (int) $i['id'] . '">' . e($i['name']) . '</option>', $INSTRUKTUR))
    . '</select></div>'
    . '<div class="col-12"><label class="form-label fw-semibold">Password (kosongkan bila tak diubah)</label><input type="password" class="form-control" name="password"></div>');
$modal('jenjangModal','Master Jenjang','jenjang','<div class="col-8"><label class="form-label fw-semibold">Nama Jenjang</label><input class="form-control f-nama" name="nama" required placeholder="SD / SMP / SMA"></div><div class="col-4"><label class="form-label fw-semibold">Urut</label><input type="number" class="form-control f-sort" name="sort_order" value="0"></div><div class="col-6"><label class="form-label fw-semibold">Status</label><select class="form-select f-aktif" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>');
$modal('usiaModal','Kelompok Usia','kelompok_usia','<div class="col-6"><label class="form-label fw-semibold">Nama</label><input class="form-control f-nama" name="nama" required placeholder="Anak-anak / Remaja / Dewasa"></div><div class="col-3"><label class="form-label fw-semibold">Min Usia</label><input type="number" class="form-control f-min" name="min_age"></div><div class="col-3"><label class="form-label fw-semibold">Maks Usia</label><input type="number" class="form-control f-max" name="max_age"></div><div class="col-4"><label class="form-label fw-semibold">Urut</label><input type="number" class="form-control f-sort" name="sort_order" value="0"></div><div class="col-4"><label class="form-label fw-semibold">Warna</label><input class="form-control f-color" name="color" placeholder="info/warning"></div><div class="col-4"><label class="form-label fw-semibold">Status</label><select class="form-select f-aktif" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>');
$modal('rombonganModal','Master Rombongan','rombongan','<div class="col-12"><label class="form-label fw-semibold">Nama Rombongan</label><input class="form-control f-nama" name="nama" required placeholder="Karang Taruna / Instansi"></div><div class="col-12"><label class="form-label fw-semibold">Deskripsi</label><input class="form-control f-deskripsi" name="deskripsi"></div><div class="col-6"><label class="form-label fw-semibold">Status</label><select class="form-select f-aktif" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>');
$modal('kategoriModal', 'Tambah Kategori Obat',
    'tambah_kategori',
    '<div class="col-12"><label class="form-label fw-semibold">Nama Kategori</label><input class="form-control" name="nama" required placeholder="Contoh: Kaporit, pH Minus..."></div>');
$modal('satuanModal', 'Tambah Satuan Obat',
    'tambah_satuan',
    '<div class="col-12"><label class="form-label fw-semibold">Nama Satuan</label><input class="form-control" name="nama" required placeholder="Contoh: botol, liter, kg..."></div>');
$modal('biayaTypeModal', 'Jenis Biaya Operasional',
    'jenis_biaya',
    '<div class="col-12"><label class="form-label fw-semibold">Nama Jenis Biaya</label><input class="form-control f-nama" name="nama" required placeholder="Contoh: Backwash, Listrik, Gaji..."></div>'
    . '<div class="col-6"><label class="form-label fw-semibold">Status</label><select class="form-select f-aktif" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>');
?>

<?php ob_start(); ?>
<script>
document.querySelectorAll('.modal').forEach(function (md) {
  md.addEventListener('show.bs.modal', function (ev) {
    const b = ev.relatedTarget;
    const row = b && b.dataset.row ? JSON.parse(b.dataset.row) : null;
    md.querySelector('.f-id').value = row && row.id ? row.id : '';
    const set = (sel, v) => { const el = md.querySelector(sel); if (el) el.value = (v === null || v === undefined) ? '' : v; };
    const jEl0 = md.querySelector('.f-jenjang'); if(jEl0) jEl0.checked = false;
    const nEl0 = md.querySelector('.f-nama-req'); if(nEl0) nEl0.checked = false;
    const uEl0 = md.querySelector('.f-usia'); if(uEl0) uEl0.checked = false;
    if (!row) return;
    set('.f-nama', row.name); set('.f-warna', row.color); set('.f-a', row.price_a); set('.f-b', row.price_b); set('.f-c', row.price_c);
    const jenjangEl = md.querySelector('.f-jenjang'); if(jenjangEl) jenjangEl.checked = !!(row.require_jenjang && parseInt(row.require_jenjang));
    const usiaEl = md.querySelector('.f-usia'); if(usiaEl) usiaEl.checked = !!(row.require_usia && parseInt(row.require_usia));
    const namaEl = md.querySelector('.f-nama-req'); if(namaEl) namaEl.checked = !!(row.require_nama && parseInt(row.require_nama));
    const katEl = md.querySelector('.f-kategori'); if(katEl) katEl.value = String(row.is_internal ?? 0);
    set('.f-prefix', row.serial_prefix); set('.f-seri', row.serial_next ?? 1);
    set('.f-tier', row.tier); set('.f-durasi', row.duration_days); set('.f-harga', row.price);
    set('.f-hp', row.phone); set('.f-spesialis', row.specialty); set('.f-username', row.username); set('.f-role', row.role);
    set('.f-aktif', String(row.is_active ?? 1));
    set('.f-sort', row.sort_order); set('.f-min', row.min_age); set('.f-max', row.max_age); set('.f-color', row.color); set('.f-deskripsi', row.description);
    set('.f-durasi', row.duration_days); set('.f-kuota', row.session_count);
    set('.f-inslink', row.instructor_id || '0');
  });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
