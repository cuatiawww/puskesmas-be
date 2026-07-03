<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Lupa Password';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.forgot-password-auth .auth-form {
  max-width: 430px;
  width: 100%;
}
.forgot-password-auth .brand-logo {
  width: 300px;
  max-width: 82%;
}
.forgot-password-auth .form-control {
  border-radius: 6px;
}
.forgot-password-auth .auth-subtitle {
  color: #6c757d;
  font-size: 0.92rem;
  margin-bottom: 0;
}
.forgot-password-auth .btn-auth {
  border: 0;
  border-radius: 6px;
  background-color: #2AB2A8;
  font-weight: 700;
  min-height: 44px;
}
.forgot-password-auth .btn-auth:hover,
.forgot-password-auth .btn-auth:focus {
  background-color: #23998f;
}
.forgot-password-auth .auth-link {
  color: #1f9f99;
  font-weight: 700;
  text-decoration: none;
}
.forgot-password-auth .auth-link:hover {
  color: #17827d;
}
.forgot-password-auth .auth-note {
  border-left: 4px solid #2AB2A8;
  background: #eefbf9;
  color: #315b58;
  border-radius: 6px;
}
.forgot-password-auth .auth-note ul {
  margin: 8px 0 0;
  padding-left: 18px;
}
CSS);
?>

<div class="auth-main v1 forgot-password-auth" style="background-image: url('<?= \app\components\SystemSettingHelper::getAssetUrl('login_background', '/app_asset/images/background-sipkk.png') ?>'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="auth-wrapper">
        <div class="auth-form">
            <a href="<?= Yii::$app->params['base_url'] ?>" class="d-block mt-5 text-center">
                <img src="<?= \app\components\SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png') ?>" class="brand-logo" style="max-width:300px; height:auto;" alt="SIPKK">
            </a>

            <div class="card mb-4 mt-3">
                <div class="card-header" style="background-color: #2AB2A8;">
                    <h4 class="text-center text-white f-w-500 mb-0">LUPA PASSWORD</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5 class="mb-2 f-w-600">Reset Akses Akun</h5>
                        <p class="auth-subtitle">Masukkan email terdaftar untuk menerima kode OTP.</p>
                    </div>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'forgot-password-form',
                    ]); ?>

                        <?= $form->field($model, 'email', [
                            'template' => "<div class=\"mb-3\">{label}\n{input}\n{error}</div>",
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ])->textInput([
                            'type' => 'email',
                            'class' => 'form-control',
                            'placeholder' => 'Email terdaftar',
                            'autocomplete' => 'email',
                            'required' => true,
                        ])->label('Email Terdaftar', ['class' => 'form-label']) ?>

                        <div class="form-group mb-0">
                            <?= Html::submitButton('Kirim Kode OTP', [
                                'class' => 'btn btn-primary btn-auth w-100',
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-center mt-3">
                        <span class="text-muted">Ingat password Anda?</span>
                        <?= Html::a('Kembali ke Login', ['/site/login'], ['class' => 'auth-link']) ?>
                    </div>

                    <div class="auth-note p-3 mt-4">
                        <strong>Informasi</strong>
                        <ul>
                            <li>Kode OTP akan dikirim ke email Anda</li>
                            <li>Kode OTP berlaku selama 10 menit</li>
                            <li>Jangan bagikan kode OTP dengan siapapun</li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer border-top">
                    <div class="text-center">
                        <p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 300;"><?= \yii\helpers\Html::encode(\app\components\SystemSettingHelper::get('footer_text', 'SIPKK 2026')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
