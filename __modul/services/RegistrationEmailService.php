<?php

namespace app\services;

use Yii;
use app\models\User;
use app\models\UserRegistration;

class RegistrationEmailService
{
    public static function sendOtp(UserRegistration $registration, string $otp): bool
    {
        $subject = 'Kode OTP Verifikasi Email - Asisten Penilaian Kinerja Puskesmas';
        $body = self::wrapHtml(
            'Verifikasi Email',
            '<p>Halo <strong>' . htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8') . '</strong>,</p>' .
            '<p>Terima kasih telah melakukan pendaftaran pada aplikasi <strong>Asisten Penilaian Kinerja Puskesmas</strong>. Gunakan kode OTP berikut untuk memverifikasi alamat email Anda:</p>' .
            '<div style="font-size:28px;font-weight:700;letter-spacing:6px;margin:18px 0;background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;display:inline-block;color:#0f766e;">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</div>' .
            '<p>Kode OTP berlaku selama <strong>10 menit</strong>. Demi keamanan, mohon tidak membagikan kode OTP ini kepada siapa pun.</p>'
        );

        return self::send($registration->email, $subject, $body);
    }

    public static function sendApproved(UserRegistration $registration): bool
    {
        $loginUrl = \yii\helpers\Url::to(['/site/login'], true);
        $loginLinkHtml = $loginUrl !== ''
            ? '<p style="margin:20px 0;"><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:10px 18px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">Masuk ke Aplikasi</a></p>'
            : '';

        $subject = 'Pendaftaran Akun Disetujui - Asisten Penilaian Kinerja Puskesmas';
        $body = self::wrapHtml(
            'Pendaftaran Disetujui',
            '<p>Halo <strong>' . htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8') . '</strong>,</p>' .
            '<p>Selamat! Pengajuan akses Anda untuk aplikasi <strong>Asisten Penilaian Kinerja Puskesmas</strong> telah disetujui.</p>' .
            '<p>Anda kini dapat masuk ke dalam sistem menggunakan username dan password yang telah Anda daftarkan.</p>' .
            $loginLinkHtml
        );

        return self::send($registration->email, $subject, $body);
    }

    public static function sendRejected(UserRegistration $registration): bool
    {
        $reason = trim((string) $registration->rejection_reason);
        $reasonHtml = $reason !== ''
            ? '<p><strong>Alasan Penolakan:</strong><br><span style="color:#d32f2f;font-style:italic;">"' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '"</span></p>'
            : '';

        $subject = 'Pendaftaran Akun Ditolak - Asisten Penilaian Kinerja Puskesmas';
        $body = self::wrapHtml(
            'Pendaftaran Ditolak',
            '<p>Halo <strong>' . htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8') . '</strong>,</p>' .
            '<p>Terima kasih telah mengajukan pendaftaran akun pada aplikasi <strong>Asisten Penilaian Kinerja Puskesmas</strong>.</p>' .
            '<p>Mohon maaf, pengajuan akses Anda belum dapat disetujui saat ini.</p>' .
            $reasonHtml .
            '<p>Silakan melakukan pendaftaran ulang dengan data yang sesuai atau hubungi Administrator untuk informasi lebih lanjut.</p>'
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
            ? '<p style="margin:20px 0;"><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:10px 18px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">Masuk ke Aplikasi</a></p>'
            : '';
        $loginTextHtml = $loginUrl !== ''
            ? '<p>Silakan kunjungi halaman login aplikasi melalui tautan berikut:<br><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '</a></p>'
            : '';

        $subject = 'Akun Asisten Penilaian Kinerja Puskesmas Telah Dibuat';
        $body = self::wrapHtml(
            'Akun Berhasil Dibuat',
            '<p>Halo <strong>' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</strong>,</p>' .
            '<p>Akun Anda telah berhasil dibuat oleh Administrator pada aplikasi <strong>Asisten Penilaian Kinerja Puskesmas</strong> dengan rincian login sebagai berikut:</p>' .
            '<div style="background:#f1f5f9;padding:15px;border-radius:6px;margin:15px 0;border:1px solid #e2e8f0;font-family:monospace;">' .
            '<strong>Username/Email:</strong> ' . htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') . '<br>' .
            '<strong>Password:</strong> ' . htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8') .
            '</div>' .
            '<p>Demi keamanan akun Anda, kami sangat menyarankan untuk segera mengubah password setelah masuk pertama kali.</p>' .
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
            '<p style="margin-top:24px;color:#829ab1;font-size:12px;">Email ini dikirim otomatis oleh sistem Asisten Penilaian Kinerja Puskesmas.</p>' .
            '</div>';
    }
}
