<?php

namespace app\models\laporankejadian;

class LaporanKejadianPosPengungsi extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_pos_pengungsi';
    }

    public function rules()
    {
        return [
            [['id_laporan'], 'required'],
            [['jml_titik_pengungsian', 'jml_titik_pengungsian_terpusat', 'jml_titik_pengungsian_mandiri', 'jml_kk_pengungsi', 'jml_total_pengungsi', 'jml_pengungsi_laki', 'jml_pengungsi_perempuan', 'created_id', 'kd_prop', 'kd_kab', 'kd_kec'], 'integer'],
            [['latitude', 'longitude', 'kecamatan'], 'string'],
            [['created_date'], 'safe'],
            [['id_laporan', 'created_name'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'kecamatan' => 'Kecamatan',
            'jml_titik_pengungsian' => 'Jml Titik Pengungsian',
            'jml_titik_pengungsian_terpusat' => 'Titik Pengungsian Terpusat',
            'jml_titik_pengungsian_mandiri' => 'Titik Pengungsian Mandiri',
            'jml_kk_pengungsi' => 'Jml KK Pengungsi',
            'jml_total_pengungsi' => 'Jml Total Pengungsi',
            'jml_pengungsi_laki' => 'Jml Pengungsi Laki-Laki',
            'jml_pengungsi_perempuan' => 'Jml Pengungsi Perempuan',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ];
    }
}

