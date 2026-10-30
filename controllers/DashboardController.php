<?php
class DashboardController
{
    public function index(): array
    {
        require_role(['admin', 'kasir', 'petugas', 'manajemen', 'internal_control']);
        $rep = new Report();
        $d = $rep->dashboard();
        $set = (new Setting())->allAsMap();
        $dow = (int) date('N');
        $tarifAktif = ($dow === 7) ? 'B' : 'A';
        
        // Data grafik kunjungan 6 bulan terakhir
        $monthlyInternalPutra = [];
        $monthlyInternalPutri = [];
        $monthlyExternalPutra = [];
        $monthlyExternalPutri = [];
        $monthNames = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-$i months"));
            $dateEnd = date('Y-m-t', strtotime($date));
            $monthName = date('F Y', strtotime($date));
            $monthNames[] = $monthName;
            
            // Internal tiket per area
            $internalTiketPutra = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRA' AND (ti.item_name LIKE '%Karyawan%' OR ti.item_name LIKE '%Keluarga%' OR ti.item_name LIKE '%Siswa Al%' OR ti.item_name LIKE '%Santri%') AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN '$date' AND '$dateEnd'")->fetch()['q'] ?? 0;
            $internalTiketPutri = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRI' AND (ti.item_name LIKE '%Karyawan%' OR ti.item_name LIKE '%Keluarga%' OR ti.item_name LIKE '%Siswa Al%' OR ti.item_name LIKE '%Santri%') AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN '$date' AND '$dateEnd'")->fetch()['q'] ?? 0;
            
            // Internal member per area
            $internalMemberPutra = db()->query("SELECT COUNT(*) n FROM member_visits v JOIN members m ON m.id=v.member_id LEFT JOIN memberships ms ON ms.member_id=m.id AND ms.status='ACTIVE' LEFT JOIN membership_packages mp ON mp.id=ms.package_id WHERE v.area='PUTRA' AND v.visit_date BETWEEN '$date' AND '$dateEnd' AND (mp.name LIKE '%Karyawan%' OR mp.name LIKE '%Siswa Al%' OR mp.name LIKE '%Santri%' OR m.name LIKE '%Santri%')")->fetch()['n'] ?? 0;
            $internalMemberPutri = db()->query("SELECT COUNT(*) n FROM member_visits v JOIN members m ON m.id=v.member_id LEFT JOIN memberships ms ON ms.member_id=m.id AND ms.status='ACTIVE' LEFT JOIN membership_packages mp ON mp.id=ms.package_id WHERE v.area='PUTRI' AND v.visit_date BETWEEN '$date' AND '$dateEnd' AND (mp.name LIKE '%Karyawan%' OR mp.name LIKE '%Siswa Al%' OR mp.name LIKE '%Santri%' OR m.name LIKE '%Santri%')")->fetch()['n'] ?? 0;
            
            $monthlyInternalPutra[] = $internalTiketPutra + $internalMemberPutra;
            $monthlyInternalPutri[] = $internalTiketPutri + $internalMemberPutri;
            
            // Total tiket per area
            $totalTiketPutra = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRA' AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN '$date' AND '$dateEnd'")->fetch()['q'] ?? 0;
            $totalTiketPutri = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRI' AND t.payment_status='PAID' AND DATE(t.created_at) BETWEEN '$date' AND '$dateEnd'")->fetch()['q'] ?? 0;
            
            // Total member per area
            $totalMemberPutra = db()->query("SELECT COUNT(*) n FROM member_visits WHERE area='PUTRA' AND visit_date BETWEEN '$date' AND '$dateEnd'")->fetch()['n'] ?? 0;
            $totalMemberPutri = db()->query("SELECT COUNT(*) n FROM member_visits WHERE area='PUTRI' AND visit_date BETWEEN '$date' AND '$dateEnd'")->fetch()['n'] ?? 0;
            
            $monthlyExternalPutra[] = ($totalTiketPutra - $internalTiketPutra) + ($totalMemberPutra - $internalMemberPutra);
            $monthlyExternalPutri[] = ($totalTiketPutri - $internalTiketPutri) + ($totalMemberPutri - $internalMemberPutri);
        }
        
