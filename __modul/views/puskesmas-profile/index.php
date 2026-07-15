<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Tata Kelola Kinerja Puskesmas';
$this->params['active_menu'] = 'puskesmas';

$canCreate = Yii::$app->controller->canCreate();
$canUpdate = Yii::$app->controller->canUpdate();
$canDelete = Yii::$app->controller->canDelete();
$hasCrudAction = $canUpdate || $canDelete;

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = \yii\helpers\Json::encode($swal);
    $js = <<<JS
(function(){
  var opt = $swalJson;
  if(!opt.toast && opt.position === undefined) {
    opt.position = 'center';
  }
  if(!opt.toast && opt.timer === undefined) opt.timer = 3000;
  if(!opt.toast && opt.showConfirmButton === undefined) opt.showConfirmButton = opt.icon === 'error';
  if (typeof Swal !== 'undefined') {
    Swal.fire(opt);
  }
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
          <h5 class="mb-0 fw-bold">TATA KELOLA KINERJA PUSKESMAS</h5>
          <p>Manajemen Profil, Indikator Kesiapan (DLI 6.1), dan Penilaian Kinerja Puskesmas (PKP)</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">KINERJA PUSKESMAS</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5>DAFTAR PUSKESMAS</h5>
          <p>Daftar data profil faskes dan isi laporan kesiapan.</p>
        </div>
        <?php if ($canCreate): ?>
        <div class="d-flex gap-2">
          <a href="<?= Url::to(['puskesmas-profile/create']) ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Puskesmas
          </a>
        </div>
        <?php endif; ?>
      </div>
      <div class="card-body table-border-style">
        <div class="table-responsive">

          <?php Pjax::begin(); ?>

          <?php
          $gridColumns = [
            ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
            [
              'attribute' => 'kode_faskes',
              'header' => 'Kode Faskes',
              'options' => ['style' => 'width: 120px'],
            ],
            [
              'attribute' => 'nama_puskesmas',
              'header' => 'Nama Puskesmas',
              'format' => 'raw',
              'value' => function($model) {
                $photo = $model->foto_puskesmas;
                $photoUrl = !empty($photo) && file_exists(Yii::getAlias('@app/../') . $photo)
                    ? Url::base() . '/' . ltrim($photo, '/')
                    : null;
                
                $imgHtml = '';
                if ($photoUrl) {
                    $imgHtml = '<img src="' . htmlspecialchars($photoUrl) . '" class="rounded border me-2" style="width: 40px; height: 40px; object-fit: cover; vertical-align: middle;">';
                }
                
                return '<div class="d-flex align-items-center">' . $imgHtml . '<span><strong>' . Html::encode($model->nama_puskesmas) . '</strong></span></div>';
              }
            ],
            [
              'attribute' => 'kategori_wilayah',
              'header' => 'Kategori Wilayah',
              'value' => function($model) {
                return $model->kategori_wilayah ?: '-';
              }
            ],
            [
              'attribute' => 'status_pelayanan',
              'header' => 'Status Pelayanan',
              'format' => 'raw',
              'value' => function($model) {
                $class = $model->status_pelayanan === 'Rawat Inap' ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $class . '">' . ($model->status_pelayanan ?: 'Non Ranap') . '</span>';
              }
            ],
            [
              'header' => 'LOKASI',
              'format' => 'raw',
              'contentOptions' => ['style' => 'min-width:200px; vertical-align:middle;'],
              'value' => function($model) {
                $levelLabel = match(strtolower($model->level_wilayah ?? 'kabupaten')) {
                  'provinsi' => '<span style="font-size:0.75rem; font-weight:600; color:#e05c2d;">Provinsi</span>',
                  default    => '<span style="font-size:0.75rem; font-weight:600; color:#e05c2d;">Kab/Kota</span>',
                };
                $kabName  = $model->kabupaten ? strtoupper($model->kabupaten->name) : '-';
                $kecName  = $model->kecamatan ? ucwords(strtolower($model->kecamatan->name)) : '';
                $provName = $model->provinsi  ? ucwords(strtolower($model->provinsi->name))  : '';
                $html = $levelLabel . '<br><strong>' . Html::encode($kabName) . '</strong>';
                if ($kecName) {
                  $html .= '<br><small class="text-muted">' . Html::encode($kecName);
                  if ($provName) $html .= ', ' . Html::encode($provName);
                  $html .= '</small>';
                }
                return $html;
              }
            ],
            [
              'header' => 'FORMULIR',
              'format' => 'raw',
              'contentOptions' => ['style' => 'width: 150px; text-align: center; vertical-align: middle;'],
              'value' => function($model) use ($canCreate) {
                $kinerjaCreateUrl = \yii\helpers\Url::to(['kinerja-create', 'id' => $model->id]);
                $kinerjaListUrl   = \yii\helpers\Url::to(['kinerja',        'id' => $model->id]);
                $penyakitUrl      = \yii\helpers\Url::to(['penyakit',       'id' => $model->id]);
                
                $createBtn = '';
                if ($canCreate) {
                    $createBtn = '
                      <li>
                        <a class="dropdown-item" href="' . $kinerjaCreateUrl . '">
                          <i class="ti ti-file-plus me-2"></i>ISI LAPORAN KINERJA
                        </a>
                      </li>
                    ';
                }

                return '
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-clipboard-list me-1"></i>FORMULIR
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      ' . $createBtn . '
                      <li>
                        <a class="dropdown-item" href="' . $kinerjaListUrl . '">
                          <i class="ti ti-report-analytics me-2"></i>RIWAYAT KINERJA
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="' . $penyakitUrl . '">
                          <i class="ti ti-activity me-2"></i>DATA KASUS PENYAKIT
                        </a>
                      </li>
                    </ul>
                  </div>
                ';
              }
            ],
          ];

          if ($hasCrudAction) {
              $gridColumns[] = [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Aksi',
                'options' => ['style' => 'width: 120px; text-align: center;'],
                'template' => ($canUpdate ? '{update} ' : '') . ($canDelete ? '{delete}' : ''),
                'buttons' => [
                  'update' => function ($url, $model) {
                    return Html::a('<i class="ti ti-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning me-1', 'title' => 'Edit Profil']);
                  },
                  'delete' => function ($url, $model) {
                    return Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], [
                      'class' => 'btn btn-sm btn-danger',
                      'title' => 'Hapus Puskesmas',
                      'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus Puskesmas ini beserta semua data kinerjanya?',
                        'method' => 'post',
                      ]
                    ]);
                  },
                ],
              ];
          }
          ?>

          <?= GridView::widget([
            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} Puskesmas',
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered align-middle'],
            'dataProvider' => $dataProvider,
            'columns' => $gridColumns,
          ]); ?>

          <?php Pjax::end(); ?>

        </div>
      </div>
    </div>
  </div>
</div>

