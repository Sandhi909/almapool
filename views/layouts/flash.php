<?php foreach (take_flash() as $f): ?>
<div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : ($f['type'] === 'success' ? 'success' : 'info') ?> alert-dismissible fade show" role="alert">
  <?= e($f['message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; ?>
