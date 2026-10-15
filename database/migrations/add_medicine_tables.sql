-- Migration untuk fitur stok obat kolam
-- Tabel untuk menyimpan data obat-obatan kolam
CREATE TABLE IF NOT EXISTS medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category_id INT NULL,
  unit_id INT NULL,
  current_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
  min_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES medicine_categories(id),
  FOREIGN KEY (unit_id) REFERENCES medicine_units(id)
);

-- Tabel untuk menyimpan riwayat pemakaian obat
CREATE TABLE IF NOT EXISTS medicine_usage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medicine_id INT NOT NULL,
  usage_date DATE NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  purpose VARCHAR(255) NULL,
  used_by INT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (medicine_id) REFERENCES medicines(id),
  FOREIGN KEY (used_by) REFERENCES users(id)
);

-- Tabel untuk menyimpan riwayat stok masuk (restock)
CREATE TABLE IF NOT EXISTS medicine_stock (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medicine_id INT NOT NULL,
  stock_date DATE NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  supplier VARCHAR(100) NULL,
  notes VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (medicine_id) REFERENCES medicines(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);
