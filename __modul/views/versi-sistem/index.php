<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Versi Sistem';
$this->params['active_menu'] = 'versi-sistem';

$isAdmin = Yii::$app->user->identity && (int) (Yii::$app->user->identity->id_user_level ?? Yii::$app->user->identity->level_user_id ?? 0) === 1;

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
  $swalJson = \yii\helpers\Json::encode($swal);
  $js = <<<JS
(function(){
  var opt = $swalJson;
  if (typeof Swal !== 'undefined') Swal.fire(opt);
})();
JS;
  $this->registerJs($js);
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">VERSI SISTEM</h5>
          <p>Informasi rilis dan log pembaruan Sistem Informasi Kesehatan</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="<?= Url::to(['/site/index']) ?>">
              <i class="ph-duotone ph-house"></i>
            </a>
          </li>
          <li class="breadcrumb-item">Versi Sistem</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Section 1: Ringkasan Integrasi Sistem -->
  <!-- <div class="col-md-12 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="fw-bold mb-3">Informasi Sistem Informasi</h5>
        <p class="text-dark">
          <strong>Versi terbaru sistem</strong> telah diluncurkan dengan peningkatan signifikan
          untuk mendukung layanan kesehatan yang lebih efisien dan akurat.
          Sistem ini telah diintegrasikan dengan <strong>API Daftarin</strong> dan
          <strong>Satu Sehat</strong>, memungkinkan sinkronisasi data pasien secara real-time.
        </p>

        <p class="text-dark">
          Antarmuka pengguna kini dirancang lebih intuitif dengan
          <strong>dashboard analitik interaktif</strong> yang membantu manajemen
          dalam pengambilan keputusan berbasis data.
          Fitur pelaporan juga lebih komprehensif dan otomatis menghasilkan
          laporan keuangan serta kinerja klinis sesuai standar Kementerian Kesehatan.
        </p>

        <p class="text-dark mb-0">
          Dari sisi keamanan, sistem telah dilengkapi dengan
          <strong>enkripsi end-to-end</strong> dan <strong>kontrol akses berbasis peran</strong>
          untuk melindungi data kesehatan yang bersifat sensitif.
        </p>
      </div>
    </div>
  </div> -->

  <!-- Section 2: Matriks Log Pembaruan Sistem (Changelog) -->
  <div class="col-md-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="mb-0 fw-bold">MATRIKS UPDATE LOG</h5>
          <p class="mb-0 text-muted">Daftar riwayat pembaruan sistem dan catatan rilis</p>
        </div>
        <?php if ($isAdmin): ?>
          <a href="<?= Url::to(['versi-sistem/create']) ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Log Versi
          </a>
        <?php endif; ?>
      </div>

      <div class="card-body table-border-style">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="text-white text-center" style="background-color: #1abc9c; border-color: #1abc9c;">
              <tr>
                <th width="5%">NO</th>
                <th width="15%">VERSI</th>
                <th width="20%">TANGGAL RILIS</th>
                <th>KETERANGAN / LOG UPDATE</th>
                <?php if ($isAdmin): ?>
                  <th width="12%">AKSI</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              $models = $dataProvider->getModels();
              if (empty($models)):
                ?>
                <tr>
                  <td colspan="<?= $isAdmin ? 5 : 4 ?>" class="text-center text-muted py-4">Data versi sistem belum
                    tersedia.</td>
                </tr>
              <?php
              else:
                $no = $dataProvider->pagination->offset + 1;
                foreach ($models as $model):
                  ?>
                  <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                      <span class="badge bg-light-primary text-primary fw-bold px-3 py-2 fs-6">
                        <?= Html::encode($model->versi) ?>
                      </span>
                    </td>
                    <td class="text-center fw-medium">
                      <?= date('d-M-Y', strtotime($model->tanggal)) ?>
                    </td>
                    <td class="text-dark">
                      <?= nl2br(Html::encode($model->keterangan)) ?>
                    </td>
                    <?php if ($isAdmin): ?>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          <?= Html::a('<i class="ti ti-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-xs btn-warning']) ?>
                          <?= Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-danger',
                            'data' => [
                              'confirm' => 'Apakah Anda yakin ingin menghapus log versi ini?',
                              'method' => 'post',
                            ]
                          ]) ?>
                        </div>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php
                endforeach;
              endif;
              ?>
            </tbody>
          </table>

          <div class="d-flex justify-content-end mt-3">
            <?= \yii\widgets\LinkPager::widget([
              'pagination' => $dataProvider->pagination,
              'options' => ['class' => 'pagination pagination-sm mb-0'],
              'linkContainerOptions' => ['class' => 'page-item'],
              'linkOptions' => ['class' => 'page-link'],
              'disabledPageCssClass' => 'disabled',
              'activePageCssClass' => 'active',
            ]) ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>