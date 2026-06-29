-- ============================================================
-- DATABASE INITIALIZATION FOR FRESH PUSKESMAS PROJECT
-- Framework: Yii 2 (PostgreSQL)
-- Created: 2026-06-29
-- ============================================================

BEGIN;

-- Drop tables if they exist to start fresh
DROP TABLE IF EXISTS public.hak_akses CASCADE;
DROP TABLE IF EXISTS public.sub_modul CASCADE;
DROP TABLE IF EXISTS public.modul CASCADE;
DROP TABLE IF EXISTS public.user_registration CASCADE;
DROP TABLE IF EXISTS public."user" CASCADE;
DROP TABLE IF EXISTS public.level_user CASCADE;
DROP TABLE IF EXISTS public.master_wilayah CASCADE;
DROP TABLE IF EXISTS public.tbl_wilayah CASCADE;
DROP TABLE IF EXISTS public.wilayah_provinsi CASCADE;
DROP TABLE IF EXISTS public.wilayah_kabupaten CASCADE;
DROP TABLE IF EXISTS public.wilayah_kecamatan CASCADE;
DROP TABLE IF EXISTS public.wilayah_desa CASCADE;

-- 1. Table level_user
CREATE TABLE public.level_user (
    id integer NOT NULL PRIMARY KEY,
    nama_level character varying(100) NOT NULL UNIQUE,
    deskripsi text,
    is_active boolean DEFAULT true,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table user
CREATE TABLE public."user" (
    id SERIAL PRIMARY KEY,
    username character varying(100) NOT NULL UNIQUE,
    password character varying(255) NOT NULL,
    nama_lengkap character varying(255) NOT NULL,
    email character varying(255) NOT NULL UNIQUE,
    no_telpon character varying(20) DEFAULT NULL,
    level_user_id integer NOT NULL REFERENCES public.level_user(id) ON UPDATE CASCADE ON DELETE CASCADE,
    is_active boolean DEFAULT true,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    foto_profil character varying(255) DEFAULT NULL,
    id_user_level integer,
    kd_prop character varying(50),
    kd_kab character varying(50),
    kd_kecamatan character varying(100),
    status boolean DEFAULT true,
    tbl_wilayah_id bigint,
    master_wilayah_id bigint,
    password_reset_otp character varying(6),
    password_reset_otp_expires_at timestamp without time zone,
    password_reset_requested_at timestamp without time zone
);

-- 3. Table user_registration
CREATE TABLE public.user_registration (
    id SERIAL PRIMARY KEY,
    user_id integer REFERENCES public."user"(id) ON DELETE SET NULL,
    kategori_akses character varying(50) NOT NULL,
    nama_lengkap character varying(150) NOT NULL,
    username character varying(150) NOT NULL,
    email character varying(255) NOT NULL,
    telp character varying(50) NOT NULL,
    nama_institusi character varying(255),
    pekerjaan_posisi character varying(255),
    alamat_user text NOT NULL,
    provinsi_id integer NOT NULL,
    kabupaten_id integer NOT NULL,
    tujuan_akses character varying(100) NOT NULL,
    tujuan_akses_lainnya character varying(255),
    status character varying(30) DEFAULT 'email_pending'::character varying NOT NULL,
    email_verified_at timestamp without time zone,
    otp_hash character varying(255),
    otp_expires_at timestamp without time zone,
    otp_sent_at timestamp without time zone,
    otp_resend_count integer DEFAULT 0 NOT NULL,
    approved_by integer REFERENCES public."user"(id) ON DELETE SET NULL,
    approved_at timestamp without time zone,
    rejected_by integer REFERENCES public."user"(id) ON DELETE SET NULL,
    rejected_at timestamp without time zone,
    rejection_reason text,
    ip_address character varying(64),
    user_agent text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 4. Table modul
CREATE TABLE public.modul (
    id integer NOT NULL PRIMARY KEY,
    nama_modul character varying(100) NOT NULL,
    label character varying(100) NOT NULL,
    deskripsi character varying(255),
    icon character varying(50),
    urutan integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP
);

-- 5. Table sub_modul
CREATE TABLE public.sub_modul (
    id integer NOT NULL PRIMARY KEY,
    modul_id integer NOT NULL REFERENCES public.modul(id) ON DELETE CASCADE,
    nama_sub_modul character varying(100) NOT NULL,
    label character varying(100) NOT NULL,
    route character varying(255),
    icon character varying(50),
    urutan integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    parent_id integer REFERENCES public.sub_modul(id) ON DELETE SET NULL
);

-- 6. Table hak_akses
CREATE TABLE public.hak_akses (
    id SERIAL PRIMARY KEY,
    level_user_id integer NOT NULL REFERENCES public.level_user(id) ON DELETE CASCADE,
    modul_id integer REFERENCES public.modul(id) ON DELETE CASCADE,
    sub_modul_id integer REFERENCES public.sub_modul(id) ON DELETE CASCADE,
    can_view boolean DEFAULT false,
    can_create boolean DEFAULT false,
    can_update boolean DEFAULT false,
    can_delete boolean DEFAULT false,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP
);

-- 7. Table tbl_wilayah
CREATE TABLE public.tbl_wilayah (
    id_user bigint NOT NULL PRIMARY KEY,
    hp character varying(255) DEFAULT NULL,
    kd_user character varying(255) DEFAULT NULL,
    kd_bps character varying(100) DEFAULT NULL,
    kd_bnpb character varying(255) DEFAULT NULL,
    kode_pusdatin character varying(255) DEFAULT NULL,
    kode_provinsi character varying(100) DEFAULT NULL,
    username character varying(100) DEFAULT NULL,
    password character varying(100) DEFAULT NULL,
    email character varying(100) DEFAULT NULL,
    join_date text,
    closed_date text,
    last_login text,
    detail_lengkap character varying(200) DEFAULT NULL,
    user_type text,
    alamat text,
    phone character varying(100) DEFAULT NULL,
    photo character varying(200) DEFAULT NULL,
    photo_thumb character varying(255) DEFAULT NULL,
    id_user_level bigint,
    status bigint,
    parent_id bigint,
    longitude character varying(200) DEFAULT NULL,
    latitude character varying(200) DEFAULT NULL,
    alamat_kantor text,
    regional bigint,
    no_hp character varying(255) DEFAULT NULL,
    keterangan text,
    peta character varying(255) DEFAULT NULL,
    luas_wilayahx character varying(255) DEFAULT NULL,
    jumlah_pendudukx character varying(255) DEFAULT NULL,
    status_kec bigint,
    luas_wilayah character varying(255) DEFAULT NULL,
    jumlah_penduduk character varying(255) DEFAULT NULL,
    kepadatan_penduduk character varying(255) DEFAULT NULL,
    topografi text,
    batas_utara text,
    batas_selatan text,
    batas_tenggara text,
    batas_barat text,
    batas_barat_laut text,
    batas_barat_daya text,
    batas_timur text,
    batas_timur_laut text,
    jml_bayi bigint,
    jml_balita bigint,
    jml_ibu_hamil bigint,
    jml_ibu_menyusui bigint,
    jml_lansia bigint,
    nilai_ipm character varying(255) DEFAULT NULL,
    nilai_ipkm character varying(255) DEFAULT NULL,
    jml_penyandang_disabilitas bigint,
    nama_dinkes text,
    nama_kadinkes text,
    parent_provinsi bigint,
    wilayah_terdekat text
);

-- 8. Table master_wilayah
CREATE TABLE public.master_wilayah (
    id bigint NOT NULL PRIMARY KEY,
    tbl_wilayah_id bigint,
    nama_wilayah character varying(255) NOT NULL,
    level_wilayah integer NOT NULL,
    parent_tbl_wilayah_id bigint,
    parent_master_wilayah_id bigint,
    username_legacy character varying(150),
    email_legacy character varying(255),
    password_legacy character varying(255),
    kd_user character varying(255),
    kd_bps character varying(100),
    kd_bnpb character varying(255),
    kode_pusdatin character varying(255),
    kode_provinsi character varying(100),
    status_aktif integer DEFAULT 1,
    alamat_kantor text,
    phone character varying(100),
    longitude character varying(200),
    latitude character varying(200),
    nama_dinkes text,
    nama_kadinkes text,
    keterangan text,
    regional bigint,
    sumber_data character varying(50) DEFAULT 'tbl_wilayah'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- 9. Table wilayah_provinsi
CREATE TABLE public.wilayah_provinsi (
    id bigint NOT NULL PRIMARY KEY,
    code text,
    parent_code text,
    bps_code text,
    name text,
    latitude text,
    longitude text,
    image text,
    jumlah_penduduk bigint,
    luas_wilayah bigint
);

-- 10. Table wilayah_kabupaten
CREATE TABLE public.wilayah_kabupaten (
    id bigint NOT NULL PRIMARY KEY,
    code text,
    parent_code text,
    bps_code text,
    name text,
    latitude text,
    longitude text,
    image text,
    jumlah_penduduk bigint,
    luas_wilayah bigint
);

-- 11. Table wilayah_kecamatan
CREATE TABLE public.wilayah_kecamatan (
    id bigint NOT NULL PRIMARY KEY,
    code text,
    parent_code text,
    bps_code text,
    name text,
    latitude text,
    longitude text,
    image text,
    jumlah_penduduk bigint,
    luas_wilayah bigint
);

-- 12. Table wilayah_desa
CREATE TABLE public.wilayah_desa (
    id bigint NOT NULL PRIMARY KEY,
    code text,
    parent_code text,
    bps_code text,
    name text,
    latitude text,
    longitude text,
    image text,
    jumlah_penduduk bigint,
    luas_wilayah bigint
);


-- ============================================================
-- INSERT DEFAULT CONFIGURATION DATA
-- ============================================================

-- Level User Roles
INSERT INTO public.level_user (id, nama_level, deskripsi, is_active) VALUES
(1, 'Super Admin', 'Akses penuh ke semua fitur sistem', true),
(2, 'PROVINSI', 'Level wilayah provinsi', true),
(3, 'KAB/KOTA', 'Level wilayah kabupaten/kota', true),
(4, 'KECAMATAN/DESA', 'Level wilayah kecamatan/desa', true),
(7, 'Masyarakat', 'Akses masyarakat untuk pengajuan akun publik', true);

-- Modul
INSERT INTO public.modul (id, nama_modul, label, deskripsi, icon, urutan, is_active) VALUES
(2, 'dashboard-laporan', 'DASHBOARD & LAPORAN', 'Laporan Kinerja Sistem Pencatatan dan Pelaporan', NULL, 1, true),
(3, 'master-data', 'MASTER DATA & KONFIGURASI', 'Data Referensi dan Konfigurasi Sistem', NULL, 6, true);

-- Sub-Modul
INSERT INTO public.sub_modul (id, modul_id, nama_sub_modul, label, route, icon, urutan, is_active, parent_id) VALUES
-- Dashboard / Beranda
(3, 2, 'beranda', 'BERANDA', 'beranda/index', 'ph-duotone ph-house', 0, true, NULL),
-- Konfigurasi
(15, 3, 'konfigurasi', 'KONFIGURASI', '#!', 'ph-duotone ph-gear', 5, true, NULL),
(2, 3, 'modul', 'MODUL', '/modul/index', 'ph-duotone ph-squares-four', 2, true, 15),
(39, 3, 'sub-modul', 'SUB MODUL', '/sub-modul/index', NULL, 1, true, 15),
(1, 3, 'navigasi', 'NAVIGASI', '/navigasi/index', 'ph-duotone ph-navigation', 1, true, 15),
-- Data User Akses
(36, 3, 'data-user-akses', 'DATA USER AKSES', '#', 'ph-duotone ph-user', 6, true, NULL),
(38, 3, 'data-user', 'DATA USER', 'user-model/index', NULL, 2, true, 36),
(37, 3, 'level-user', 'LEVEL USER', 'level-user/index', NULL, 1, true, 36),
(156, 3, 'user-registration', 'USER REGISTRATION', 'user-registration/index', NULL, 0, true, 36);

-- Default Super Admin User (password: admin123)
INSERT INTO public."user" (id, username, password, nama_lengkap, email, level_user_id, is_active, status) VALUES
(1, 'admin', '$2y$10$0GeipK/cJt2.aLJVOLegtOzTFB3kq/ZTQO/fnGh/.hEzwKGqNnP3e', 'Super Administrator', 'admin@puskesmas.go.id', 1, true, true);

-- Grant hak_akses for Super Admin (level_user_id = 1)
INSERT INTO public.hak_akses (level_user_id, modul_id, sub_modul_id, can_view, can_create, can_update, can_delete) VALUES
(1, 2, 3, true, true, true, true),   -- Beranda
(1, 3, 15, true, true, true, true),  -- Konfigurasi
(1, 3, 2, true, true, true, true),   -- Modul
(1, 3, 39, true, true, true, true),  -- Sub Modul
(1, 3, 1, true, true, true, true),   -- Navigasi
(1, 3, 36, true, true, true, true),  -- Data User Akses
(1, 3, 38, true, true, true, true),  -- Data User
(1, 3, 37, true, true, true, true),  -- Level User
(1, 3, 156, true, true, true, true); -- User Registration

-- Fix serial sequence value after manual id inserts
SELECT setval('public.user_id_seq', COALESCE((SELECT MAX(id)+1 FROM public."user"), 1), false);
SELECT setval('public.user_registration_id_seq', 1, false);
SELECT setval('public.hak_akses_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.hak_akses), 1), false);

COMMIT;
