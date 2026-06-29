<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detail User: ' . $model->username;
$this->params['active_menu'] = 'data-user';

?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 font-weight-600 fw-bold">DETAIL USER</h5>
        </div>
        <p>Informasi user: <?= Html::encode($model->username) ?></p>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="#">AKSES</a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['user-model/index']) ?>">DATA USER</a></li>
          <li class="breadcrumb-item" aria-current="page">DETAIL</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Informasi User</h5>
        <div class="gap-2">
          <a href="<?= Url::to(['user-model/update', 'id' => $model->getId()]) ?>" class="btn btn-warning btn-sm">
            <i class="ti ti-edit me-1"></i> Edit
          </a>
          <a href="<?= Url::to(['user-model/index']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-striped">
          <tr>
            <th width="30%">Username</th>
            <td><?= Html::encode($model->username) ?></td>
          </tr>
          <tr>
            <th>Email</th>
            <td><?= Html::encode($model->email) ?></td>
          </tr>
          <tr>
            <th>Master Wilayah</th>
            <td><?= Html::encode($model->masterWilayah->nama_wilayah ?? '-') ?></td>
          </tr>
          <tr>
            <th>Level User</th>
            <td>
              <span class="badge bg-light-primary">
                <?= Html::encode($model->levelUser->nama_level ?? '-') ?>
              </span>
            </td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <?php if ($model->is_active): ?>
                <span class="badge bg-light-success">Aktif</span>
              <?php else: ?>
                <span class="badge bg-light-danger">Nonaktif</span>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Aksi</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="<?= Url::to(['user-model/update', 'id' => $model->getId()]) ?>" class="btn btn-warning">
            <i class="ti ti-edit me-1"></i> Edit User
          </a>
          <a href="<?= Url::to(['user-model/delete', 'id' => $model->getId()]) ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus user ini?');">
            <i class="ti ti-trash me-1"></i> Hapus User
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
