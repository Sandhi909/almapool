<?php
// Les, Transaksi (biaya+setoran), Laporan, Master.
class LesController
{
    public function index(): array
    {
        require_role(['admin', 'kasir']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $this->handlePost();
        }
        return [
            'LES_PAKET' => (new LessonPackage())->active(),
            'INSTRUKTUR' => (new Instructor())->active(),
            'JADWAL_LES' => (new LessonSchedule())->listAll(),
            'SISWA' => (new LessonStudent())->listAll(),
            'STAT' => ['les_aktif' => (int) (new LessonStudent())->queryOne("SELECT COUNT(*) n FROM lesson_students WHERE status = 'ACTIVE'")['n']],
        ];
    }

    private function handlePost(): void
    {
        $act = (string) post('action', '');
        if ($act === 'siswa') {
            $nama = (string) post('nama', '');
            $pkgId = (int) post('package_id', 0);
            if ($nama === '') {
                flash('error', 'Nama peserta wajib diisi.');
                redirect('?page=les');
            }
            $pkg = $pkgId ? (new LessonPackage())->find($pkgId) : null;
            $dur = $pkg ? (int) $pkg['duration_days'] : 90;
            $quota = $pkg ? (int) $pkg['session_count'] : 10;
            $start = post('tgl_mulai') ?: date('Y-m-d');
            $end = date('Y-m-d', strtotime($start . ' +' . ($dur - 1) . ' days'));
            (new LessonStudent())->create([
                'name' => $nama, 'phone' => post('hp') ?: null,
                'birth_date' => post('tgl_lahir') ?: null,
                'gender' => post('gender') === 'P' ? 'P' : 'L',
                'package_id' => $pkgId ?: null,
                'instructor_id' => (int) post('instructor_id', 0) ?: null,
                'start_date' => $start, 'end_date' => $end, 'quota_sessions' => $quota,
            ]);
            flash('success', 'Peserta didaftarkan: ' . $quota . 'x pertemuan / ' . $dur . ' hari (mana yang tercapai dulu).');
        } elseif ($act === 'jadwal') {
            (new LessonSchedule())->create([
                'day_of_week' => post('hari'), 'start_time' => post('mulai'), 'end_time' => post('selesai'),
                'class_name' => (string) post('kelas', ''), 'instructor_id' => (int) post('instructor_id', 0) ?: null,
                'area' => in_array(post('area'), ['PUTRA', 'PUTRI'], true) ? post('area') : 'PUTRA',
                'capacity' => max(1, (int) post('kapasitas', 10)),
            ]);
            flash('success', 'Jadwal les ditambahkan.');
        } elseif ($act === 'absen') {
            (new LessonSession())->mark(
                (int) post('student_id', 0), (int) post('schedule_id', 0) ?: null,
                (string) (post('tanggal') ?: date('Y-m-d')), (int) post('instructor_id', 0) ?: null,
                post('hadir') === 'TIDAK' ? 'TIDAK' : 'HADIR');
            flash('success', 'Pertemuan dicatat.');
        } elseif ($act === 'hapus-siswa') {
            require_role(['admin', 'kasir']);
            $sid = (int) post('id', 0);
            if ($sid) {
                $pdo = db();
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare('DELETE FROM lesson_sessions WHERE student_id=?')->execute([$sid]);
                    $pdo->prepare('DELETE FROM lesson_students WHERE id=?')->execute([$sid]);
                    $pdo->commit();
                    flash('success', 'Peserta les dihapus beserta absensinya.');
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    flash('error', 'Gagal hapus peserta: ' . $e->getMessage());
                }
            }
        } elseif ($act === 'hapus-jadwal') {
            require_role(['admin', 'kasir']);
            $jid = (int) post('id', 0);
            if ($jid) {
                $pdo = db();
                try {
                    $pdo->prepare('UPDATE lesson_sessions SET schedule_id=NULL WHERE schedule_id=?')->execute([$jid]);
                    $pdo->prepare('DELETE FROM lesson_schedules WHERE id=?')->execute([$jid]);
                    flash('success', 'Jadwal les dihapus.');
                } catch (Throwable $e) {
                    flash('error', 'Gagal hapus jadwal: ' . $e->getMessage());
                }
            }
        }
        redirect('?page=les');
    }

    public function students(): void
    {
        require_role(['admin', 'kasir']);
        $rows = (new LessonStudent())->search((string) get_param('q', ''));
        json_response(['results' => $rows]);
    }
}

