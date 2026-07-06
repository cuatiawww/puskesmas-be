<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Tutorial */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tutorial-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="fw-bold mb-0">Formulir Tutorial</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'nama_tutorial')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan nama tutorial (contoh: Formulir Pencatatan Kegawatdaruratan)'
            ]) ?>

            <?= $form->field($model, 'keterangan')->textarea([
                'rows' => 4,
                'placeholder' => 'Masukkan deskripsi atau keterangan singkat mengenai tutorial ini...'
            ]) ?>

            <?= $form->field($model, 'link_tutorial')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan link dokumen tutorial/PDF (contoh: https://host/file.pdf)'
            ]) ?>

            <?= $form->field($model, 'link_video')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan link video tutorial (contoh: https://youtube.com/watch?v=xxx)'
            ]) ?>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Tutorial', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
