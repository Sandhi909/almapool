<?php
// Base model: query via PDO prepared statements.
abstract class Model
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = db();
    }

    public function all(string $orderBy = 'id ASC'): array
    {
        return $this->db->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}")->fetchAll();
    }

    public function find($id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $cols = array_keys($data);
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO `{$this->table}` (`" . implode('`,`', $cols) . "`) VALUES ({$ph})";
        $this->db->prepare($sql)->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update($id, array $data): bool
    {
        $sets = implode(', ', array_map(fn ($c) => "`{$c}` = ?", array_keys($data)));
        return $this->db->prepare("UPDATE `{$this->table}` SET {$sets} WHERE id = ?")
            ->execute([...array_values($data), $id]);
    }

    public function delete($id): bool
    {
        return $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?")->execute([$id]);
    }

    public function query(string $sql, array $params = []): array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function queryOne(string $sql, array $params = []): ?array
    {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }
}
