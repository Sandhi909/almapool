<?php
// Absensi les oleh instruktur (melihat hanya peserta sendiri).
class AbsensiController
{
    public function index(): array
    {
        require_role(['admin', 'instruktur']);
        $db = db();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $this->store();
        }

        $isAdmin = is_admin();
        $instrukturId = 0;
        if (!$isAdmin) {
            $st = $db->prepare("SELECT id FROM instructors WHERE user_id = ? LIMIT 1");
            $st->execute([(int) auth_user()['id']]);
            $link = $st->fetch();
            if (!$link) {
                return ['tanggal' => date('Y-m-d'), 'instruktur' => null, 'SISWA' => [], 'schedules' => [],
                    'INSTRUKTUR' => [], 'instrukturId' => 0, 'isAdmin' => false];
            }
            $instrukturId = (int) $link['id'];
        } else {
            $instrukturId = (int) get_param('instruktur', 0);
        }

        $tanggal = (string) (get_param('tanggal', '') ?: date('Y-m-d'));
        $ins = $instrukturId ? (new Instructor())->find($instrukturId) : null;

        $siswa = [];
        if ($instrukturId) {
            $st = $db->prepare(
                "SELECT s.*, lp.name AS paket,
                        (SELECT COUNT(*) FROM lesson_sessions ss WHERE ss.student_id = s.id AND ss.attendance = 'HADIR') AS hadir,
                        (SELECT attendance FROM lesson_sessions ss WHERE ss.student_id = s.id AND ss.session_date = ? LIMIT 1) AS absen_hari_ini
                 FROM lesson_students s LEFT JOIN lesson_packages lp ON lp.id = s.package_id
                 WHERE s.instructor_id = ? AND s.status = 'ACTIVE' ORDER BY s.name");
            $st->execute([$tanggal, $instrukturId]);
            $siswa = $st->fetchAll();
        }
        $schedules = [];
        if ($instrukturId) {
            $st = $db->prepare('SELECT * FROM lesson_schedules WHERE instructor_id = ? AND is_active = 1 ORDER BY start_time');
            $st->execute([$instrukturId]);
            $schedules = $st->fetchAll();
        }

        return ['tanggal' => $tanggal, 'instruktur' => $ins, 'SISWA' => $siswa, 'schedules' => $schedules,
            'INSTRUKTUR' => (new Instructor())->active(), 'instrukturId' => $instrukturId, 'isAdmin' => $isAdmin];
    }

    private function store(): void
    {
        $isAdmin = is_admin();
        $tanggal = (string) (post('tanggal', '') ?: date('Y-m-d'));
        $instrukturId = $isAdmin ? (int) post('instruktur_id', 0) : 0;
        if (!$isAdmin) {
            $st = db()->prepare("SELECT id FROM instructors WHERE user_id = ? LIMIT 1");
            $st->execute([(int) auth_user()['id']]);
            $link = $st->fetch();
            $instrukturId = $link ? (int) $link['id'] : 0;
        }
        if (!$instrukturId) {
            flash('error', 'Akun Anda belum dikaitkan ke data instruktur.');
            redirect('?page=absensi');
        }
        $hadir = post('hadir', []);
        if (!is_array($hadir) || !$hadir) {
            flash('error', 'Tidak ada absensi yang dipilih.');
            redirect('?page=absensi&tanggal=' . $tanggal . ($isAdmin ? '&instruktur=' . $instrukturId : ''));
        }
        $sess = new LessonSession();
        $db = db();
        $n = 0;
        foreach ($hadir as $sid => $val) {
            $sid = (int) $sid;
            // Pastikan peserta milik instruktur ini.
            $st = $db->prepare('SELECT id, quota_sessions FROM lesson_students WHERE id = ? AND instructor_id = ?');
            $st->execute([$sid, $instrukturId]);
            $ok = $st->fetch();
            if (!$ok) continue;
            $sess->mark($sid, null, $tanggal, $instrukturId, $val === 'TIDAK' ? 'TIDAK' : 'HADIR');
            // Lulus kuota -> selesai otomatis (mana yang tercapai dulu: kuota / masa).
            $st = $db->prepare("SELECT COUNT(*) n FROM lesson_sessions WHERE student_id = ? AND attendance = 'HADIR'");
            $st->execute([$sid]);
            $c = (int) $st->fetch()['n'];
            if ($c >= (int) $ok['quota_sessions']) {
                $db->prepare("UPDATE lesson_students SET status = 'COMPLETED' WHERE id = ?")->execute([$sid]);
            }
            $n++;
        }
        flash('success', $n . ' absensi tanggal ' . tanggal_indo($tanggal) . ' tersimpan.');
        redirect('?page=absensi&tanggal=' . $tanggal . ($isAdmin ? '&instruktur=' . $instrukturId : ''));
    }
}
