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
  ('email_header_color',      '#0f766e', 'text', 'Warna Aksen Email', NOW(), NOW()),
  ('email_otp_greeting',       'Yth.', 'text', 'Sapaan OTP', NOW(), NOW()),
  ('email_otp_color',          '#0284c7', 'text', 'Warna OTP', NOW(), NOW()),
  ('email_approved_greeting',  'Yth.', 'text', 'Sapaan Akun Disetujui', NOW(), NOW()),
  ('email_approved_color',     '#0f766e', 'text', 'Warna Akun Disetujui', NOW(), NOW()),
  ('email_rejected_greeting',  'Yth.', 'text', 'Sapaan Akun Ditolak', NOW(), NOW()),
  ('email_rejected_color',     '#e11d48', 'text', 'Warna Akun Ditolak', NOW(), NOW()),
  ('email_created_greeting',   'Yth.', 'text', 'Sapaan Akun Dibuat Admin', NOW(), NOW()),
  ('email_created_color',      '#0f766e', 'text', 'Warna Akun Dibuat Admin', NOW(), NOW())
ON CONFLICT ("key") DO NOTHING;

-- 5. TAMBAH TABEL FILE ASSET JIKA BELUM ADA
-- ============================================================
CREATE TABLE IF NOT EXISTS public.file_asset (
    id SERIAL PRIMARY KEY,
    file_path TEXT,
    hash TEXT,
    tipe_file VARCHAR(255),
    ukuran VARCHAR(50),
    id_user INTEGER,
    update_date TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    file_name VARCHAR(255)
);

-- 6. KONFIGURASI TAMPILAN SISTEM - LINK KE DASHBOARD WEB
-- ============================================================
INSERT INTO system_setting ("key", "value", "type", "label", category, created_at, updated_at) VALUES
  ('login_dashboard_link', 'https://puskes-kappa.vercel.app/login', 'text', 'Link ke Dashboard Web', 'general', NOW(), NOW())
ON CONFLICT ("key") DO NOTHING;

-- 7. TABEL USER ACTIVITY LOG & SEED MENU ITEM
-- ============================================================
CREATE TABLE IF NOT EXISTS public.user_activity_log (
    id BIGSERIAL PRIMARY KEY,
    user_id INT,
    username VARCHAR(255),
    action VARCHAR(50) NOT NULL,
    module VARCHAR(100),
    controller VARCHAR(100) NOT NULL,
    action_id VARCHAR(100) NOT NULL,
    route VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    target_model VARCHAR(255),
    target_id VARCHAR(255),
    changes TEXT,
    ip_address VARCHAR(50),
    user_agent TEXT,
    browser VARCHAR(50),
    platform VARCHAR(50),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_user_activity_log_action ON public.user_activity_log (action);
CREATE INDEX IF NOT EXISTS idx_user_activity_log_created_at ON public.user_activity_log (created_at);
CREATE INDEX IF NOT EXISTS idx_user_activity_log_route ON public.user_activity_log (route);
CREATE INDEX IF NOT EXISTS idx_user_activity_log_user_id ON public.user_activity_log (user_id);
CREATE INDEX IF NOT EXISTS idx_user_activity_log_username ON public.user_activity_log (username);

INSERT INTO sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active, parent_id, created_at, updated_at)
SELECT 3, 'user-activity-log', 'LOG AKTIVITAS USER', '/user-activity/index', 'ph-duotone ph-clock-counter-clockwise', 3, true, 36, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM sub_modul WHERE route = '/user-activity/index' OR nama_sub_modul = 'user-activity-log');

-- 8. SINKRONISASI SEQUENCE UNTUK MENGHINDARI ERROR DUPLICATE KEY
-- ============================================================
SELECT setval('sub_modul_id_seq', COALESCE((SELECT MAX(id)+1 FROM sub_modul), 1), false);

-- 9. SEED DATA MODUL BANTUAN (TUTORIAL, KONTAK WA, DOWNLOAD, VERSI SISTEM)
-- ============================================================
TRUNCATE TABLE public.tutorial RESTART IDENTITY CASCADE;
TRUNCATE TABLE public.kontak RESTART IDENTITY CASCADE;
TRUNCATE TABLE public.download RESTART IDENTITY CASCADE;
TRUNCATE TABLE public.versi_sistem RESTART IDENTITY CASCADE;

-- SEED DATA TUTORIAL
INSERT INTO public.tutorial (nama_tutorial, keterangan, link_tutorial, link_video, created_at, updated_at) VALUES
('Panduan Penggunaan Sistem Asistensi Kinerja Puskesmas', 'Tatakelola penggunaan aplikasi Asistensi Kinerja Puskesmas mulai dari login, sinkronisasi data, hingga pembacaan laporan.', 'https://puskesmas-be.mediaciptainformasi.co.id/file/tutorial/panduan-sistem.pdf', 'https://www.youtube.com/watch?v=tutorial-puskesmas', NOW(), NOW()),
('Manajemen Data User dan Hak Akses', 'Panduan untuk administrator puskesmas dalam melakukan pengelolaan user, aktivasi akun, dan pembagian level hak akses.', 'https://puskesmas-be.mediaciptainformasi.co.id/file/tutorial/manajemen-user.pdf', 'https://www.youtube.com/watch?v=manajemen-user', NOW(), NOW()),
('Sinkronisasi Modul dan Integrasi Data Kesehatan', 'Tatakelola sinkronisasi modul aplikasi dan panduan integrasi data kesehatan puskesmas secara terpadu.', 'https://puskesmas-be.mediaciptainformasi.co.id/file/tutorial/integrasi-data.pdf', 'https://www.youtube.com/watch?v=integrasi-data', NOW(), NOW());

-- SEED DATA KONTAK
INSERT INTO public.kontak (nama_kontak, jabatan, email, whatsapp, created_at, updated_at) VALUES
('Helpdesk Asistensi Puskesmas', 'Tim Support TI Pusat', 'support.puskesmas@kemkes.go.id', '0811163119', NOW(), NOW()),
('Direktorat Pelayanan Kesehatan Primer', 'Administrator Program', 'yankes.primer@kemkes.go.id', '081234567890', NOW(), NOW());

-- SEED DATA DOWNLOAD
INSERT INTO public.download (nama_download, kategori, link_download, created_at, updated_at) VALUES
('Panduan Teknis Penggunaan Aplikasi (PDF)', 'Dokumen', 'https://puskesmas-be.mediaciptainformasi.co.id/file/download/panduan_teknis.pdf', NOW(), NOW()),
('Browser Google Chrome', 'Browser', 'https://www.google.com/chrome/', NOW(), NOW()),
('Browser Mozilla Firefox', 'Browser', 'https://www.mozilla.org/firefox/new/', NOW(), NOW());

-- SEED DATA VERSI SISTEM
INSERT INTO public.versi_sistem (versi, keterangan, tanggal, created_at, updated_at) VALUES
('v1.0.0-beta', '- Inisialisasi Sistem Asistensi Kinerja Puskesmas
- Fitur Manajemen Pengguna (Aktivasi, OTP, Approval)
- Pengaturan Navigasi Menu, Modul, dan Level User Akses', '2026-07-01', NOW(), NOW()),
('v1.1.0-release', '- Penambahan fitur Log Aktivitas Pengguna (User Activity Tracking) untuk audit trails
- Penambahan modul Bantuan terpadu (Tutorial, Kontak WA, Daftar Download, Versi Sistem)
- Perbaikan bug sinkronisasi status login Single Sign-On (SSO)
- Optimasi template email verifikasi OTP dan notifikasi pembuatan akun', '2026-07-09', NOW(), NOW());


