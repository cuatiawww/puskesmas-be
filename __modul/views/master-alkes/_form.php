<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MasterAlkes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-alkes-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Formulir Master Alkes</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'nama_alkes')->textInput(['maxlength' => true, 'placeholder' => 'Contoh: Stetoskop Duplex, Autoclave']) ?>

            <?= $form->field($model, 'kategori')->dropDownList([
                'Peralatan Medis' => 'Peralatan Medis',
                'Sarana' => 'Sarana',
                'Prasarana' => 'Prasarana',
            ]) ?>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Master', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
