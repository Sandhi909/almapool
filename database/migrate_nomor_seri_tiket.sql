-- Migrasi Nomor Seri Tiket - Almapool
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

SELECT * FROM ticket_types;
SHOW COLUMNS FROM transaction_items LIKE 'serial%';
