<?php

namespace app\models\laporankejadian;

class LaporanKejadianFaskesRusak extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_faskes_rusak';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'nama_faskes'], 'required'],
            [['kd_prop', 'kd_kab', 'kd_kec', 'rusak_berat', 'rusak_sedang', 'rusak_ringan', 'berfungsi', 'tidak_berfungsi', 'created_id'], 'integer'],
            [['nama_faskes'], 'string', 'max' => 100],
            [['created_date', 'tgl_laporan'], 'safe'],
            [['id_laporan', 'created_name'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'nama_faskes' => 'Nama Faskes',
            'rusak_berat' => 'Rusak Berat',
            'rusak_sedang' => 'Rusak Sedang',
            'rusak_ringan' => 'Rusak Ringan',
            'berfungsi' => 'Masih Berfungsi',
            'tidak_berfungsi' => 'Tidak Berfungsi',
        ];
    }
}

