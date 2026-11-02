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

    /** Data transaksi keseluruhan (read-only, dengan filter & pagination). */
    public function transaksi(): array
    {
        require_role(['admin', 'keuangan']);
        $from = trim((string) get_param('dari', ''));
        $to = trim((string) get_param('sampai', ''));
        if ($from !== '' && $to !== '' && $from > $to) [$from, $to] = [$to, $from];
        $filter = [
            'from' => $from,
            'to' => $to,
            'type' => (string) get_param('jenis', ''),
            'method' => (string) get_param('metode', ''),
            'status' => (string) get_param('status', ''),
            'q' => trim((string) get_param('q', '')),
        ];
        if (!in_array($filter['type'], ['TIKET', 'MEMBER', 'LES', 'SEWA', 'TAMBAHAN'], true)) $filter['type'] = '';
        if (!in_array($filter['method'], ['TUNAI', 'QRIS', 'TRANSFER', 'GABUNGAN', 'TAGIHAN'], true)) $filter['method'] = '';
        if (!in_array($filter['status'], ['PAID', 'CANCELLED'], true)) $filter['status'] = '';

        $perPage = 50;
        $total = (new Transaction())->countRange($filter);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($pages, (int) get_param('hal', 1)));
        return [
            'ROWS' => (new Transaction())->listRange($filter, $perPage, ($page - 1) * $perPage),
            'FILTER' => $filter,
            'SUM' => (new Transaction())->summaryRange($filter),
            'PAGE' => $page,
            'PAGES' => $pages,
            'PER_PAGE' => $perPage,
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
