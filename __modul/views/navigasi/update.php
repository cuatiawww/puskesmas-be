<?php

use yii\helpers\Html;

$this->title = 'Edit Navigasi: ' . $model->label;
$this->params['active_menu'] = 'navigasi';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">EDIT NAVIGASI</h5>
          <p>Form untuk mengubah data modul/navigasi</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">NAVIGASI</a></li>
          <li class="breadcrumb-item" aria-current="page">Edit</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <?= $this->render('_form', ['model' => $model]) ?>
  </div>
</div>
