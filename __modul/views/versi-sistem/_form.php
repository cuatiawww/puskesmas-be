<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\VersiSistem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="versi-sistem-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="fw-bold mb-0">Formulir Log Versi Sistem</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'versi')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Masukkan nomor versi (contoh: v1.0.0-beta atau v2.1.0)'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'tanggal')->textInput([
                        'type' => 'date', 
                        'placeholder' => 'Pilih tanggal rilis'
                    ]) ?>
                </div>
            </div>

            <?= $form->field($model, 'keterangan')->textarea([
                'rows' => 6,
                'placeholder' => "Masukkan keterangan update atau catatan rilis. Contoh:\n- Menambahkan modul Bantuan baru\n- Integrasi API Satu Sehat\n- Perbaikan bug loading pada halaman login"
            ]) ?>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Log Versi', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
