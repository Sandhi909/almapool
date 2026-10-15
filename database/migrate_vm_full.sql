-- ============================================
-- ALMAPOOL - Migrasi FULL untuk VM (sekali jalan, aman diulang)
-- DB: db_kolam_renang
-- Isi: require_jenjang/nama/usia/is_internal + master_jenjang + master_kelompok_usia + master_rombongan
-- Cara pakai: mysql -u root -p db_kolam_renang < migrate_vm_full.sql
-- atau phpMyAdmin > Import
-- ============================================
USE db_kolam_renang;

-- 0. Pastikan DB ada
CREATE DATABASE IF NOT EXISTS db_kolam_renang CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 1. Tambah kolom di ticket_types jika belum ada (pakai procedure agar aman diulang)
DROP PROCEDURE IF EXISTS add_col_if_missing;
DELIMITER $$
CREATE PROCEDURE add_col_if_missing(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=tbl AND COLUMN_NAME=col) THEN
    SET @sql = ddl;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;
CALL add_col_if_missing('ticket_types','require_jenjang',"ALTER TABLE ticket_types ADD COLUMN require_jenjang TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
CALL add_col_if_missing('ticket_types','require_nama',"ALTER TABLE ticket_types ADD COLUMN require_nama TINYINT(1) NOT NULL DEFAULT 0 AFTER require_jenjang");
CALL add_col_if_missing('ticket_types','require_usia',"ALTER TABLE ticket_types ADD COLUMN require_usia TINYINT(1) NOT NULL DEFAULT 0 AFTER require_nama");
CALL add_col_if_missing('ticket_types','is_internal',"ALTER TABLE ticket_types ADD COLUMN is_internal TINYINT(1) NOT NULL DEFAULT 0 AFTER require_usia");
DROP PROCEDURE IF EXISTS add_col_if_missing;

-- 2. Buat tabel master (IF NOT EXISTS = aman diulang)
CREATE TABLE IF NOT EXISTS master_jenjang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS master_kelompok_usia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  min_age INT NULL,
  max_age INT NULL,
  color VARCHAR(20) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS master_rombongan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Seed master data (IGNORE = tidak duplikat)
INSERT IGNORE INTO master_jenjang (name, sort_order) VALUES
 ('SD',1), ('SMP',2), ('SMA',3), ('Mahasiswa MU',4);
INSERT IGNORE INTO master_kelompok_usia (name, min_age, max_age, color, sort_order) VALUES
 ('Anak-anak',0,12,'info',1), ('Remaja',13,17,'warning',2), ('Dewasa',18,99,'primary',3);
INSERT IGNORE INTO master_rombongan (name) VALUES
 ('Umum'),('Karang Taruna'),('Instansi'),('Komunitas'),('Sekolah Eksternal');

-- 4. Seed / update tiket rombongan default (butuh kolom baru sudah ada)
INSERT INTO ticket_types (name, color, price_a, price_b, price_c, is_active, require_jenjang, require_nama, require_usia, is_internal)
SELECT * FROM (SELECT 'Rombongan Internal >20 Siswa Almasoem' AS name, 'Biru Tua' AS color, 12000 AS price_a, 12000 AS price_b, 15000 AS price_c, 1 AS is_active, 1 AS require_jenjang, 0 AS require_nama, 0 AS require_usia, 1 AS is_internal) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM ticket_types WHERE name='Rombongan Internal >20 Siswa Almasoem');
INSERT INTO ticket_types (name, color, price_a, price_b, price_c, is_active, require_jenjang, require_nama, require_usia, is_internal)
SELECT * FROM (SELECT 'Rombongan Eksternal' AS name, 'Orange' AS color, 20000 AS price_a, 25000 AS price_b, 30000 AS price_c, 1 AS is_active, 0 AS require_jenjang, 1 AS require_nama, 0 AS require_usia, 0 AS is_internal) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM ticket_types WHERE name='Rombongan Eksternal');

-- 5. Perbaiki flag is_internal / require untuk data lama
UPDATE ticket_types SET require_jenjang=1 WHERE name LIKE '%Internal >20%' AND require_jenjang=0;
UPDATE ticket_types SET require_nama=1 WHERE name LIKE '%Eksternal%' AND require_nama=0;
UPDATE ticket_types SET is_internal=1 WHERE (name LIKE '%Karyawan%' OR name LIKE '%Santri%') AND is_internal=0;
UPDATE ticket_types SET is_internal=1 WHERE id=2 AND is_internal=0;
UPDATE ticket_types SET is_internal=1 WHERE name LIKE '%Internal%' AND is_internal=0;
UPDATE ticket_types SET is_internal=0 WHERE name IN ('Umum','Rombongan Eksternal','Rombongan 10-19 orang','Pengantar (Minggu & Libur)','Voucher') AND is_internal=1;

-- 6. Verifikasi
SELECT 'ticket_types' AS tbl, id, name, is_internal, require_jenjang, require_nama, require_usia FROM ticket_types ORDER BY id;
SELECT 'master_jenjang' AS tbl, * FROM master_jenjang ORDER BY sort_order;
SELECT 'master_kelompok_usia' AS tbl, * FROM master_kelompok_usia ORDER BY sort_order;
SELECT 'master_rombongan' AS tbl, * FROM master_rombongan ORDER BY name;
