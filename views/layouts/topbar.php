<div class="tt-topbar">
  <button class="icon-btn d-lg-none" id="btnSidebar" aria-label="Menu"><i class="bi bi-list"></i></button>
  <div class="tt-breadcrumb d-none d-md-flex align-items-center gap-2">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:30px;height:30px;background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#fff"><i class="bi bi-water"></i></span>
    <b class="fw-semibold text-dark">Kolam Renang Al Ma'soem</b><span class="text-muted">/</span><span class="fw-semibold" style="color:#0284c7"><?= e($pageTitle ?? '') ?></span>
  </div>
  <div class="ms-auto d-flex align-items-center gap-2">
    <div class="d-flex align-items-center gap-2">
      <div class="tt-avatar"><?= e(initials(auth_user()['name'] ?? '?')) ?></div>
      <div class="d-none d-md-block"><b class="d-block" style="font-size:.8rem"><?= e(auth_user()['name'] ?? '-') ?></b>
        <small class="text-muted"><?= e(ucfirst(auth_role() ?? '')) ?></small></div>
    </div>
    <a href="<?= url('index.php?page=logout') ?>" class="icon-btn" title="Keluar" aria-label="Keluar" style="color:var(--tt-red);border-color:#fecaca;background:#fef2f2"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>
