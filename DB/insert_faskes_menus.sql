-- ============================================================
-- SQL SCRIPT UNTUK KONFIGURASI MENU DAN HAK AKSES DATA FASKES
-- Project: Puskesmas
-- ============================================================

-- 1. Bersihkan data lama jika ada untuk mencegah duplikasi (opsional/aman)
DELETE FROM hak_akses WHERE sub_modul_id IN (157, 158, 159, 160, 161, 162, 163);
DELETE FROM sub_modul WHERE id IN (157, 158, 159, 160, 161, 162, 163);

-- 2. Insert Parent Sub-modul "DATA FASKES" under Modul ID 3 (MASTER DATA & KONFIGURASI)
INSERT INTO sub_modul (id, modul_id, nama_sub_modul, label, route, icon, urutan, is_active, created_at, updated_at, parent_id)
VALUES (157, 3, 'master-data-fakses', 'DATA FASKES', '', 'ph-duotone ph-newspaper', 4, true, NOW(), NOW(), NULL);

-- 3. Insert Children Sub-modul untuk Faskes Spesifik
INSERT INTO sub_modul (id, modul_id, nama_sub_modul, label, route, icon, urutan, is_active, created_at, updated_at, parent_id) VALUES
(158, 3, 'rumah-sakit', 'RUMAH SAKIT', 'rumah-sakit/index', '', 1, true, NOW(), NOW(), 157),
(159, 3, 'puskesmas/index', 'PUSKESMAS', 'puskesmas/index', '', 2, true, NOW(), NOW(), 157),
(160, 3, 'pustu', 'PUSTU', 'pustu/index', '', 3, true, NOW(), NOW(), 157),
(161, 3, 'klinik', 'KLINIK', 'klinik/index', '', 4, true, NOW(), NOW(), 157),
(162, 3, 'posyandu', 'POSYANDU', 'posyandu/index', '', 5, true, NOW(), NOW(), 157),
(163, 3, 'bbk-bblk/index', 'BKK & BBLK', 'bbk-bblk/index', '', 6, true, NOW(), NOW(), 157);

-- 4. Update postgres sequence agar tidak bentrok dengan auto-increment data berikutnya
SELECT setval('sub_modul_id_seq', COALESCE((SELECT MAX(id)+1 FROM sub_modul), 1), false);

-- 5. Insert Hak Akses untuk level Provinsi (Level ID 2) dan level Kabupaten/Kota (Level ID 3)
-- Provinsi (Level ID 2)
INSERT INTO hak_akses (level_user_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at) VALUES
(2, 157, true, true, true, true, NOW(), NOW()),
(2, 158, true, true, true, true, NOW(), NOW()),
(2, 159, true, true, true, true, NOW(), NOW()),
(2, 160, true, true, true, true, NOW(), NOW()),
(2, 161, true, true, true, true, NOW(), NOW()),
(2, 162, true, true, true, true, NOW(), NOW()),
(2, 163, true, true, true, true, NOW(), NOW());

-- Kab/Kota (Level ID 3)
INSERT INTO hak_akses (level_user_id, sub_modul_id, can_view, can_create, can_update, can_delete, created_at, updated_at) VALUES
(3, 157, true, true, true, true, NOW(), NOW()),
(3, 158, true, true, true, true, NOW(), NOW()),
(3, 159, true, true, true, true, NOW(), NOW()),
(3, 160, true, true, true, true, NOW(), NOW()),
(3, 161, true, true, true, true, NOW(), NOW()),
(3, 162, true, true, true, true, NOW(), NOW()),
(3, 163, true, true, true, true, NOW(), NOW());
