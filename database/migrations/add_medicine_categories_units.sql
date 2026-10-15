-- Migration untuk kategori dan satuan obat
CREATE TABLE IF NOT EXISTS medicine_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicine_units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default data
INSERT INTO medicine_categories (name) VALUES 
('Kaporit'), ('pH Minus'), ('pH Plus'), ('Tawas'), ('Pakan Ikan'), ('Lainnya');

INSERT INTO medicine_units (name) VALUES 
('botol'), ('liter'), ('kg'), ('sak'), ('paket'), ('lembar');
