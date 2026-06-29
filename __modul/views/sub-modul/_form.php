<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Modul;
use app\models\SubModul;
use yii\helpers\ArrayHelper;

$iconReferenceUrl = 'https://html.phoenixcoded.net/flatable/elements/icon-phosphor.html';

?>

<div class="card">
  <div class="card-body">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'modul_id')->dropDownList(
          ArrayHelper::map(Modul::find()->where(['is_active' => true])->orderBy('urutan')->all(), 'id', 'label'),
          [
            'prompt' => '-- Pilih Navigasi --',
            'id' => 'modul-id',
            'onchange' => 'loadSubModul(this.value);'
          ]
        )->label('Navigasi') ?>
        <small class="form-text text-muted">Pilih navigasi terlebih dahulu untuk menampilkan daftar modul yang tersedia.</small>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'parent_id')->dropDownList(
          $model->parent_id ? 
            ArrayHelper::map(
              SubModul::find()
                ->where(['modul_id' => $model->modul_id, 'is_active' => true, 'parent_id' => null])
                ->orderBy('urutan')
                ->all(), 
              'id', 
              'label'
            ) : [],
          [
            'prompt' => '-- Pilih Modul --',
            'id' => 'parent_id'
          ]
        )->label('Modul') ?>
        <small class="form-text text-muted">Modul mengikuti navigasi yang dipilih.</small>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'nama_sub_modul')->textInput(['maxlength' => true, 'placeholder' => 'konfigurasi-sub-modul'])->label('Nama Sub-Modul') ?>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Konfigurasi Sub Modul'])->label('Label Sub-Modul') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'route')->textInput(['maxlength' => true, 'placeholder' => '/sub-modul/index'])->label('Route') ?>
      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'ph-duotone ph-squares-four'])->label('Ikon') ?>
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
          <?= Html::submitButton($model->isNewRecord ? 'Buat Sub-Modul' : 'Update Sub-Modul', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
          <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
      </div>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>

<script>
function loadSubModul(modulId) {
  if (!modulId) {
    jQuery('#parent_id').html('<option value="">-- Pilih Modul --</option>');
    jQuery('#parent_id').prop('disabled', true);
    return;
  }
  
  jQuery.ajax({
    url: '<?= Url::to(['get-sub-modul-by-modul']) ?>',
    type: 'GET',
    data: {modul_id: modulId},
    dataType: 'json',
    success: function(data) {
      var html = '<option value="">-- Pilih Modul --</option>';
      for (var id in data) {
        if (data.hasOwnProperty(id)) {
          var selected = (id == <?= json_encode($model->parent_id) ?>) ? 'selected' : '';
          html += '<option value="' + id + '" ' + selected + '>' + data[id] + '</option>';
        }
      }
      jQuery('#parent_id').html(html);
      jQuery('#parent_id').prop('disabled', false);
    },
    error: function() {
      jQuery('#parent_id').html('<option value="">-- Error loading data --</option>');
      jQuery('#parent_id').prop('disabled', true);
    }
  });
}

// Load on page init if modul_id is set
jQuery(document).ready(function() {
  var modulId = jQuery('#modul-id').val();
  jQuery('#parent_id').prop('disabled', !modulId);
  if (modulId) {
    loadSubModul(modulId);
  }
});
</script>
