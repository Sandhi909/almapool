</div><!-- /tt-body -->
<nav class="tt-bottomnav">
  <?php if (($activeMenu ?? '') === 'absensi' || auth_role() === 'instruktur'): ?>
    <a href="<?= url('index.php?page=absensi') ?>" class="<?= ($activeMenu ?? '') === 'absensi' ? 'active' : '' ?>"><i class="bi bi-check2-square"></i>Absensi</a>
    <a href="<?= url('index.php?page=logout') ?>" style="color:var(--tt-red)"><i class="bi bi-box-arrow-right"></i>Keluar</a>
  <?php else: ?>
  <a href="<?= url('index.php?page=scan') ?>" class="<?= ($activeMenu ?? '') === 'scan' ? 'active' : '' ?>"><i class="bi bi-qr-code-scan"></i>Scan QR</a>
  <a href="<?= url('index.php?page=kasir') ?>" class="<?= ($activeMenu ?? '') === 'kasir' ? 'active' : '' ?>"><i class="bi bi-calculator"></i>Kasir</a>
  <a href="<?= url('index.php?page=member') ?>" class="<?= ($activeMenu ?? '') === 'member' ? 'active' : '' ?>"><i class="bi bi-person-badge"></i>Member</a>
  <a href="<?= url('index.php?page=dashboard') ?>" class="<?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid"></i>Ringkasan</a>
  <a href="<?= url('index.php?page=logout') ?>" style="color:var(--tt-red)"><i class="bi bi-box-arrow-right"></i>Keluar</a>
  <?php endif; ?>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<?php if (!empty($pageScripts)) { echo $pageScripts; } ?>
</div><!-- /tt-app -->
</body>
</html>
