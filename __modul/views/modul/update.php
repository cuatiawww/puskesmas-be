<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ubah Navigasi';
$this->params['active_menu'] = 'modul';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">UBAH MODUL</h5>
          <p>Ubah data modul.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['modul/index']) ?>">Modul</a></li>
          <li class="breadcrumb-item">Ubah</li>
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
