-- Migrate: Add expense_types & operational_costs tables
-- Created: 2026-10-26

-- 1. Create expense_types master table
CREATE TABLE IF NOT EXISTS `expense_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Create operational_costs table with foreign key to expense_types
CREATE TABLE IF NOT EXISTS `operational_costs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cost_date` DATE NOT NULL,
  `expense_type_id` INT NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
);

-- 3. Seed default expense types
INSERT IGNORE INTO `expense_types` (`id`, `name`, `is_active`) VALUES
  (1, 'Backwash', 1),
  (2, 'Listrik', 1),
  (3, 'Gaji', 1),
  (4, 'Penggantian Listrik', 1),
  (5, 'Makan PJ Rombongan', 1);
