<?php

namespace app\models;

use app\components\TimeHelper;
use Yii;
use yii\base\Model;
use app\models\level_user\LevelUser;
use app\services\RegistrationEmailService;
use app\services\WilayahService;

class RegisterMasyarakatForm extends Model
{
    public const SCENARIO_API = 'api';

    public $kategori_akses;
    public $nama_lengkap;
    public $username;
    public $password;
    public $re_password;
    public $email;
    public $telp;
    public $nama_institusi;
    public $pekerjaan_posisi;
    public $alamat_user;
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $desa_id;
    public $tujuan_akses;
    public $tujuan_akses_lainnya;
    public $verifyCode;

    public function rules()
    {
        return [
            [[
                'kategori_akses',
                'nama_lengkap',
                'username',
                'password',
                're_password',
                'email',
                'telp',
                'alamat_user',
                'provinsi_id',
                'kabupaten_id',
                'tujuan_akses',
            ], 'required'],
            [['kecamatan_id', 'desa_id'], 'required', 'on' => self::SCENARIO_API],
            [['verifyCode'], 'required', 'except' => self::SCENARIO_API],
            [['nama_institusi', 'pekerjaan_posisi', 'tujuan_akses_lainnya'], 'string'],
            [['nama_lengkap', 'username', 'email', 'telp'], 'string', 'max' => 150],
            [['alamat_user'], 'string', 'max' => 1000],
            [['kategori_akses'], 'in', 'range' => array_keys(self::kategoriAksesOptions())],
            [['tujuan_akses'], 'in', 'range' => array_keys(self::tujuanAksesOptions())],
            [['provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id'], 'string', 'max' => 32],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_.-]{4,50}$/', 'message' => 'Username minimal 4 karakter dan hanya boleh huruf, angka, titik, garis bawah, atau strip.'],
            ['username', 'validateUniqueUsername'],
            ['email', 'email'],
            ['email', 'validateUniqueEmail'],
            ['telp', 'match', 'pattern' => '/^[0-9+().\-\s]{8,25}$/', 'message' => 'Nomor telepon tidak valid.'],
            ['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{8,}$/', 'message' => 'Password minimal 8 karakter, harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus (@$!%*?&).'],
            ['re_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Konfirmasi password tidak sama.'],
            ['verifyCode', function ($attribute) {
                if ($this->scenario === self::SCENARIO_API) {
                    return;
                }
                if (!\app\components\CaptchaCustom::validateInput($this->$attribute, true)) {
                    $this->addError($attribute, 'Kode captcha salah atau kadaluarsa.');
                }
            }],
            ['kabupaten_id', 'validateKabupaten'],
            ['kecamatan_id', 'validateKecamatan'],
            ['desa_id', 'validateDesa'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'kategori_akses' => 'Kategori Akses',
            'nama_lengkap' => 'Nama Lengkap',
            'username' => 'Username',
            'password' => 'Password',
            're_password' => 'Re-Password',
            'email' => 'Email',
            'telp' => 'Telp',
            'nama_institusi' => 'Nama Institusi',
            'pekerjaan_posisi' => 'Pekerjaan/Posisi',
            'alamat_user' => 'Alamat User',
            'provinsi_id' => 'Pilih Provinsi',
            'kabupaten_id' => 'Pilih Kab/Kota',
            'kecamatan_id' => 'Pilih Kecamatan',
            'desa_id' => 'Pilih Desa/Kelurahan',
            'tujuan_akses' => 'Tujuan Akses',
            'tujuan_akses_lainnya' => 'Tujuan Akses Lainnya',
            'verifyCode' => 'Kode Captcha',
        ];
    }

    public static function kategoriAksesOptions(): array
    {
        return [
            'masyarakat_umum' => 'Masyarakat Umum',
            'lintas_sektor' => 'Lintas Sektor',
            'ngo_ormas' => 'NGO/Ormas',
            'lainnya' => 'Lainnya',
        ];
    }

    public static function tujuanAksesOptions(): array
    {
        return [
            'riset_analisa' => 'Riset/Analisa',
            'referensi_media' => 'Sumber Referensi/Media',
            'integrasi_interoperabilitas' => 'Integrasi/Interoperabilitas',
            'lainnya' => 'Lainnya',
        ];
    }

    public function validateUniqueUsername($attribute)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $username = trim((string) $this->$attribute);
        $pendingRegistration = UserRegistration::find()
            ->where(['username' => $username])
            ->andWhere(['status' => [
                UserRegistration::STATUS_EMAIL_PENDING,
                UserRegistration::STATUS_PENDING_APPROVAL,
                UserRegistration::STATUS_APPROVED,
            ]])
            ->exists();

