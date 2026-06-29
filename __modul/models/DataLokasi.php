<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class DataLokasi extends ActiveRecord
{
    public static function tableName()
    {
        return 'data_lokasi';
    }

    public function rules()
    {
        return [
            [['nomor_checkin', 'nama_lokasi'], 'required'],
            [['nomor_checkin'], 'integer'],
            [['nomor_checkin'], 'in', 'range' => [1, 2, 3, 4, 5, 6, 7, 8, 9]],
            [['nama_lokasi'], 'string', 'max' => 100],
            [['is_active'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nomor_checkin' => 'Nomor Check-in',
            'nama_lokasi' => 'Nama Lokasi',
            'is_active' => 'Status Aktif',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diperbarui Pada',
        ];
    }

    /**
     * Get sektors relation
     */
    public function getSektors()
    {
        return $this->hasMany(DataSektor::class, ['lokasi_id' => 'id']);
    }

    /**
     * Get active sektors
     */
    public function getActiveSektors()
    {
        return $this->hasMany(DataSektor::class, ['lokasi_id' => 'id'])
            ->where(['is_active' => true]);
    }

    /**
     * Get area list for dropdown by nomor_checkin
     */
    public static function getLokasiByNomor($nomor_checkin)
    {
        return static::find()
            ->where(['nomor_checkin' => $nomor_checkin, 'is_active' => true])
            ->select(['id', 'nomor_checkin', 'nama_lokasi'])
            ->asArray()
            ->all();
    }
}
