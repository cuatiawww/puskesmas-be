<?php

namespace app\models\laporankejadian;

class LaporanKejadianWilayahTerdampak extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_wilayah_terdampak';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'desa', 'dampak_wilayah'], 'required'],
            [['jml_pos_pengungsi', 'jml_pengungsi', 'created_id'], 'integer'],
            [['desa', 'dampak_wilayah'], 'string'],
            [['id_laporan', 'created_name'], 'string', 'max' => 100],
            [['created_date'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'desa' => 'Desa',
            'dampak_wilayah' => 'Dampak Wilayah',
            'jml_pos_pengungsi' => 'Jml Pos Pengungsi',
            'jml_pengungsi' => 'Jml Pengungsi',
        ];
    }
}

