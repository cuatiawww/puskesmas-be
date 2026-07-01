<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "master_obat".
 *
 * @property int $id
 * @property string $nama_obat
 * @property string|null $kategori
 * @property string|null $created_at
 */
class MasterObat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_obat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_obat'], 'required'],
            [['created_at'], 'safe'],
            [['nama_obat'], 'string', 'max' => 255],
            [['kategori'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_obat' => 'Nama Obat Esensial',
            'kategori' => 'Kategori / Golongan',
            'created_at' => 'Created At',
        ];
    }
}
