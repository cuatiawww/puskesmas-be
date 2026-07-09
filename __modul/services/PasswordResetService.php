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

            $c = RegistrationEmailService::cfg();
            $accent = $c['otp_color'] ?? '#0284c7';
            $greeting = $c['otp_greeting'] ?? 'Halo';
            $sysName = $c['system_name'] ?? 'Asistensi Kinerja Puskesmas';

            $subject = 'Kode OTP Reset Password - ' . $sysName;
            
            $displayName = trim((string) ($user->nama_lengkap ?? '')) !== ''
                ? (string) $user->nama_lengkap
                : (string) $user->username;
            $name = strtoupper(htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'));
            
            $additionalNotes = '<br>Jika Anda tidak merasa meminta untuk mereset password, silakan abaikan email ini secara aman.';
            
            $content = '
                <div style="color:' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-size:18px;font-weight:bold;margin-bottom:15px;">RESET PASSWORD</div>
                <p>' . htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') . ' <strong>' . $name . '</strong>,</p>
                <p>Anda menerima email ini karena adanya permintaan untuk mereset password akun Anda di aplikasi <strong>' . htmlspecialchars($sysName, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
                <p>Gunakan kode OTP berikut untuk melanjutkan proses reset password:</p>'
                . RegistrationEmailService::otpBlock($otp, $accent, $additionalNotes)
                . RegistrationEmailService::loginButton($accent)
                . RegistrationEmailService::signatureBlock();

            $html = RegistrationEmailService::wrapHtml($content, $accent);

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
