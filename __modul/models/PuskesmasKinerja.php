<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "puskesmas_kinerja".
 *
 * @property int $id
 * @property int $puskesmas_id
 * @property int $tahun
 * @property string $periode_tipe
 * @property int $periode_nilai
 * @property bool|null $dokter_tersedia
 * @property bool|null $nakes_9_jenis
 * @property bool|null $nakes_11_jenis
 * @property float|null $persen_alkes
 * @property float|null $persen_spa
 * @property int|null $jumlah_obat_esensial
 * @property float|null $persen_obat_esensial
 * @property bool|null $bmhp_tersedia
 * @property bool|null $status_blud
 * @property bool|null $sk_blud_tersedia
 * @property bool|null $status_ilp
 * @property bool|null $sk_ilp_tersedia
 * @property int|null $jumlah_pustu_aktif
 * @property float|null $skor_pkp_klaster1
 * @property float|null $skor_pkp_klaster2
 * @property float|null $skor_pkp_klaster3
 * @property float|null $skor_pkp_klaster4
 * @property float|null $skor_pkp_lintas_klaster
 * @property float|null $skor_pkp_total
 * @property string|null $alokasi_bok
 * @property string|null $realisasi_bok
 * @property string|null $realisasi_insentif_ukm
 * @property string|null $realisasi_insentif_fktp
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property PuskesmasProfile $puskesmas
 */
class PuskesmasKinerja extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'puskesmas_kinerja';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['puskesmas_id', 'tahun', 'periode_tipe', 'periode_nilai'], 'required'],
            [['puskesmas_id', 'tahun', 'periode_nilai', 'jumlah_obat_esensial', 'jumlah_pustu_aktif'], 'integer'],
            [['dokter_tersedia', 'nakes_9_jenis', 'nakes_11_jenis', 'bmhp_tersedia', 'status_blud', 'sk_blud_tersedia', 'status_ilp', 'sk_ilp_tersedia', 'prasarana_terpelihara', 'farmasi_bmhp_terkendali', 'tata_kelola_remunerasi', 'tata_kelola_barang_jasa', 'indikator_mutu_dilaporkan', 'insiden_keselamatan_dilaporkan', 'manajemen_risiko_diterapkan'], 'boolean'],
            [['persen_alkes', 'persen_spa', 'persen_obat_esensial', 'skor_pkp_klaster1', 'skor_pkp_klaster2', 'skor_pkp_klaster3', 'skor_pkp_klaster4', 'skor_pkp_lintas_klaster', 'skor_pkp_total'], 'number'],
            [['alokasi_bok', 'realisasi_bok', 'realisasi_insentif_ukm', 'realisasi_insentif_fktp', 'sumber_apbd_dau', 'sumber_apbd_bok', 'sumber_kapitasi', 'sumber_tarif', 'sumber_hibah', 'sumber_kerjasama', 'sumber_lainnya'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['periode_tipe'], 'string', 'max' => 50],
            [['puskesmas_id', 'tahun', 'periode_tipe', 'periode_nilai'], 'unique', 'targetAttribute' => ['puskesmas_id', 'tahun', 'periode_tipe', 'periode_nilai']],
            [['puskesmas_id'], 'exist', 'skipOnError' => true, 'targetClass' => PuskesmasProfile::class, 'targetAttribute' => ['puskesmas_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'puskesmas_id' => 'Puskesmas',
            'tahun' => 'Tahun',
            'periode_tipe' => 'Periode Tipe',
            'periode_nilai' => 'Periode Nilai',
            'dokter_tersedia' => 'Dokter Tersedia',
            'nakes_9_jenis' => '9 Jenis Nakes Lengkap',
            'nakes_11_jenis' => '11 Jenis Nakes Lengkap',
            'persen_alkes' => 'Persentase Alkes',
            'persen_spa' => 'Persentase SPA',
            'jumlah_obat_esensial' => 'Jumlah Obat Esensial',
            'persen_obat_esensial' => 'Persentase Obat Esensial',
            'bmhp_tersedia' => 'BMHP Tersedia',
            'status_blud' => 'Status BLUD',
            'sk_blud_tersedia' => 'SK BLUD Tersedia',
            'status_ilp' => 'Status ILP',
            'sk_ilp_tersedia' => 'SK ILP Tersedia',
            'jumlah_pustu_aktif' => 'Jumlah Pustu Aktif',
            'skor_pkp_klaster1' => 'Skor PKP Klaster 1 (Manajemen)',
            'skor_pkp_klaster2' => 'Skor PKP Klaster 2 (Ibu & Anak)',
            'skor_pkp_klaster3' => 'Skor PKP Klaster 3 (Dewasa & Lansia)',
            'skor_pkp_klaster4' => 'Skor PKP Klaster 4 (Surveilans Penyakit)',
            'skor_pkp_lintas_klaster' => 'Skor PKP Lintas Klaster',
            'skor_pkp_total' => 'Skor PKP Total',
            'alokasi_bok' => 'Alokasi BOK',
            'realisasi_bok' => 'Realisasi BOK',
            'realisasi_insentif_ukm' => 'Realisasi Insentif UKM',
            'realisasi_insentif_fktp' => 'Realisasi Insentif FKTP',
            'prasarana_terpelihara' => 'Prasarana Dilakukan Pemeliharaan Berkala',
            'farmasi_bmhp_terkendali' => 'Sediaan Farmasi & BMHP Terkendali',
            'sumber_apbd_dau' => 'Sumber Dana APBD: DAU',
            'sumber_apbd_bok' => 'Sumber Dana APBD: BOK',
            'sumber_kapitasi' => 'Sumber Dana Jasa Layanan: Kapitasi',
            'sumber_tarif' => 'Sumber Dana Jasa Layanan: Tarif Layanan',
            'sumber_hibah' => 'Sumber Dana Hibah',
            'sumber_kerjasama' => 'Sumber Dana Kerja Sama',
            'sumber_lainnya' => 'Sumber Dana Lainnya (Sah)',
            'tata_kelola_remunerasi' => 'Remunerasi Terlaksana',
            'tata_kelola_barang_jasa' => 'Tata Kelola Pengadaan Barang Jasa Terlaksana',
            'indikator_mutu_dilaporkan' => 'Pengukuran & Pelaporan Indikator Mutu Terpenuhi',
            'insiden_keselamatan_dilaporkan' => 'Pelaporan Insiden Keselamatan Pasien Terpenuhi',
            'manajemen_risiko_diterapkan' => 'Penerapan Manajemen Risiko Terpenuhi',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Puskesmas]].
     */
    public function getPuskesmas()
    {
        return $this->hasOne(PuskesmasProfile::class, ['id' => 'puskesmas_id']);
    }
}
