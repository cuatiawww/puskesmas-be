<?php
use yii\helpers\Html;
$this->title = 'Update Sub-Modul: ' . $model->label;
$this->params['breadcrumbs'][] = ['label' => 'Navigasi', 'url' => ['index', 'tab' => 'submodul']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="navigasi-update-submodul">
    <h5 class="mb-3 fw-bold"><?= Html::encode($this->title) ?></h5>
    <?= $this->render('_form_submodul', ['model' => $model]) ?>
</div>
