<?php

use yii\db\Migration;

class m260108_000002_add_visitasi_id_to_pemeriksaan_terapi extends Migration
{
    public function safeUp()
    {
      
        $this->addColumn('{{%pemeriksaan_terapi}}', 'visitasi_id', $this->integer()->null()->after('pemeriksaan_id'));

      
        $this->alterColumn('{{%pemeriksaan_terapi}}', 'pemeriksaan_id', $this->integer()->null());

      
        $this->addForeignKey(
            'fk_pemeriksaan_terapi_visitasi',
            '{{%pemeriksaan_terapi}}',
            'visitasi_id',
            '{{%visitasi}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_pemeriksaan_terapi_visitasi', '{{%pemeriksaan_terapi}}', 'visitasi_id');

        // Add check constraint: either pemeriksaan_id or visitasi_id must be filled (not both)
        // Note: This is PostgreSQL syntax, for MySQL you may need to handle this in application logic
        // $this->execute("ALTER TABLE pemeriksaan_terapi ADD CONSTRAINT chk_terapi_source CHECK ((pemeriksaan_id IS NOT NULL AND visitasi_id IS NULL) OR (pemeriksaan_id IS NULL AND visitasi_id IS NOT NULL))");
    } 
//ds
    public function safeDown()
    {
        $this->dropForeignKey('fk_pemeriksaan_terapi_visitasi', '{{%pemeriksaan_terapi}}');
        $this->dropIndex('idx_pemeriksaan_terapi_visitasi', '{{%pemeriksaan_terapi}}');
        $this->dropColumn('{{%pemeriksaan_terapi}}', 'visitasi_id');
        $this->alterColumn('{{%pemeriksaan_terapi}}', 'pemeriksaan_id', $this->integer()->notNull());
    }
}
