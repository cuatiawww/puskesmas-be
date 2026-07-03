<?php

namespace app\services;

use Yii;
use app\models\User;

class PasswordResetService
{
    /**
     * Kirim email OTP untuk reset password
     */
    public static function sendResetOtp(User $user, string $otp): bool
    {
        try {
            Yii::info('Attempting to send password reset OTP to: ' . $user->email, __METHOD__);

            $subject = 'Kode OTP Reset Password - Asistensi Kinerja Puskesmas';
            $html = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#243b53;">' .
                '<h2 style="color:#0f766e;">Reset Password</h2>' .
                '<p>Halo ' . htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') . ',</p>' .
                '<p>Anda menerima email ini karena adanya permintaan untuk mereset password akun Anda di aplikasi <strong>Asistensi Kinerja Puskesmas</strong>.</p>' .
                '<p style="margin:20px 0;">Gunakan kode OTP berikut untuk melanjutkan proses reset password:</p>' .
                '<div style="font-size:32px;font-weight:bold;letter-spacing:8px;margin:24px 0;padding:16px;background:#f5f5f5;border-radius:8px;text-align:center;color:#0f766e;border:1px solid #e2e8f0;display:inline-block;">' .
                htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') .
                '</div>' .
                '<p><strong>Penting:</strong></p>' .
                '<ul style="margin:12px 0;">' .
                '<li>Kode OTP ini berlaku selama <strong>10 menit</strong>.</li>' .
                '<li>Demi keamanan, jangan bagikan kode OTP ini kepada siapa pun.</li>' .
                '<li>Jika Anda tidak merasa meminta untuk mereset password, silakan abaikan email ini secara aman.</li>' .
                '</ul>' .
                '<p style="margin-top:24px;color:#829ab1;font-size:12px;">Email ini dikirim otomatis oleh sistem Asistensi Kinerja Puskesmas. Mohon tidak membalas email ini.</p>' .
                '</div>';

            $result = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($user->email)
                ->setSubject($subject)
                ->setHtmlBody($html)
                ->send();

            if ($result) {
                Yii::info('Password reset OTP sent successfully to: ' . $user->email, __METHOD__);
            } else {
                Yii::warning('Password reset OTP send returned false for: ' . $user->email, __METHOD__);
            }

            return $result;
        } catch (\Throwable $e) {
            Yii::error('Gagal mengirim password reset OTP ke ' . $user->email . ': ' . $e->getMessage(), __METHOD__);
            Yii::error('Trace: ' . $e->getTraceAsString(), __METHOD__);
            return false;
        }
    }
}
