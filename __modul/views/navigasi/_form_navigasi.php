<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="navigasi-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form-navigasi-ajax',
        'enableAjaxValidation' => false,
    ]); ?>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'nama_modul')->textInput(['maxlength' => true, 'placeholder' => 'konfigurasi, master-data'])->label('Nama Navigasi') ?>
      </div>
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Konfigurasi, Master Data'])->label('Label Navigasi') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12 mb-3">
        <?= $form->field($model, 'deskripsi')->textarea(['rows' => 3, 'placeholder' => 'Deskripsi dari modul/navigasi']) ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'urutan')->input('number', ['placeholder' => 'Urutan tampil']) ?>
      </div>
      <div class="col-md-6 mb-3 align-self-center pt-3">
        <?= $form->field($model, 'is_active')->checkbox() ?>
      </div>
    </div>

    <div class="form-group text-end mt-3">
      <?= Html::submitButton($model->isNewRecord ? 'Tambah Navigasi' : 'Update Navigasi', ['class' => $model->isNewRecord ? 'btn btn-success px-4' : 'btn btn-primary px-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
