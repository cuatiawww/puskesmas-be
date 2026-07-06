<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Download */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="download-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="fw-bold mb-0">Formulir Unduhan</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'nama_download')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan nama unduhan (contoh: Chrome Browser, Panduan Manual)'
            ]) ?>

            <?= $form->field($model, 'kategori')->textInput([
                'maxlength' => true,
                'placeholder' => 'Masukkan kategori/fungsi (contoh: Browser, Aplikasi, Dokumen)'
            ]) ?>

            <?= $form->field($model, 'link_download')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan link unduhan (contoh: https://google.com/chrome)'
            ]) ?>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Unduhan', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
