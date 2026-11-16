-- Migration: kolom KAS untuk Stok Opname (menyesuaikan format SO Kolam YAB)
-- DB: db_kolam_renang
-- Aman dijalankan ulang, error duplikat kolom berarti kolom sudah ada.
ALTER TABLE stok_opname
  ADD COLUMN kas_di_kantor DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER biaya_keluar,
  ADD COLUMN bukti_setor  DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER kas_di_kantor,
  ADD COLUMN transfer_yab DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER bukti_setor,
  ADD COLUMN qris_kolam  DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER transfer_yab,
  ADD COLUMN sewa_qris   DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER qris_kolam;
