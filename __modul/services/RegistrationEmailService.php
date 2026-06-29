<?php

namespace app\services;

use Yii;
use app\models\User;
use app\models\UserRegistration;

class RegistrationEmailService
{
    public static function sendOtp(UserRegistration $registration, string $otp): bool
    {
        $subject = 'Kode OTP Verifikasi Email Puskesmas';
        $body = self::wrapHtml(
            'Verifikasi Email',
            '<p>Gunakan kode OTP berikut untuk verifikasi email Anda:</p>' .
            '<div style="font-size:28px;font-weight:700;letter-spacing:6px;margin:18px 0;">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</div>' .
            '<p>Kode OTP berlaku selama 10 menit.</p>'
        );

        return self::send($registration->email, $subject, $body);
    }

    public static function sendApproved(UserRegistration $registration): bool
    {
        $subject = 'Pendaftaran Puskesmas Disetujui';
        $body = self::wrapHtml(
            'Pendaftaran Disetujui',
            '<p>Halo ' . htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8') . ',</p>' .
            '<p>Pengajuan akses Puskesmas Anda telah disetujui. Anda sudah dapat login menggunakan username yang didaftarkan.</p>'
        );

        return self::send($registration->email, $subject, $body);
    }

    public static function sendRejected(UserRegistration $registration): bool
    {
        $reason = trim((string) $registration->rejection_reason);
        $reasonHtml = $reason !== ''
            ? '<p><strong>Alasan:</strong> ' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '</p>'
            : '';

        $subject = 'Pendaftaran Puskesmas Ditolak';
        $body = self::wrapHtml(
            'Pendaftaran Ditolak',
            '<p>Halo ' . htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8') . ',</p>' .
            '<p>Mohon maaf, pengajuan akses Puskesmas Anda belum dapat disetujui.</p>' .
            $reasonHtml
        );

        return self::send($registration->email, $subject, $body);
    }

    public static function sendAdminCreatedAccount(User $user, string $plainPassword): bool
    {
        $displayName = trim((string) ($user->nama_lengkap ?? '')) !== ''
            ? (string) $user->nama_lengkap
            : (string) $user->username;

        $loginUrl = \yii\helpers\Url::to(['/site/login'], true);
        $loginLinkHtml = $loginUrl !== ''
            ? '<p style="margin:20px 0;"><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:10px 18px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">Buka Beranda Utama Puskesmas</a></p>'
            : '';
        $loginTextHtml = $loginUrl !== ''
            ? '<p>Silakan kunjungi beranda utama Puskesmas melalui tautan berikut:<br><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '</a></p>'
            : '';

        $subject = 'Akun Puskesmas Anda Telah Dibuat';
        $body = self::wrapHtml(
            'Akun Berhasil Dibuat',
            '<p>Halo <strong>' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</strong>,</p>' .
            '<p>Akun Anda telah berhasil dibuat oleh admin/pengelola Puskesmas dengan rincian login berikut:</p>' .
            '<div style="background:#f1f5f9;padding:15px;border-radius:6px;margin:15px 0;border:1px solid #e2e8f0;font-family:monospace;">' .
            '<strong>Username/Email:</strong> ' . htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') . '<br>' .
            '<strong>Password:</strong> ' . htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8') .
            '</div>' .
            '<p>Anda dapat masuk ke sistem menggunakan informasi tersebut melalui tautan berikut:</p>' .
            $loginLinkHtml .
            $loginTextHtml
        );

        return self::send((string) $user->email, $subject, $body);
    }

    protected static function send(string $to, string $subject, string $html): bool
    {
        try {
            Yii::info('Attempting to send email to: ' . $to . ', subject: ' . $subject, __METHOD__);
            
            $result = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($to)
                ->setSubject($subject)
                ->setHtmlBody($html)
                ->send();
                
            if ($result) {
                Yii::info('Email sent successfully to: ' . $to, __METHOD__);
            } else {
                Yii::warning('Email send returned false for: ' . $to, __METHOD__);
            }
            
            return $result;
        } catch (\Throwable $e) {
            Yii::error('Gagal mengirim email ke ' . $to . ': ' . $e->getMessage(), __METHOD__);
            Yii::error('Trace: ' . $e->getTraceAsString(), __METHOD__);
            return false;
        }
    }

    protected static function wrapHtml(string $title, string $content): string
    {
        return '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#243b53;">' .
            '<h2 style="color:#0f766e;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>' .
            $content .
            '<p style="margin-top:24px;color:#829ab1;font-size:12px;">Email ini dikirim otomatis oleh Puskesmas.</p>' .
            '</div>';
    }
}
