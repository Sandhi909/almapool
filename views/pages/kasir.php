<?php
$dow = (int) date('N');
$tarifAktif = ($dow === 7) ? 'B' : 'A';
$settings = $settings ?? [];
?>
<style>
.kasir-hero{background:linear-gradient(135deg,#0ea5e9 0%,#0284c7 45%,#1e3a8a 100%);border-radius:20px;color:#fff;position:relative;overflow:hidden;box-shadow:0 12px 32px rgba(2,132,199,.25)}
.kasir-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(600px 200px at 85% -10%,rgba(255,255,255,.18),transparent 60%)}
.katalog-grid .tiket-card{border-radius:18px;border:1px solid #e0f2fe;background:#fff;box-shadow:0 6px 18px rgba(14,165,233,.08);transition:.15s}
.katalog-grid .tiket-card:hover{box-shadow:0 10px 28px rgba(14,165,233,.16);transform:translateY(-1px);border-color:#bae6fd}
.qty-row{border-radius:12px;padding:8px 10px;display:flex;align-items:center;gap:8px}
.qty-row.putra{background:linear-gradient(135deg,#ecfeff,#e0f2fe);border:1px solid #bae6fd}
.qty-row.putri{background:linear-gradient(135deg,#fdf2f8,#fce7f3);border:1px solid #fbcfe8}
.qty-input{width:64px;height:34px;border-radius:10px;text-align:center;font-weight:800;border:1.5px solid #e2e8f0;background:#fff}
.qty-input:focus{border-color:#0ea5e9;box-shadow:0 0 0 3px rgba(14,165,233,.15)}
.cart-card{border-radius:20px;border:1px solid #e0f2fe;background:#fff;box-shadow:0 10px 28px rgba(14,165,233,.12)}
</style>

<div class="kasir-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white" style="width:48px;height:48px;color:#0ea5e9;box-shadow:0 4px 12px rgba(0,0,0,.12)"><i class="bi bi-calculator fs-4"></i></span>
    <div>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'"><?= e(auth_user()['name']) ?></h5>
      <small class="opacity-90"><?= hari_tanggal_indo(date('Y-m-d')) ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-3 flex-wrap align-items-center" style="position:relative;z-index:1">
    <div class="px-3 py-2 rounded-4 bg-white text-dark" style="min-width:140px;box-shadow:0 6px 16px rgba(0,0,0,.12)">
      <small class="text-muted d-block">Tarif</small>
      <div class="btn-group btn-group-sm w-100 mt-1" role="group" id="tarifGroup">
        <?php foreach (['A','B','C'] as $t): ?>
        <button type="button" class="btn <?= $t===$tarifAktif?'btn-tt':'btn-outline-secondary' ?>" data-tarif="<?= $t ?>" style="font-weight:800"><?= $t ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php if (!empty($EDIT)): ?>
<div class="alert d-flex flex-wrap align-items-center gap-3 mb-3" style="background:linear-gradient(135deg,#fefce8,#fef3c7);border:1.5px solid #fde68a;border-radius:14px">
  <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:40px;height:40px;background:#f59e0b;color:#fff"><i class="bi bi-pencil-square"></i></span>
  <div>
    <b>Edit Transaksi — <?= e($EDIT['trx']['number']) ?></b><br><small class="text-muted"><?= e(date('d/m/Y H:i',strtotime($EDIT['trx']['created_at']))) ?> • <?= rupiah($EDIT['trx']['amount']) ?></small>
  </div>
  <a class="btn btn-light btn-sm ms-auto" href="<?= url('index.php?page=kasir') ?>"><i class="bi bi-x-lg me-1"></i>Batal</a>
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-xl-7">
    <ul class="nav nav-pills gap-2 mb-3">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#kt-tiket" type="button"><i class="bi bi-ticket-perforated me-1"></i>Tiket</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#kt-member" type="button"><i class="bi bi-person-badge me-1"></i>Member</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#kt-les" type="button"><i class="bi bi-water me-1"></i>Les</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#kt-sewa" type="button"><i class="bi bi-tools me-1"></i>Sewa</button></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="kt-tiket">
        <div class="position-relative mb-3">
          <i class="bi bi-search position-absolute" style="left:14px;top:50%;transform:translateY(-50%);color:#0ea5e9"></i>
          <input class="form-control ps-5" id="cariItem" placeholder="Cari tiket..." style="border-radius:14px;height:44px;border:1.5px solid #bae6fd;background:#fff">
        </div>
        <div class="row g-3 katalog-grid" id="katalog">
          <?php foreach ($TIKET as $t): ?>
          <div class="col-md-6 katalog-item" data-nama="<?= e(strtolower($t['name'].' '.$t['color'])) ?>">
            <div class="tiket-card p-3 h-100 d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <span class="pill pill-blue mb-2 d-inline-flex"><?= e($t['color']) ?></span>
                  <h6 class="fw-bold mb-1" style="font-family:'Plus Jakarta Sans';line-height:1.2"><?= e($t['name']) ?></h6>
                  <small class="text-muted num">A <?= $t['price_a']?rupiah($t['price_a']):'-' ?> • B <?= $t['price_b']?rupiah($t['price_b']):'-' ?> • C <?= $t['price_c']?rupiah($t['price_c']):'-' ?></small>
                </div>
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width:40px;height:40px;background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff"><i class="bi bi-ticket-perforated"></i></span>
              </div>
              <div class="mt-3">
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2" style="background:#f0fdfa;border:1px dashed #a7f3d0">
                  <small class="text-muted">Harga <b><span class="tarif-lbl">Tarif <?= e($tarifAktif) ?></span></b></small>
                  <b class="num fs-5 price" style="color:#0369a1" data-a="<?= $t['price_a'] ?>" data-b="<?= $t['price_b'] ?>" data-c="<?= $t['price_c'] ?>"><?= $t['price_'.strtolower($tarifAktif)]?rupiah($t['price_'.strtolower($tarifAktif)]):'-' ?></b>
                </div>
                <div class="d-flex gap-2 mt-3 align-items-center flex-wrap p-2 rounded-3" id="flex-row-<?= $t['id'] ?>" style="background:#f8fafc;border:1.5px solid #e2e8f0">
                  <div class="d-flex flex-column gap-1" style="min-width:110px;flex:1">
                    <label class="small fw-bold mb-0" style="font-size:.70rem">Kolam</label>
                    <select class="form-select form-select-sm" id="area-flex-<?= $t['id'] ?>" style="height:38px"><option value="PUTRA">PUTRA</option><option value="PUTRI">PUTRI</option></select>
                  </div>
                  <div class="d-flex flex-column gap-1" style="min-width:80px;flex:1">
                    <label class="small fw-bold mb-0" style="font-size:.70rem">Qty</label>
                    <input type="number" min="1" value="1" class="form-control form-control-sm qty-flex-input" id="qty-flex-<?= $t['id'] ?>" data-ref="<?= $t['id'] ?>" style="height:38px;text-align:center">
                  </div>
                  <div class="d-flex flex-column gap-1 justify-content-end" style="min-width:120px;flex:1.5">
                    <label class="small fw-bold mb-0" style="font-size:.70rem;visibility:hidden">Aksi</label>
                    <button class="btn btn-tt btn-sm" type="button" data-flex-add data-ref="<?= $t['id'] ?>" data-nama="Tiket <?= e($t['name']) ?>" style="height:38px"><i class="bi bi-cart-plus me-1"></i>Tambah</button>
                  </div>
                </div>
                <?php $hasFlex = !empty($t['require_jenjang']) || !empty($t['require_usia']??0) || !empty($t['require_nama']); $flexShow = $hasFlex ? '' : 'display:none;'; ?>
                <?php if (!empty($t['require_jenjang'])): ?>
                <div class="rombongan-extra mt-2 p-2 rounded-3" data-tiket-id="<?= $t['id'] ?>" data-require="jenjang" style="<?= $flexShow ?>background:#f0f9ff;border:1.5px solid #bae6fd">
                  <label class="form-label small fw-bold mb-1"><i class="bi bi-mortarboard me-1"></i><?= e($t['name']) ?> — Jenjang</label>
                  <select class="form-select form-select-sm rombongan-jenjang-input" data-ref="<?= $t['id'] ?>" id="rombongan-jenjang-<?= $t['id'] ?>">
                    <option value="">Pilih jenjang</option>
                    <?php foreach (($JENJANG_MASTER ?? []) as $jm): ?><option value="<?= e($jm['name']) ?>"><?= e($jm['name']) ?></option><?php endforeach; ?>
                  </select>
                  <small class="text-muted" style="font-size:.7rem">Wajib pilih</small>
                </div>
                <?php endif; ?>
                <?php if (!empty($t['require_usia'] ?? 0)): ?>
                <div class="rombongan-extra mt-2 p-2 rounded-3" data-tiket-id="<?= $t['id'] ?>" data-require="usia" style="<?= $flexShow ?>background:#f0fdfa;border:1.5px solid #a7f3d0">
                  <label class="form-label small fw-bold mb-1"><i class="bi bi-person me-1"></i><?= e($t['name']) ?> — Kelompok Usia</label>
                  <select class="form-select form-select-sm rombongan-usia-input" data-ref="<?= $t['id'] ?>" id="rombongan-usia-<?= $t['id'] ?>">
                    <option value="">Pilih kelompok usia</option>
                    <?php foreach (($USIA_MASTER ?? []) as $um): ?><option value="<?= e($um['name']) ?>"><?= e($um['name']) ?><?= $um['min_age']!==null?' ('.$um['min_age'].'-'.$um['max_age'].' th)':'' ?></option><?php endforeach; ?>
                  </select>
                  <small class="text-muted" style="font-size:.7rem">Wajib pilih</small>
                </div>
                <?php endif; ?>
                <?php if (!empty($t['require_nama'])): ?>
                <div class="rombongan-extra mt-2 p-2 rounded-3" data-tiket-id="<?= $t['id'] ?>" data-require="nama" style="<?= $flexShow ?>background:#fff7ed;border:1.5px solid #fed7aa">
                  <label class="form-label small fw-bold mb-1"><i class="bi bi-people me-1"></i><?= e($t['name']) ?> — Rombongan Eksternal</label>
                  <select class="form-select form-select-sm rombongan-nama-input" data-ref="<?= $t['id'] ?>" id="rombongan-nama-<?= $t['id'] ?>">
                    <option value="">Pilih rombongan</option>
                    <?php foreach (($ROMBONGAN_MASTER ?? []) as $rm): ?><option value="<?= e($rm['name']) ?>"><?= e($rm['name']) ?></option><?php endforeach; ?>
                    <option value="__custom">Lainnya (isi manual)</option>
                  </select>
                  <input type="text" class="form-control form-control-sm mt-2 d-none rombongan-nama-custom" data-ref="<?= $t['id'] ?>" id="rombongan-nama-custom-<?= $t['id'] ?>" placeholder="Isi nama rombongan manual">
                  <small class="text-muted" style="font-size:.7rem">Wajib pilih</small>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="tab-pane fade" id="kt-member">
        <div class="card-pool p-3 mb-3" style="border:1.5px solid #e0f2fe">
          <div class="row g-2 align-items-end">
            <div class="col-md-7"><label class="form-label fw-semibold small">Member</label>
              <input class="form-control" id="memberSearch" placeholder="Cari member..." autocomplete="off" style="border-radius:12px;height:42px">
              <div class="list-group mt-2" id="memberResults" style="display:none;max-height:220px;overflow:auto"></div></div>
            <div class="col-md-5"><label class="form-label fw-semibold small">Kelas</label>
              <div class="btn-group w-100" role="group" id="kelasGroup">
                <button type="button" class="btn btn-tt" data-kelas="Gold">Gold</button>
                <button type="button" class="btn btn-outline-secondary" data-kelas="Silver">Silver</button>
              </div></div>
          </div>
          <small class="text-muted d-block mt-2" id="memberPicked">Belum ada member dipilih.</small>
        </div>
        <div class="row g-3">
          <?php foreach ($MEMBER_PAKET as $jenis => $tiers): ?>
          <div class="col-md-6">
            <div class="tiket-card p-3 h-100">
              <small class="text-primary fw-bold">• Member</small>
              <h6 class="fw-bold mb-1"><?= e($jenis) ?></h6>
              <small class="text-muted num">Gold <?= rupiah($tiers['GOLD']['price']) ?> • Silver <?= rupiah($tiers['SILVER']['price']) ?></small>
              <div class="d-flex align-items-center justify-content-between mt-3 p-2 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                <div><small class="text-muted">Harga <b><span class="kelas-lbl">Gold</span></b></small>
                  <b class="num fs-5 mprice" data-gold="<?= $tiers['GOLD']['price'] ?>" data-silver="<?= $tiers['SILVER']['price'] ?>" data-goldref="<?= $tiers['GOLD']['id'] ?>" data-silverref="<?= $tiers['SILVER']['id'] ?>"><?= rupiah($tiers['GOLD']['price']) ?></b></div>
                <div class="d-flex align-items-center gap-2">
                  <button class="qty-btn" type="button" data-act="min" data-kind="member" data-key="member-<?= e($jenis) ?>" data-nama="Member <?= e($jenis) ?>">−</button>
                  <span class="qty-val num" id="qty-member-<?= e($jenis) ?>">0</span>
                  <button class="qty-btn plus" type="button" data-act="plus" data-kind="member" data-key="member-<?= e($jenis) ?>" data-nama="Member <?= e($jenis) ?>">+</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="tab-pane fade" id="kt-les">
        <div class="card-pool p-3 mb-3">
          <label class="form-label fw-semibold small">Peserta les (opsional)</label>
          <input class="form-control" id="studentSearch" placeholder="Cari peserta..." autocomplete="off" style="border-radius:12px;height:42px">
          <div class="list-group mt-2" id="studentResults" style="display:none;max-height:220px;overflow:auto"></div>
          <small class="text-muted d-block mt-2" id="studentPicked">Walk-in</small>
        </div>
        <div class="row g-3">
          <?php foreach ($LES_PAKET as $p): ?>
          <div class="col-md-6">
            <div class="tiket-card p-3">
              <small class="text-primary fw-bold">• Les <?= e($p['name']) ?> — <?= rupiah($p['price']) ?>/sesi</small>
              <h6 class="fw-bold mt-1">Les <?= e($p['name']) ?></h6>
              <?php foreach (['Putra','Putri'] as $gp): $k='les-'.$p['id'].'-'.strtolower($gp); ?>
              <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div><b style="font-size:.9rem"><?= $gp ?></b><br><small class="text-muted num"><?= rupiah($p['price']) ?></small></div>
                <div class="d-flex align-items-center gap-2">
                  <button class="qty-btn" type="button" data-act="min" data-kind="les" data-ref="<?= $p['id'] ?>" data-detail="<?= $gp ?>" data-key="<?= $k ?>" data-nama="Les <?= e($p['name']) ?> <?= $gp ?>" data-fix="<?= $p['price'] ?>">−</button>
                  <span class="qty-val num" id="qty-<?= $k ?>">0</span>
                  <button class="qty-btn plus" type="button" data-act="plus" data-kind="les" data-ref="<?= $p['id'] ?>" data-detail="<?= $gp ?>" data-key="<?= $k ?>" data-nama="Les <?= e($p['name']) ?> <?= $gp ?>" data-fix="<?= $p['price'] ?>">+</button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="tab-pane fade" id="kt-sewa">
        <div class="tiket-card p-3 d-flex gap-3 align-items-center flex-wrap">
          <div class="flex-fill"><h6 class="fw-bold mb-1"><?= e($ALAT_SEWA_NAMA) ?></h6></div>
          <div class="input-group" style="max-width:200px"><span class="input-group-text bg-white">Rp</span><input class="form-control num" id="sewaNominal" inputmode="numeric" placeholder="Nominal" style="height:42px"></div>
          <button class="btn btn-tt" type="button" id="btnSewaAdd">Tambah</button>
        </div>
      </div>
    </div>

    <div class="card-pool p-3 mt-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b>Transaksi Hari Ini</b><span class="ms-auto badge bg-light border text-muted"><?= count($TRANSAKSI) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table tt-table align-middle mb-0">
          <thead><tr><th>No.</th><th>Waktu</th><th>Jenis</th><th>Kolam</th><th>Total</th><th>Metode</th><th></th></tr></thead>
          <tbody>
            <?php foreach (array_slice($TRANSAKSI,0,5) as $tr): 
              $canRefundK = $tr['payment_status']==='PAID' && $tr['type']==='TIKET' && (time()-strtotime($tr['created_at']) < 24*3600);
            ?>
            <tr>
              <td class="num fw-bold" style="color:#0284c7"><?= e($tr['number']) ?></td>
              <td class="num"><?= e(date('H:i',strtotime($tr['created_at']))) ?></td>
              <td><span class="pill pill-blue"><?= e($tr['type']) ?></span></td>
              <td><span class="pill <?= $tr['area']==='PUTRI'?'pill-amber':'pill-blue' ?>"><?= e($tr['area']==='CAMPURAN'?'Putra, Putri':$tr['area']) ?></span></td>
              <td class="num fw-bold"><?= rupiah($tr['amount']) ?></td>
              <td><span class="pill <?= $tr['payment_method']==='TUNAI'?'pill-grey':'pill-green' ?>" title="<?= e(Transaction::payLabel($tr)) ?>"><?= e($tr['payment_method']) ?></span></td>
              <td class="text-nowrap">
                <div class="btn-group btn-group-sm" role="group">
                  <a class="btn btn-light border" target="_blank" href="<?= url('index.php?page=struk&id='.(int)$tr['id']) ?>" title="Cetak"><i class="bi bi-printer"></i></a>
                  <?php if(!empty($tr['notes'])): ?>
                  <button class="btn btn-light border" type="button" data-bs-toggle="modal" data-bs-target="#notesModalK<?= (int)$tr['id'] ?>" title="Catatan"><i class="bi bi-file-text"></i></button>
                  <?php endif; ?>
                  <?php if($tr['payment_status']==='CANCELLED'): ?>
                    <span class="btn btn-light border disabled" title="Refunded"><i class="bi bi-x-circle text-danger"></i></span>
                  <?php elseif($canRefundK): ?>
                    <a class="btn btn-outline-primary" href="<?= url('index.php?page=kasir&edit='.(int)$tr['id']) ?>" title="Edit"><i class="bi bi-pencil-square"></i></a>
                    <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#refundModalK<?= (int)$tr['id'] ?>" title="Refund"><i class="bi bi-arrow-counterclockwise"></i></button>
                  <?php else: ?>
                    <button class="btn btn-outline-secondary" type="button" disabled title="Lewat 24 jam"><i class="bi bi-pencil-square"></i></button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(!$TRANSAKSI): ?><tr><td colspan="7" class="text-center text-muted py-3">Belum ada transaksi hari ini.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php foreach (array_slice($TRANSAKSI,0,5) as $tr): if($tr['payment_status']!=='PAID' || $tr['type']!=='TIKET' || (time()-strtotime($tr['created_at'])>=24*3600)) continue; ?>
<div class="modal fade" id="refundModalK<?= (int)$tr['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" style="border-radius:20px" method="post" action="<?= url('index.php?page=transaksi') ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="refund"><input type="hidden" name="id" value="<?= (int)$tr['id'] ?>">
      <div class="modal-header"><h5 class="modal-title fw-bold">Refund — <?= e($tr['number']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <label class="form-label fw-semibold">Keterangan *</label>
        <textarea class="form-control" name="keterangan" rows="3" required placeholder="Alasan..."></textarea>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" type="submit">Refund</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php foreach (array_slice($TRANSAKSI,0,5) as $tr): if (empty($tr['notes'])) continue; ?>
<div class="modal fade" id="notesModalK<?= (int)$tr['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px">
      <div class="modal-header"><h5 class="modal-title fw-bold">Catatan — <?= e($tr['number']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="bg-light p-3 rounded-3" style="font-family:monospace;font-size:13px;white-space:pre-wrap"><?= e($tr['notes']) ?></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
    </div>
  </div>
</div>
<?php endforeach; ?>

  <div class="col-xl-5">
    <div class="cart-card p-3 position-sticky" style="top:84px">
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff"><i class="bi bi-cart3"></i></span>
        <h6 class="fw-bold mb-0" style="font-family:'Plus Jakarta Sans'">Keranjang</h6>
        <button class="btn btn-link btn-sm text-danger ms-auto text-decoration-none" type="button" id="btnResetCart"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
      </div>
      <div id="cartEmpty" class="text-center py-4 rounded-4 mb-3" style="background:linear-gradient(135deg,#f0fdfa,#ecfeff);border:1.5px dashed #bae6fd">
        <i class="bi bi-cart-plus fs-1 d-block mb-2" style="color:#7dd3fc"></i>
        <b class="small">Keranjang kosong</b>
      </div>
      <div id="cartList" class="d-grid gap-2 mb-3"></div>
      <div class="rounded-4 p-3 mb-3" style="background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff;box-shadow:0 8px 20px rgba(37,99,235,.25)">
        <div class="d-flex justify-content-between small opacity-90"><span>Total Item</span><b class="num" id="cartCount">0 item</b></div>
        <div class="d-flex justify-content-between small opacity-90"><span>Kolam</span><b id="cartArea">—</b></div>
        <hr style="border-color:rgba(255,255,255,.25);margin:10px 0">
        <div class="d-flex justify-content-between align-items-center"><span class="small opacity-90">Total Bayar</span><b class="num fs-3" id="cartTotal" style="letter-spacing:-.5px">Rp0</b></div>
      </div>
      <b class="small">Metode Pembayaran</b>
      <div class="row g-2 my-2">
        <div class="col-3"><div class="pay-opt active" data-pay="TUNAI"><i class="bi bi-cash-stack fs-4 d-block"></i><b style="font-size:.8rem">Tunai</b></div></div>
        <div class="col-3"><div class="pay-opt" data-pay="QRIS"><i class="bi bi-qr-code fs-4 d-block"></i><b style="font-size:.8rem">QRIS</b></div></div>
        <div class="col-3"><div class="pay-opt" data-pay="TRANSFER"><i class="bi bi-bank fs-4 d-block"></i><b style="font-size:.8rem">Transfer</b></div></div>
        <div class="col-3"><div class="pay-opt" data-pay="GABUNGAN"><i class="bi bi-pie-chart fs-4 d-block"></i><b style="font-size:.8rem">Gabungan</b></div></div>
      </div>
      <div id="splitBox" class="rounded-4 p-3 mb-2 d-none" style="background:#f0fdfa;border:1.5px dashed #6ee7b7">
        <b class="small"><i class="bi bi-pie-chart me-1"></i>Rincian Gabungan <small class="text-muted fw-normal">— jumlah harus pas total</small></b>
        <div class="input-group input-group-sm mt-2 mb-2"><span class="input-group-text bg-white" style="min-width:86px">Tunai</span><span class="input-group-text bg-white">Rp</span><input class="form-control num split-input" id="splitTunai" inputmode="numeric" placeholder="0"></div>
        <div class="input-group input-group-sm mb-2"><span class="input-group-text bg-white" style="min-width:86px">QRIS</span><span class="input-group-text bg-white">Rp</span><input class="form-control num split-input" id="splitQris" inputmode="numeric" placeholder="0"></div>
        <div class="input-group input-group-sm mb-2"><span class="input-group-text bg-white" style="min-width:86px">Transfer</span><span class="input-group-text bg-white">Rp</span><input class="form-control num split-input" id="splitTransfer" inputmode="numeric" placeholder="0"></div>
        <div class="d-flex justify-content-between align-items-center small"><span class="text-muted">Sisa</span><b class="num" id="splitSisa">Rp0</b></div>
      </div>
      <div id="singleCashBox">
      <div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-2" style="background:#f0fdfa;border:1px solid #a7f3d0">
        <small class="text-muted">Uang diterima</small><b class="num text-success" id="lblKembali">Kembalian: Rp0</b>
      </div>
      <div class="d-flex gap-2 mb-2">
        <button class="btn btn-light btn-sm flex-fill" type="button" data-cash="pas" style="border-radius:10px">Uang Pas</button>
        <button class="btn btn-light btn-sm flex-fill" type="button" data-cash="100000" style="border-radius:10px">100rb</button>
        <button class="btn btn-light btn-sm flex-fill" type="button" data-cash="200000" style="border-radius:10px">200rb</button>
      </div>
      <div class="input-group mb-3"><span class="input-group-text bg-white">Rp</span><input class="form-control num" id="cashInput" inputmode="numeric" placeholder="Nominal" style="height:44px;border-radius:0 12px 12px 0"></div>
      </div>
      <button class="btn btn-tt btn-lg w-100 fw-bold" type="button" id="btnBayar" style="border-radius:14px;padding:12px;box-shadow:0 8px 20px rgba(37,99,235,.3)">BAYAR</button>
    </div>
  </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const cart = {};
  let tarif = '<?= e($tarifAktif) ?>';
  let kelas = 'Gold';
  let memberId = 0;
  let studentId = 0;
  const editData = <?= !empty($EDIT) ? json_encode($EDIT, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) : 'null' ?>;
  if(editData){ tarif = editData.tarif || tarif; }
  const fmt = n => 'Rp' + n.toLocaleString('id-ID');
  const $ = id => document.getElementById(id);

  function priceOf(el) {
    if (el.dataset.fix) return +el.dataset.fix;
    const card = el.closest('.tiket-card');
    const mp = card.querySelector('.mprice');
    if (mp) {
      const k = kelas === 'Gold' ? 'gold' : 'silver';
      el.dataset.ref = mp.dataset[k + 'ref'];
      return +mp.dataset[k];
    }
    const p = card.querySelector('.price');
    return +(p.dataset[tarif.toLowerCase()] || 0);
  }
  function labelOf(el) {
    const card = el.closest('.tiket-card');
    if (el.dataset.fix) return '';
    if (card.querySelector('.mprice')) return ' (' + kelas + ')';
    return ' (Tarif ' + tarif + ')';
  }
  function syncRombonganExtra(){
    document.querySelectorAll('.rombongan-extra').forEach(el=>{
      const ref = el.dataset.tiketId;
      if(document.getElementById('flex-row-'+ref)){ el.style.display=''; return; }
      const q1 = parseInt(($('qty-tiket-'+ref+'-putra')||{}).value,10)||0;
      const q2 = parseInt(($('qty-tiket-'+ref+'-putri')||{}).value,10)||0;
      el.style.display = (q1+q2)>0 ? '' : 'none';
    });
  }
  function syncInput(key, qty){
    const inp = document.getElementById('qty-' + CSS.escape(key));
    if(!inp) return;
    if(inp.tagName === 'INPUT') inp.value = qty || 0;
    else inp.textContent = qty;
  }
  function setCartQty(key, el, qty){
    qty = Math.max(0, parseInt(qty,10) || 0);
    const harga = priceOf(el);
    if (qty>0 && harga <= 0 && el.dataset.kind==='tiket') {
      Swal.fire({ icon: 'warning', title: 'Tarif tidak berlaku', text: 'Tiket ini tidak tersedia pada Tarif ' + tarif + '.' });
      syncInput(key, cart[key]?cart[key].qty:0);
      return;
    }
    if (!cart[key]) cart[key] = { kind: el.dataset.kind, ref: el.dataset.ref || 0, detail: el.dataset.detail || '', area: el.dataset.area || '', nama: el.dataset.nama + labelOf(el), harga: harga, qty: 0, jenjang:'', usia:'', rombongan:'' };
    cart[key].harga = harga;
    cart[key].ref = el.dataset.ref || cart[key].ref;
    cart[key].area = el.dataset.area || '';
    if (el.dataset.kind === 'tiket') {
      const ref = el.dataset.ref;
      const jEl = document.getElementById('rombongan-jenjang-'+ref);
      const uEl = document.getElementById('rombongan-usia-'+ref);
      const nEl = document.getElementById('rombongan-nama-'+ref);
      cart[key].jenjang = jEl ? jEl.value.trim() : '';
      cart[key].usia = uEl ? uEl.value.trim() : '';
      cart[key].rombongan = nEl ? rombonganNamaValue(nEl) : '';
    }
    if (el.dataset.kind === 'member') {
      if (!memberId) { Swal.fire({ icon: 'warning', title: 'Pilih member dulu', text: 'Wajib pilih member.' }); syncInput(key,0); return; }
      cart[key].nama = el.dataset.nama + ' (' + kelas + ')';
    }
    cart[key].qty = qty;
    syncInput(key, qty);
    syncRombonganExtra();
    render();
  }
  function render() {
    const list = $('cartList');
    const ids = Object.keys(cart).filter(k => cart[k].qty > 0);
    $('cartEmpty').style.display = ids.length ? 'none' : '';
    list.innerHTML = '';
    let total = 0, count = 0;
    const areas = new Set();
    ids.forEach(k => {
      const it = cart[k];
      total += it.qty * it.harga; count += it.qty;
      if(it.area) areas.add(it.area);
      const areaBadge = it.area ? '<span class="badge '+(it.area==='PUTRI'?'bg-white border text-danger':'bg-primary')+'" style="font-size:.62rem">'+it.area+'</span> ' : '';
      const extraBadges = [];
      if(it.jenjang) extraBadges.push('<span class="badge bg-info" style="font-size:.62rem">'+it.jenjang+'</span>');
      if(it.usia) extraBadges.push('<span class="badge bg-success" style="font-size:.62rem">'+it.usia+'</span>');
      if(it.rombongan) extraBadges.push('<span class="badge bg-warning text-dark" style="font-size:.62rem">'+it.rombongan+'</span>');
      const row = document.createElement('div');
      row.className = 'd-flex align-items-center gap-2 p-2 rounded-3';
      row.style.background = '#f0f9ff';
      row.style.border = '1px solid #e0f2fe';
      row.innerHTML = '<div class="flex-fill">'+areaBadge+' '+(extraBadges.join(' '))+'<br><b style="font-size:.85rem">' + it.nama + '</b><br><small class="text-muted">' + fmt(it.harga) + ' × ' + it.qty + '</small></div>'
        + '<b class="num" style="color:#0ea5e9">' + fmt(it.qty * it.harga) + '</b><button class="btn btn-sm btn-outline-danger ms-1" data-del="'+k+'" title="Hapus"><i class="bi bi-x-lg"></i></button>';
      list.appendChild(row);
    });
    list.querySelectorAll('[data-del]').forEach(b=>b.addEventListener('click',()=>{ delete cart[b.dataset.del]; render(); }));
    $('cartCount').textContent = count + ' item';
    let areaLabel = '—';
    if(areas.size===1) areaLabel=[...areas][0];
    else if(areas.size>1) areaLabel='CAMPURAN';
    $('cartArea').textContent = areaLabel;
    $('cartTotal').textContent = fmt(total);
    $('cartTotal').dataset.value = total;
    hitungKembali();
  }
  // prefill jika mode edit transaksi
  if(editData && editData.items){
    editData.items.forEach(it=>{
      // mapping: hanya tiket yang auto-isi, member/les diabaikan (edit tiket saja sesuai permintaan)
      if((it.item_type||'').toUpperCase()==='TIKET'){
        const area = (it.detail==='PUTRI'?'PUTRI':'PUTRA');
        const key = 'tiket-'+it.item_ref_id+'-'+area.toLowerCase();
        const el = document.querySelector('[data-key=\"'+key+'\"]');
        if(el){
          const qty = parseInt(it.quantity,10)||0;
          cart[key]={kind:'tiket',ref:it.item_ref_id,detail:'',area:area,nama:it.item_name+' ('+area+') ('+'Tarif '+(editData.tarif||tarif)+')',harga:parseFloat(it.unit_price),qty:qty};
          const inp=document.getElementById('qty-'+key);
          if(inp) inp.value=qty;
        }
      }
    });
    // ubah tombol bayar
    const btnBayarEl=document.getElementById('btnBayar');
    if(btnBayarEl) btnBayarEl.innerHTML='<i class=\"bi bi-save me-2\"></i>SIMPAN PERUBAHAN';
  }

  document.querySelectorAll('.qty-btn').forEach(b => b.addEventListener('click', function () {
    const key = this.dataset.key;
    const cur = cart[key] ? cart[key].qty : (parseInt((document.getElementById('qty-'+CSS.escape(key))||{}).value,10)||0);
    setCartQty(key, this, cur + (this.dataset.act === 'plus' ? 1 : -1));
  }));
  document.querySelectorAll('.qty-input').forEach(inp => {
    inp.addEventListener('input', function(){ setCartQty(this.dataset.key, this, this.value); });
    inp.addEventListener('change', function(){ setCartQty(this.dataset.key, this, this.value); });
    inp.addEventListener('focus', function(){ this.select(); });
  });
  // Flexible tambah per tiket (1 transaksi bisa beda jenjang/usia/rombongan per tiket)
  document.querySelectorAll('[data-flex-add]').forEach(btn=>{
    btn.addEventListener('click', function(){
      const ref = this.dataset.ref;
      const nama = this.dataset.nama;
      const card = this.closest('.tiket-card');
      const priceEl = card.querySelector('.price');
      const harga = +(priceEl.dataset[tarif.toLowerCase()]||0);
      if(harga<=0){ Swal.fire({icon:'warning',title:'Tarif tidak berlaku',text:'Tiket tidak tersedia pada Tarif '+tarif}); return; }
      const areaSel = document.getElementById('area-flex-'+ref);
      const area = areaSel ? areaSel.value : 'PUTRA';
      const qtyIn = document.getElementById('qty-flex-'+ref);
      let qty = parseInt(qtyIn ? qtyIn.value : 1,10)||1; if(qty<1) qty=1;
      const jEl = document.getElementById('rombongan-jenjang-'+ref);
      const uEl = document.getElementById('rombongan-usia-'+ref);
      const nEl = document.getElementById('rombongan-nama-'+ref);
      const jenjang = jEl ? jEl.value.trim() : '';
      const usia = uEl ? uEl.value.trim() : '';
      const rombongan = nEl ? rombonganNamaValue(nEl) : '';
      // validasi required
      const needJenjang = !!card.querySelector('.rombongan-jenjang-input');
      const needUsia = !!card.querySelector('.rombongan-usia-input');
      const needNama = !!card.querySelector('.rombongan-nama-input');
      if(needJenjang && !jenjang){ Swal.fire({icon:'warning',title:'Jenjang wajib dipilih'}); if(jEl) jEl.focus(); return; }
      if(needUsia && !usia){ Swal.fire({icon:'warning',title:'Kelompok usia wajib dipilih'}); if(uEl) uEl.focus(); return; }
      if(needNama && !rombongan){ Swal.fire({icon:'warning',title:'Rombongan wajib dipilih'}); if(nEl) nEl.focus(); return; }
      const key = 'tiket-flex-'+ref+'-'+area+'-'+(jenjang||'-')+'-'+(usia||'-')+'-'+(rombongan||'-')+'-'+Date.now();
      let dispNama = nama+' ('+area+') (Tarif '+tarif+')';
      if(jenjang) dispNama+=' - '+jenjang;
      if(usia) dispNama+=' - '+usia;
      if(rombongan) dispNama+=' - '+rombongan;
      cart[key]={kind:'tiket',ref:ref,area:area,qty:qty,harga:harga,nama:dispNama,jenjang:jenjang,usia:usia,rombongan:rombongan,detail:area};
      render();
      Swal.fire({icon:'success',title:'Ditambahkan',text:dispNama+' x'+qty, timer:1200, showConfirmButton:false});
    });
  });

  document.querySelectorAll('#tarifGroup [data-tarif]').forEach(b => b.addEventListener('click', function () {
    tarif = this.dataset.tarif;
    document.querySelectorAll('#tarifGroup .btn').forEach(x => { x.classList.remove('btn-tt'); x.classList.add('btn-outline-secondary'); });
    this.classList.remove('btn-outline-secondary'); this.classList.add('btn-tt');
    document.querySelectorAll('.tarif-lbl').forEach(el => el.textContent = 'Tarif ' + tarif);
    document.querySelectorAll('.price').forEach(p => {
      const v = +(p.dataset[tarif.toLowerCase()] || 0);
      p.textContent = v ? fmt(v) : '-';
    });
    Object.keys(cart).forEach(k => {
      if (cart[k].kind === 'tiket') {
        const btn = document.querySelector('[data-key="' + k + '"][data-act="plus"]');
        if (btn) { cart[k].harga = priceOf(btn); cart[k].nama = btn.dataset.nama + labelOf(btn); }
      }
    });
    render();
  }));

  document.querySelectorAll('#kelasGroup [data-kelas]').forEach(b => b.addEventListener('click', function () {
    kelas = this.dataset.kelas;
    document.querySelectorAll('#kelasGroup .btn').forEach(x => { x.classList.remove('btn-tt'); x.classList.add('btn-outline-secondary'); });
    this.classList.remove('btn-outline-secondary'); this.classList.add('btn-tt');
    document.querySelectorAll('.kelas-lbl').forEach(el => el.textContent = kelas);
    const k = kelas === 'Gold' ? 'gold' : 'silver';
    document.querySelectorAll('.mprice').forEach(p => { p.textContent = fmt(+p.dataset[k]); });
    Object.keys(cart).forEach(k2 => {
      if (cart[k2].kind === 'member') {
        const btn = document.querySelector('[data-key="' + k2 + '"][data-act="plus"]');
        if (btn) { cart[k2].harga = priceOf(btn); cart[k2].nama = btn.dataset.nama + ' (' + kelas + ')'; }
      }
    });
    render();
  }));


  let mTimer = null;
  $('memberSearch').addEventListener('input', function () {
    clearTimeout(mTimer);
    const box = $('memberResults');
    mTimer = setTimeout(function () {
      const q = $('memberSearch').value.trim();
      if (q.length < 2) { box.style.display = 'none'; return; }
      fetch('<?= url('index.php?page=member&ajax=search') ?>&q=' + encodeURIComponent(q))
        .then(r => r.json()).then(d => {
          box.innerHTML = '';
          (d.results || []).forEach(r => {
            const a = document.createElement('button');
            a.type = 'button'; a.className = 'list-group-item list-group-item-action';
            a.innerHTML = '<b>' + r.nomor + '</b> — ' + r.nama + ' <small class="text-muted">' + r.paket + ' (s/d ' + r.berakhir + ')</small>';
            a.addEventListener('click', function () {
              memberId = r.id;
              $('memberPicked').innerHTML = 'Member: <b>' + r.nomor + ' — ' + r.nama + '</b>';
              $('memberSearch').value = r.nomor;
              box.style.display = 'none';
            });
            box.appendChild(a);
          });
          box.style.display = (d.results || []).length ? '' : 'none';
        });
    }, 300);
  });
  let sTimer = null;
  $('studentSearch').addEventListener('input', function () {
    clearTimeout(sTimer);
    const box = $('studentResults');
    sTimer = setTimeout(function () {
      const q = $('studentSearch').value.trim();
      if (q.length < 2) { box.style.display = 'none'; return; }
      fetch('<?= url('index.php?page=les&ajax=students') ?>&q=' + encodeURIComponent(q))
        .then(r => r.json()).then(d => {
          box.innerHTML = '';
          (d.results || []).forEach(r => {
            const a = document.createElement('button');
            a.type = 'button'; a.className = 'list-group-item list-group-item-action';
            a.innerHTML = '<b>' + r.name + '</b>';
            a.addEventListener('click', function () {
              studentId = r.id;
              $('studentPicked').innerHTML = 'Peserta: <b>' + r.name + '</b>';
              $('studentSearch').value = r.name;
              box.style.display = 'none';
            });
            box.appendChild(a);
          });
          box.style.display = (d.results || []).length ? '' : 'none';
        });
    }, 300);
  });
  $('btnSewaAdd').addEventListener('click', function () {
    const v = +($('sewaNominal').value.replace(/[^0-9]/g, '') || 0);
    if (!v) { Swal.fire({ icon: 'warning', title: 'Nominal kosong', text: 'Isi nominal dulu.' }); return; }
    cart['sewa-' + Date.now()] = { kind: 'sewa', ref: 0, nama: 'Alat Sewa', harga: v, qty: 1 };
    $('sewaNominal').value = '';
    render();
  });
  let payMethod = 'TUNAI';
  document.querySelectorAll('.pay-opt').forEach(p => p.addEventListener('click', function () {
    document.querySelectorAll('.pay-opt').forEach(x => x.classList.remove('active'));
    this.classList.add('active');
    payMethod = this.dataset.pay;
    const split = payMethod === 'GABUNGAN';
    $('splitBox').classList.toggle('d-none', !split);
    $('singleCashBox').classList.toggle('d-none', split);
    hitungKembali();
  }));
  function splitVals() {
    return {
      TUNAI: +($('splitTunai').value.replace(/[^0-9]/g, '') || 0),
      QRIS: +($('splitQris').value.replace(/[^0-9]/g, '') || 0),
      TRANSFER: +($('splitTransfer').value.replace(/[^0-9]/g, '') || 0)
    };
  }
  function hitungSisa() {
    const total = +($('cartTotal').dataset.value || 0);
    const s = splitVals();
    const sisa = total - (s.TUNAI + s.QRIS + s.TRANSFER);
    const el = $('splitSisa');
    el.textContent = (sisa === 0 ? 'Pas ' : (sisa > 0 ? 'Kurang ' : 'Lebih ')) + fmt(Math.abs(sisa));
    el.style.color = sisa === 0 ? '#15803d' : '#b91c1c';
  }
  let cash = 0;
  function hitungKembali() {
    if (payMethod === 'GABUNGAN') { hitungSisa(); return; }
    const total = +($('cartTotal').dataset.value || 0);
    $('lblKembali').textContent = 'Kembalian: ' + fmt(cash ? Math.max(0, cash - total) : 0);
  }
  document.querySelectorAll('.split-input').forEach(i => i.addEventListener('input', hitungSisa));
  document.querySelectorAll('[data-cash]').forEach(b => b.addEventListener('click', function () {
    const total = +($('cartTotal').dataset.value || 0);
    cash = this.dataset.cash === 'pas' ? total : (+this.dataset.cash);
    $('cashInput').value = cash ? cash.toLocaleString('id-ID') : '';
    hitungKembali();
  }));
  $('cashInput').addEventListener('input', function () {
    cash = +(this.value.replace(/[^0-9]/g, '') || 0);
    hitungKembali();
  });
  // custom rombongan select -> show text input if Lainnya
  document.querySelectorAll('.rombongan-nama-input').forEach(sel=>{
    sel.addEventListener('change', function(){
      const custom = document.getElementById('rombongan-nama-custom-'+this.dataset.ref);
      if(!custom) return;
      if(this.value==='__custom'){ custom.classList.remove('d-none'); custom.focus(); }
      else { custom.classList.add('d-none'); custom.value=''; }
    });
  });
  function rombonganNamaValue(el){
    if(el.value==='__custom'){
      const c=document.getElementById('rombongan-nama-custom-'+el.dataset.ref);
      return c ? c.value.trim() : '';
    }
    return (el.value||'').trim();
  }
  function resetCart() {
    Object.keys(cart).forEach(k => delete cart[k]);
    document.querySelectorAll('.qty-input').forEach(el=>el.value='0');
    document.querySelectorAll('.qty-val').forEach(el=>el.textContent='0');
    document.querySelectorAll('.qty-flex-input').forEach(el=>el.value='1');
    cash = 0; $('cashInput').value = '';
    ['splitTunai','splitQris','splitTransfer'].forEach(id => { $(id).value = ''; });
    hitungSisa();
    document.querySelectorAll('.rombongan-jenjang-input').forEach(el=>el.value='');
    document.querySelectorAll('.rombongan-usia-input').forEach(el=>el.value='');
    document.querySelectorAll('.rombongan-nama-input').forEach(el=>el.value='');
    document.querySelectorAll('.rombongan-nama-custom').forEach(el=>{el.value=''; el.classList.add('d-none');});
   syncRombonganExtra(); render();
  }
  $('btnResetCart').addEventListener('click', resetCart);
  $('btnBayar').addEventListener('click', function () {
    const total = +($('cartTotal').dataset.value || 0);
    if (!total) { Swal.fire({ icon: 'warning', title: 'Keranjang kosong' }); return; }
    // Validasi per cart entry (mendukung fleksibel: 1 tiket sama beda jenjang/usia/rombongan dalam 1 transaksi)
    for(const k in cart){
      const it = cart[k]; if(it.qty<=0 || it.kind!=='tiket') continue;
      const ref = it.ref;
      const card = document.querySelector(`[data-flex-add][data-ref="${ref}"]`)?.closest('.tiket-card') || document.querySelector(`[data-key="tiket-${ref}-putra"]`)?.closest('.tiket-card') || document.querySelector(`[data-key="tiket-${ref}-putri"]`)?.closest('.tiket-card');
      const needJenjang = !!card?.querySelector('.rombongan-jenjang-input');
      const needUsia = !!card?.querySelector('.rombongan-usia-input');
      const needNama = !!card?.querySelector('.rombongan-nama-input');
      if(needJenjang && !it.jenjang){ Swal.fire({icon:'warning',title:'Jenjang wajib dipilih',text: it.nama+' butuh jenjang'}); return; }
      if(needUsia && !it.usia){ Swal.fire({icon:'warning',title:'Kelompok usia wajib dipilih',text: it.nama+' butuh kelompok usia'}); return; }
      if(needNama && !it.rombongan){ Swal.fire({icon:'warning',title:'Rombongan wajib dipilih',text: it.nama+' butuh nama rombongan'}); return; }
    }
    // Fallback map untuk tiket non-flex (qty per area) — tetap kirim untuk kompatibilitas backend
    const rombonganJenjangMap = {};
    const rombonganNamaMap = {};
    const rombonganUsiaMap = {};
    // isi map dari cart (ambil salah satu per ref untuk kompatibilitas lama)
    for(const k in cart){
      const it=cart[k]; if(it.kind!=='tiket' || it.qty<=0) continue;
      if(it.jenjang && !rombonganJenjangMap[it.ref]) rombonganJenjangMap[it.ref]=it.jenjang;
      if(it.usia && !rombonganUsiaMap[it.ref]) rombonganUsiaMap[it.ref]=it.usia;
      if(it.rombongan && !rombonganNamaMap[it.ref]) rombonganNamaMap[it.ref]=it.rombongan;
    }
    // juga validasi lama untuk qty input langsung (jika ada yang belum masuk cart tapi qty>0)
    document.querySelectorAll('.rombongan-jenjang-input').forEach(el=>{
      const ref=el.dataset.ref;
      const qty=(parseInt(($('qty-tiket-'+ref+'-putra')||{}).value,10)||0)+(parseInt(($('qty-tiket-'+ref+'-putri')||{}).value,10)||0);
      if(qty>0 && !rombonganJenjangMap[ref]) rombonganJenjangMap[ref]=el.value.trim();
    });
    document.querySelectorAll('.rombongan-usia-input').forEach(el=>{
      const ref=el.dataset.ref;
      const qty=(parseInt(($('qty-tiket-'+ref+'-putra')||{}).value,10)||0)+(parseInt(($('qty-tiket-'+ref+'-putri')||{}).value,10)||0);
      if(qty>0 && !rombonganUsiaMap[ref]) rombonganUsiaMap[ref]=el.value.trim();
    });
    document.querySelectorAll('.rombongan-nama-input').forEach(el=>{
      const ref=el.dataset.ref;
      const qty=(parseInt(($('qty-tiket-'+ref+'-putra')||{}).value,10)||0)+(parseInt(($('qty-tiket-'+ref+'-putri')||{}).value,10)||0);
      if(qty>0 && !rombonganNamaMap[ref]) rombonganNamaMap[ref]=rombonganNamaValue(el);
    });
    const pay = payMethod;
    // Kembalian berlaku untuk metode tunggal (TUNAI/QRIS/TRANSFER): kosong = uang pas.
    // Gabungan: jumlah tiap metode harus pas total (tanpa kembalian).
    let payments = null;
    let tendered = cash > 0 ? cash : total;
    if (pay === 'GABUNGAN') {
      payments = splitVals();
      const s = payments.TUNAI + payments.QRIS + payments.TRANSFER;
      if (!Object.values(payments).some(v => v > 0)) {
        Swal.fire({ icon: 'warning', title: 'Rincian kosong', text: 'Isi nominal Tunai/QRIS/Transfer dulu.' });
        return;
      }
      if (s !== total) {
        Swal.fire({ icon: 'warning', title: 'Jumlah belum pas', text: 'Total metode ' + fmt(s) + ', total belanja ' + fmt(total) + '.' });
        return;
      }
      tendered = total;
    } else if (tendered < total) {
      Swal.fire({ icon: 'warning', title: 'Uang kurang', text: 'Butuh ' + fmt(total) + ', diterima ' + fmt(tendered) });
      return;
    }
    const items = Object.keys(cart).filter(k => cart[k].qty > 0).map(k => ({
      kind: cart[k].kind, ref: cart[k].ref, detail: cart[k].detail || '',
      area: cart[k].area || '', qty: cart[k].qty, amount: cart[k].kind === 'sewa' ? cart[k].harga : 0,
      jenjang: cart[k].jenjang || '', usia: cart[k].usia || '', rombongan: cart[k].rombongan || ''
    }));
    const isEdit = !!(editData && editData.id);
    Swal.fire({ title: isEdit ? 'Simpan perubahan transaksi?' : 'Proses pembayaran ' + pay + '?', text: fmt(total), icon: 'question', showCancelButton: true, confirmButtonText: isEdit ? 'Simpan' : 'Bayar' })
      .then(res => {
        if (!res.isConfirmed) return;
        const cartAreas = [...new Set(Object.keys(cart).filter(k=>cart[k].qty>0 && cart[k].area).map(k=>cart[k].area))];
        let areaGlobal = 'PUTRA';
        if(cartAreas.length===1) areaGlobal=cartAreas[0];
        else if(cartAreas.length>1) areaGlobal='CAMPURAN';
        else if(cartAreas.length===0) areaGlobal='PUTRA';
        const payload={ tarif: tarif, kelas: kelas, area: areaGlobal, payment: pay, payments: payments, tendered: tendered, member_id: memberId, student_id: studentId, items: items, rombongan_jenjang_map: rombonganJenjangMap, rombongan_nama_map: rombonganNamaMap, rombongan_usia_map: rombonganUsiaMap };
        if(editData && editData.id) payload.edit_id=editData.id;
        fetch('<?= url('index.php?page=kasir&ajax=checkout') ?>', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        }).then(r => r.json()).then(d => {
          if (!d.ok) { Swal.fire({ icon: 'error', title: 'Gagal', text: d.message }); return; }
          Swal.fire({
            icon: 'success', title: d.number, text: fmt(d.total) + ' — ' + d.message,
            showCancelButton: true, confirmButtonText: 'Cetak Struk', cancelButtonText: 'Selesai'
          }).then(r2 => {
            if (r2.isConfirmed) window.open('<?= url('index.php?page=struk&id=') ?>' + d.receipt_id, '_blank');
            location.reload();
          });
        }).catch(() => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Server tidak merespons.' }));
      });
  });
  $('cariItem').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.katalog-item').forEach(el => {
      el.style.display = el.dataset.nama.includes(q) ? '' : 'none';
    });
  });
   syncRombonganExtra(); render();
 });
</script>
<?php $pageScripts = ob_get_clean(); ?>
