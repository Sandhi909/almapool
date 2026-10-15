<?php
class Member extends Model
{
    protected string $table = 'members';

    public function generateNumber(): string
    {
        for ($i = 0; $i < 50; $i++) {
            $row = $this->queryOne("SELECT member_number FROM members WHERE member_number LIKE 'MEM-%' ORDER BY member_number DESC LIMIT 1");
            $next = $row ? ((int) substr($row['member_number'], 4)) + 1 : 1;
            $num = 'MEM-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            if (!$this->queryOne('SELECT id FROM members WHERE member_number = ?', [$num])) return $num;
        }
        return 'MEM-' . date('His');
    }

    public function findByNumber(string $number): ?array
    {
        return $this->queryOne('SELECT * FROM members WHERE member_number = ? OR qr_identifier = ? LIMIT 1', [$number, $number]);
    }

    public function search(string $q, int $limit = 20): array
    {
        $like = '%' . $q . '%';
        return $this->query(
            'SELECT m.*, ms.end_date, ms.status AS ms_status, mp.name AS paket, mp.tier
             FROM members m
             LEFT JOIN memberships ms ON ms.id = (SELECT id FROM memberships WHERE member_id = m.id ORDER BY end_date DESC, id DESC LIMIT 1)
             LEFT JOIN membership_packages mp ON mp.id = ms.package_id
             WHERE m.member_number LIKE ? OR m.name LIKE ? OR m.phone LIKE ?
             ORDER BY m.name LIMIT ' . (int) $limit, [$like, $like, $like]);
    }

    public function listAll(?string $status = null): array
    {
        $rows = $this->query(
            'SELECT m.*, ms.start_date, ms.end_date, ms.status AS ms_status, ms.package_id, mp.name AS paket, mp.tier
              FROM members m
              LEFT JOIN memberships ms ON ms.id = (SELECT id FROM memberships WHERE member_id = m.id ORDER BY end_date DESC, id DESC LIMIT 1)
              LEFT JOIN membership_packages mp ON mp.id = ms.package_id
              ORDER BY m.created_at DESC');
        if ($status === null) return $rows;
        return array_values(array_filter($rows, fn ($r) => $this->derivedStatus($r) === $status));
    }

    public function detail(int $id): ?array
    {
        $m = $this->find($id);
        if (!$m) return null;
        $m['membership'] = $this->queryOne(
            'SELECT ms.*, mp.name AS paket, mp.tier FROM memberships ms
             JOIN membership_packages mp ON mp.id = ms.package_id
             WHERE ms.member_id = ? ORDER BY ms.end_date DESC LIMIT 1', [$id]);
        $m['visits'] = $this->query(
            'SELECT v.*, u.name AS officer FROM member_visits v LEFT JOIN users u ON u.id = v.officer_id
             WHERE v.member_id = ? ORDER BY v.visit_date DESC LIMIT 10', [$id]);
        $m['payments'] = $this->query(
            'SELECT t.number, t.created_at, t.amount, t.payment_method FROM transactions t
             WHERE t.member_id = ? ORDER BY t.created_at DESC LIMIT 10', [$id]);
        return $m;
    }

    public function derivedStatus(array $row): string
    {
        if (empty($row['end_date'])) return 'Expired';
        if (($row['ms_status'] ?? '') === 'PENDING_PAYMENT') return 'Akan Expired';
        return status_membership($row['end_date'], (int) $this->expiringDays());
    }

    public function expiringDays(): int
    {
        $r = $this->queryOne("SELECT setting_value FROM settings WHERE setting_key = 'expiring_days'");
        return (int) ($r['setting_value'] ?? 7);
    }

    public function counts(): array
    {
        $rows = $this->listAll();
        $c = ['total' => count($rows), 'Aktif' => 0, 'Akan Expired' => 0, 'Expired' => 0];
        foreach ($rows as $r) $c[$this->derivedStatus($r)]++;
        return $c;
    }

    public function visitedToday(int $memberId, ?string $date = null): ?array
    {
        $date = $date ?: date('Y-m-d');
        return $this->queryOne('SELECT * FROM member_visits WHERE member_id = ? AND visit_date = ?', [$memberId, $date]);
    }

    public function checkin(int $memberId, string $area, int $officerId): array
    {
        $m = $this->find($memberId);
        if (!$m) return ['ok' => false, 'message' => 'QR Code tidak valid atau member tidak ditemukan.'];
        $ms = $this->queryOne('SELECT * FROM memberships WHERE member_id = ? ORDER BY end_date DESC, id DESC LIMIT 1', [$memberId]);
        if (!$ms || $ms['status'] === 'PENDING_PAYMENT') {
            return ['ok' => false, 'message' => 'Membership belum aktif (pembayaran belum lunas).'];
        }
        if ($ms['end_date'] < date('Y-m-d')) {
            return ['ok' => false, 'message' => 'Membership sudah expired pada ' . tanggal_indo($ms['end_date']) . '. Silakan perpanjang di kasir.'];
        }
        $done = $this->visitedToday($memberId);
        if ($done) {
            return ['ok' => false, 'message' => 'Member sudah melakukan kunjungan hari ini pada pukul ' . date('H:i', strtotime($done['check_in_at'])) . '.'];
        }
        // Aturan: Gold bebas kapan saja. Silver di hari libur kena biaya tambahan.
        $tier = strtoupper($ms['tier_snapshot'] ?? 'SILVER');
        $surcharge = 0.0;
        if ($tier !== 'GOLD' && is_libur(date('Y-m-d'))) {
            $surcharge = silver_holiday_fee();
        }
        try {
            $this->db->prepare('INSERT INTO member_visits (member_id, visit_date, check_in_at, area, officer_id) VALUES (?,?,?,?,?)')
                ->execute([$memberId, date('Y-m-d'), date('Y-m-d H:i:s'), $area, $officerId]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                return ['ok' => false, 'message' => 'Member sudah melakukan kunjungan hari ini.'];
            }
            throw $e;
        }
        return ['ok' => true, 'message' => 'Check-in berhasil.', 'surcharge' => $surcharge, 'tier' => $tier];
    }
}
