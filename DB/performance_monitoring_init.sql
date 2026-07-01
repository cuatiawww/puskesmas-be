-- Create Table for Puskesmas Profile
CREATE TABLE IF NOT EXISTS public.puskesmas_profile (
    id SERIAL PRIMARY KEY,
    kode_faskes VARCHAR(50) UNIQUE NOT NULL,
    nama_puskesmas VARCHAR(255) NOT NULL,
    provinsi_id BIGINT,
    kabupaten_id BIGINT,
    kecamatan_id BIGINT,
    kelurahan_id BIGINT,
    kategori_wilayah VARCHAR(50) DEFAULT 'Tidak Terpencil', -- 'Tidak Terpencil', 'Terpencil', 'Sangat Terpencil'
    kategori_jenis VARCHAR(50) DEFAULT 'Perkotaan',        -- 'Perkotaan', 'Pedesaan'
    status_pelayanan VARCHAR(50) DEFAULT 'Non Rawat Inap', -- 'Rawat Inap', 'Non Rawat Inap'
    jumlah_penduduk INT DEFAULT 0,
    nomor_izin VARCHAR(255),
    izin_berlaku_sampai DATE,
    tanggal_registrasi DATE,
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Table for Puskesmas Kinerja (Performance data)
CREATE TABLE IF NOT EXISTS public.puskesmas_kinerja (
    id SERIAL PRIMARY KEY,
    puskesmas_id INT REFERENCES public.puskesmas_profile(id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    periode_tipe VARCHAR(50) NOT NULL, -- 'Tahunan', 'Kuartal', 'Bulanan'
    periode_nilai INT NOT NULL,        -- e.g. 2026, 1 (Q1), 3 (March)
    
    -- SDM & Alkes (DLI 6.1)
    dokter_tersedia BOOLEAN DEFAULT FALSE,
    nakes_9_jenis BOOLEAN DEFAULT FALSE,
    nakes_11_jenis BOOLEAN DEFAULT FALSE,
    persen_alkes DOUBLE PRECISION DEFAULT 0.0,
    persen_spa DOUBLE PRECISION DEFAULT 0.0,
    
    -- PERBEKES (Obat)
    jumlah_obat_esensial INT DEFAULT 0,
    persen_obat_esensial DOUBLE PRECISION DEFAULT 0.0,
    bmhp_tersedia BOOLEAN DEFAULT FALSE,
    
    -- BLUD & ILP
    status_blud BOOLEAN DEFAULT FALSE,
    sk_blud_tersedia BOOLEAN DEFAULT FALSE,
    status_ilp BOOLEAN DEFAULT FALSE,
    sk_ilp_tersedia BOOLEAN DEFAULT FALSE,
    jumlah_pustu_aktif INT DEFAULT 0,
    
    -- PKP (Penilaian Kinerja Puskesmas)
    skor_pkp_klaster1 DOUBLE PRECISION DEFAULT 0.0,
    skor_pkp_klaster2 DOUBLE PRECISION DEFAULT 0.0,
    skor_pkp_klaster3 DOUBLE PRECISION DEFAULT 0.0,
    skor_pkp_klaster4 DOUBLE PRECISION DEFAULT 0.0,
    skor_pkp_lintas_klaster DOUBLE PRECISION DEFAULT 0.0,
    skor_pkp_total DOUBLE PRECISION DEFAULT 0.0,
    
    -- Pembiayaan
    alokasi_bok NUMERIC(15,2) DEFAULT 0.00,
    realisasi_bok NUMERIC(15,2) DEFAULT 0.00,
    realisasi_insentif_ukm NUMERIC(15,2) DEFAULT 0.00,
    realisasi_insentif_fktp NUMERIC(15,2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unique_puskesmas_period UNIQUE (puskesmas_id, tahun, periode_tipe, periode_nilai)
);

-- Create Table for 10 Penyakit Terbanyak
CREATE TABLE IF NOT EXISTS public.puskesmas_penyakit (
    id SERIAL PRIMARY KEY,
    puskesmas_id INT REFERENCES public.puskesmas_profile(id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    bulan INT NOT NULL,
    nama_penyakit VARCHAR(255) NOT NULL,
    jumlah_kasus INT DEFAULT 0,
    ranking INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unique_puskesmas_penyakit_rank UNIQUE (puskesmas_id, tahun, bulan, ranking)
);
