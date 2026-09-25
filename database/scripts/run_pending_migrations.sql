-- =====================================================================
-- SCRIPT SQL MIGRATION SIMPEG FKEP UNRI
-- Jalankan di phpMyAdmin / HeidiSQL / MySQL Workbench
-- Tanggal: 2026-09-25
-- =====================================================================

-- =====================================================================
-- MIGRATION 1: Tambah Kolom 2-Step Approval ke tabel pengajuan_cuti
-- (2026_09_25_000004_add_two_step_approval_to_pengajuan_cuti_table)
-- =====================================================================

-- Cek jika kolom belum ada, kemudian tambahkan
SET @db_name = DATABASE();

-- Tambah atasan_langsung_id (FK ke users.id)
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = 'pengajuan_cuti'
  AND COLUMN_NAME = 'atasan_langsung_id'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `pengajuan_cuti` ADD COLUMN `atasan_langsung_id` BIGINT UNSIGNED NULL AFTER `status`',
  'SELECT "atasan_langsung_id sudah ada" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tambah pertimbangan_atasan
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = 'pengajuan_cuti'
  AND COLUMN_NAME = 'pertimbangan_atasan'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `pengajuan_cuti` ADD COLUMN `pertimbangan_atasan` VARCHAR(50) NULL AFTER `atasan_langsung_id`',
  'SELECT "pertimbangan_atasan sudah ada" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tambah catatan_atasan_langsung
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = 'pengajuan_cuti'
  AND COLUMN_NAME = 'catatan_atasan_langsung'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `pengajuan_cuti` ADD COLUMN `catatan_atasan_langsung` TEXT NULL AFTER `pertimbangan_atasan`',
  'SELECT "catatan_atasan_langsung sudah ada" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tambah pertimbangan_atasan_at
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = 'pengajuan_cuti'
  AND COLUMN_NAME = 'pertimbangan_atasan_at'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `pengajuan_cuti` ADD COLUMN `pertimbangan_atasan_at` DATETIME NULL AFTER `catatan_atasan_langsung`',
  'SELECT "pertimbangan_atasan_at sudah ada" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tambah pybmc_id
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
  AND TABLE_NAME = 'pengajuan_cuti'
  AND COLUMN_NAME = 'pybmc_id'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `pengajuan_cuti` ADD COLUMN `pybmc_id` BIGINT UNSIGNED NULL AFTER `pertimbangan_atasan_at`',
  'SELECT "pybmc_id sudah ada" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tambah Foreign Key atasan_langsung_id -> users.id (opsional, skip jika error)
-- ALTER TABLE `pengajuan_cuti` ADD CONSTRAINT `fk_cuti_atasan_langsung` FOREIGN KEY (`atasan_langsung_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;
-- ALTER TABLE `pengajuan_cuti` ADD CONSTRAINT `fk_cuti_pybmc` FOREIGN KEY (`pybmc_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- =====================================================================
-- MIGRATION 2: Pisahkan Jabatan S1/S2 Gabungan (Jika Belum Terpisah)
-- (2026_09_25_000005_fix_split_jabatan_s1_s2_and_koorprodi)
-- =====================================================================

-- 2.A. Cek apakah Koordinator Prodi S1/S2 masih gabungan
SET @koorprodiGabungId = (SELECT id FROM jabatan WHERE nama_jabatan = 'Koordinator Prodi S1/S2 Keperawatan' LIMIT 1);

-- Insert Koorprodi S1 jika belum ada
INSERT IGNORE INTO jabatan (kode_jabatan, nama_jabatan, keterangan, created_at, updated_at)
SELECT 'JB-KPS1', 'Koordinator Prodi S1 Keperawatan', 'Koordinator Program Studi S1 Keperawatan (Koorprodi S1)', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM jabatan WHERE nama_jabatan = 'Koordinator Prodi S1 Keperawatan');

-- Insert Koorprodi S2 jika belum ada
INSERT IGNORE INTO jabatan (kode_jabatan, nama_jabatan, keterangan, created_at, updated_at)
SELECT 'JB-KPS2', 'Koordinator Prodi S2 Keperawatan', 'Koordinator Program Studi S2 Keperawatan (Koorprodi S2)', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM jabatan WHERE nama_jabatan = 'Koordinator Prodi S2 Keperawatan');

-- Migrasi pegawai yang masih menggunakan jabatan gabungan -> default ke S1
UPDATE pegawai p
JOIN jabatan j ON p.jabatan_id = j.id
SET p.jabatan_id = (SELECT id FROM jabatan WHERE nama_jabatan = 'Koordinator Prodi S1 Keperawatan' LIMIT 1)
WHERE j.nama_jabatan = 'Koordinator Prodi S1/S2 Keperawatan';

-- Hapus jabatan gabungan jika tidak ada yang menggunakannya lagi
DELETE FROM jabatan
WHERE nama_jabatan = 'Koordinator Prodi S1/S2 Keperawatan'
AND NOT EXISTS (SELECT 1 FROM pegawai WHERE jabatan_id = jabatan.id)
AND NOT EXISTS (SELECT 1 FROM riwayat_jabatan WHERE jabatan_id = jabatan.id);

-- 2.B. Pisahkan Dosen S1/S2 gabungan
INSERT IGNORE INTO jabatan (kode_jabatan, nama_jabatan, keterangan, created_at, updated_at)
SELECT 'JB-DS1', 'Dosen S1 Keperawatan', 'Dosen Fungsional Program Studi S1 Keperawatan', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM jabatan WHERE nama_jabatan = 'Dosen S1 Keperawatan');

INSERT IGNORE INTO jabatan (kode_jabatan, nama_jabatan, keterangan, created_at, updated_at)
SELECT 'JB-DS2', 'Dosen S2 Keperawatan', 'Dosen Fungsional Program Studi S2 Keperawatan', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM jabatan WHERE nama_jabatan = 'Dosen S2 Keperawatan');

UPDATE pegawai p
JOIN jabatan j ON p.jabatan_id = j.id
SET p.jabatan_id = (SELECT id FROM jabatan WHERE nama_jabatan = 'Dosen S1 Keperawatan' LIMIT 1)
WHERE j.nama_jabatan = 'Dosen S1/S2 Keperawatan';

DELETE FROM jabatan
WHERE nama_jabatan = 'Dosen S1/S2 Keperawatan'
AND NOT EXISTS (SELECT 1 FROM pegawai WHERE jabatan_id = jabatan.id)
AND NOT EXISTS (SELECT 1 FROM riwayat_jabatan WHERE jabatan_id = jabatan.id);

-- 2.C. Pastikan jabatan Dosen Profesi Ners ada
INSERT IGNORE INTO jabatan (kode_jabatan, nama_jabatan, keterangan, created_at, updated_at)
SELECT 'JB-DN', 'Dosen Profesi Ners', 'Dosen Fungsional Program Studi Profesi Ners', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM jabatan WHERE nama_jabatan = 'Dosen Profesi Ners');

-- =====================================================================
-- VERIFIKASI - Cek hasil akhir
-- =====================================================================
-- Cek kolom tabel pengajuan_cuti:
-- SHOW COLUMNS FROM pengajuan_cuti;

-- Cek jabatan yang ada:
-- SELECT id, kode_jabatan, nama_jabatan FROM jabatan ORDER BY id;

SELECT 'Migration selesai! Silakan refresh aplikasi.' AS status;
