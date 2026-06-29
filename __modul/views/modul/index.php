<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Manajemen Modul';
$this->params['active_menu'] = 'modul';

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = \yii\helpers\Json::encode($swal);
    $js = <<<JS
(function(){
  var opt = $swalJson;
  if(!opt.toast && opt.position === undefined) {
    if (opt.icon === 'success' || (opt.title && /berhasil/i.test(opt.title))) {
      opt.position = 'center';
    } else {
      opt.position = 'top-end';
    }
  }
  if(!opt.toast && opt.timer === undefined) opt.timer = 2500;
  if(!opt.toast && opt.showConfirmButton === undefined) opt.showConfirmButton = false;
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
          <h5 class="mb-0 fw-bold">KONFIGURASI MODUL</h5>
          <p>Tatakelola Pengaturan Modul dalam Navigasi</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">MODUL</li>
        </ul>
      </div>
      <div style="margin-top: 10px;" class="d-flex">
        <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
          <i class="ti ti-external-link me-1"></i> Info Selengkapnya
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5>DAFTAR MODUL</h5>
          <p>Daftar modul yang tersedia di dalam navigasi.</p>
        </div>
        <a href="<?= Url::to(['modul/create']) ?>" class="btn btn-sm btn-primary">
          <i class="ti ti-plus me-1"></i> Tambah Modul
        </a>
      </div>
      <div class="card-body table-border-style">
        <div class="table-responsive">

          <?php Pjax::begin(); ?>

          <?= GridView::widget([
            'summary' => '',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
              ['class' => 'yii\\grid\\SerialColumn', 'header' => 'NO'],
              [
                'attribute' => 'id',
                'header' => 'ID',
                'options' => ['style' => 'width: 50px'],
              ],
              [
                'attribute' => 'modul_id',
                'header' => 'Navigasi',
                'value' => function($model) {
                  return $model->modul ? $model->modul->label : '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(\app\models\Modul::find()->orderBy('urutan')->all(), 'id', 'label'),
              ],
              [
                'attribute' => 'nama_sub_modul',
                'header' => 'Nama Modul',
              ],
              [
                'attribute' => 'label',
                'header' => 'Label',
              ],
              [
                'attribute' => 'route',
                'header' => 'Route',
              ],
              [
                'attribute' => 'urutan',
                'header' => 'Urutan',
                'options' => ['style' => 'width: 80px'],
              ],
              [
                'attribute' => 'is_active',
                'header' => 'Status',
                'format' => 'raw',
                'filter' => [1 => 'Active', 0 => 'Inactive'],
                'value' => function($model) {
                  $active = is_array($model) ? ($model['is_active'] ?? null) : ($model->is_active ?? null);
                  return $active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                }
              ],
              [
                'class' => 'yii\\grid\\ActionColumn',
                'header' => 'Aksi',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                  'view' => function ($url, $model) {
                    return Html::a('<i class="ti ti-eye"></i>', $url, ['class' => 'btn btn-sm btn-info', 'title' => 'View']);
                  },
                  'update' => function ($url, $model) {
                    return Html::a('<i class="ti ti-edit"></i>', $url, ['class' => 'btn btn-sm btn-warning', 'title' => 'Edit']);
                  },
                  'delete' => function ($url, $model) {
                    return Html::a('<i class="ti ti-trash"></i>', $url, ['class' => 'btn btn-sm btn-danger', 'data-confirm' => 'Are you sure?', 'data-method' => 'post', 'title' => 'Delete']);
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