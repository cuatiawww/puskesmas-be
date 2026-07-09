<?php

namespace app\services;

use Yii;
use app\models\User;
use app\models\UserRegistration;
use app\components\SystemSettingHelper;

class RegistrationEmailService
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helper: baca semua konfigurasi email dari system_setting sekali saja
    // ─────────────────────────────────────────────────────────────────────────
    protected static function cfg(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $systemName  = SystemSettingHelper::get('email_system_name',  'Asistensi Kinerja Puskesmas');
        $senderLabel = SystemSettingHelper::get('email_sender_label', $systemName . ' (KEMKES RI)');
        $globalGreeting = SystemSettingHelper::get('email_greeting_prefix', 'Yth.');
        $globalColor = SystemSettingHelper::get('email_header_color', '#0f766e');

        $cache = [
            // Nama sistem yang muncul di dalam isi email
            'system_name'        => $systemName,
            // Label pengirim di baris tanda tangan bawah isi email
            'sender_label'       => $senderLabel,
            // Sapaan awal sebelum nama user (misal "Yth.", "Kepada Yth.", "Dear")
            'greeting_prefix'    => $globalGreeting,
            // Teks nama organisasi di footer berwarna
            'footer_org'         => SystemSettingHelper::get('email_footer_org',         'Kementerian Kesehatan Republik Indonesia'),
            // Teks link di footer kanan
            'footer_link_label'  => SystemSettingHelper::get('email_footer_link_label',  'Kunjungi Website'),
            // URL link di footer kanan
            'footer_link_url'    => SystemSettingHelper::get('email_footer_link_url',    ''),
            // Warna aksen (strip atas card + warna footer) — gunakan hex
            'header_color'       => $globalColor,

            // Specific OTP config
            'otp_greeting'       => SystemSettingHelper::get('email_otp_greeting',       $globalGreeting),
            'otp_color'          => SystemSettingHelper::get('email_otp_color',          '#0284c7'),

            // Specific Approved config
            'approved_greeting'  => SystemSettingHelper::get('email_approved_greeting',  $globalGreeting),
            'approved_color'     => SystemSettingHelper::get('email_approved_color',     $globalColor),

            // Specific Rejected config
            'rejected_greeting'  => SystemSettingHelper::get('email_rejected_greeting',  $globalGreeting),
            'rejected_color'     => SystemSettingHelper::get('email_rejected_color',     '#e11d48'),

            // Specific Created config
            'created_greeting'   => SystemSettingHelper::get('email_created_greeting',   $globalGreeting),
            'created_color'      => SystemSettingHelper::get('email_created_color',      $globalColor),
        ];
        return $cache;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Blok tanda tangan bawah isi email (konsisten di semua email)
    // ─────────────────────────────────────────────────────────────────────────
    protected static function signatureBlock(): string
    {
        $c = self::cfg();
        return '
            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 25px 0;">
            <p style="margin: 0; font-size: 13px; color: #475569;">
                Dikirim oleh,<br>
                <strong>' . htmlspecialchars($c['sender_label'], ENT_QUOTES, 'UTF-8') . '</strong>
            </p>
        ';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Baris tombol CTA "Masuk ke Aplikasi"
    // ─────────────────────────────────────────────────────────────────────────
    protected static function loginButton(string $accentColor): string
    {
        $loginUrl = \yii\helpers\Url::to(['/site/login'], true);
        if (empty($loginUrl)) {
            return '';
        }
        return '<div style="margin:25px 0;">'
            . '<a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" '
            . 'style="display:inline-block;padding:12px 24px;background:' . htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8') . ';color:#ffffff;'
            . 'text-decoration:none;border-radius:6px;font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.15);">'
            . 'Masuk ke Aplikasi &rarr;'
            . '</a></div>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Blok tabel kredensial (username + password)
    // ─────────────────────────────────────────────────────────────────────────
    protected static function credentialBlock(string $username, ?string $plainPassword, string $accentColor): string
    {
        $rows = '
            <tr>
                <td width="30%" valign="top" style="color:#64748b;font-weight:bold;">Username/Email:</td>
                <td width="70%" valign="top" style="color:#334155;font-family:monospace;font-size:15px;">'
                    . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '
                </td>
            </tr>';

        if ($plainPassword !== null) {
            $rows .= '
            <tr>
                <td valign="top" style="color:#64748b;font-weight:bold;">Kata Sandi:</td>
                <td valign="top" style="color:#334155;font-family:monospace;font-size:15px;">'
                    . htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8') . '
                </td>
            </tr>';
        }

        $safeAccent = htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8');
        $block = '
            <div style="background:#f8fafc;padding:20px;border-radius:6px;margin:20px 0;border:1px solid #e2e8f0;border-left:4px solid ' . $safeAccent . ';font-size:14px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="5" style="font-family:Arial,sans-serif;">'
                    . $rows . '
                </table>
            </div>';

        if ($plainPassword !== null) {
            $block .= '<p style="font-size:13px;color:#64748b;">Demi keamanan akun Anda, mohon segera mengubah kata sandi setelah pertama kali masuk ke aplikasi.</p>';
        }

        return $block;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Blok Kode OTP Verifikasi
    // ─────────────────────────────────────────────────────────────────────────
    protected static function otpBlock(string $otp, string $accentColor): string
    {
        $safeAccent = htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8');
        return '
            <div style="background:#f8fafc;padding:25px;border-radius:6px;margin:20px 0;border:1px solid #e2e8f0;border-left:4px solid ' . $safeAccent . ';text-align:center;">
                <p style="margin:0 0 12px 0;color:#64748b;font-weight:bold;font-size:12px;letter-spacing:1px;text-transform:uppercase;">Kode OTP Verifikasi</p>
                <div style="font-size:32px;font-weight:700;letter-spacing:8px;background:#ffffff;
                            padding:12px 25px;border-radius:6px;border:1px solid #cbd5e1;
                            display:inline-block;color:' . $safeAccent . ';box-shadow:inset 0 1px 2px rgba(0,0,0,0.05);">'
                    . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '
                </div>
                <p style="margin:15px 0 0 0;font-size:13px;color:#64748b;">Kode OTP ini berlaku selama <strong>10 menit</strong>.<br>Demi keamanan, mohon tidak membagikan kode OTP ini kepada siapa pun.</p>
            </div>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. OTP Verifikasi Email
    // ─────────────────────────────────────────────────────────────────────────
    public static function sendOtp(UserRegistration $registration, string $otp): bool
    {
        $c    = self::cfg();
        $name = strtoupper(htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8'));
        $sysName = htmlspecialchars($c['system_name'], ENT_QUOTES, 'UTF-8');
        $greeting = htmlspecialchars($c['otp_greeting'], ENT_QUOTES, 'UTF-8');
        $accent = $c['otp_color'];

        $subject = 'Kode OTP Verifikasi Email - ' . $c['system_name'];

        $content = '
            <div style="color:' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-size:18px;font-weight:bold;margin-bottom:15px;">KODE VERIFIKASI EMAIL</div>
            <p>' . $greeting . ' <strong>' . $name . '</strong>,</p>
            <p>Terima kasih telah melakukan pendaftaran akun pada aplikasi <strong>' . $sysName . '</strong>.
               Gunakan kode OTP berikut untuk memverifikasi alamat email Anda:</p>'
            . self::otpBlock($otp, $accent)
            . self::loginButton($accent)
            . self::signatureBlock();

        $body = self::wrapHtml($content, $accent);
        return self::send($registration->email, $subject, $body);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Pendaftaran Akun Disetujui
    // ─────────────────────────────────────────────────────────────────────────
    public static function sendApproved(UserRegistration $registration, ?string $plainPassword = null): bool
    {
        $c       = self::cfg();
        $accent  = $c['approved_color'];
        $name    = strtoupper(htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8'));
        $sysName = htmlspecialchars($c['system_name'], ENT_QUOTES, 'UTF-8');
        $greeting = htmlspecialchars($c['approved_greeting'], ENT_QUOTES, 'UTF-8');

        $user     = $registration->user;
        $username = $user
            ? ($user->username ?? $registration->email)
            : $registration->email;

        $subject = 'Pendaftaran Akun Disetujui - ' . $c['system_name'];

        $content = '
            <div style="color:' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-size:18px;font-weight:bold;margin-bottom:15px;">PENDAFTARAN AKUN DISETUJUI</div>
            <p>' . $greeting . ' <strong>' . $name . '</strong>,</p>
            <p>Terima kasih, pendaftaran Anda telah kami verifikasi. Pengajuan akses akun Anda
               untuk aplikasi <strong>' . $sysName . '</strong> telah <strong>disetujui</strong>.</p>
            <p>Silakan gunakan informasi berikut untuk masuk ke dalam sistem:</p>'
            . self::credentialBlock($username, $plainPassword, $accent)
            . self::loginButton($accent)
            . self::signatureBlock();

        $body = self::wrapHtml($content, $accent);
        return self::send($registration->email, $subject, $body);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Pendaftaran Akun Ditolak
    // ─────────────────────────────────────────────────────────────────────────
    public static function sendRejected(UserRegistration $registration): bool
    {
        $c       = self::cfg();
        $accent  = $c['rejected_color'];
        $name    = strtoupper(htmlspecialchars($registration->nama_lengkap, ENT_QUOTES, 'UTF-8'));
        $sysName = htmlspecialchars($c['system_name'], ENT_QUOTES, 'UTF-8');
        $greeting = htmlspecialchars($c['rejected_greeting'], ENT_QUOTES, 'UTF-8');

        $reason     = trim((string) $registration->rejection_reason);
        $reasonHtml = $reason !== ''
            ? '<div style="background:#fff1f2;padding:15px 20px;border-radius:6px;margin:20px 0;border-left:4px solid ' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-size:14px;">'
              . '<strong style="color:#9f1239;">Alasan Penolakan:</strong><br>'
              . '<span style="color:' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-style:italic;">"' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '"</span>'
              . '</div>'
            : '';

        $subject = 'Pendaftaran Akun Ditolak - ' . $c['system_name'];

        $content = '
            <div style="color:' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-size:18px;font-weight:bold;margin-bottom:15px;">PENDAFTARAN AKUN DITOLAK</div>
            <p>' . $greeting . ' <strong>' . $name . '</strong>,</p>
            <p>Terima kasih telah melakukan pengajuan pendaftaran akun pada aplikasi <strong>' . $sysName . '</strong>.</p>
            <p>Mohon maaf, pengajuan pendaftaran Anda belum dapat kami setujui saat ini.</p>'
            . $reasonHtml . '
            <p>Silakan melakukan pendaftaran ulang dengan data yang sesuai dan valid,
               atau hubungi Administrator untuk informasi lebih lanjut.</p>'
            . self::signatureBlock();

        $body = self::wrapHtml($content, $accent);
        return self::send($registration->email, $subject, $body);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Akun Dibuat oleh Admin
    // ─────────────────────────────────────────────────────────────────────────
    public static function sendAdminCreatedAccount(User $user, string $plainPassword): bool
    {
        $c       = self::cfg();
        $accent  = $c['created_color'];
        $sysName = htmlspecialchars($c['system_name'], ENT_QUOTES, 'UTF-8');
        $greeting = htmlspecialchars($c['created_greeting'], ENT_QUOTES, 'UTF-8');

        $displayName = trim((string) ($user->nama_lengkap ?? '')) !== ''
            ? (string) $user->nama_lengkap
            : (string) $user->username;
        $name = strtoupper(htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'));

        $subject = 'Akun ' . $c['system_name'] . ' Telah Dibuat';

        $content = '
            <div style="color:' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . ';font-size:18px;font-weight:bold;margin-bottom:15px;">AKUN BERHASIL DIBUAT</div>
            <p>' . $greeting . ' <strong>' . $name . '</strong>,</p>
            <p>Akun Anda telah berhasil dibuat oleh Administrator pada aplikasi <strong>' . $sysName . '</strong>.
               Silakan gunakan rincian login berikut untuk mengakses akun Anda:</p>'
            . self::credentialBlock((string) $user->username, $plainPassword, $accent)
            . self::loginButton($accent)
            . self::signatureBlock();

        $body = self::wrapHtml($content, $accent);
        return self::send((string) $user->email, $subject, $body);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: kirim email via Yii mailer
    // ─────────────────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: HTML wrapper (card email dengan header logo + footer berwarna)
    // ─────────────────────────────────────────────────────────────────────────
    protected static function wrapHtml(string $content, string $themeColor = '#0f766e'): string
    {
        $c = self::cfg();

        // Logo: email_logo → login_logo → default
        $logoUrl = SystemSettingHelper::getAssetUrl(
            'email_logo',
            SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png', true),
            true
        );

        $frontendUrl   = !empty($c['footer_link_url'])
            ? $c['footer_link_url']
            : (Yii::$app->params['frontend_url'] ?? 'https://puskes-kappa.vercel.app');
        $footerOrg     = htmlspecialchars($c['footer_org'],        ENT_QUOTES, 'UTF-8');
        $footerLink    = htmlspecialchars($c['footer_link_label'], ENT_QUOTES, 'UTF-8');
        $safeLogoUrl   = htmlspecialchars($logoUrl,                ENT_QUOTES, 'UTF-8');
        $safeFrontend  = htmlspecialchars($frontendUrl,            ENT_QUOTES, 'UTF-8');
        $safeTheme     = htmlspecialchars($themeColor,             ENT_QUOTES, 'UTF-8');

        return '
        <div style="background-color:#f4f6f8;padding:30px 15px;font-family:Arial,sans-serif;min-height:100%;">
            <div style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;
                        overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);
                        border-top:6px solid ' . $safeTheme . ';">

                <!-- Header: Logo -->
                <div style="padding:25px 30px;text-align:center;border-bottom:1px solid #f1f5f9;">
                    <img src="' . $safeLogoUrl . '" alt="Logo Instansi"
                         style="height:50px;width:auto;max-width:100%;display:inline-block;">
                </div>

                <!-- Body Content -->
                <div style="padding:30px 45px;color:#334155;font-size:15px;line-height:1.7;">
                    ' . $content . '
                </div>

                <!-- Footer berwarna -->
                <div style="background-color:' . $safeTheme . ';padding:15px 30px;
                            border-bottom-left-radius:8px;border-bottom-right-radius:8px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="left" style="color:#ffffff;font-size:12px;font-weight:bold;font-family:Arial,sans-serif;">
                                ' . $footerOrg . '
                            </td>
                            <td align="right" style="font-size:12px;font-family:Arial,sans-serif;">
                                <a href="' . $safeFrontend . '"
                                   style="color:#ffffff;text-decoration:none;font-weight:bold;display:inline-block;">
                                    ' . $footerLink . ' &rarr;
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
        ';
    }
}
