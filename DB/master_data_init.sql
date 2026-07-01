-- Create Master Tables
CREATE TABLE IF NOT EXISTS public.master_nakes (
    id SERIAL PRIMARY KEY,
    nama_nakes VARCHAR(255) NOT NULL,
    kategori VARCHAR(50) NOT NULL, -- 'Medis', 'Tenaga Kesehatan', 'Penunjang'
    is_dli_9 BOOLEAN DEFAULT FALSE,
    is_dli_11 BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.master_obat (
    id SERIAL PRIMARY KEY,
    nama_obat VARCHAR(255) NOT NULL,
    kategori VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.master_alkes (
    id SERIAL PRIMARY KEY,
    nama_alkes VARCHAR(255) NOT NULL,
    kategori VARCHAR(100), -- 'Peralatan Medis', 'Sarana', 'Prasarana'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Transactional Detail Mapping Tables
CREATE TABLE IF NOT EXISTS public.puskesmas_nakes_detail (
    id SERIAL PRIMARY KEY,
    puskesmas_id INT REFERENCES public.puskesmas_profile(id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    periode_tipe VARCHAR(50) NOT NULL,
    periode_nilai INT NOT NULL,
    nakes_id INT REFERENCES public.master_nakes(id) ON DELETE CASCADE,
    jumlah INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_pusk_nakes UNIQUE (puskesmas_id, tahun, periode_tipe, periode_nilai, nakes_id)
);

CREATE TABLE IF NOT EXISTS public.puskesmas_obat_detail (
    id SERIAL PRIMARY KEY,
    puskesmas_id INT REFERENCES public.puskesmas_profile(id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    periode_tipe VARCHAR(50) NOT NULL,
    periode_nilai INT NOT NULL,
    obat_id INT REFERENCES public.master_obat(id) ON DELETE CASCADE,
    is_tersedia BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_pusk_obat UNIQUE (puskesmas_id, tahun, periode_tipe, periode_nilai, obat_id)
);

CREATE TABLE IF NOT EXISTS public.puskesmas_alkes_detail (
    id SERIAL PRIMARY KEY,
    puskesmas_id INT REFERENCES public.puskesmas_profile(id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    periode_tipe VARCHAR(50) NOT NULL,
    periode_nilai INT NOT NULL,
    alkes_id INT REFERENCES public.master_alkes(id) ON DELETE CASCADE,
    is_tersedia BOOLEAN DEFAULT FALSE,
    kondisi_baik BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_pusk_alkes UNIQUE (puskesmas_id, tahun, periode_tipe, periode_nilai, alkes_id)
);

-- Insert Standard Master Data Nakes (9 & 11 Jenis Nakes Permenkes)
INSERT INTO public.master_nakes (nama_nakes, kategori, is_dli_9, is_dli_11) VALUES
('Dokter Spesialis / Dokter Umum', 'Medis', true, true),
('Dokter Gigi', 'Medis', true, true),
('Perawat', 'Tenaga Kesehatan', true, true),
('Bidan', 'Tenaga Kesehatan', true, true),
('Tenaga Kesehatan Masyarakat (Epidemiolog/Promkes)', 'Tenaga Kesehatan', true, true),
('Tenaga Sanitasi Lingkungan (Kesling)', 'Tenaga Kesehatan', true, true),
('Nutrisionis / Tenaga Gizi', 'Tenaga Kesehatan', true, true),
('Apoteker / Tenaga Kefarmasian', 'Tenaga Kesehatan', true, true),
('Ahli Teknologi Laboratorium Medik (ATLM)', 'Tenaga Kesehatan', true, true),
('Tenaga Kesehatan Tradisional', 'Tenaga Kesehatan', false, true),
('Tenaga Administrasi / Pendukung Kerja', 'Penunjang', false, true);

-- Insert Standard Master Data 40 Obat Esensial Puskesmas (SMILE)
INSERT INTO public.master_obat (nama_obat, kategori) VALUES
('Paracetamol 500mg', 'Analgesik/Antipiretik'),
('Ibuprofen 400mg', 'Analgesik/Antipiretik'),
('Amoxicillin 500mg', 'Antibiotik'),
('Ciprofloxacin 500mg', 'Antibiotik'),
('Eritromisin 500mg', 'Antibiotik'),
('Cefadroxil 500mg', 'Antibiotik'),
('Cotrimoxazole 480mg', 'Antibiotik'),
('Metronidazole 500mg', 'Antibiotik'),
('Oralit sachet', 'Rehidrasi'),
('Zinc tablet 20mg', 'Suplemen Anak'),
('Amlodipine 5mg', 'Antihipertensi'),
('Captopril 25mg', 'Antihipertensi'),
('Metformin 500mg', 'Antidiabetes'),
('Glibenclamide 5mg', 'Antidiabetes'),
('Salbutamol 2mg', 'Antiasma'),
('Dexamethasone 0.5mg', 'Kortikosteroid'),
('Prednison 5mg', 'Kortikosteroid'),
('Antasida Doen tablet', 'Obat Lambung'),
('Ranitidine 150mg', 'Obat Lambung'),
('Asam Mefenamat 500mg', 'Analgesik'),
('Ketoconazole 200mg', 'Antijamur'),
('Mebendazole 100mg', 'Obat Cacing'),
('Albendazole 400mg', 'Obat Cacing'),
('Vitamin B Kompleks', 'Vitamin & Suplemen'),
('Vitamin C 100mg', 'Vitamin & Suplemen'),
('Vitamin A 200.000 IU', 'Vitamin & Suplemen'),
('Kalsium Laktat (Kalk)', 'Vitamin & Suplemen'),
('Tablet Tambah Darah (Fe)', 'Suplemen Ibu Hamil'),
('Diazepam 5mg', 'Antikonvulsan/Sedatif'),
('Phenobarbital 30mg', 'Antikonvulsan'),
('Furosemide 40mg', 'Diuretik'),
('Spironolactone 25mg', 'Diuretik'),
('Cetirizine 10mg', 'Antihistamin'),
('Chlorpheniramine Maleate (CTM)', 'Antihistamin'),
('Loperamide 2mg', 'Antidiare'),
('Povidone Iodine 10% cairan', 'Antiseptik'),
('Salep Kulit Hidrokortison 2.5%', 'Obat Topikal'),
('Salep Mata Kloramfenikol 1%', 'Obat Mata'),
('Fitomenadion (Vitamin K1)', 'Koagulan'),
('Vaksin BCG', 'Imunisasi');

-- Insert Standard Master Data Alkes Kunci (ASPAK)
INSERT INTO public.master_alkes (nama_alkes, kategori) VALUES
('Stetoskop Duplex', 'Peralatan Medis'),
('Tensimeter Digital/Air Raksa', 'Peralatan Medis'),
('Termometer Infra Red / Axila', 'Peralatan Medis'),
('Timbangan Dewasa & Pengukur Tinggi', 'Peralatan Medis'),
('Timbangan Bayi (Baby Scale)', 'Peralatan Medis'),
('Infusion Set (Selang & Jarum Infus)', 'Peralatan Medis'),
('Tabung Oksigen & Regulator', 'Peralatan Medis'),
('Nebulizer', 'Peralatan Medis'),
('Dental Unit lengkap', 'Peralatan Medis'),
('Autoclave Sterilisator', 'Peralatan Medis'),
('Centrifuge Laboratorium', 'Peralatan Medis'),
('Mikroskop Binokuler', 'Peralatan Medis'),
('Glukometer (Cek Gula Darah)', 'Peralatan Medis'),
('Hematology Analyzer', 'Peralatan Medis'),
('Set Bedah Minor (Minor Surgery Set)', 'Peralatan Medis'),
('Lampu Tindakan (Examination Lamp)', 'Peralatan Medis'),
('Suction Pump (Penyedot Lendir)', 'Peralatan Medis'),
('Ambu Bag / Resusitator', 'Peralatan Medis'),
('Ekg 12 Channel (Perekam Jantung)', 'Peralatan Medis'),
('Kursi Roda Pasien', 'Peralatan Medis');