class TransaksiController
{
    public function index(): array
    {
        require_role(['admin', 'kasir', 'manajemen']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $this->handlePost();
        }
        $filter = ['date' => get_param('tgl', date('Y-m-d')), 'type' => get_param('jenis', ''),
            'method' => get_param('metode', ''), 'q' => get_param('q', '')];
        $y = (int) date('Y');
        $mo = (int) date('m');
        $sum = (new Transaction())->todaySummary($filter['date'] ?: date('Y-m-d'));
        // Setoran OTOMATIS dari transaksi per metode bayar (fitur input manual dinonaktifkan).
        $setoranAuto = ['QRIS' => (float) ($sum['by']['QRIS'] ?? 0),
            'TRANSFER' => (float) ($sum['by']['TRANSFER'] ?? 0),
            'UANG' => (float) ($sum['by']['TUNAI'] ?? 0)];
        $totBiayaArr = (new OpCost())->total($y, $mo);
        $totBiaya = array_sum($totBiayaArr);
        return [
            'TRANSAKSI' => (new Transaction())->listAll($filter),
            'filter' => $filter,
            'sumHari' => $sum,
            'BIAYA' => (new OpCost())->monthly($y, $mo),
            'totBiaya' => $totBiaya,
            'EXPENSE_TYPES' => (new MasterExpenseType())->active(),
            'SETORAN' => $setoranAuto,
            'LAPORAN' => ['periode' => date('F Y')],
        ];
    }

