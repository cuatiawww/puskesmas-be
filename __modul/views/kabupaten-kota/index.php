<?php

use app\models\MasterWilayah;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = $pageTitle;
$this->params['active_menu'] = $activeMenu;

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = Json::encode($swal);
    $js = <<<JS
(function(){
  var opt = $swalJson;
  if(!opt.toast && opt.position === undefined) {
    opt.position = (opt.icon === 'success') ? 'center' : 'top-end';
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
                    <h5 class="mb-0 fw-bold"><?= Html::encode(strtoupper($pageTitle)) ?></h5>
                    <p><?= Html::encode($scopeSummary) ?></p>
                </div>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
                    <li class="breadcrumb-item"><?= Html::encode(strtoupper($pageTitle)) ?></li>
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
                    <h5>DAFTAR KABUPATEN/KOTA</h5>
                    <p class="mb-0">Data yang muncul mengikuti provinsi atau kabupaten user.</p>
                </div>
                <?php if ($canCrud): ?>
                    <div>
                        <a href="<?= Url::to(['create']) ?>" class="btn btn-primary">
                            <i class="ph-duotone ph-plus-square"></i> TAMBAH
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <?php Pjax::begin(['id' => 'pjax-kabupaten-kota']); ?>
                    <?= GridView::widget([
                        'summary' => '',
                        'layout' => "{items}\n<div class=\"d-flex justify-content-end mt-3\">{pager}</div>",
                        'tableOptions' => [
                            'id' => 'table-kabupaten-kota',
                            'class' => 'table table-striped table-hover',
                        ],
                        'pager' => [
                            'options' => ['class' => 'pagination mb-0'],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledPageCssClass' => 'page-item disabled',
                            'activePageCssClass' => 'page-item active',
                            'prevPageCssClass' => 'page-item',
                            'nextPageCssClass' => 'page-item',
                            'pageCssClass' => 'page-item',
                        ],
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
                            [
                                'attribute' => 'nama_wilayah',
                                'header' => 'NAMA KABUPATEN/KOTA',
                                'contentOptions' => ['style' => 'max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'],
                            ],
                            [
                                'header' => 'PROVINSI',
                                'value' => static function (MasterWilayah $model) {
                                    return $model->parent ? $model->parent->nama_wilayah : '-';
                                },
                                'contentOptions' => ['style' => 'max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'],
                            ],
                            [
                                'attribute' => 'status_aktif',
                                'header' => 'STATUS',
                                'value' => static fn(MasterWilayah $model) => (int) $model->status_aktif === 1 ? 'Aktif' : 'Nonaktif',
                                'contentOptions' => ['style' => 'width:110px;'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'AKSI',
                                'template' => $canCrud ? '{view} {update} {delete}' : '{view}',
                                'buttons' => [
                                    'view' => static fn($url, MasterWilayah $model) => Html::a('<i class="ti ti-eye"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-info text-white', 'title' => 'Lihat', 'data-pjax' => 0]),
                                    'update' => static fn($url, MasterWilayah $model) => Html::a('<i class="ti ti-pencil"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning', 'title' => 'Edit', 'data-pjax' => 0]),
                                    'delete' => static fn($url, MasterWilayah $model) => Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger', 'title' => 'Hapus', 'data-confirm' => 'Yakin hapus data ini?', 'data-method' => 'post', 'data-pjax' => 0]),
                                ],
                                'contentOptions' => ['style' => 'min-width:160px; text-align:center;'],
                            ],
                        ],
                    ]) ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
