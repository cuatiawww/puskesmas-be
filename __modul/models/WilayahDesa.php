<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model for table "wilayah_desa"
 * @property int $id
 * @property string $code
 * @property string $parent_code
 * @property string $bps_code
 * @property string $name
 * @property string $province_code
 * @property string $district_code
 * @property string $latitude
 * @property string $longitude
 * @property string $image
 * @property int $jumlah_penduduk
 * @property int $luas_wilayah
 */
class WilayahDesa extends ActiveRecord
{
    public static function tableName()
    {
        return 'wilayah_desa';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    public function rules()
    {
        return [
            [['code', 'name', 'parent_code'], 'required'],
            [['jumlah_penduduk', 'luas_wilayah'], 'integer'],
            [['code', 'parent_code', 'bps_code', 'province_code', 'district_code', 'latitude', 'longitude', 'image'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Kode Desa/Kelurahan',
            'parent_code' => 'Kecamatan',
            'bps_code' => 'Kode BPS',
            'name' => 'Nama Desa/Kelurahan',
            'province_code' => 'Kode Provinsi',
            'district_code' => 'Kode Kabupaten/Kota',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'jumlah_penduduk' => 'Jumlah Penduduk',
            'luas_wilayah' => 'Luas Wilayah',
        ];
    }

    public function getKecamatan()
    {
        return $this->hasOne(WilayahKecamatan::class, ['code' => 'parent_code']);
    }
}
