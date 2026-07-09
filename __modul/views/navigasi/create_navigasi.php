<?php
use yii\helpers\Html;
$this->title = 'Tambah Kategori Navigasi';
$this->params['breadcrumbs'][] = ['label' => 'Navigasi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="navigasi-create-navigasi">
    <h5 class="mb-3 fw-bold"><?= Html::encode($this->title) ?></h5>
    <?= $this->render('_form_navigasi', ['model' => $model]) ?>
</div>
