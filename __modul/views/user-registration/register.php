<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\MaskedInput;
use app\models\RegisterMasyarakatForm;
use app\components\CaptchaCustom;

$this->title = 'Pendaftaran Akun SIPKK';
?>

<div class="user-registration-register">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4 text-primary">
                            <i class="fas fa-user-plus"></i> Pendaftaran SIPKK
                        </h1>
                        
                        <?php if (Yii::$app->session->hasFlash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= Yii::$app->session->getFlash('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info" role="alert">
                            <strong>Informasi:</strong> Lengkapi semua data dengan benar. Anda akan menerima kode OTP via email untuk verifikasi.
                        </div>

                        <?php $form = ActiveForm::begin([
                            'id' => 'register-form',
                            'options' => ['class' => 'needs-validation'],
                        ]); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'kategori_akses', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg'],
                                ])->dropDownList(RegisterMasyarakatForm::kategoriAksesOptions(), [
                                    'prompt' => 'Pilih Kategori...',
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'nama_lengkap', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Nama lengkap'],
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'username', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div><small class="text-muted">4-50 karakter</small>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Username (4-50 karakter)'],
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'email', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'type' => 'email', 'placeholder' => 'Email'],
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'password', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div><small class="text-muted">Min 8 karakter (huruf besar, kecil, angka)</small>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'type' => 'password', 'placeholder' => 'Password'],
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 're_password', [
                                    'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                    'inputOptions' => ['class' => 'form-control form-control-lg', 'type' => 'password', 'placeholder' => 'Konfirmasi password'],
                                ]) ?>
                            </div>
                        </div>

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

                        <div class="mb-3">
                            <?= $form->field($model, 'alamat_user', [
                                'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                'inputOptions' => ['class' => 'form-control form-control-lg', 'rows' => 3, 'placeholder' => 'Alamat lengkap'],
                            ])->textarea() ?>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'tujuan_akses_lainnya', [
                                'template' => '{label}<div class="input-group mb-3">{input}</div>{error}',
                                'inputOptions' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Jelaskan jika pilihan "Lainnya"'],
                            ])->textarea(['rows' => 2]) ?>
                        </div>

                        <!-- Captcha -->
                        <div class="mb-3">
                            <label class="form-label">Verifikasi Captcha</label>
                            <div class="card border-light">
                                <div class="card-body p-3">
                                    <?= CaptchaCustom::widget([
                                        'name' => 'verifyCode',
                                        'captchaAction' => '/site/captcha',
                                    ]) ?>
                                </div>
                            </div>
                            <?= $form->field($model, 'verifyCode', [
                                'template' => '{input}{error}',
                                'inputOptions' => ['class' => 'form-control form-control-lg mt-2', 'placeholder' => 'Masukkan kode captcha'],
                            ]) ?>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Saya setuju dengan <a href="#" target="_blank">Syarat dan Ketentuan</a> penggunaan SIPKK
                            </label>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <?= Html::submitButton('Daftar Sekarang', ['class' => 'btn btn-primary btn-lg']) ?>
                        </div>

                        <div class="text-center">
                            <p class="text-muted">
                                Sudah punya akun? 
                                <?= Html::a('Masuk di sini', ['/site/login']) ?>
                            </p>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Load provinsi data on page load
$this->registerJs(<<<JS
    $(document).ready(function() {
        loadProvinsi();
        $('#provinsi-select').on('change', function() {
            loadKabupaten($(this).val());
        });
    });

    function loadProvinsi() {
        $.get('/sipkk-baru/formulir-bencana/debug-provinsi', function(data) {
            let options = '<option value="">Pilih Provinsi...</option>';
            if (data) {
                $.each(data, function(id, name) {
                    options += '<option value="' + id + '">' + name + '</option>';
                });
            }
            $('#provinsi-select').html(options);
        });
    }

    function loadKabupaten(provinsiId) {
        if (!provinsiId) {
            $('#kabupaten-select').html('<option value="">Pilih Kab/Kota...</option>');
            return;
        }
        $.get('/sipkk-baru/formulir-bencana/get-kabupaten', {id: provinsiId}, function(data) {
            let options = '<option value="">Pilih Kab/Kota...</option>';
            if (data) {
                $.each(data, function(id, name) {
                    options += '<option value="' + id + '">' + name + '</option>';
                });
            }
            $('#kabupaten-select').html(options);
        });
    }
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
    .btn-lg {
        border-radius: 6px;
        padding: 12px 24px;
    }
    .text-primary {
        color: #0f766e !important;
    }
</style>
