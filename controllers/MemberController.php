<?php
class MemberController
{
    public function index(): array
    {
        require_role(['admin', 'kasir', 'manajemen']);
        $m = new Member();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $action = (string) post('action', '');
            if ($action === 'update') {
                $this->update($m);
            } else {
                $this->store($m);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && (string) get_param('action') === 'delete') {
            $this->delete($m, (int) get_param('id', 0));
        }
        $rows = $m->listAll();
        // ambil ringkasan kunjungan per member
        $visitMap = [];
        try {
            $vs = db()->query('SELECT member_id, COUNT(*) cnt, MAX(visit_date) last_date, MAX(check_in_at) last_at FROM member_visits GROUP BY member_id')->fetchAll();
            foreach ($vs as $v) $visitMap[(int)$v['member_id']] = $v;
        } catch (Throwable $e) {}
        foreach ($rows as &$r) {
            $r['status'] = $m->derivedStatus($r);
            $r['sisa'] = $r['end_date'] ? ('s/d ' . tanggal_indo($r['end_date'])) : '-';
            $vm = $visitMap[(int)$r['id']] ?? null;
            $r['total_kunjungan'] = $vm ? (int)$vm['cnt'] : 0;
            $r['kunjungan_terakhir'] = $vm ? $vm['last_at'] : null;
            $r['kunjungan_today'] = $vm && $vm['last_date'] === date('Y-m-d') ? 1 : 0;
        }
        // riwayat kunjungan terbaru untuk tabel bawah (opsional)
        $recentVisits = [];
        try {
            $recentVisits = db()->query('SELECT v.*, m.name, m.member_number FROM member_visits v JOIN members m ON m.id=v.member_id ORDER BY v.check_in_at DESC LIMIT 20')->fetchAll();
        } catch (Throwable $e) {}
        return ['MEMBERS' => $rows, 'STAT' => $this->stats($m), 'PAKET_MEMBER' => (new MembershipPackage())->matrix(), 'RECENT_VISITS' => $recentVisits];
    }

    private function stats(Member $m): array
    {
        $c = $m->counts();
        return ['total_member' => $c['total'], 'member_aktif' => $c['Aktif'],
            'akan_expired' => $c['Akan Expired'], 'expired' => $c['Expired']];
    }

    private function update(Member $m): void
    {
        $id = (int) post('member_id', 0);
        $nama = (string) post('nama', '');
        $paketId = (int) post('package_id', 0);
        if (!$id || $nama === '' || !$paketId) {
            flash('error', 'Member ID, nama, dan paket wajib diisi.');
            redirect('?page=member');
        }
        $member = $m->find($id);
        if (!$member) {
            flash('error', 'Member tidak ditemukan.');
            redirect('?page=member');
        }
        $m->update($id, [
            'name' => $nama, 'identity_number' => post('nik') ?: null,
            'phone' => post('hp') ?: null, 'gender' => in_array(post('gender'), ['L', 'P'], true) ? post('gender') : null,
            'birth_date' => post('tgl_lahir') ?: null, 'address' => post('alamat') ?: null,
            'notes' => post('catatan') ?: null,
        ]);
        $pkgRow = (new MembershipPackage())->find($paketId);
        db()->prepare('UPDATE memberships SET package_id=?, package_name_snapshot=?, tier_snapshot=?, price_snapshot=? WHERE member_id=? AND status="PENDING_PAYMENT"')
            ->execute([$paketId, $pkgRow['name'] ?? '', $pkgRow['tier'] ?? 'GOLD', $pkgRow['price'] ?? 0, $id]);
        flash('success', 'Member ' . $member['member_number'] . ' diperbarui.');
        redirect('?page=member');
    }

    private function delete(Member $m, int $id): void
    {
        require_role(['admin', 'manajemen']);
        $member = $m->find($id);
        if (!$member) {
            flash('error', 'Member tidak ditemukan.');
            redirect('?page=member');
        }
        $pdo = db();
        try {
            $pdo->beginTransaction();
            // transaction_items ON DELETE CASCADE, tapi hapus eksplisit agar aman
            $pdo->prepare('DELETE ti FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE t.member_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM transactions WHERE member_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM member_visits WHERE member_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM memberships WHERE member_id = ?')->execute([$id]);
            $m->delete($id);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', 'Gagal hapus member: ' . $e->getMessage());
            redirect('?page=member');
        }
        flash('success', 'Member ' . $member['member_number'] . ' dihapus.');
        redirect('?page=member');
    }

    private function store(Member $m): void
    {
        $nama = (string) post('nama', '');
        $paketId = (int) post('package_id', 0);
        if ($nama === '' || !$paketId) {
            flash('error', 'Nama dan paket wajib diisi.');
            redirect('?page=member');
        }
        $pkg = (new MembershipPackage())->find($paketId);
        if (!$pkg) {
            flash('error', 'Paket tidak valid.');
            redirect('?page=member');
        }
        $number = $m->generateNumber();
        $id = $m->create([
            'member_number' => $number, 'name' => $nama,
            'identity_number' => post('nik') ?: null, 'phone' => post('hp') ?: null,
            'gender' => in_array(post('gender'), ['L', 'P'], true) ? post('gender') : null,
            'birth_date' => post('tgl_lahir') ?: null, 'address' => post('alamat') ?: null,
            'qr_identifier' => $number, 'notes' => post('catatan') ?: null,
        ]);
        $start = post('tgl_mulai') ?: date('Y-m-d');
        // Aturan: tanggal ke tanggal kalender (daftar 7 Okt paket Bulanan -> habis 7 Nov,
        // paket Tahunan 7 Okt 2025 -> habis 7 Okt 2026). Lihat membership_end_date().
        $end = membership_end_date($start, (int) $pkg['duration_days'], (string) ($pkg['name'] ?? ''));
        $this->db($m)->prepare(
            'INSERT INTO memberships (member_id, package_id, package_name_snapshot, tier_snapshot, price_snapshot, start_date, end_date, status)
             VALUES (?,?,?,?,?,?,?,?)')->execute([$id, $pkg['id'], $pkg['name'], $pkg['tier'], $pkg['price'], $start, $end, 'PENDING_PAYMENT']);
        flash('success', 'Member ' . $number . ' dibuat. Lanjutkan pembayaran di kasir (tab Member).');
        redirect('?page=member');
    }

    private function db(Member $m): PDO
    {
        return db();
    }

    public function detail(int $id): ?array
    {
        require_role(['admin', 'kasir', 'manajemen']);
        return (new Member())->detail($id);
    }

    /** AJAX: cari member untuk kasir. */
    public function search(): void
    {
        require_role(['admin', 'kasir']);
        $q = (string) get_param('q', '');
        $rows = $q === '' ? [] : (new Member())->search($q);
        json_response(['results' => array_map(fn ($r) => [
            'id' => $r['id'], 'nomor' => $r['member_number'], 'nama' => $r['name'],
            'paket' => trim(($r['paket'] ?? '') . ' ' . ($r['tier'] ?? '')),
            'berakhir' => $r['end_date'] ? tanggal_indo($r['end_date']) : '-',
        ], $rows)]);
    }

    /** Kartu member: preview + cetak. */
    public function card(int $id): ?array
    {
        require_role(['admin', 'kasir']);
        $m = (new Member())->detail($id);
        if (!$m) {
            http_response_code(404);
            die('Member tidak ditemukan.');
        }
        return ['m' => $m, 'settings' => (new Setting())->allAsMap()];
    }
}
