<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk &middot; Kolam Renang Al Ma'soem</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="login-card row g-0">
    <div class="col-lg-6 p-4 p-lg-5" style="background:linear-gradient(150deg,#0284c7,#0d9488);color:#fff">
      <div class="d-flex align-items-center gap-2 mb-4">
        <img src="<?= url(APP_LOGO) ?>" alt="Logo" style="height:44px;background:#fff;border-radius:12px;padding:2px">
        <div><h4 class="mb-0 fw-bold">Kolam Renang</h4><small style="letter-spacing:.15em;font-size:.65rem">AL MA'SOEM</small></div>
      </div>
      <h2 class="fw-bold mb-2">Sistem Manajemen Kolam Renang</h2>
      <p class="mb-4" style="color:#e0f2fe">Kasir tiket, membership QR, scan check-in turnstile, les renang &amp; monitoring — dalam satu pintu.</p>
      <ul class="list-unstyled d-grid gap-2 small">
        <li><i class="bi bi-check-circle-fill me-2"></i>Scan QR &rarr; data member muncul &rarr; check-in tercatat</li>
        <li><i class="bi bi-check-circle-fill me-2"></i>POS tiket harian &amp; rombongan (Cash / Transfer)</li>
        <li><i class="bi bi-check-circle-fill me-2"></i>Kartu member + QR dapat dicetak &amp; dicetak ulang</li>
      </ul>
    </div>
    <div class="col-lg-6 p-4 p-lg-5">
      <h4 class="fw-bold mb-1">Selamat Datang</h4>
      <p class="text-muted small mb-4">Masuk untuk mengelola kolam renang</p>
      <form method="post" action="<?= url('index.php?page=login') ?>">
        <?= csrf_field() ?>
        <?php if (!empty($GLOBALS['login_error'])): ?>
        <div class="alert alert-danger py-2 small"><?= e($GLOBALS['login_error']) ?></div>
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="username">Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold" for="password">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
          </div>
        </div>
        <button class="btn btn-tt w-100 btn-lg" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>MASUK</button>
      </form>
      <p class="text-center text-muted small mt-4 mb-0">&copy; 2026 Kolam Renang Al Ma'soem &mdash; Sistem Manajemen Kolam</p>
    </div>
  </div>
</div>
</body>
</html>
