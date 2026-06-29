<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detail Navigasi';
$this->params['active_menu'] = 'modul';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">DETAIL MODUL</h5>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['modul/index']) ?>">Modul</a></li>
          <li class="breadcrumb-item">Detail</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h5>Informasi Modul</h5>
        <div>
          <?= Html::a('Ubah', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning']) ?>
          <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-borderless">
          <tr><th style="width:180px">Nama Modul</th><td><?= Html::encode($model->nama_modul) ?></td></tr>
          <tr><th>Label</th><td><?= Html::encode($model->label) ?></td></tr>
          <tr><th>Deskripsi</th><td><?= Html::encode($model->deskripsi) ?></td></tr>
          <tr><th>Icon</th><td><?= Html::encode($model->icon) ?></td></tr>
          <tr><th>Urutan</th><td><?= Html::encode($model->urutan) ?></td></tr>
          <tr><th>Status</th><td><?= $model->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak Aktif</span>' ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>