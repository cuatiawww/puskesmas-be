<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\models\UserRegistration;

$this->title = 'Verifikasi Email - SIPKK';
?>

<div class="user-registration-verify-email">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="fas fa-envelope-open-text" style="font-size: 3rem; color: #0f766e;"></i>
                        </div>

                        <h2 class="mb-2 text-primary">Verifikasi Email</h2>
                        <p class="text-muted mb-4">
                            Kami telah mengirimkan kode OTP ke email:<br>
                            <strong><?= htmlspecialchars($registration->email, ENT_QUOTES, 'UTF-8') ?></strong>
                        </p>

                        <?php if (Yii::$app->session->hasFlash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?= Yii::$app->session->getFlash('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (Yii::$app->session->hasFlash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?= Yii::$app->session->getFlash('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($otpExpired): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock"></i> <strong>Kode OTP sudah kadaluarsa</strong><br>
                                Silakan kirim ulang untuk mendapatkan kode baru
                            </div>
                        <?php endif; ?>

                        <?php $form = ActiveForm::begin([
                            'id' => 'verify-email-form',
                            'method' => 'post',
                        ]); ?>

                        <div class="mb-4">
                            <?= $form->field($model, 'otp', [
                                'template' => '{label}<div class="input-group input-group-lg mb-2">{input}</div>{error}',
                                'inputOptions' => [
                                    'class' => 'form-control text-center',
                                    'placeholder' => '0000',
                                    'maxlength' => '4',
                                    'pattern' => '[0-9]{4}',
                                    'inputmode' => 'numeric',
                                    'autofocus' => true,
                                ],
                                'labelOptions' => ['class' => 'form-label mb-2'],
                            ])->label('Masukkan Kode OTP (4 Digit)') ?>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <?= Html::submitButton('Verifikasi', [
                                'class' => 'btn btn-primary btn-lg',
                                'id' => 'verify-btn',
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                        <!-- Resend OTP Section -->
                        <div class="border-top pt-4 mt-4">
                            <p class="text-muted mb-3">Tidak menerima kode OTP?</p>
                            
                            <?php if ($registration->otp_resend_count < 3): ?>
                                <button type="button" class="btn btn-outline-primary w-100" id="resend-otp-btn" 
                                        data-registration-id="<?= $registration->id ?>">
                                    <i class="fas fa-redo"></i> Kirim Ulang OTP
                                </button>
                                <p class="text-muted small mt-3">
                                    Sisa pengiriman ulang: <strong><?= (3 - $registration->otp_resend_count) ?>/3</strong>
                                </p>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> Batas pengiriman ulang sudah tercapai<br>
                                    Silakan hubungi admin atau coba lagi dalam beberapa saat
                                </div>
                            <?php endif; ?>

                            <div class="mt-3 pt-3 border-top">
                                <p class="text-muted mb-3">Atau</p>
                                <?= Html::a('Edit Data Registrasi', 
                                    ['edit-registration', 'registration_id' => $registration->id], 
                                    ['class' => 'btn btn-outline-warning w-100']) ?>
                                <small class="text-muted d-block mt-2 text-center">
                                    <i class="fas fa-pencil-alt"></i> Perbaharui data sebelum verifikasi
                                </small>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info mt-4 text-start">
                            <small>
                                <strong>💡 Tips:</strong>
                                <ul class="mb-0">
                                    <li>Kode OTP berlaku selama <strong>10 menit</strong></li>
                                    <li>Cek folder <strong>Spam/Junk</strong> jika belum menerima email</li>
                                    <li>Jangan bagikan kode OTP kepada siapapun</li>
                                </ul>
                            </small>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-muted">
                                <?= Html::a('Kembali ke Registrasi', ['register'], ['class' => 'text-decoration-none']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
$(document).ready(function() {
    // Restrict OTP input to numbers only
    $('#verifyemailform-otp').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Handle Resend OTP
    $('#resend-otp-btn').on('click', function() {
        let btn = $(this);
        let registrationId = btn.data('registration-id');
        
        btn.disabled = true;
        btn.html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');

        $.ajax({
            url: '/sipkk-baru/user-registration/resend-otp',
            type: 'POST',
            data: {registration_id: registrationId},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    let alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">')
                        .html('<i class="fas fa-check-circle"></i> ' + response.message + 
                              '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
                    $('#verify-email-form').before(alert);
                    
                    // Reset OTP input
                    $('#verifyemailform-otp').val('').focus();
                    
                    // Reload page to update UI
                    setTimeout(() => location.reload(), 2000);
                } else {
                    alert('Error: ' + response.message);
                    btn.disabled = false;
                    btn.html('<i class="fas fa-redo"></i> Kirim Ulang OTP');
                }
            },
            error: function() {
                alert('Terjadi error. Silakan coba lagi.');
                btn.disabled = false;
                btn.html('<i class="fas fa-redo"></i> Kirim Ulang OTP');
            }
        });
    });

    // Auto-submit when OTP reaches 4 digits
    $('#verifyemailform-otp').on('input', function() {
        if ($(this).val().length === 4) {
            // Optional: auto-submit form
            // $('#verify-email-form').submit();
        }
    });
});
JS
, \yii\web\View::POS_END);
?>

<style>
    .card {
        border-radius: 12px;
    }
    .form-control-lg {
        border-radius: 6px;
        padding: 12px 16px;
    }
    .input-group-lg {
        margin-bottom: 0;
    }
    .input-group-lg .form-control {
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 8px;
        text-align: center;
        height: 50px;
    }
    .btn-lg {
        border-radius: 6px;
        padding: 12px 24px;
    }
    .text-primary {
        color: #0f766e !important;
    }
    .btn-primary {
        background-color: #0f766e;
        border-color: #0f766e;
    }
    .btn-primary:hover {
        background-color: #0d5f59;
        border-color: #0d5f59;
    }
</style>
