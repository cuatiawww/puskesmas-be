<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "master_nakes".
 *
 * @property int $id
 * @property string $nama_nakes
 * @property string $kategori
 * @property bool|null $is_dli_9
 * @property bool|null $is_dli_11
 * @property string|null $created_at
 */
class MasterNakes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_nakes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_nakes', 'kategori'], 'required'],
            [['is_dli_9', 'is_dli_11'], 'boolean'],
            [['created_at'], 'safe'],
            [['nama_nakes'], 'string', 'max' => 255],
            [['kategori'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_nakes' => 'Nama Ketenagaan / Profesi',
            'kategori' => 'Kategori Tenaga',
            'is_dli_9' => 'Indikator DLI 9 Jenis Nakes (Wajib)',
            'is_dli_11' => 'Indikator DLI 11 Jenis Nakes (Wajib)',
            'created_at' => 'Created At',
        ];
    }
}
