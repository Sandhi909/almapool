-- Clean Demo/Dummy untuk testing real lapangan
-- Hapus semua transaksi & demo, sisakan master (ticket, paket, settings, admin)
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE member_visits;
TRUNCATE TABLE transaction_items;
TRUNCATE TABLE transactions;
TRUNCATE TABLE memberships;
TRUNCATE TABLE members;
TRUNCATE TABLE lesson_sessions;
TRUNCATE TABLE lesson_students;
TRUNCATE TABLE lesson_schedules;
TRUNCATE TABLE operational_costs;
TRUNCATE TABLE deposits;
-- Hapus user demo, sisakan admin (id=1)
DELETE FROM users WHERE id != 1;
-- Reset admin password jadi admin123 (hash baru) - ganti setelah login!
-- UPDATE users SET name='Administrator', username='admin', role='admin', is_active=1 WHERE id=1;
-- Optional: hapus instructor demo jika mau (comment jika mau keep)
-- TRUNCATE TABLE instructors;
SET FOREIGN_KEY_CHECKS=1;
-- Tampilkan sisa
SELECT 'users' t, COUNT(*) c FROM users UNION ALL SELECT 'members', COUNT(*) FROM members UNION ALL SELECT 'transactions', COUNT(*) FROM transactions;
