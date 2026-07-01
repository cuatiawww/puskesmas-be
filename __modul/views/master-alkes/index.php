<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Master Alat Kesehatan (Alkes)';
$this->params['active_menu'] = 'master-alkes';

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
          <h5 class="mb-0 fw-bold">MASTER ALAT KESEHATAN (ALKES)</h5>
          <p>Konfigurasi daftar standar sarana prasarana alkes nasional Puskesmas.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">MASTER DATA</li>
          <li class="breadcrumb-item">ALKES</li>
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
          <h5>Daftar Alkes & Sarpras</h5>
          <p>Daftar alkes acuan untuk monitoring pemenuhan target alkes (by ASPAK).</p>
        </div>
        <a href="<?= Url::to(['master-alkes/create']) ?>" class="btn btn-sm btn-primary">
          <i class="ti ti-plus me-1"></i> Tambah Alkes
        </a>
      </div>
      <div class="card-body table-border-style">
        <div class="table-responsive">
          <?php Pjax::begin(); ?>
          <?= GridView::widget([
            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} Alkes',
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered align-middle'],
            'dataProvider' => $dataProvider,
            'columns' => [
              ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
              [
                'attribute' => 'nama_alkes',
                'header' => 'Nama Alkes / Sarpras',
              ],
              [
                'attribute' => 'kategori',
                'header' => 'Kategori Alkes',
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
                        'confirm' => 'Apakah Anda yakin ingin menghapus data master alkes ini?',
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
