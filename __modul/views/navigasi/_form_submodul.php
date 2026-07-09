<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Modul;
use app\models\SubModul;
use yii\helpers\ArrayHelper;

$iconReferenceUrl = 'https://html.phoenixcoded.net/flatable/elements/icon-phosphor.html';
?>

<div class="submodul-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form-submodul-ajax',
        'enableAjaxValidation' => false,
    ]); ?>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'modul_id')->dropDownList(
            ArrayHelper::map(Modul::find()->where(['is_active' => true])->orderBy('urutan')->all(), 'id', 'label'),
            [
                'prompt' => '-- Pilih Navigasi --',
                'id' => 'modul-dropdown-submodul-form',
                'class' => 'form-select',
                'required' => true,
            ]
        )->label('Navigasi') ?>
        <small class="form-text text-muted">Pilih navigasi kategori terlebih dahulu.</small>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label font-weight-bold">Modul Parent</label>
        <div class="input-group">
          <?= $form->field($model, 'parent_id', [
              'options' => ['class' => 'flex-grow-1'],
          ])->dropDownList(
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
                  'id' => 'parent-modul-dropdown-inline',
                  'class' => 'form-select',
                  'required' => true,
              ]
          )->label(false) ?>
          <button type="button" class="btn btn-primary" id="btn-add-modul-inline" title="Tambah Modul Baru Inline">
            <i class="ti ti-plus"></i>
          </button>
        </div>
        <small class="form-text text-muted">Pilih modul parent atau klik <strong>+</strong> jika modul belum dibuat.</small>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'nama_sub_modul')->textInput(['maxlength' => true, 'placeholder' => 'user-activity'])->label('Nama Sub-Modul') ?>
      </div>
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'LOG USER AKTIVITAS'])->label('Label Sub-Modul') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'route')->textInput(['maxlength' => true, 'placeholder' => 'user-activity/index'])->label('Route') ?>
      </div>
      <div class="col-md-6 mb-3">
        <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'ph-duotone ph-android-logo'])->label('Ikon') ?>
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
      <?= Html::submitButton($model->isNewRecord ? 'Tambah Sub-Modul' : 'Update Sub-Modul', ['class' => $model->isNewRecord ? 'btn btn-success px-4' : 'btn btn-primary px-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
(function($) {
    function loadSubModul(modulId) {
      if (!modulId) {
        $('#parent-modul-dropdown-inline').html('<option value="">-- Pilih Modul --</option>');
        $('#parent-modul-dropdown-inline').prop('disabled', true);
        return;
      }
      
      $.ajax({
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
          $('#parent-modul-dropdown-inline').html(html);
          $('#parent-modul-dropdown-inline').prop('disabled', false);
        },
        error: function() {
          $('#parent-modul-dropdown-inline').html('<option value="">-- Error loading data --</option>');
          $('#parent-modul-dropdown-inline').prop('disabled', true);
        }
      });
    }

    $('#modul-dropdown-submodul-form').on('change', function() {
        loadSubModul($(this).val());
    });

    $(document).ready(function() {
      var modulId = $('#modul-dropdown-submodul-form').val();
      $('#parent-modul-dropdown-inline').prop('disabled', !modulId);
      if (modulId) {
        loadSubModul(modulId);
      }
    });
})(jQuery);
</script>
