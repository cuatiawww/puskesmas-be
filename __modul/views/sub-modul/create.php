<?php

use yii\helpers\Html;

$this->title = 'Tambah Sub-Modul';
$this->params['active_menu'] = 'sub-modul';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">TAMBAH SUB-MODUL</h5>
          <p>Form untuk menambah sub-modul baru</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">SUB-MODUL</a></li>
          <li class="breadcrumb-item" aria-current="page">Tambah</li>
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
