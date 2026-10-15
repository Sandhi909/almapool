-- Update medicine tables: remove price, change category/unit to foreign keys
ALTER TABLE medicines DROP COLUMN unit_price;
ALTER TABLE medicines ADD COLUMN category_id INT NULL AFTER name;
ALTER TABLE medicines ADD COLUMN unit_id INT NULL AFTER category_id;
ALTER TABLE medicines ADD FOREIGN KEY (category_id) REFERENCES medicine_categories(id);
ALTER TABLE medicines ADD FOREIGN KEY (unit_id) REFERENCES medicine_units(id);
ALTER TABLE medicines DROP COLUMN category;
ALTER TABLE medicines DROP COLUMN unit;

ALTER TABLE medicine_stock DROP COLUMN unit;
ALTER TABLE medicine_stock DROP COLUMN unit_price;
ALTER TABLE medicine_usage DROP COLUMN unit;
