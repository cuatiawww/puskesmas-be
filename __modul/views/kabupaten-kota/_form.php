<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->params['active_menu'] = $activeMenu;
?>

<?php $form = ActiveForm::begin(); ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1"><?= Html::encode($pageTitle) ?></h5>
        <p class="mb-0 text-muted">Tentukan provinsi induk untuk kabupaten/kota ini.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <?= $form->field($model, 'tbl_wilayah_id')->input('number', ['min' => 1]) ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'nama_wilayah')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'parent_master_wilayah_id')->dropDownList($parentOptions, ['prompt' => 'Pilih Provinsi']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'status_aktif')->dropDownList([1 => 'Aktif', 0 => 'Nonaktif']) ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'keterangan')->textarea(['rows' => 4]) ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>

<?php ActiveForm::end(); ?>
