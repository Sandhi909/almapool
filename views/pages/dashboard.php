<?php // $STAT, $FEED, $MEMBERS dari DashboardController. ?>
<div class="page-hero p-3 p-md-4 mb-3 d-flex flex-wrap align-items-center gap-3 position-relative" style="z-index:1">
  <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
    <span class="hero-icon"><i class="bi bi-water fs-4"></i></span>
    <div>
      <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans'">Selamat Datang di Kolam Renang Al Ma'soem</h5>
      <small class="opacity-90"><?= e(hari_tanggal_indo()) ?></small>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 flex-wrap align-items-center" style="position:relative;z-index:1">
    <div class="px-3 py-2 rounded-4 bg-white text-dark" style="min-width:160px;box-shadow:0 6px 16px rgba(0,0,0,.12)">
      <small class="text-muted d-block" style="font-size:.68rem;letter-spacing:.04em">TARIF HARI INI</small>
      <b class="num fs-5" style="color:#0284c7">Tarif <?= e($TARIF_AKTIF) ?></b>
      <small class="text-muted d-block" style="font-size:.65rem"><?= e($TARIF_HARI[$TARIF_AKTIF] ?? '') ?></small>
    </div>
    <span class="pill bg-white text-primary" style="position:relative;z-index:1"><i class="bi bi-tag"></i> <?= e($settings['sesi_putra'] ?? '') ?><?= ($settings['sesi_putra'] ?? '') !== ($settings['sesi_putri'] ?? '') ? ' &bull; ' . e($settings['sesi_putri'] ?? '') : '' ?></span>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="tt-card h-100">
      <div class="d-flex justify-content-between"><span class="metric-label">Total Member Terdaftar</span><span class="metric-icon mi-blue"><i class="bi bi-people"></i></span></div>
      <div class="metric-value num"><?= number_format($STAT['total_member'], 0, ',', '.') ?></div>
      <small class="text-muted">Member aktif: <b><?= number_format($STAT['member_aktif'], 0, ',', '.') ?></b></small>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tt-card h-100">
      <div class="d-flex justify-content-between"><span class="metric-label">Check-in Hari Ini</span><span class="metric-icon mi-teal"><i class="bi bi-person-check"></i></span></div>
      <div class="metric-value num"><?= $STAT['checkin_hari'] ?> <small class="metric-unit">Orang</small></div>
      <small class="text-muted">Putra: <b><?= $checkinPutra ?></b> &bull; Putri: <b><?= $checkinPutri ?></b> / maks <?= $STAT['kapasitas'] ?> pax</small>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tt-card h-100">
      <div class="d-flex justify-content-between"><span class="metric-label">Tiket Kasir Terjual</span><span class="metric-icon mi-blue"><i class="bi bi-ticket-perforated"></i></span></div>
      <div class="metric-value num"><?= $STAT['tiket_hari'] ?> <small class="metric-unit">Tiket</small></div>
      <small class="text-muted"><?= $STAT['tiket_hari'] ?> tiket &bull; Putra: <?= $checkinPutra ?> &bull; Putri: <?= $checkinPutri ?></small>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tt-card h-100">
      <div class="d-flex justify-content-between"><span class="metric-label">Total Pendapatan Kasir</span><span class="metric-icon mi-green"><i class="bi bi-cash-stack"></i></span></div>
      <div class="metric-value num" style="color:var(--tt-primary)"><?= rupiah_short($STAT['omset_hari']) ?></div>
      <small class="text-muted">Cash: <?= rupiah_short($transSum['by']['TUNAI']) ?> &bull; QRIS/TF: <?= rupiah_short($transSum['by']['QRIS'] + $transSum['by']['TRANSFER']) ?></small>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-xl-6">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-bar-chart-line me-1"></i>Kunjungan 6 Bulan Terakhir</b>
        <small class="ms-auto text-muted">Internal vs External</small>
      </div>
      <div style="height:300px">
        <canvas id="chartMonthly"></canvas>
      </div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 mb-2">
        <b><i class="bi bi-bar-chart me-1"></i>Kunjungan 7 Hari Terakhir</b>
        <small class="ms-auto text-muted">Internal vs External</small>
      </div>
      <div style="height:300px">
        <canvas id="chartDaily"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="tt-card">
      <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
        <b><i class="bi bi-bell me-1 text-danger"></i>Member Akan Expired (&lt; 3 Hari)</b>
        <span class="pill pill-red ms-auto"><?= count($MEMBERS) ?> Perlu Konfirmasi</span>
      </div>
      <small class="text-muted">Prioritas follow-up perpanjangan sebelum akses turnstile diblokir</small>
      <div class="table-responsive mt-2">
        <table class="table tt-table align-middle mb-0">
          <thead><tr><th>Member</th><th>ID Member</th><th>Paket</th><th>Kedaluwarsa</th><th>Status Sisa</th></tr></thead>
          <tbody>
            <?php foreach ($MEMBERS as $m): ?>
            <tr>
              <td><div class="d-flex gap-2 align-items-center"><span class="tt-avatar"><?= e(initials($m['nama'])) ?></span>
                <div><b><?= e($m['nama']) ?></b><br><small class="text-muted num"><?= e($m['hp']) ?></small></div></div></td>
              <td><span class="pill pill-blue num"><?= e($m['nomor']) ?></span></td>
              <td><?= e($m['paket']) ?></td>
              <td class="num"><?= e(tanggal_indo($m['berakhir'])) ?></td>
              <td><span class="pill pill-amber">Segera habis</span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$MEMBERS): ?><tr><td colspan="5" class="text-center text-muted">Tidak ada member akan expired 3 hari ke depan.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="tt-card">
      <b><span class="live-dot me-1"></span>Live Feed Aktivitas</b>
      <small class="text-muted d-block mb-2">Gate &amp; Kasir</small>
      <?php
      foreach ($FEED as $f):
      ?>
      <div class="feed-item">
        <div class="flex-fill">
          <div class="d-flex justify-content-between"><b><?= e($f['judul']) ?></b><small class="text-muted num"><?= e($f['waktu']) ?></small></div>
          <small class="text-muted d-block"><?= e($f['desc']) ?></small>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const monthlyData = <?= json_encode($chartData['monthly'] ?? []) ?>;
  const dailyData = <?= json_encode($chartData['daily'] ?? []) ?>;
  
  // Chart bulanan
  const ctxMonthly = document.getElementById('chartMonthly');
  if (ctxMonthly && monthlyData.labels) {
    new Chart(ctxMonthly, {
      type: 'bar',
      data: {
        labels: monthlyData.labels,
        datasets: [
          {
            label: 'Internal Putra',
            data: monthlyData.internalPutra,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 1
          },
          {
            label: 'Internal Putri',
            data: monthlyData.internalPutri,
            backgroundColor: 'rgba(52, 211, 153, 0.8)',
            borderColor: 'rgba(52, 211, 153, 1)',
            borderWidth: 1
          },
          {
            label: 'External Putra',
            data: monthlyData.externalPutra,
            backgroundColor: 'rgba(245, 158, 11, 0.8)',
            borderColor: 'rgba(245, 158, 11, 1)',
            borderWidth: 1
          },
          {
            label: 'External Putri',
            data: monthlyData.externalPutri,
            backgroundColor: 'rgba(251, 191, 36, 0.8)',
            borderColor: 'rgba(251, 191, 36, 1)',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }
  
  // Chart harian
  const ctxDaily = document.getElementById('chartDaily');
  if (ctxDaily && dailyData.labels) {
    new Chart(ctxDaily, {
      type: 'line',
      data: {
        labels: dailyData.labels,
        datasets: [
          {
            label: 'Internal Putra',
            data: dailyData.internalPutra,
            borderColor: 'rgba(16, 185, 129, 1)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
          },
          {
            label: 'Internal Putri',
            data: dailyData.internalPutri,
            borderColor: 'rgba(52, 211, 153, 1)',
            backgroundColor: 'rgba(52, 211, 153, 0.1)',
            tension: 0.4,
            fill: true
          },
          {
            label: 'External Putra',
            data: dailyData.externalPutra,
            borderColor: 'rgba(245, 158, 11, 1)',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4,
            fill: true
          },
          {
            label: 'External Putri',
            data: dailyData.externalPutri,
            borderColor: 'rgba(251, 191, 36, 1)',
            backgroundColor: 'rgba(251, 191, 36, 0.1)',
            tension: 0.4,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
