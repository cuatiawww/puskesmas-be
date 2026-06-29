<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "data_dukung".
 *
 * @property int $id
 * @property string|null $id_dukung
 * @property string $id_jemaah
 * @property string $nama_jemaah
 * @property string $jenis_dokumen
 * @property string $nama_dokumen
 * @property string|null $nomor_dokumen
 * @property string|null $file_path
 * @property string|null $keterangan
 * @property string|null $status_verifikasi
 * @property string|null $tanggal_verifikasi
 * @property string|null $petugas_verifikasi
 * @property string|null $catatan_verifikasi
 */
class DataDukung extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'data_dukung';
    }

    public function rules()
    {
        return [
            [['id_jemaah', 'nama_jemaah', 'jenis_dokumen', 'nama_dokumen'], 'required'],
            [['tanggal_verifikasi'], 'safe'],
            [['keterangan', 'catatan_verifikasi'], 'string'],
            [['id_dukung', 'id_jemaah', 'nama_jemaah', 'jenis_dokumen', 'nama_dokumen', 'nomor_dokumen', 'file_path', 'status_verifikasi', 'petugas_verifikasi'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_dukung' => 'ID Dukung',
            'id_jemaah' => 'ID Jemaah',
            'nama_jemaah' => 'Nama Jemaah',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nama_dokumen' => 'Nama Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'file_path' => 'File Path',
            'keterangan' => 'Keterangan',
            'status_verifikasi' => 'Status Verifikasi',
            'tanggal_verifikasi' => 'Tanggal Verifikasi',
            'petugas_verifikasi' => 'Petugas Verifikasi',
            'catatan_verifikasi' => 'Catatan Verifikasi',
        ];
    }
}
