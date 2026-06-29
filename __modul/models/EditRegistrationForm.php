<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\widgets\MaskedInput;

class EditRegistrationForm extends Model
{
    public $nama_lengkap;
    public $email;
    public $telp;
    public $nama_institusi;
    public $pekerjaan_posisi;
    public $alamat_user;
    public $provinsi_id;
    public $kabupaten_id;
    public $tujuan_akses;
    public $tujuan_akses_lainnya;

    private $originalEmail = null;
    private $registrationId = null;

    public function rules()
    {
        return [
            [[
                'nama_lengkap',
                'email',
                'telp',
                'alamat_user',
                'provinsi_id',
                'kabupaten_id',
                'tujuan_akses',
            ], 'required'],
            [['nama_institusi', 'pekerjaan_posisi', 'tujuan_akses_lainnya'], 'string'],
            [['nama_lengkap', 'email', 'telp'], 'string', 'max' => 150],
            [['alamat_user'], 'string', 'max' => 1000],
            [['tujuan_akses'], 'in', 'range' => array_keys(RegisterMasyarakatForm::tujuanAksesOptions())],
            [['provinsi_id', 'kabupaten_id'], 'integer'],
            ['email', 'email', 'message' => 'Format email tidak valid.'],
            ['email', 'validateUniqueEmail'],
            ['telp', 'match', 'pattern' => '/^[0-9+().\-\s]{8,25}$/', 'message' => 'Nomor telepon tidak valid.'],
            ['kabupaten_id', 'validateKabupaten'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nama_lengkap' => 'Nama Lengkap',
            'email' => 'Email',
            'telp' => 'Telp',
            'nama_institusi' => 'Nama Institusi',
            'pekerjaan_posisi' => 'Pekerjaan/Posisi',
            'alamat_user' => 'Alamat User',
            'provinsi_id' => 'Pilih Provinsi',
            'kabupaten_id' => 'Pilih Kab/Kota',
            'tujuan_akses' => 'Tujuan Akses',
            'tujuan_akses_lainnya' => 'Tujuan Akses Lainnya',
        ];
    }

    /**
     * Load data dari UserRegistration
     */
    public function loadFromRegistration(UserRegistration $registration)
    {
        $this->nama_lengkap = $registration->nama_lengkap;
        $this->email = $registration->email;
        $this->originalEmail = $registration->email;
        $this->registrationId = (int) $registration->id;
        $this->telp = $registration->telp;
        $this->nama_institusi = $registration->nama_institusi;
        $this->pekerjaan_posisi = $registration->pekerjaan_posisi;
        $this->alamat_user = $registration->alamat_user;
        $this->provinsi_id = $registration->provinsi_id;
        $this->kabupaten_id = $registration->kabupaten_id;
        $this->tujuan_akses = $registration->tujuan_akses;
        $this->tujuan_akses_lainnya = $registration->tujuan_akses_lainnya;
    }

    /**
     * Validasi email unik (tapi abaikan email original)
     */
    public function validateUniqueEmail($attribute)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $email = trim((string) $this->$attribute);
        
        // Jika email sama dengan email asli, skip validasi
        if ($this->originalEmail && $email === $this->originalEmail) {
            return;
        }

        // Cek di tabel user
        if (User::findOne(['email' => $email])) {
            $this->addError($attribute, 'Email sudah terdaftar di sistem.');
            return;
        }

        // Cek di registrasi yang lain (exclude current registration)
        $duplicate = UserRegistration::find()
            ->where(['email' => $email])
            ->andWhere(['!=', 'id', (int) $this->registrationId])
            ->andWhere(['in', 'status', [
                UserRegistration::STATUS_EMAIL_PENDING,
                UserRegistration::STATUS_PENDING_APPROVAL,
            ]])
            ->exists();

        if ($duplicate) {
            $this->addError($attribute, 'Email sudah digunakan oleh registrasi lain.');
        }
    }

    /**
     * Validasi kabupaten sesuai provinsi
     */
    public function validateKabupaten($attribute)
    {
        if ($this->hasErrors('provinsi_id') || empty($this->provinsi_id) || empty($this->$attribute)) {
            return;
        }

        $exists = MasterWilayah::find()
            ->where([
                'level_wilayah' => 3,
                'tbl_wilayah_id' => (int) $this->$attribute,
                'parent_tbl_wilayah_id' => (int) $this->provinsi_id,
            ])
            ->exists();

        if (!$exists) {
            $this->addError($attribute, 'Kab/Kota tidak sesuai dengan provinsi yang dipilih.');
        }
    }
}
