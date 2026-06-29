<?php

namespace app\models\laporankejadian;

class LaporanKejadianPenyakit extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_penyakit';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'id_penyakit', 'jml'], 'required'],
            [['jml'], 'integer'],
            [['id_penyakit'], 'string', 'max' => 255],
            [['keterangan'], 'string'],
            [['created_date'], 'safe'],
            [['id_laporan', 'created_by'], 'string', 'max' => 200],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'id_penyakit' => 'Jenis Penyakit',
            'jml' => 'Jumlah Kasus',
            'keterangan' => 'Keterangan',
        ];
    }
}

