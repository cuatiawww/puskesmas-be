<?php
/**
 * Script test email langsung — jalankan via browser:
 * http://localhost/puskesmas/test_email.php?to=email@tujuan.com&type=otp
 *
 * Parameter:
 *  - to   : alamat email tujuan (wajib)
 *  - type : otp | approved | rejected | created  (default: semua)
 */

// ── Bootstrap Yii2 ─────────────────────────────────────────────
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV')   or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/_conf/web.php';

// Hapus komponen yang tidak dibutuhkan untuk mode CLI/script
unset($config['as beforeRequest']);

$app = new yii\web\Application($config);

// ── Parameter dari URL ─────────────────────────────────────────
$targetEmail = $_GET['to'] ?? 'test@example.com';
$type        = $_GET['type'] ?? 'all';

// ── Buat mock data ─────────────────────────────────────────────
// Mock UserRegistration
$mockRegistration = new app\models\UserRegistration();
$mockRegistration->nama_lengkap   = 'Yosua Test User';
$mockRegistration->email          = $targetEmail;
$mockRegistration->rejection_reason = 'Data yang diisikan tidak lengkap dan tidak sesuai dengan persyaratan yang ditentukan.';

// Mock User (untuk approved & created)
$mockUser = new app\models\User();
$mockUser->username    = $targetEmail;
$mockUser->nama_lengkap = 'Yosua Test User';

// Lampirkan mock user ke registration
$mockRegistration->populateRelation('user', $mockUser);

// ── Kirim Email ────────────────────────────────────────────────
$results = [];

if ($type === 'otp' || $type === 'all') {
    $otp    = (string) rand(100000, 999999);
    $result = app\services\RegistrationEmailService::sendOtp($mockRegistration, $otp);
    $results[] = [
        'type'    => 'OTP Verifikasi Email',
        'to'      => $targetEmail,
        'otp'     => $otp,
        'success' => $result,
    ];
}

if ($type === 'approved' || $type === 'all') {
    $plainPwd = 'TMPPASS1';
    $result   = app\services\RegistrationEmailService::sendApproved($mockRegistration, $plainPwd);
    $results[] = [
        'type'      => 'Pendaftaran Disetujui',
        'to'        => $targetEmail,
        'password'  => $plainPwd,
        'success'   => $result,
    ];
}

if ($type === 'rejected' || $type === 'all') {
    $result = app\services\RegistrationEmailService::sendRejected($mockRegistration);
    $results[] = [
        'type'    => 'Pendaftaran Ditolak',
        'to'      => $targetEmail,
        'success' => $result,
    ];
}

if ($type === 'created' || $type === 'all') {
    $plainPwd = 'ADMIN123';
    $result   = app\services\RegistrationEmailService::sendAdminCreatedAccount($mockUser, $plainPwd);
    $results[] = [
        'type'     => 'Akun Dibuat Admin',
        'to'       => $targetEmail,
        'password' => $plainPwd,
        'success'  => $result,
    ];
}

// ── Tampilkan Hasil ────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Test Email — Puskesmas</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 30px; }
    .card { max-width: 700px; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,.08); overflow: hidden; }
    .header { background: #0f766e; color: #fff; padding: 20px 30px; }
    .header h1 { margin: 0; font-size: 20px; }
    .header p { margin: 4px 0 0; font-size: 13px; opacity: .8; }
    .body { padding: 25px 30px; }
    .result { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
    .result.ok  { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .result.fail{ background: #fff1f2; border: 1px solid #fecdd3; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: bold; }
    .badge.ok   { background: #0f766e; color: #fff; }
    .badge.fail { background: #e11d48; color: #fff; }
    .meta { font-size: 12px; color: #64748b; margin-top: 3px; }
    .footer { text-align: center; padding: 15px; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; }
    .url-info { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; font-size: 13px; }
    .url-info code { font-family: monospace; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; }
  </style>
</head>
<body>
<div class="card">
  <div class="header">
    <h1>🧪 Test Kirim Email — Puskesmas</h1>
    <p>Email dikirim ke: <strong><?= htmlspecialchars($targetEmail) ?></strong></p>
  </div>
  <div class="body">
    <div class="url-info">
      Gunakan parameter URL untuk memilih jenis email:<br>
      <code>?to=email@tujuan.com&type=otp</code> &nbsp;|&nbsp;
      <code>type=approved</code> &nbsp;|&nbsp;
      <code>type=rejected</code> &nbsp;|&nbsp;
      <code>type=created</code> &nbsp;|&nbsp;
      <code>type=all</code>
    </div>
    <?php foreach ($results as $r): ?>
    <div class="result <?= $r['success'] ? 'ok' : 'fail' ?>">
      <div>
        <span class="badge <?= $r['success'] ? 'ok' : 'fail' ?>"><?= $r['success'] ? '✓ TERKIRIM' : '✗ GAGAL' ?></span>
        <strong style="margin-left: 8px;"><?= htmlspecialchars($r['type']) ?></strong>
        <div class="meta">
          Kepada: <strong><?= htmlspecialchars($r['to']) ?></strong>
          <?php if (!empty($r['otp'])): ?> &nbsp;·&nbsp; OTP: <strong><?= $r['otp'] ?></strong><?php endif; ?>
          <?php if (!empty($r['password'])): ?> &nbsp;·&nbsp; Password: <strong><?= htmlspecialchars($r['password']) ?></strong><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="footer">Script ini hanya untuk pengujian. Hapus sebelum deploy ke production.</div>
</div>
</body>
</html>
