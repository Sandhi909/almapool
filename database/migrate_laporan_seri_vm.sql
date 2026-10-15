-- ============================================
-- ALMAPOOL VM - Migrasi Laporan + Nomor Seri Tiket
-- 1 file import, aman diulang (IF NOT EXISTS)
-- DB: db_kolam_renang
-- Isi:
--  - ticket_types: serial_prefix, serial_next (untuk input Nomor Seri Awal di Master Tiket)
--  - transaction_items: serial_prefix, serial_start, serial_end (untuk cetak seri per transaksi & rekap Laporan)
-- Laporan tidak butuh tabel baru - cukup kolom di atas, view laporan otomatis ambil MIN/MAX seri
-- Cara import: mysql -u root -p db_kolam_renang < migrate_laporan_seri_vm.sql
-- atau phpMyAdmin > db_kolam_renang > Import
-- ============================================
USE db_kolam_renang;

DROP PROCEDURE IF EXISTS add_seri_col;
DELIMITER $$
CREATE PROCEDURE add_seri_col(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=tbl AND COLUMN_NAME=col) THEN
    SET @sql = ddl;
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;
CALL add_seri_col('ticket_types','serial_prefix',"ALTER TABLE ticket_types ADD COLUMN serial_prefix VARCHAR(10) NOT NULL DEFAULT '' AFTER is_internal");
CALL add_seri_col('ticket_types','serial_next',"ALTER TABLE ticket_types ADD COLUMN serial_next INT NOT NULL DEFAULT 1 AFTER serial_prefix");
CALL add_seri_col('transaction_items','serial_prefix',"ALTER TABLE transaction_items ADD COLUMN serial_prefix VARCHAR(10) NULL AFTER subtotal");
CALL add_seri_col('transaction_items','serial_start',"ALTER TABLE transaction_items ADD COLUMN serial_start INT NULL AFTER serial_prefix");
CALL add_seri_col('transaction_items','serial_end',"ALTER TABLE transaction_items ADD COLUMN serial_end INT NULL AFTER serial_start");
DROP PROCEDURE IF EXISTS add_seri_col;

-- Verifikasi
SELECT id, name, serial_prefix, serial_next FROM ticket_types ORDER BY id;
SHOW COLUMNS FROM ticket_types LIKE 'serial%';
SHOW COLUMNS FROM transaction_items LIKE 'serial%';
-- Contoh cek rekap laporan per periode (seri awal/akhir per tiket):
-- SELECT tt.name, MIN(ti.serial_start) seri_awal, MAX(ti.serial_end) seri_akhir
-- FROM transaction_items ti JOIN ticket_types tt ON tt.id=ti.item_ref_id
-- WHERE ti.item_type='TIKET' AND ti.serial_start IS NOT NULL GROUP BY tt.id;
