<?php
// Read-only pages untuk role keuangan.
class KeuanganController
{
    /** Daftar transaksi tagihan (tiket tagihan + transaksi dengan nominal tagihan). */
    public function tagihan(): array
    {
        require_role(['admin', 'keuangan']);
        $from = (string) get_param('dari', date('Y-m-01'));
        $to = (string) get_param('sampai', date('Y-m-d'));
        if ($from > $to) [$from, $to] = [$to, $from];
        $filter = ['from' => $from, 'to' => $to, 'q' => (string) get_param('q', '')];
        return [
            'ROWS' => (new Tagihan())->listAll($filter),
            'FILTER' => $filter,
            'SUM' => (new Tagihan())->summary($filter),
        ];
    }

    /** Daftar biaya operasional (read-only). */
    public function biaya(): array
    {
        require_role(['admin', 'keuangan']);
        $year = max(2020, min(2100, (int) get_param('tahun', date('Y'))));
        $month = max(1, min(12, (int) get_param('bulan', date('n'))));
        $rows = (new OpCost())->monthly($year, $month);
        return [
            'ROWS' => $rows,
            'YEAR' => $year,
            'MONTH' => $month,
            'TOTAL' => array_sum(array_map(fn ($r) => (float) $r['amount'], $rows)),
        ];
    }
}
