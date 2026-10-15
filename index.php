<?php
// Front controller: index.php?page=<halaman>&ajax=<aksi>
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Auth.php';
foreach (glob(__DIR__ . '/models/*.php') as $f) require_once $f;
foreach (glob(__DIR__ . '/controllers/*.php') as $f) require_once $f;

$page = strtolower((string) ($_GET['page'] ?? 'dashboard'));
$ajax = (string) ($_GET['ajax'] ?? '');

if ($page === 'login') {
    (new AuthController())->login();
    exit;
}
if ($page === 'logout') {
    (new AuthController())->logout();
    exit;
}

/* ---------- endpoint AJAX/POST khusus (tanpa layout) ---------- */
if ($ajax === 'checkout' && $page === 'kasir') {
    (new KasirController())->checkout();
    exit;
}
if ($ajax === 'search' && $page === 'member') {
    (new MemberController())->search();
    exit;
}
if ($ajax === 'students' && $page === 'les') {
    (new LesController())->students();
    exit;
}
if ($page === 'scan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new ScanController())->checkin();
    exit;
}

/* ---------- halaman ---------- */
$routes = [
    'dashboard'  => ['DashboardController', 'index', 'Dashboard', 'dashboard', 'Kendali terpusat operasional kolam'],
    'kasir'      => ['KasirController', 'index', 'Kasir & Tiket', 'kasir', 'POS loket tiket & pembelian'],
    'struk'      => ['KasirController', 'receipt', 'Struk Tiket', 'kasir', 'Bukti transaksi pelanggan'],
    'member'     => ['MemberController', 'index', 'Kelola Member', 'member', 'Direktori keanggotaan'],
    'kartu'      => ['MemberController', 'card', 'Kartu Member', 'member', 'Preview & cetak kartu'],
    'scan'       => [null, null, 'Scan & Check-in', 'scan', 'Pintu masuk turnstile'],
    'hasil-scan' => ['ScanController', 'result', 'Hasil Scan', 'scan', 'Verifikasi member'],
    'les'        => ['LesController', 'index', 'Les Renang & Jadwal', 'les', 'Paket, peserta, instruktur & jadwal'],
    'absensi'    => ['AbsensiController', 'index', 'Absensi Les', 'absensi', 'Input kehadiran peserta oleh instruktur'],
    'transaksi'  => ['TransaksiController', 'index', 'Transaksi & Laporan', 'transaksi', 'Riwayat transaksi operasional'],
    'laporan'    => ['LaporanController', 'index', 'Laporan Bulanan', 'laporan', 'Format laporan Kolam Renang Al Ma\'soem'],
    'kunjungan'  => ['KunjunganController', 'index', 'Laporan Kunjungan', 'kunjungan', 'Putra/Putri & External/Internal per periode'],
    'obat'       => ['MedicineController', 'index', 'Stok Obat', 'obat', 'Manajemen stok obat kolam'],
    'master'     => ['MasterController', 'index', 'Master Data & Pengaturan', 'master', 'Paket, tiket, user & pengaturan'],
    'sewa'       => [null, null, 'Sewa Alat', 'sewa', 'Sewa peralatan kolam'],
];
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'dashboard';
}
[$ctrl, $method, $pageTitle, $activeMenu, $pageSubtitle] = $routes[$page];

$data = [];
if ($ctrl) {
    $c = new $ctrl();
    if ($page === 'kartu' || $page === 'struk') {
        $data = $c->$method((int) ($_GET['id'] ?? 0));
        if ($data === null) {
            http_response_code(404);
            die('Data tidak ditemukan.');
        }
    } else {
        $data = $c->$method();
    }
}
if ($page === 'scan') {
    require_role(['admin', 'petugas', 'kasir']);
    $n = db()->query("SELECT COUNT(*) n FROM member_visits WHERE visit_date = '" . date('Y-m-d') . "'")->fetch();
    $data = ['settings' => (new Setting())->allAsMap(), 'checkinHari' => (int) ($n['n'] ?? 0)];
}
if ($page === 'sewa') {
    require_role(['admin', 'kasir']);
    $today = date('Y-m-d');
    $data = [
        'TRANSAKSI' => (new Transaction())->listAll(['date' => $today, 'type' => 'SEWA']),
        'shiftStat' => (new Transaction())->todaySummary($today),
    ];
}
extract($data, EXTR_SKIP);

require __DIR__ . '/views/layouts/header.php';
require __DIR__ . '/views/layouts/sidebar.php';
require __DIR__ . '/views/layouts/topbar.php';
require __DIR__ . '/views/layouts/flash.php';
?>
<main class="tt-main">
  <?php require __DIR__ . '/views/pages/' . $page . '.php'; ?>
</main>
<?php require __DIR__ . '/views/layouts/footer.php'; ?>
