<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "download".
 *
 * @property int $id
 * @property string $nama_download
 * @property string|null $kategori
 * @property string|null $link_download
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Download extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'download';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_download', 'link_download'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_download', 'kategori', 'link_download'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_download' => 'Nama Download',
            'kategori' => 'Kategori / Fungsi',
            'link_download' => 'Link Download',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
