<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\VerifyEmailForm */
/* @var $registration app\models\UserRegistration */

$this->title = 'Verifikasi Email';

$this->registerCss(<<<CSS
.verify-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f8fb;
  padding: 24px;
}
.verify-card {
  width: min(560px, 100%);
  background: #fff;
  border: 1px solid #e6edf5;
  border-radius: 8px;
  box-shadow: 0 12px 30px rgba(16, 42, 67, .08);
  padding: 32px;
}
.verify-title {
  color: #102a43;
  font-weight: 700;
  margin-bottom: 8px;
  text-align: center;
}
.verify-subtitle {
  color: #627d98;
  margin-bottom: 22px;
  text-align: center;
}
.otp-input {
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 8px;
}
.verify-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
  margin-top: 16px;
}
.btn-verify {
  background: #1f9f99;
  border-color: #1f9f99;
  color: #fff;
  font-weight: 700;
}
.btn-verify:hover,
.btn-verify:focus {
  background: #17827d;
  border-color: #17827d;
  color: #fff;
}
@media (max-width: 575px) {
  .verify-actions {
    flex-direction: column;
    align-items: stretch;
  }
}
CSS);
?>

<div class="verify-page">
  <div class="verify-card">
    <h2 class="verify-title">Verifikasi Email</h2>
    <p class="verify-subtitle">
      Masukkan OTP 4 digit yang dikirim ke <?= Html::encode($registration->email) ?>.
      OTP berlaku selama 10 menit.
    </p>

    <?php foreach (['success', 'warning', 'error'] as $type): ?>
      <?php if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
          <?= Html::encode(Yii::$app->session->getFlash($type)) ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php $form = ActiveForm::begin(['id' => 'verify-email-form']); ?>
      <?= $form->field($model, 'otp')->textInput([
          'class' => 'form-control otp-input',
          'maxlength' => 4,
          'inputmode' => 'numeric',
          'autocomplete' => 'one-time-code',
          'placeholder' => '0000',
      ])->label(false) ?>

      <div class="verify-actions">
        <?= Html::a('Kembali ke Login', ['site/login'], ['class' => 'btn btn-outline-secondary']) ?>
        <div class="d-flex gap-2">
          <?= Html::submitButton('Verifikasi', ['class' => 'btn btn-verify']) ?>
        </div>
      </div>
    <?php ActiveForm::end(); ?>

    <?php $resendForm = ActiveForm::begin([
        'action' => Url::to(['site/resend-otp', 'id' => $registration->id]),
        'method' => 'post',
        'options' => ['class' => 'mt-3 text-center'],
    ]); ?>
      <?= Html::submitButton('Kirim Ulang OTP', ['class' => 'btn btn-link']) ?>
    <?php ActiveForm::end(); ?>
  </div>
</div>
