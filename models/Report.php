<?php
// Agregasi Laporan Bulanan dari database (format dokumen Al Ma'soem).
class Report extends Model
{
    protected string $table = 'transactions';

    public function monthly(int $year, int $month): array
    {
        $ym = sprintf('%04d-%02d', $year, $month);
        $txn = "DATE_FORMAT(created_at,'%Y-%m') = ?";
        $cost = "DATE_FORMAT(cost_date,'%Y-%m') = ?";
        $dep = "DATE_FORMAT(deposit_date,'%Y-%m') = ?";
        return $this->aggregate($txn, [$ym], $cost, [$ym], $dep, [$ym]);
    }

    public function daily(string $date): array
    {
        $txn = 'DATE(created_at) = ?';
        $cost = 'DATE(cost_date) = ?';
        $dep = 'DATE(deposit_date) = ?';
        return $this->aggregate($txn, [$date], $cost, [$date], $dep, [$date]);
    }

    public function weekly(string $start, string $end): array
    {
        $txn = 'DATE(created_at) BETWEEN ? AND ?';
        $cost = 'DATE(cost_date) BETWEEN ? AND ?';
        $dep = 'DATE(deposit_date) BETWEEN ? AND ?';
        $p = [$start, $end];
        return $this->aggregate($txn, $p, $cost, $p, $dep, $p);
    }

