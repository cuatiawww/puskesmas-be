<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MasterNakes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-nakes-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Formulir Master Ketenagaan</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'nama_nakes')->textInput(['maxlength' => true, 'placeholder' => 'Contoh: Dokter Umum, Perawat']) ?>

            <?= $form->field($model, 'kategori')->dropDownList([
                'Medis' => 'Medis',
                'Tenaga Kesehatan' => 'Tenaga Kesehatan',
                'Penunjang' => 'Penunjang',
            ]) ?>

            <div class="row mt-3">
                <div class="col-md-6">
                    <?= $form->field($model, 'is_dli_9')->checkbox() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'is_dli_11')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Master', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
