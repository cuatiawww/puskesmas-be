<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tutorial".
 *
 * @property int $id
 * @property string $nama_tutorial
 * @property string|null $keterangan
 * @property string|null $link_tutorial
 * @property string|null $link_video
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Tutorial extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tutorial';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_tutorial'], 'required'],
            [['keterangan'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_tutorial', 'link_tutorial', 'link_video'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_tutorial' => 'Nama Tutorial',
            'keterangan' => 'Keterangan',
            'link_tutorial' => 'Link Tutorial / Download PDF',
            'link_video' => 'Link Video',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
