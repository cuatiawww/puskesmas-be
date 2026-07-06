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

-- 4. SETTING EMAIL NOTIFIKASI
-- ============================================================
-- Konfigurasi template email notifikasi — bisa diubah dari UI Konfigurasi Sistem.
INSERT INTO system_setting ("key", "value", "type", "label", created_at, updated_at) VALUES
  ('email_logo',              '', 'image', 'Logo Email Notifikasi', NOW(), NOW()),
  ('email_system_name',       'Asistensi Kinerja Puskesmas', 'text', 'Nama Sistem (Email)', NOW(), NOW()),
  ('email_greeting_prefix',   'Yth.', 'text', 'Sapaan Pembuka Email', NOW(), NOW()),
  ('email_sender_label',      'Asistensi Kinerja Puskesmas (KEMKES RI)', 'text', 'Label Pengirim Email', NOW(), NOW()),
  ('email_footer_org',        'Kementerian Kesehatan Republik Indonesia', 'text', 'Teks Organisasi Footer Email', NOW(), NOW()),
  ('email_footer_link_label', 'Kunjungi Website', 'text', 'Label Link Footer Email', NOW(), NOW()),
  ('email_footer_link_url',   '', 'text', 'URL Link Footer Email', NOW(), NOW()),
  ('email_header_color',      '#0f766e', 'text', 'Warna Aksen Email', NOW(), NOW())
ON CONFLICT ("key") DO NOTHING;
