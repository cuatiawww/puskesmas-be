<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Modul;
use yii\helpers\ArrayHelper;

$iconReferenceUrl = 'https://html.phoenixcoded.net/flatable/elements/icon-phosphor.html';
?>

<div class="modul-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form-modul-ajax',
        'enableAjaxValidation' => false,
    ]); ?>

    <div class="row">
      <div class="col-md-12 mb-3">
        <label class="form-label font-weight-bold">Navigasi Parent</label>
        <div class="input-group">
          <?= $form->field($model, 'modul_id', [
              'options' => ['class' => 'flex-grow-1'],
          ])->dropDownList(
              ArrayHelper::map(Modul::find()->where(['is_active' => true])->orderBy('urutan')->all(), 'id', 'label'),
              [
                  'prompt' => '-- Pilih Navigasi --',
                  'id' => 'modul-dropdown-inline',
                  'class' => 'form-select',
                  'required' => true,
              ]
          )->label(false) ?>
          <button type="button" class="btn btn-primary" id="btn-add-navigasi-inline" title="Tambah Navigasi Baru Inline">
            <i class="ti ti-plus"></i>
          </button>
        </div>
        <small class="form-text text-muted">Pilih navigasi kategori di atas, atau klik tombol <strong>+</strong> jika kategori belum dibuat.</small>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'nama_sub_modul')->textInput(['maxlength' => true, 'placeholder' => 'data-pasien, data-master'])->label('Nama Modul') ?>
      </div>
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Data Pasien, Data Master'])->label('Label Modul') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'route')->textInput(['maxlength' => true, 'placeholder' => '/data-pasien/index'])->label('Route') ?>
      </div>
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'ph-duotone ph-database'])->label('Ikon') ?>
        <small class="form-text text-muted">
          Lihat <a href="<?= $iconReferenceUrl ?>" target="_blank" rel="noopener">Referensi Ikon Phosphor</a>.
        </small>
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
      <?= Html::submitButton($model->isNewRecord ? 'Tambah Modul' : 'Update Modul', ['class' => $model->isNewRecord ? 'btn btn-success px-4' : 'btn btn-primary px-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
