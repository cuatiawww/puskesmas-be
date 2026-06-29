<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Reset Password';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.forgot-password-auth .auth-form {
  max-width: 520px;
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
.forgot-password-auth .email-badge {
  display: inline-block;
  color: #17827d;
  background: #eefbf9;
  border-radius: 6px;
  font-size: 0.86rem;
  margin-top: 10px;
  padding: 6px 10px;
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
.forgot-password-auth .btn-auth:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}
.forgot-password-auth .auth-link {
  color: #1f9f99;
  font-weight: 700;
  text-decoration: none;
}
.forgot-password-auth .auth-link:hover {
  color: #17827d;
}
.forgot-password-auth .password-rules {
  border-left: 4px solid #2AB2A8;
  background: #eefbf9;
  color: #315b58;
  border-radius: 6px;
}
.forgot-password-auth .password-rules ul {
  margin: 8px 0 0;
  padding-left: 18px;
  font-size: 0.9rem;
}
.forgot-password-auth .password-rules li {
  color: #dc3545;
}
.forgot-password-auth .password-rules li.is-valid {
  color: #198754;
}
.forgot-password-auth .password-hint {
  color: #829ab1;
  font-size: 12px;
  margin-top: 6px;
}
.forgot-password-auth .password-strength {
  margin-top: 8px;
}
.forgot-password-auth .password-strength-track {
  height: 7px;
  border-radius: 999px;
  overflow: hidden;
  background: #e6edf5;
}
.forgot-password-auth .password-strength-bar {
  width: 0;
  height: 100%;
  border-radius: 999px;
  transition: width .18s ease, background-color .18s ease;
}
.forgot-password-auth .password-strength-text {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-top: 5px;
  color: #829ab1;
  font-size: 12px;
}
.forgot-password-auth .password-strength-label {
  font-weight: 700;
}
.forgot-password-auth .password-strength[data-level="1"] .password-strength-bar { width: 20%; background: #d92d20; }
.forgot-password-auth .password-strength[data-level="2"] .password-strength-bar { width: 40%; background: #f04438; }
.forgot-password-auth .password-strength[data-level="3"] .password-strength-bar { width: 60%; background: #f79009; }
.forgot-password-auth .password-strength[data-level="4"] .password-strength-bar { width: 80%; background: #12b76a; }
.forgot-password-auth .password-strength[data-level="5"] .password-strength-bar { width: 100%; background: #067647; }
.forgot-password-auth .password-strength[data-level="1"] .password-strength-label { color: #d92d20; }
.forgot-password-auth .password-strength[data-level="2"] .password-strength-label { color: #f04438; }
.forgot-password-auth .password-strength[data-level="3"] .password-strength-label { color: #f79009; }
.forgot-password-auth .password-strength[data-level="4"] .password-strength-label { color: #12b76a; }
.forgot-password-auth .password-strength[data-level="5"] .password-strength-label { color: #067647; }
CSS);
?>

<div class="auth-main v1 forgot-password-auth" style="background-image: url('<?= Yii::$app->params['base_url'] ?>/app_asset/images/background-sipkk.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="auth-wrapper">
        <div class="auth-form">
            <a href="<?= Yii::$app->params['base_url'] ?>" class="d-block mt-5 text-center">
                <img src="<?= Yii::$app->params['base_url'] ?>/app_asset/images/logo-kemenkes-warna.png" class="brand-logo" alt="SIPKK">
            </a>

            <div class="card mb-4 mt-3">
                <div class="card-header" style="background-color: #2AB2A8;">
                    <h4 class="text-center text-white f-w-500 mb-0">RESET PASSWORD</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5 class="mb-2 f-w-600">Verifikasi Kode OTP</h5>
                        <p class="auth-subtitle">Masukkan kode OTP dan password baru Anda.</p>
                        <span class="email-badge">Email: <strong><?= Html::encode($email) ?></strong></span>
                    </div>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'reset-password-form',
                    ]); ?>

                        <?= $form->field($model, 'otp', [
                            'template' => "<div class=\"mb-3\">{label}\n{input}\n{hint}\n{error}</div>",
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ])->textInput([
                            'class' => 'form-control text-center',
                            'placeholder' => '6 digit kode OTP',
                            'maxlength' => '6',
                            'inputmode' => 'numeric',
                            'pattern' => '[0-9]{6}',
                            'autocomplete' => 'one-time-code',
                            'style' => 'font-size: 18px; letter-spacing: 6px; font-weight: 700;',
                        ])->label('Kode OTP', ['class' => 'form-label'])
                          ->hint('Cek email Anda untuk kode OTP yang berlaku selama 10 menit.', ['class' => 'form-text text-muted']) ?>

                        <?= $form->field($model, 'password', [
                            'template' => "<div class=\"mb-3\">{label}\n{input}\n{hint}\n{error}</div>",
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ])->passwordInput([
                            'class' => 'form-control',
                            'placeholder' => 'Password baru',
                            'autocomplete' => 'new-password',
                        ])->label('Password Baru', ['class' => 'form-label'])
                          ->hint('Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan karakter khusus (@$!%*?&).', ['class' => 'password-hint']) ?>
                        <div id="password-strength" class="password-strength" data-level="0" aria-live="polite">
                          <div class="password-strength-track">
                            <div class="password-strength-bar"></div>
                          </div>
                          <div class="password-strength-text">
                            <span id="password-strength-label" class="password-strength-label">Belum diisi</span>
                            <span id="password-strength-hint">Masukkan password.</span>
                          </div>
                        </div>

                        <?= $form->field($model, 'password_confirm', [
                            'template' => "<div class=\"mb-3\">{label}\n{input}\n{error}</div>",
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ])->passwordInput([
                            'class' => 'form-control',
                            'placeholder' => 'Konfirmasi password baru',
                            'autocomplete' => 'new-password',
                        ])->label('Konfirmasi Password', ['class' => 'form-label']) ?>

                        <div class="password-rules p-3 mb-4">
                            <strong>Persyaratan Password</strong>
                            <ul>
                                <li id="length-check">Minimal 8 karakter</li>
                                <li id="uppercase-check">Minimal 1 huruf besar (A-Z)</li>
                                <li id="lowercase-check">Minimal 1 huruf kecil (a-z)</li>
                                <li id="number-check">Minimal 1 angka (0-9)</li>
                                <li id="special-check">Minimal 1 karakter khusus (@$!%*?&)</li>
                            </ul>
                        </div>

                        <div class="form-group mb-0">
                            <?= Html::submitButton('Reset Password', [
                                'class' => 'btn btn-primary btn-auth w-100',
                                'id' => 'submit-btn',
                                'disabled' => true,
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-center mt-3">
                        <?= Html::a('Kembali ke Lupa Password', ['/forgot-password/request'], ['class' => 'auth-link']) ?>
                    </div>
                </div>

                <div class="card-footer border-top">
                    <div class="text-center">
                        <p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 300;">SIPKK 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.querySelector('input[name="ResetPasswordForm[password]"]');
    const otpInput = document.querySelector('input[name="ResetPasswordForm[otp]"]');
    const submitBtn = document.getElementById('submit-btn');

    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    }

    function validatePassword() {
        if (!passwordInput || !submitBtn) {
            return;
        }

        const password = passwordInput.value;
        const checks = {
            'length-check': password.length >= 8,
            'uppercase-check': /[A-Z]/.test(password),
            'lowercase-check': /[a-z]/.test(password),
            'number-check': /\d/.test(password),
            'special-check': /[@$!%*?&]/.test(password),
        };

        let allValid = true;
        Object.keys(checks).forEach(function(id) {
            const element = document.getElementById(id);
            if (!element) {
                return;
            }

            element.classList.toggle('is-valid', checks[id]);
            if (!checks[id]) {
                allValid = false;
            }
        });

        submitBtn.disabled = !allValid;
    }

    function getPasswordScore(value) {
        if (!value) return 0;

        let score = 0;
        if (value.length >= 1) score++;
        if (value.length >= 8) score++;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value) && value.length >= 10) score++;

        return Math.min(score, 5);
    }

    function updatePasswordStrength() {
        if (!passwordInput) return;

        const value = passwordInput.value || '';
        const score = getPasswordScore(value);
        const strength = document.getElementById('password-strength');
        const strengthLabel = document.getElementById('password-strength-label');
        const strengthHint = document.getElementById('password-strength-hint');
        
        if (!strength || !strengthLabel || !strengthHint) return;

        const labels = ['', 'Sangat Lemah', 'Lemah', 'Cukup Kuat', 'Kuat', 'Sangat Kuat'];
        const hints = [
            'Masukkan password.',
            'Tambahkan minimal 8 karakter.',
            'Tambahkan huruf besar dan huruf kecil.',
            'Tambahkan angka agar memenuhi syarat.',
            'Password sudah memenuhi syarat.',
            'Password sangat kuat.'
        ];

        strength.setAttribute('data-level', score);
        strengthLabel.textContent = labels[score] || 'Belum diisi';
        strengthHint.textContent = hints[score] || hints[0];
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength();
            validatePassword();
        });
        passwordInput.addEventListener('change', function() {
            updatePasswordStrength();
            validatePassword();
        });
        validatePassword();
        updatePasswordStrength();
    }
});
</script>
