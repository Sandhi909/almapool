-- ============================================
-- Migrasi Kategori Internal/Eksternal - untuk VM
-- Lanjutan dari migrate_rombongan_master_vm.sql yang sudah diimport
-- DB: db_kolam_renang
-- ============================================

USE db_kolam_renang;

-- Tambah kolom kategori Internal/Eksternal (jika belum ada)
ALTER TABLE ticket_types ADD COLUMN is_internal TINYINT(1) NOT NULL DEFAULT 0 AFTER require_nama;
-- Jika error "Duplicate column name" abaikan saja

-- Update flag is_internal untuk tiket existing
UPDATE ticket_types SET is_internal = 1 WHERE name LIKE '%Karyawan%' AND is_internal = 0;
UPDATE ticket_types SET is_internal = 1 WHERE name LIKE '%Santri%' AND is_internal = 0;
UPDATE ticket_types SET is_internal = 1 WHERE name LIKE '%Internal >20%' AND is_internal = 0;
UPDATE ticket_types SET is_internal = 1 WHERE id = 2 AND is_internal = 0; -- Siswa Al Ma'soem / Mhs / rombongan ≤20

-- Pastikan eksternal tetap 0
UPDATE ticket_types SET is_internal = 0 WHERE name IN ('Umum','Rombongan Eksternal','Rombongan 10-19 orang','Pengantar (Minggu & Libur)','Voucher') AND is_internal = 1;

-- Verifikasi
SELECT id, name, is_internal, require_jenjang, require_nama FROM ticket_types ORDER BY id;

-- Cara pakai di VM:
-- scp C:\xampp\htdocs\almapool\database\migrate_kategori_vm.sql user@IP_VM:/tmp/
-- mysql -u root -p db_kolam_renang < /tmp/migrate_kategori_vm.sql
