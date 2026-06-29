<?php

namespace app\models\laporankejadian;

class LaporanKejadianLokasi extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_lokasi';
    }

    public function rules()
    {
        return [
            [['id_laporan'], 'required'],
            [['kd_prop', 'kd_kab', 'kd_kec', 'jml_terancam', 'created_id'], 'integer'],
            [['nama_desa', 'topografi', 'kecamatan', 'latitude', 'longitude'], 'string'],
            [['id_laporan', 'created_name'], 'string', 'max' => 100],
            [['tgl_laporan', 'created_date'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'kecamatan' => 'Kecamatan',
            'kd_kec' => 'Kecamatan',
            'nama_desa' => 'Nama Desa/Dusun',
            'jml_terancam' => 'Jumlah Penduduk Terancam',
            'topografi' => 'Topografi',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ];
    }
}

