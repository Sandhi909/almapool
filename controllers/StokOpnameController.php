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

        // Biaya operasional hari ini
        $biayaHarian = (new OpCost())->totalDaily($date);

        // Existing record for this date
        $existing = (new StokOpname())->byDate($date);
        $agg = $this->aggregate($date);

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
            'AGG' => $agg,
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

        // KAS (input internal control)
        $kasDiKantor = to_money(post('kas_di_kantor', 0));
        $buktiSetor = to_money(post('bukti_setor', 0));
        $transferYab = to_money(post('transfer_yab', 0));
        $qrisKolam = to_money(post('qris_kolam', 0));
        $sewaQris = to_money(post('sewa_qris', 0));

        $existing = (new StokOpname())->byDate($date);
        $data = [
            'opname_date' => $date,
            'tunai_masuk' => to_money(post('tunai_masuk', 0)),
            'tunai_real' => $tunaiReal,
            'qris' => $qris,
            'transfer' => $transfer,
            'tagihan' => $tagihan,
            'biaya_keluar' => $biayaKeluar,
            'kas_di_kantor' => $kasDiKantor,
            'bukti_setor' => $buktiSetor,
            'transfer_yab' => $transferYab,
            'qris_kolam' => $qrisKolam,
            'sewa_qris' => $sewaQris,
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

    /**
     * Kumpulkan data yang dipakai halaman SO dan berkas Excel.
     * Semua angka dihitung dari transaksi pada tanggal tertentu (sama seperti laporan).
     */
    private function aggregate(string $date): array
    {
        $settings = (new Setting())->allAsMap();
        $rep = (new Report())->daily($date);
        $so = (new StokOpname())->byDate($date);

        // Tiket per warna
        $colors = [];
        foreach ((new TicketType())->active() as $t) {
            $color = trim((string) ($t['color'] ?? ''));
            if ($color === '' || $color === '-') continue;
            if (!isset($colors[$color])) {
                $colors[$color] = [
                    'label' => strtoupper($color),
                    'pa' => (float) $t['price_a'], 'pb' => (float) $t['price_b'], 'pc' => (float) $t['price_c'],
                    'qa' => 0.0, 'qb' => 0.0, 'qc' => 0.0, 'total' => 0.0,
                    'seri_min' => null, 'seri_max' => null, 'qty' => 0.0,
                ];
            }
        }
        foreach ($rep['tickets'] as $r) {
            $color = trim((string) ($r['color'] ?? ''));
            if (!isset($colors[$color])) continue;
            $qa = (float) $r['qa']; $qb = (float) $r['qb']; $qc = (float) $r['qc'];
            $b = &$colors[$color];
            $b['qa'] += $qa; $b['qb'] += $qb; $b['qc'] += $qc;
            $b['qty'] += $qa + $qb + $qc;
            $b['total'] += (float) $r['subtotal'];
            if ($r['seri_awal'] !== null && $r['seri_akhir'] !== null) {
                $b['seri_min'] = $b['seri_min'] === null ? (int) $r['seri_awal'] : min($b['seri_min'], (int) $r['seri_awal']);
                $b['seri_max'] = $b['seri_max'] === null ? (int) $r['seri_akhir'] : max($b['seri_max'], (int) $r['seri_akhir']);
            }
            unset($b);
        }
        $ticketTotal = array_sum(array_column($colors, 'total'));

        // Member Gold/Silver
        $pkgMatrix = (new MembershipPackage())->matrix();
        $repMembers = [];
        foreach ($rep['members'] as $m) $repMembers[$m['jenis']] = $m;
        $memberRows = [
            ['Bulanan', 'Bulanan'],
            ["- Siswa Al Ma'soem", 'Siswa Al Ma’soem'],
            ['- Karyawan', 'Karyawan'],
            ['Tahunan', 'Tahunan'],
        ];
        $memberTotal = 0.0;
        $members = [];
        foreach ($memberRows as [$label, $key]) {
            $m = $repMembers[$key] ?? ['qg' => 0, 'qs' => 0, 'jg' => 0, 'js' => 0];
            $g = $pkgMatrix[$key]['GOLD'] ?? null;
            $s = $pkgMatrix[$key]['SILVER'] ?? null;
            $jg = (float) $m['jg']; $js = (float) $m['js'];
            $memberTotal += $jg + $js;
            $members[] = [
                'label' => $label,
                'gt' => (float) ($g['price'] ?? 0), 'gq' => (float) $m['qg'], 'gj' => $jg,
                'st' => (float) ($s['price'] ?? 0), 'sq' => (float) $m['qs'], 'sj' => $js,
                'total' => $jg + $js,
            ];
        }

        $lesTotal = 0.0;
        foreach ($rep['les'] as $l) $lesTotal += (float) $l['jpra'] + (float) $l['jpri'];
        $sewa = (float) $rep['sewa'];
        $setoran = $ticketTotal + $memberTotal + $lesTotal + $sewa;

        $biaya = $rep['biaya'];
        $biayaTotal = array_sum(array_map(fn ($b) => (float) $b['jumlah'], $biaya));

        $kas = [
            'kas_di_kantor' => $so ? (float) $so['kas_di_kantor'] : 0.0,
            'bukti_setor' => $so ? (float) $so['bukti_setor'] : 0.0,
            'transfer_yab' => $so ? (float) $so['transfer_yab'] : 0.0,
            'qris_kolam' => $so ? (float) $so['qris_kolam'] : 0.0,
            'sewa_qris' => $so ? (float) $so['sewa_qris'] : 0.0,
        ];
        $selisih = $setoran - $biayaTotal - array_sum($kas);

        return [
            'settings' => $settings,
            'colors' => $colors,
            'ticketTotal' => $ticketTotal,
            'members' => $members,
            'memberTotal' => $memberTotal,
            'lesTotal' => $lesTotal,
            'sewa' => $sewa,
            'setoran' => $setoran,
            'biaya' => $biaya,
            'biayaTotal' => $biayaTotal,
            'kas' => $kas,
            'selisih' => $selisih,
            'notes' => $so ? (string) ($so['notes'] ?? '') : '',
        ];
    }

    /**
     * Unduh hasil Stok Opname sebagai XLSX (format SO Kolam YAB).
     * Dipanggil via index.php?page=stok-opname&ajax=export&tgl=YYYY-MM-DD
     */
    public function export(): void
    {
        require_role(['internal_control']);
        $date = (string) get_param('tgl', date('Y-m-d'));
        $a = $this->aggregate($date);

        $kolam = trim((string) ($a['settings']['nama_kolam'] ?? "KOLAM RENANG AL MA'SOEM"));
        $periode = date('l j F Y', strtotime($date));

        $x = new Xlsx('SO');
        $x->width(1, 5.7); $x->width(2, 39.7); $x->width(3, 16); $x->width(4, 18.4);
        $x->width(5, 16); $x->width(6, 15.7); $x->width(7, 11.6); $x->width(8, 15.7);
        $x->width(9, 23.4); $x->width(10, 12.3);

        $r = 3;
        $x->merge($r, 1, $r, 10);
        $x->text($r, 1, $kolam . ' BANDUNG', Xlsx::S_TITLE);
        $r = 4;
        $x->merge($r, 1, $r, 10);
        $x->text($r, 1, 'Periode ' . $periode, Xlsx::S_SUBTITLE);
        $r = 5;
        $x->merge($r, 1, $r, 10);
        $x->text($r, 1, 'Bissmillahirrahmanirrahim', Xlsx::S_BISMILLAH);
        $r = 7;
        $x->merge($r, 2, $r, 9);
        $x->text($r, 2, 'Pada hari Periode ' . $periode . ' telah dilakukan Stock Opname di ' . $kolam . ' BANDUNG dengan rincian sebagai berikut :', Xlsx::S_WRAP);

        // PENERIMAAN
        $r = 9;
        $x->merge($r, 2, $r, 9);
        $x->text($r, 2, 'PENERIMAAN', Xlsx::S_SECTION);

        $r = 10;
        $x->text($r, 2, 'TIKET', Xlsx::S_HEADER);
        $x->text($r, 3, 'JUMLAH', Xlsx::S_HEADER);
        $x->text($r, 4, 'TARIF A', Xlsx::S_HEADER);
        $x->text($r, 5, 'JUMLAH', Xlsx::S_HEADER);
        $x->text($r, 6, 'TARIF B', Xlsx::S_HEADER);
        $x->text($r, 7, 'JUMLAH', Xlsx::S_HEADER);
        $x->text($r, 8, 'TARIF C', Xlsx::S_HEADER);
        $x->text($r, 9, 'TOTAL', Xlsx::S_HEADER);

        $r = 11;
        foreach ($a['colors'] as $b) {
            $x->text($r, 2, $b['label'], Xlsx::S_LABEL);
            $x->cell($r, 3, $b['qa'], Xlsx::S_CENTER);
            $x->cell($r, 4, $b['pa'], Xlsx::S_NUM);
            $x->cell($r, 5, $b['qb'], Xlsx::S_CENTER);
            $x->cell($r, 6, $b['pb'], Xlsx::S_NUM);
            $x->cell($r, 7, $b['qc'], Xlsx::S_CENTER);
            $x->cell($r, 8, $b['pc'], Xlsx::S_NUM);
            $x->cell($r, 9, $b['total'], Xlsx::S_NUM_BOLD);
            $r++;
        }
        $x->merge($r, 2, $r, 8);
        $x->text($r, 2, 'Total Penerimaan', Xlsx::S_TOTAL_LABEL);
        $x->cell($r, 9, $a['ticketTotal'], Xlsx::S_TOTAL_NUM);
        $r += 2;

        // Member
        $x->text($r, 2, 'Jenis Member', Xlsx::S_LABEL_BOLD);
        $x->merge($r, 3, $r, 5);
        $x->text($r, 3, 'Gold', Xlsx::S_CENTER_BOLD);
        $x->merge($r, 6, $r, 8);
        $x->text($r, 6, 'Silver', Xlsx::S_CENTER_BOLD);
        $x->merge($r, 9, $r, 10);
        $x->text($r, 9, 'Jumlah (Rp)', Xlsx::S_CENTER_BOLD);
        $r++;
        $x->text($r, 2, '', Xlsx::S_LABEL);
        $x->text($r, 3, 'Tarif (Rp)', Xlsx::S_HEADER);
        $x->text($r, 4, 'Qty', Xlsx::S_HEADER);
        $x->text($r, 5, 'Jumlah', Xlsx::S_HEADER);
        $x->text($r, 6, 'Tarif (Rp)', Xlsx::S_HEADER);
        $x->text($r, 7, 'Qty', Xlsx::S_HEADER);
        $x->text($r, 8, 'Jumlah', Xlsx::S_HEADER);
        $r++;
        foreach ($a['members'] as $m) {
            $x->text($r, 2, $m['label'], Xlsx::S_LABEL);
            $x->cell($r, 3, $m['gt'], Xlsx::S_NUM);
            $x->cell($r, 4, $m['gq'], Xlsx::S_CENTER);
            $x->cell($r, 5, $m['gj'], Xlsx::S_NUM);
            $x->cell($r, 6, $m['st'], Xlsx::S_NUM);
            $x->cell($r, 7, $m['sq'], Xlsx::S_CENTER);
            $x->cell($r, 8, $m['sj'], Xlsx::S_NUM);
            $x->cell($r, 9, $m['total'], Xlsx::S_NUM);
            $r++;
        }
        $x->text($r, 2, 'Total Penerimaan dari Member', Xlsx::S_TOTAL_LABEL);
        $x->cell($r, 4, 0, Xlsx::S_TOTAL_NUM);
        $x->cell($r, 5, 0, Xlsx::S_TOTAL_NUM);
        $x->cell($r, 7, 0, Xlsx::S_TOTAL_NUM);
        $x->cell($r, 8, 0, Xlsx::S_TOTAL_NUM);
        $x->cell($r, 9, $a['memberTotal'], Xlsx::S_TOTAL_NUM);
        $r++;
        $x->merge($r, 2, $r, 7);
        $x->text($r, 2, 'Total Penerimaan', Xlsx::S_TOTAL_LABEL);
        $x->cell($r, 8, 0, Xlsx::S_TOTAL_NUM);
        $x->cell($r, 9, $a['ticketTotal'] + $a['memberTotal'], Xlsx::S_TOTAL_NUM);
        $r++;
        $x->text($r, 2, 'LES Privat', Xlsx::S_LABEL);
        $x->cell($r, 9, $a['lesTotal'], Xlsx::S_NUM);
        $r++;
        $x->text($r, 2, 'Sewa Alat Renang', Xlsx::S_LABEL);
        $x->cell($r, 9, $a['sewa'], Xlsx::S_NUM);
        $r++;
        $x->merge($r, 2, $r, 8);
        $x->text($r, 2, 'Total Uang yang di setorkan kepada Keuangan YAB', Xlsx::S_TOTAL_LABEL);
        $x->cell($r, 9, $a['setoran'], Xlsx::S_TOTAL_NUM);
        $r += 2;

        // PENGELUARAN
        $x->merge($r, 2, $r, 4);
        $x->text($r, 2, 'PENGELUARAN', Xlsx::S_SECTION);
        $r++;
        $x->merge($r, 2, $r, 4);
        $x->text($r, 2, 'BIAYA OPERASIONAL', Xlsx::S_LABEL_BOLD);
        $r++;
        foreach ($a['biaya'] as $b) {
            $x->text($r, 2, (string) $b['nama'], Xlsx::S_LABEL);
            $x->merge($r, 3, $r, 4);
            $x->cell($r, 3, (float) $b['jumlah'], Xlsx::S_NUM);
            $r++;
        }
        $x->text($r, 2, 'Total Biaya Oprasional', Xlsx::S_TOTAL_LABEL);
        $x->merge($r, 3, $r, 4);
        $x->cell($r, 3, $a['biayaTotal'], Xlsx::S_TOTAL_NUM);
        $r += 2;

        // KAS
        $x->merge($r, 2, $r, 4);
        $x->text($r, 2, 'KAS', Xlsx::S_SECTION);
        $r++;
        $kasRows = [
            ['KAS YANG ADA DI KANTOR', $a['kas']['kas_di_kantor']],
            ['Bukti setor ke keuangan', $a['kas']['bukti_setor']],
            ['Transfer rekening YAB', $a['kas']['transfer_yab']],
            ['Qris Kolam', $a['kas']['qris_kolam']],
            ['sewa alat di qris', $a['kas']['sewa_qris']],
            ['* SELISIH', $a['selisih']],
        ];
        foreach ($kasRows as [$label, $val]) {
            $x->text($r, 2, $label, Xlsx::S_LABEL);
            $x->merge($r, 3, $r, 4);
            $x->cell($r, 3, $val, $label === '* SELISIH' ? Xlsx::S_NUM_BOLD : Xlsx::S_NUM);
            $r++;
        }
        $r += 2;

        // NO TIKET KELUAR
        $x->merge($r, 2, $r, 10);
        $x->text($r, 2, 'NO TIKET KELUAR', Xlsx::S_SECTION);
        $r++;
        $x->text($r, 2, 'WARNA', Xlsx::S_HEADER);
        $x->text($r, 3, 'AWAL', Xlsx::S_HEADER);
        $x->text($r, 4, 'AKHIR', Xlsx::S_HEADER);
        $x->text($r, 5, 'JUMLAH', Xlsx::S_HEADER);
        $x->text($r, 6, 'Hasil Stock', Xlsx::S_HEADER);
        $x->text($r, 7, 'Selisih', Xlsx::S_HEADER);
        $r++;
        $totalQty = 0.0;
        foreach ($a['colors'] as $b) {
            $x->text($r, 2, $b['label'], Xlsx::S_LABEL);
            $x->cell($r, 3, $b['seri_min'] ?? '', Xlsx::S_CENTER);
            $x->cell($r, 4, $b['seri_max'] ?? '', Xlsx::S_CENTER);
            $x->cell($r, 5, $b['qty'], Xlsx::S_CENTER);
            $x->cell($r, 6, '', Xlsx::S_CENTER);
            $x->cell($r, 7, '', Xlsx::S_CENTER);
            $totalQty += $b['qty'];
            $r++;
        }
        $x->text($r, 2, 'TOTAL', Xlsx::S_TOTAL_LABEL);
        $x->cell($r, 5, $totalQty, Xlsx::S_TOTAL_NUM);
        $r += 2;

        // Catatan
        $x->text($r, 2, 'Catatan :', Xlsx::S_LABEL_BOLD);
        $r++;
        $noteLines = $a['notes'] !== '' ? preg_split('/\r\n|\r|\n/', $a['notes']) : ['*', '*', '*', '*', '*', '*'];
        foreach ($noteLines as $line) {
            $x->merge($r, 2, $r, 9);
            $x->text($r, 2, $line, Xlsx::S_TEXT);
            $r++;
        }
        $r++;

        // TTD
        $x->text($r, 5, 'Bandung,', Xlsx::S_CENTER);
        $x->text($r, 6, 'Periode ' . $periode, Xlsx::S_CENTER);
        $r++;
        $x->text($r, 2, "Mjr. Kolam Renang Al Ma'soem", Xlsx::S_CENTER);
        $x->text($r, 5, "Staf Administrasi Kolam Renang Al Ma'soem", Xlsx::S_CENTER);
        $r += 2;
        $x->text($r, 2, 'Internal Control', Xlsx::S_CENTER);
        $x->text($r + 2, 10, 'Hal 1/1', Xlsx::S_CENTER);

        $bulan = bulan_indo((int) date('n', strtotime($date)));
        $filename = "SO Kolam YAB " . $bulan . "'" . date('y', strtotime($date)) . '.xlsx';
        $x->stream($filename);
    }
}
