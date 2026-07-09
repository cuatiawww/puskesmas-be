<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ResetPasswordForm adalah model untuk form reset password
 * User memasukkan kode OTP dan password baru
 */
class ResetPasswordForm extends Model
{
    public $email;
    public $otp;
    public $password;
    public $password_confirm;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['email', 'otp', 'password', 'password_confirm'], 'required'],
            [['email'], 'email', 'message' => 'Format email tidak valid'],
            [['otp'], 'string', 'length' => 6, 'message' => 'Kode OTP harus 6 digit'],
            [['otp'], 'validateOtp'],
            [['password'], 'string', 'min' => 8, 'message' => 'Password minimal 8 karakter'],
            [['password'], 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{8,}$/', 
             'message' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus (@$!%*?&)'],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'message' => 'Konfirmasi password tidak cocok'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'otp' => 'Kode OTP',
            'password' => 'Password Baru',
            'password_confirm' => 'Konfirmasi Password',
        ];
    }

    /**
     * Validasi OTP
     */
    public function validateOtp($attribute)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['email' => $this->email]);
            if (!$user) {
                $this->addError($attribute, 'Email tidak ditemukan');
                return;
            }

            // Cek OTP
            if ($user->password_reset_otp !== $this->otp) {
                $this->addError($attribute, 'Kode OTP salah');
                return;
            }

            // Cek waktu kadaluarsa
            if ($user->password_reset_otp_expires_at && strtotime($user->password_reset_otp_expires_at) < time()) {
                $this->addError($attribute, 'Kode OTP sudah kadaluarsa, silakan minta kode baru');
                return;
            }
        }
    }

    /**
     * Reset password untuk user
     */
    public function resetPassword(): bool
    {
        $user = User::findOne(['email' => $this->email]);
        if (!$user) {
            return false;
        }

        // Set password baru
        $user->password = Yii::$app->security->generatePasswordHash($this->password);

        // Clear OTP
        $user->password_reset_otp = null;
        $user->password_reset_otp_expires_at = null;
        $user->password_reset_requested_at = null;

        if (!$user->save()) {
            Yii::error('Gagal menyimpan password baru untuk user: ' . $user->id, __METHOD__);
            return false;
        }

        // Log info
        Yii::info('Password reset berhasil untuk user: ' . $user->email, __METHOD__);
        return true;
    }
}
