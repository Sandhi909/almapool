<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <span class="hero-icon" style="position:relative;z-index:1"><i class="bi bi-check2-square fs-4"></i></span>
  <div style="position:relative;z-index:1">
    <small class="opacity-90" style="letter-spacing:.08em;font-weight:700">ABSENSI LES</small>
    <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Absensi Les — <?= e($instruktur ? $instruktur['name'] : 'Pilih instruktur') ?></h5>
    <small class="opacity-90">Satu pembayaran berlaku 3 bulan / 10x pertemuan — mana yang tercapai dulu.</small>
  </div>
  <form method="get" action="<?= url('index.php') ?>" class="ms-auto d-flex gap-2 align-items-end flex-wrap">
    <input type="hidden" name="page" value="absensi">
    <?php if ($isAdmin): ?>
    <div><label class="form-label small fw-semibold mb-1">Instruktur</label>
      <select class="form-select form-select-sm" name="instruktur" onchange="this.form.submit()">
        <option value="0">-- Pilih --</option>
        <?php foreach ($INSTRUKTUR as $i): ?>
        <option value="<?= $i['id'] ?>" <?= (int) $instrukturId === (int) $i['id'] ? 'selected' : '' ?>><?= e($i['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <?php endif; ?>
    <div><label class="form-label small fw-semibold mb-1">Tanggal</label>
      <input type="date" class="form-control form-control-sm" name="tanggal" value="<?= e($tanggal) ?>" onchange="this.form.submit()"></div>
  </form>
</div>

<?php if (!$instruktur): ?>
<div class="tt-card text-center text-muted py-5">
  <?= $isAdmin ? 'Pilih instruktur dulu.' : 'Akun Anda belum dikaitkan ke data instruktur. Hubungi admin.' ?>
</div>
<?php elseif (!$SISWA): ?>
<div class="tt-card text-center text-muted py-5">Tidak ada peserta aktif untuk instruktur ini.</div>
<?php else: ?>
<form method="post" action="<?= url('index.php?page=absensi') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
  <?php if ($isAdmin): ?><input type="hidden" name="instruktur_id" value="<?= (int) $instrukturId ?>"><?php endif; ?>
  <div class="tt-card">
    <div class="d-flex align-items-center gap-2 mb-2">
      <b>Peserta — <?= e(tanggal_indo($tanggal)) ?></b>
      <button class="btn btn-tt ms-auto" type="submit"><i class="bi bi-save me-1"></i>Simpan Absensi</button>
    </div>
    <div class="table-responsive"><table class="table tt-table align-middle mb-0">
      <thead><tr><th>Peserta</th><th>Progress</th><th>Masa Berlaku</th><th class="text-center">Hadir?</th></tr></thead>
      <tbody>
        <?php foreach ($SISWA as $s):
          $quota = max(1, (int) ($s['quota_sessions'] ?? 10));
          $hadir = (int) $s['hadir'];
          $pct = min(100, round($hadir / $quota * 100));
          $sisaHari = $s['end_date'] ? (int) floor((strtotime($s['end_date']) - strtotime(date('Y-m-d'))) / 86400) : null;
          $tandai = $s['absen_hari_ini'] ?? null;
        ?>
        <tr>
          <td><b><?= e($s['name']) ?></b><br><small class="text-muted"><?= e($s['paket'] ?: '-') ?> &bull; <?= $s['gender'] === 'P' ? 'Putri' : 'Putra' ?></small></td>
          <td style="min-width:140px"><b class="num"><?= $hadir ?>/<?= $quota ?></b>
            <div class="progress" style="height:6px"><div class="progress-bar" style="width:<?= $pct ?>%;background:var(--tt-primary)"></div></div></td>
          <td><span class="num"><?= e($s['end_date'] ? tanggal_indo($s['end_date']) : '-') ?></span><br>
            <small class="<?= $sisaHari !== null && $sisaHari < 0 ? 'text-danger' : 'text-muted' ?>"><?= $sisaHari === null ? '' : ($sisaHari < 0 ? 'Masa habis' : $sisaHari . ' hari lagi') ?></small></td>
          <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
              <input type="radio" class="btn-check" name="hadir[<?= (int) $s['id'] ?>]" id="h<?= (int) $s['id'] ?>" value="HADIR" <?= $tandai !== 'TIDAK' ? 'checked' : '' ?>>
              <label class="btn btn-outline-success" for="h<?= (int) $s['id'] ?>">Hadir</label>
              <input type="radio" class="btn-check" name="hadir[<?= (int) $s['id'] ?>]" id="t<?= (int) $s['id'] ?>" value="TIDAK" <?= $tandai === 'TIDAK' ? 'checked' : '' ?>>
              <label class="btn btn-outline-danger" for="t<?= (int) $s['id'] ?>">Tidak</label>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
    <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Peserta otomatis <b>Selesai</b> saat mencapai 10x hadir atau masa 3 bulan habis — mana yang lebih dulu.</small>
  </div>
</form>
<?php endif; ?>
