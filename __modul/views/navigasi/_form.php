<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="card">
  <div class="card-body">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'nama_modul')->textInput(['maxlength' => true, 'placeholder' => 'konfigurasi, master-data'])->label('Nama Navigasi') ?>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Konfigurasi, Master Data'])->label('Label Navigasi') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <?= $form->field($model, 'deskripsi')->textarea(['rows' => 3, 'placeholder' => 'Deskripsi dari modul/navigasi']) ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'urutan')->input('number', ['placeholder' => 'Urutan tampil']) ?>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'is_active')->checkbox() ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <?= Html::submitButton($model->isNewRecord ? 'Tambah Navigasi' : 'Update Navigasi', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
          <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
      </div>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>
