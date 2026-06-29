<?php

namespace app\models\laporankejadian;

class LaporanKejadianFaskesTerdampak extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_faskes_terdampak';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'nama_faskes', 'kondisi_faskes', 'fungsi_pelayanan'], 'required'],
            [['kondisi_faskes', 'fungsi_pelayanan'], 'integer'],
            [['nama_faskes'], 'string'],
            [['created_date'], 'safe'],
            [['jenis_faskes', 'id_faskes', 'id_laporan'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'nama_faskes' => 'Nama Faskes',
            'kondisi_faskes' => 'Kondisi Faskes',
            'fungsi_pelayanan' => 'Fungsi Pelayanan',
        ];
    }
}


