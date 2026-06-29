<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Konfigurasi Navigasi';
$this->params['active_menu'] = 'navigasi';

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
          <h5 class="mb-0 fw-bold">KONFIGURASI NAVIGASI</h5>
          <p>Tatakelola Pengaturan Section Menu Sistem</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">NAVIGASI</li>
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
          <h5>DAFTAR NAVIGASI</h5>
          <p>Daftar modul/navigasi yang tersedia.</p>
        </div>
        <a href="<?= Url::to(['navigasi/create']) ?>" class="btn btn-sm btn-primary">
          <i class="ti ti-plus me-1"></i> Tambah Navigasi
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
                'attribute' => 'nama_modul',
                'header' => 'Nama Navigasi',
              ],
              [
                'attribute' => 'label',
                'header' => 'Label',
              ],
              [
                'attribute' => 'deskripsi',
                'header' => 'Deskripsi',
                'value' => function($model) {
                  return substr($model->deskripsi ?? '', 0, 50) . (strlen($model->deskripsi ?? '') > 50 ? '...' : '');
                }
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
                  return $model->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                }
              ],
              [
                'class' => 'yii\\grid\\ActionColumn',
                'header' => 'Aksi',
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