        if (User::findOne(['username' => $username]) || $pendingRegistration) {
            $this->addError($attribute, 'Username sudah terdaftar.');
        }
    }

    public function validateUniqueEmail($attribute)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $email = trim((string) $this->$attribute);
        
        // Cek existing user di tabel user
        if (User::findOne(['email' => $email])) {
            $this->addError($attribute, 'Email sudah terdaftar. Silakan login atau gunakan email lain.');
            return;
        }

        // Cek registrasi pending
        $existingRegistration = UserRegistration::find()
            ->where(['email' => $email])
            ->andWhere(['in', 'status', [
                UserRegistration::STATUS_EMAIL_PENDING,
                UserRegistration::STATUS_PENDING_APPROVAL,
            ]])
            ->one();

        if ($existingRegistration) {
            if ($existingRegistration->status === UserRegistration::STATUS_EMAIL_PENDING) {
                // Mark untuk recovery - jangan block, biarkan controller handle
                $this->addError($attribute, 'EMAIL_PENDING_RECOVERY:' . $existingRegistration->id);
            } else {
                // Sudah dalam approval
                $this->addError($attribute, 'Email Anda sedang dalam proses verifikasi. Menunggu persetujuan admin.');
            }
            return;
        }

        // Cek registrasi yang sudah approved - berarti user sudah ada
        $approvedRegistration = UserRegistration::find()
            ->where(['email' => $email])
            ->andWhere(['status' => UserRegistration::STATUS_APPROVED])
            ->one();

        if ($approvedRegistration && $approvedRegistration->user_id) {
            $this->addError($attribute, 'Email sudah terdaftar. Silakan login atau gunakan email lain.');
        }
    }

    public function validateKabupaten($attribute)
    {
        if ($this->hasErrors('provinsi_id') || empty($this->provinsi_id) || empty($this->$attribute)) {
            return;
        }

        $wilayah = new WilayahService();
        if (!$wilayah->isValidKabupaten((string)$this->provinsi_id, (string)$this->$attribute)) {
            $this->addError($attribute, 'Kab/Kota tidak sesuai dengan provinsi yang dipilih.');
        }
    }

    public function validateKecamatan($attribute)
    {
        if ($this->hasErrors('kabupaten_id') || empty($this->kabupaten_id) || empty($this->$attribute)) {
            return;
        }

        $wilayah = new WilayahService();
        if (!$wilayah->isValidKecamatan((string)$this->kabupaten_id, (string)$this->$attribute)) {
            $this->addError($attribute, 'Kecamatan tidak sesuai dengan kab/kota yang dipilih.');
        }
    }

    public function validateDesa($attribute)
    {
        if ($this->hasErrors('kecamatan_id') || empty($this->kecamatan_id) || empty($this->$attribute)) {
            return;
        }

        $wilayah = new WilayahService();
        if (!$wilayah->isValidDesa((string)$this->kecamatan_id, (string)$this->$attribute)) {
            $this->addError($attribute, 'Desa/kelurahan tidak sesuai dengan kecamatan yang dipilih.');
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = User::getDb()->beginTransaction();

        $user = new User();
        $user->username = trim($this->username);
        $user->password = Yii::$app->security->generatePasswordHash($this->password);

        $this->assignIfExists($user, 'nama_lengkap', trim($this->nama_lengkap));
        $this->assignIfExists($user, 'email', trim($this->email));
        $this->assignIfExists($user, 'no_telpon', trim($this->telp));
        $this->assignIfExists($user, 'phone', trim($this->telp));
        $this->assignIfExists($user, 'alamat', trim($this->alamat_user));
        $this->assignIfExists($user, 'master_wilayah_id', null);
        $this->assignIfExists($user, 'kd_prop', (string) $this->provinsi_id);
        $this->assignIfExists($user, 'kd_kab', (string) $this->kabupaten_id);
        $this->assignIfExists($user, 'kd_kecamatan', (string) $this->kecamatan_id);

        $levelUserId = $this->resolveMasyarakatLevelId();
        if ($levelUserId === null) {
            $transaction->rollBack();
            $this->addError('kategori_akses', 'Level user Masyarakat belum tersedia dan gagal dibuat otomatis.');
            return false;
        }

        $this->assignIfExists($user, 'level_user_id', $levelUserId);
        $this->assignIfExists($user, 'id_user_level', $levelUserId);
        $this->assignIfExists($user, 'is_active', false);
        $this->assignIfExists($user, 'status', 0);

        $now = TimeHelper::now();
        $this->assignIfExists($user, 'created_at', $now);
        $this->assignIfExists($user, 'updated_at', $now);
        $this->assignIfExists($user, 'join_date', $now);
        $this->assignIfExists($user, 'authkey', Yii::$app->security->generateRandomString(32));
        $this->assignIfExists($user, 'token', (string) random_int(1000, 9999));

        $extra = [
            'register_type' => 'masyarakat',
            'approval_status' => 'pending',
            'kategori_akses' => $this->kategori_akses,
            'tujuan_akses' => $this->tujuan_akses,
            'tujuan_akses_lainnya' => $this->tujuan_akses_lainnya,
            'nama_institusi' => $this->nama_institusi,
            'pekerjaan_posisi' => $this->pekerjaan_posisi,
            'alamat_user' => $this->alamat_user,
            'provinsi_code' => (string) $this->provinsi_id,
            'kabupaten_code' => (string) $this->kabupaten_id,
            'kecamatan_code' => (string) $this->kecamatan_id,
            'desa_code' => (string) $this->desa_id,
            'ip_address' => Yii::$app->request->userIP,
            'user_agent' => Yii::$app->request->userAgent,
            'registered_at' => $now,
        ];

        $encodedExtra = json_encode($extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assignIfExists($user, 'keterangan', $encodedExtra);
        $this->assignIfExists($user, 'detail_lengkap', $encodedExtra);

        if (!$user->save()) {
            $transaction->rollBack();
            foreach ($user->getErrors() as $attribute => $messages) {
                $this->addError($attribute, implode(' ', $messages));
            }
            return false;
        }

        $registration = new UserRegistration();
        $registration->user_id = (int) $user->getId();
        $registration->kategori_akses = $this->kategori_akses;
        $registration->nama_lengkap = trim($this->nama_lengkap);
        $registration->username = trim($this->username);
        $registration->email = trim($this->email);
        $registration->telp = trim($this->telp);
        $registration->nama_institusi = trim((string) $this->nama_institusi);
        $registration->pekerjaan_posisi = trim((string) $this->pekerjaan_posisi);
        $registration->alamat_user = trim($this->alamat_user);
        $registration->provinsi_id = (int) $this->provinsi_id;
        $registration->kabupaten_id = (int) $this->kabupaten_id;
        $registration->tujuan_akses = $this->tujuan_akses;
        $registration->tujuan_akses_lainnya = trim((string) $this->tujuan_akses_lainnya);
        $registration->status = UserRegistration::STATUS_EMAIL_PENDING;
        $registration->ip_address = Yii::$app->request->userIP;
        $registration->user_agent = Yii::$app->request->userAgent;

        $otp = $registration->generateOtp();

        if (!$registration->save()) {
            $transaction->rollBack();
            foreach ($registration->getErrors() as $attribute => $messages) {
                $this->addError($attribute, implode(' ', $messages));
            }
            return false;
        }

        $transaction->commit();

        if (!RegistrationEmailService::sendOtp($registration, $otp)) {
            if ($this->scenario !== self::SCENARIO_API) {
                Yii::$app->session->setFlash('warning', 'Pendaftaran tersimpan, tetapi email OTP gagal dikirim. Silakan gunakan kirim ulang OTP.');
            }
        }

        return $registration;
    }

    protected function resolveMasyarakatLevelId(): ?int
    {
        $level = LevelUser::find()->where(['ilike', 'nama_level', 'masyarakat'])->one();

        if (!$level) {
            $level = LevelUser::find()->where(['ilike', 'nama_level', 'user'])->orderBy(['id' => SORT_ASC])->one();
        }

        if (!$level) {
            $level = $this->createMasyarakatLevel();
        }

        return $level ? (int) $level->id : null;
    }

    protected function createMasyarakatLevel(): ?LevelUser
    {
        $level = new LevelUser();
        $level->nama_level = 'Masyarakat';
        $level->deskripsi = 'Akses masyarakat untuk pengajuan akun publik';
        $level->is_active = true;

        if ($level->hasAttribute('created_at')) {
            $level->created_at = TimeHelper::now();
        }

        if ($level->hasAttribute('updated_at')) {
            $level->updated_at = TimeHelper::now();
        }

        try {
            return $level->save(false) ? $level : null;
        } catch (\Throwable $e) {
            Yii::error('Gagal membuat level Masyarakat otomatis: ' . $e->getMessage(), __METHOD__);
            $existing = LevelUser::find()->where(['ilike', 'nama_level', 'masyarakat'])->one();
            return $existing ?: null;
        }
    }

    protected function assignIfExists(User $user, string $attribute, $value): void
    {
        if ($user->hasAttribute($attribute)) {
            $user->setAttribute($attribute, $value);
        }
    }
}
