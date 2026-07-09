<?php

namespace app\models;

use app\components\TimeHelper;
use app\services\WilayahService;
use Yii;
use yii\base\Model;

class FaskesForm extends Model
{
    public $id;
    public $kode_satusehat;
    public $kode_sarana;
    public $nama;
    public $alamat;

    // Type A: RS, Puskesmas, Klinik
    public $kode_prop;
    public $nama_prop;
    public $kode_kab;
    public $nama_kab;

    // Type B: Posyandu, Pustu
    public $kode_provinsi;
    public $nama_provinsi;
    public $kode_kabkota;
    public $nama_kabkota;

    // Common
    public $kode_kecamatan;
    public $nama_kecamatan;
    public $kode_kelurahan;
    public $nama_kelurahan;

    public $status_sarana;
    public $operasional;
    public $telp;
    public $email;

    public function rules()
    {
        return [
            [['nama', 'kode_satusehat', 'kode_sarana'], 'required'],
            [['alamat', 'status_sarana', 'telp', 'email'], 'string'],
            [['operasional'], 'integer'],
            [['kode_satusehat', 'kode_sarana', 'nama', 'alamat', 'status_sarana', 'telp', 'email', 'kode_prop', 'nama_prop', 'kode_kab', 'nama_kab', 'kode_provinsi', 'nama_provinsi', 'kode_kabkota', 'nama_kabkota', 'kode_kecamatan', 'nama_kecamatan', 'kode_kelurahan', 'nama_kelurahan'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'kode_satusehat' => 'Kode Satusehat',
            'kode_sarana' => 'Kode Sarana / BPJS',
            'nama' => 'Nama Faskes',
            'alamat' => 'Alamat Lengkap',
            'kode_prop' => 'Provinsi',
            'nama_prop' => 'Nama Provinsi',
            'kode_kab' => 'Kabupaten/Kota',
            'nama_kab' => 'Nama Kabupaten/Kota',
            'kode_provinsi' => 'Provinsi',
            'nama_provinsi' => 'Nama Provinsi',
            'kode_kabkota' => 'Kabupaten/Kota',
            'nama_kabkota' => 'Nama Kabupaten/Kota',
            'kode_kecamatan' => 'Kecamatan',
            'nama_kecamatan' => 'Nama Kecamatan',
            'kode_kelurahan' => 'Kelurahan / Desa',
            'nama_kelurahan' => 'Nama Kelurahan / Desa',
            'status_sarana' => 'Status Sarana',
            'operasional' => 'Operasional',
            'telp' => 'No. Telepon',
            'email' => 'Email',
        ];
    }

    public function loadFromRow(array $row, string $jenis): void
    {
        $this->id = $row['id'] ?? null;
        $this->kode_satusehat = $row['kode_satusehat'] ?? null;
        $this->kode_sarana = $row['kode_sarana'] ?? null;
        $this->nama = $row['nama'] ?? null;
        $this->alamat = $row['alamat'] ?? null;
        $this->status_sarana = $row['status_sarana'] ?? null;
        $this->operasional = $row['operasional'] ?? null;
        $this->telp = $row['telp'] ?? null;
        $this->email = $row['email'] ?? null;
        $this->kode_kecamatan = $row['kode_kecamatan'] ?? null;
        $this->nama_kecamatan = $row['nama_kecamatan'] ?? null;

        if (in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
            $this->kode_prop = $row['kode_prop'] ?? null;
            $this->nama_prop = $row['nama_prop'] ?? null;
            $this->kode_kab = $row['kode_kab'] ?? null;
            $this->nama_kab = $row['nama_kab'] ?? null;
        } else {
            $this->kode_provinsi = $row['kode_provinsi'] ?? null;
            $this->nama_provinsi = $row['nama_provinsi'] ?? null;
            $this->kode_kabkota = $row['kode_kabkota'] ?? null;
            $this->nama_kabkota = $row['nama_kabkota'] ?? null;
            $this->kode_kelurahan = $row['kode_kelurahan'] ?? null;
            $this->nama_kelurahan = $row['nama_kelurahan'] ?? null;
        }
    }

    public function save(string $tableName, string $jenis): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $db = Yii::$app->db;
        $wilayahService = new WilayahService();

        $namaProv = '';
        $namaKab = '';
        $namaKec = '';
        $namaKel = '';

        if (in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
            $provCode = $this->kode_prop;
            $kabCode = $this->kode_kab;
        } else {
            $provCode = $this->kode_provinsi;
            $kabCode = $this->kode_kabkota;
        }

        if (!empty($provCode)) {
            $namaProv = $wilayahService->findProvinsiName($provCode) ?: '';
        }
        if (!empty($kabCode)) {
            $namaKab = $wilayahService->findKabupatenName($kabCode) ?: '';
        }
        if (!empty($this->kode_kecamatan)) {
            $namaKec = (string) $db->createCommand("SELECT name FROM wilayah_kecamatan WHERE code = :code", [':code' => $this->kode_kecamatan])->queryScalar() ?: '';
        }
        if (!empty($this->kode_kelurahan)) {
            $namaKel = (string) $db->createCommand("SELECT name FROM wilayah_desa WHERE code = :code", [':code' => $this->kode_kelurahan])->queryScalar() ?: '';
        }

        $columns = [
            'kode_satusehat' => $this->kode_satusehat,
            'kode_sarana' => $this->kode_sarana,
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'status_sarana' => $this->status_sarana,
            'operasional' => (int)$this->operasional,
            'telp' => $this->telp,
            'email' => $this->email,
            'kode_kecamatan' => $this->kode_kecamatan,
            'nama_kecamatan' => $namaKec,
            'update_date' => TimeHelper::now(),
        ];

        if (in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
            $columns['kode_prop'] = $provCode;
            $columns['nama_prop'] = $namaProv;
            $columns['kode_kab'] = $kabCode;
            $columns['nama_kab'] = $namaKab;
            $columns['jenis_sarana_nama'] = $jenis === 'rs' ? 'Rumah Sakit' : ($jenis === 'puskesmas' ? 'Puskesmas' : 'Klinik');
        } else {
            $columns['kode_provinsi'] = $provCode;
            $columns['nama_provinsi'] = $namaProv;
            $columns['kode_kabkota'] = $kabCode;
            $columns['nama_kabkota'] = $namaKab;
            $columns['kode_kelurahan'] = $this->kode_kelurahan;
            $columns['nama_kelurahan'] = $namaKel;
            $columns['jenis_sarana_nama'] = $jenis === 'posyandu' ? 'Posyandu' : 'Pustu';
            
            // Set additional codes for standard mapping
            if ($jenis === 'posyandu') {
                $columns['jenis_sarana_kode'] = '131';
                $columns['jenis_sarana_nama_alt'] = 'Posyandu';
            } elseif ($jenis === 'pustu') {
                $columns['jenis_sarana_kode'] = '132';
                $columns['jenis_sarana_nama_alt'] = 'Pustu';
            }
        }

        if ($this->id) {
            $db->createCommand()->update($tableName, $columns, ['id' => $this->id])->execute();
        } else {
            $db->createCommand()->insert($tableName, $columns)->execute();
            $this->id = $db->getLastInsertID();
        }

        return true;
    }
}
