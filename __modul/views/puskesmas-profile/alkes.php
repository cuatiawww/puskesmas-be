<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $puskesmas app\models\PuskesmasProfile */
/* @var $kinerja app\models\PuskesmasKinerja */
/* @var $masterAlkes array */
/* @var $currentAvail array */
/* @var $currentBaik array */

$this->title = 'Ketersediaan Alkes & Sarpras - ' . $puskesmas->nama_puskesmas;
$this->params['active_menu'] = 'puskesmas';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">KELOLA KETERSEDIAAN ALAT KESEHATAN & SARPRAS</h5>
          <p>Puskesmas: <?= Html::encode($puskesmas->nama_puskesmas) ?> | Tahun: <?= $kinerja->tahun ?> (<?= $kinerja->periode_tipe ?>: <?= $kinerja->periode_nilai ?>)</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/kinerja', 'id' => $puskesmas->id]) ?>">RIWAYAT</a></li>
          <li class="breadcrumb-item">KETERSEDIAAN ALKES</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5>Form Checklist Alkes & Kondisi (ASPAK)</h5>
          <p class="text-muted small">Centang ketersediaan alat dan status kondisi baik. Persentase Alkes dihitung dari jumlah alat yang **Tersedia DAN berkondisi Baik** dibagi dengan total standar alat.</p>
        </div>
        <?= Html::a('Kembali', ['kinerja', 'id' => $puskesmas->id], ['class' => 'btn btn-sm btn-secondary']) ?>
      </div>
      <div class="card-body table-border-style">
        <?= Html::beginForm('', 'post') ?>
        
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
              <tr>
                <th style="width: 60px; text-align: center;">NO</th>
                <th>Nama Alat Kesehatan / Sarana Prasarana</th>
                <th style="width: 250px;">Kategori</th>
                <th style="width: 150px; text-align: center;">Tersedia?</th>
                <th style="width: 150px; text-align: center;">Kondisi Baik?</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($masterAlkes as $index => $alkes): ?>
                <?php 
                  $aId = (int)$alkes['id'];
                  $isAvail = isset($currentAvail[$aId]) ? $currentAvail[$aId] : false;
                  $isBaik = isset($currentBaik[$aId]) ? $currentBaik[$aId] : true;
                ?>
                <tr>
                  <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                  <td><strong><?= Html::encode($alkes['nama_alkes']) ?></strong></td>
                  <td><span class="badge bg-light text-dark"><?= Html::encode($alkes['kategori']) ?></span></td>
                  <td style="text-align: center;">
                    <div class="form-check form-switch d-inline-block">
                      <input type="checkbox" name="alkes_available[<?= $aId ?>]" value="1" <?= $isAvail ? 'checked' : '' ?> class="form-check-input" style="cursor: pointer;">
                    </div>
                  </td>
                  <td style="text-align: center;">
                    <div class="form-check form-switch d-inline-block">
                      <input type="checkbox" name="alkes_baik[<?= $aId ?>]" value="1" <?= $isBaik ? 'checked' : '' ?> class="form-check-input" style="cursor: pointer;">
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="text-end mt-3">
          <?= Html::a('Batal', ['kinerja', 'id' => $puskesmas->id], ['class' => 'btn btn-secondary me-2']) ?>
          <?= Html::submitButton('Simpan & Update Persentase', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= Html::endForm() ?>
      </div>
    </div>
  </div>
</div>
