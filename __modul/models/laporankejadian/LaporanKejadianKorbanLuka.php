<?php

namespace app\models\laporankejadian;

class LaporanKejadianKorbanLuka extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_korban_luka';
    }

    public function rules()
    {
        return [
            [['id_laporan', 'nama_faskes'], 'required'],
            [['kd_prop', 'kd_kab', 'lb_laki_laki', 'lb_perempuan', 'lb_total', 'lr_laki_laki', 'lr_perempuan', 'lr_total', 'lr_gangguan_jiwa_anak', 'lr_gangguan_jiwa_dewasa', 'created_id'], 'integer'],
            [['lb_kasus_terbanyak', 'lr_kasus_terbanyak'], 'string'],
            [['nama_faskes'], 'string', 'max' => 200],
            [['created_date', 'tgl_laporan'], 'safe'],
            [['id_laporan', 'created_name'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'nama_faskes' => 'Nama Faskes',
            'lb_laki_laki' => 'Rawat Inap Laki-Laki',
            'lb_perempuan' => 'Rawat Inap Perempuan',
            'lb_total' => 'Rawat Inap Total',
            'lb_kasus_terbanyak' => 'Kasus Rawat Inap Terbanyak',
            'lr_laki_laki' => 'Rawat Jalan Laki-Laki',
            'lr_perempuan' => 'Rawat Jalan Perempuan',
            'lr_total' => 'Rawat Jalan Total',
            'lr_kasus_terbanyak' => 'Kasus Rawat Jalan Terbanyak',
            'lr_gangguan_jiwa_anak' => 'Gangguan Jiwa Anak',
            'lr_gangguan_jiwa_dewasa' => 'Gangguan Jiwa Dewasa',
        ];
    }
}

