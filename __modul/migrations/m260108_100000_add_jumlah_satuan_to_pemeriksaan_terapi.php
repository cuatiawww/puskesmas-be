<?php

use yii\db\Migration;

class m260108_100000_add_jumlah_satuan_to_pemeriksaan_terapi extends Migration
{
    public function safeUp()
    {
        echo "Adding jumlah and satuan columns to pemeriksaan_terapi table...\n";

        $this->addColumn('{{%pemeriksaan_terapi}}', 'jumlah', $this->integer()->defaultValue(1)->after('keterangan'));
        $this->addColumn('{{%pemeriksaan_terapi}}', 'satuan', $this->string(50)->defaultValue('tablet')->after('jumlah'));

        echo "Columns added successfully!\n";
    }

    public function safeDown()
    {
        $this->dropColumn('{{%pemeriksaan_terapi}}', 'satuan');
        $this->dropColumn('{{%pemeriksaan_terapi}}', 'jumlah');
    }
}
