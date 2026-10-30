<?php
$nav = function (string $key, string $href, string $icon, string $label) use ($activeMenu) {
    $active = $activeMenu === $key ? ' active' : '';
    echo '<a class="nav-link' . $active . '" href="' . url($href) . '">'
        . '<i class="bi ' . $icon . '"></i><span>' . e($label) . '</span></a>';
};
$role = auth_role();
$u = auth_user();
$canKasir = in_array($role, ['admin', 'kasir'], true);
$canOwner = in_array($role, ['admin', 'manajemen'], true);
$canPetugas = in_array($role, ['admin', 'kasir', 'petugas'], true);
$isInstruktur = $role === 'instruktur';
?>
<aside class="tt-sidebar" id="ttSidebar">
  <div class="tt-brand">
    <img src="<?= url(APP_LOGO) ?>" alt="Logo" class="tt-logo">
    <div><strong style="font-size:.95rem">Kolam Renang</strong><small>AL MA'SOEM</small></div>
  </div>
  <div class="tt-session">
    <span class="dot"></span>
    <div><b><?= e($u['name'] ?? '-') ?></b><br><span class="text-muted"><?= e(ucfirst($role ?? '')) ?> &bull; Loket Pendaftaran</span></div>
  </div>
  <nav class="tt-nav">
    <div class="nav-cap">NAVIGASI UTAMA</div>
    <?php if (!$isInstruktur): ?>
      <?php $nav('dashboard', 'index.php?page=dashboard', 'bi-grid-1x2-fill', 'Dashboard'); ?>
    <?php endif; ?>
    <?php if ($isInstruktur): ?>
      <?php $nav('absensi', 'index.php?page=absensi', 'bi-check2-square', 'Absensi Les'); ?>
    <?php endif; ?>
    <?php if ($canKasir): ?>
      <?php $nav('kasir', 'index.php?page=kasir', 'bi-calculator-fill', 'Kasir & Tiket'); ?>
      <?php $nav('member', 'index.php?page=member', 'bi-person-badge-fill', 'Kelola Member'); ?>
    <?php endif; ?>
    <?php $nav('scan', 'index.php?page=scan', 'bi-qr-code-scan', 'Scan & Check-in'); ?>
    <?php if ($canKasir): ?>
      <?php $nav('les', 'index.php?page=les', 'bi-tsunami', 'Les Renang & Jadwal'); ?>
    <?php endif; ?>
    <?php if ($canKasir || $canOwner || $canInternalControl): ?>
      <?php $nav('transaksi', 'index.php?page=transaksi', 'bi-receipt-cutoff', 'Transaksi & Laporan'); ?>
      <?php $nav('laporan', 'index.php?page=laporan', 'bi-file-earmark-bar-graph-fill', 'Laporan Bulanan'); ?>
      <?php $nav('kunjungan', 'index.php?page=kunjungan', 'bi-people-fill', 'Laporan Kunjungan'); ?>
      <?php if ($canInternalControl): ?>
        <?php $nav('stok-opname', 'index.php?page=stok-opname', 'bi-clipboard-check', 'Stok Opname'); ?>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($canPetugas): ?>
      <?php $nav('obat', 'index.php?page=obat', 'bi-capsule', 'Stok Obat'); ?>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
      <?php $nav('master', 'index.php?page=master', 'bi-gear-fill', 'Master Data & Pengaturan'); ?>
    <?php endif; ?>
  </nav>
  <div class="tt-user">
    <div class="tt-avatar"><?= e(initials($u['name'] ?? '?')) ?></div>
    <div class="flex-fill"><b class="d-block" style="font-size:.82rem"><?= e($u['name'] ?? '-') ?></b>
      <small class="text-muted"><?= e(strtoupper($role ?? '')) ?></small></div>
    <a href="<?= url('index.php?page=logout') ?>" class="text-muted" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</aside>
<div class="tt-body">
