<?php
class Medicine extends Model
{
    protected string $table = 'medicines';

    public function active(): array
    {
        return $this->query('SELECT m.*, mc.name as category_name, mu.name as unit_name FROM medicines m LEFT JOIN medicine_categories mc ON mc.id = m.category_id LEFT JOIN medicine_units mu ON mu.id = m.unit_id WHERE m.is_active = 1 ORDER BY m.name ASC');
    }

    public function lowStock(): array
    {
        return $this->query('SELECT m.*, mc.name as category_name, mu.name as unit_name FROM medicines m LEFT JOIN medicine_categories mc ON mc.id = m.category_id LEFT JOIN medicine_units mu ON mu.id = m.unit_id WHERE m.is_active = 1 AND m.current_stock <= m.min_stock ORDER BY m.name ASC');
    }

    public function categories(): array
    {
        return $this->query('SELECT * FROM medicine_categories WHERE is_active = 1 ORDER BY name ASC');
    }

    public function units(): array
    {
        return $this->query('SELECT * FROM medicine_units WHERE is_active = 1 ORDER BY name ASC');
    }

    public function addStock(int $id, float $quantity, string $supplier = null, string $notes = null): bool
    {
        $this->db->beginTransaction();
        try {
            // Update current stock
            $this->db->prepare('UPDATE medicines SET current_stock = current_stock + ?, updated_at = NOW() WHERE id = ?')->execute([$quantity, $id]);

            // Record stock entry
            $this->db->prepare('INSERT INTO medicine_stock (medicine_id, stock_date, quantity, supplier, notes, created_by) VALUES (?, CURDATE(), ?, ?, ?, ?)')->execute([
                $id, $quantity, $supplier, $notes, auth_user()['id']
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function useStock(int $id, float $quantity, string $purpose, string $notes = null): bool
    {
        $this->db->beginTransaction();
        try {
            $medicine = $this->find($id);
            if (!$medicine) throw new Exception('Obat tidak ditemukan');
            if ($medicine['current_stock'] < $quantity) throw new Exception('Stok tidak mencukupi');

            // Update current stock
            $this->db->prepare('UPDATE medicines SET current_stock = current_stock - ?, updated_at = NOW() WHERE id = ?')->execute([$quantity, $id]);

            // Record usage
            $this->db->prepare('INSERT INTO medicine_usage (medicine_id, usage_date, quantity, purpose, notes, used_by) VALUES (?, CURDATE(), ?, ?, ?, ?)')->execute([
                $id, $quantity, $purpose, $notes, auth_user()['id']
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getStockHistory(int $id, int $limit = 50): array
    {
        return $this->query(
            'SELECT ms.*, u.name as created_by_name
             FROM medicine_stock ms
             LEFT JOIN users u ON u.id = ms.created_by
             WHERE ms.medicine_id = ?
             ORDER BY ms.stock_date DESC, ms.id DESC
             LIMIT ?',
            [$id, $limit]
        );
    }

    public function getUsageHistory(int $id, int $limit = 50): array
    {
        return $this->query(
            'SELECT mu.*, u.name as used_by_name
             FROM medicine_usage mu
             LEFT JOIN users u ON u.id = mu.used_by
             WHERE mu.medicine_id = ?
             ORDER BY mu.usage_date DESC, mu.id DESC
             LIMIT ?',
            [$id, $limit]
        );
    }
}