    private function handlePost(): void
    {
        $act = (string) post('action', '');
        if ($act === 'biaya') {
            require_role(['admin', 'kasir']);
            (new OpCost())->create([
                'cost_date' => post('tanggal') ?: date('Y-m-d'), 'expense_type_id' => (int) post('expense_type_id', 0),
                'amount' => to_money(post('jumlah', 0)), 'notes' => post('keterangan') ?: null,
                'created_by' => auth_user() ? (int) auth_user()['id'] : null,
            ]);
            flash('success', 'Biaya operasional dicatat.');
            redirect('?page=transaksi');
        } elseif ($act === 'setoran') {
            // NONAKTIF: setoran kini otomatis dari transaksi (lihat Report::$trxPay).
            flash('info', 'Input setoran manual dinonaktifkan. Nominal setoran otomatis dari transaksi kasir.');
            redirect('?page=transaksi');
        } elseif ($act === 'hapus-biaya') {
            require_role(['admin']);
            (new OpCost())->delete((int) post('id', 0));
            flash('success', 'Biaya dihapus.');
            redirect('?page=transaksi');
        } elseif ($act === 'edit-biaya') {
            require_role(['admin', 'kasir']);
            $id = (int) post('id', 0);
            if ($id) {
                $d = [
                    'cost_date' => post('tanggal') ?: date('Y-m-d'), 'expense_type_id' => (int) post('expense_type_id', 0),
                    'amount' => to_money(post('jumlah', 0)), 'notes' => post('keterangan') ?: null,
                ];
                (new OpCost())->update($id, $d);
                flash('success', 'Biaya operasional diperbarui.');
                redirect('?page=transaksi');
            }
        } elseif ($act === 'hapus-setoran') {
            flash('info', 'Input setoran manual dinonaktifkan. Nominal setoran otomatis dari transaksi kasir.');
            redirect('?page=transaksi');
        } elseif ($act === 'edit-transaksi') {
            require_role(['admin','kasir']);
            $id = (int) post('id', 0);
            $trx = (new Transaction())->find($id);
            if (!$trx) { flash('error','Transaksi tidak ditemukan.'); redirect('?page=transaksi'); }
            if ($trx['payment_status'] !== 'PAID') { flash('error','Transaksi '.$trx['payment_status'].' tidak bisa diedit.'); redirect('?page=transaksi'); }
            if (time() - strtotime($trx['created_at']) > 24*3600) { flash('error','Edit hanya dalam 24 jam.'); redirect('?page=transaksi'); }
            // ambil qty dari POST: qty[item_id] => new qty
            $qtyMap = $_POST['qty'] ?? [];
            if (!is_array($qtyMap) || empty($qtyMap)) { flash('error','Tidak ada perubahan qty.'); redirect('?page=transaksi'); }
            $items = db()->query('SELECT * FROM transaction_items WHERE transaction_id='.(int)$id)->fetchAll();
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $newTotal = 0;
                foreach ($items as $it) {
                    $iid = (int)$it['id'];
                    $newQty = isset($qtyMap[$iid]) ? max(0, (float)str_replace(',','.', $qtyMap[$iid])) : (float)$it['quantity'];
                    $newQty = round($newQty, 2);
                    if ($newQty <= 0) {
                        $pdo->prepare('DELETE FROM transaction_items WHERE id=?')->execute([$iid]);
                    } else {
                        $sub = $newQty * (float)$it['unit_price'];
                        $pdo->prepare('UPDATE transaction_items SET quantity=?, subtotal=? WHERE id=?')->execute([$newQty, $sub, $iid]);
                        $newTotal += $sub;
                    }
                }
                // cek sisa item
                $cnt = (int)$pdo->query('SELECT COUNT(*) c FROM transaction_items WHERE transaction_id='.(int)$id)->fetch()['c'];
                if ($cnt === 0) { $pdo->rollBack(); flash('error','Transaksi tidak boleh kosong (qty 0 semua). Gunakan refund.'); redirect('?page=transaksi'); }
                // update header area/kolam dan payment jika ada
                $newKolam = in_array(post('kolam'), ['PUTRA','PUTRI'], true) ? post('kolam') : $trx['area'];
                $newPay = in_array(post('payment_method'), ['TUNAI','QRIS','TRANSFER'], true) ? post('payment_method') : $trx['payment_method'];
                // Edit selalu jadi metode tunggal, kecuali transaksi gabungan yang metodenya
                // tidak diubah (rincian lama diskalakan proporsional ke total baru).
                if ($newPay === 'GABUNGAN') {
                    $oldTotal = max(0.01, (float) ($trx['amount'] ?? 0));
                    $ratio = (float) $newTotal / $oldTotal;
                    $newPayCols = [
                        'QRIS' => round((float) ($trx['pay_qris'] ?? 0) * $ratio, 2),
                        'TRANSFER' => round((float) ($trx['pay_transfer'] ?? 0) * $ratio, 2),
                        'TUNAI' => 0.0,
                    ];
                    $newPayCols['TUNAI'] = round((float) $newTotal - $newPayCols['QRIS'] - $newPayCols['TRANSFER'], 2);
                } else {
                    $newPayCols = Transaction::normalizePay(null, $newPay, (float) $newTotal);
                }
                $pdo->prepare('UPDATE transactions SET amount=?, paid_amount=?, change_amount=?, payment_method=?, pay_tunai=?, pay_qris=?, pay_transfer=?, area=?, notes=CONCAT(COALESCE(notes,""), " [EDIT ", NOW(), "]") WHERE id=?')->execute([$newTotal, $newTotal, 0, $newPay, $newPayCols['TUNAI'], $newPayCols['QRIS'], $newPayCols['TRANSFER'], $newKolam, $id]);
                $pdo->commit();
                flash('success','Transaksi '.$trx['number'].' diperbarui. Total baru '.rupiah($newTotal).'.');
            } catch (Throwable $e) { if($pdo->inTransaction()) $pdo->rollBack(); flash('error','Gagal edit: '.$e->getMessage()); }
        } elseif ($act === 'refund') {
            require_role(['admin','kasir']);
            $id = (int) post('id', 0);
            $trx = (new Transaction())->find($id);
            if (!$trx) { flash('error','Transaksi tidak ditemukan.'); redirect('?page=transaksi'); }
            if ($trx['payment_status'] !== 'PAID') { flash('error','Transaksi sudah '.$trx['payment_status'].' tidak bisa refund.'); redirect('?page=transaksi'); }
            if ($trx['type'] !== 'TIKET') { flash('error','Refund hanya untuk tiket.'); redirect('?page=transaksi'); }
            $created = strtotime($trx['created_at']);
            if (time() - $created > 24*3600) { flash('error','Refund hanya bisa dalam 24 jam (transaksi '.$trx['number'].' sudah lewat sehari).'); redirect('?page=transaksi'); }
            $ket = trim((string)post('keterangan',''));
            if ($ket === '') { flash('error','Keterangan refund wajib diisi.'); redirect('?page=transaksi'); }
            $ketSafe = substr($ket,0,200);
            db()->prepare("UPDATE transactions SET payment_status='CANCELLED', notes=CONCAT(COALESCE(notes,''), ' [REFUND ', NOW(), ': ', ?, ']') WHERE id=?")->execute([$ketSafe, $id]);
            flash('success','Refund berhasil: '.$trx['number'].' dibatalkan. Keterangan: '.$ketSafe);
        }
        redirect('?page=transaksi');
    }
}

