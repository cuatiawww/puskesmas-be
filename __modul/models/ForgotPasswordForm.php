<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ForgotPasswordForm adalah model untuk form lupa password
 * User memasukkan email untuk menerima kode OTP reset password
 */
class ForgotPasswordForm extends Model
{
    public $email;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['email'], 'required', 'message' => 'Email harus diisi'],
            [['email'], 'email', 'message' => 'Format email tidak valid'],
            [['email'], 'validateEmailExists'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }

    /**
     * Validasi bahwa email terdaftar di sistem
     */
    public function validateEmailExists($attribute)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['email' => $this->email]);
            if (!$user) {
                $this->addError($attribute, 'Email tidak ditemukan dalam sistem');
            }
        }
    }

    /**
     * Generate OTP dan kirim ke email
     */
    public function sendPasswordResetOtp(): bool
    {
        $user = User::findOne(['email' => $this->email]);
        if (!$user) {
            return false;
        }

        // Generate 6 digit OTP
        $otp = sprintf('%06d', random_int(0, 999999));

        // Simpan OTP ke database dengan masa berlaku 10 menit
        $user->password_reset_otp = $otp;
        $user->password_reset_otp_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $user->password_reset_requested_at = date('Y-m-d H:i:s');

        if (!$user->save()) {
            Yii::error('Gagal menyimpan password reset OTP untuk user: ' . $user->id_user, __METHOD__);
            return false;
        }

        // Kirim email
        return \app\services\PasswordResetService::sendResetOtp($user, $otp);
    }
}
