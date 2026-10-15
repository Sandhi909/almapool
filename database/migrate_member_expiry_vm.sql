-- ============================================
-- ALMAPOOL - Migrasi masa berlaku member ke tanggal-ke-tanggal kalender
-- Contoh: daftar 7 Okt paket Bulanan (30 hari) -> habis 7 Nov (bukan 6 Nov).
--         paket Tahunan 7 Okt 2025 -> habis 7 Okt 2026 (bukan +365 hari).
-- Aman diulang (idempotent): end_date selalu dihitung ulang dari start_date.
-- Cara pakai di VM:
--   mysql -u root -p db_kolam_renang < migrate_member_expiry_vm.sql
-- atau phpMyAdmin > Import
-- ============================================
USE db_kolam_renang;

-- 1. Baris yang paketnya masih ada (punya duration_days + nama paket).
UPDATE memberships ms
JOIN membership_packages mp ON mp.id = ms.package_id
SET ms.end_date = CASE
  WHEN mp.name LIKE '%Tahun%' OR mp.duration_days >= 360
    THEN DATE_ADD(ms.start_date, INTERVAL GREATEST(1, ROUND(mp.duration_days / 365)) YEAR)
  WHEN mp.name LIKE '%Bulan%' OR mp.name LIKE '%Siswa%' OR mp.name LIKE '%Karyawan%'
    OR MOD(mp.duration_days, 30) = 0
    THEN DATE_ADD(ms.start_date, INTERVAL GREATEST(1, ROUND(mp.duration_days / 30)) MONTH)
  ELSE DATE_ADD(ms.start_date, INTERVAL mp.duration_days DAY)
END;

-- 2. Baris yatim (package_id sudah dihapus): tebak dari snapshot nama.
-- Semua paket bawaan selain Tahunan adalah bulanan, jadi default +1 MONTH.
UPDATE memberships ms
LEFT JOIN membership_packages mp ON mp.id = ms.package_id
SET ms.end_date = CASE
  WHEN ms.package_name_snapshot LIKE '%Tahun%'
    THEN DATE_ADD(ms.start_date, INTERVAL 1 YEAR)
  ELSE DATE_ADD(ms.start_date, INTERVAL 1 MONTH)
END
WHERE mp.id IS NULL;

-- 3. Verifikasi: tanggal (hari) habis harus sama dengan tanggal daftar,
-- kecuali klaim ujung-bulan (31 Jan -> 28/29 Feb).
SELECT ms.id, m.member_number, m.name,
  ms.package_name_snapshot AS paket,
  ms.start_date AS tgl_daftar, ms.end_date AS masa_berlaku,
  DAY(ms.start_date) AS tgl_daftar_tgl, DAY(ms.end_date) AS berlaku_tgl,
  DATEDIFF(ms.end_date, ms.start_date) AS selisih_hari
FROM memberships ms
JOIN members m ON m.id = ms.member_id
ORDER BY ms.id;
