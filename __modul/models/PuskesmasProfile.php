<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "puskesmas_profile".
 *
 * @property int $id
 * @property string $kode_faskes
 * @property string $nama_puskesmas
 * @property int|null $provinsi_id
 * @property int|null $kabupaten_id
 * @property int|null $kecamatan_id
 * @property int|null $kelurahan_id
 * @property string|null $kategori_wilayah
 * @property string|null $kategori_jenis
 * @property string|null $status_pelayanan
 * @property int|null $jumlah_penduduk
 * @property string|null $nomor_izin
 * @property string|null $izin_berlaku_sampai
 * @property string|null $tanggal_registrasi
 * @property bool|null $status_aktif
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PuskesmasProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'puskesmas_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kode_faskes', 'nama_puskesmas'], 'required'],
            [['provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'jumlah_penduduk', 'jumlah_nakes_medis', 'jumlah_nakes_paramedis', 'jumlah_nakes_penunjang'], 'integer'],
            [['izin_berlaku_sampai', 'tanggal_registrasi', 'created_at', 'updated_at'], 'safe'],
            [['status_aktif', 'bangunan_permanen', 'bangunan_terpisah', 'lab_tingkat1'], 'boolean'],
            [['kode_faskes', 'kategori_wilayah', 'kategori_jenis', 'status_pelayanan', 'status_akreditasi', 'level_wilayah'], 'string', 'max' => 50],
            [['nama_puskesmas', 'nomor_izin'], 'string', 'max' => 255],
            [['kode_faskes'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kode_faskes' => 'Kode Faskes',
            'nama_puskesmas' => 'Nama Puskesmas',
            'provinsi_id' => 'Provinsi',
            'kabupaten_id' => 'Kabupaten',
            'kecamatan_id' => 'Kecamatan',
            'kelurahan_id' => 'Desa/Kelurahan',
            'kategori_wilayah' => 'Kategori Wilayah',
            'kategori_jenis' => 'Kategori Jenis',
            'status_pelayanan' => 'Status Pelayanan',
            'jumlah_penduduk' => 'Jumlah Penduduk',
            'nomor_izin' => 'Nomor Izin',
            'izin_berlaku_sampai' => 'Izin Berlaku Sampai',
            'tanggal_registrasi' => 'Tanggal Registrasi',
            'status_aktif' => 'Status Aktif',
            'bangunan_permanen' => 'Bangunan Permanen',
            'bangunan_terpisah' => 'Bangunan Terpisah Dari Lainnya',
            'lab_tingkat1' => 'Laboratorium Kesehatan Masyarakat Tingkat 1',
            'status_akreditasi' => 'Status Akreditasi Eksternal',
            'level_wilayah' => 'Level Wilayah (Provinsi/Kabupaten)',
            'jumlah_nakes_medis' => 'Jumlah Tenaga Medis (Dokter/Gigi dll)',
            'jumlah_nakes_paramedis' => 'Jumlah Tenaga Kesehatan (Perawat/Bidan dll)',
            'jumlah_nakes_penunjang' => 'Jumlah Tenaga Pendukung/Penunjang',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[WilayahProvinsi]] – joins via 'code' since provinsi_id stores the province code.
     */
    public function getProvinsi()
    {
        return $this->hasOne(\app\models\WilayahProvinsi::class, ['code' => 'provinsi_id']);
    }

    /**
     * Gets query for [[WilayahKabupaten]] – joins via 'code' since kabupaten_id stores the kabupaten code.
     */
    public function getKabupaten()
    {
        return $this->hasOne(\app\models\WilayahKabupaten::class, ['code' => 'kabupaten_id']);
    }

    /**
     * Gets query for [[WilayahKecamatan]] – joins via 'code' since kecamatan_id stores the kecamatan code.
     */
    public function getKecamatan()
    {
        return $this->hasOne(\app\models\WilayahKecamatan::class, ['code' => 'kecamatan_id']);
    }

    /**
     * Gets query for [[PuskesmasKinerja]].
     */
    public function getKinerjas()
    {
        return $this->hasMany(PuskesmasKinerja::class, ['puskesmas_id' => 'id']);
    }

    /**
     * Gets query for [[PuskesmasPenyakit]].
     */
    public function getPenyakits()
    {
        return $this->hasMany(PuskesmasPenyakit::class, ['puskesmas_id' => 'id']);
    }
}
