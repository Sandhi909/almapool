<?php
class StokOpname extends Model
{
    protected string $table = 'stok_opname';

    public function byDate(string $date): ?array
    {
        return $this->queryOne('SELECT * FROM stok_opname WHERE opname_date = ? LIMIT 1', [$date]);
    }

    public function recent(int $limit = 20): array
    {
        return $this->query('SELECT so.*, u.name AS created_by_name FROM stok_opname so LEFT JOIN users u ON so.created_by = u.id ORDER BY so.opname_date DESC, so.id DESC LIMIT ?', [$limit]);
    }
}
?>