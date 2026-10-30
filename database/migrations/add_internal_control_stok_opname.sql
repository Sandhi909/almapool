-- Migration: Add internal_control role and stok_opname table
-- Run on both local and VM

-- 1. Add internal_control to role enum
ALTER TABLE users MODIFY COLUMN role ENUM('admin','kasir','petugas','manajemen','instruktur','internal_control') NOT NULL;

-- 2. Create stok_opname table
CREATE TABLE IF NOT EXISTS stok_opname (
  id INT AUTO_INCREMENT PRIMARY KEY,
  opname_date DATE NOT NULL,
  tunai_masuk DECIMAL(14,2) NOT NULL DEFAULT 0,
  tunai_real DECIMAL(14,2) NOT NULL DEFAULT 0,
  qris DECIMAL(14,2) NOT NULL DEFAULT 0,
  transfer DECIMAL(14,2) NOT NULL DEFAULT 0,
  tagihan DECIMAL(14,2) NOT NULL DEFAULT 0,
  biaya_keluar DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);