<?php

use app\models\UserRegistration;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $registration app\models\UserRegistration */

$this->title = 'Status Pendaftaran';

$status = $registration->status;
$isApproved = $status === UserRegistration::STATUS_APPROVED;
$isRejected = $status === UserRegistration::STATUS_REJECTED;
$isWaiting = $status === UserRegistration::STATUS_PENDING_APPROVAL;

$title = $isApproved ? 'Pendaftaran Disetujui' : ($isRejected ? 'Pendaftaran Ditolak' : 'Menunggu Persetujuan Admin Pusat');
$message = $isApproved
    ? 'Akun Anda sudah disetujui. Silakan login menggunakan username dan password yang didaftarkan.'
    : ($isRejected
        ? 'Mohon maaf, pengajuan akses Anda belum dapat disetujui.'
        : 'Email Anda sudah terverifikasi. Pengajuan akses sedang menunggu review Admin Pusat.');
$icon = $isApproved ? 'ti-check' : ($isRejected ? 'ti-x' : 'ti-clock');
$color = $isApproved ? '#067647' : ($isRejected ? '#d92d20' : '#1f9f99');

$this->registerCss(<<<CSS
.waiting-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f8fb;
  padding: 24px;
}
.waiting-card {
  width: min(660px, 100%);
  background: #fff;
  border: 1px solid #e6edf5;
  border-radius: 8px;
  box-shadow: 0 12px 30px rgba(16, 42, 67, .08);
  padding: 34px;
  text-align: center;
}
.waiting-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #effcf9;
  color: $color;
  font-size: 34px;
}
.waiting-card h2 {
  color: #102a43;
  margin-bottom: 10px;
  font-weight: 700;
}
.waiting-card p {
  color: #627d98;
  margin-bottom: 18px;
}
.status-box {
  background: #f8fafc;
  border: 1px solid #e6edf5;
  border-radius: 8px;
  padding: 14px;
  margin: 18px 0;
  text-align: left;
}
.btn-waiting {
  background: #1f9f99;
  border-color: #1f9f99;
  color: #fff;
  font-weight: 700;
  padding: 10px 22px;
}
.btn-waiting:hover,
.btn-waiting:focus {
  background: #17827d;
  border-color: #17827d;
  color: #fff;
}
CSS);
?>

<div class="waiting-page">
  <div class="waiting-card">
    <div class="waiting-icon">
      <i class="ti <?= Html::encode($icon) ?>"></i>
    </div>
    <h2><?= Html::encode($title) ?></h2>
    <p><?= Html::encode($message) ?></p>

    <div class="status-box">
      <div><strong>Nama:</strong> <?= Html::encode($registration->nama_lengkap) ?></div>
      <div><strong>Username:</strong> <?= Html::encode($registration->username) ?></div>
      <div><strong>Email:</strong> <?= Html::encode($registration->email) ?></div>
      <div><strong>Status:</strong> <?= Html::encode($registration->getStatusLabel()) ?></div>
      <?php if ($isRejected && $registration->rejection_reason): ?>
        <div><strong>Alasan:</strong> <?= Html::encode($registration->rejection_reason) ?></div>
      <?php endif; ?>
    </div>

    <?= Html::a($isWaiting ? 'Kembali ke Login' : 'Login', ['site/login'], ['class' => 'btn btn-waiting']) ?>
  </div>
</div>
