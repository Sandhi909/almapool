<?php
// Seed PRODUCTION minimal - tanpa dummy transaksi/member/visits untuk testing real lapangan.
// Jalankan sekali via CLI/browser ?allow=1. Aman diulang (INSERT IGNORE / check exists).
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

if (defined('APP_ENV') && APP_ENV === 'production' && php_sapi_name() !== 'cli' && empty($_GET['allow'])) {
    http_response_code(403);
    die('Seed diblok di production. Akses via CLI atau ?allow=1');
}

$pdo = db();
if ((int) $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'] > 0) {
    echo "Seed dilewati: users sudah ada (DB sudah siap real test).\n";
    exit;
}
$pdo->beginTransaction();

// Hanya 1 admin real - ganti password setelah login!
$pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)")->execute(
    ['Administrator', 'admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

$settings = [
    'nama_kolam' => 'KOLAM RENANG AL MA’SOEM',
    'jam_operasional' => '08:00 – 16:00 WIB',
    'sesi_putra' => '08:00 – 12:00 WIB',
    'sesi_putri' => '13:00 – 16:00 WIB',
    'kapasitas' => '400',
    'expiring_days' => '7',
    'silver_holiday_fee' => '15000',
    'holidays' => '',
    'saldo_awal_bon' => '0',
    'saldo_awal_tunai' => '0',
    'ttd_admin' => 'Salsabila Yustiani K',
    'ttd_admin_jabatan' => 'Administrasi Kolam',
    'ttd_manajer' => 'Asep Supriadi',
    'ttd_manajer_jabatan' => 'Manajer Kolam',
    'ttd_kepala' => 'Cecep Hermawan',
    'ttd_kepala_jabatan' => 'Kepala Direktorat Pengelola Unit Bisnis',
];
$st = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?)");
foreach ($settings as $k => $v) $st->execute([$k, $v]);

$tickets = [
    ['Umum', 'Putih', 25000, 30000, 35000],
    ['Siswa Al Ma’soem / Mhs Al Ma’soem / rombongan ≤ 20 orang', 'Biru', 15000, 15000, 20000],
    ['Rombongan 10-19 orang', 'Biru Cap', 20000, 30000, 35000],
    ['Santri Al Ma’soem', 'Hijau', 15000, 15000, 20000],
    ['Karyawan & Keluarga Al Ma’soem Group', 'Hijau Cap', 7000, 9000, 10000],
    ['Pengantar (Minggu & Libur)', 'Coklat', 0, 12000, 12000],
    ['Voucher', '-', 15000, 0, 0],
];
$st = $pdo->prepare("INSERT INTO ticket_types (name, color, price_a, price_b, price_c) VALUES (?,?,?,?,?)");
foreach ($tickets as $t) $st->execute($t);

$packages = [
    ['Bulanan', 'GOLD', 30, 140000], ['Bulanan', 'SILVER', 30, 110000],
    ['Siswa Al Ma’soem', 'GOLD', 30, 70000], ['Siswa Al Ma’soem', 'SILVER', 30, 55000],
    ['Karyawan', 'GOLD', 30, 42000], ['Karyawan', 'SILVER', 30, 33000],
    ['Tahunan', 'GOLD', 365, 1200000], ['Tahunan', 'SILVER', 365, 900000],
];
$st = $pdo->prepare("INSERT INTO membership_packages (name, tier, duration_days, price) VALUES (?,?,?,?)");
foreach ($packages as $p) $st->execute($p);

$st = $pdo->prepare("INSERT INTO lesson_packages (name, price, duration_days, session_count) VALUES (?,?,?,?)");
$st->execute(['Private', 450000, 90, 10]);
$st->execute(['Reguler', 350000, 90, 10]);

$st = $pdo->prepare("INSERT INTO instructors (name, phone, specialty) VALUES (?,?,?)");
$st->execute(['Budi Hartono', '0812-2200-1100', 'Anak & Pemula']);
$st->execute(['Sari Melati', '0857-3300-2211', 'Gaya Kupu & Punggung']);
$st->execute(['Agus Setiawan', '0813-4400-3322', 'Atlet Prestasi']);

$pdo->commit();
echo "Seed PRODUCTION OK - siap testing real (tanpa dummy member/transaksi).\n";
