<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "kontak".
 *
 * @property int $id
 * @property string $nama_kontak
 * @property string|null $jabatan
 * @property string|null $email
 * @property string|null $whatsapp
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Kontak extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kontak';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_kontak', 'whatsapp'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_kontak', 'jabatan', 'email'], 'string', 'max' => 255],
            [['whatsapp'], 'string', 'max' => 50],
            [['email'], 'email', 'message' => 'Format email tidak valid.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_kontak' => 'Nama Kontak',
            'jabatan' => 'Jabatan',
            'email' => 'Email',
            'whatsapp' => 'Nomor WhatsApp',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
