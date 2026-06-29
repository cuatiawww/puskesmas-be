<?php

namespace app\models\laporankejadian;

class LaporanKejadianHeader extends BaseLaporanKejadianModel
{
    public static function tableName()
    {
        return 'laporan_kejadian_header';
    }

    public function rules()
    {
        return [
            [['id_laporan'], 'required'],
            [['kd_prop', 'kd_kab', 'id_jenis_bencana', 'akses_lokasi', 'jaringan_listrik', 'air_bersih', 'meninggal_pria', 'meninggal_wanita', 'meninggal_total', 'hilang_pria', 'hilang_wanita', 'hilang_total', 'luka_berat_total', 'luka_ringan_total', 'pengungsi_total', 'penduduk_terdampak'], 'integer'],
            [['deskripsi_bencana', 'akses_lokasi_keterangan', 'jalur_komunikasi', 'kronologis', 'bantuan', 'rekomendasi', 'latitude_array', 'longitude_array', 'catatan_lainnya', 'tahap_a'], 'string'],
            [['id_laporan', 'kecamatan', 'nama_dinkes', 'wilayah_waktu', 'waktu_bencana', 'last_step', 'sk_siaga', 'verifikasi_name_prov', 'verifikasi_name', 'status_siaga_name', 'jenis_laporan', 'id_penyakit_klb', 'mobilisasi_emt', 'mobilisasi_psc'], 'string', 'max' => 255],
            [['tgl_bencana', 'created_date', 'tgl_verifikasi_prov', 'tgl_verifikasi', 'tgl_status_siaga', 'sync_date'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_laporan' => 'Id Laporan',
            'kd_prop' => 'Kd Prop',
            'kd_kab' => 'Kd Kab',
            'kecamatan' => 'Kecamatan',
            'nama_dinkes' => 'Nama Dinkes',
            'id_jenis_bencana' => 'Jenis Bencana',
            'tgl_bencana' => 'Tanggal Bencana',
            'waktu_bencana' => 'Waktu Bencana',
            'wilayah_waktu' => 'Wilayah Waktu',
            'deskripsi_bencana' => 'Deskripsi Bencana',
            'akses_lokasi' => 'Akses Lokasi',
            'akses_lokasi_keterangan' => 'Keterangan Akses Lokasi',
            'jalur_komunikasi' => 'Jalur Komunikasi',
            'jaringan_listrik' => 'Jaringan Listrik',
            'air_bersih' => 'Air Bersih',
            'kronologis' => 'Kronologis',
            'bantuan' => 'Bantuan',
            'rekomendasi' => 'Rekomendasi',
            'mobilisasi_emt' => 'Mobilisasi EMT',
            'mobilisasi_psc' => 'Mobilisasi PSC',
        ];
    }
}

