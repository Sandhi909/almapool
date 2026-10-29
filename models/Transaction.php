<?php
class Transaction extends Model
{
    protected string $table = 'transactions';

    public function generateNumber(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $row = $this->queryOne('SELECT number FROM transactions WHERE number LIKE ? ORDER BY number DESC LIMIT 1', [$prefix . '%']);
        $next = $row ? ((int) substr($row['number'], -4)) + 1 : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat transaksi + items. $items: [item_type, item_ref_id, item_name, tariff_day, detail, quantity, unit_price].
     * Harga disimpan sebagai snapshot.
     * $head boleh bawa 'pay' => ['TUNAI'=>x,'QRIS'=>y,'TRANSFER'=>z] untuk pembayaran gabungan.
     * Jika 'pay' tidak ada, diturunkan dari payment_method tunggal (seluruh total di 1 metode).
     */
    public function checkout(array $head, array $items): int
    {
        $total = 0.0;
        foreach ($items as $it) $total += (float) $it['quantity'] * (float) $it['unit_price'];
        $total = round($total, 2);
        $pay = $this->normalizePay($head['pay'] ?? null, $head['payment_method'] ?? 'TUNAI', $total);
        $this->db->beginTransaction();
        try {
            $id = $this->create([
                'number' => $this->generateNumber(),
                'type' => $head['type'],
                'member_id' => $head['member_id'] ?? null,
                'amount' => $total,
                'paid_amount' => $head['paid_amount'] ?? $total,
                'change_amount' => max(0, ($head['paid_amount'] ?? $total) - $total),
                'payment_method' => $head['payment_method'],
                'pay_tunai' => $pay['TUNAI'],
                'pay_qris' => $pay['QRIS'],
                'pay_transfer' => $pay['TRANSFER'],
                'pay_tagihan' => $pay['TAGIHAN'],
                'payment_status' => 'PAID',
                'cashier_id' => $head['cashier_id'],
                'area' => $head['area'] ?? 'PUTRA',
                'notes' => $head['notes'] ?? null,
            ]);
            // alokasi nomor seri per tiket (sequential)
            foreach ($items as &$it) {
                if (($it['item_type'] ?? '') === 'TIKET' && !empty($it['item_ref_id'])) {
                    $row = $this->db->query('SELECT serial_prefix, serial_next FROM ticket_types WHERE id='.(int)$it['item_ref_id'].' FOR UPDATE')->fetch();
                    if ($row) {
                        $qty = (int) max(1, round((float)$it['quantity']));
                        $start = (int) ($row['serial_next'] ?? 1);
                        $end = $start + $qty - 1;
                        $it['serial_prefix'] = $row['serial_prefix'] ?? '';
                        $it['serial_start'] = $start;
                        $it['serial_end'] = $end;
                        $this->db->prepare('UPDATE ticket_types SET serial_next=? WHERE id=?')->execute([$end+1, (int)$it['item_ref_id']]);
                    }
                }
            }
            unset($it);
            $st = $this->db->prepare('INSERT INTO transaction_items (transaction_id, item_type, item_ref_id, item_name, tariff_day, detail, quantity, unit_price, subtotal, serial_prefix, serial_start, serial_end) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            foreach ($items as $it) {
                $st->execute([$id, $it['item_type'], $it['item_ref_id'] ?? null, $it['item_name'],
                    $it['tariff_day'] ?? '-', $it['detail'] ?? null,
                    $it['quantity'], $it['unit_price'], (float) $it['quantity'] * (float) $it['unit_price'],
                    $it['serial_prefix'] ?? null, $it['serial_start'] ?? null, $it['serial_end'] ?? null]);
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Normalisasi rincian bayar per metode. Selalu mengembalikan
     * ['TUNAI'=>x,'QRIS'=>y,'TRANSFER'=>z] yang jumlahnya == $total.
     */
    public static function normalizePay($pay, string $method, float $total): array
    {
        $out = ['TUNAI' => 0.0, 'QRIS' => 0.0, 'TRANSFER' => 0.0, 'TAGIHAN' => 0.0];
        if (is_array($pay)) {
            foreach (['TUNAI', 'TAGIHAN', 'QRIS', 'TRANSFER'] as $m) {
                $out[$m] = max(0, round((float) ($pay[$m] ?? 0), 2));
            }
        } elseif (in_array($method, ['TUNAI', 'TAGIHAN', 'QRIS', 'TRANSFER'], true)) {
            $out[$method] = $total;
        } else {
        } else {
            $out['TUNAI'] = $total;
        }
        return $out;
    }

    /** Label ringkas rincian bayar untuk struk/tabel, mis. "Tunai 25rb + QRIS 25rb". */
    public static function payLabel(array $trx): string
    {
        if (($trx['payment_method'] ?? '') !== 'GABUNGAN') {
            $pm = (string) ($trx['payment_method'] ?? '-');
            return $pm === 'TAGIHAN' ? 'Tagihan' : $pm;
        }
        $parts = [];
        foreach ([['TUNAI', 'Tunai'], ['QRIS', 'QRIS'], ['TRANSFER', 'Transfer']] as [$k, $lbl]) {
            $v = (float) ($trx['pay_' . strtolower($k)] ?? 0);
            if ($v > 0) $parts[] = $lbl . ' ' . rupiah($v);
        }
        return $parts ? 'Gabungan (' . implode(' + ', $parts) . ')' : 'Gabungan';
    }

    public function listAll(array $filter = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filter['date'])) { $where[] = 'DATE(t.created_at) = ?'; $params[] = $filter['date']; }
        if (!empty($filter['type'])) { $where[] = 't.type = ?'; $params[] = $filter['type']; }
        if (!empty($filter['method'])) { $where[] = 't.payment_method = ?'; $params[] = $filter['method']; }
        if (!empty($filter['q'])) {
            $where[] = '(t.number LIKE ? OR t.notes LIKE ?)';
            $params[] = '%' . $filter['q'] . '%';
            $params[] = '%' . $filter['q'] . '%';
        }
        return $this->query(
            'SELECT t.*, u.name AS kasir, (SELECT GROUP_CONCAT(CONCAT(CASE WHEN ti.item_type="TIKET" THEN CONCAT(COALESCE(tt.color,"-"), " - ", TRIM(LEADING "Tiket " FROM ti.item_name)) WHEN ti.item_type="MEMBER" THEN ti.item_name ELSE ti.item_name END, IF(ti.detail IS NOT NULL AND ti.detail<>"-" AND ti.detail<>"", CONCAT(" (",ti.detail,")"), ""), " x", TRIM(TRAILING ".0" FROM ti.quantity)) SEPARATOR ", ") FROM transaction_items ti LEFT JOIN ticket_types tt ON tt.id=ti.item_ref_id WHERE ti.transaction_id=t.id) AS jenis_detail FROM transactions t LEFT JOIN users u ON u.id = t.cashier_id
             WHERE ' . implode(' AND ', $where) . ' ORDER BY t.created_at DESC LIMIT 200', $params);
    }

    public function items(int $id): array
    {
        return $this->query('SELECT * FROM transaction_items WHERE transaction_id = ? ORDER BY id', [$id]);
    }

    public function todaySummary(?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        $r = $this->queryOne("SELECT COUNT(*) n, COALESCE(SUM(amount),0) t FROM transactions WHERE DATE(created_at) = ? AND payment_status = 'PAID'", [$date]);
        // Split-aware: transaksi GABUNGAN dipecah ke tiap kolom metodenya.
        $m = $this->queryOne(
            "SELECT
                COALESCE(SUM(CASE WHEN payment_method IN ('TUNAI','TAGIHAN') THEN amount WHEN payment_method = 'GABUNGAN' THEN pay_tunai + pay_tagihan ELSE 0 END),0) tunai,
                COALESCE(SUM(CASE WHEN payment_method = 'QRIS' THEN amount WHEN payment_method = 'GABUNGAN' THEN pay_qris ELSE 0 END),0) qris,
                COALESCE(SUM(CASE WHEN payment_method = 'TRANSFER' THEN amount WHEN payment_method = 'GABUNGAN' THEN pay_transfer ELSE 0 END),0) transfer
              FROM transactions WHERE DATE(created_at) = ? AND payment_status = 'PAID'", [$date]);
        $by = ['TUNAI' => (float) ($m['tunai'] ?? 0), 'QRIS' => (float) ($m['qris'] ?? 0), 'TRANSFER' => (float) ($m['transfer'] ?? 0)];
        return ['count' => (int) $r['n'], 'total' => (float) $r['t'], 'by' => $by];
    }
}
