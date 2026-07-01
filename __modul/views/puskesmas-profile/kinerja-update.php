<?php

use yii\helpers\Url;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PuskesmasKinerja */
/* @var $puskesmas app\models\PuskesmasProfile */

$this->title = 'Edit Laporan Periodik';
$this->params['active_menu'] = 'puskesmas';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">FORMULIR LAPORAN KINERJA PUSKESMAS</h5>
          <p><?= Html::encode($puskesmas->nama_puskesmas) ?> &mdash; Tahun <?= $model->tahun ?> | <?= $model->periode_tipe === 'Kuartal' ? 'Kuartal ' . $model->periode_nilai : $model->periode_tipe ?></p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/kinerja', 'id' => $puskesmas->id]) ?>">RIWAYAT LAPORAN</a></li>
          <li class="breadcrumb-item">FORMULIR</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $this->render('_kinerja_form', [
            'model' => $model,
            'puskesmas' => $puskesmas,
        ]) ?>
    </div>
</div>
