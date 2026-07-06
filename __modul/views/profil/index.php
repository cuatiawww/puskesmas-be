<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Akun Saya';
$this->params['active_menu'] = 'profil';

$foto_profil = $model->foto_profil;
$foto_profil_url = !empty($foto_profil) && file_exists(Yii::getAlias('@app/../') . $foto_profil)
    ? Url::base() . '/' . ltrim($foto_profil, '/')
    : Url::base() . '/app_asset/images/user/avatar-1.jpg';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold text-uppercase">AKUN SAYA</h5>
          <p>Informasi detail profil akun Anda.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">BERANDA</li>
          <li class="breadcrumb-item">AKUN SAYA</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Informasi Akun</h5>
        <a href="<?= Url::to(['profil/update']) ?>" class="btn btn-warning btn-sm text-dark">
          <i class="ti ti-edit me-1"></i> Edit Profil
        </a>
      </div>
      <div class="card-body">
        <table class="table table-striped table-bordered align-middle">
          <tr>
            <th width="35%" class="fw-semibold text-dark">Nama Lengkap</th>
            <td><?= Html::encode($model->nama_lengkap) ?></td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">Username</th>
            <td><code><?= Html::encode($model->username) ?></code></td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">Email</th>
            <td><?= Html::encode($model->email) ?></td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">No. Telepon / WhatsApp</th>
            <td><?= Html::encode($model->no_telpon ?: '-') ?></td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">Jenis Kelamin</th>
            <td><?= Html::encode($model->jenis_kelamin ?: '-') ?></td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">Alamat</th>
            <td><?= Html::encode($model->alamat ?: '-') ?></td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">Level User</th>
            <td>
              <span class="badge bg-light-primary">
                <?= Html::encode($model->levelUser->nama_level ?? '-') ?>
              </span>
            </td>
          </tr>
          <tr>
            <th class="fw-semibold text-dark">Wilayah Kerja</th>
            <td><?= Html::encode($model->masterWilayah->nama_wilayah ?? '-') ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card text-center">
      <div class="card-header">
        <h5 class="mb-0 fw-bold">Foto & Keamanan</h5>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <img src="<?= htmlspecialchars($foto_profil_url) ?>" alt="Avatar" class="rounded-circle border border-2 shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
          <h5 class="mt-3 mb-0 fw-bold"><?= Html::encode($model->nama_lengkap) ?></h5>
          <span class="text-muted small"><?= Html::encode($model->levelUser->nama_level ?? '-') ?></span>
        </div>
        
        <div class="d-grid gap-2">
          <a href="<?= Url::to(['profil/update']) ?>" class="btn btn-primary btn-block btn-sm">
            <i class="ti ti-user me-1"></i> Edit Detail Profil
          </a>
          <a href="<?= Url::to(['profil/ubah-password']) ?>" class="btn text-white btn-block btn-sm" style="background-color: #1abc9c; border-color: #1abc9c;">
            <i class="ti ti-key me-1"></i> Ubah Password Keamanan
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
