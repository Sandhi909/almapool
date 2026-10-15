<?php
class TicketType extends Model
{
    protected string $table = 'ticket_types';
    public function active(): array
    {
        return $this->query('SELECT * FROM ticket_types WHERE is_active = 1 ORDER BY id');
    }
}

class MembershipPackage extends Model
{
    protected string $table = 'membership_packages';
    public function active(): array
    {
        return $this->query('SELECT * FROM membership_packages WHERE is_active = 1 ORDER BY name, tier');
    }
    public function matrix(): array
    {
        $rows = $this->active();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['name']][$r['tier']] = $r;
        }
        return $out;
    }
}

class Membership extends Model
{
    protected string $table = 'memberships';

    /** Beli/perpanjang: masa baru mulai setelah masa aktif lama (PRD). */
    public function purchase(int $memberId, int $packageId): array
    {
        $pkg = $this->queryOne('SELECT * FROM membership_packages WHERE id = ?', [$packageId]);
        if (!$pkg) throw new RuntimeException('Paket tidak valid.');
        // Ada pendaftaran yang belum dibayar untuk paket yang sama -> aktifkan itu (jangan dobel).
        $pending = $this->queryOne(
            "SELECT * FROM memberships WHERE member_id = ? AND package_id = ? AND status = 'PENDING_PAYMENT' ORDER BY id DESC LIMIT 1",
            [$memberId, $packageId]);
        if ($pending) {
            $this->update($pending['id'], ['status' => 'ACTIVE']);
            return ['id' => $pending['id'], 'start' => $pending['start_date'], 'end' => $pending['end_date'],
                'price' => (float) $pkg['price'], 'name' => $pkg['name'], 'tier' => $pkg['tier']];
        }
        $cur = $this->queryOne('SELECT * FROM memberships WHERE member_id = ? ORDER BY end_date DESC, id DESC LIMIT 1', [$memberId]);
        $today = date('Y-m-d');
        $start = $today;
        if ($cur && $cur['status'] === 'ACTIVE' && $cur['end_date'] >= $today) {
            $start = date('Y-m-d', strtotime($cur['end_date'] . ' +1 day'));
        }
        $end = membership_end_date($start, (int) $pkg['duration_days'], (string) ($pkg['name'] ?? ''));
        $id = $this->create([
            'member_id' => $memberId, 'package_id' => $packageId,
            'package_name_snapshot' => $pkg['name'], 'tier_snapshot' => $pkg['tier'],
            'price_snapshot' => $pkg['price'], 'start_date' => $start, 'end_date' => $end,
            'status' => 'ACTIVE',
        ]);
        return ['id' => $id, 'start' => $start, 'end' => $end, 'price' => (float) $pkg['price'],
            'name' => $pkg['name'], 'tier' => $pkg['tier']];
    }
}

class LessonPackage extends Model
{
    protected string $table = 'lesson_packages';
    public function active(): array
    {
        return $this->query('SELECT * FROM lesson_packages WHERE is_active = 1 ORDER BY id');
    }
}

class Instructor extends Model
{
    protected string $table = 'instructors';
    public function active(): array
    {
        return $this->query('SELECT * FROM instructors WHERE is_active = 1 ORDER BY name');
    }
}

class LessonSchedule extends Model
{
    protected string $table = 'lesson_schedules';
    public function listAll(): array
    {
        return $this->query(
            'SELECT ls.*, i.name AS instruktur FROM lesson_schedules ls
             LEFT JOIN instructors i ON i.id = ls.instructor_id ORDER BY
             FIELD(day_of_week,"Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu"), start_time');
    }
    public function today(): array
    {
        $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'][date('l')];
        return $this->query(
            'SELECT ls.*, i.name AS instruktur FROM lesson_schedules ls
             LEFT JOIN instructors i ON i.id = ls.instructor_id
             WHERE ls.day_of_week = ? AND ls.is_active = 1 ORDER BY start_time', [$hari]);
    }
}

class LessonStudent extends Model
{
    protected string $table = 'lesson_students';
    public function listAll(): array
    {
        return $this->query(
            'SELECT s.*, lp.name AS paket, i.name AS instruktur,
                    (SELECT COUNT(*) FROM lesson_sessions ss WHERE ss.student_id = s.id AND ss.attendance = ?) AS hadir
             FROM lesson_students s
             LEFT JOIN lesson_packages lp ON lp.id = s.package_id
             LEFT JOIN instructors i ON i.id = s.instructor_id
             ORDER BY s.created_at DESC', ['HADIR']);
    }
    public function search(string $q): array
    {
        return $this->query('SELECT id, name FROM lesson_students WHERE name LIKE ? AND status = ? ORDER BY name LIMIT 15', ['%' . $q . '%', 'ACTIVE']);
    }
}

class LessonSession extends Model
{
    protected string $table = 'lesson_sessions';
    public function mark(int $studentId, ?int $scheduleId, string $date, ?int $instructorId, string $attendance): void
    {
        $ex = $this->queryOne('SELECT id FROM lesson_sessions WHERE student_id = ? AND session_date = ?', [$studentId, $date]);
        if ($ex) {
            $this->update($ex['id'], ['attendance' => $attendance, 'schedule_id' => $scheduleId, 'instructor_id' => $instructorId]);
        } else {
            $this->create(['student_id' => $studentId, 'schedule_id' => $scheduleId, 'session_date' => $date,
                'instructor_id' => $instructorId, 'attendance' => $attendance]);
        }
    }
    public function forStudent(int $studentId): array
    {
        return $this->query('SELECT * FROM lesson_sessions WHERE student_id = ? ORDER BY session_date DESC LIMIT 20', [$studentId]);
    }
}