class LaporanController
{
    public function index(): array
    {
        require_role(['admin', 'kasir', 'manajemen']);
        $type = (string) get_param('type', 'bulanan');
        $set = (new Setting())->allAsMap();
        $rep = new Report();

        if ($type === 'harian') {
            $date = (string) get_param('tanggal', date('Y-m-d'));
            $repData = $rep->daily($date);
            $periode = 'Harian ' . hari_tanggal_indo($date);
            $period = ['type' => 'harian', 'date' => $date];
        } elseif ($type === 'mingguan') {
            $month = max(1, min(12, (int) get_param('bulan', date('n'))));
            $week = max(1, min(4, (int) get_param('minggu', current_week_of_month())));
            $year = max(2020, min(2100, (int) get_param('tahun', date('Y'))));
            [$start, $end] = weekOfMonthBounds($year, $month, $week);
            $repData = $rep->weekly($start, $end);
            $periode = 'Mingguan ke-' . $week . ' ' . bulan_indo($month) . ' ' . $year . ' (' . tanggal_indo($start) . ' - ' . tanggal_indo($end) . ')';
            $period = ['type' => 'mingguan', 'year' => $year, 'month' => $month, 'week' => $week, 'start' => $start, 'end' => $end];
        } else {
            $month = max(1, min(12, (int) get_param('bulan', date('n'))));
            $year = max(2020, min(2100, (int) get_param('tahun', date('Y'))));
            $repData = $rep->monthly($year, $month);
            $periode = bulan_indo($month) . ' ' . $year;
            $period = ['type' => 'bulanan', 'month' => $month, 'year' => $year];
        }

        return [
            'APP' => ['nama_kolam' => $set['nama_kolam'] ?? 'KOLAM RENANG AL MA’SOEM', 'periode' => $periode],
            'LAPORAN' => $this->shape($repData, $set, $periode),
            'TIKET' => (new TicketType())->active(),
            'MEMBER_PAKET' => (new MembershipPackage())->matrix(),
            'LES_PAKET' => (new LessonPackage())->active(),
            'TYPE' => $type,
            'PERIOD' => $period,
        ];
    }

    private function shape(array $rep, array $set, string $periode): array
{
        $tiket = [];
        foreach ($rep['tickets'] as $t) {
            $pengunjung = (float)($t['qa'] ?? 0) + (float)($t['qb'] ?? 0) + (float)($t['qc'] ?? 0);
            $tiket[] = ['nama' => $t['name'], 'warna' => $t['color'],
                'ta' => (float) $t['la'], 'tb' => (float) $t['lb'], 'tc' => (float) $t['lc'],
                'qa' => (float) ($t['qa'] ?? 0), 'qb' => (float) ($t['qb'] ?? 0), 'qc' => (float) ($t['qc'] ?? 0),
                'pengunjung' => $pengunjung,
                'subtotal' => (float) $t['subtotal'],
                'seri_prefix' => $t['seri_prefix'] ?? '', 'seri_awal' => $t['seri_awal'] ?? null, 'seri_akhir' => $t['seri_akhir'] ?? null];
        }
        $member = [];
        foreach ($rep['members'] as $m) {
            $jml = (float)($m['jg'] ?? 0) + (float)($m['js'] ?? 0);
            $member[] = ['jenis' => $m['jenis'], 'qg' => (float) $m['qg'], 'qs' => (float) $m['qs'], 
                'tg' => 0, 'ts' => 0, 'jg' => (float)($m['jg'] ?? 0), 'js' => (float)($m['js'] ?? 0), 'jml' => $jml];
        }
        $les = [];
        foreach ($rep['les'] as $l) {
            $jml = (float)($l['jpra'] ?? 0) + (float)($l['jpri'] ?? 0);
            $les[] = ['jenis' => 'Les ' . $l['jenis'], 'tarif' => (float)($l['tarif'] ?? 0),
                'qputra' => (float) $l['qputra'], 'qputri' => (float) $l['qputri'],
                'jpra' => (float)($l['jpra'] ?? 0), 'jpri' => (float)($l['jpri'] ?? 0), 'jml' => $jml];
        }
        $biaya = array_map(fn ($b) => ['nama' => $b['nama'], 'jumlah' => (float) $b['jumlah']], $rep['biaya']);
        // Setoran OTOMATIS dari transaksi (bukan input manual deposits):
        // QRIS & TRANSFER langsung ke rekening yayasan, TUNAI = setoran uang.
        $pay = $rep['trxPay'] ?? ['TUNAI' => 0, 'QRIS' => 0, 'TRANSFER' => 0];
        return [
            'periode' => $periode,
            'tambahan' => (float) $rep['tambahan'],
            'saldo_awal' => ['bon' => (float) ($set['saldo_awal_bon'] ?? 0), 'tunai' => (float) ($set['saldo_awal_tunai'] ?? 0)],
            'tiket' => $tiket, 'member' => $member, 'sewa_alat' => (float) $rep['sewa'], 'les' => $les,
            'biaya' => $biaya,
            'setoran' => [
                ['nama' => 'QRIS', 'jumlah' => (float) ($pay['QRIS'] ?? 0)],
                ['nama' => 'Transfer Rekening Mandiri Yayasan', 'jumlah' => (float) ($pay['TRANSFER'] ?? 0)],
                ['nama' => 'Setoran Uang', 'jumlah' => (float) ($pay['TUNAI'] ?? 0)],
            ],
            'saldo_akhir' => ['bon' => null, 'tunai' => null],
            'keterangan' => ['Tarif A : Senin s.d Sabtu', 'Tarif B : Minggu & Libur Nasional', 'Tarif C : Idul Fitri'],
            'ttd' => [
                ['nama' => $set['ttd_admin'] ?? '-', 'jabatan' => $set['ttd_admin_jabatan'] ?? 'Administrasi Kolam'],
                ['nama' => $set['ttd_manajer'] ?? '-', 'jabatan' => $set['ttd_manajer_jabatan'] ?? 'Manajer Kolam'],
                ['nama' => $set['ttd_kepala'] ?? '-', 'jabatan' => $set['ttd_kepala_jabatan'] ?? 'Kepala Direktorat Pengelola Unit Bisnis'],
            ],
        ];
    }
}

