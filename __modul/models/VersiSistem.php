<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "versi_sistem".
 *
 * @property int $id
 * @property string $versi
 * @property string|null $keterangan
 * @property string $tanggal
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class VersiSistem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'versi_sistem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['versi', 'tanggal'], 'required'],
            [['keterangan'], 'string'],
            [['tanggal', 'created_at', 'updated_at'], 'safe'],
            [['versi'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'versi' => 'Versi Sistem',
            'keterangan' => 'Keterangan Update',
            'tanggal' => 'Tanggal Rilis',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
