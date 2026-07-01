<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MasterObat */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-obat-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Formulir Master Obat</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'nama_obat')->textInput(['maxlength' => true, 'placeholder' => 'Contoh: Amoxicillin 500mg, Paracetamol']) ?>

            <?= $form->field($model, 'kategori')->textInput(['maxlength' => true, 'placeholder' => 'Contoh: Antibiotik, Analgesik']) ?>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Master', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
