<?php
// Helper global: escaping, URL, format angka/tanggal Indonesia.

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function rupiah($n): string
{
    return 'Rp' . number_format((float) $n, 0, ',', '.');
}

function rupiah_short($n): string
{
    $n = (float) $n;
    if ($n >= 1000000) {
        $s = number_format($n / 1000000, 1, ',', '.');
        return 'Rp' . rtrim(rtrim($s, '0'), ',') . 'M';
    }
    if ($n >= 1000) {
        $s = number_format($n / 1000, 0, ',', '.');
        return 'Rp' . $s . 'rb';
    }
    return rupiah($n);
}

function bulan_indo(int $m): string
{
    $b = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $b[$m] ?? '';
}

function tanggal_indo(?string $date): string
{
    if (!$date) return '-';
    $t = strtotime($date);
    if (!$t) return '-';
    return date('d', $t) . ' ' . bulan_indo((int) date('n', $t)) . ' ' . date('Y', $t);
}

/** Format ringkas: "07 Okt 2026". */
function tanggal_pendek(?string $date): string
{
    if (!$date) return '-';
    $t = strtotime($date);
    if (!$t) return '-';
    $b = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul',
        'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date('d', $t) . ' ' . ($b[(int) date('n', $t)] ?? '') . ' ' . date('Y', $t);
}

function hari_tanggal_indo(?string $date = null): string
{
    $t = $date ? strtotime($date) : time();
    $h = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'];
    return ($h[date('l', $t)] ?? '') . ', ' . date('d', $t) . ' '
        . bulan_indo((int) date('n', $t)) . ' ' . date('Y', $t);
}

/**
 * Batas minggu ISO (Senin–Minggu) untuk $year minggu ke-$week.
 * @return array{0:string,1:string} [start, end] sebagai 'Y-m-d'.
 */
function weekBounds(int $year, int $week): array
{
    $start = date('Y-m-d', strtotime(sprintf('%04d-W%02d-1', $year, $week)));
    $end = date('Y-m-d', strtotime('+6 days', strtotime($start)));
    return [$start, $end];
}

/**
 * Batas minggu laporan per bulan (konsisten 4 minggu):
 * minggu 1 = tgl 1-7, minggu 2 = 8-14, minggu 3 = 15-21,
 * minggu 4 = 22 s.d. akhir bulan (28/29/30/31 menyesuaikan bulan).
 *
 * @return array{0:string,1:string} [start, end] sebagai 'Y-m-d'.
 */
function weekOfMonthBounds(int $year, int $month, int $week): array
{
    $month = max(1, min(12, $month));
    $week = max(1, min(4, $week));
    $lastDay = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
    $startDay = ($week - 1) * 7 + 1;
    $endDay = $week === 4 ? $lastDay : $startDay + 6;
    return [
        sprintf('%04d-%02d-%02d', $year, $month, $startDay),
        sprintf('%04d-%02d-%02d', $year, $month, $endDay),
    ];
}

/** Nomor minggu berjalan dalam bulan (1-4) untuk tanggal tertentu. */
function current_week_of_month(?string $date = null): int
{
    $day = (int) date('j', $date ? strtotime($date) : time());
    return max(1, min(4, (int) ceil($day / 7)));
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach ($parts as $i => $p) {
        if ($i > 1) break;
        $out .= strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: '?';
}

function post(string $key, $default = null)
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function get_param(string $key, $default = null)
{
    $v = $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function to_money($v): float
{
    if ($v === null || $v === '') return 0.0;
    $clean = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string) $v));
    return round((float) $clean, 2);
}

function redirect(string $to): void
{
    header('Location: ' . url('index.php') . (str_starts_with($to, '?') ? $to : '?' . ltrim($to, '?')));
    exit;
}

function status_membership(string $endDate, int $expiringDays = 7): string
{
    $today = date('Y-m-d');
    if ($endDate < $today) return 'Expired';
    $diff = (strtotime($endDate) - strtotime($today)) / 86400;
    return $diff <= $expiringDays ? 'Akan Expired' : 'Aktif';
}

