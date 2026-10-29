-- Migration: Add pay_tagihan column to transactions
-- Run this on both local and VM

ALTER TABLE transactions ADD COLUMN pay_tagihan DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER pay_transfer;