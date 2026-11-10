-- Migration: Add cashback column to transactions
-- Cashback bersifat display-only: hanya tampil sebagai rincian di struk,
-- tidak memengaruhi nominal, total, laporan, atau perhitungan apa pun.
-- Run this on both local and VM

ALTER TABLE transactions ADD COLUMN cashback DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER pay_tagihan;
