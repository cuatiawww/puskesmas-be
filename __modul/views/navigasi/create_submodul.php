<?php
use yii\helpers\Html;
$this->title = 'Tambah Sub-Modul';
$this->params['breadcrumbs'][] = ['label' => 'Navigasi', 'url' => ['index', 'tab' => 'submodul']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="navigasi-create-submodul">
    <h5 class="mb-3 fw-bold"><?= Html::encode($this->title) ?></h5>
    <?= $this->render('_form_submodul', ['model' => $model]) ?>
</div>
