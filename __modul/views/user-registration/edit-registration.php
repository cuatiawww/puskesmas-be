<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\models\RegisterMasyarakatForm;

$this->title = 'Perbaharui Data - SIPKK';
?>

<div class="user-registration-edit">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4 text-primary">
                            <i class="fas fa-edit"></i> Perbaharui Data Registrasi
                        </h1>

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

                        <div class="alert alert-info" role="alert">
                            <strong><i class="fas fa-info-circle"></i> Informasi:</strong> 
                            Ubah data yang Anda inginkan. Setelah selesai, Anda akan masuk ke halaman verifikasi OTP.
                        </div>

                        <?php $form = ActiveForm::begin([
                            'id' => 'edit-registration-form',
                            'options' => ['class' => 'needs-validation'],
                        ]); ?>

                        <!-- Nama Lengkap & Email -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'nama_lengkap', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Nama lengkap'],
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'email', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'type' => 'email', 'placeholder' => 'Email'],
                                ]) ?>
                            </div>
                        </div>

                        <!-- Telepon & Institusi -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'telp', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Nomor Telepon'],
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'nama_institusi', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Nama Institusi (opsional)'],
                                ]) ?>
                            </div>
                        </div>

                        <!-- Posisi & Tujuan Akses -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'pekerjaan_posisi', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Posisi/Jabatan (opsional)'],
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'tujuan_akses', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg'],
                                ])->dropDownList(RegisterMasyarakatForm::tujuanAksesOptions(), [
                                    'prompt' => 'Pilih Tujuan...',
                                ]) ?>
                            </div>
                        </div>

                        <!-- Provinsi & Kabupaten -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'provinsi_id', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'id' => 'provinsi-select'],
                                ])->dropDownList([], [
                                    'prompt' => 'Pilih Provinsi...',
                                    'data-url' => \yii\helpers\Url::to(['formulir-bencana/get-kabupaten']),
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'kabupaten_id', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'id' => 'kabupaten-select'],
                                ])->dropDownList([], [
                                    'prompt' => 'Pilih Kab/Kota...',
                                ]) ?>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="mb-3">
                            <?= $form->field($model, 'alamat_user', [
                                'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                'inputOptions' => ['class' => 'form-control form-control-lg', 'rows' => 3, 'placeholder' => 'Alamat lengkap'],
                            ])->textarea() ?>
                        </div>

                        <!-- Tujuan Akses Lainnya -->
                        <div class="mb-4">
                            <?= $form->field($model, 'tujuan_akses_lainnya', [
                                'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Jelaskan jika pilihan "Lainnya"'],
                            ])->textarea(['rows' => 2]) ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mb-3">
                            <?= Html::submitButton('Simpan Perubahan', [
                                'class' => 'btn btn-primary btn-lg',
                                'id' => 'submit-btn',
                            ]) ?>
                        </div>

                        <div class="d-grid gap-2">
                            <?= Html::a('Kembali ke Verifikasi', 
                                ['verify-email', 'registration_id' => $registration->id], 
                                ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                        <div class="alert alert-light mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock"></i> 
                                Data username, password, dan kategori akses tidak dapat diubah
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript untuk load kabupaten berdasarkan provinsi (sama seperti di register.php)
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const provinsiSelect = document.getElementById('provinsi-select');
    const kabupatenSelect = document.getElementById('kabupaten-select');
    
    if (provinsiSelect && kabupatenSelect) {
        // Load kabupaten saat provinsi berubah
        provinsiSelect.addEventListener('change', function() {
            if (this.value) {
                fetch(`\${provinsiSelect.getAttribute('data-url')}?provinsi_id=\${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        kabupatenSelect.innerHTML = '<option value="">Pilih Kab/Kota...</option>';
                        Object.entries(data).forEach(([id, name]) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = name;
                            kabupatenSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading kabupaten:', error));
            } else {
                kabupatenSelect.innerHTML = '<option value="">Pilih Kab/Kota...</option>';
            }
        });

        // Load kabupaten awal jika ada provinsi yang dipilih
        if (provinsiSelect.value) {
            provinsiSelect.dispatchEvent(new Event('change'));
        }
    }
});
JS;
$this->registerJs($js);
?>
