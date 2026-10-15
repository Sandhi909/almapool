-- ============================================
-- Migrasi Rombongan Master - untuk VM
-- DB: db_kolam_renang
-- Jalankan via phpMyAdmin > Import atau mysql CLI
-- Aman diulang (IF NOT EXISTS / check)
-- ============================================

USE db_kolam_renang;

-- 1. Tambah kolom flag di ticket_types (untuk pilihan di Master)
-- Cek dulu apakah kolom sudah ada (MySQL 8 tidak support IF NOT EXISTS untuk ADD COLUMN, jadi pakai prosedur)
-- Cara simple: jalankan 2 baris ini, jika sudah ada akan error tapi aman diabaikan
ALTER TABLE ticket_types ADD COLUMN require_jenjang TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;
ALTER TABLE ticket_types ADD COLUMN require_nama TINYINT(1) NOT NULL DEFAULT 0 AFTER require_jenjang;
ALTER TABLE ticket_types ADD COLUMN is_internal TINYINT(1) NOT NULL DEFAULT 0 AFTER require_nama;

-- Jika error "Duplicate column name", abaikan saja (kolom sudah ada).

-- 2. Tambah tiket rombongan default (jika belum ada)
INSERT INTO ticket_types (name, color, price_a, price_b, price_c, is_active, require_jenjang, require_nama, is_internal)
SELECT * FROM (SELECT 'Rombongan Internal >20 Siswa Almasoem' AS name, 'Biru Tua' AS color, 12000 AS price_a, 12000 AS price_b, 15000 AS price_c, 1 AS is_active, 1 AS require_jenjang, 0 AS require_nama, 1 AS is_internal) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM ticket_types WHERE name = 'Rombongan Internal >20 Siswa Almasoem');

INSERT INTO ticket_types (name, color, price_a, price_b, price_c, is_active, require_jenjang, require_nama, is_internal)
SELECT * FROM (SELECT 'Rombongan Eksternal' AS name, 'Orange' AS color, 20000 AS price_a, 25000 AS price_b, 30000 AS price_c, 1 AS is_active, 0 AS require_jenjang, 1 AS require_nama, 0 AS is_internal) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM ticket_types WHERE name = 'Rombongan Eksternal');

-- 3. Update flag untuk tiket yang sudah ada (jaga konsistensi)
UPDATE ticket_types SET require_jenjang = 1 WHERE name LIKE '%Internal >20%' AND require_jenjang = 0;
UPDATE ticket_types SET require_nama = 1 WHERE name LIKE '%Eksternal%' AND require_nama = 0;
-- Kategori Internal/External untuk laporan kunjungan (akurat)
UPDATE ticket_types SET is_internal = 1 WHERE (name LIKE '%Karyawan%' OR name LIKE '%Santri%') AND is_internal = 0;
UPDATE ticket_types SET is_internal = 1 WHERE id = 2 AND is_internal = 0; -- Siswa Al Ma'soem
UPDATE ticket_types SET is_internal = 1 WHERE name LIKE '%Internal%' AND is_internal = 0;
UPDATE ticket_types SET is_internal = 0 WHERE name IN ('Umum','Rombongan Eksternal','Rombongan 10-19 orang','Pengantar (Minggu & Libur)','Voucher') AND is_internal = 1;

-- 4. Verifikasi
SELECT id, name, color, price_a, price_b, price_c, is_active, is_internal, require_jenjang, require_nama FROM ticket_types ORDER BY id;

-- ============================================
-- Cara pakai di VM:
-- 1. Copy file ini ke VM: /tmp/migrate_rombongan_master_vm.sql
-- 2. Jalankan:
--    mysql -u root -p db_kolam_renang < /tmp/migrate_rombongan_master_vm.sql
--    atau via phpMyAdmin > db_kolam_renang > Import > pilih file > Go
-- 3. Cek Master > Paket Tiket harus muncul kolom Opsi (Jenjang/Nama)
-- ============================================
