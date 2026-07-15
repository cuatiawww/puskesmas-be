<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->params['active_menu'] = $activeMenu;
?>

<?php $form = ActiveForm::begin(); ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1"><?= Html::encode($pageTitle) ?></h5>
        <p class="mb-0 text-muted">Tentukan kecamatan induk untuk desa/kelurahan ini.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'bps_code')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'parent_code')->dropDownList($parentOptions, ['prompt' => 'Pilih Kecamatan']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'jumlah_penduduk')->input('number', ['min' => 0]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'luas_wilayah')->input('number', ['min' => 0]) ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>

<?php ActiveForm::end(); ?>
