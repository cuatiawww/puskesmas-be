<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Master Ketenagaan (SDM)';
$this->params['active_menu'] = 'master-nakes';

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
          <h5 class="mb-0 fw-bold">MASTER KETENAGAAN (SDM)</h5>
          <p>Konfigurasi standar ketenagaan / nakes berdasarkan DLI & Permenkes.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">MASTER DATA</li>
          <li class="breadcrumb-item">SDM</li>
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
          <h5>Daftar Standar Profesi</h5>
          <p>Konfigurasi kelengkapan 9 jenis nakes (DLI 6.1) dan 11 jenis nakes.</p>
        </div>
        <a href="<?= Url::to(['master-nakes/create']) ?>" class="btn btn-sm btn-primary">
          <i class="ti ti-plus me-1"></i> Tambah Ketenagaan
        </a>
      </div>
      <div class="card-body table-border-style">
        <div class="table-responsive">
          <?php Pjax::begin(); ?>
          <?= GridView::widget([
            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} Ketenagaan',
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered align-middle'],
            'dataProvider' => $dataProvider,
            'columns' => [
              ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
              [
                'attribute' => 'nama_nakes',
                'header' => 'Nama Profesi / Ketenagaan',
              ],
              [
                'attribute' => 'kategori',
                'header' => 'Kategori Tenaga',
                'value' => function($model) {
                  return $model->kategori;
                }
              ],
              [
                'attribute' => 'is_dli_9',
                'header' => 'DLI 9 Jenis Nakes',
                'format' => 'raw',
                'value' => function($model) {
                  return $model->is_dli_9 ? '<span class="badge bg-success">Wajib</span>' : '<span class="text-muted">-</span>';
                }
              ],
              [
                'attribute' => 'is_dli_11',
                'header' => 'DLI 11 Jenis Nakes',
                'format' => 'raw',
                'value' => function($model) {
                  return $model->is_dli_11 ? '<span class="badge bg-info">Wajib</span>' : '<span class="text-muted">-</span>';
                }
              ],
              [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Aksi',
                'options' => ['style' => 'width: 100px; text-align: center;'],
                'template' => '{update} {delete}',
                'buttons' => [
                  'update' => function ($url, $model) {
                    return Html::a('<i class="ti ti-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-xs btn-warning me-1']);
                  },
                  'delete' => function ($url, $model) {
                    return Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], [
                      'class' => 'btn btn-xs btn-danger',
                      'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus data master ketenagaan ini?',
                        'method' => 'post',
                      ]
                    ]);
                  },
                ],
              ],
            ],
          ]); ?>
          <?php Pjax::end(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
