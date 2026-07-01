<?php

use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\PuskesmasProfile */
/* @var $wilayahService app\services\WilayahService */

$this->title = 'Update Profil: ' . $model->nama_puskesmas;
$this->params['active_menu'] = 'puskesmas';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">EDIT PROFIL PUSKESMAS</h5>
          <p>Ubah detail registrasi dan informasi operasional Puskesmas.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item">EDIT PROFIL</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', [
            'model' => $model,
            'wilayahService' => $wilayahService,
        ]) ?>
    </div>
</div>