    private function aggregate(string $txnWhere, array $txnParams, string $costWhere, array $costParams, string $depWhere, array $depParams): array
{
        $tickets = $this->query(
            "SELECT tt.name, tt.color, tt.price_a AS la, tt.price_b AS lb, tt.price_c AS lc,
                    SUM(CASE WHEN ti.tariff_day = 'A' THEN ti.quantity ELSE 0 END) AS qa,
                    SUM(CASE WHEN ti.tariff_day = 'B' THEN ti.quantity ELSE 0 END) AS qb,
                    SUM(CASE WHEN ti.tariff_day = 'C' THEN ti.quantity ELSE 0 END) AS qc,
                    SUM(ti.subtotal) AS subtotal,
                    MIN(ti.serial_start) AS seri_awal, MAX(ti.serial_end) AS seri_akhir,
                    MAX(ti.serial_prefix) AS seri_prefix
             FROM ticket_types tt
             JOIN transaction_items ti ON ti.item_ref_id = tt.id AND ti.item_type = 'TIKET'
                AND ti.transaction_id IN (SELECT id FROM transactions WHERE $txnWhere AND payment_status = 'PAID')
             GROUP BY tt.id, tt.name, tt.color
             ORDER BY tt.id", $txnParams);

        $members = $this->query(
            "SELECT mp.name AS jenis,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Gold' THEN ti.quantity ELSE 0 END),0) qg,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Silver' THEN ti.quantity ELSE 0 END),0) qs,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Gold' THEN ti.subtotal ELSE 0 END),0) jg,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Silver' THEN ti.subtotal ELSE 0 END),0) js
             FROM (SELECT DISTINCT name FROM membership_packages) mp
             LEFT JOIN transaction_items ti ON ti.item_name LIKE CONCAT('%', mp.name, '%') AND ti.item_type = 'MEMBER'
                AND ti.transaction_id IN (SELECT id FROM transactions WHERE $txnWhere AND payment_status = 'PAID')
             GROUP BY mp.name", $txnParams);

        $les = $this->query(
            "SELECT lp.name AS jenis, lp.price AS tarif,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Putra' THEN ti.quantity ELSE 0 END),0) qputra,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Putri' THEN ti.quantity ELSE 0 END),0) qputri,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Putra' THEN ti.subtotal ELSE 0 END),0) jpra,
                    COALESCE(SUM(CASE WHEN ti.detail = 'Putri' THEN ti.subtotal ELSE 0 END),0) jpri
             FROM lesson_packages lp
             LEFT JOIN transaction_items ti ON ti.item_ref_id = lp.id AND ti.item_type = 'LES'
                AND ti.transaction_id IN (SELECT id FROM transactions WHERE $txnWhere AND payment_status = 'PAID')
             GROUP BY lp.id ORDER BY lp.id", $txnParams);

        $sewa = (float) ($this->queryOne(
            "SELECT COALESCE(SUM(ti.subtotal),0) t FROM transaction_items ti
             JOIN transactions t ON t.id = ti.transaction_id
             WHERE ti.item_type = 'SEWA' AND $txnWhere AND t.payment_status = 'PAID'", $txnParams)['t'] ?? 0);

        $biaya = $this->query(
            "SELECT COALESCE(et.name, 'Biaya Lainnya') nama, COALESCE(SUM(oc.amount),0) jumlah FROM operational_costs oc
             LEFT JOIN expense_types et ON oc.expense_type_id = et.id
             WHERE $costWhere GROUP BY oc.expense_type_id ORDER BY MIN(oc.id)", $costParams);

        $dep = $this->query(
            "SELECT kind, COALESCE(SUM(amount),0) jumlah FROM deposits
             WHERE $depWhere GROUP BY kind", $depParams);
        $depMap = ['QRIS' => 0, 'TRANSFER' => 0, 'UANG' => 0];
        foreach ($dep as $d) {
            $k = strtoupper(trim((string) ($d['kind'] ?? '')));
            if (isset($depMap[$k])) $depMap[$k] = (float) $d['jumlah'];
        }

        // Setoran OTOMATIS dari transaksi kasir per metode bayar (PAID saja).
        // QRIS/TRANSFER langsung masuk rekening yayasan, TUNAI = setoran uang.
        // Split-aware: transaksi GABUNGAN dipecah ke tiap kolom metodenya.
        $pay = $this->queryOne(
            "SELECT
                COALESCE(SUM(CASE WHEN payment_method = 'TUNAI' THEN amount WHEN payment_method = 'GABUNGAN' THEN pay_tunai ELSE 0 END),0) tunai,
                COALESCE(SUM(CASE WHEN payment_method = 'QRIS' THEN amount WHEN payment_method = 'GABUNGAN' THEN pay_qris ELSE 0 END),0) qris,
                COALESCE(SUM(CASE WHEN payment_method = 'TRANSFER' THEN amount WHEN payment_method = 'GABUNGAN' THEN pay_transfer ELSE 0 END),0) transfer
              FROM transactions WHERE $txnWhere AND payment_status = 'PAID'", $txnParams);
        $trxPay = ['TUNAI' => (float) ($pay['tunai'] ?? 0), 'QRIS' => (float) ($pay['qris'] ?? 0), 'TRANSFER' => (float) ($pay['transfer'] ?? 0)];

        $tambahan = (float) ($this->queryOne(
            "SELECT COALESCE(SUM(ti.subtotal),0) t FROM transaction_items ti
             JOIN transactions t ON t.id = ti.transaction_id
             WHERE ti.item_type = 'TAMBAHAN' AND $txnWhere AND t.payment_status = 'PAID'", $txnParams)['t'] ?? 0);

        return compact('tickets', 'members', 'les', 'sewa', 'biaya', 'depMap', 'trxPay', 'tambahan');
    }

    public function dashboard(): array
    {
        $today = date('Y-m-d');
        $m = new Member();
        $mc = $m->counts();
        $t = new Transaction();
        $sum = $t->todaySummary($today);
        $checkin = $this->queryOne('SELECT COUNT(*) n FROM member_visits WHERE visit_date = ?', [$today])['n'];
        $checkinPutra = $this->queryOne("SELECT COUNT(*) n FROM member_visits WHERE visit_date = ? AND area = 'PUTRA'", [$today])['n'];
        $checkinPutri = $this->queryOne("SELECT COUNT(*) n FROM member_visits WHERE visit_date = ? AND area = 'PUTRI'", [$today])['n'];
        $tiketHari = $this->queryOne("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE ti.item_type = 'TIKET' AND DATE(t.created_at) = ? AND t.payment_status = 'PAID'", [$today])['q'];
        $lesAktif = $this->queryOne("SELECT COUNT(*) n FROM lesson_students WHERE status = 'ACTIVE'")['n'];
        $jadwalHari = $this->query('SELECT ls.*, i.name AS instruktur FROM lesson_schedules ls LEFT JOIN instructors i ON i.id = ls.instructor_id WHERE ls.is_active = 1 ORDER BY FIELD(day_of_week,"Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu"), start_time LIMIT 4');
        $expiring = $this->query(
            'SELECT m.name, m.phone, ms.end_date, mp.name AS paket FROM memberships ms
             JOIN members m ON m.id = ms.member_id JOIN membership_packages mp ON mp.id = ms.package_id
             WHERE ms.end_date BETWEEN ? AND DATE_ADD(?, INTERVAL 3 DAY) AND ms.status = ?
             ORDER BY ms.end_date LIMIT 5', [$today, $today, 'ACTIVE']);
        $feed = $this->query(
            "(SELECT 'checkin' k, CONCAT('Check-in ', v.area) judul, v.check_in_at waktu, CONCAT(mm.name, ' • ', DATE_FORMAT(v.visit_date,'%d/%m')) isi, 'Akses Terbuka' sub FROM member_visits v JOIN members mm ON mm.id = v.member_id ORDER BY v.check_in_at DESC LIMIT 3)
             UNION ALL
             (SELECT 'trx' k, CONCAT('Transaksi ', t.number) judul, t.created_at waktu, CONCAT(t.type, ' • Rp', FORMAT(t.amount,0)) isi, t.payment_method sub FROM transactions t ORDER BY t.created_at DESC LIMIT 3)
             ORDER BY waktu DESC LIMIT 6");
        return compact('mc', 'sum', 'checkin', 'checkinPutra', 'checkinPutri', 'tiketHari', 'lesAktif', 'jadwalHari', 'expiring', 'feed');
    }
}
