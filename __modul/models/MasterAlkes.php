<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "master_alkes".
 *
 * @property int $id
 * @property string $nama_alkes
 * @property string|null $kategori
 * @property string|null $created_at
 */
class MasterAlkes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_alkes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_alkes'], 'required'],
            [['created_at'], 'safe'],
            [['nama_alkes'], 'string', 'max' => 255],
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
            'nama_alkes' => 'Nama Alkes / Sarpras',
            'kategori' => 'Kategori Alkes',
            'created_at' => 'Created At',
        ];
    }
}
