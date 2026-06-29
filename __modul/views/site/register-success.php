<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Pendaftaran Terkirim';

$this->registerCss(<<<CSS
.register-success-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f8fb;
  padding: 24px;
}
.register-success-card {
  width: min(620px, 100%);
  background: #fff;
  border: 1px solid #e6edf5;
  border-radius: 8px;
  box-shadow: 0 12px 30px rgba(16, 42, 67, .08);
  padding: 34px;
  text-align: center;
}
.register-success-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #effcf9;
  color: #1f9f99;
  font-size: 34px;
}
.register-success-card h2 {
  color: #102a43;
  margin-bottom: 10px;
  font-weight: 700;
}
.register-success-card p {
  color: #627d98;
  margin-bottom: 22px;
}
.btn-success-login {
  background: #1f9f99;
  border-color: #1f9f99;
  color: #fff;
  font-weight: 700;
  padding: 10px 22px;
}
.btn-success-login:hover,
.btn-success-login:focus {
  background: #17827d;
  border-color: #17827d;
  color: #fff;
}
CSS);
?>

<div class="register-success-page">
  <div class="register-success-card">
    <div class="register-success-icon">
      <i class="ti ti-check"></i>
    </div>
    <h2>Pendaftaran Terkirim</h2>
    <p>Akun Anda sudah masuk sebagai pengajuan akses masyarakat dan menunggu approval Admin Pusat. Untuk tahap berikutnya nanti bisa dilanjutkan dengan verifikasi email dan notifikasi approval.</p>
    <?= Html::a('Kembali ke Login', ['site/login'], ['class' => 'btn btn-success-login']) ?>
  </div>
</div>
