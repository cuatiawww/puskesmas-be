<?php
use yii\helpers\Html;
$this->title = 'Update Kategori Navigasi: ' . $model->label;
$this->params['breadcrumbs'][] = ['label' => 'Navigasi', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="navigasi-update-navigasi">
    <h5 class="mb-3 fw-bold"><?= Html::encode($this->title) ?></h5>
    <?= $this->render('_form_navigasi', ['model' => $model]) ?>
</div>
