<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tambah Navigasi';
$this->params['active_menu'] = 'modul';
$role_short_name = Yii::$app->user->identity->level_user_id ?? (Yii::$app->user->identity->username ?? '');
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 mb-0 font-weight-600 fw-bold">TAMBAH MODUL</h5>
            <p>Form tambah modul</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['modul/index']) ?>">Modul</a></li>
          <li class="breadcrumb-item">Tambah Modul</li>
        </ul>
      </div>
      <div style="margin-top: -5px;">
            <a class="btn btn-sm btn-light-primary rounded-pill px-2" role="button" href="javascript:void(0);">
              <i class="ti ti-external-link me-1"></i>
              Info Selengkapnya
            </a>
          </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <?= $this->render('_form', ['model' => $model]) ?>
  </div>
</div>
