-- Migrasi Master Jenjang, Kelompok Usia, Rombongan Eksternal untuk Almapool
USE db_kolam_renang;

-- 1. Master Jenjang (SD, SMP, SMA, Mahasiswa MU, dll)
CREATE TABLE IF NOT EXISTS master_jenjang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Master Kelompok Usia (Anak-anak, Remaja, Dewasa)
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

-- 3. Master Rombongan Eksternal (daftar nama rombongan)
CREATE TABLE IF NOT EXISTS master_rombongan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tambah flag baru di ticket_types untuk kelompok usia (opsional per tiket)
ALTER TABLE ticket_types ADD COLUMN require_usia TINYINT(1) NOT NULL DEFAULT 0 AFTER require_nama;

-- Seed default
INSERT IGNORE INTO master_jenjang (name, sort_order) VALUES
 ('SD', 1), ('SMP', 2), ('SMA', 3), ('Mahasiswa MU', 4);

INSERT IGNORE INTO master_kelompok_usia (name, min_age, max_age, color, sort_order) VALUES
 ('Anak-anak', 0, 12, 'info', 1),
 ('Remaja', 13, 17, 'warning', 2),
 ('Dewasa', 18, 99, 'primary', 3);

INSERT IGNORE INTO master_rombongan (name) VALUES
 ('Umum'), ('Karang Taruna'), ('Instansi'), ('Komunitas'), ('Sekolah Eksternal');