class MasterController
{
    public function index(): array
    {
        require_role(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $this->handlePost();
        }
        return [
            'TIKET' => (new TicketType())->all('id ASC'),
            'MEMBER_PAKET' => (new MembershipPackage())->all('name ASC, tier ASC'),
            'LES_PAKET' => (new LessonPackage())->all('id ASC'),
            'INSTRUKTUR' => (new Instructor())->all('name ASC'),
            'USERS' => (new User())->listAll(),
            'LAPORAN' => ['ttd' => []],
            'TARIF_HARI' => ['A' => 'Senin s.d Sabtu', 'B' => 'Minggu & Libur Nasional', 'C' => 'Idul Fitri'],
            'settings' => (new Setting())->allAsMap(),
            'ALAT_SEWA_NAMA' => 'Alat Sewa',
            'CATEGORIES' => (new Medicine())->categories(),
            'UNITS' => (new Medicine())->units(),
            'JENJANG' => (function(){ try{ return class_exists('MasterJenjang') ? (new MasterJenjang())->all('sort_order ASC, name ASC') : []; } catch(Throwable $e){ return []; } })(),
            'KELOMPOK_USIA' => (function(){ try{ return class_exists('MasterKelompokUsia') ? (new MasterKelompokUsia())->all('sort_order ASC, name ASC') : []; } catch(Throwable $e){ return []; } })(),
            'ROMBONGAN' => (function(){ try{ return class_exists('MasterRombongan') ? (new MasterRombongan())->all('name ASC') : []; } catch(Throwable $e){ return []; } })(),
            'JENIS_BIAYA' => (new MasterExpenseType())->all('name ASC'),
        ];
    }

