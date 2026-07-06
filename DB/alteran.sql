-- ============================================================
-- DDL & SEED DATA ALTERATION LOG
-- ============================================================

-- 1. ALTER TABLE UNTUK MENAMBAH KOLOM FOTO PUSKESMAS
-- ============================================================
ALTER TABLE puskesmas_profile ADD COLUMN IF NOT EXISTS foto_puskesmas VARCHAR(255);

-- 2. HAK AKSES PERMISSIONS UNTUK LEVEL 2 (PROVINSI), 3 (KAB/KOTA), DAN 4 (KECAMATAN)
-- PADA SUBMODUL BERANDA (ID 3) DAN PUSKESMAS PROFILE (ID 4)
-- ============================================================

-- LEVEL 2 (PROVINSI)
INSERT INTO hak_akses (level_user_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
VALUES 
    (2, 3, true, false, false, false, NOW(), NOW()),
    (2, 4, true, false, false, false, NOW(), NOW())
ON CONFLICT (level_user_id, sub_modul_id) 
DO UPDATE SET 
    can_view = true, 
    can_create = false, 
    can_update = false, 
    can_delete = false, 
    updated_at = NOW();

-- LEVEL 3 (KAB/KOTA)
INSERT INTO hak_akses (level_user_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
VALUES 
    (3, 3, true, false, false, false, NOW(), NOW()),
    (3, 4, true, false, false, false, NOW(), NOW())
ON CONFLICT (level_user_id, sub_modul_id) 
DO UPDATE SET 
    can_view = true, 
    can_create = false, 
    can_update = false, 
    can_delete = false, 
    updated_at = NOW();

-- LEVEL 4 (KECAMATAN/DESA)
INSERT INTO hak_akses (level_user_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
VALUES 
    (4, 3, true, false, false, false, NOW(), NOW()),
    (4, 4, true, false, false, false, NOW(), NOW())
ON CONFLICT (level_user_id, sub_modul_id) 
DO UPDATE SET 
    can_view = true, 
    can_create = false, 
    can_update = false, 
    can_delete = false, 
    updated_at = NOW();

-- 3. ALTER TABLE UNTUK MENAMBAH KOLOM ALAMAT DAN JENIS KELAMIN PADA TABEL USER
-- ============================================================
ALTER TABLE "user" ADD COLUMN IF NOT EXISTS alamat TEXT;
ALTER TABLE "user" ADD COLUMN IF NOT EXISTS jenis_kelamin VARCHAR(20);
