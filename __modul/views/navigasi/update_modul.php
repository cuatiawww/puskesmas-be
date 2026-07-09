<?php
use yii\helpers\Html;
$this->title = 'Update Modul: ' . $model->label;
$this->params['breadcrumbs'][] = ['label' => 'Navigasi', 'url' => ['index', 'tab' => 'modul']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="navigasi-update-modul">
    <h5 class="mb-3 fw-bold"><?= Html::encode($this->title) ?></h5>
    <?= $this->render('_form_modul', ['model' => $model]) ?>
</div>
