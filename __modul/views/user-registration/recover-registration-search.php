<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Cari Registrasi - SIPKK';
?>

<div class="user-registration-recover-search">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-search" style="font-size: 3rem; color: #0f766e;"></i>
                        </div>

                        <h2 class="text-center mb-2 text-primary">Lanjutkan Registrasi</h2>
                        <p class="text-center text-muted mb-4">
                            Masukkan email Anda untuk melanjutkan verifikasi registrasi yang belum selesai
                        </p>

                        <?php if (Yii::$app->session->hasFlash('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?= Yii::$app->session->getFlash('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php $form = ActiveForm::begin([
                            'id' => 'recover-registration-form',
                            'method' => 'post',
                        ]); ?>

                        <div class="mb-4">
                            <?= $form->field($model, 'email', [
                                'template' => '{label}<div class="input-group mb-2">{input}</div>{error}',
                                'inputOptions' => [
                                    'class' => 'form-control form-control-lg',
                                    'type' => 'email',
                                    'placeholder' => 'Masukkan email Anda',
                                    'autofocus' => true,
                                ],
                                'labelOptions' => ['class' => 'form-label'],
                            ]) ?>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <?= Html::submitButton('Cari Registrasi', [
                                'class' => 'btn btn-primary btn-lg',
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                        <div class="border-top pt-4 mt-4">
                            <p class="text-center text-muted mb-3">Atau</p>
                            <?= Html::a('Kembali ke Pendaftaran', ['register'], 
                                ['class' => 'btn btn-outline-secondary btn-lg w-100']) ?>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>ℹ️ Catatan:</strong><br>
                                Jika email tidak ditemukan, berarti:<br>
                                • Email sudah terverifikasi, atau<br>
                                • Email belum pernah mendaftar<br>
                                Silakan coba email lain atau pendaftar baru.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