        // Data grafik 7 hari terakhir
        $dailyInternalPutra = [];
        $dailyInternalPutri = [];
        $dailyExternalPutra = [];
        $dailyExternalPutri = [];
        $dayNames = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($date));
            $dayNames[] = $dayName;
            
            // Internal tiket per area
            $internalTiketPutra = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRA' AND (ti.item_name LIKE '%Karyawan%' OR ti.item_name LIKE '%Keluarga%' OR ti.item_name LIKE '%Siswa Al%' OR ti.item_name LIKE '%Santri%') AND t.payment_status='PAID' AND DATE(t.created_at) = '$date'")->fetch()['q'] ?? 0;
            $internalTiketPutri = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRI' AND (ti.item_name LIKE '%Karyawan%' OR ti.item_name LIKE '%Keluarga%' OR ti.item_name LIKE '%Siswa Al%' OR ti.item_name LIKE '%Santri%') AND t.payment_status='PAID' AND DATE(t.created_at) = '$date'")->fetch()['q'] ?? 0;
            
            // Internal member per area
            $internalMemberPutra = db()->query("SELECT COUNT(*) n FROM member_visits v JOIN members m ON m.id=v.member_id LEFT JOIN memberships ms ON ms.member_id=m.id AND ms.status='ACTIVE' LEFT JOIN membership_packages mp ON mp.id=ms.package_id WHERE v.area='PUTRA' AND v.visit_date = '$date' AND (mp.name LIKE '%Karyawan%' OR mp.name LIKE '%Siswa Al%' OR mp.name LIKE '%Santri%' OR m.name LIKE '%Santri%')")->fetch()['n'] ?? 0;
            $internalMemberPutri = db()->query("SELECT COUNT(*) n FROM member_visits v JOIN members m ON m.id=v.member_id LEFT JOIN memberships ms ON ms.member_id=m.id AND ms.status='ACTIVE' LEFT JOIN membership_packages mp ON mp.id=ms.package_id WHERE v.area='PUTRI' AND v.visit_date = '$date' AND (mp.name LIKE '%Karyawan%' OR mp.name LIKE '%Siswa Al%' OR mp.name LIKE '%Santri%' OR m.name LIKE '%Santri%')")->fetch()['n'] ?? 0;
            
            $dailyInternalPutra[] = $internalTiketPutra + $internalMemberPutra;
            $dailyInternalPutri[] = $internalTiketPutri + $internalMemberPutri;
            
            // Total tiket per area
            $totalTiketPutra = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRA' AND t.payment_status='PAID' AND DATE(t.created_at) = '$date'")->fetch()['q'] ?? 0;
            $totalTiketPutri = db()->query("SELECT COALESCE(SUM(ti.quantity),0) q FROM transaction_items ti JOIN transactions t ON t.id=ti.transaction_id WHERE ti.item_type='TIKET' AND ti.detail='PUTRI' AND t.payment_status='PAID' AND DATE(t.created_at) = '$date'")->fetch()['q'] ?? 0;
            
            // Total member per area
            $totalMemberPutra = db()->query("SELECT COUNT(*) n FROM member_visits WHERE area='PUTRA' AND visit_date = '$date'")->fetch()['n'] ?? 0;
            $totalMemberPutri = db()->query("SELECT COUNT(*) n FROM member_visits WHERE area='PUTRI' AND visit_date = '$date'")->fetch()['n'] ?? 0;
            
            $dailyExternalPutra[] = ($totalTiketPutra - $internalTiketPutra) + ($totalMemberPutra - $internalMemberPutra);
            $dailyExternalPutri[] = ($totalTiketPutri - $internalTiketPutri) + ($totalMemberPutri - $internalMemberPutri);
        }
        
        return [
            'TARIF_HARI' => ['A' => 'Senin s.d Sabtu', 'B' => 'Minggu & Libur Nasional', 'C' => 'Idul Fitri'],
            'TARIF_AKTIF' => $tarifAktif,
            'STAT' => [
                'total_member' => $d['mc']['total'],
                'member_aktif' => $d['mc']['Aktif'],
                'akan_expired' => $d['mc']['Akan Expired'],
                'expired' => $d['mc']['Expired'],
                'checkin_hari' => $d['checkin'],
                'kapasitas' => (int) ($set['kapasitas'] ?? 400),
                'tiket_hari' => (int) $d['tiketHari'],
                'omset_hari' => $d['sum']['total'],
                'les_aktif' => $d['lesAktif'],
            ],
            'FEED' => array_map(function ($f) {
                $map = ['checkin' => ['ikon' => 'bi-person-check', 'warna' => 'hijau'],
                        'trx' => ['ikon' => 'bi-receipt', 'warna' => 'biru']];
                $m = $map[$f['k']] ?? ['ikon' => 'bi-info-circle', 'warna' => 'biru'];
                return ['ikon' => $m['ikon'], 'warna' => $m['warna'], 'judul' => $f['judul'],
                    'waktu' => date('H:i:s', strtotime($f['waktu'])), 'desc' => $f['isi'], 'sub' => $f['sub']];
            }, $d['feed']),
            'MEMBERS' => array_map(function ($e) {
                return ['nama' => $e['name'], 'nomor' => '', 'hp' => $e['phone'] ?? '',
                    'paket' => $e['paket'] ?? '', 'berakhir' => $e['end_date'] ?? '',
                    'sisa' => '', 'status' => 'Akan Expired'];
            }, $d['expiring']),
            'TRANSAKSI' => (new Transaction())->listAll(['date' => date('Y-m-d')]),
            'checkinPutra' => $d['checkinPutra'],
            'checkinPutri' => $d['checkinPutri'],
            'jadwalHari' => $d['jadwalHari'],
            'transSum' => $d['sum'],
            'settings' => $set,
            'chartData' => [
                'monthly' => [
                    'labels' => $monthNames,
                    'internalPutra' => $monthlyInternalPutra,
                    'internalPutri' => $monthlyInternalPutri,
                    'externalPutra' => $monthlyExternalPutra,
                    'externalPutri' => $monthlyExternalPutri,
                ],
                'daily' => [
                    'labels' => $dayNames,
                    'internalPutra' => $dailyInternalPutra,
                    'internalPutri' => $dailyInternalPutri,
                    'externalPutra' => $dailyExternalPutra,
                    'externalPutri' => $dailyExternalPutri,
                ],
            ],
        ];
    }
}
