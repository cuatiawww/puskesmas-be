<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Modul;
use yii\helpers\ArrayHelper;

$iconReferenceUrl = 'https://html.phoenixcoded.net/flatable/elements/icon-phosphor.html';

?>

<div class="card">
  <div class="card-body">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
      <div class="col-md-12">
        <?= $form->field($model, 'modul_id')->dropDownList(
          ArrayHelper::map(Modul::find()->where(['is_active' => true])->orderBy('urutan')->all(), 'id', 'label'),
          [
            'prompt' => '-- Pilih Navigasi --',
          ]
        )->label('Navigasi') ?>
        <small class="form-text text-muted">Pilih navigasi terlebih dahulu, lalu isi nama modul yang akan tampil di bawah navigasi tersebut.</small>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'nama_sub_modul')->textInput(['maxlength' => true, 'placeholder' => 'data-pasien, data-master'])->label('Nama Modul') ?>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Data Pasien, Data Master'])->label('Label Modul') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'route')->textInput(['maxlength' => true, 'placeholder' => '/data-pasien/index'])->label('Route') ?>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'ph-duotone ph-database'])->label('Ikon') ?>
        <div class="form-text">
          Jika belum menentukan ikon, lihat
          <a href="<?= $iconReferenceUrl ?>" target="_blank" rel="noopener">master data ikon Flat Able (Phosphor)</a>.
        </div>
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
          <?= Html::submitButton($model->isNewRecord ? 'Tambah Modul' : 'Update Modul', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
          <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
      </div>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>
