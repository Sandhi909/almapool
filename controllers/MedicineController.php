<?php
class MedicineController
{
    public function index(): array
    {
        require_role(['admin', 'kasir', 'petugas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $this->handlePost();
        }
        return [
            'MEDICINES' => (new Medicine())->active(),
            'LOW_STOCK' => (new Medicine())->lowStock(),
            'CATEGORIES' => (new Medicine())->categories(),
            'UNITS' => (new Medicine())->units(),
        ];
    }

    private function handlePost(): void
    {
        $act = (string) post('action', '');
        $medicine = new Medicine();

        if ($act === 'tambah') {
            $nama = (string) post('nama', '');
            if ($nama === '') {
                flash('error', 'Nama obat wajib diisi.');
                redirect('?page=obat');
            }
            $medicine->create([
                'name' => $nama,
                'category_id' => (int) post('kategori_id', 0) ?: null,
                'unit_id' => (int) post('satuan_id', 0) ?: null,
                'current_stock' => (float) post('stok_awal', 0),
                'min_stock' => (float) post('stok_minimum', 0),
                'notes' => post('catatan') ?: null,
            ]);
            flash('success', 'Obat berhasil ditambahkan.');
        } elseif ($act === 'edit') {
            $id = (int) post('id', 0);
            $med = $medicine->find($id);
            if (!$med) {
                flash('error', 'Obat tidak ditemukan.');
                redirect('?page=obat');
            }
            $medicine->update($id, [
                'name' => post('nama'),
                'category_id' => (int) post('kategori_id', 0) ?: null,
                'unit_id' => (int) post('satuan_id', 0) ?: null,
                'min_stock' => (float) post('stok_minimum', 0),
                'notes' => post('catatan') ?: null,
            ]);
            flash('success', 'Obat berhasil diperbarui.');
        } elseif ($act === 'hapus') {
            $id = (int) post('id', 0);
            $medicine->update($id, ['is_active' => 0]);
            flash('success', 'Obat berhasil dihapus.');
        } elseif ($act === 'restock') {
            $id = (int) post('id', 0);
            $qty = (float) post('jumlah', 0);
            if ($qty <= 0) {
                flash('error', 'Jumlah stok harus lebih dari 0.');
                redirect('?page=obat');
            }
            $medicine->addStock($id, $qty, post('supplier') ?: null, post('catatan') ?: null);
            flash('success', 'Stok berhasil ditambahkan.');
        } elseif ($act === 'pakai') {
            $id = (int) post('id', 0);
            $qty = (float) post('jumlah', 0);
            $purpose = (string) post('tujuan', '');
            if ($qty <= 0) {
                flash('error', 'Jumlah pemakaian harus lebih dari 0.');
                redirect('?page=obat');
            }
            if ($purpose === '') {
                flash('error', 'Tujuan pemakaian wajib diisi.');
                redirect('?page=obat');
            }
            try {
                $medicine->useStock($id, $qty, $purpose, post('catatan') ?: null);
                flash('success', 'Pemakaian obat berhasil dicatat.');
            } catch (Exception $e) {
                flash('error', $e->getMessage());
            }
        }
        redirect('?page=obat');
    }

    public function history(int $id): array
    {
        require_role(['admin', 'kasir', 'petugas']);
        $medicine = (new Medicine())->find($id);
        if (!$medicine) {
            http_response_code(404);
            die('Obat tidak ditemukan.');
        }
        return [
            'medicine' => $medicine,
            'stock_history' => (new Medicine())->getStockHistory($id),
            'usage_history' => (new Medicine())->getUsageHistory($id),
        ];
    }

    public function manageCategories(): array
    {
        require_role(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $act = (string) post('action', '');
            if ($act === 'tambah_kategori') {
                $nama = (string) post('nama', '');
                if ($nama === '') {
                    flash('error', 'Nama kategori wajib diisi.');
                    redirect('?page=master&tab=obat');
                }
                db()->prepare('INSERT INTO medicine_categories (name) VALUES (?)')->execute([$nama]);
                flash('success', 'Kategori berhasil ditambahkan.');
            } elseif ($act === 'hapus_kategori') {
                $id = (int) post('id', 0);
                db()->prepare('UPDATE medicine_categories SET is_active = 0 WHERE id = ?')->execute([$id]);
                flash('success', 'Kategori berhasil dihapus.');
            }
            redirect('?page=master&tab=obat');
        }
        return ['CATEGORIES' => (new Medicine())->categories()];
    }

    public function manageUnits(): array
    {
        require_role(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $act = (string) post('action', '');
            if ($act === 'tambah_satuan') {
                $nama = (string) post('nama', '');
                if ($nama === '') {
                    flash('error', 'Nama satuan wajib diisi.');
                    redirect('?page=master&tab=obat');
                }
                db()->prepare('INSERT INTO medicine_units (name) VALUES (?)')->execute([$nama]);
                flash('success', 'Satuan berhasil ditambahkan.');
            } elseif ($act === 'hapus_satuan') {
                $id = (int) post('id', 0);
                db()->prepare('UPDATE medicine_units SET is_active = 0 WHERE id = ?')->execute([$id]);
                flash('success', 'Satuan berhasil dihapus.');
            }
            redirect('?page=master&tab=obat');
        }
        return ['UNITS' => (new Medicine())->units()];
    }
}
