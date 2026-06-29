<?php

namespace app\models\laporankejadian;

class LaporanKejadianTenaga extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_tenaga';
    }

    public function rules()
    {
        return [
            [['id_laporan'], 'required'],
            [['jml_dokter', 'jml_perawat', 'jml_bidan', 'jml_farmasi', 'jml_kesling', 'jml_gizi', 'jml_tenaga_lainnya', 'kebutuhan_dokter', 'kebutuhan_perawat', 'kebutuhan_bidan', 'kebutuhan_farmasi', 'kebutuhan_gizi', 'kebutuhan_tenaga_lainnya', 'kebutuhan_kesling', 'created_id'], 'integer'],
            [['nama_faskes', 'created_by'], 'string', 'max' => 200],
            [['created_date'], 'safe'],
            [['id_laporan'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'nama_faskes' => 'Nama Faskes',
            'jml_dokter' => 'Jumlah Dokter',
            'kebutuhan_dokter' => 'Kebutuhan Dokter',
            'jml_perawat' => 'Jumlah Perawat',
            'kebutuhan_perawat' => 'Kebutuhan Perawat',
            'jml_bidan' => 'Jumlah Bidan',
            'kebutuhan_bidan' => 'Kebutuhan Bidan',
            'jml_farmasi' => 'Jumlah Farmasi',
            'kebutuhan_farmasi' => 'Kebutuhan Farmasi',
            'jml_gizi' => 'Jumlah Gizi',
            'kebutuhan_gizi' => 'Kebutuhan Gizi',
            'jml_kesling' => 'Jumlah Kesling',
            'kebutuhan_kesling' => 'Kebutuhan Kesling',
            'jml_tenaga_lainnya' => 'Jumlah Tenaga Lainnya',
            'kebutuhan_tenaga_lainnya' => 'Kebutuhan Tenaga Lainnya',
        ];
    }
}

