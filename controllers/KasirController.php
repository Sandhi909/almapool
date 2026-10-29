<?php
class KasirController
{
    public function index(): array
    {
        require_role(['admin', 'kasir']);
        $today = date('Y-m-d');
        $edit = null;
        $editId = (int) get_param('edit', 0);
        if ($editId) {
            $trx = (new Transaction())->find($editId);
            // Semua transaksi PAID (tiket, member, les/private, sewa) boleh diedit.
            if ($trx && $trx['payment_status'] === 'PAID') {
                $items = (new Transaction())->items($editId);
                $tarif = 'A';
                foreach ($items as $itRow) {
                    if (in_array($itRow['tariff_day'] ?? '', ['A', 'B', 'C'], true)) {
                        $tarif = $itRow['tariff_day'];
                        break;
                    }
                }
                $member = null;
                if (!empty($trx['member_id'])) {
                    $member = (new Member())->find((int) $trx['member_id']);
                }
                $edit = ['id'=>$editId, 'trx'=>$trx, 'items'=>$items, 'tarif'=>$tarif, 'member'=>$member];
            } else {
                flash('error','Transaksi tidak bisa diedit (hanya transaksi berstatus PAID).');
            }
        }
        try{ $jenjangMaster = class_exists('MasterJenjang') ? (new MasterJenjang())->active() : []; } catch(Throwable $e){ $jenjangMaster=[]; }
        try{ $usiaMaster = class_exists('MasterKelompokUsia') ? (new MasterKelompokUsia())->active() : []; } catch(Throwable $e){ $usiaMaster=[]; }
        try{ $rombonganMaster = class_exists('MasterRombongan') ? (new MasterRombongan())->active() : []; } catch(Throwable $e){ $rombonganMaster=[]; }
        return [
            'TIKET' => (new TicketType())->active(),
            'MEMBER_PAKET' => (new MembershipPackage())->matrix(),
            'LES_PAKET' => (new LessonPackage())->active(),
            'ALAT_SEWA_NAMA' => 'Alat Sewa',
            'TRANSAKSI' => (new Transaction())->listAll(['date' => $today]),
            'shiftStat' => (new Transaction())->todaySummary($today),
            'settings' => (new Setting())->allAsMap(),
            'EDIT' => $edit,
            'JENJANG_MASTER' => $jenjangMaster,
            'USIA_MASTER' => $usiaMaster,
            'ROMBONGAN_MASTER' => $rombonganMaster,
        ];
    }

