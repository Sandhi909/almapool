<?php
class Tagihan extends Model
{
    protected string $table = 'transactions';

    /**
     * Daftar transaksi tagihan:
     * - transaksi yang mengandung tiket bertanda is_tagihan (walaupun dibayar tunai)
     * - transaksi yang punya nominal tagihan (pay_tagihan > 0)
     */
    public function listAll(array $filter = []): array
    {
        $where = ["t.payment_status = 'PAID'", '(x.is_tagihan_ticket = 1 OR t.pay_tagihan > 0)'];
        $params = [];
        if (!empty($filter['from'])) { $where[] = 'DATE(t.created_at) >= ?'; $params[] = $filter['from']; }
        if (!empty($filter['to'])) { $where[] = 'DATE(t.created_at) <= ?'; $params[] = $filter['to']; }
        if (!empty($filter['q'])) {
            $where[] = '(t.number LIKE ? OR u.name LIKE ? OR x.items LIKE ?)';
            $like = '%' . $filter['q'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        return $this->query(
            "SELECT t.*, u.name AS kasir, x.items, x.nilai_tiket, x.is_tagihan_ticket,
                    CASE WHEN t.pay_tagihan > 0 THEN t.pay_tagihan ELSE t.amount END AS tagihan_nilai
             FROM transactions t
             JOIN (
                 SELECT ti.transaction_id,
                        GROUP_CONCAT(DISTINCT ti.item_name SEPARATOR ', ') AS items,
                        COALESCE(SUM(CASE WHEN tt.is_tagihan = 1 THEN ti.subtotal ELSE 0 END), 0) AS nilai_tiket,
                        MAX(CASE WHEN tt.is_tagihan = 1 THEN 1 ELSE 0 END) AS is_tagihan_ticket
                 FROM transaction_items ti
                 LEFT JOIN ticket_types tt ON tt.id = ti.item_ref_id AND ti.item_type = 'TIKET'
                 GROUP BY ti.transaction_id
             ) x ON x.transaction_id = t.id
             LEFT JOIN users u ON u.id = t.cashier_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY t.created_at DESC, t.id DESC
             LIMIT 500",
            $params);
    }

    public function summary(array $filter = []): array
    {
        $rows = $this->listAll($filter);
        $out = ['count' => count($rows), 'nilai_tiket' => 0.0, 'tagihan' => 0.0, 'total' => 0.0];
        foreach ($rows as $r) {
            $out['nilai_tiket'] += (float) ($r['nilai_tiket'] ?? 0);
            $out['tagihan'] += (float) ($r['tagihan_nilai'] ?? 0);
            $out['total'] += (float) ($r['amount'] ?? 0);
        }
        return $out;
    }
}
