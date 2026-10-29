<div class="kasir-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative no-print" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white" style="width:48px;height:48px;color:#0ea5e9;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-receipt fs-4"></i></span>
    <div>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Struk Transaksi — <?= e($t['number']) ?></h5>
      <small class="opacity-90"><?= e($t['payment_method']) ?> &bull; <?= rupiah($t['amount']) ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 no-print" style="position:relative;z-index:1">
    <a class="btn bg-white text-primary fw-bold btn-sm" href="<?= url('index.php?page=kasir') ?>" style="border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-arrow-left me-1"></i>Kasir</a>
    <button class="btn bg-white bg-opacity-10 border border-white border-opacity-25 text-white btn-sm" type="button" onclick="window.print()" style="border-radius:12px;backdrop-filter:blur(8px)"><i class="bi bi-printer me-1"></i>Cetak</button>
  </div>
</div>
<style>
  /* Struk thermal 58mm - ditebelin untuk printer thermal */
  #strukPrint { max-width: 58mm; width: 58mm; margin: 0 auto; font-size: 11.5px; padding: 8px; font-weight: 700; color: #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  #strukPrint * { color: #000 !important; }
  #strukPrint table, #strukPrint div { font-weight: 700; }
  #strukPrint .fw-bold { font-weight: 800 !important; }
  .struk-divider { border: 0; border-top: 2px solid #000; margin: 7px 0; opacity: 1; }
  .struk-divider-dashed { border: 0; border-top: 1.5px dashed #000; margin: 7px 0; opacity: 1; }
  .struk-divider-double { border: 0; border-top: 3px double #000; margin: 7px 0; opacity: 1; height: 3px; }
  @media print {
    @page { size: 58mm auto; margin: 0; }
    html, body { width: 58mm; margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .kasir-hero, .no-print { display: none !important; }
    .tt-sidebar, .tt-topbar, .tt-bottomnav { display: none !important; }
    .tt-body { margin-left: 0 !important; }
    .tt-main { padding: 0 !important; }
    .row.justify-content-center { margin: 0 !important; display: block !important; }
    .row.justify-content-center > [class*="col-"] { max-width: 58mm !important; width: 58mm !important; padding: 0 !important; margin: 0 auto !important; flex: none !important; }
    #strukPrint { border: none !important; box-shadow: none !important; border-radius: 0 !important; width: 58mm !important; max-width: 58mm !important; }
    #strukPrint img { height: 48px !important; }
  }
</style>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="tt-card" id="strukPrint" style="font-family:'Courier New',monospace;font-size:11.5px;font-weight:700;color:#000">
      <div class="text-center">
        <img src="<?= url(APP_LOGO) ?>" alt="Logo" style="height:48px">
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:13px;color:#000">Tiket Kolam Renang Al Ma'soem</div>
        <hr class="struk-divider-double">
      </div>
      <table class="w-100">
        <tr><td>No. Transaksi</td><td class="text-end fw-bold"><?= e($t['number']) ?></td></tr>
        <tr><td>Tanggal</td><td class="text-end"><?= e(date('d/m/Y', strtotime($t['created_at']))) ?></td></tr>
        <tr><td>Kasir</td><td class="text-end"><?= e($t['kasir'] ?: '-') ?></td></tr>
      </table>
      <hr class="struk-divider-dashed">
      <?php foreach ($items as $it):
        $label = $it['item_name'];
        if (!empty($it['detail'])) $label .= ' (' . $it['detail'] . ')';
        $seri = '';
        if (!empty($it['serial_start'])) {
            $pref = $it['serial_prefix'] ?? '';
            $s = $pref . str_pad((string)$it['serial_start'],4,'0',STR_PAD_LEFT);
            $e = $pref . str_pad((string)$it['serial_end'],4,'0',STR_PAD_LEFT);
            $seri = $s === $e ? $s : $s.' - '.$e;
        }
      ?>
      <div><?= e($label) ?><?php if($seri): ?> <span style="font-size:10px">[#<?= e($seri) ?>]</span><?php endif; ?></div>
      <div class="d-flex"><span><?= rtrim(rtrim(number_format((float) $it['quantity'], 2, ',', '.'), '0'), ',') ?> x <?= number_format($it['unit_price'], 0, ',', '.') ?></span>
        <span class="ms-auto"><?= number_format($it['subtotal'], 0, ',', '.') ?></span></div>
      <?php endforeach; ?>
      <hr class="struk-divider-dashed">
      <div class="d-flex fw-bold" style="font-size:13px;font-weight:800"><span>TOTAL</span><span class="ms-auto">Rp<?= number_format($t['amount'], 0, ',', '.') ?></span></div>
      <?php if (($t['payment_method'] ?? '') === 'GABUNGAN'): ?>
      <?php foreach ([['pay_tunai', 'Tunai'], ['pay_qris', 'QRIS'], ['pay_transfer', 'Transfer']] as [$col, $lbl]): ?>
      <?php if ((float) ($t[$col] ?? 0) > 0): ?>
      <div class="d-flex"><span>- <?= $lbl ?></span><span class="ms-auto">Rp<?= number_format($t[$col], 0, ',', '.') ?></span></div>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php endif; ?>
      <div class="d-flex"><span>Bayar (<?= e($t['payment_method'] === 'TAGIHAN' ? 'Tagihan' : $t['payment_method']) ?>)</span><span class="ms-auto">Rp<?= number_format($t['paid_amount'], 0, ',', '.') ?></span></div>
      <div class="d-flex"><span>Kembalian</span><span class="ms-auto">Rp<?= number_format($t['change_amount'], 0, ',', '.') ?></span></div>
      <?php if ($t['payment_status'] === 'CANCELLED' && !empty($t['notes'])): ?>
      <?php
      $refundReason = '';
      if (preg_match('/\[REFUND[^:]*:\s*(.*?)\]/', $t['notes'], $matches)) {
        $refundReason = trim($matches[1]);
      }
      ?>
      <hr class="struk-divider-dashed">
      <div class="text-danger fw-bold" style="font-size:12px">STATUS: CANCELLED</div>
      <?php if ($refundReason): ?>
      <div class="text-danger" style="font-size:11px">Refund: <?= e($refundReason) ?></div>
      <?php endif; ?>
      <?php endif; ?>
      <hr class="struk-divider-double">
      <div class="text-center">
        <div id="qrStruk" class="d-inline-block bg-white p-1"></div>
        <div class="small mt-2" style="font-weight:800">terima kasih &amp; selamat berenang!</div>
      </div>
    </div>
    <small class="text-muted d-block text-center mt-1 no-print">Kertas thermal 58mm — gunakan setting kertas 58mm saat cetak.</small>
  </div>
</div>

<?php ob_start(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new QRCode(document.getElementById('qrStruk'), { text: '<?= e($t['number']) ?>', width: 90, height: 90 });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