/**
 * Hitung tanggal habis member: dari tanggal daftar ke tanggal yang sama
 * (kalender, bukan +N hari mentah).
 *
 * Contoh: daftar 7 Okt paket Bulanan (30 hari) -> habis 7 Nov.
 *          daftar 7 Okt 2025 paket Tahunan (365 hari) -> habis 7 Okt 2026.
 *
 * Aturan:
 * - Paket tahunan (nama mengandung 'tahun' / durasi >= 360 hari):
 *   tambah tahun kalender (365 hari => +1 tahun). Tanggal dijaga,
 *   ujung bulan di-clamp (29 Feb 2024 +1 thn => 28 Feb 2025).
 * - Paket bulanan (nama mengandung 'bulan' / durasi kelipatan 30):
 *   tambah bulan kalender (30 hari => +1 bulan, 90 hari => +3 bulan).
 *   Ujung bulan di-clamp (31 Jan +1 bln => 28/29 Feb).
 * - Durasi custom lain: start + durasi hari (perilaku lama, tidak berubah).
 */
function membership_end_date(string $startDate, int $durationDays, ?string $packageName = null): string
{
    $startDate = substr(trim($startDate), 0, 10);
    $t = strtotime($startDate);
    if (!$t) $startDate = date('Y-m-d');
    $d = max(1, $durationDays);
    $name = strtolower(trim((string) ($packageName ?? '')));

    $isYearly = str_contains($name, 'tahun') || str_contains($name, 'annual') || str_contains($name, 'year') || $d >= 360;
    $isMonthly = str_contains($name, 'bulan') || str_contains($name, 'month')
        || str_contains($name, 'siswa') || str_contains($name, 'karyawan')
        || ($d < 360 && $d % 30 === 0);

    if ($isYearly) {
        $years = max(1, (int) round($d / 365));
        return membership_add_calendar($startDate, 0, $years);
    }
    if ($isMonthly) {
        $months = max(1, (int) round($d / 30));
        return membership_add_calendar($startDate, $months, 0);
    }
    return date('Y-m-d', strtotime($startDate . ' +' . $d . ' days'));
}

/** Tambah bulan/tahun kalender dengan clamp ujung bulan (31 Jan +1 bln => 28/29 Feb). */
function membership_add_calendar(string $startDate, int $months, int $years): string
{
    [$y, $m, $d] = array_map('intval', explode('-', substr($startDate, 0, 10)));
    $m += $months;
    $y += $years + intdiv($m - 1, 12);
    $m = (($m - 1) % 12) + 1;
    $last = (int) date('t', mktime(0, 0, 0, $m, 1, $y));
    return sprintf('%04d-%02d-%02d', $y, $m, min($d, $last));
}

function pill_status(string $status): string
{
    $map = ['Aktif' => 'pill-green', 'Akan Expired' => 'pill-amber', 'Expired' => 'pill-red',
        'ACTIVE' => 'pill-green', 'EXPIRING_SOON' => 'pill-amber', 'EXPIRED' => 'pill-red',
        'PENDING_PAYMENT' => 'pill-grey'];
    $c = $map[$status] ?? 'pill-grey';
    return '<span class="pill ' . $c . '"><span class="dot"></span>' . e($status) . '</span>';
}

/**
 * Hari libur = Sabtu/Minggu + tanggal di pengaturan holidays (satu per baris, YYYY-MM-DD).
 * Silver tidak bebas di hari libur (kena biaya tambahan), Gold bebas kapan saja.
 */
function is_libur(?string $date = null): bool
{
    $date = $date ?: date('Y-m-d');
    $dow = (int) date('N', strtotime($date));
    if ($dow >= 6) return true;
    try {
        $r = db()->query("SELECT setting_value FROM settings WHERE setting_key = 'holidays'")->fetch();
        $list = preg_split('/[\s,;]+/', trim((string) ($r['setting_value'] ?? '')));
        return in_array($date, $list, true);
    } catch (Throwable $e) {
        return false;
    }
}

function silver_holiday_fee(): float
{
    try {
        $r = db()->query("SELECT setting_value FROM settings WHERE setting_key = 'silver_holiday_fee'")->fetch();
        return max(0, (float) ($r['setting_value'] ?? 15000));
    } catch (Throwable $e) {
        return 15000.0;
    }
}
