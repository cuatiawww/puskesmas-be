<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Edit Profil';
$this->params['active_menu'] = 'profil';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold text-uppercase">EDIT PROFIL</h5>
          <p>Ubah detail informasi akun Anda.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">BERANDA</li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['profil/index']) ?>">AKUN SAYA</a></li>
          <li class="breadcrumb-item">EDIT PROFIL</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0 fw-bold">Form Edit Profil</h5>
      </div>
      <div class="card-body">
        
        <?php if ($model->hasErrors()): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Html::errorSummary($model) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?= Html::beginForm(['profil/update'], 'post', ['enctype' => 'multipart/form-data']) ?>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
              <?= Html::textInput('nama_lengkap', $model->nama_lengkap, [
                  'class' => 'form-control', 
                  'required' => true,
                  'placeholder' => 'Masukkan nama lengkap'
              ]) ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Username (Tidak dapat diubah)</label>
              <?= Html::textInput('username', $model->username, [
                  'class' => 'form-control bg-light', 
                  'disabled' => true,
              ]) ?>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Email <span class="text-danger">*</span></label>
              <?= Html::textInput('email', $model->email, [
                  'class' => 'form-control', 
                  'type' => 'email',
                  'required' => true,
                  'placeholder' => 'Masukkan alamat email'
              ]) ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">No. Telepon / WhatsApp</label>
              <?= Html::textInput('no_telpon', $model->no_telpon, [
                  'class' => 'form-control', 
                  'placeholder' => 'Masukkan nomor telepon'
              ]) ?>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Jenis Kelamin</label>
              <?= Html::dropDownList('jenis_kelamin', $model->jenis_kelamin, [
                  '' => '-- Pilih Jenis Kelamin --',
                  'Laki-laki' => 'Laki-laki',
                  'Perempuan' => 'Perempuan',
              ], ['class' => 'form-control']) ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Alamat</label>
              <?= Html::textarea('alamat', $model->alamat, [
                  'class' => 'form-control',
                  'rows' => 2,
                  'placeholder' => 'Masukkan alamat lengkap'
              ]) ?>
            </div>
          </div>

          <div class="row mb-4 align-items-center">
            <div class="col-md-8">
              <label class="form-label fw-semibold text-dark">Foto Profil Baru</label>
              <?= Html::fileInput('foto_profil', null, ['class' => 'form-control', 'accept' => 'image/*']) ?>
              <div class="form-text text-muted small">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
            </div>
            <div class="col-md-4 text-center">
              <?php 
              $foto_profil = $model->foto_profil;
              $foto_profil_url = !empty($foto_profil) && file_exists(Yii::getAlias('@app/../') . $foto_profil)
                  ? Url::base() . '/' . ltrim($foto_profil, '/')
                  : Url::base() . '/app_asset/images/user/avatar-1.jpg';
              ?>
              <img src="<?= htmlspecialchars($foto_profil_url) ?>" alt="Current Avatar" class="rounded-circle border mt-3" style="width: 60px; height: 60px; object-fit: cover;">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Level User</label>
              <input type="text" class="form-control bg-light" value="<?= Html::encode($model->levelUser->nama_level ?? '-') ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Wilayah Kerja</label>
              <input type="text" class="form-control bg-light" value="<?= Html::encode($model->masterWilayah->nama_wilayah ?? '-') ?>" disabled>
            </div>
          </div>

          <div class="text-end gap-2 mt-4">
            <a href="<?= Url::to(['profil/index']) ?>" class="btn btn-outline-secondary px-4 me-2">
              Batal
            </a>
            <button type="submit" class="btn text-white px-4" style="background-color: #1abc9c; border-color: #1abc9c;">
              <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
            </button>
          </div>

        <?= Html::endForm() ?>

      </div>
    </div>
  </div>
</div>
