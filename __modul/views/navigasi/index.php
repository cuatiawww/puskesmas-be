<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Manajemen Navigasi & Modul';
$this->params['active_menu'] = 'navigasi';

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = \yii\helpers\Json::encode($swal);
    $js = <<<JS
(function(){
  var opt = $swalJson;
  if(!opt.toast && opt.position === undefined) {
    opt.position = 'center';
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
          <h5 class="mb-0 fw-bold">MANAJEMEN NAVIGASI &amp; MODUL</h5>
          <p>Tatakelola Pengaturan Navigasi Kategori, Modul, dan Sub-Modul Sistem Secara Terpadu</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">NAVIGASI &amp; MODUL</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header p-0">
        <ul class="nav nav-tabs" id="navigasiTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active py-3 px-4" id="navigasi-tab" data-bs-toggle="tab" data-bs-target="#tab-pane-navigasi" type="button" role="tab" aria-controls="tab-pane-navigasi" aria-selected="true">
              <i class="ph-duotone ph-navigation-arrow me-2 align-middle fs-5"></i> 1. Kategori Navigasi
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-3 px-4" id="modul-tab" data-bs-toggle="tab" data-bs-target="#tab-pane-modul" type="button" role="tab" aria-controls="tab-pane-modul" aria-selected="false">
              <i class="ph-duotone ph-squares-four me-2 align-middle fs-5"></i> 2. Modul
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-3 px-4" id="submodul-tab" data-bs-toggle="tab" data-bs-target="#tab-pane-submodul" type="button" role="tab" aria-controls="tab-pane-submodul" aria-selected="false">
              <i class="ph-duotone ph-list me-2 align-middle fs-5"></i> 3. Sub-Modul
            </button>
          </li>
        </ul>
      </div>
      
      <div class="card-body">
        <div class="tab-content" id="navigasiTabContent">
          
          <!-- Tab 1: Kategori Navigasi -->
          <div class="tab-pane fade show active" id="tab-pane-navigasi" role="tabpanel" aria-labelledby="navigasi-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h6 class="mb-0 fw-bold">Daftar Kategori Navigasi</h6>
                <span class="text-muted small">Menu pengelompokan utama di sidebar admin (modul table)</span>
              </div>
              <a href="<?= Url::to(['/navigasi/create-navigasi']) ?>" class="btn btn-sm btn-primary btn-modal-trigger" data-title="Tambah Kategori Navigasi Baru">
                <i class="ti ti-plus me-1"></i> Tambah Kategori
              </a>
            </div>
            
            <div class="table-responsive table-border-style">
              <?php Pjax::begin(['id' => 'pjax-navigasi']); ?>
              <?= GridView::widget([
                'summary' => '',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'dataProvider' => $providerNavigasi,
                'filterModel' => $searchNavigasi,
                'columns' => [
                  ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
                  [
                    'attribute' => 'id',
                    'header' => 'ID',
                    'options' => ['style' => 'width: 60px'],
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
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Aksi',
                    'options' => ['style' => 'width: 100px'],
                    'buttons' => [
                      'view' => function ($url, $model) { return false; },
                      'update' => function ($url, $model) {
                        return Html::a('<i class="ti ti-edit"></i>', ['update-navigasi', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-warning btn-modal-trigger', 
                            'title' => 'Edit',
                            'data-title' => 'Edit Kategori Navigasi'
                        ]);
                      },
                      'delete' => function ($url, $model) {
                        return Html::a('<i class="ti ti-trash"></i>', ['delete-navigasi', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger', 
                            'data-confirm' => 'Hapus kategori navigasi ini?', 
                            'data-method' => 'post', 
                            'title' => 'Hapus'
                        ]);
                      },
                    ],
                  ],
                ],
              ]); ?>
              <?php Pjax::end(); ?>
            </div>
          </div>

          <!-- Tab 2: Modul -->
          <div class="tab-pane fade" id="tab-pane-modul" role="tabpanel" aria-labelledby="modul-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h6 class="mb-0 fw-bold">Daftar Modul Utama</h6>
                <span class="text-muted small">Modul level 1 di bawah navigasi (sub_modul table, parent_id is null)</span>
              </div>
              <a href="<?= Url::to(['/navigasi/create-modul']) ?>" class="btn btn-sm btn-primary btn-modal-trigger" data-title="Tambah Modul Baru">
                <i class="ti ti-plus me-1"></i> Tambah Modul
              </a>
            </div>
            
            <div class="table-responsive table-border-style">
              <?php Pjax::begin(['id' => 'pjax-modul']); ?>
              <?= GridView::widget([
                'summary' => '',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'dataProvider' => $providerModul,
                'filterModel' => $searchModul,
                'columns' => [
                  ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
                  [
                    'attribute' => 'id',
                    'header' => 'ID',
                    'options' => ['style' => 'width: 60px'],
                  ],
                  [
                    'attribute' => 'modul_id',
                    'header' => 'Navigasi Parent',
                    'value' => 'modul.label',
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
                    'attribute' => 'icon',
                    'header' => 'Ikon',
                    'format' => 'raw',
                    'value' => function($model) {
                      return $model->icon ? '<i class="' . Html::encode($model->icon) . ' fs-5"></i> <small class="text-muted ms-1">' . Html::encode($model->icon) . '</small>' : '-';
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
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Aksi',
                    'options' => ['style' => 'width: 100px'],
                    'buttons' => [
                      'view' => function ($url, $model) { return false; },
                      'update' => function ($url, $model) {
                        return Html::a('<i class="ti ti-edit"></i>', ['update-modul', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-warning btn-modal-trigger', 
                            'title' => 'Edit',
                            'data-title' => 'Edit Modul'
                        ]);
                      },
                      'delete' => function ($url, $model) {
                        return Html::a('<i class="ti ti-trash"></i>', ['delete-modul', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger', 
                            'data-confirm' => 'Hapus modul ini?', 
                            'data-method' => 'post', 
                            'title' => 'Hapus'
                        ]);
                      },
                    ],
                  ],
                ],
              ]); ?>
              <?php Pjax::end(); ?>
            </div>
          </div>

          <!-- Tab 3: Sub-Modul -->
          <div class="tab-pane fade" id="tab-pane-submodul" role="tabpanel" aria-labelledby="submodul-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h6 class="mb-0 fw-bold">Daftar Sub-Modul Utama</h6>
                <span class="text-muted small">Menu level 2 di bawah modul (sub_modul table, parent_id is not null)</span>
              </div>
              <a href="<?= Url::to(['/navigasi/create-submodul']) ?>" class="btn btn-sm btn-primary btn-modal-trigger" data-title="Tambah Sub-Modul Baru">
                <i class="ti ti-plus me-1"></i> Tambah Sub-Modul
              </a>
            </div>
            
            <div class="table-responsive table-border-style">
              <?php Pjax::begin(['id' => 'pjax-submodul']); ?>
              <?= GridView::widget([
                'summary' => '',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'dataProvider' => $providerSubSubModul ?? $providerSubModul,
                'filterModel' => $searchSubModul,
                'columns' => [
                  ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
                  [
                    'attribute' => 'id',
                    'header' => 'ID',
                    'options' => ['style' => 'width: 60px'],
                  ],
                  [
                    'attribute' => 'modul_id',
                    'header' => 'Navigasi',
                    'value' => 'modul.label',
                  ],
                  [
                    'attribute' => 'parent_id',
                    'header' => 'Modul Parent',
                    'value' => 'parent.label',
                  ],
                  [
                    'attribute' => 'nama_sub_modul',
                    'header' => 'Nama Sub-Modul',
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
                    'attribute' => 'icon',
                    'header' => 'Ikon',
                    'format' => 'raw',
                    'value' => function($model) {
                      return $model->icon ? '<i class="' . Html::encode($model->icon) . ' fs-5"></i> <small class="text-muted ms-1">' . Html::encode($model->icon) . '</small>' : '-';
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
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Aksi',
                    'options' => ['style' => 'width: 100px'],
                    'buttons' => [
                      'view' => function ($url, $model) { return false; },
                      'update' => function ($url, $model) {
                        return Html::a('<i class="ti ti-edit"></i>', ['update-submodul', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-warning btn-modal-trigger', 
                            'title' => 'Edit',
                            'data-title' => 'Edit Sub-Modul'
                        ]);
                      },
                      'delete' => function ($url, $model) {
                        return Html::a('<i class="ti ti-trash"></i>', ['delete-submodul', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger', 
                            'data-confirm' => 'Hapus sub-modul ini?', 
                            'data-method' => 'post', 
                            'title' => 'Hapus'
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
  </div>
</div>

<!-- Main CRUD Modal -->
<div class="modal fade" id="crudModal" tabindex="-1" aria-labelledby="crudModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="crudModalLabel">Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="crudModalBody">
        <!-- Loaded via AJAX -->
      </div>
    </div>
  </div>
</div>

<?php
$this->registerJs(<<<JS
// Modal trigger script
$(document).on('click', '.btn-modal-trigger', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var title = $(this).data('title') || 'Form';
    $('#crudModalLabel').text(title);
    $('#crudModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat form...</p></div>');
    $('#crudModal').modal('show');
    $.get(url, function(data) {
        $('#crudModalBody').html(data);
    });
});

// Redirect inline to Navigasi category tab
$(document).on('click', '#btn-add-navigasi-inline', function(e) {
    e.preventDefault();
    $('#crudModal').modal('hide');
    $('#navigasi-tab').tab('show');
    setTimeout(function() {
        $('#tab-pane-navigasi a[data-title="Tambah Kategori Navigasi Baru"]').trigger('click');
    }, 450);
});

// Redirect inline to Modul tab
$(document).on('click', '#btn-add-modul-inline', function(e) {
    e.preventDefault();
    $('#crudModal').modal('hide');
    $('#modul-tab').tab('show');
    setTimeout(function() {
        $('#tab-pane-modul a[data-title="Tambah Modul Baru"]').trigger('click');
    }, 450);
});

// Handle tab pre-select on page load
$(document).ready(function() {
    var activeTab = '{$activeTab}';
    if (activeTab === 'modul') {
        $('#modul-tab').tab('show');
    } else if (activeTab === 'submodul') {
        $('#submodul-tab').tab('show');
    } else {
        $('#navigasi-tab').tab('show');
    }
});

// Update browser address bar tab query param on tab switch
$('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    var targetId = $(e.target).attr('id');
    var tabName = 'navigasi';
    if (targetId === 'modul-tab') tabName = 'modul';
    if (targetId === 'submodul-tab') tabName = 'submodul';
    
    var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabName;
    window.history.pushState({ path: newUrl }, '', newUrl);
});
JS
);
?>
