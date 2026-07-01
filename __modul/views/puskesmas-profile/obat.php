<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $puskesmas app\models\PuskesmasProfile */
/* @var $kinerja app\models\PuskesmasKinerja */
/* @var $masterObat array */
/* @var $currentValues array */

$this->title = 'Ketersediaan Obat Esensial - ' . $puskesmas->nama_puskesmas;
$this->params['active_menu'] = 'puskesmas';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">KELOLA KETERSEDIAAN OBAT ESENSIAL</h5>
          <p>Puskesmas: <?= Html::encode($puskesmas->nama_puskesmas) ?> | Tahun: <?= $kinerja->tahun ?> (<?= $kinerja->periode_tipe ?>: <?= $kinerja->periode_nilai ?>)</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/kinerja', 'id' => $puskesmas->id]) ?>">RIWAYAT</a></li>
          <li class="breadcrumb-item">KETERSEDIAAN OBAT</li>
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
          <h5>Form Checklist Obat Esensial (SMILE)</h5>
          <p class="text-muted small">Centang obat yang tersedia/cukup dalam persediaan periode ini. Sistem akan otomatis menghitung total dan persentase ketersediaan obat esensial.</p>
        </div>
        <?= Html::a('Kembali', ['kinerja', 'id' => $puskesmas->id], ['class' => 'btn btn-sm btn-secondary']) ?>
      </div>
      <div class="card-body table-border-style">
        <?= Html::beginForm('', 'post') ?>
        
        <div class="row">
          <?php 
            // Chunk obat into columns
            $chunks = array_chunk($masterObat, ceil(count($masterObat) / 2));
          ?>
          <?php foreach ($chunks as $colIndex => $chunk): ?>
            <div class="col-md-6">
              <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                  <tr>
                    <th style="width: 50px; text-align: center;">NO</th>
                    <th>Nama Obat</th>
                    <th>Kategori</th>
                    <th style="width: 100px; text-align: center;">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($chunk as $index => $obat): ?>
                    <?php 
                      $oId = (int)$obat['id'];
                      $isAvailable = isset($currentValues[$oId]) ? $currentValues[$oId] : false;
                      // Global serial number
                      $serialNum = ($colIndex * count($chunks[0])) + $index + 1;
                    ?>
                    <tr>
                      <td style="text-align: center; font-weight: bold;"><?= $serialNum ?></td>
                      <td><strong><?= Html::encode($obat['nama_obat']) ?></strong></td>
                      <td><span class="badge bg-light text-dark"><?= Html::encode($obat['kategori']) ?></span></td>
                      <td style="text-align: center;">
                        <div class="form-check form-switch d-inline-block">
                          <input type="checkbox" name="obat_available[<?= $oId ?>]" value="1" <?= $isAvailable ? 'checked' : '' ?> class="form-check-input" style="cursor: pointer;">
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="text-end mt-4">
          <?= Html::a('Batal', ['kinerja', 'id' => $puskesmas->id], ['class' => 'btn btn-secondary me-2']) ?>
          <?= Html::submitButton('Simpan & Update Persentase', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= Html::endForm() ?>
      </div>
    </div>
  </div>
</div>
