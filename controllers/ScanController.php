<?php
class ScanController
{
    public function result(): array
    {
        require_role(['admin', 'petugas', 'kasir']);
        $id = (string) (get_param('id', ''));
        $area = get_param('area', 'PUTRA') === 'PUTRI' ? 'PUTRI' : 'PUTRA';
        $data = ['SCAN_MEMBER' => null, 'lookup' => $id, 'visited' => null, 'area' => $area, 'auto_checkin' => null];
        if ($id !== '') {
            $m = (new Member())->findByNumber(preg_match('/^MEM-/', $id) ? $id : 'MEM-' . str_pad($id, 6, '0', STR_PAD_LEFT));
            if ($m) {
                $memberId = (int) $m['id'];
                // AUTO-CHECKIN: jika member aktif & belum kunjungan hari ini, langsung catat kunjungan ke DB
                $d0 = (new Member())->detail($memberId);
                $ms0 = $d0['membership'] ?? null;
                $status0 = $ms0 ? status_membership($ms0['end_date']) : 'Expired';
                $already = (new Member())->visitedToday($memberId);
                if ($status0 !== 'Expired' && !$already) {
                    $auto = (new Member())->checkin($memberId, $area, (int) auth_user()['id']);
                    if ($auto['ok']) {
                        $data['auto_checkin'] = $auto;
                        $already = (new Member())->visitedToday($memberId);
                        if ($auto['ok']) flash('success', $auto['message'] . ' Kunjungan tercatat otomatis ke database.');
                    } else {
                        $data['auto_checkin'] = $auto;
                    }
                }
                $d = (new Member())->detail($memberId);
                $ms = $d['membership'];
                $tier = strtoupper($ms['tier_snapshot'] ?? 'SILVER');
                $visitedNow = (new Member())->visitedToday($memberId);
                $data['SCAN_MEMBER'] = [
                    'id' => $m['id'], 'nama' => $m['name'], 'nomor' => $m['member_number'], 'hp' => $m['phone'] ?? '-',
                    'paket' => $ms ? ($ms['package_name_snapshot'] . ' ' . ucfirst(strtolower($ms['tier_snapshot']))) : '-',
                    'tier' => $tier,
                    'status' => $ms ? status_membership($ms['end_date']) : 'Expired',
                    'berlaku' => $ms ? tanggal_indo($ms['end_date']) : '-',
                    'sisa' => $ms ? ('s/d ' . tanggal_indo($ms['end_date'])) : '-',
                    'kunjungan' => $visitedNow ? 'Sudah Check-in' : 'Belum Check-in',
                    'terakhir' => $d['visits'][0]['check_in_at'] ?? '-',
                ];
                $data['visited'] = $visitedNow;
            }
        }
        return $data;
    }

    public function checkin(): void
    {
        require_role(['admin', 'petugas', 'kasir']);
        csrf_verify();
        $memberId = (int) post('member_id', 0);
        $area = post('area') === 'PUTRI' ? 'PUTRI' : 'PUTRA';
        $r = (new Member())->checkin($memberId, $area, (int) auth_user()['id']);
        $back = '?page=hasil-scan';
        if ($memberId) {
            $mm = (new Member())->find($memberId);
            if ($mm) $back .= '&id=' . urlencode($mm['member_number']) . '&area=' . $area;
        }
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false || ($_POST['ajax'] ?? '') === '1') {
            json_response($r, $r['ok'] ? 200 : 422);
        }
        flash($r['ok'] ? 'success' : 'error', $r['message']);
        redirect($back);
    }
}
