<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $puskesmas app\models\PuskesmasProfile */
/* @var $kinerja app\models\PuskesmasKinerja */
/* @var $masterNakes array */
/* @var $currentValues array */

$this->title = 'Detail Ketenagaan SDM - ' . $puskesmas->nama_puskesmas;
$this->params['active_menu'] = 'puskesmas';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">KELOLA DETAIL SDM KESEHATAN</h5>
          <p>Puskesmas: <?= Html::encode($puskesmas->nama_puskesmas) ?> | Tahun: <?= $kinerja->tahun ?> (<?= $kinerja->periode_tipe ?>: <?= $kinerja->periode_nilai ?>)</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/kinerja', 'id' => $puskesmas->id]) ?>">RIWAYAT</a></li>
          <li class="breadcrumb-item">DETAIL SDM</li>
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
          <h5>Form Rincian Jumlah SDM Kesehatan</h5>
          <p class="text-muted small">Input jumlah riil personil untuk masing-masing standar profesi nakes di Puskesmas. Sistem akan menghitung otomatis kelayakan DLI 6.1 (9 Jenis Nakes & 11 Jenis Nakes lengkap).</p>
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
                <th>Standard Profesi Kesehatan</th>
                <th style="width: 200px;">Kategori</th>
                <th style="width: 120px; text-align: center;">DLI 9</th>
                <th style="width: 120px; text-align: center;">DLI 11</th>
                <th style="width: 200px; text-align: center;">Jumlah Personil (Riil)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($masterNakes as $index => $nakes): ?>
                <?php 
                  $nId = (int)$nakes['id'];
                  $qty = isset($currentValues[$nId]) ? $currentValues[$nId] : 0;
                  $isDli9 = filter_var($nakes['is_dli_9'], FILTER_VALIDATE_BOOLEAN);
                  $isDli11 = filter_var($nakes['is_dli_11'], FILTER_VALIDATE_BOOLEAN);
                ?>
                <tr>
                  <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                  <td><strong><?= Html::encode($nakes['nama_nakes']) ?></strong></td>
                  <td><span class="badge bg-light text-dark"><?= Html::encode($nakes['kategori']) ?></span></td>
                  <td style="text-align: center;">
                    <?= $isDli9 ? '<span class="badge bg-success"><i class="ti ti-check"></i> Wajib</span>' : '<span class="text-muted">-</span>' ?>
                  </td>
                  <td style="text-align: center;">
                    <?= $isDli11 ? '<span class="badge bg-info"><i class="ti ti-check"></i> Wajib</span>' : '<span class="text-muted">-</span>' ?>
                  </td>
                  <td style="text-align: center;">
                    <input type="number" name="nakes_qty[<?= $nId ?>]" value="<?= $qty ?>" min="0" class="form-control text-center mx-auto" style="width: 120px;" required>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="text-end mt-3">
          <?= Html::a('Batal', ['kinerja', 'id' => $puskesmas->id], ['class' => 'btn btn-secondary me-2']) ?>
          <?= Html::submitButton('Simpan Data & Hitung Otomatis', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= Html::endForm() ?>
      </div>
    </div>
  </div>
</div>