    /**
     * Checkout via AJAX JSON:
     * {tarif:'A', kelas:'Gold', area:'PUTRA', payment:'TUNAI', member_id?, student_id?, items:[{kind:'tiket'|'member'|'les'|'sewa', ref, nama, qty, detail?}]}
     * Member & les divalidasi ke master (snapshot harga server).
     */
    public function checkout(): void
    {
        require_role(['admin', 'kasir']);
        $raw = file_get_contents('php://input');
        $in = json_decode($raw, true);
        if (!is_array($in) || empty($in['items'])) json_response(['ok' => false, 'message' => 'Keranjang kosong.'], 422);

        $tarif = in_array($in['tarif'] ?? '', ['A', 'B', 'C'], true) ? $in['tarif'] : 'A';
        $kelas = ($in['kelas'] ?? '') === 'Silver' ? 'SILVER' : 'GOLD';
        $areaGlobal = in_array($in['area'] ?? '', ['PUTRA', 'PUTRI', 'CAMPURAN'], true) ? $in['area'] : 'PUTRA';
        $paySingle = in_array($in['payment'] ?? '', ['TUNAI', 'TAGIHAN', 'QRIS', 'TRANSFER'], true) ? $in['payment'] : 'TUNAI';
        // Pembayaran gabungan (split): {TUNAI:x, QRIS:y, TRANSFER:z}. Jika tidak dikirim, pakai jalur tunggal lama.
        $paySplit = null;
        if (isset($in['payments']) && is_array($in['payments'])) {
            $paySplit = [];
            foreach (['TUNAI', 'TAGIHAN', 'QRIS', 'TRANSFER'] as $m) {
                $paySplit[$m] = max(0, round((float) ($in['payments'][$m] ?? 0), 2));
            }
        }
        $editId = (int) ($in['edit_id'] ?? 0);
        $oldTrx = null;
        if ($editId) {
            $oldTrx = (new Transaction())->find($editId);
            if (!$oldTrx) json_response(['ok' => false, 'message' => 'Transaksi edit tidak ditemukan.'], 404);
            if ($oldTrx['payment_status'] !== 'PAID') {
                json_response(['ok' => false, 'message' => 'Transaksi sudah ' . $oldTrx['payment_status'] . ' tidak bisa diedit.'], 422);
            }
        }
        // Saat edit, pertahankan member lama bila frontend tidak mengirim ulang.
        $memberId = (int) ($in['member_id'] ?? 0) ?: (int) ($oldTrx['member_id'] ?? 0) ?: null;
        // Support maps per-ticket (jenjang, usia, rombongan)
        $rombonganJenjangMap = $in['rombongan_jenjang_map'] ?? [];
        $rombonganNamaMap = $in['rombongan_nama_map'] ?? [];
        $rombonganUsiaMap = $in['rombongan_usia_map'] ?? [];
        if (isset($in['rombongan_jenjang']) && !isset($rombonganJenjangMap[8])) $rombonganJenjangMap[8] = trim($in['rombongan_jenjang']);
        if (isset($in['rombongan_nama']) && !isset($rombonganNamaMap[9])) $rombonganNamaMap[9] = trim($in['rombongan_nama']);
        try{ $validJenjang = class_exists('MasterJenjang') ? array_column((new MasterJenjang())->active(),'name') : ['SD','SMP','SMA','Mahasiswa MU']; } catch(Throwable $e){ $validJenjang=['SD','SMP','SMA','Mahasiswa MU']; }
        try{ $validUsia = class_exists('MasterKelompokUsia') ? array_column((new MasterKelompokUsia())->active(),'name') : ['Anak-anak','Remaja','Dewasa']; } catch(Throwable $e){ $validUsia=['Anak-anak','Remaja','Dewasa']; }
        try{ $validRombongan = class_exists('MasterRombongan') ? array_column((new MasterRombongan())->active(),'name') : []; } catch(Throwable $e){ $validRombongan=[]; }
        if(!$validJenjang) $validJenjang=['SD','SMP','SMA','Mahasiswa MU'];
        if(!$validUsia) $validUsia=['Anak-anak','Remaja','Dewasa'];
        foreach($rombonganJenjangMap as $k=>$v){ $v=trim($v); if($v!=='' && !in_array($v,$validJenjang,true)) $rombonganJenjangMap[$k]=''; else $rombonganJenjangMap[$k]=$v; }
        foreach($rombonganNamaMap as $k=>$v){ $v=trim($v); if(mb_strlen($v)>80) $v=mb_substr($v,0,80); if($v!=='' && $validRombongan && !in_array($v,$validRombongan,true) && mb_strlen($v)>80) $v=mb_substr($v,0,80); $rombonganNamaMap[$k]=$v; }
        foreach($rombonganUsiaMap as $k=>$v){ $v=trim($v); if($v!=='' && !in_array($v,$validUsia,true)) $rombonganUsiaMap[$k]=''; else $rombonganUsiaMap[$k]=$v; }

        $trx = new Transaction();
        $items = [];
        $type = 'TIKET';
        $areasSeen = [];
        $sewaNames = [];
        foreach ($in['items'] as $it) {
            $kind = $it['kind'] ?? '';
            // Sewa lama tidak mengirim qty (dianggap 1 item).
            $qty = max(0, (float) ($it['qty'] ?? ($kind === 'sewa' ? 1 : 0)));
            if ($qty <= 0) continue;
            if ($kind === 'tiket') {
                $t = (new TicketType())->find((int) ($it['ref'] ?? 0));
                if (!$t || !(int) $t['is_active']) json_response(['ok' => false, 'message' => 'Tiket tidak valid.'], 422);
                $price = (float) $t['price_' . strtolower($tarif)];
                if ($price <= 0) json_response(['ok' => false, 'message' => 'Tiket "' . $t['name'] . '" tidak berlaku pada Tarif ' . $tarif . '.'], 422);
                $areaItem = in_array($it['area'] ?? $areaGlobal, ['PUTRA','PUTRI'], true) ? ($it['area'] ?? $areaGlobal) : $areaGlobal;
                $areasSeen[$areaItem] = true;
                $itemName = 'Tiket ' . $t['name'];
                $needJenjang = !empty($t['require_jenjang']);
                $needNama = !empty($t['require_nama']);
                $needUsia = !empty($t['require_usia'] ?? null);
                $refKey = (string)$t['id'];
                // prioritas per-item (fleksibel) lalu fallback map per-tiket
                $jenjangVal = trim($it['jenjang'] ?? $rombonganJenjangMap[$refKey] ?? '');
                $usiaVal = trim($it['usia'] ?? $rombonganUsiaMap[$refKey] ?? '');
                $rombonganVal = trim($it['rombongan'] ?? $rombonganNamaMap[$refKey] ?? '');
                if ($needJenjang && $jenjangVal!=='') $itemName .= ' - ' . $jenjangVal;
                if ($needUsia && $usiaVal!=='') $itemName .= ' - ' . $usiaVal;
                if ($needNama && $rombonganVal!=='') $itemName .= ' - ' . $rombonganVal;
                // validasi per-item untuk fleksibel
                if ($needJenjang && $jenjangVal==='') json_response(['ok'=>false,'message'=>'Tiket "'.$t['name'].'" ('.$areaItem.') wajib pilih jenjang.'],422);
                if ($needUsia && $usiaVal==='') json_response(['ok'=>false,'message'=>'Tiket "'.$t['name'].'" ('.$areaItem.') wajib pilih kelompok usia.'],422);
                if ($needNama && $rombonganVal==='') json_response(['ok'=>false,'message'=>'Tiket "'.$t['name'].'" ('.$areaItem.') wajib isi rombongan.'],422);
                $items[] = ['item_type' => 'TIKET', 'item_ref_id' => $t['id'], 'item_name' => $itemName,
                    'tariff_day' => $tarif, 'detail' => $areaItem, 'quantity' => $qty, 'unit_price' => $price];
            } elseif ($kind === 'member') {
                if (!$memberId) json_response(['ok' => false, 'message' => 'Pilih member dulu untuk penjualan membership.'], 422);
                $p = (new MembershipPackage())->find((int) ($it['ref'] ?? 0));
                if (!$p) json_response(['ok' => false, 'message' => 'Paket member tidak valid.'], 422);
                $items[] = ['item_type' => 'MEMBER', 'item_ref_id' => $p['id'],
                    'item_name' => 'Member ' . $p['name'], 'tariff_day' => '-', 'detail' => ucfirst(strtolower($p['tier'])),
                    'quantity' => $qty, 'unit_price' => (float) $p['price']];
                $type = 'MEMBER';
            } elseif ($kind === 'les') {
                $p = (new LessonPackage())->find((int) ($it['ref'] ?? 0));
                if (!$p) json_response(['ok' => false, 'message' => 'Paket les tidak valid.'], 422);
                $detail = ($it['detail'] ?? '') === 'Putri' ? 'Putri' : 'Putra';
                $items[] = ['item_type' => 'LES', 'item_ref_id' => $p['id'],
                    'item_name' => 'Les ' . $p['name'], 'tariff_day' => '-', 'detail' => $detail,
                    'quantity' => $qty, 'unit_price' => (float) $p['price']];
                if ($type === 'TIKET') $type = 'LES';
            } elseif ($kind === 'sewa') {
                $amount = to_money($it['amount'] ?? 0);
                if ($amount <= 0) continue;
                $sewaName = trim((string) ($it['nama'] ?? '')) ?: 'Alat Sewa';
                if (mb_strlen($sewaName) > 150) $sewaName = mb_substr($sewaName, 0, 150);
                $sewaNames[] = $sewaName;
                $items[] = ['item_type' => 'SEWA', 'item_ref_id' => null, 'item_name' => $sewaName,
                    'tariff_day' => '-', 'detail' => null, 'quantity' => $qty, 'unit_price' => $amount];
                if ($type === 'TIKET') $type = 'SEWA';
            }
        }
        if (!$items) json_response(['ok' => false, 'message' => 'Tidak ada item valid.'], 422);
        // validasi sudah dilakukan per-item di loop tiket di atas (mendukung fleksibel per tiket beda jenjang/usia/rombongan)
        // Izinkan campuran Putra dan Putri dalam satu transaksi
        if (count($areasSeen) > 1) {
            $areaFinal = 'CAMPURAN';
        } else {
            $areaFinal = count($areasSeen) === 1 ? array_key_first($areasSeen) : $areaGlobal;
        }

        // Tentukan metode bayar: tunggal atau gabungan (total metode harus pas dengan total belanja).
        $totalNow = 0.0;
        foreach ($items as $it) $totalNow += (float) $it['quantity'] * (float) $it['unit_price'];
        $totalNow = round($totalNow, 2);
        $pay = $paySingle;
        $payDetail = null;
        $paidSplit = null;
        if ($paySplit !== null) {
            $nz = array_filter($paySplit, fn ($v) => $v > 0);
            if (!$nz) json_response(['ok' => false, 'message' => 'Isi nominal tiap metode bayar dulu.'], 422);
            if (abs(round(array_sum($paySplit), 2) - $totalNow) > 0.009) {
                json_response(['ok' => false, 'message' => 'Total metode (' . rupiah(array_sum($paySplit)) . ') tidak sama dengan total belanja (' . rupiah($totalNow) . ').'], 422);
            }
            if (count($nz) === 1) {
                // Hanya 1 metode diisi -> perlakukan sebagai bayar tunggal (kembalian tetap berlaku).
                $pay = array_key_first($nz);
                $paidSplit = (float) reset($nz);
            } else {
                $pay = 'GABUNGAN';
                $payDetail = $paySplit;
            }
        }
        $payCols = Transaction::normalizePay($payDetail, $pay, $totalNow);

        // Susun catatan transaksi (dipakai untuk transaksi baru maupun edit).
        $notesParts = ['POS loket'];
        foreach($rombonganJenjangMap as $ref=>$val){ if($val!==''){ $tt=(new TicketType())->find((int)$ref); $notesParts[] = ($tt['name'] ?? 'Tiket '.$ref).' Jenjang: '.$val; } }
        foreach($rombonganUsiaMap as $ref=>$val){ if($val!==''){ $tt=(new TicketType())->find((int)$ref); $notesParts[] = ($tt['name'] ?? 'Tiket '.$ref).' Usia: '.$val; } }
        foreach($rombonganNamaMap as $ref=>$val){ if($val!==''){ $tt=(new TicketType())->find((int)$ref); $notesParts[] = ($tt['name'] ?? 'Tiket '.$ref).' Rombongan: '.$val; } }
        if ($type === 'SEWA' && $sewaNames) {
            // Halaman Sewa memakai kolom notes sebagai keterangan alat.
            $notesParts = $sewaNames;
        } elseif ($sewaNames) {
            $notesParts[] = 'Sewa: ' . implode(', ', $sewaNames);
        }
        $notesFinal = implode(' | ', $notesParts);

        if ($editId) {
            // Jangan ubah jenis transaksi saat edit (mis. tiket menjadi member),
            // supaya efek samping membership/les tetap konsisten.
            if (($oldTrx['type'] ?? '') !== $type) {
                json_response(['ok' => false, 'message' => 'Jenis transaksi tidak bisa diubah saat edit.'], 422);
            }
            $total = $totalNow;
            // alokasi nomor seri untuk edit (sequential baru)
            foreach($items as &$ei){ if(($ei['item_type']??'')==='TIKET' && !empty($ei['item_ref_id'])){ $row=db()->query('SELECT serial_prefix, serial_next FROM ticket_types WHERE id='.(int)$ei['item_ref_id'])->fetch(); if($row){ $qty=(int)max(1,round((float)$ei['quantity'])); $start=(int)($row['serial_next']??1); $end=$start+$qty-1; $ei['serial_prefix']=$row['serial_prefix']??''; $ei['serial_start']=$start; $ei['serial_end']=$end; } } } unset($ei);
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $pdo->prepare('DELETE FROM transaction_items WHERE transaction_id=?')->execute([$editId]);
                // update serial_next per tiket yang dialokasikan di atas
                foreach($items as $ei){ if(isset($ei['serial_end'])) $pdo->prepare('UPDATE ticket_types SET serial_next=? WHERE id=?')->execute([(int)$ei['serial_end']+1, (int)$ei['item_ref_id']]); }
                $st=$pdo->prepare('INSERT INTO transaction_items (transaction_id, item_type, item_ref_id, item_name, tariff_day, detail, quantity, unit_price, subtotal, serial_prefix, serial_start, serial_end) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                foreach($items as $it){ $st->execute([$editId,$it['item_type'],$it['item_ref_id'],$it['item_name'],$it['tariff_day'],$it['detail'],$it['quantity'],$it['unit_price'],$it['quantity']*$it['unit_price'],$it['serial_prefix']??null,$it['serial_start']??null,$it['serial_end']??null]); }
                // Catatan transaksi diturunkan ulang dari item terbaru (hindari duplikasi "POS loket").
                $stamp = ' [EDIT ' . date('Y-m-d H:i:s') . ']';
                $editNotes = mb_substr($notesFinal, 0, max(0, 255 - mb_strlen($stamp))) . $stamp;
                $pdo->prepare('UPDATE transactions SET type=?, member_id=?, amount=?, paid_amount=?, change_amount=?, payment_method=?, pay_tunai=?, pay_qris=?, pay_transfer=?, pay_tagihan=?, area=?, notes=? WHERE id=?')->execute([$type,$memberId,$total,$total,0,$pay,$payCols['TUNAI'],$payCols['QRIS'],$payCols['TRANSFER'],$payCols['TAGIHAN'],$areaFinal,$editNotes,$editId]);
                $pdo->commit();
            } catch(Throwable $e){ if($pdo->inTransaction()) $pdo->rollBack(); json_response(['ok'=>false,'message'=>'Gagal edit: '.$e->getMessage()],500); }
            $row = $trx->find($editId);
            json_response(['ok'=>true,'message'=>'Transaksi diperbarui.','number'=>$row['number'],'total'=>(float)$row['amount'],'receipt_id'=>$editId]);
        }

        $id = $trx->checkout([
            'type' => $type, 'member_id' => $memberId, 'payment_method' => $pay,
            'pay' => $payDetail,
            'cashier_id' => (int) auth_user()['id'], 'area' => $areaFinal,
            'paid_amount' => $pay === 'GABUNGAN' ? $totalNow : (to_money($paidSplit ?? ($in['tendered'] ?? 0)) ?: null),
            'notes' => $notesFinal,
        ], $items);

        // Aktifkan/perpanjang membership untuk item member.
        foreach ($items as $it) {
            if ($it['item_type'] === 'MEMBER' && $memberId) {
                (new Membership())->purchase($memberId, (int) $it['item_ref_id']);
            }
        }
        $row = $trx->find($id);
        json_response(['ok' => true, 'message' => 'Pembayaran berhasil.', 'number' => $row['number'],
            'total' => (float) $row['amount'], 'receipt_id' => $id]);
    }

    /** Struk tiket untuk pelanggan (bukti transaksi, siap cetak). */
    public function receipt(int $id): array
    {
        require_role(['admin', 'kasir']);
        $trx = new Transaction();
        $t = $trx->queryOne(
            'SELECT t.*, u.name AS kasir FROM transactions t LEFT JOIN users u ON u.id = t.cashier_id WHERE t.id = ?', [$id]);
        if (!$t) {
            http_response_code(404);
            die('Transaksi tidak ditemukan.');
        }
        return ['t' => $t, 'items' => $trx->items($id), 'settings' => (new Setting())->allAsMap()];
    }
}
