<?php

namespace app\models\laporankejadian;

class LaporanKejadianKorbanPengungsi extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_korban_pengungsi';
    }

    public function rules()
    {
        return [
            [['id_laporan'], 'required'],
            [['kd_prop', 'kd_kab', 'kd_kec', 'gangguan_jiwa_anak', 'gangguan_jiwa_dewasa', 'pengungsi_laki_laki', 'pengungsi_perempuan', 'pengungsi_total', 'jml_kk', 'rentan_bayi', 'rentan_balita', 'rentan_bumil', 'rentan_buteki', 'cacat_l', 'cacat_p', 'lansia_l', 'lansia_p', 'created_id', 'status_android', 'sync_status', 'bayi_0_5_bulan', 'bayi_6_11_bulan', 'anak_12_23_bulan', 'remaja_putra', 'remaja_putri', 'balita_gizi_kurang', 'balita_gizi_buruk', 'ibu_nifas', 'ibu_hamil_kurang_energi_kronis', 'penderita_penyakit_kronis', 'penderita_diare', 'penderita_ispa'], 'integer'],
            [['tgl_laporan', 'created_date', 'sync_date'], 'safe'],
            [['nama_tempat'], 'string', 'max' => 200],
            [['kecamatan'], 'string'],
            [['created_name'], 'string', 'max' => 100],
            [['id_laporan'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'kecamatan' => 'Kecamatan',
            'nama_tempat' => 'Nama Tempat',
            'gangguan_jiwa_anak' => 'Gangguan Jiwa Anak',
            'gangguan_jiwa_dewasa' => 'Gangguan Jiwa Dewasa',
            'pengungsi_laki_laki' => 'Pengungsi Laki-Laki',
            'pengungsi_perempuan' => 'Pengungsi Perempuan',
            'pengungsi_total' => 'Pengungsi Total',
            'jml_kk' => 'Jml KK',
            'rentan_bayi' => 'Rentan Bayi',
            'rentan_balita' => 'Rentan Balita',
            'rentan_bumil' => 'Rentan Bumil',
            'rentan_buteki' => 'Rentan Buteki',
            'cacat_l' => 'Cacat L',
            'cacat_p' => 'Cacat P',
            'lansia_l' => 'Lansia L',
            'lansia_p' => 'Lansia P',
        ];
    }
}