    private function handlePost(): void
    {
        $act = (string) post('action', '');
        $id = (int) post('id', 0);
        if ($act === 'tiket') {
            $d = ['name' => (string) post('nama', ''), 'color' => (string) post('warna', '-'),
                'price_a' => to_money(post('a', 0)), 'price_b' => to_money(post('b', 0)), 'price_c' => to_money(post('c', 0)),
                'is_active' => post('is_active') === '0' ? 0 : 1,
                'is_internal' => post('is_internal') === '1' ? 1 : 0,
                'require_jenjang' => post('require_jenjang') === '1' ? 1 : 0,
                'require_nama' => post('require_nama') === '1' ? 1 : 0,
                'require_usia' => post('require_usia') === '1' ? 1 : 0,
                'serial_prefix' => substr(trim((string)post('serial_prefix','')),0,10),
                'serial_next' => max(1, (int)post('serial_next',1))];
            try { $id ? (new TicketType())->update($id, $d) : (new TicketType())->create($d); }
            catch (Throwable $e) {
                if(str_contains($e->getMessage(),'serial_')){ // fallback if kolom belum ada
                    unset($d['serial_prefix'],$d['serial_next']);
                    $id ? (new TicketType())->update($id,$d) : (new TicketType())->create($d);
                } elseif(str_contains($e->getMessage(),'require_usia')){ unset($d['require_usia']); $id ? (new TicketType())->update($id,$d) : (new TicketType())->create($d);
                } else throw $e;
            }
            flash('success', 'Paket tiket disimpan.');
        } elseif ($act === 'paket') {
            $d = ['name' => (string) post('nama', ''), 'tier' => post('tier') === 'SILVER' ? 'SILVER' : 'GOLD',
                'duration_days' => max(1, (int) post('durasi', 30)), 'price' => to_money(post('harga', 0)),
                'description' => post('deskripsi') ?: null, 'is_active' => post('is_active') === '0' ? 0 : 1];
            $id ? (new MembershipPackage())->update($id, $d) : (new MembershipPackage())->create($d);
            flash('success', 'Paket membership disimpan.');
        } elseif ($act === 'lespaket') {
            $d = ['name' => (string) post('nama', ''), 'price' => to_money(post('harga', 0)),
                'duration_days' => max(1, (int) post('durasi', 90)),
                'session_count' => max(1, (int) post('kuota', 10)),
                'description' => post('deskripsi') ?: null, 'is_active' => post('is_active') === '0' ? 0 : 1];
            $id ? (new LessonPackage())->update($id, $d) : (new LessonPackage())->create($d);
            flash('success', 'Paket les disimpan.');
        } elseif ($act === 'instruktur') {
            $d = ['name' => (string) post('nama', ''), 'phone' => post('hp') ?: null,
                'specialty' => post('spesialis') ?: null, 'is_active' => post('is_active') === '0' ? 0 : 1];
            $id ? (new Instructor())->update($id, $d) : (new Instructor())->create($d);
            flash('success', 'Instruktur disimpan.');
        } elseif ($act === 'user') {
            $d = ['name' => (string) post('nama', ''), 'username' => (string) post('username', ''),
                'role' => in_array(post('role'), ['admin', 'kasir', 'petugas', 'manajemen', 'instruktur'], true) ? post('role') : 'kasir',
                'is_active' => post('is_active') === '0' ? 0 : 1];
            if (!$id && post('password', '') === '') {
                flash('error', 'Password wajib diisi untuk user baru.');
                redirect('?page=master');
            }
            if (post('password', '') !== '') $d['password'] = password_hash(post('password'), PASSWORD_DEFAULT);
            $m = new User();
            if ($id) {
                if (($d['username'] !== ($m->find($id)['username'] ?? '')) && $m->findByUsername($d['username'])) {
                    flash('error', 'Username sudah dipakai.');
                    redirect('?page=master');
                }
                $m->update($id, $d);
            } else {
                if ($m->findByUsername($d['username'])) {
                    flash('error', 'Username sudah dipakai.');
                    redirect('?page=master');
                }
                $id = $m->create($d);
            }
            // Kaitkan akun instruktur ke data instruktur (untuk halaman absensi).
            $linkIns = (int) post('instructor_link', 0);
            db()->prepare('UPDATE instructors SET user_id = NULL WHERE user_id = ?')->execute([$id]);
            if ($d['role'] === 'instruktur' && $linkIns) {
                db()->prepare('UPDATE instructors SET user_id = ? WHERE id = ?')->execute([$id, $linkIns]);
            }
            flash('success', 'User disimpan.');
        } elseif ($act === 'setting') {
            $s = new Setting();
            foreach (['nama_kolam', 'sesi_putra', 'sesi_putri', 'kapasitas', 'expiring_days',
                      'silver_holiday_fee', 'holidays',
                      'saldo_awal_bon', 'saldo_awal_tunai', 'ttd_admin', 'ttd_admin_jabatan',
                      'ttd_manajer', 'ttd_manajer_jabatan', 'ttd_kepala', 'ttd_kepala_jabatan'] as $k) {
                if (isset($_POST[$k])) $s->set($k, (string) post($k, ''));
            }
            flash('success', 'Pengaturan disimpan.');
        } elseif ($act === 'tambah_kategori') {
            $nama = (string) post('nama', '');
            if ($nama === '') {
                flash('error', 'Nama kategori wajib diisi.');
                redirect('?page=master');
            }
            db()->prepare('INSERT INTO medicine_categories (name) VALUES (?)')->execute([$nama]);
            flash('success', 'Kategori obat berhasil ditambahkan.');
        } elseif ($act === 'hapus_kategori') {
            $id = (int) post('id', 0);
            db()->prepare('UPDATE medicine_categories SET is_active = 0 WHERE id = ?')->execute([$id]);
            flash('success', 'Kategori obat berhasil dihapus.');
        } elseif ($act === 'tambah_satuan') {
            $nama = (string) post('nama', '');
            if ($nama === '') {
                flash('error', 'Nama satuan wajib diisi.');
                redirect('?page=master');
            }
            db()->prepare('INSERT INTO medicine_units (name) VALUES (?)')->execute([$nama]);
            flash('success', 'Satuan obat berhasil ditambahkan.');
        } elseif ($act === 'hapus_satuan') {
            $id = (int) post('id', 0);
            db()->prepare('UPDATE medicine_units SET is_active = 0 WHERE id = ?')->execute([$id]);
            flash('success', 'Satuan obat berhasil dihapus.');
        } elseif ($act === 'jenjang') {
            $d = ['name'=>(string)post('nama',''),'sort_order'=>(int)post('sort_order',0),'is_active'=>post('is_active')==='0'?0:1];
            if($d['name']===''){ flash('error','Nama jenjang wajib.'); redirect('?page=master'); }
            try{ $id ? (new MasterJenjang())->update($id,$d) : (new MasterJenjang())->create($d); flash('success','Jenjang disimpan.'); } catch(Throwable $e){ flash('error','Gagal: '.$e->getMessage()); }
        } elseif ($act === 'kelompok_usia') {
            $d = ['name'=>(string)post('nama',''),'min_age'=>post('min_age')===''?null:(int)post('min_age',0),'max_age'=>post('max_age')===''?null:(int)post('max_age',0),'color'=>(string)(post('color','')?:null),'sort_order'=>(int)post('sort_order',0),'is_active'=>post('is_active')==='0'?0:1];
            if($d['name']===''){ flash('error','Nama kelompok usia wajib.'); redirect('?page=master'); }
            try{ $id ? (new MasterKelompokUsia())->update($id,$d) : (new MasterKelompokUsia())->create($d); flash('success','Kelompok usia disimpan.'); } catch(Throwable $e){ flash('error','Gagal: '.$e->getMessage()); }
        } elseif ($act === 'rombongan') {
            $d = ['name'=>(string)post('nama',''),'description'=>(string)(post('deskripsi','')?:null),'is_active'=>post('is_active')==='0'?0:1];
            if($d['name']===''){ flash('error','Nama rombongan wajib.'); redirect('?page=master'); }
            try{ $id ? (new MasterRombongan())->update($id,$d) : (new MasterRombongan())->create($d); flash('success','Rombongan disimpan.'); } catch(Throwable $e){ flash('error','Gagal: '.$e->getMessage()); }
        } elseif ($act === 'jenis_biaya') {
            $d = ['name'=>(string)post('nama',''),'is_active'=>post('is_active')==='0'?0:1];
            if($d['name']===''){ flash('error','Nama jenis biaya wajib.'); redirect('?page=master'); }
            try{ $id ? (new MasterExpenseType())->update($id,$d) : (new MasterExpenseType())->create($d); flash('success','Jenis biaya disimpan.'); } catch(Throwable $e){ flash('error','Gagal: '.$e->getMessage()); }
        } elseif ($act === 'hapus') {
            $tabel = (string) post('tabel', '');
            $pdo = db();
            try {
                if ($tabel === 'instruktur' && $id) {
                    // Null-kan referensi, jangan hard delete bila masih dipakai
                    $cntSched = (int) $pdo->query("SELECT COUNT(*) c FROM lesson_schedules WHERE instructor_id=" . (int)$id)->fetch()['c'];
                    $cntStud = (int) $pdo->query("SELECT COUNT(*) c FROM lesson_students WHERE instructor_id=" . (int)$id)->fetch()['c'];
                    $cntSess = (int) $pdo->query("SELECT COUNT(*) c FROM lesson_sessions WHERE instructor_id=" . (int)$id)->fetch()['c'];
                    if ($cntSched || $cntStud || $cntSess) {
                        // Soft: detach + nonaktifkan
                        $pdo->prepare('UPDATE lesson_schedules SET instructor_id=NULL WHERE instructor_id=?')->execute([$id]);
                        $pdo->prepare('UPDATE lesson_students SET instructor_id=NULL WHERE instructor_id=?')->execute([$id]);
                        $pdo->prepare('UPDATE lesson_sessions SET instructor_id=NULL WHERE instructor_id=?')->execute([$id]);
                        $pdo->prepare('UPDATE instructors SET user_id=NULL, is_active=0 WHERE id=?')->execute([$id]);
                        flash('success', 'Instruktur masih dipakai jadwal/siswa — data di-nonaktifkan & dilepas (tidak dihapus permanen).');
                    } else {
                        $pdo->prepare('UPDATE instructors SET user_id=NULL WHERE user_id IN (SELECT id FROM users WHERE id IN (SELECT user_id FROM instructors WHERE id=?))')->execute([$id]);
                        (new Instructor())->delete($id);
                        flash('success', 'Instruktur dihapus.');
                    }
                } elseif ($tabel === 'tiket' && $id) {
                    $used = (int) $pdo->query("SELECT COUNT(*) c FROM transaction_items WHERE item_type='TIKET' AND item_ref_id=" . (int)$id)->fetch()['c'];
                    if ($used) {
                        $pdo->prepare('UPDATE ticket_types SET is_active=0 WHERE id=?')->execute([$id]);
                        flash('success', 'Tiket sudah pernah dipakai transaksi — di-nonaktifkan (tidak dihapus).');
                    } else {
                        (new TicketType())->delete($id);
                        flash('success', 'Tiket dihapus.');
                    }
                } elseif ($tabel === 'paket' && $id) {
                    $used = (int) $pdo->query("SELECT COUNT(*) c FROM memberships WHERE package_id=" . (int)$id)->fetch()['c'];
                    if ($used) {
                        $pdo->prepare('UPDATE membership_packages SET is_active=0 WHERE id=?')->execute([$id]);
                        flash('success', 'Paket member sudah dipakai — di-nonaktifkan (tidak dihapus).');
                    } else {
                        (new MembershipPackage())->delete($id);
                        flash('success', 'Paket dihapus.');
                    }
                } elseif ($tabel === 'lespaket' && $id) {
                    $used = (int) $pdo->query("SELECT COUNT(*) c FROM lesson_students WHERE package_id=" . (int)$id)->fetch()['c'];
                    $used2 = (int) $pdo->query("SELECT COUNT(*) c FROM transaction_items WHERE item_type='LES' AND item_ref_id=" . (int)$id)->fetch()['c'];
                    if ($used || $used2) {
                        $pdo->prepare('UPDATE lesson_packages SET is_active=0 WHERE id=?')->execute([$id]);
                        flash('success', 'Paket les sudah dipakai — di-nonaktifkan (tidak dihapus).');
                    } else {
                        (new LessonPackage())->delete($id);
                        flash('success', 'Paket les dihapus.');
                    }
                } elseif ($tabel === 'user' && $id) {
                    // Lepaskan FK sebelum hapus user
                    $pdo->prepare('UPDATE instructors SET user_id=NULL WHERE user_id=?')->execute([$id]);
                    $pdo->prepare('UPDATE transactions SET cashier_id=NULL WHERE cashier_id=?')->execute([$id]);
                    $pdo->prepare('UPDATE operational_costs SET created_by=NULL WHERE created_by=?')->execute([$id]);
                    $pdo->prepare('UPDATE deposits SET created_by=NULL WHERE created_by=?')->execute([$id]);
                    $pdo->prepare('UPDATE member_visits SET officer_id=NULL WHERE officer_id=?')->execute([$id]);
                    $pdo->prepare('UPDATE lesson_sessions SET instructor_id=NULL WHERE instructor_id IN (SELECT id FROM instructors WHERE user_id=?)')->execute([$id]);
                    (new User())->delete($id);
                    flash('success', 'User dihapus.');
                } elseif ($tabel === 'jenjang' && $id) { (new MasterJenjang())->delete($id); flash('success','Jenjang dihapus.'); }
                elseif ($tabel === 'kelompok_usia' && $id) { (new MasterKelompokUsia())->delete($id); flash('success','Kelompok usia dihapus.'); }
                elseif ($tabel === 'rombongan' && $id) { (new MasterRombongan())->delete($id); flash('success','Rombongan dihapus.'); }
                elseif ($tabel === 'jenis_biaya' && $id) { (new MasterExpenseType())->delete($id); flash('success','Jenis biaya dihapus.'); }
                elseif (isset(['tiket'=>1,'paket'=>1,'lespaket'=>1,'instruktur'=>1,'user'=>1][$tabel]) && $id) {
                    // fallback (tidak terpakai)
                }
            } catch (Throwable $e) {
                flash('error', 'Gagal hapus: ' . $e->getMessage());
            }
        }
        redirect('?page=master');
    }
}
