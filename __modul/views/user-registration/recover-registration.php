<?php

use yii\helpers\Html;
use app\models\UserRegistration;

$this->title = 'Lanjutkan Verifikasi - SIPKK';
?>

<div class="user-registration-recover">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="fas fa-sync-alt" style="font-size: 3rem; color: #0f766e;"></i>
                        </div>

                        <h2 class="mb-2 text-primary">Lanjutkan Verifikasi</h2>
                        <p class="text-muted mb-4">
                            Registrasi Anda belum selesai diverifikasi.<br>
                            Email: <strong><?= htmlspecialchars($registration->email, ENT_QUOTES, 'UTF-8') ?></strong>
                        </p>

                        <?php if (Yii::$app->session->hasFlash('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle"></i> <?= Yii::$app->session->getFlash('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb"></i> Pilih salah satu aksi di bawah:
                        </div>

                        <div class="d-grid gap-2">
                            <!-- Option 1: Lanjut Verifikasi OTP -->
                            <div class="mb-3">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-check-circle text-success"></i> 
                                    <strong>Ada kode OTP?</strong>
                                </p>
                                <?= Html::a('Lanjutkan Verifikasi OTP', 
                                    ['verify-email', 'registration_id' => $registration->id], 
                                    ['class' => 'btn btn-primary btn-lg w-100']) ?>
                                <small class="text-muted d-block mt-2">
                                    Masukkan kode OTP yang dikirim ke email Anda
                                </small>
                            </div>

                            <div class="text-muted">
                                <strong>atau</strong>
                            </div>

                            <!-- Option 2: Edit Data -->
                            <div class="mb-3">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-pencil-alt text-warning"></i> 
                                    <strong>Ada yang salah?</strong>
                                </p>
                                <?= Html::a('Perbaharui Data Registrasi', 
                                    ['edit-registration', 'registration_id' => $registration->id], 
                                    ['class' => 'btn btn-warning btn-lg w-100']) ?>
                                <small class="text-muted d-block mt-2">
                                    Edit data sebelum verifikasi OTP
                                </small>
                            </div>

                            <div class="text-muted">
                                <strong>atau</strong>
                            </div>

                            <!-- Option 3: Resend OTP -->
                            <div>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-envelope text-info"></i> 
                                    <strong>Tidak ada kode OTP?</strong>
                                </p>
                                <?php if (!$otpExpired && $registration->otp_resend_count < 10): ?>
                                    <button type="button" class="btn btn-info btn-lg w-100" id="resend-otp-btn" 
                                            data-registration-id="<?= $registration->id ?>">
                                        <i class="fas fa-redo"></i> Kirim Ulang OTP
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        Sisa pengiriman: <strong><?= (10 - $registration->otp_resend_count) ?>/10</strong>
                                    </small>
                                <?php elseif ($otpExpired): ?>
                                    <button type="button" class="btn btn-info btn-lg w-100" id="resend-otp-btn" 
                                            data-registration-id="<?= $registration->id ?>">
                                        <i class="fas fa-redo"></i> Kirim Ulang OTP (Kadaluarsa)
                                    </button>
                                    <small class="text-warning d-block mt-2">
                                        <i class="fas fa-clock"></i> Kode OTP sudah kadaluarsa
                                    </small>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                                        <i class="fas fa-ban"></i> Batas Pengiriman Tercapai
                                    </button>
                                    <small class="text-danger d-block mt-2">
                                        Hubungi admin untuk bantuan lebih lanjut
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="border-top mt-4 pt-4">
                            <p class="text-muted mb-2">Ingin memulai registrasi baru?</p>
                            <?= Html::a('Kembali ke Pendaftaran', ['register'], 
                                ['class' => 'btn btn-outline-secondary btn-sm w-100']) ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4 alert alert-light text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Registrasi ID: <code><?= htmlspecialchars($registration->id) ?></code>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const resendBtn = document.getElementById('resend-otp-btn');
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            const registrationId = this.getAttribute('data-registration-id');
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

            fetch('<?= \yii\helpers\Url::to(['resend-otp']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ registration_id: registrationId })
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal mengirim OTP: ' + data.message);
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }
});
JS;
$this->registerJs($js);
?>
