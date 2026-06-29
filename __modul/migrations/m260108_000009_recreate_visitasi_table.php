<?php

use yii\db\Migration;

class m260108_000009_recreate_visitasi_table extends Migration
{
    public function safeUp()
    {
        echo "Dropping foreign key constraint from pemeriksaan_terapi...\n";
        try {
            $this->dropForeignKey('fk_pemeriksaan_terapi_visitasi', '{{%pemeriksaan_terapi}}');
        } catch (\Exception $e) {
            echo "Foreign key might not exist: " . $e->getMessage() . "\n";
        }
        echo "Dropping existing visitasi table...\n";
        $this->dropTable('{{%visitasi}}');

        echo "Creating new visitasi table with complete structure...\n";

        $this->createTable('{{%visitasi}}', [
            'id' => $this->primaryKey(),

            // Core fields
            'id_jamaah' => $this->integer()->notNull(),
            'tanggal_visitasi' => $this->date()->notNull(),
            'waktu_visitasi' => $this->time(),
            'lokasi' => $this->string(255),

            // Status Jamaah: masih_dirawat, pulang, wafat
            'status_jamaah' => $this->string(50)->notNull()->defaultValue('masih_dirawat'),

            // Fields untuk MASIH DIRAWAT (sama seperti pemeriksaan )
            'keluhan_utama' => $this->text(),
            'anamnesa' => $this->text(),

            // Objektif - Vital Signs
            'gcs' => $this->string(255),
            'tekanan_darah' => $this->string(255),
            'nadi' => $this->integer(),
            'respirasi' => $this->integer(),
            'suhu_tubuh' => $this->decimal(4, 1),
            'spo' => $this->integer(),
            'gds' => $this->integer(),
            'kolesterol' => $this->integer(),
            'asam_urat' => $this->decimal(4, 1),

            // Objektif - Pemeriksaan Fisik
            'thoraks' => $this->text(),
            'abdomen' => $this->text(),
            'neurologi' => $this->text(),
            'fisik_lainnya' => $this->text(),

            // Assessment
            'diagnosa_utama' => $this->string(255),
            'diagnosa_tambahan' => $this->string(255),

            // Plan
            'tindakan_medis' => $this->string(255),
            'tindakan_lainnya' => $this->text(),
            'penunjang_medis' => $this->text(), 
            'kesimpulan' => $this->string(50), // rawat_jalan, rujuk, emergency

            // Rujukan fields (jika kesimpulan = rujuk)
            'rujuk_lokasi' => $this->string(255),
            'rujuk_nama_ruangan' => $this->string(255),
            'rujuk_kamar' => $this->string(255),
            'rujuk_catatan' => $this->text(),

            // Emergency fields (jika kesimpulan = emergency)
            'emergency_keterangan' => $this->text(),

            // Fields untuk PULANG
            'status_pulang' => $this->string(50), // sembuh, pulang_paksa, lainnya
            'status_pulang_keterangan' => $this->text(),
            'tujuan_kepulangan' => $this->string(50), // kembali_ke_kloter, lainnya
            'tujuan_kepulangan_keterangan' => $this->text(),

            // Fields untuk WAFAT
            'penyebab_kematian' => $this->text(),
            'kronologis_tanggal_wafat' => $this->date(),
            'kronologis_jam_wafat' => $this->time(),
            'kronologis_tempat_wafat' => $this->string(255),
            'pemakaman_lokasi' => $this->string(100), // arab_saudi, indonesia
            'pemakaman_keterangan' => $this->text(),

            // Timestamps
            'created_at' => $this->datetime(),
            'updated_at' => $this->datetime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Create indexes for better query performance
        $this->createIndex('idx_visitasi_jamaah', '{{%visitasi}}', 'id_jamaah');
        $this->createIndex('idx_visitasi_tanggal', '{{%visitasi}}', 'tanggal_visitasi');
        $this->createIndex('idx_visitasi_status', '{{%visitasi}}', 'status_jamaah');

        // Re-add foreign key from pemeriksaan_terapi to visitasi
        echo "Re-creating foreign key constraint in pemeriksaan_terapi...\n";
        $this->addForeignKey(
            'fk_pemeriksaan_terapi_visitasi',
            '{{%pemeriksaan_terapi}}',
            'visitasi_id',
            '{{%visitasi}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        echo "Visitasi table created successfully with all fields!\n";
    }

    public function safeDown()
    {
        $this->dropTable('{{%visitasi}}');
    }
}
