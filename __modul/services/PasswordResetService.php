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

            $subject = 'Kode OTP Reset Password Puskesmas';
            $html = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#243b53;">' .
                '<h2 style="color:#0f766e;">Reset Password</h2>' .
                '<p>Halo ' . htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') . ',</p>' .
                '<p>Anda telah meminta untuk mereset password akun Puskesmas Anda.</p>' .
                '<p style="margin:20px 0;">Gunakan kode OTP berikut untuk mereset password:</p>' .
                '<div style="font-size:32px;font-weight:bold;letter-spacing:8px;margin:24px 0;padding:16px;background:#f5f5f5;border-radius:8px;text-align:center;">' .
                htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') .
                '</div>' .
                '<p><strong>Penting:</strong></p>' .
                '<ul style="margin:12px 0;">' .
                '<li>Kode OTP berlaku selama <strong>10 menit</strong></li>' .
                '<li>Jangan bagikan kode OTP ini kepada siapapun</li>' .
                '<li>Jika Anda tidak merasa meminta reset password, abaikan email ini</li>' .
                '</ul>' .
                '<p style="margin-top:24px;color:#829ab1;font-size:12px;">Email ini dikirim otomatis oleh Puskesmas. Jangan balas email ini.</p>' .
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
