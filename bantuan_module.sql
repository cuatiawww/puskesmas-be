-- ============================================================
-- DDL UNTUK MODUL BANTUAN
-- ============================================================

-- 1. Tabel Tutorial
CREATE TABLE IF NOT EXISTS tutorial (
    id SERIAL PRIMARY KEY,
    nama_tutorial VARCHAR(255) NOT NULL,
    keterangan TEXT,
    link_tutorial VARCHAR(255),
    link_video VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 2. Tabel Kontak
CREATE TABLE IF NOT EXISTS kontak (
    id SERIAL PRIMARY KEY,
    nama_kontak VARCHAR(255) NOT NULL,
    jabatan VARCHAR(255),
    email VARCHAR(255),
    whatsapp VARCHAR(50),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 3. Tabel Download
CREATE TABLE IF NOT EXISTS download (
    id SERIAL PRIMARY KEY,
    nama_download VARCHAR(255) NOT NULL,
    kategori VARCHAR(255),
    link_download VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 4. Tabel Versi Sistem
CREATE TABLE IF NOT EXISTS versi_sistem (
    id SERIAL PRIMARY KEY,
    versi VARCHAR(50) NOT NULL,
    keterangan TEXT,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- ============================================================
-- SEED DATA NAVIGASI (MODUL, SUB MODUL, DAN HAK AKSES)
-- ============================================================
DO $$
DECLARE
    v_modul_id INT;
    v_sub_tutorial_id INT;
    v_sub_kontak_id INT;
    v_sub_download_id INT;
    v_sub_versi_id INT;
    v_level_id INT;
BEGIN
    -- A. Insert Modul Bantuan if not exists
    IF NOT EXISTS (SELECT 1 FROM modul WHERE nama_modul = 'bantuan') THEN
        INSERT INTO modul (nama_modul, label, deskripsi, icon, urutan, is_active, created_at, updated_at)
        VALUES ('bantuan', 'BANTUAN', 'Modul Bantuan, Kontak, Download, dan Versi Sistem', 'ph-duotone ph-question', 4, true, NOW(), NOW())
        RETURNING id INTO v_modul_id;
    ELSE
        SELECT id INTO v_modul_id FROM modul WHERE nama_modul = 'bantuan';
    END IF;

    -- B. Insert Sub Modul Tutorial if not exists
    IF NOT EXISTS (SELECT 1 FROM sub_modul WHERE route = '/tutorial/index') THEN
        INSERT INTO sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active)
        VALUES (v_modul_id, 'tutorial', 'TUTORIAL', '/tutorial/index', 'ph-duotone ph-book-open', 1, true)
        RETURNING id INTO v_sub_tutorial_id;
    ELSE
        SELECT id INTO v_sub_tutorial_id FROM sub_modul WHERE route = '/tutorial/index';
    END IF;

    -- C. Insert Sub Modul Kontak if not exists
    IF NOT EXISTS (SELECT 1 FROM sub_modul WHERE route = '/kontak/index') THEN
        INSERT INTO sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active)
        VALUES (v_modul_id, 'kontak', 'KONTAK WA', '/kontak/index', 'ph-duotone ph-whatsapp-logo', 2, true)
        RETURNING id INTO v_sub_kontak_id;
    ELSE
        SELECT id INTO v_sub_kontak_id FROM sub_modul WHERE route = '/kontak/index';
    END IF;

    -- D. Insert Sub Modul Download if not exists
    IF NOT EXISTS (SELECT 1 FROM sub_modul WHERE route = '/download/index') THEN
        INSERT INTO sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active)
        VALUES (v_modul_id, 'download', 'DAFTAR DOWNLOAD', '/download/index', 'ph-duotone ph-download-simple', 3, true)
        RETURNING id INTO v_sub_download_id;
    ELSE
        SELECT id INTO v_sub_download_id FROM sub_modul WHERE route = '/download/index';
    END IF;

    -- E. Insert Sub Modul Versi Sistem if not exists
    IF NOT EXISTS (SELECT 1 FROM sub_modul WHERE route = '/versi-sistem/index') THEN
        INSERT INTO sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active)
        VALUES (v_modul_id, 'versi-sistem', 'VERSI SISTEM', '/versi-sistem/index', 'ph-duotone ph-git-branch', 4, true)
        RETURNING id INTO v_sub_versi_id;
    ELSE
        SELECT id INTO v_sub_versi_id FROM sub_modul WHERE route = '/versi-sistem/index';
    END IF;

    -- F. Configure Access Control (hak_akses) for each level user
    -- Level 1 (Super Admin) gets full CRUD access.
    -- Other levels (2, 3, 4, 7, etc.) get View Only access.
    FOR v_level_id IN SELECT id FROM level_user LOOP
        -- Tutorial
        IF NOT EXISTS (SELECT 1 FROM hak_akses WHERE level_user_id = v_level_id AND sub_modul_id = v_sub_tutorial_id) THEN
            INSERT INTO hak_akses (level_user_id, modul_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
            VALUES (v_level_id, v_modul_id, v_sub_tutorial_id, true, (v_level_id = 1), (v_level_id = 1), (v_level_id = 1), NOW(), NOW());
        END IF;

        -- Kontak
        IF NOT EXISTS (SELECT 1 FROM hak_akses WHERE level_user_id = v_level_id AND sub_modul_id = v_sub_kontak_id) THEN
            INSERT INTO hak_akses (level_user_id, modul_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
            VALUES (v_level_id, v_modul_id, v_sub_kontak_id, true, (v_level_id = 1), (v_level_id = 1), (v_level_id = 1), NOW(), NOW());
        END IF;

        -- Download
        IF NOT EXISTS (SELECT 1 FROM hak_akses WHERE level_user_id = v_level_id AND sub_modul_id = v_sub_download_id) THEN
            INSERT INTO hak_akses (level_user_id, modul_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
            VALUES (v_level_id, v_modul_id, v_sub_download_id, true, (v_level_id = 1), (v_level_id = 1), (v_level_id = 1), NOW(), NOW());
        END IF;

        -- Versi Sistem
        IF NOT EXISTS (SELECT 1 FROM hak_akses WHERE level_user_id = v_level_id AND sub_modul_id = v_sub_versi_id) THEN
            INSERT INTO hak_akses (level_user_id, modul_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at)
            VALUES (v_level_id, v_modul_id, v_sub_versi_id, true, (v_level_id = 1), (v_level_id = 1), (v_level_id = 1), NOW(), NOW());
        END IF;
    END LOOP;
END $$;
