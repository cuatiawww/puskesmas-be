<?php

namespace app\models\laporankejadian;

class LaporanKejadianKorbanMeninggal extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_korban_meninggal';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'nama'], 'required'],
            [['kd_prop', 'kd_kab', 'created_id'], 'integer'],
            [['nama', 'jenis_kelamin', 'usia', 'kewarganegaraan'], 'string', 'max' => 255],
            [['alamat_korban', 'tempat_meninggal', 'penyebab_kematian'], 'string'],
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
            'alamat_korban' => 'Alamat Korban',
            'tempat_meninggal' => 'Tempat Meninggal',
            'penyebab_kematian' => 'Penyebab Kematian',
        ];
    }
}


