ALTER TABLE puskesmas_profile 
ADD COLUMN IF NOT EXISTS level_wilayah VARCHAR(50) DEFAULT 'kabupaten';
