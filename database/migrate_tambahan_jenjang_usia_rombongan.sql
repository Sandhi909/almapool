-- Migrasi tambahan ALMAPOOL - 1 file import (aman diulang)
-- Isi: 3 tabel master (jenjang, kelompok usia, rombongan) + kolom require_usia
-- DB: db_kolam_renang
USE db_kolam_renang;

-- 1. Tabel master_jenjang
CREATE TABLE IF NOT EXISTS master_jenjang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel master_kelompok_usia
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

-- 3. Tabel master_rombongan
CREATE TABLE IF NOT EXISTS master_rombongan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Kolom require_usia di ticket_types (pakai procedure biar tidak error jika sudah ada)
DROP PROCEDURE IF EXISTS add_require_usia;
DELIMITER $$
CREATE PROCEDURE add_require_usia()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ticket_types' AND COLUMN_NAME='require_usia') THEN
    ALTER TABLE ticket_types ADD COLUMN require_usia TINYINT(1) NOT NULL DEFAULT 0 AFTER require_nama;
  END IF;
END$$
DELIMITER ;
CALL add_require_usia();
DROP PROCEDURE IF EXISTS add_require_usia;

-- 5. Seed data (tidak duplikat)
INSERT IGNORE INTO master_jenjang (name, sort_order) VALUES
 ('SD',1), ('SMP',2), ('SMA',3), ('Mahasiswa MU',4);

INSERT IGNORE INTO master_kelompok_usia (name, min_age, max_age, color, sort_order) VALUES
 ('Anak-anak',0,12,'info',1), ('Remaja',13,17,'warning',2), ('Dewasa',18,99,'primary',3);

INSERT IGNORE INTO master_rombongan (name) VALUES
 ('Umum'),('Karang Taruna'),('Instansi'),('Komunitas'),('Sekolah Eksternal');

-- Verifikasi
SELECT * FROM master_jenjang ORDER BY sort_order;
SELECT * FROM master_kelompok_usia ORDER BY sort_order;
SELECT * FROM master_rombongan ORDER BY name;
SHOW COLUMNS FROM ticket_types LIKE 'require_usia';
