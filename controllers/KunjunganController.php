<?php
class KunjunganController
{
    public function index(): array
    {
        require_role(['admin', 'kasir', 'manajemen', 'petugas']);
        $type = (string) get_param('type', 'harian');
        if (!in_array($type, ['harian','mingguan','bulanan','rentang'], true)) $type='harian';

        if ($type === 'harian') {
            $date = (string) get_param('tanggal', date('Y-m-d'));
            $start = $end = $date;
            $periode = hari_tanggal_indo($date);
            $period = ['type'=>'harian','date'=>$date];
        } elseif ($type === 'mingguan') {
            $month = max(1, min(12, (int) get_param('bulan', date('n'))));
            $week = max(1, min(4, (int) get_param('minggu', current_week_of_month())));
            $year = max(2020, min(2100, (int) get_param('tahun', date('Y'))));
            [$start,$end] = weekOfMonthBounds($year,$month,$week);
            $periode = 'Minggu ke-'.$week.' '.bulan_indo($month).' '.$year.' ('.tanggal_indo($start).' - '.tanggal_indo($end).')';
            $period = ['type'=>'mingguan','year'=>$year,'month'=>$month,'week'=>$week,'start'=>$start,'end'=>$end];
        } elseif ($type === 'rentang') {
            $a = (string) get_param('tgl_awal', date('Y-m-d'));
            $b = (string) get_param('tgl_akhir', date('Y-m-d'));
            if ($a > $b) [$a,$b]=[$b,$a];
            $start=$a; $end=$b;
            $periode = tanggal_indo($start).' s/d '.tanggal_indo($end);
            $period = ['type'=>'rentang','tgl_awal'=>$start,'tgl_akhir'=>$end];
        } else {
            $month = max(1, min(12, (int) get_param('bulan', date('n'))));
            $year = max(2020, min(2100, (int) get_param('tahun', date('Y'))));
            $start = sprintf('%04d-%02d-01', $year, $month);
            $end = date('Y-m-t', strtotime($start));
            $periode = bulan_indo($month).' '.$year;
            $period = ['type'=>'bulanan','month'=>$month,'year'=>$year];
        }

        $rep = $this->aggregate($start,$end);

        return [
            'TYPE'=>$type,'PERIOD'=>$period,'PERIODE_LABEL'=>$periode,
            'START'=>$start,'END'=>$end,
            'DATA'=>$rep,
        ];
    }

