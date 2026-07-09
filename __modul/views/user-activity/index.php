<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\SqlDataProvider $dataProvider */
/** @var yii\base\DynamicModel $searchModel */
/** @var array $stats */

$this->title = 'Log Aktivitas Pengguna';
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .table thead th a {
        color: #fff !important;
    }
    .table thead th a:hover {
        color: #fff !important;
        text-decoration: underline;
    }
");
?>

<!-- Page Header (Flat Able style) -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 font-weight-600 fw-bold">LOG AKTIVITAS PENGGUNA</h5>
                </div>
                <p>Matriks rekam jejak akses halaman dan perubahan database oleh user</p>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item">LOG AKTIVITAS PENGGUNA</li>
                </ul>
            </div>
            <div style="margin-top: 10px;" class="d-flex col-12">
                <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
                    <i class="ti ti-external-link me-1"></i> Info Selengkapnya
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistical Widgets (Flat Able theme classes) -->
<div class="row mb-4">
    <!-- Total Logs -->
    <div class="col-md-3">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">Total Log Aktivitas</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['total_logs']) ?></h3>
                    </div>
                    <i class="ti ti-history text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Users Today -->
    <div class="col-md-3">
        <div class="card bg-info text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">User Aktif Hari Ini</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['active_today']) ?></h3>
                    </div>
                    <i class="ti ti-user text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Actions -->
    <div class="col-md-3">
        <div class="card bg-success text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">Operasi Tambah (Create)</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['create_actions']) ?></h3>
                    </div>
                    <i class="ti ti-circle-plus text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Actions -->
    <div class="col-md-3">
        <div class="card bg-warning text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">Operasi Ubah (Update)</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['update_actions']) ?></h3>
                    </div>
                    <i class="ti ti-edit text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Matrix Grid -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>MATRIKS AKTIVITAS PENGGUNA</h5>
                    <p class="mb-0 text-muted">Daftar rekam jejak jumlah aktivitas dan informasi akses terakhir setiap user.</p>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <?php Pjax::begin(['id' => 'pjax-user-activity-matrix']); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-hover'],
                        'layout' => "{items}\n<div class='py-3 px-4 d-flex justify-content-between align-items-center'>{summary}<div class='d-flex justify-content-end'>{pager}</div></div>",
                        'pager' => [
                            'options' => ['class' => 'pagination mb-0'],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledPageCssClass' => 'page-item disabled',
                            'activePageCssClass' => 'page-item active',
                            'prevPageCssClass' => 'page-item',
                            'nextPageCssClass' => 'page-item',
                            'pageCssClass' => 'page-item',
                        ],
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'header' => 'NO',
                                'contentOptions' => ['style' => 'width: 60px;', 'class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'username',
                                'label' => 'USERNAME',
                                'value' => function ($model) {
                                    return '<strong>' . Html::encode($model['username']) . '</strong>';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'label' => 'VIEW (AKSES)',
                                'filter' => false,
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    return $model['view_count'] > 0 
                                        ? '<span class="badge bg-light text-dark border">' . $model['view_count'] . '</span>' 
                                        : '-';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'label' => 'CREATE (TAMBAH)',
                                'filter' => false,
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    return $model['create_count'] > 0 
                                        ? '<span class="badge bg-success">' . $model['create_count'] . '</span>' 
                                        : '-';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'label' => 'UPDATE (UBAH)',
                                'filter' => false,
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    return $model['update_count'] > 0 
                                        ? '<span class="badge bg-warning text-white">' . $model['update_count'] . '</span>' 
                                        : '-';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'label' => 'DELETE (HAPUS)',
                                'filter' => false,
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    return $model['delete_count'] > 0 
                                        ? '<span class="badge bg-danger">' . $model['delete_count'] . '</span>' 
                                        : '-';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'total_count',
                                'label' => 'TOTAL AKTIVITAS',
                                'filter' => false,
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    return '<span class="badge bg-primary">' . $model['total_count'] . '</span>';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'last_active',
                                'label' => 'AKTIVITAS TERAKHIR',
                                'filter' => false,
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asDatetime($model['last_active'], 'medium');
                                },
                            ],
                            [
                                'label' => 'IP & BROWSER TERAKHIR',
                                'filter' => false,
                                'value' => function ($model) {
                                    $ip = Html::encode($model['last_ip']);
                                    $browser = Html::encode($model['last_browser']);
                                    $platform = Html::encode($model['last_platform']);
                                    return "<div class='small text-muted'><i class='ti ti-device-desktop me-1'></i>{$browser} ({$platform})</div>
                                            <div class='small text-primary fw-bold'><i class='ti ti-world me-1'></i>{$ip}</div>";
                                },
                                'format' => 'raw',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'AKSI',
                                'template' => '{view}',
                                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 100px;'],
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a('<i class="ti ti-eye"></i>', 
                                            Url::to(['view', 'username' => $model['username']]), 
                                            [
                                                'class' => 'btn btn-sm btn-primary',
                                                'title' => 'Lihat Riwayat Aktivitas',
                                                'data-pjax' => 0
                                            ]
                                        );
                                    }
                                ]
                            ]
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
