-- Migration: Add opsi kelas untuk tiket (paket kelas dari tiket jenjang)
-- Run on both local and VM (once).

ALTER TABLE ticket_types ADD COLUMN kelas_aktif TINYINT(1) NOT NULL DEFAULT 0 AFTER is_tagihan;

-- Aktifkan opsi kelas untuk tiket yang butuh jenjang (seperti tiket internal kelas SD/SMP/Mts).
UPDATE ticket_types SET kelas_aktif = 1 WHERE require_jenjang = 1;
