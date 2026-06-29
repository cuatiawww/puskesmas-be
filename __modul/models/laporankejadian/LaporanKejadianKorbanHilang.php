<?php

namespace app\models\laporankejadian;

class LaporanKejadianKorbanHilang extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_korban_hilang';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'nama'], 'required'],
            [['kd_prop', 'kd_kab', 'created_id'], 'integer'],
            [['nama', 'jenis_kelamin', 'usia', 'kewarganegaraan', 'nomor_identitas'], 'string', 'max' => 255],
            [['alamat_korban', 'lokasi_hilang'], 'string'],
            [['created_date', 'tgl_laporan'], 'safe'],
            [['id_laporan', 'created_name'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'nama' => 'Nama',
            'jenis_kelamin' => 'Jenis Kelamin',
            'usia' => 'Usia',
            'kewarganegaraan' => 'Kewarganegaraan',
            'nomor_identitas' => 'Nomor Identitas',
            'alamat_korban' => 'Alamat Korban',
            'lokasi_hilang' => 'Lokasi Hilang',
        ];
    }
}

