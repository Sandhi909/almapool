<?php
class OpCost extends Model
{
    protected string $table = 'operational_costs';
    public function monthly(int $year, int $month): array
    {
        return $this->query(
            "SELECT * FROM operational_costs WHERE DATE_FORMAT(cost_date,'%Y-%m') = ?
             ORDER BY cost_date DESC, id DESC", [sprintf('%04d-%02d', $year, $month)]);
    }
    public function total(int $year, int $month): float
    {
        return (float) ($this->queryOne(
            "SELECT COALESCE(SUM(amount),0) t FROM operational_costs WHERE DATE_FORMAT(cost_date,'%Y-%m') = ?",
            [sprintf('%04d-%02d', $year, $month)])['t'] ?? 0);
    }
}

class Deposit extends Model
{
    protected string $table = 'deposits';
    public function monthly(int $year, int $month): array
    {
        return $this->query(
            "SELECT * FROM deposits WHERE DATE_FORMAT(deposit_date,'%Y-%m') = ?
             ORDER BY deposit_date DESC, id DESC", [sprintf('%04d-%02d', $year, $month)]);
    }
    public function totals(int $year, int $month): array
    {
        $rows = $this->query(
            "SELECT kind, COALESCE(SUM(amount),0) t FROM deposits WHERE DATE_FORMAT(deposit_date,'%Y-%m') = ? GROUP BY kind",
            [sprintf('%04d-%02d', $year, $month)]);
        $out = ['QRIS' => 0, 'TRANSFER' => 0, 'UANG' => 0];
        foreach ($rows as $r) $out[$r['kind']] = (float) $r['t'];
        return $out;
    }
}

class Setting extends Model
{
    protected string $table = 'settings';
    public function allAsMap(): array
    {
        $rows = $this->query('SELECT setting_key, setting_value FROM settings');
        $out = [];
        foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
        return $out;
    }
    public function get(string $key, string $default = ''): string
    {
        $r = $this->queryOne('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
        return $r ? (string) $r['setting_value'] : $default;
    }
    public function set(string $key, string $value): void
    {
        if ($this->queryOne('SELECT setting_key FROM settings WHERE setting_key = ?', [$key])) {
            $this->db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?')->execute([$value, $key]);
        } else {
            $this->db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?)')->execute([$key, $value]);
        }
    }
}
