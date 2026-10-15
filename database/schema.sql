-- Skema database db_kolam_renang (Kolam Renang Al Ma'soem, sesi Putra/Putri).
CREATE DATABASE IF NOT EXISTS db_kolam_renang CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_kolam_renang;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','kasir','petugas','manajemen','instruktur') NOT NULL DEFAULT 'kasir',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_number VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  identity_number VARCHAR(30) NULL,
  phone VARCHAR(20) NULL,
  gender ENUM('L','P') NULL,
  birth_date DATE NULL,
  address VARCHAR(255) NULL,
  photo VARCHAR(255) NULL,
  qr_identifier VARCHAR(30) NOT NULL UNIQUE,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS membership_packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  tier ENUM('GOLD','SILVER') NOT NULL,
  duration_days INT NOT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS memberships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  package_id INT NOT NULL,
  package_name_snapshot VARCHAR(80) NOT NULL,
  tier_snapshot ENUM('GOLD','SILVER') NOT NULL,
  price_snapshot DECIMAL(14,2) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('ACTIVE','PENDING_PAYMENT','EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES members(id),
  FOREIGN KEY (package_id) REFERENCES membership_packages(id)
);

CREATE TABLE IF NOT EXISTS ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  color VARCHAR(20) NOT NULL DEFAULT '-',
  price_a DECIMAL(14,2) NOT NULL DEFAULT 0,
  price_b DECIMAL(14,2) NOT NULL DEFAULT 0,
  price_c DECIMAL(14,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  require_jenjang TINYINT(1) NOT NULL DEFAULT 0,
  require_nama TINYINT(1) NOT NULL DEFAULT 0,
  require_usia TINYINT(1) NOT NULL DEFAULT 0,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  serial_prefix VARCHAR(10) NOT NULL DEFAULT '',
  serial_next INT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS master_jenjang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS master_kelompok_usia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  min_age INT NULL,
  max_age INT NULL,
  color VARCHAR(20) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS master_rombongan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  number VARCHAR(25) NOT NULL UNIQUE,
  type ENUM('TIKET','MEMBER','LES','SEWA','TAMBAHAN') NOT NULL,
  member_id INT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  change_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_method ENUM('TUNAI','QRIS','TRANSFER','GABUNGAN') NOT NULL,
  pay_tunai DECIMAL(14,2) NOT NULL DEFAULT 0,
  pay_qris DECIMAL(14,2) NOT NULL DEFAULT 0,
  pay_transfer DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_status ENUM('PAID','UNPAID','CANCELLED') NOT NULL DEFAULT 'PAID',
  cashier_id INT NULL,
  area ENUM('PUTRA','PUTRI','CAMPURAN') NOT NULL DEFAULT 'CAMPURAN',
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES members(id),
  FOREIGN KEY (cashier_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS transaction_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_id INT NOT NULL,
  item_type ENUM('TIKET','MEMBER','LES','SEWA','TAMBAHAN') NOT NULL,
  item_ref_id INT NULL,
  item_name VARCHAR(150) NOT NULL,
  tariff_day ENUM('A','B','C','-') NOT NULL DEFAULT '-',
  detail VARCHAR(30) NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  serial_prefix VARCHAR(10) NULL,
  serial_start INT NULL,
  serial_end INT NULL,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS member_visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  visit_date DATE NOT NULL,
  check_in_at DATETIME NOT NULL,
  area ENUM('PUTRA','PUTRI') NOT NULL DEFAULT 'PUTRA',
  officer_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_visit (member_id, visit_date),
  FOREIGN KEY (member_id) REFERENCES members(id),
  FOREIGN KEY (officer_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS lesson_packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  duration_days INT NOT NULL DEFAULT 90,
  session_count INT NOT NULL DEFAULT 10,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS instructors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NULL,
  specialty VARCHAR(100) NULL,
  user_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS lesson_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  day_of_week ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  class_name VARCHAR(100) NOT NULL,
  instructor_id INT NULL,
  area ENUM('PUTRA','PUTRI','CAMPURAN') NOT NULL DEFAULT 'CAMPURAN',
  capacity INT NOT NULL DEFAULT 10,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (instructor_id) REFERENCES instructors(id)
);

CREATE TABLE IF NOT EXISTS lesson_students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NULL,
  birth_date DATE NULL,
  gender ENUM('L','P') NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  quota_sessions INT NOT NULL DEFAULT 10,
  package_id INT NULL,
  instructor_id INT NULL,
  status ENUM('ACTIVE','COMPLETED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (package_id) REFERENCES lesson_packages(id),
  FOREIGN KEY (instructor_id) REFERENCES instructors(id)
);

CREATE TABLE IF NOT EXISTS lesson_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  schedule_id INT NULL,
  session_date DATE NOT NULL,
  instructor_id INT NULL,
  attendance ENUM('HADIR','TIDAK') NOT NULL DEFAULT 'HADIR',
  notes VARCHAR(255) NULL,
  UNIQUE KEY uq_session (student_id, session_date),
  FOREIGN KEY (student_id) REFERENCES lesson_students(id),
  FOREIGN KEY (schedule_id) REFERENCES lesson_schedules(id),
  FOREIGN KEY (instructor_id) REFERENCES instructors(id)
);

CREATE TABLE IF NOT EXISTS operational_costs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cost_date DATE NOT NULL,
  name VARCHAR(150) NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS deposits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  deposit_date DATE NOT NULL,
  kind ENUM('QRIS','TRANSFER','UANG') NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(60) PRIMARY KEY,
  setting_value VARCHAR(255) NOT NULL
);
