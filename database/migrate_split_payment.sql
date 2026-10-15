-- Migrasi Pembayaran Gabungan (split payment) - Almapool
-- Kasir bisa terima 1 transaksi dibayar 2-3 metode sekaligus (mis. QRIS 25rb + Tunai 25rb).
USE db_kolam_renang;

ALTER TABLE transactions MODIFY payment_method ENUM('TUNAI','QRIS','TRANSFER','GABUNGAN') NOT NULL;

DROP PROCEDURE IF EXISTS add_pay_col;
DELIMITER $$
CREATE PROCEDURE add_pay_col(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=tbl AND COLUMN_NAME=col) THEN
    SET @sql = ddl;
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;
CALL add_pay_col('transactions','pay_tunai',"ALTER TABLE transactions ADD COLUMN pay_tunai DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER payment_method");
CALL add_pay_col('transactions','pay_qris',"ALTER TABLE transactions ADD COLUMN pay_qris DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER pay_tunai");
CALL add_pay_col('transactions','pay_transfer',"ALTER TABLE transactions ADD COLUMN pay_transfer DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER pay_qris");
DROP PROCEDURE IF EXISTS add_pay_col;

-- Backfill historis: transaksi metode tunggal -> seluruh nominal masuk kolom metodenya.
UPDATE transactions SET pay_tunai = amount WHERE payment_method = 'TUNAI' AND pay_tunai = 0 AND pay_qris = 0 AND pay_transfer = 0;
UPDATE transactions SET pay_qris = amount WHERE payment_method = 'QRIS' AND pay_tunai = 0 AND pay_qris = 0 AND pay_transfer = 0;
UPDATE transactions SET pay_transfer = amount WHERE payment_method = 'TRANSFER' AND pay_tunai = 0 AND pay_qris = 0 AND pay_transfer = 0;

SHOW COLUMNS FROM transactions LIKE 'pay_%';
SELECT payment_method, COUNT(*) n, SUM(amount) total FROM transactions GROUP BY payment_method;
