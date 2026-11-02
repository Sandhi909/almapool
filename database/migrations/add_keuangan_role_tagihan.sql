-- Migration: Add keuangan role + flag tiket tagihan
-- Run on both local and VM (once).

-- 1. Add keuangan to role enum
ALTER TABLE users MODIFY COLUMN role ENUM('admin','kasir','petugas','manajemen','instruktur','internal_control','keuangan') NOT NULL;

-- 2. Flag tiket yang masuk daftar transaksi tagihan keuangan
ALTER TABLE ticket_types ADD COLUMN is_tagihan TINYINT(1) NOT NULL DEFAULT 0;

-- 3. Tandai tiket tagihan sesuai daftar
UPDATE ticket_types SET is_tagihan = 1 WHERE
  name IN (
    'Internal YAB (Kegiatan Cabang Olahraga)',
    'Internal YAB (Kegiatan Ekstrakurikuler)',
    'Internal YAB (Kegiatan Sport Day)',
    'Internal PSAM (Kegiatan Ekskul PSAM)',
    'Internal PSAM (Kegiatan Renang PSAM)'
  )
  OR name LIKE 'Santri Al Ma%';
