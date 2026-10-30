<?php
class StokOpnameController
{
    public function index(): array
    {
        require_role(['internal_control']);
        $date = (string) get_param('tgl', date('Y-m-d'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $this->handlePost($date);
        }

        // Tunai masuk hari ini dari transaksi
        $sum = (new Transaction())->todaySummary($date);
        $tunaiMasuk = $sum['by']['TUNAI'] ?? 0;
        $qrisMasuk = $sum['by']['QRIS'] ?? 0;
        $transferMasuk = $sum['by']['TRANSFER'] ?? 0;
        $tagihanMasuk = 0; // tagihan counted in tunai, but show separately if needed

        // Biaya operasional hari ini
        $biayaHarian = (new OpCost())->totalDaily($date);

        // Existing record for this date
        $existing = (new StokOpname())->byDate($date);

        return [
            'date' => $date,
            'sum' => $sum,
            'tunaiMasuk' => $tunaiMasuk,
            'qrisMasuk' => $qrisMasuk,
            'transferMasuk' => $transferMasuk,
            'biayaHarian' => $biayaHarian,
            'existing' => $existing,
            'recent' => (new StokOpname())->recent(10),
            'LAPORAN' => ['periode' => date('F Y')],
        ];
    }

    private function handlePost(string $date): void
    {
        $tunaiReal = to_money(post('tunai_real', 0));
        $qris = to_money(post('qris', 0));
        $transfer = to_money(post('transfer', 0));
        $tagihan = to_money(post('tagihan', 0));
        $biayaKeluar = to_money(post('biaya_keluar', 0));
        $total = $tunaiReal + $qris + $transfer + $tagihan - $biayaKeluar;
        $notes = post('notes') ?: null;

        $existing = (new StokOpname())->byDate($date);
        $data = [
            'opname_date' => $date,
            'tunai_masuk' => to_money(post('tunai_masuk', 0)),
            'tunai_real' => $tunaiReal,
            'qris' => $qris,
            'transfer' => $transfer,
            'tagihan' => $tagihan,
            'biaya_keluar' => $biayaKeluar,
            'total' => $total,
            'notes' => $notes,
            'created_by' => auth_user() ? (int) auth_user()['id'] : null,
        ];

        if ($existing) {
            (new StokOpname())->update($existing['id'], $data);
            flash('success', 'Stok opname diperbarui.');
        } else {
            (new StokOpname())->create($data);
            flash('success', 'Stok opname dicatat.');
        }
        redirect('?page=stok-opname&tgl=' . urlencode($date));
    }
}
?>