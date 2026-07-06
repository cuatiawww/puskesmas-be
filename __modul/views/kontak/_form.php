<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Kontak */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="kontak-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="fw-bold mb-0">Formulir Kontak</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'nama_kontak')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan nama kontak (contoh: R. Andry Noviandi H, S.Kom)'
            ]) ?>

            <?= $form->field($model, 'jabatan')->textInput([
                'maxlength' => true,
                'placeholder' => 'Masukkan jabatan/unit (contoh: Tim Siskohatkes)'
            ]) ?>

            <?= $form->field($model, 'email')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan alamat email (contoh: helpdesk@domain.com)'
            ]) ?>

            <?= $form->field($model, 'whatsapp')->textInput([
                'maxlength' => true, 
                'placeholder' => 'Masukkan nomor WhatsApp (contoh: 08123456789 atau +628123456789)'
            ])->hint('Gunakan format nomor lokal (08x) atau internasional (+628x).') ?>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Kontak', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
