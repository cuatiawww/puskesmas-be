<?php

namespace app\models;

use app\components\TimeHelper;
use Yii;

class UserRegistration extends \yii\db\ActiveRecord
{
    public const STATUS_EMAIL_PENDING = 'email_pending';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'user_registration';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    public function rules()
    {
        return [
            [['kategori_akses', 'nama_lengkap', 'username', 'email', 'telp', 'alamat_user', 'provinsi_id', 'kabupaten_id', 'tujuan_akses'], 'required'],
            [['user_id', 'provinsi_id', 'kabupaten_id', 'otp_resend_count', 'approved_by', 'rejected_by'], 'integer'],
            [['alamat_user', 'user_agent', 'rejection_reason'], 'string'],
            [['email_verified_at', 'otp_expires_at', 'otp_sent_at', 'approved_at', 'rejected_at', 'created_at', 'updated_at'], 'safe'],
            [['kategori_akses', 'telp', 'status', 'ip_address'], 'string', 'max' => 64],
            [['nama_lengkap', 'username'], 'string', 'max' => 150],
            [['email', 'nama_institusi', 'pekerjaan_posisi', 'tujuan_akses_lainnya', 'otp_hash'], 'string', 'max' => 255],
            [['tujuan_akses'], 'string', 'max' => 100],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = TimeHelper::now();
        if ($insert && $this->hasAttribute('created_at') && empty($this->created_at)) {
            $this->created_at = $now;
        }
        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = $now;
        }

        return true;
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_EMAIL_PENDING => 'Menunggu Verifikasi Email',
            self::STATUS_PENDING_APPROVAL => 'Menunggu Persetujuan Admin Pusat',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    public function getUser()
    {
        $primaryKey = User::primaryKey()[0] ?? 'id';
        return $this->hasOne(User::class, [$primaryKey => 'user_id']);
    }

    public function generateOtp(): string
    {
        $otp = (string) random_int(1000, 9999);
        $this->otp_hash = Yii::$app->security->generatePasswordHash($otp);
        $this->otp_expires_at = TimeHelper::addMinutes(10);
        $this->otp_sent_at = TimeHelper::now();
        return $otp;
    }

    public function validateOtp(string $otp): bool
    {
        $otp = trim($otp);
        if (!preg_match('/^\d{4}$/', $otp)) {
            return false;
        }

        if (empty($this->otp_hash) || empty($this->otp_expires_at)) {
            return false;
        }

        if (strtotime((string) $this->otp_expires_at) < time()) {
            return false;
        }

        try {
            return Yii::$app->security->validatePassword($otp, $this->otp_hash);
        } catch (\Throwable $e) {
            Yii::warning('Validasi OTP gagal: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    public function markEmailVerified(): bool
    {
        $this->email_verified_at = TimeHelper::now();
        $this->status = self::STATUS_PENDING_APPROVAL;
        $this->otp_hash = null;
        $this->otp_expires_at = null;
        return $this->save(false);
    }

    public function markApproved(int $adminId): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $adminId;
        $this->approved_at = TimeHelper::now();
        $this->rejected_by = null;
        $this->rejected_at = null;
        $this->rejection_reason = null;
        return $this->save(false);
    }

    public function markRejected(int $adminId, ?string $reason = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejected_by = $adminId;
        $this->rejected_at = TimeHelper::now();
        $this->rejection_reason = $reason;
        return $this->save(false);
    }
}