    private function aggregate(string $start, string $end): array
    {
        $pdo = db();
        $tiketPutra = $tiketPutri = $tiketTotal = $rombonganPutra = $rombonganPutri = $rombonganTotal = 0;
        $internalTiketPutra = $internalTiketPutri = $internalTiketTotal = 0;
        $externalTiketPutra = $externalTiketPutri = $externalTiketTotal = 0;
        try {
            $rows = $pdo->prepare(
                "SELECT ti.detail, COALESCE(SUM(ti.quantity),0) q
                 FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id
                 WHERE ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI') AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN ? AND ?
                 GROUP BY ti.detail"
            );
            $rows->execute([$start,$end]);
            foreach ($rows->fetchAll() as $r) {
                $q = (float)$r['q'];
                if ($r['detail']==='PUTRA') $tiketPutra+=$q;
                elseif ($r['detail']==='PUTRI') $tiketPutri+=$q;
                $tiketTotal+=$q;
            }
            // internal tiket per area — berdasarkan flag is_internal di master ticket_types (fallback LIKE untuk data lama)
            $ir = $pdo->prepare(
                "SELECT ti.detail, COALESCE(SUM(ti.quantity),0) q
                 FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id
                 LEFT JOIN ticket_types tt ON tt.id = ti.item_ref_id
                 WHERE ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI')
                   AND (tt.is_internal = 1 OR (tt.id IS NULL AND (ti.item_name LIKE '%Karyawan%' OR ti.item_name LIKE '%Keluarga%' OR ti.item_name LIKE '%Siswa Al%' OR ti.item_name LIKE '%Santri%' OR ti.item_name LIKE '%Internal%')))
                   AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN ? AND ?
                 GROUP BY ti.detail"
            );
            $ir->execute([$start,$end]);
            foreach ($ir->fetchAll() as $r) {
                $q=(float)$r['q'];
                if ($r['detail']==='PUTRA') $internalTiketPutra+=$q;
                elseif ($r['detail']==='PUTRI') $internalTiketPutri+=$q;
                $internalTiketTotal+=$q;
            }
            // rombongan
            $rRows = $pdo->prepare(
                "SELECT ti.detail, COALESCE(SUM(ti.quantity),0) q
                 FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id
                 WHERE ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI') AND ti.item_name LIKE '%rombongan%' AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN ? AND ?
                 GROUP BY ti.detail"
            );
            $rRows->execute([$start,$end]);
            foreach ($rRows->fetchAll() as $r) {
                $q=(float)$r['q'];
                if ($r['detail']==='PUTRA') $rombonganPutra+=$q;
                elseif ($r['detail']==='PUTRI') $rombonganPutri+=$q;
                $rombonganTotal+=$q;
            }
            // rincian per jenis tiket (Umum, Santri, dll) per Putra/Putri
            $tiketRincian = [];
            try {
                $tr = $pdo->prepare(
                    "SELECT tt.id, tt.name, tt.color,
                            COALESCE(SUM(CASE WHEN ti.detail='PUTRA' THEN ti.quantity ELSE 0 END),0) as putra,
                            COALESCE(SUM(CASE WHEN ti.detail='PUTRI' THEN ti.quantity ELSE 0 END),0) as putri,
                            COALESCE(SUM(ti.quantity),0) as total
                     FROM ticket_types tt
                     LEFT JOIN transaction_items ti ON ti.item_ref_id=tt.id AND ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI')
                       AND ti.transaction_id IN (SELECT id FROM transactions WHERE payment_status='PAID' AND DATE(created_at) BETWEEN ? AND ?)
                     GROUP BY tt.id, tt.name, tt.color
                     ORDER BY tt.id"
                );
                $tr->execute([$start,$end]);
                $tiketRincian = $tr->fetchAll();
            } catch(Throwable $e){ $tiketRincian=[]; }

            // rincian jenjang dinamis dari master_jenjang
            try { $jenjangRows = $pdo->query("SELECT name FROM master_jenjang WHERE is_active=1 ORDER BY sort_order")->fetchAll(PDO::FETCH_COLUMN); } catch(Throwable $e){ $jenjangRows=[]; }
            $jenjangList = $jenjangRows ?: ['SD','SMP','SMA','Mahasiswa MU'];
            $jenjangTotal = array_fill_keys($jenjangList, 0);
            $jenjangPutra = array_fill_keys($jenjangList, 0);
            $jenjangPutri = array_fill_keys($jenjangList, 0);
            foreach ($jenjangList as $jen) {
                try {
                    $jr = $pdo->prepare(
                        "SELECT ti.detail, COALESCE(SUM(ti.quantity),0) q
                         FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id
                         WHERE ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI') AND ti.item_name LIKE ? AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN ? AND ?
                         GROUP BY ti.detail"
                    );
                    $like = '% - ' . $jen . '%';
                    $jr->execute([$like, $start, $end]);
                    foreach ($jr->fetchAll() as $r) {
                        $q=(float)$r['q'];
                        $jenjangTotal[$jen]+= $q;
                        if ($r['detail']==='PUTRA') $jenjangPutra[$jen]+= $q;
                        elseif ($r['detail']==='PUTRI') $jenjangPutri[$jen]+= $q;
                    }
                } catch (Throwable $e) {}
            }
            // rincian kelompok usia dinamis dari master_kelompok_usia (Anak-anak/Remaja/Dewasa)
            try { $usiaRows = $pdo->query("SELECT name FROM master_kelompok_usia WHERE is_active=1 ORDER BY sort_order")->fetchAll(PDO::FETCH_COLUMN); } catch(Throwable $e){ $usiaRows=[]; }
            $usiaList = $usiaRows ?: ['Anak-anak','Remaja','Dewasa'];
            $usiaTotal = array_fill_keys($usiaList, 0);
            $usiaPutra = array_fill_keys($usiaList, 0);
            $usiaPutri = array_fill_keys($usiaList, 0);
            foreach ($usiaList as $usia) {
                try {
                    $ur = $pdo->prepare(
                        "SELECT ti.detail, COALESCE(SUM(ti.quantity),0) q
                         FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id
                         WHERE ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI') AND ti.item_name LIKE ? AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN ? AND ?
                         GROUP BY ti.detail"
                    );
                    $like = '% - ' . $usia . '%';
                    $ur->execute([$like, $start, $end]);
                    foreach ($ur->fetchAll() as $r) {
                        $q=(float)$r['q'];
                        $usiaTotal[$usia]+= $q;
                        if ($r['detail']==='PUTRA') $usiaPutra[$usia]+= $q;
                        elseif ($r['detail']==='PUTRI') $usiaPutri[$usia]+= $q;
                    }
                } catch (Throwable $e) {}
            }
        } catch (Throwable $e) {}

        // Member visits split internal/external berdasar paket member
        $memberPutra = $memberPutri = $memberTotal = 0;
        $internalMemberPutra = $internalMemberPutri = $internalMemberTotal = 0;
        $externalMemberPutra = $externalMemberPutri = $externalMemberTotal = 0;
        try {
            $mRows = $pdo->prepare("SELECT area, COUNT(*) n FROM member_visits WHERE visit_date BETWEEN ? AND ? GROUP BY area");
            $mRows->execute([$start,$end]);
            foreach ($mRows->fetchAll() as $r) {
                $n=(int)$r['n'];
                if ($r['area']==='PUTRA') $memberPutra=$n;
                elseif ($r['area']==='PUTRI') $memberPutri=$n;
            }
            $memberTotal=$memberPutra+$memberPutri;
            // internal member visits = join memberships/package name internal
            $im = $pdo->prepare(
                "SELECT v.area, COUNT(*) n FROM member_visits v
                 JOIN members m ON m.id=v.member_id
                 LEFT JOIN memberships ms ON ms.member_id=m.id AND ms.status='ACTIVE'
                 LEFT JOIN membership_packages mp ON mp.id=ms.package_id
                 WHERE v.visit_date BETWEEN ? AND ?
                 AND (mp.name LIKE '%Karyawan%' OR mp.name LIKE '%Siswa Al%' OR mp.name LIKE '%Santri%' OR m.name LIKE '%Santri%')
                 GROUP BY v.area"
            );
            $im->execute([$start,$end]);
            foreach ($im->fetchAll() as $r) {
                $n=(int)$r['n'];
                if ($r['area']==='PUTRA') $internalMemberPutra=$n;
                elseif ($r['area']==='PUTRI') $internalMemberPutri=$n;
            }
            $internalMemberTotal=$internalMemberPutra+$internalMemberPutri;
            $externalMemberPutra=$memberPutra-$internalMemberPutra;
            $externalMemberPutri=$memberPutri-$internalMemberPutri;
            $externalMemberTotal=$memberTotal-$internalMemberTotal;
        } catch (Throwable $e) {}

        $putraTotal = $tiketPutra + $memberPutra;
        $putriTotal = $tiketPutri + $memberPutri;
        $grandTotal = $putraTotal + $putriTotal;
        $campQ2 = 0;

        // External = Umum + Rombongan + member biasa ; Internal = Karyawan/Keluarga/Siswa/Santri (tiket + member)
        $internal = $internalTiketTotal + $internalMemberTotal;
        $external = ($tiketTotal - $internalTiketTotal) + $externalMemberTotal;
        $externalTiketTotal = $tiketTotal - $internalTiketTotal;

        // Daily breakdown for chart/table if harian->per jam, mingguan->per hari, bulanan->per hari
        $daily = [];
        try {
            $q = $pdo->prepare(
                "SELECT d as tgl,
                        (SELECT COALESCE(SUM(ti.quantity),0) FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND DATE(t.created_at)=d AND t.payment_status='PAID') as tiket,
                        (SELECT COUNT(*) FROM member_visits WHERE visit_date=d) as member
                 FROM (SELECT DISTINCT visit_date as d FROM member_visits WHERE visit_date BETWEEN ? AND ?
                       UNION SELECT DISTINCT DATE(created_at) as d FROM transactions WHERE DATE(created_at) BETWEEN ? AND ?) x
                 ORDER BY d"
            );
            $q->execute([$start,$end,$start,$end]);
            $daily = $q->fetchAll();
        } catch (Throwable $e) { $daily=[]; }

        // Rombongan Eksternal detail: nama rombongan + tanggal + Putra/Putri
        $rombonganEksternal = [];
        try {
            $re = $pdo->prepare(
                "SELECT DATE(t.created_at) as tgl,
                        TRIM(SUBSTRING_INDEX(ti.item_name, '-', -1)) as nama,
                        ti.detail, COALESCE(SUM(ti.quantity),0) as q
                 FROM transaction_items ti
                 JOIN transactions t ON t.id = ti.transaction_id
                 LEFT JOIN ticket_types tt ON tt.id = ti.item_ref_id
                 WHERE ti.item_type='TIKET' AND ti.detail IN ('PUTRA','PUTRI')
                   AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN ? AND ?
                   AND (tt.require_nama = 1 OR ti.item_name LIKE '%Rombongan Eksternal - %')
                 GROUP BY tgl, nama, ti.detail
                 ORDER BY tgl DESC, nama ASC"
            );
            $re->execute([$start,$end]);
            $map = [];
            foreach ($re->fetchAll() as $r) {
                $key = $r['tgl'].'|'.$r['nama'];
                if (!isset($map[$key])) $map[$key] = ['tgl'=>$r['tgl'],'nama'=>$r['nama'],'putra'=>0,'putri'=>0,'total'=>0];
                $q=(float)$r['q'];
                if ($r['detail']==='PUTRA') $map[$key]['putra']+=$q;
                elseif ($r['detail']==='PUTRI') $map[$key]['putri']+=$q;
                $map[$key]['total']+=$q;
            }
            $rombonganEksternal = array_values($map);
        } catch (Throwable $e) { $rombonganEksternal=[]; }

        return compact('tiketPutra','tiketPutri','tiketTotal','tiketRincian','rombonganPutra','rombonganPutri','rombonganTotal','memberPutra','memberPutri','memberTotal','putraTotal','putriTotal','grandTotal','external','internal','internalTiketTotal','externalTiketTotal','internalMemberTotal','externalMemberTotal','campQ2','daily','jenjangTotal','jenjangPutra','jenjangPutri','jenjangList','usiaTotal','usiaPutra','usiaPutri','usiaList','rombonganEksternal');
    }
}
